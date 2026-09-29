<?php
/** Schrijft inhoud terug zoals aangeleverd, zonder domein te vervangen. */
global $wpdb;
$map = rtrim( $args[0], '/' );
$goed = 0;
foreach ( glob( $map . '/*.b64' ) as $bestand ) {
	$id = (int) basename( $bestand, '.b64' );
	$inhoud = base64_decode( file_get_contents( $bestand ) );
	$post = get_post( $id );
	if ( ! $post || '' === $inhoud ) { echo "  $id overgeslagen\n"; continue; }
	if ( $post->post_content === $inhoud ) { echo "  $id was al gelijk\n"; continue; }
	wp_save_post_revision( $id );
	$wpdb->update( $wpdb->posts, array( 'post_content' => $inhoud, 'post_modified' => current_time( 'mysql' ), 'post_modified_gmt' => current_time( 'mysql', 1 ) ), array( 'ID' => $id ) );
	clean_post_cache( $id );
	$cache = WP_CONTENT_DIR . '/et-cache/' . $id;
	if ( is_dir( $cache ) ) { foreach ( (array) glob( $cache . '/*' ) as $f ) { @unlink( $f ); } @rmdir( $cache ); }
	printf( "  %-8s samengevoegd (%d bytes)\n", $id, strlen( $inhoud ) );
	$goed++;
}
echo "\n  $goed pagina's bijgewerkt\n";
