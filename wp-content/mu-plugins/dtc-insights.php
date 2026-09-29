<?php
/**
 * Plugin Name: dtc insights
 * Description: Eigen bezoekcijfers voor de site: paginaweergaven, bezoekers, bronnen, apparaten en verblijfsduur. Alles blijft op deze server, er gaan geen gegevens naar Google of een andere partij, en er wordt geen cookie geplaatst.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen. De tabel blijft dan staan; wissen kan
 * op het scherm zelf onder "instellingen".
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const DTC_IN_SLUG   = 'dtc-insights';
const DTC_IN_CAP    = 'edit_pages';
const DTC_IN_OPTIE  = 'dtc_insights_opties';
const DTC_IN_DB     = 'dtc_insights_db';
const DTC_IN_VERSIE = 1;

function dtc_in_tabel() {
	global $wpdb;
	return $wpdb->prefix . 'dtc_insights';
}

function dtc_in_opties() {
	$standaard = array(
		'aan'      => 1,   // meten aan of uit
		'eigen'    => 0,   // eigen bezoeken (ingelogde redacteuren) meetellen
		'bewaren'  => 24,  // maanden
	);
	$bewaard = get_option( DTC_IN_OPTIE );
	if ( ! is_array( $bewaard ) ) { $bewaard = array(); }
	return array_merge( $standaard, $bewaard );
}

/* ============================================================ de tabel */

add_action( 'init', 'dtc_in_installeren', 1 );
function dtc_in_installeren() {
	if ( (int) get_option( DTC_IN_DB ) === DTC_IN_VERSIE ) { return; }

	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$tabel   = dtc_in_tabel();
	$collatie = $wpdb->get_charset_collate();

	dbDelta( "CREATE TABLE $tabel (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		tijd datetime NOT NULL,
		dag date NOT NULL,
		bezoeker char(32) NOT NULL,
		sessie char(32) NOT NULL,
		pad varchar(190) NOT NULL DEFAULT '/',
		titel varchar(190) NOT NULL DEFAULT '',
		bron varchar(100) NOT NULL DEFAULT '',
		verwijzer varchar(190) NOT NULL DEFAULT '',
		campagne varchar(100) NOT NULL DEFAULT '',
		apparaat varchar(10) NOT NULL DEFAULT '',
		browser varchar(30) NOT NULL DEFAULT '',
		systeem varchar(30) NOT NULL DEFAULT '',
		seconden smallint(5) unsigned NOT NULL DEFAULT 0,
		eerste tinyint(1) NOT NULL DEFAULT 0,
		PRIMARY KEY  (id),
		KEY dag (dag),
		KEY tijd (tijd),
		KEY sessie (sessie),
		KEY bezoeker (bezoeker),
		KEY pad (pad(64))
	) $collatie;" );

	update_option( DTC_IN_DB, DTC_IN_VERSIE, true );
}

/* ============================================================ meten */

/**
 * Het zout wisselt elke dag. Daardoor is het bezoekerskenmerk een dag lang
 * bruikbaar om herhaalbezoek te herkennen, maar is het niet terug te rekenen
 * naar een IP-adres en zijn twee dagen niet aan elkaar te koppelen.
 */
function dtc_in_zout() {
	$z       = get_option( 'dtc_insights_zout' );
	$vandaag = gmdate( 'Y-m-d' );
	if ( ! is_array( $z ) || empty( $z['dag'] ) || $z['dag'] !== $vandaag ) {
		$z = array( 'dag' => $vandaag, 'zout' => wp_generate_password( 32, false, false ) );
		update_option( 'dtc_insights_zout', $z, true );
	}
	return $z['zout'];
}

function dtc_in_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $sleutel ) {
		if ( ! empty( $_SERVER[ $sleutel ] ) ) {
			$delen = explode( ',', (string) $_SERVER[ $sleutel ] );
			return trim( $delen[0] );
		}
	}
	return '';
}

/** het kenmerk van de bezoeker: een hash, geen IP-adres */
function dtc_in_bezoeker() {
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
	return md5( dtc_in_zout() . '|' . dtc_in_ip() . '|' . $ua );
}

