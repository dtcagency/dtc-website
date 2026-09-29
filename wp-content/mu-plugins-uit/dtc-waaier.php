<?php
/**
 * Plugin Name: DTC hero-waaier
 * Description: Regelt de negen vakken van de beeldmozaiek in de hero op de homepage. Per vak kies je of er altijd hetzelfde beeld staat of dat het rouleert langs het werk dat we hebben gedaan. De rouleerbeelden komen uit de campagnekaarten, dus die hoef je maar op één plek bij te houden.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen; de waaier valt dan terug op de
 * beelden die in de pagina zelf staan.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const DTC_WA_OPTIE = 'dtc_waaier_opties';
const DTC_WA_SLUG  = 'dtc-waaier';
const DTC_WA_VAKKEN = 9;

function dtc_wa_opties() {
	$standaard = array(
		'interval' => 6,   // seconden; 0 = alleen wisselen bij het openen
		'rail'     => 'campaigns',
		'vakken'   => array(),
	);
	for ( $i = 1; $i <= DTC_WA_VAKKEN; $i++ ) {
		$standaard['vakken'][ $i ] = array( 'soort' => 'rouleren', 'beeld' => '' );
	}
	$bewaard = get_option( DTC_WA_OPTIE );
	if ( ! is_array( $bewaard ) ) { $bewaard = array(); }
	$uit = array_merge( $standaard, $bewaard );
	/* ontbrekende vakken aanvullen, zodat een half opgeslagen instelling niets breekt */
	for ( $i = 1; $i <= DTC_WA_VAKKEN; $i++ ) {
		if ( empty( $uit['vakken'][ $i ] ) || ! is_array( $uit['vakken'][ $i ] ) ) {
			$uit['vakken'][ $i ] = $standaard['vakken'][ $i ];
		}
	}
	return $uit;
}

/** de beelden waar de rouleervakken uit putten: de campagnekaarten */
function dtc_wa_voorraad() {
	$o = dtc_wa_opties();
	$uit = array();

	if ( function_exists( 'dtc_ca_kaarten' ) ) {
		foreach ( dtc_ca_kaarten( $o['rail'] ) as $kaart ) {
			$id = get_post_thumbnail_id( $kaart->ID );
			if ( ! $id ) { continue; }
			$url = wp_get_attachment_image_url( $id, 'large' );
			if ( ! $url ) { continue; }
			$uit[] = array(
				'url'  => $url,
				'naam' => $kaart->post_title,
				'link' => get_post_meta( $kaart->ID, '_dtc_campagne_link', true ),
			);
		}
	}
	return $uit;
}

/* ============================================================ de site */

