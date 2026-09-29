<?php
/**
 * Leest de inhoud van alle pagina's, blokken en berichten uit en geeft per stuk
 * een vingerafdruk terug. Draait zowel tegen de lokale als tegen de Railway-database.
 * Gebruik: wp eval-file inventaris.php [<domein-van-deze-installatie>]
 */
global $wpdb;

$eigen = isset( $args[0] ) ? rtrim( $args[0], '/' ) : 'http://localhost:8080';
$neutraal = 'DTC_SITE';

$rijen = $wpdb->get_results(
	"SELECT ID, post_title, post_type, post_name, post_content, post_modified
	 FROM {$wpdb->posts}
	 WHERE post_status = 'publish'
	   AND post_type IN ('page','post','et_pb_layout','et_footer_layout','et_header_layout')
	 ORDER BY ID"
);

$uit = array();
foreach ( $rijen as $r ) {
	$kaal = str_replace( $eigen, $neutraal, $r->post_content );
	$uit[] = array(
		'id'       => (int) $r->ID,
		'titel'    => $r->post_title,
		'type'     => $r->post_type,
		'slug'     => $r->post_name,
		'md5'      => md5( $kaal ),
		'lengte'   => strlen( $kaal ),
		'gewijzigd'=> $r->post_modified,
		'via_plugin' => get_post_meta( $r->ID, '_dtc_tk_bewerkt', true ),
		'door'       => get_post_meta( $r->ID, '_dtc_tk_bewerkt_door', true ),
	);
}
echo "\n" . base64_encode( wp_json_encode( array( 'site' => $eigen, 'items' => $uit ) ) ) . "\n";