function dtc_in_robot( $ua ) {
	$ua = strtolower( $ua );
	if ( '' === $ua ) { return true; }
	foreach ( array( 'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'headless', 'lighthouse', 'preview', 'monitor', 'pingdom', 'python-', 'facebookexternalhit', 'ahrefs', 'semrush' ) as $woord ) {
		if ( false !== strpos( $ua, $woord ) ) { return true; }
	}
	return false;
}

function dtc_in_browser( $ua ) {
	$kaart = array(
		'Edg/' => 'Edge', 'OPR/' => 'Opera', 'SamsungBrowser' => 'Samsung',
		'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari',
	);
	foreach ( $kaart as $naald => $naam ) {
		if ( false !== strpos( $ua, $naald ) ) { return $naam; }
	}
	return 'overig';
}

function dtc_in_systeem( $ua ) {
	$kaart = array(
		'iPhone' => 'iOS', 'iPad' => 'iPadOS', 'Android' => 'Android',
		'Mac OS X' => 'macOS', 'Windows' => 'Windows', 'Linux' => 'Linux',
	);
	foreach ( $kaart as $naald => $naam ) {
		if ( false !== strpos( $ua, $naald ) ) { return $naam; }
	}
	return 'overig';
}

/** waar komt het bezoek vandaan: een eigen naam per herkomst */
function dtc_in_bron( $verwijzer ) {
	$verwijzer = trim( (string) $verwijzer );
	if ( '' === $verwijzer ) { return 'direct'; }

	$host = strtolower( (string) wp_parse_url( $verwijzer, PHP_URL_HOST ) );
	if ( '' === $host ) { return 'direct'; }
	$host = preg_replace( '/^www\./', '', $host );

	$eigen = preg_replace( '/^www\./', '', strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
	if ( $host === $eigen ) { return 'intern'; }

	$kaart = array(
		'google'     => 'Google',       'bing'      => 'Bing',
		'duckduckgo' => 'DuckDuckGo',   'ecosia'    => 'Ecosia',
		'yahoo'      => 'Yahoo',        'linkedin'  => 'LinkedIn',
		'facebook'   => 'Facebook',     'instagram' => 'Instagram',
		'youtube'    => 'YouTube',      'tiktok'    => 'TikTok',
		'pinterest'  => 'Pinterest',    'reddit'    => 'Reddit',
		't.co'       => 'X',            'x.com'     => 'X',
		'chatgpt'    => 'ChatGPT',      'perplexity' => 'Perplexity',
		'claude'     => 'Claude',
	);
	foreach ( $kaart as $naald => $naam ) {
		if ( false !== strpos( $host, $naald ) ) { return $naam; }
	}
	return $host;
}

/** zoekmachine, sociaal of gewoon een verwijzing — voor de groepering */
function dtc_in_soort( $bron ) {
	if ( 'direct' === $bron ) { return 'direct'; }
	if ( 'intern' === $bron ) { return 'intern'; }
	if ( in_array( $bron, array( 'Google', 'Bing', 'DuckDuckGo', 'Ecosia', 'Yahoo' ), true ) ) { return 'zoekmachine'; }
	if ( in_array( $bron, array( 'ChatGPT', 'Perplexity', 'Claude' ), true ) ) { return 'AI-assistent'; }
	if ( in_array( $bron, array( 'LinkedIn', 'Facebook', 'Instagram', 'YouTube', 'TikTok', 'Pinterest', 'Reddit', 'X' ), true ) ) { return 'sociaal'; }
	return 'verwijzing';
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'dtc-insights/v1', '/tel', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'dtc_in_tel',
	) );
	register_rest_route( 'dtc-insights/v1', '/duur', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'dtc_in_duur',
	) );
} );

function dtc_in_tel( WP_REST_Request $verzoek ) {
	$o = dtc_in_opties();
	if ( ! $o['aan'] ) { return array( 'id' => 0 ); }

	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
	if ( dtc_in_robot( $ua ) ) { return array( 'id' => 0 ); }

	global $wpdb;
	$tabel = dtc_in_tabel();

	/* het pad zonder vraagtekens erachter, anders valt elke campagnelink apart */
	$ruw  = (string) $verzoek->get_param( 'pad' );
	$pad  = (string) wp_parse_url( $ruw, PHP_URL_PATH );
	if ( '' === $pad ) { $pad = '/'; }
	$pad  = '/' . ltrim( $pad, '/' );
	$pad  = mb_substr( $pad, 0, 190 );

	/* utm-waarden uit de link halen, zodat campagnes herkenbaar blijven */
	$campagne = '';
	$vraag    = (string) wp_parse_url( $ruw, PHP_URL_QUERY );
	if ( $vraag ) {
		parse_str( $vraag, $delen );
		$stukken = array();
		foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign' ) as $sleutel ) {
			if ( ! empty( $delen[ $sleutel ] ) ) { $stukken[] = sanitize_text_field( (string) $delen[ $sleutel ] ); }
		}
		$campagne = mb_substr( implode( ' / ', $stukken ), 0, 100 );
	}

	$verwijzer = esc_url_raw( (string) $verzoek->get_param( 'verwijzer' ) );
	$bron      = dtc_in_bron( $verwijzer );
	$breedte   = (int) $verzoek->get_param( 'breedte' );

	if ( $breedte > 0 ) {
		$apparaat = $breedte < 768 ? 'telefoon' : ( $breedte < 1024 ? 'tablet' : 'desktop' );
	} else {
		$apparaat = wp_is_mobile() ? 'telefoon' : 'desktop';
	}

	$bezoeker = dtc_in_bezoeker();
	$nu       = current_time( 'mysql' );

	/* dezelfde bezoeker binnen een half uur = hetzelfde bezoek */
	$vorige = $wpdb->get_row( $wpdb->prepare(
		"SELECT sessie, tijd FROM $tabel WHERE bezoeker = %s ORDER BY id DESC LIMIT 1",
		$bezoeker
	) );
	if ( $vorige && strtotime( $vorige->tijd ) > strtotime( $nu ) - 1800 ) {
		$sessie = $vorige->sessie;
		$eerste = 0;
	} else {
		$sessie = md5( $bezoeker . microtime( true ) . wp_rand() );
		$eerste = 1;
	}

	$wpdb->insert( $tabel, array(
		'tijd'      => $nu,
		'dag'       => substr( $nu, 0, 10 ),
		'bezoeker'  => $bezoeker,
		'sessie'    => $sessie,
		'pad'       => $pad,
		'titel'     => mb_substr( sanitize_text_field( (string) $verzoek->get_param( 'titel' ) ), 0, 190 ),
		'bron'      => mb_substr( $bron, 0, 100 ),
		'verwijzer' => mb_substr( $verwijzer, 0, 190 ),
		'campagne'  => $campagne,
		'apparaat'  => $apparaat,
		'browser'   => dtc_in_browser( $ua ),
		'systeem'   => dtc_in_systeem( $ua ),
		'seconden'  => 0,
		'eerste'    => $eerste,
	) );

	return array( 'id' => (int) $wpdb->insert_id );
}

