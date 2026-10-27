<?php
/**
 * Plugin Name: DTC Performance
 * Description: Lazy loading voor afbeeldingen die Divi en DiviFlash rechtstreeks uitvoeren, plus wat kleine opschoning in de head.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hoeveel afbeeldingen bovenaan we overslaan. Die staan direct in beeld;
 * die lazy maken vertraagt juist wat de bezoeker als eerste ziet.
 */
const DTC_PERF_EAGER = 2;

/**
 * Divi-modules zetten zelf alt="" op hun afbeeldingen en negeren daarbij de
 * alt-tekst uit de mediabibliotheek. Daardoor stonden klantlogo's en
 * casebeelden zonder alt op de site.
 *
 * Deze functie bouwt één keer per request een lijst van bestandsnamen met
 * een ingevulde alt-tekst. Alleen die krijgen hem alsnog; decoratieve
 * beelden (pijltjes, sluitkruisjes) hebben geen alt-tekst in de bibliotheek
 * en houden dus terecht een lege alt.
 *
 * @return array<string,string> bestandsnaam zonder maat-achtervoegsel => alt
 */
function dtc_perf_alt_map() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	global $wpdb;
	$map = array();
	$rows = $wpdb->get_results(
		"SELECT pm.post_id, pm.meta_value AS alt, f.meta_value AS file
		 FROM {$wpdb->postmeta} pm
		 JOIN {$wpdb->postmeta} f ON f.post_id = pm.post_id AND f.meta_key = '_wp_attached_file'
		 WHERE pm.meta_key = '_wp_attachment_image_alt' AND pm.meta_value <> ''"
	);
	foreach ( (array) $rows as $r ) {
		$base = pathinfo( $r->file, PATHINFO_FILENAME );
		$map[ $base ] = $r->alt;
	}
	return $map;
}

/** Haalt "-1080x720" van een bestandsnaam af zodat elk formaat matcht. */
function dtc_perf_basename( $src ) {
	$name = pathinfo( wp_parse_url( $src, PHP_URL_PATH ) ?? '', PATHINFO_FILENAME );
	return preg_replace( '/-\d+x\d+$/', '', $name );
}

/**
 * WordPress voegt loading="lazy" toe via wp_filter_content_tags(), maar dat
 * raakt alleen afbeeldingen die als HTML in de content staan. Divi en
 * DiviFlash bouwen hun <img> in PHP op tijdens het uitvoeren van de
 * shortcodes, dus die glippen erlangs - op de homepage was dat 33 van de 33.
 *
 * Daarom hier, ná alle shortcode-verwerking, alsnog over de uitvoer heen.
 */
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || is_feed() || empty( $content ) ) {
		return $content;
	}
	if ( false === stripos( $content, '<img' ) ) {
		return $content;
	}

	$seen = 0;

	return preg_replace_callback( '#<img\b[^>]*>#i', function ( $m ) use ( &$seen ) {
		$tag = $m[0];
		$seen++;

		// Lege alt aanvullen als deze afbeelding er een heeft in de bibliotheek.
		if ( preg_match( '/\salt\s*=\s*(["\'])\s*\1/i', $tag )
			&& preg_match( '/\ssrc\s*=\s*["\']([^"\']+)["\']/i', $tag, $s ) ) {
			$map  = dtc_perf_alt_map();
			$base = dtc_perf_basename( $s[1] );
			if ( isset( $map[ $base ] ) ) {
				$tag = preg_replace(
					'/\salt\s*=\s*(["\'])\s*\1/i',
					' alt="' . esc_attr( $map[ $base ] ) . '"',
					$tag,
					1
				);
			}
		}

		// Al ingesteld? Dan met rust laten - ook als dat "eager" is.
		if ( preg_match( '/\sloading\s*=/i', $tag ) ) {
			return $tag;
		}
		// Een data-URI lazy maken heeft geen zin.
		if ( preg_match( '/\ssrc\s*=\s*["\']data:/i', $tag ) ) {
			return $tag;
		}

		if ( $seen <= DTC_PERF_EAGER ) {
			// De eerste beelden zijn meestal het grootste element boven de
			// vouw; die juist met voorrang laden.
			$add = ' loading="eager" fetchpriority="high" decoding="async"';
		} else {
			$add = ' loading="lazy" decoding="async"';
		}

		// Alleen het afsluitende teken weghalen; rtrim met '/>' zou ook in een
		// attribuutwaarde kunnen snijden die op > eindigt.
		$self  = (bool) preg_match( '#/>\s*$#', $tag );
		$open  = preg_replace( '#\s*/?>\s*$#', '', $tag );
		return $open . $add . ( $self ? ' />' : '>' );
	}, $content );
}, 999 );

