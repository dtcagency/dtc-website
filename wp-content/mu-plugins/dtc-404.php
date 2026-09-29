<?php
/**
 * Plugin Name: DTC 404
 * Description: Eigen 404-pagina in de huisstijl, met bruikbare routes terug. Vervangt Divi's standaardtekst.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DTC_404_DIR = __DIR__ . '/dtc-404';

add_filter( 'template_include', function ( $template ) {
	if ( is_404() ) {
		$file = DTC_404_DIR . '/404.php';
		if ( file_exists( $file ) ) {
			return $file;
		}
	}
	return $template;
}, 99 );

/** Divi laat hier "404 Not Found" zien; dat kan vriendelijker. */
add_filter( 'pre_get_document_title', function ( $title ) {
	if ( is_404() ) {
		return 'Page not found | dtc /always on';
	}
	return $title;
}, 100 );

/**
 * Een foutpagina hoort niet in de zoekresultaten. WordPress zet hier niets,
 * en de site heeft geen SEO-plugin die dat regelt.
 */
add_action( 'wp_head', function () {
	if ( is_404() ) {
		echo '<meta name="robots" content="noindex, follow" />' . "\n";
	}
}, 1 );

/**
 * De routes die we aanbieden. Per route staan er meerdere mogelijke slugs:
 * de eerste die echt bestaat wordt gebruikt. Zo blijft de pagina heel als een
 * slug ooit verandert - een 404 die naar een 404 wijst is erger dan geen 404.
 */
function dtc_404_routes() {
	$routes = array(
		array(
			'naam'  => 'our work',
			'tekst' => 'Campaigns and cases for brands in automotive, mobility and beyond.',
			'paden' => array( 'cases_new', 'work', 'work_new' ),
		),
		array(
			'naam'  => 'services',
			'tekst' => 'Strategy, creation, growth, media and everything HubSpot.',
			'paden' => array( 'services', 'services_new', 'services-overzicht-2' ),
		),
		array(
			'naam'  => 'insights',
			'tekst' => 'The campaigns we are proud of, and the trends worth your time.',
			'paden' => array( 'insights' ),
		),
		array(
			'naam'  => 'the hub',
			'tekst' => 'What we keep learning, written down so you can use it.',
			'paden' => array( '@hub' ),
		),
		array(
			'naam'  => 'let&rsquo;s talk',
			'tekst' => 'Book an intake, or just have a coffee. No obligation.',
			'paden' => array( 'contact', 'lets-talk-_-new' ),
		),
	);

	$uit = array();
	foreach ( $routes as $r ) {
		foreach ( $r['paden'] as $pad ) {
			/* een @ ervoor betekent: geen pagina maar een archief, dat bestaat altijd */
			if ( '@' === substr( $pad, 0, 1 ) ) {
				$uit[] = array( $r['naam'], $r['tekst'], '/' . substr( $pad, 1 ) . '/' );
				break;
			}
			if ( get_page_by_path( $pad ) ) {
				$uit[] = array( $r['naam'], $r['tekst'], '/' . $pad . '/' );
				break;
			}
		}
	}
	return $uit;
}

/**
 * Zoekt de pagina die het meest lijkt op wat er gevraagd werd. Bij een typefout
 * of een oude link scheelt dat de bezoeker een zoektocht.
 *
 * @return array|null array( titel, url ) of null als niets genoeg lijkt.
 */
function dtc_404_bedoelde_je() {
	$gevraagd = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( '' === $gevraagd ) { return null; }

	/* alleen het laatste stuk van het pad; /hubspot/sales-hup/ -> sales-hup */
	$delen    = explode( '/', $gevraagd );
	$gevraagd = end( $delen );
	if ( strlen( $gevraagd ) < 3 ) { return null; }

	$paginas = get_posts( array(
		'post_type'      => array( 'page', 'hub' ),
		'post_status'    => 'publish',
		'posts_per_page' => 200,
	) );

	$beste = null;
	$score = 0;
	foreach ( $paginas as $p ) {
		if ( '' === $p->post_name ) { continue; }
		similar_text( $gevraagd, $p->post_name, $procent );
		if ( $procent > $score ) {
			$score = $procent;
			$beste = $p;
		}
	}

	/* onder de 70% lijkt het nergens op en is een suggestie alleen verwarrend */
	if ( ! $beste || $score < 70 ) { return null; }
	return array( $beste->post_title, get_permalink( $beste ) );
}