function dtc_in_duur( WP_REST_Request $verzoek ) {
	global $wpdb;
	$tabel    = dtc_in_tabel();
	$id       = absint( $verzoek->get_param( 'id' ) );
	$seconden = (int) $verzoek->get_param( 'seconden' );
	if ( ! $id || $seconden < 1 ) { return array( 'ok' => false ); }
	$seconden = min( $seconden, 3600 );

	/* alleen een regel van het laatste half uur bijwerken, en nooit omlaag */
	$wpdb->query( $wpdb->prepare(
		"UPDATE $tabel SET seconden = GREATEST(seconden, %d) WHERE id = %d AND tijd > DATE_SUB(%s, INTERVAL 30 MINUTE)",
		$seconden, $id, current_time( 'mysql' )
	) );
	return array( 'ok' => true );
}

/* het stukje meetcode op de site zelf */
add_action( 'wp_footer', 'dtc_in_meetcode', 5 );
function dtc_in_meetcode() {
	$o = dtc_in_opties();
	if ( ! $o['aan'] || is_admin() || is_preview() || is_customize_preview() ) { return; }
	if ( ! $o['eigen'] && is_user_logged_in() && current_user_can( DTC_IN_CAP ) ) { return; }

	$basis = esc_url_raw( rest_url( 'dtc-insights/v1/' ) );
	?>
<script id="dtc-insights">
( function () {
	var basis = '<?php echo esc_js( $basis ); ?>';
	var start = Date.now();
	var id = 0;

	try {
		fetch( basis + 'tel', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			credentials: 'omit',
			keepalive: true,
			body: JSON.stringify( {
				pad: location.pathname + location.search,
				titel: document.title,
				verwijzer: document.referrer,
				breedte: window.innerWidth
			} )
		} ).then( function ( r ) { return r.json(); } )
		  .then( function ( a ) { if ( a && a.id ) { id = a.id; } } )
		  .catch( function () {} );
	} catch ( e ) {}

	function afsluiten() {
		if ( ! id || ! navigator.sendBeacon ) { return; }
		var s = Math.round( ( Date.now() - start ) / 1000 );
		if ( s < 1 ) { return; }
		try {
			navigator.sendBeacon( basis + 'duur', new Blob(
				[ JSON.stringify( { id: id, seconden: s } ) ],
				{ type: 'application/json' }
			) );
		} catch ( e ) {}
	}

	addEventListener( 'visibilitychange', function () {
		if ( document.visibilityState === 'hidden' ) { afsluiten(); }
	} );
	addEventListener( 'pagehide', afsluiten );
} )();
</script>
	<?php
}

/* ============================================================ opruimen */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'dtc_in_opruimen' ) ) {
		wp_schedule_event( time() + 3600, 'daily', 'dtc_in_opruimen' );
	}
} );

add_action( 'dtc_in_opruimen', function () {
	global $wpdb;
	$o = dtc_in_opties();
	$maanden = max( 1, (int) $o['bewaren'] );
	$tabel = dtc_in_tabel();
	$wpdb->query( $wpdb->prepare(
		"DELETE FROM $tabel WHERE dag < DATE_SUB(CURDATE(), INTERVAL %d MONTH)",
		$maanden
	) );
} );

/* ============================================================ cijfers ophalen */