add_action( 'wp_footer', 'dtc_wa_script', 14 );
function dtc_wa_script() {
	if ( is_admin() ) { return; }

	$o = dtc_wa_opties();
	$voorraad = dtc_wa_voorraad();

	/* zonder voorraad valt er niets te rouleren; dan laten we de waaier zoals hij is */
	$rouleert = false;
	foreach ( $o['vakken'] as $vak ) {
		if ( 'rouleren' === $vak['soort'] ) { $rouleert = true; }
	}
	if ( $rouleert && ! $voorraad ) { return; }

	$gegevens = array(
		'interval' => max( 0, (int) $o['interval'] ),
		'vakken'   => array_values( array_map( function ( $v ) {
			return array( 'soort' => $v['soort'], 'beeld' => $v['beeld'] );
		}, $o['vakken'] ) ),
		'voorraad' => $voorraad,
	);
	?>
<style id="dtc-waaier">
.dtc-wa-vak img{transition:opacity .5s ease}
.dtc-wa-vak.dtc-wa-wissel img{opacity:0}
@media (prefers-reduced-motion: reduce){ .dtc-wa-vak img{transition:none} }
</style>
<script id="dtc-waaier-js">
( function () {
	var gegevens = <?php echo wp_json_encode( $gegevens ); ?>;
	var voorraad = gegevens.voorraad || [];

	/* Op desktop staan de negen beelden als losse modules in de hero; op de
	   telefoon toont dezelfde reeks zich als carrousel. Allebei aansturen, in
	   dezelfde volgorde. */
	function vakken() {
		var uit = [];
		for ( var i = 0; i < <?php echo (int) DTC_WA_VAKKEN; ?>; i++ ) {
			var el = document.querySelector( '.et_pb_row_0 .et_pb_image_' + i + ' img' )
				|| document.querySelector( '.difl_contentcarouselitem_' + i + ' img' );
			if ( el ) { uit.push( { nr: i, img: el } ); }
		}
		return uit;
	}

	var lijst = vakken();
	if ( ! lijst.length ) { return; }

	/* beelden die al op een vast vak staan horen niet ook nog te rouleren;
	   anders zie je hetzelfde beeld twee keer naast elkaar */
	var vast = {};
	gegevens.vakken.forEach( function ( v ) {
		if ( v && 'vast' === v.soort && v.beeld ) { vast[ v.beeld ] = true; }
	} );
	voorraad = voorraad.filter( function ( b ) { return ! vast[ b.url ]; } );

	/* willekeurige volgorde, zodat niet elk bezoek dezelfde cases vooraan staan */
	var volgorde = voorraad.map( function ( _, i ) { return i; } );
	for ( var i = volgorde.length - 1; i > 0; i-- ) {
		var j = Math.floor( Math.random() * ( i + 1 ) );
		var h = volgorde[ i ]; volgorde[ i ] = volgorde[ j ]; volgorde[ j ] = h;
	}

	var rouleert = [];
	var teller = 0;

	lijst.forEach( function ( vak ) {
		var inst = gegevens.vakken[ vak.nr ];
		if ( ! inst ) { return; }
		vak.img.closest( '.et_pb_image, .difl_contentcarouselitem' ).classList.add( 'dtc-wa-vak' );

		if ( 'vast' === inst.soort ) {
			if ( inst.beeld ) { zet( vak, { url: inst.beeld } ); }
			return;
		}
		if ( ! voorraad.length ) { return; }
		var stand = teller % volgorde.length;
		teller++;
		zet( vak, voorraad[ volgorde[ stand ] ] );
		vak.stand = stand;
		rouleert.push( vak );
	} );

	function zet( vak, beeld ) {
		if ( ! beeld || ! beeld.url ) { return; }
		vak.img.src = beeld.url;
		vak.img.removeAttribute( 'srcset' );
		if ( beeld.naam ) { vak.img.alt = beeld.naam; }
	}

	function wissel( vak, beeld ) {
		var houder = vak.img.closest( '.dtc-wa-vak' );
		var vooraf = new Image();
		vooraf.onload = function () {
			houder.classList.add( 'dtc-wa-wissel' );
			setTimeout( function () {
				zet( vak, beeld );
				houder.classList.remove( 'dtc-wa-wissel' );
			}, 520 );
		};
		vooraf.src = beeld.url;
	}

	if ( ! gegevens.interval || ! rouleert.length || ! voorraad.length ) { return; }
	if ( window.matchMedia && matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }

	/* steeds één vak tegelijk; dat oogt rustiger dan alles tegelijk */
	var beurt = 0;
	setInterval( function () {
		if ( document.hidden ) { return; }
		var vak = rouleert[ beurt % rouleert.length ];
		beurt++;
		vak.stand = ( vak.stand + rouleert.length ) % volgorde.length;
		wissel( vak, voorraad[ volgorde[ vak.stand ] ] );
	}, Math.max( 2, gegevens.interval ) * 1000 );
} )();
</script>
	<?php
}

/* ============================================================ instellingen */

