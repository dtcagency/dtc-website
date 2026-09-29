<?php
/** Geeft de bewerkbare teksten van een post terug (zelfde uitlezing als het DTC-teksten-scherm). */
$id = (int) $args[0];
$p = get_post( $id );
if ( ! $p ) { echo "\nGEENPOST\n"; return; }
if ( ! function_exists( 'dtc_tk_teksten' ) ) { echo "\nGEENPLUGIN\n"; return; }
$uit = array();
foreach ( dtc_tk_teksten( $p->post_content ) as $t ) {
	$uit[] = array( 'o' => (int) $t['offset'], 't' => $t['tekst'] );
}
echo "\n" . base64_encode( wp_json_encode( $uit ) ) . "\n";
