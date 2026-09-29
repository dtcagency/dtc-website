<?php
/**
 * Plugin Name: DTC Redirects
 * Description: Stuurt oude adressen door naar de huidige, zodat links en zoekresultaten blijven werken.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pagina's die van adres zijn veranderd. Zonder deze omleiding levert het
 * oude adres een 404 op, en dat staat nog in links, bookmarks, zoekresultaten
 * en in advertenties die eerder zijn uitgezet.
 *
 * Links: het oude pad zonder schuine strepen, rechts het nieuwe.
 */
function dtc_redirects_map() {
	return array(
		'services_new'          => '/services/',
		'hubspotnieuwe-opzet'   => '/hubspot/',
		'hubspot-implementation' => '/hubspot/implementation/',
		'marketing-hub'          => '/hubspot/marketing-hub/',
		'sales-hub'              => '/hubspot/sales-hub/',
		'service-hub'            => '/hubspot/service-hub/',
		'operations-hub'         => '/hubspot/operations-hub/',
		'training-consultancy'   => '/hubspot/training-consultancy/',
		'lets-talk-_-new'        => '/contact/',
	);
}

/**
 * Vroeg in de afhandeling, zodat WordPress niet eerst een 404-pagina opbouwt.
 * Een 301 vertelt Google dat de verhuizing blijvend is.
 */
add_action( 'template_redirect', function () {
	$pad = trim( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
	if ( '' === $pad ) {
		return;
	}
	$map = dtc_redirects_map();
	if ( ! isset( $map[ $pad ] ) ) {
		return;
	}
	// de zoekopdracht in de url meenemen, anders raken campagneparameters kwijt
	$query = $_SERVER['QUERY_STRING'] ?? '';
	$doel  = home_url( $map[ $pad ] ) . ( $query ? '?' . $query : '' );
	wp_safe_redirect( $doel, 301 );
	exit;
}, 1 );