add_action( 'admin_menu', function () {
	if ( defined( 'DTC_TK_SLUG' ) && menu_page_url( DTC_TK_SLUG, false ) ) {
		add_submenu_page( DTC_TK_SLUG, 'Hero-waaier', 'Hero-waaier', 'edit_pages', DTC_WA_SLUG, 'dtc_wa_scherm' );
	} else {
		add_menu_page( 'Hero-waaier', 'Hero-waaier', 'edit_pages', DTC_WA_SLUG, 'dtc_wa_scherm', 'dashicons-images-alt', 5 );
	}
}, 11 );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( false !== strpos( $hook, DTC_WA_SLUG ) ) { wp_enqueue_media(); }
} );

add_action( 'admin_post_dtc_wa_opslaan', function () {
	if ( ! current_user_can( 'edit_pages' ) ) { wp_die( 'Geen rechten.' ); }
	check_admin_referer( 'dtc_wa_opties' );
	$in = wp_unslash( $_POST );

	$vakken = array();
	for ( $i = 1; $i <= DTC_WA_VAKKEN; $i++ ) {
		$soort = ( isset( $in['soort'][ $i ] ) && 'vast' === $in['soort'][ $i ] ) ? 'vast' : 'rouleren';
		$vakken[ $i ] = array(
			'soort' => $soort,
			'beeld' => esc_url_raw( trim( (string) ( $in['beeld'][ $i ] ?? '' ) ) ),
		);
	}

	update_option( DTC_WA_OPTIE, array(
		'interval' => max( 0, min( 60, (int) ( $in['interval'] ?? 6 ) ) ),
		'rail'     => sanitize_key( $in['rail'] ?? 'campaigns' ),
		'vakken'   => $vakken,
	), false );

	wp_safe_redirect( add_query_arg( array( 'page' => DTC_WA_SLUG, 'dtc' => 'opgeslagen' ), admin_url( 'admin.php' ) ) );
	exit;
} );

