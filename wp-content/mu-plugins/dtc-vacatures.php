<?php
/**
 * Plugin Name: DTC vacatures
 * Description: Haalt de open vacatures live op bij jobs.dtc.agency en toont ze via [dtc_vacatures].
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const DTC_VAC_BRON     = 'https://jobs.dtc.agency';
const DTC_VAC_CACHE    = 'dtc_vacatures_cache';    // transient, 6 uur
const DTC_VAC_RESERVE  = 'dtc_vacatures_reserve';  // option, laatste geslaagde ophaling

/* ---------------------------------------------------------------- ophalen */

function dtc_vac_tekst( $ruw ) {
	$t = wp_strip_all_tags( html_entity_decode( $ruw, ENT_QUOTES, 'UTF-8' ) );
	return trim( preg_replace( '/\s+/u', ' ', $t ) );
}

/** eerste zinnen tot ongeveer $max tekens */
function dtc_vac_inkorten( $tekst, $max = 340 ) {
	if ( mb_strlen( $tekst ) <= $max ) { return $tekst; }
	$zinnen = preg_split( '/(?<=[.!?])\s+/u', $tekst );
	$uit = '';
	foreach ( $zinnen as $zin ) {
		if ( $uit !== '' && mb_strlen( $uit . ' ' . $zin ) > $max ) { break; }
		$uit = $uit === '' ? $zin : $uit . ' ' . $zin;
	}
	return $uit !== '' ? $uit : ( mb_substr( $tekst, 0, $max ) . '…' );
}

/** uren in het Engels: "fulltime (32-40 uur)" -> "fulltime (32-40 hours)" */
function dtc_vac_engels( $tekst ) {
	$vervang = array(
		'uur per week' => 'hours a week',
		'uur'          => 'hours',
		'hrs'          => 'hours',
		'hr'           => 'hours',
		"\u{2013}"      => '-',
		'Fulltime'     => 'full-time',
		'fulltime'     => 'full-time',
		'Parttime'     => 'part-time',
		'parttime'     => 'part-time',
	);
	return strtr( $tekst, $vervang );
}

function dtc_vac_haal_detail( $url ) {
	$antwoord = wp_remote_get( $url, array( 'timeout' => 12, 'user-agent' => 'dtc-website' ) );
	if ( is_wp_error( $antwoord ) || 200 !== wp_remote_retrieve_response_code( $antwoord ) ) {
		return array();
	}
	$html = wp_remote_retrieve_body( $antwoord );
	$uit  = array();

	if ( preg_match( '/class="dtc-job-location"[^>]*>(.*?)<\/span>/s', $html, $m ) ) {
		$uit['plaats'] = dtc_vac_tekst( $m[1] );
	}
	if ( preg_match( '/class="dtc-job-dept-badge"[^>]*>(.*?)<\/span>/s', $html, $m ) ) {
		$uit['afdeling'] = dtc_vac_tekst( $m[1] );
	}
	if ( preg_match( '/class="dtc-job-meta-text"[^>]*>(.*?)<\/span>/s', $html, $m ) ) {
		$uit['uren'] = dtc_vac_engels( dtc_vac_tekst( $m[1] ) );
	}
	if ( preg_match( '/class="dtc-job-intro"[^>]*>(.*?)<\/div>/s', $html, $m ) ) {
		$uit['intro'] = dtc_vac_inkorten( dtc_vac_tekst( $m[1] ) );
	}
	return $uit;
}

/** lijst met vacatures; array van [titel, meta, intro, link] */
function dtc_vac_ophalen() {
	$api = DTC_VAC_BRON . '/wp-json/wp/v2/dtc_job?per_page=20&orderby=date&order=desc&_fields=id,title,link,slug';
	$antwoord = wp_remote_get( $api, array( 'timeout' => 12, 'user-agent' => 'dtc-website' ) );
	if ( is_wp_error( $antwoord ) || 200 !== wp_remote_retrieve_response_code( $antwoord ) ) {
		return false;
	}
	$rijen = json_decode( wp_remote_retrieve_body( $antwoord ), true );
	if ( ! is_array( $rijen ) || ! $rijen ) { return false; }

	$jobs = array();
	foreach ( $rijen as $rij ) {
		if ( empty( $rij['link'] ) ) { continue; }
		$titel  = dtc_vac_tekst( isset( $rij['title']['rendered'] ) ? $rij['title']['rendered'] : '' );
		if ( '' === $titel ) { continue; }
		$detail = dtc_vac_haal_detail( $rij['link'] );

		$delen = array();
		if ( ! empty( $detail['afdeling'] ) ) { $delen[] = $detail['afdeling']; }
		if ( ! empty( $detail['plaats'] ) )   { $delen[] = $detail['plaats']; }
		if ( ! empty( $detail['uren'] ) )     { $delen[] = $detail['uren']; }

		$jobs[] = array(
			'titel' => mb_strtolower( $titel, 'UTF-8' ),
			'meta'  => mb_strtolower( implode( ' · ', $delen ), 'UTF-8' ),
			'intro' => ! empty( $detail['intro'] ) ? $detail['intro'] : '',
			'link'  => $rij['link'],
		);
	}
	return $jobs ? $jobs : false;
}

