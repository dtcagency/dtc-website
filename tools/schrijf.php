<?php
/**
 * Schrijft inhoud terug. Verwacht een map met bestanden <id>.b64 waarin de inhoud
 * staat met DTC_SITE als domeinplaatshouder.
 * Gebruik: wp eval-file schrijf.php <map> <domein-van-deze-installatie>
 */
global $wpdb;
$map   = rtrim( $args[0], '/' );
$eigen = rtrim( $args[1], '/' );

$goed = 0; $fout = 0;
foreach ( glob( $map . '/*.b64' ) as $bestand ) {
	$id = (int) basename( $bestand, '.b64' );
	$inhoud = base64_decode( file_get_contents( $bestand ) );
	if ( false === $inhoud || '' === $inhoud ) { echo "  $id lege inhoud, overgeslagen\n"; $fout++; continue; }
	$inhoud = str_replace( 'DTC_SITE', $eigen, $inhoud );

	$post = get_post( $id );
	if ( ! $post ) { echo "  $id bestaat hier niet\n"; $fout++; continue; }
	if ( $post->post_content === $inhoud ) { echo "  $id was al gelijk\n"; continue; }

	wp_save_post_revision( $id );
	$wpdb->update(
		$wpdb->posts,
		array( 'post_content' => $inhoud, 'post_modified' => current_time( 'mysql' ), 'post_modified_gmt' => current_time( 'mysql', 1 ) ),
		array( 'ID' => $id )
	);
	clean_post_cache( $id );
	delete_post_meta( $id, '_dtc_tk_bewerkt' );
	delete_post_meta( $id, '_dtc_tk_bewerkt_door' );

	$na = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $id ) );
	if ( md5( $na ) === md5( $inhoud ) ) { printf( "  %-8s bijgewerkt (%d bytes)\n", $id, strlen( $inhoud ) ); $goed++; }
	else { echo "  $id MISLUKT\n"; $fout++; }

	$cache = WP_CONTENT_DIR . '/et-cache/' . $id;
	if ( is_dir( $cache ) ) { foreach ( (array) glob( $cache . '/*' ) as $f ) { @unlink( $f ); } @rmdir( $cache ); }
}
echo "\n  $goed bijgewerkt, $fout mislukt\n";
