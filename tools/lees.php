<?php
/** Geeft de inhoud van één post terug, met het eigen domein vervangen door DTC_SITE. */
$id = (int) $args[0];
$eigen = isset( $args[1] ) ? rtrim( $args[1], '/' ) : 'http://localhost:8080';
$p = get_post( $id );
if ( ! $p ) { echo "\nGEENPOST\n"; return; }
echo "\n" . base64_encode( str_replace( $eigen, 'DTC_SITE', $p->post_content ) ) . "\n";