/** de gekozen periode als begin- en einddatum, plus de vorige periode ernaast */
function dtc_in_periode() {
	$keuze = isset( $_GET['periode'] ) ? sanitize_key( $_GET['periode'] ) : '30';
	$nu    = current_time( 'timestamp' );
	$dag   = 86400;

	switch ( $keuze ) {
		case 'vandaag':
			$van = $tot = gmdate( 'Y-m-d', $nu );
			$naam = 'vandaag'; $korrel = 'uur'; break;
		case 'gisteren':
			$van = $tot = gmdate( 'Y-m-d', $nu - $dag );
			$naam = 'gisteren'; $korrel = 'uur'; break;
		case '7':
			$van = gmdate( 'Y-m-d', $nu - 6 * $dag ); $tot = gmdate( 'Y-m-d', $nu );
			$naam = 'laatste 7 dagen'; $korrel = 'dag'; break;
		case '90':
			$van = gmdate( 'Y-m-d', $nu - 89 * $dag ); $tot = gmdate( 'Y-m-d', $nu );
			$naam = 'laatste 90 dagen'; $korrel = 'dag'; break;
		case '365':
			$van = gmdate( 'Y-m-d', $nu - 364 * $dag ); $tot = gmdate( 'Y-m-d', $nu );
			$naam = 'laatste 12 maanden'; $korrel = 'maand'; break;
		case '30':
		default:
			$keuze = '30';
			$van = gmdate( 'Y-m-d', $nu - 29 * $dag ); $tot = gmdate( 'Y-m-d', $nu );
			$naam = 'laatste 30 dagen'; $korrel = 'dag'; break;
	}

	$lengte    = ( strtotime( $tot ) - strtotime( $van ) ) / $dag + 1;
	$vorig_tot = gmdate( 'Y-m-d', strtotime( $van ) - $dag );
	$vorig_van = gmdate( 'Y-m-d', strtotime( $van ) - $lengte * $dag );

	return compact( 'keuze', 'van', 'tot', 'naam', 'korrel', 'vorig_van', 'vorig_tot' );
}

/** de vijf kerncijfers over een periode */
function dtc_in_kerncijfers( $van, $tot ) {
	global $wpdb;
	$t = dtc_in_tabel();

	$rij = $wpdb->get_row( $wpdb->prepare(
		"SELECT COUNT(*) AS weergaven,
		        COUNT(DISTINCT bezoeker) AS bezoekers,
		        COUNT(DISTINCT sessie) AS bezoeken
		 FROM $t WHERE dag BETWEEN %s AND %s",
		$van, $tot
	) );

	$een = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM (
			SELECT sessie FROM $t WHERE dag BETWEEN %s AND %s GROUP BY sessie HAVING COUNT(*) = 1
		) AS enkel",
		$van, $tot
	) );

	$duur = (float) $wpdb->get_var( $wpdb->prepare(
		"SELECT AVG(d) FROM (
			SELECT SUM(seconden) AS d FROM $t WHERE dag BETWEEN %s AND %s GROUP BY sessie
		) AS perbezoek",
		$van, $tot
	) );

	$bezoeken = $rij ? (int) $rij->bezoeken : 0;

	return array(
		'weergaven' => $rij ? (int) $rij->weergaven : 0,
		'bezoekers' => $rij ? (int) $rij->bezoekers : 0,
		'bezoeken'  => $bezoeken,
		'bounce'    => $bezoeken ? round( $een / $bezoeken * 100 ) : 0,
		'duur'      => (int) round( $duur ),
	);
}

/** de reeks voor de grafiek */
function dtc_in_reeks( $van, $tot, $korrel ) {
	global $wpdb;
	$t = dtc_in_tabel();

	if ( 'uur' === $korrel ) {
		$rijen = $wpdb->get_results( $wpdb->prepare(
			"SELECT HOUR(tijd) AS sleutel, COUNT(*) AS weergaven, COUNT(DISTINCT bezoeker) AS bezoekers
			 FROM $t WHERE dag BETWEEN %s AND %s GROUP BY HOUR(tijd)",
			$van, $tot
		), OBJECT_K );
		$uit = array();
		for ( $u = 0; $u < 24; $u++ ) {
			$uit[] = array(
				'label'     => sprintf( '%02d', $u ),
				'volledig'  => sprintf( '%02d:00', $u ),
				'weergaven' => isset( $rijen[ $u ] ) ? (int) $rijen[ $u ]->weergaven : 0,
				'bezoekers' => isset( $rijen[ $u ] ) ? (int) $rijen[ $u ]->bezoekers : 0,
			);
		}
		return $uit;
	}

	if ( 'maand' === $korrel ) {
		$rijen = $wpdb->get_results( $wpdb->prepare(
			"SELECT DATE_FORMAT(dag, '%%Y-%%m') AS sleutel, COUNT(*) AS weergaven, COUNT(DISTINCT bezoeker) AS bezoekers
			 FROM $t WHERE dag BETWEEN %s AND %s GROUP BY sleutel",
			$van, $tot
		), OBJECT_K );
		$uit = array();
		$loop = strtotime( substr( $van, 0, 7 ) . '-01' );
		$eind = strtotime( substr( $tot, 0, 7 ) . '-01' );
		while ( $loop <= $eind ) {
			$s = gmdate( 'Y-m', $loop );
			$uit[] = array(
				'label'     => mb_strtolower( wp_date( 'M', $loop ) ),
				'volledig'  => wp_date( 'F Y', $loop ),
				'weergaven' => isset( $rijen[ $s ] ) ? (int) $rijen[ $s ]->weergaven : 0,
				'bezoekers' => isset( $rijen[ $s ] ) ? (int) $rijen[ $s ]->bezoekers : 0,
			);
			$loop = strtotime( '+1 month', $loop );
		}
		return $uit;
	}

	$rijen = $wpdb->get_results( $wpdb->prepare(
		"SELECT dag AS sleutel, COUNT(*) AS weergaven, COUNT(DISTINCT bezoeker) AS bezoekers
		 FROM $t WHERE dag BETWEEN %s AND %s GROUP BY dag",
		$van, $tot
	), OBJECT_K );

	$uit  = array();
	$loop = strtotime( $van );
	$eind = strtotime( $tot );
	while ( $loop <= $eind ) {
		$s = gmdate( 'Y-m-d', $loop );
		$uit[] = array(
			'label'     => gmdate( 'j', $loop ),
			'volledig'  => wp_date( 'j F Y', $loop ),
			'weergaven' => isset( $rijen[ $s ] ) ? (int) $rijen[ $s ]->weergaven : 0,
			'bezoekers' => isset( $rijen[ $s ] ) ? (int) $rijen[ $s ]->bezoekers : 0,
		);
		$loop += 86400;
	}
	return $uit;
}