function dtc_wa_scherm() {
	if ( ! current_user_can( 'edit_pages' ) ) { wp_die( 'Geen rechten.' ); }
	$o = dtc_wa_opties();
	$voorraad = dtc_wa_voorraad();

	$rails = array();
	if ( taxonomy_exists( 'campagne_rail' ) ) {
		foreach ( get_terms( array( 'taxonomy' => 'campagne_rail', 'hide_empty' => false ) ) as $term ) {
			if ( ! is_wp_error( $term ) ) { $rails[ $term->slug ] = $term->name; }
		}
	}
	?>
	<div class="wrap dtc-tk dtc-wa">
		<?php
		if ( function_exists( 'dtc_tk_titelbalk' ) ) {
			dtc_tk_titelbalk( 'De negen beelden in de hero op de homepage: per vak een vast beeld of rouleren langs het werk.' );
		} else { echo '<h1>Hero-waaier</h1>'; }
		?>

		<?php if ( isset( $_GET['dtc'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>De instellingen zijn opgeslagen.</p></div>
		<?php endif; ?>

		<?php if ( ! $voorraad ) : ?>
			<div class="notice notice-warning inline"><p>Er zijn nog geen campagnekaarten met een beeld in de gekozen rail, dus er valt niets te rouleren. Voeg ze toe onder <strong>Campagnes</strong>.</p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="dtc_wa_opslaan">
			<?php wp_nonce_field( 'dtc_wa_opties' ); ?>

			<div class="dtc-wa-vakken">
				<?php for ( $i = 1; $i <= DTC_WA_VAKKEN; $i++ ) :
					$vak = $o['vakken'][ $i ]; ?>
					<div class="dtc-tk-groep dtc-wa-vak">
						<h2>vak <?php echo (int) $i; ?></h2>
						<p>
							<label><input type="radio" name="soort[<?php echo (int) $i; ?>]" value="rouleren" <?php checked( $vak['soort'], 'rouleren' ); ?>> rouleren langs het werk</label><br>
							<label><input type="radio" name="soort[<?php echo (int) $i; ?>]" value="vast" <?php checked( $vak['soort'], 'vast' ); ?>> altijd dit beeld</label>
						</p>
						<div class="dtc-wa-beeld">
							<div class="voorbeeld"><?php if ( $vak['beeld'] ) : ?><img src="<?php echo esc_url( $vak['beeld'] ); ?>" alt=""><?php else : ?><span>geen beeld</span><?php endif; ?></div>
							<input type="hidden" name="beeld[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $vak['beeld'] ); ?>" class="dtc-wa-url">
							<button type="button" class="button dtc-wa-kies">Beeld kiezen&hellip;</button>
							<button type="button" class="button dtc-wa-leeg">Leegmaken</button>
						</div>
					</div>
				<?php endfor; ?>
			</div>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="dtc-wa-interval">Wisselen om de</label></th>
					<td><input type="number" id="dtc-wa-interval" name="interval" value="<?php echo esc_attr( $o['interval'] ); ?>" min="0" max="60" step="1"> seconden
					<p class="description">0 = niet wisselen terwijl iemand kijkt; bij elk bezoek staat er dan wel een andere selectie. Er wisselt steeds één vak tegelijk.</p></td>
				</tr>
				<?php if ( $rails ) : ?>
				<tr>
					<th scope="row"><label for="dtc-wa-rail">Waar komen de beelden vandaan</label></th>
					<td>
						<select id="dtc-wa-rail" name="rail">
							<?php foreach ( $rails as $slug => $naam ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $o['rail'], $slug ); ?>><?php echo esc_html( $naam ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">De campagnekaarten uit deze rail. Nu beschikbaar: <strong><?php echo count( $voorraad ); ?></strong> beeld<?php echo 1 === count( $voorraad ) ? '' : 'en'; ?>.</p>
					</td>
				</tr>
				<?php endif; ?>
			</table>

			<p class="submit"><?php submit_button( 'Opslaan', 'primary', 'submit', false ); ?></p>
		</form>
	</div>

	<style>
		.dtc-wa-vakken{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin:18px 0}
		.dtc-wa-vak{margin:0}
		.dtc-wa-vak label{display:inline-flex;align-items:center;gap:8px;font-size:14px;line-height:2}
		.dtc-wa-beeld .voorbeeld{height:120px;display:flex;align-items:center;justify-content:center;background:#f4f1ec;border-radius:10px;overflow:hidden;margin-bottom:10px}
		.dtc-wa-beeld .voorbeeld img{max-width:100%;max-height:120px;object-fit:contain}
		.dtc-wa-beeld .voorbeeld span{color:#a99a86;font-size:13px}
	</style>
	<?php if ( function_exists( 'dtc_tk_stijl' ) ) { dtc_tk_stijl(); } ?>
	<script>
	jQuery( function ( $ ) {
		$( document ).on( 'click', '.dtc-wa-kies', function () {
			var vak = $( this ).closest( '.dtc-wa-beeld' );
			var kader = wp.media( { title: 'Kies een beeld', button: { text: 'Deze gebruiken' }, multiple: false } );
			kader.on( 'select', function () {
				var bestand = kader.state().get( 'selection' ).first().toJSON();
				vak.find( '.dtc-wa-url' ).val( bestand.url );
				vak.find( '.voorbeeld' ).html( $( '<img>' ).attr( 'src', bestand.url ) );
				vak.closest( '.dtc-wa-vak' ).find( 'input[value=vast]' ).prop( 'checked', true );
			} );
			kader.open();
		} );
		$( document ).on( 'click', '.dtc-wa-leeg', function () {
			var vak = $( this ).closest( '.dtc-wa-beeld' );
			vak.find( '.dtc-wa-url' ).val( '' );
			vak.find( '.voorbeeld' ).html( '<span>geen beeld</span>' );
		} );
	} );
	</script>
	<?php
}