/** uit cache, met terugval op de laatste geslaagde ophaling */
function dtc_vac_jobs() {
	$cache = get_transient( DTC_VAC_CACHE );
	if ( is_array( $cache ) && $cache ) { return $cache; }

	// cache verlopen maar er is nog een goede lijst: die direct tonen en op de
	// achtergrond verversen, zodat niemand op de bezoeker hoeft te wachten
	$reserve = get_option( DTC_VAC_RESERVE );
	if ( is_array( $reserve ) && $reserve ) {
		set_transient( DTC_VAC_CACHE, $reserve, 20 * MINUTE_IN_SECONDS );
		if ( ! wp_next_scheduled( 'dtc_vac_ververs' ) || wp_next_scheduled( 'dtc_vac_ververs' ) > time() + 120 ) {
			wp_schedule_single_event( time() + 30, 'dtc_vac_ververs' );
		}
		return $reserve;
	}

	// nog nooit opgehaald: nu wel wachten
	$jobs = dtc_vac_ophalen();
	if ( $jobs ) {
		set_transient( DTC_VAC_CACHE, $jobs, 6 * HOUR_IN_SECONDS );
		update_option( DTC_VAC_RESERVE, $jobs, false );
		return $jobs;
	}
	return array();
}

/* ------------------------------------------------------- cron: vooraf verversen */

add_action( 'dtc_vac_ververs', 'dtc_vac_cron_ververs' );
function dtc_vac_cron_ververs() {
	$jobs = dtc_vac_ophalen();
	if ( $jobs ) {
		set_transient( DTC_VAC_CACHE, $jobs, 6 * HOUR_IN_SECONDS );
		update_option( DTC_VAC_RESERVE, $jobs, false );
	}
}
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'dtc_vac_ververs' ) ) {
		wp_schedule_event( time() + 300, 'twicedaily', 'dtc_vac_ververs' );
	}
} );

/* ------------------------------------------------------------------ tonen */

function dtc_vac_stats() {
	return array(
		array( '220', 'customer base' ),
		array( '20', 'years of experience' ),
		array( '3110', 'projects yearly' ),
	);
}

function dtc_vac_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'kop'    => 'open roles',
		'onder'  => 'We grow with the work. These are the seats we are looking to fill right now — all of them in Utrecht, all of them in the same room as the rest of the team.',
		'stats'  => 'ja',
	), $atts, 'dtc_vacatures' );

	$jobs = dtc_vac_jobs();

	$jobs[] = array(
		'titel' => 'open application',
		'meta'  => 'always open · utrecht',
		'intro' => 'Nothing that fits, but you think you belong here? Tell us what you would add to the team and we will make time for a coffee.',
		'link'  => DTC_VAC_BRON . '/',
		'knop'  => 'send your story',
	);

	ob_start();
	?>