/** een top-lijst over een kolom */
function dtc_in_top( $kolom, $van, $tot, $aantal = 12, $waar = '' ) {
	global $wpdb;
	$t = dtc_in_tabel();
	$toegestaan = array( 'pad', 'bron', 'apparaat', 'browser', 'systeem', 'campagne' );
	if ( ! in_array( $kolom, $toegestaan, true ) ) { return array(); }

	return $wpdb->get_results( $wpdb->prepare(
		"SELECT `$kolom` AS naam,
		        COUNT(*) AS weergaven,
		        COUNT(DISTINCT sessie) AS bezoeken,
		        MAX(titel) AS titel,
		        ROUND(AVG(NULLIF(seconden,0))) AS duur
		 FROM $t
		 WHERE dag BETWEEN %s AND %s $waar
		 GROUP BY `$kolom`
		 ORDER BY weergaven DESC
		 LIMIT %d",
		$van, $tot, $aantal
	) );
}

/** bezoekers van de laatste vijf minuten */
function dtc_in_nu() {
	global $wpdb;
	$t = dtc_in_tabel();
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(DISTINCT bezoeker) FROM $t WHERE tijd > DATE_SUB(%s, INTERVAL 5 MINUTE)",
		current_time( 'mysql' )
	) );
}

function dtc_in_tijd( $seconden ) {
	$seconden = (int) $seconden;
	if ( $seconden < 60 ) { return $seconden . 's'; }
	return floor( $seconden / 60 ) . 'm ' . ( $seconden % 60 ) . 's';
}

function dtc_in_verschil( $nu, $toen ) {
	if ( ! $toen ) { return null; }
	return round( ( $nu - $toen ) / $toen * 100 );
}

/* ============================================================ het scherm */

add_action( 'admin_menu', function () {
	if ( defined( 'DTC_TK_SLUG' ) && menu_page_url( DTC_TK_SLUG, false ) ) {
		add_submenu_page( DTC_TK_SLUG, 'Insights', 'Insights', DTC_IN_CAP, DTC_IN_SLUG, 'dtc_in_scherm' );
	} else {
		add_menu_page( 'dtc insights', 'dtc insights', DTC_IN_CAP, DTC_IN_SLUG, 'dtc_in_scherm', 'dashicons-chart-bar', 4 );
	}
}, 11 );

add_action( 'admin_post_dtc_in_opslaan', function () {
	if ( ! current_user_can( DTC_IN_CAP ) ) { wp_die( 'Geen rechten.' ); }
	check_admin_referer( 'dtc_in_opties' );
	$in = wp_unslash( $_POST );

	if ( isset( $in['wissen'] ) ) {
		global $wpdb;
		$wpdb->query( 'TRUNCATE TABLE ' . dtc_in_tabel() );
		$melding = 'gewist';
	} else {
		update_option( DTC_IN_OPTIE, array(
			'aan'     => empty( $in['aan'] ) ? 0 : 1,
			'eigen'   => empty( $in['eigen'] ) ? 0 : 1,
			'bewaren' => max( 1, min( 120, (int) ( $in['bewaren'] ?? 24 ) ) ),
		), false );
		$melding = 'opgeslagen';
	}

	wp_safe_redirect( add_query_arg(
		array( 'page' => DTC_IN_SLUG, 'dtc' => $melding ),
		admin_url( 'admin.php' )
	) );
	exit;
} );