/**
 * WordPress laadt op elke pagina een script en stylesheet voor emoji, ook
 * als er geen emoji op staat. Divi's eigen instelling dekt alleen de
 * front-end van het thema.
 */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
} );

/**
 * De oEmbed-ontdekkingslinks en de RSD/wlwmanifest-tags worden door deze
 * site niet gebruikt en staan op elke pagina in de head.
 */
add_action( 'init', function () {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
} );

/**
 * Divi laadt standaard Open Sans van Google mee: tien gewichten en cursieven,
 * samen ruim honderd fontfaces. De site gebruikt uitsluitend Arboria, dus dat
 * bestand doet niets behalve het eerste scherm vertragen — het is een
 * render-blokkerend verzoek naar een andere server.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_dequeue_style( 'et-divi-open-sans' );
	wp_deregister_style( 'et-divi-open-sans' );
}, 100 );

/**
 * De voorverbindingen naar Google Fonts hebben daarmee ook geen doel meer.
 */
add_filter( 'wp_resource_hints', function ( $hints, $relatie ) {
	if ( 'dns-prefetch' !== $relatie && 'preconnect' !== $relatie ) { return $hints; }
	return array_values( array_filter( $hints, function ( $h ) {
		$url = is_array( $h ) ? ( $h['href'] ?? '' ) : $h;
		return false === strpos( (string) $url, 'fonts.googleapis.com' )
			&& false === strpos( (string) $url, 'fonts.gstatic.com' );
	} ) );
}, 10, 2 );

/**
 * Divi levert zijn icoonfont (de pijltjes in de knoppen) alleen als eot, woff,
 * ttf en svg. Het woff-bestand is 90 kB en wordt op elke pagina opgehaald.
 * Dezelfde font als woff2 is 29 kB. Het bestand staat naast de andere in de
 * themamap; na een Divi-update moet het opnieuw gemaakt worden met:
 *   python3 -c "from fontTools.ttLib import TTFont; f=TTFont('modules.ttf'); f.flavor='woff2'; f.save('modules.woff2')"
 */
add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	$map = get_template_directory_uri() . '/core/admin/fonts/modules/all/';
	echo '<style id="dtc-icoonfont">@font-face{font-family:ETmodules;font-display:block;'
		. 'src:url(' . esc_url( $map . 'modules.woff2' ) . ') format("woff2"),'
		. 'url(' . esc_url( $map . 'modules.woff' ) . ') format("woff");'
		. 'font-weight:400;font-style:normal}</style>';
}, 98 );

/**
 * Arboria, één keer voor de hele site. Dezelfde vier @font-face-blokken
 * (300, 400, 500 en 700) stonden 125 keer in de pagina-inhoud: elke
 * HubSpot-pagina declareerde ze zestien keer, andere pagina's drie tot
 * zeven keer. Ze staan nu hier, en de twee gewichten die bovenaan elke
 * pagina nodig zijn (Book voor de tekst, Medium voor de koppen) worden
 * vooraf opgehaald, zodat de tekst niet eerst in een ander lettertype
 * verschijnt en dan verspringt.
 */
add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	$map = content_url( '/uploads/et-fonts/' );
	foreach ( array( 'Arboria-Book', 'Arboria-Medium' ) as $f ) {
		echo '<link rel="preload" href="' . esc_url( $map . $f . '.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
	$gewichten = array( 300 => 'Light', 400 => 'Book', 500 => 'Medium', 700 => 'Bold' );
	echo '<style id="dtc-arboria">';
	foreach ( $gewichten as $gewicht => $naam ) {
		echo "@font-face{font-family:'Arboria';src:url('" . esc_url( $map . 'Arboria-' . $naam . '.woff2' ) . "') format('woff2');font-weight:" . $gewicht . ';font-display:swap}';
	}
	echo '</style>' . "\n";
}, 2 );