<style>
@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Medium.woff2') format('woff2');font-weight:500;font-display:swap}
@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Light.woff2') format('woff2');font-weight:300;font-display:swap}
.dtc-vac{font-family:'Arboria',sans-serif;background:#f4f1ec;padding:80px 0}
.dtc-vac .in{width:76%;max-width:100%;margin:0 auto}
.dtc-vac .smal{max-width:980px;margin:0 auto}
.dtc-vac h2{font-weight:500;font-size:75px;line-height:.8;color:#2d2824;text-align:left;margin:0 0 22px;text-transform:lowercase}
.dtc-vac .onder{font-weight:300;font-size:18px;line-height:1.8;color:#2d2824;text-align:left;margin:0 0 46px;max-width:56ch}
.dtc-vac details{border-top:1px solid rgba(45,40,36,.16)}
.dtc-vac details:last-of-type{border-bottom:1px solid rgba(45,40,36,.16)}
.dtc-vac summary{list-style:none;cursor:pointer;display:grid;grid-template-columns:44% 1fr;align-items:center;gap:24px;padding:24px 0}
.dtc-vac summary::-webkit-details-marker{display:none}
.dtc-vac .titel{display:flex;align-items:center;gap:14px;font-weight:500;font-size:24px;line-height:1.5;color:#2d2824;transition:color .25s}
.dtc-vac .plus{flex:0 0 auto;font-weight:300;font-size:26px;line-height:1;color:#c0ab94;transition:transform .3s}
.dtc-vac details[open] .plus{transform:rotate(45deg)}
.dtc-vac .meta{font-weight:300;font-size:16px;line-height:1.8;color:#6c6259}
.dtc-vac summary:hover .titel{color:#8a7257}
.dtc-vac .paneel{padding:0 0 28px;display:grid;grid-template-columns:44% 1fr;gap:24px}
.dtc-vac .paneel p{grid-column:2;font-weight:300;font-size:18px;line-height:1.8;color:#2d2824;margin:0 0 24px;max-width:60ch}
.dtc-vac .btn{grid-column:2;justify-self:start;display:inline-flex;align-items:center;gap:10px;background:#2d2824;color:#fff;border-radius:100px;padding:8px 30px 8px 9px;text-decoration:none;font-weight:500;font-size:16px;line-height:1.7;transition:background .3s,color .3s}
.dtc-vac .btn .pijl{display:inline-flex;align-items:center;justify-content:center;width:39px;height:39px;border:2px solid #c0ab94;border-radius:72px;font-family:'ETmodules';font-size:22px;line-height:22px;transition:background .3s,color .3s}
.dtc-vac .btn:hover{background:#c0ab94;color:#2d2824}
.dtc-vac .btn:hover .pijl{background:#2d2824;color:#fff}
.dtc-vac .cijfers{display:flex;justify-content:center;align-items:flex-start;gap:clamp(40px,8vw,140px);margin-top:66px}
.dtc-vac .cijfer{text-align:center}
.dtc-vac .cijfer b{display:block;font-weight:500;font-size:clamp(40px,4.6vw,66px);line-height:1;color:#2d2824}
.dtc-vac .cijfer span{display:block;font-weight:300;font-size:15px;line-height:1.6;color:#6c6259;margin-top:12px}
@media(max-width:900px){.dtc-vac{padding:56px 0}.dtc-vac h2{font-size:70px}.dtc-vac .titel{font-size:22px}.dtc-vac summary{grid-template-columns:1fr;gap:6px;padding:20px 0}
.dtc-vac .paneel{grid-template-columns:1fr}.dtc-vac .paneel p,.dtc-vac .btn{grid-column:1}
.dtc-vac .cijfers{margin-top:50px;gap:clamp(24px,6vw,60px)}}
@media(max-width:600px){.dtc-vac .in{width:90%}.dtc-vac h2{font-size:50px;line-height:.9}.dtc-vac .titel{font-size:20px}.dtc-vac .cijfers{gap:10px;justify-content:space-between;margin-top:44px}
.dtc-vac .cijfer{flex:1 1 0}.dtc-vac .cijfer b{font-size:34px}.dtc-vac .cijfer span{font-size:13px;margin-top:8px}}
</style>
<div class="dtc-vac"><div class="in">
	<h2><?php echo esc_html( $atts['kop'] ); ?></h2>
	<?php if ( $atts['onder'] ) : ?><p class="onder"><?php echo esc_html( $atts['onder'] ); ?></p><?php endif; ?>
	<div class="smal">
	<?php foreach ( $jobs as $job ) : ?>
		<details>
			<summary>
				<span class="titel"><span class="plus">+</span><?php echo esc_html( $job['titel'] ); ?></span>
				<span class="meta"><?php echo esc_html( $job['meta'] ); ?></span>
			</summary>
			<div class="paneel">
				<?php if ( ! empty( $job['intro'] ) ) : ?><p><?php echo esc_html( $job['intro'] ); ?></p><?php endif; ?>
				<a class="btn" href="<?php echo esc_url( $job['link'] ); ?>" target="_blank" rel="noopener">
					<span class="pijl">&#x24;</span> <?php echo esc_html( ! empty( $job['knop'] ) ? $job['knop'] : 'view vacancy' ); ?>
				</a>
			</div>
		</details>
	<?php endforeach; ?>
	</div>
	<?php if ( 'ja' === $atts['stats'] ) : ?>
	<div class="cijfers">
		<?php foreach ( dtc_vac_stats() as $stat ) : ?>
			<div class="cijfer"><b><?php echo esc_html( $stat[0] ); ?></b><span><?php echo esc_html( $stat[1] ); ?></span></div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div></div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'dtc_vacatures', 'dtc_vac_shortcode' );