function dtc_in_scherm() {
	if ( ! current_user_can( DTC_IN_CAP ) ) { wp_die( 'Geen rechten.' ); }

	$o  = dtc_in_opties();
	$p  = dtc_in_periode();
	$nu = dtc_in_kerncijfers( $p['van'], $p['tot'] );
	$to = dtc_in_kerncijfers( $p['vorig_van'], $p['vorig_tot'] );
	$reeks = dtc_in_reeks( $p['van'], $p['tot'], $p['korrel'] );

	$perioden = array(
		'vandaag'  => 'vandaag',
		'gisteren' => 'gisteren',
		'7'        => '7 dagen',
		'30'       => '30 dagen',
		'90'       => '90 dagen',
		'365'      => '12 maanden',
	);

	$kaarten = array(
		array( 'paginaweergaven', number_format_i18n( $nu['weergaven'] ), dtc_in_verschil( $nu['weergaven'], $to['weergaven'] ), '' ),
		array( 'bezoekers',       number_format_i18n( $nu['bezoekers'] ), dtc_in_verschil( $nu['bezoekers'], $to['bezoekers'] ), '' ),
		array( 'bezoeken',        number_format_i18n( $nu['bezoeken'] ),  dtc_in_verschil( $nu['bezoeken'], $to['bezoeken'] ), '' ),
		array( 'gem. bezoekduur', dtc_in_tijd( $nu['duur'] ),             dtc_in_verschil( $nu['duur'], $to['duur'] ), '' ),
		array( 'één pagina',      $nu['bounce'] . '%',                    dtc_in_verschil( $nu['bounce'], $to['bounce'] ), 'omgekeerd' ),
	);
	?>
	<div class="wrap dtc-tk dtc-in">
		<?php
		if ( function_exists( 'dtc_tk_titelbalk' ) ) {
			dtc_tk_titelbalk( 'De bezoekcijfers van de site. Alles wordt op deze server bewaard, zonder cookies en zonder dat er gegevens naar een andere partij gaan.' );
		} else {
			echo '<h1>dtc insights</h1>';
		}
		?>

		<?php if ( isset( $_GET['dtc'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php echo 'gewist' === $_GET['dtc'] ? 'Alle gemeten gegevens zijn gewist.' : 'De instellingen zijn opgeslagen.'; ?>
			</p></div>
		<?php endif; ?>

		<?php if ( ! $o['aan'] ) : ?>
			<div class="notice notice-warning inline"><p>Het meten staat uit. Zet het onderaan bij <strong>instellingen</strong> weer aan.</p></div>
		<?php endif; ?>

		<div class="dtc-in-balk">
			<nav class="dtc-in-perioden">
				<?php foreach ( $perioden as $sleutel => $label ) :
					$actief = $sleutel === $p['keuze'] ? ' aan' : '';
					$url    = add_query_arg( array( 'page' => DTC_IN_SLUG, 'periode' => $sleutel ), admin_url( 'admin.php' ) ); ?>
					<a class="dtc-in-periode<?php echo $actief; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
			<span class="dtc-in-live"><span class="stip"></span><?php echo (int) dtc_in_nu(); ?> nu op de site</span>
		</div>

		<div class="dtc-in-kaarten">
			<?php foreach ( $kaarten as $k ) :
				list( $label, $waarde, $verschil, $soort ) = $k; ?>
				<div class="dtc-in-kaart">
					<span class="label"><?php echo esc_html( $label ); ?></span>
					<span class="waarde"><?php echo esc_html( $waarde ); ?></span>
					<?php if ( null !== $verschil ) :
						$goed = ( 'omgekeerd' === $soort ) ? $verschil <= 0 : $verschil >= 0;
						$klasse = 0 === (int) $verschil ? 'gelijk' : ( $goed ? 'op' : 'af' ); ?>
						<span class="verschil <?php echo esc_attr( $klasse ); ?>">
							<?php echo esc_html( ( $verschil > 0 ? '+' : '' ) . $verschil . '%' ); ?>
							<span class="tov">t.o.v. vorige periode</span>
						</span>
					<?php else : ?>
						<span class="verschil gelijk"><span class="tov">geen vergelijking</span></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="dtc-tk-groep dtc-in-grafiek">
			<h2><?php echo esc_html( 'paginaweergaven — ' . $p['naam'] ); ?></h2>
			<?php dtc_in_grafiek( $reeks, $p['korrel'] ); ?>
		</div>

		<div class="dtc-in-kolommen">
			<?php
			dtc_in_lijst( 'pagina&rsquo;s', dtc_in_top( 'pad', $p['van'], $p['tot'], 12 ), 'pad' );
			dtc_in_lijst( 'waar komen ze vandaan', dtc_in_top( 'bron', $p['van'], $p['tot'], 12, "AND bron <> 'intern'" ), 'bron' );
			dtc_in_lijst( 'binnenkomers', dtc_in_top( 'pad', $p['van'], $p['tot'], 8, 'AND eerste = 1' ), 'pad' );
			dtc_in_lijst( 'apparaat', dtc_in_top( 'apparaat', $p['van'], $p['tot'], 5 ), 'kaal' );
			dtc_in_lijst( 'browser', dtc_in_top( 'browser', $p['van'], $p['tot'], 6 ), 'kaal' );
			$campagnes = dtc_in_top( 'campagne', $p['van'], $p['tot'], 8, "AND campagne <> ''" );
			if ( $campagnes ) { dtc_in_lijst( 'campagnes', $campagnes, 'kaal' ); }
			?>
		</div>

		<details class="dtc-in-instellingen">
			<summary>instellingen</summary>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="dtc_in_opslaan">
				<?php wp_nonce_field( 'dtc_in_opties' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">Meten</th>
						<td><label><input type="checkbox" name="aan" value="1" <?php checked( $o['aan'], 1 ); ?>> aan</label>
						<p class="description">Uit = er wordt niets meer bijgehouden. De cijfers van hiervoor blijven staan.</p></td>
					</tr>
					<tr>
						<th scope="row">Eigen bezoeken</th>
						<td><label><input type="checkbox" name="eigen" value="1" <?php checked( $o['eigen'], 1 ); ?>> meetellen</label>
						<p class="description">Standaard uit: wie ingelogd is en de site mag bewerken, telt niet mee.</p></td>
					</tr>
					<tr>
						<th scope="row"><label for="dtc-in-bewaren">Bewaren</label></th>
						<td><input type="number" id="dtc-in-bewaren" name="bewaren" value="<?php echo esc_attr( $o['bewaren'] ); ?>" min="1" max="120" step="1"> maanden
						<p class="description">Oudere regels worden elke nacht opgeruimd.</p></td>
					</tr>
				</table>
				<p class="submit">
					<?php submit_button( 'Opslaan', 'primary', 'submit', false ); ?>
					<button type="submit" name="wissen" value="1" class="button" onclick="return confirm('Alle gemeten gegevens wissen? Dit kan niet ongedaan gemaakt worden.')">Alle gegevens wissen</button>
				</p>
			</form>
			<p class="description">Er wordt geen cookie geplaatst en geen IP-adres bewaard. Een bezoeker wordt herkend aan een hash van het IP-adres en de browser met een zout dat elke dag wisselt, dus die hash is niet terug te rekenen en niet over dagen heen te volgen.</p>
		</details>
	</div>

	<?php if ( function_exists( 'dtc_tk_stijl' ) ) { dtc_tk_stijl(); } ?>
	<?php dtc_in_stijl(); ?>
	<?php
}

/** een staafgrafiek als gewone svg, zodat er geen bibliotheek nodig is */
function dtc_in_grafiek( $reeks, $korrel = 'dag' ) {
	$aantal = count( $reeks );
	if ( ! $aantal ) { echo '<p>Nog niets gemeten in deze periode.</p>'; return; }

	$hoogste = 0;
	foreach ( $reeks as $r ) { $hoogste = max( $hoogste, $r['weergaven'] ); }
	if ( ! $hoogste ) { echo '<p>Nog niets gemeten in deze periode.</p>'; return; }

	$breed = 1000;
	$hoog  = 220;
	$vak   = $breed / $aantal;
	$staaf = max( 3, min( 34, $vak * 0.62 ) );
	$stap  = $aantal > 24 ? (int) ceil( $aantal / 12 ) : ( $aantal > 12 ? 2 : 1 );
	?>
	<svg class="dtc-in-svg" viewBox="0 0 <?php echo (int) $breed; ?> <?php echo (int) ( $hoog + 26 ); ?>" role="img" aria-label="paginaweergaven per periode">
		<?php for ( $i = 1; $i <= 3; $i++ ) :
			$y = $hoog - ( $hoog * $i / 4 ); ?>
			<line x1="0" x2="<?php echo (int) $breed; ?>" y1="<?php echo esc_attr( $y ); ?>" y2="<?php echo esc_attr( $y ); ?>" stroke="#f0ebe2" stroke-width="1" />
		<?php endfor; ?>
		<line x1="0" x2="<?php echo (int) $breed; ?>" y1="<?php echo (int) $hoog; ?>" y2="<?php echo (int) $hoog; ?>" stroke="#e8e0d4" stroke-width="1" />
		<?php foreach ( $reeks as $i => $r ) :
			$h = $r['weergaven'] ? max( 2, $r['weergaven'] / $hoogste * ( $hoog - 12 ) ) : 0;
			$x = $i * $vak + ( $vak - $staaf ) / 2; ?>
			<?php if ( $h ) : ?>
			<rect x="<?php echo esc_attr( $x ); ?>" y="<?php echo esc_attr( $hoog - $h ); ?>"
			      width="<?php echo esc_attr( $staaf ); ?>" height="<?php echo esc_attr( $h ); ?>"
			      rx="3" fill="#2d2824">
				<title><?php echo esc_html( $r['volledig'] . ': ' . $r['weergaven'] . ' weergaven, ' . $r['bezoekers'] . ' bezoekers' ); ?></title>
			</rect>
			<?php endif; ?>
			<?php if ( 0 === $i % $stap ) : ?>
			<text x="<?php echo esc_attr( $i * $vak + $vak / 2 ); ?>" y="<?php echo (int) ( $hoog + 18 ); ?>"
			      text-anchor="middle" font-size="12" fill="#a99a86"><?php echo esc_html( $r['label'] ); ?></text>
			<?php endif; ?>
		<?php endforeach; ?>
	</svg>
	<p class="dtc-in-hoogste">drukste <?php echo esc_html( $korrel ); ?>: <?php echo esc_html( number_format_i18n( $hoogste ) ); ?> weergaven</p>
	<?php
}

/** een lijstje met een balkje achter elke regel */
function dtc_in_lijst( $titel, $rijen, $soort ) {
	$hoogste = 0;
	foreach ( $rijen as $r ) { $hoogste = max( $hoogste, (int) $r->weergaven ); }
	?>
	<div class="dtc-tk-groep dtc-in-lijst">
		<h2><?php echo wp_kses_post( $titel ); ?></h2>
		<?php if ( ! $rijen ) : ?>
			<p class="leeg">nog niets gemeten</p>
		<?php else : ?>
			<table>
				<?php foreach ( $rijen as $r ) :
					$deel = $hoogste ? max( 2, round( $r->weergaven / $hoogste * 100 ) ) : 0; ?>
					<tr>
						<td class="naam">
							<span class="balk" style="width:<?php echo esc_attr( $deel ); ?>%"></span>
							<?php if ( 'pad' === $soort ) : ?>
								<a href="<?php echo esc_url( home_url( $r->naam ) ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( $r->titel ); ?>"><?php echo esc_html( $r->naam ); ?></a>
							<?php elseif ( 'bron' === $soort ) : ?>
								<span><?php echo esc_html( $r->naam ); ?></span>
								<span class="soort"><?php echo esc_html( dtc_in_soort( $r->naam ) ); ?></span>
							<?php else : ?>
								<span><?php echo esc_html( $r->naam ? $r->naam : 'onbekend' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="getal"><?php echo esc_html( number_format_i18n( $r->weergaven ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

function dtc_in_stijl() {
	?>
	<style>
		.dtc-in-balk{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin:4px 0 20px}
		.dtc-in-perioden{display:flex;flex-wrap:wrap;gap:8px}
		.dtc-in-periode{
			display:inline-block;padding:7px 16px;background:#fff;border:1px solid #e0d6c6;border-radius:999px;
			text-decoration:none;font-size:13px;font-weight:500;color:#6b6258;
		}
		.dtc-in-periode:hover{border-color:#c0ab94;color:#2d2824}
		.dtc-in-periode.aan{background:#2d2824;border-color:#2d2824;color:#fff}
		.dtc-in-live{margin-left:auto;display:inline-flex;align-items:center;gap:8px;font-size:13px;color:#8a7257}
		.dtc-in-live .stip{width:8px;height:8px;border-radius:50%;background:#7d9068;box-shadow:0 0 0 4px rgba(125,144,104,.18)}

		.dtc-in-kaarten{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin:0 0 18px}
		.dtc-in-kaart{background:#fff;border:1px solid #e8e0d4;border-radius:16px;padding:18px 20px}
		.dtc-in-kaart .label{display:block;font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;color:#a99a86}
		.dtc-in-kaart .waarde{display:block;margin:8px 0 6px;font-size:34px;font-weight:500;line-height:1;color:#2d2824;letter-spacing:-.02em}
		.dtc-in-kaart .verschil{display:block;font-size:12px;color:#a99a86}
		.dtc-in-kaart .verschil.op{color:#5f7a4e}
		.dtc-in-kaart .verschil.af{color:#b4492f}
		.dtc-in-kaart .verschil .tov{color:#a99a86;margin-left:4px}

		.dtc-in-grafiek h2{margin-bottom:18px}
		.dtc-in-svg{width:100%;height:auto;max-height:300px;display:block}
		.dtc-in-svg rect{transition:fill .2s}
		.dtc-in-svg rect:hover{fill:#c0ab94}
		.dtc-in-hoogste{margin:10px 0 14px;font-size:12px;color:#a99a86}

		.dtc-in-kolommen{display:grid;grid-template-columns:repeat(auto-fit,minmax(330px,1fr));gap:18px;align-items:start}
		.dtc-in-kolommen .dtc-tk-groep{margin:0}
		.dtc-in-lijst table{width:100%;border-collapse:collapse;margin-bottom:14px}
		.dtc-in-lijst td{padding:7px 0;border-bottom:1px solid #f4f1ec;font-size:14px;vertical-align:middle}
		.dtc-in-lijst tr:last-child td{border-bottom:0}
		.dtc-in-lijst .naam{position:relative;padding-left:10px;max-width:0;width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
		.dtc-in-lijst .naam a{text-decoration:none}
		.dtc-in-lijst .balk{position:absolute;left:0;top:3px;bottom:3px;background:#f4f1ec;border-radius:5px;z-index:0}
		.dtc-in-lijst .naam a,.dtc-in-lijst .naam span{position:relative;z-index:1}
		.dtc-in-lijst .soort{margin-left:8px;font-size:11px;color:#a99a86}
		.dtc-in-lijst .getal{text-align:right;white-space:nowrap;padding-left:14px;font-weight:500;color:#2d2824}
		.dtc-in-lijst .leeg{color:#a99a86;font-size:13px}

		.dtc-in-instellingen{margin:22px 0 40px}
		.dtc-in-instellingen > summary{
			display:inline-block;padding:7px 18px;background:#fff;border:1px solid #e0d6c6;border-radius:999px;
			font-size:13px;font-weight:500;color:#6b6258;cursor:pointer;list-style:none;
		}
		.dtc-in-instellingen > summary::-webkit-details-marker{display:none}
		.dtc-in-instellingen > summary:hover{border-color:#8a7257;color:#2d2824}
		.dtc-in-instellingen[open] > summary{background:#2d2824;border-color:#2d2824;color:#fff}
		.dtc-in-instellingen > .description{max-width:760px;margin-top:10px}
	</style>
	<?php
}
