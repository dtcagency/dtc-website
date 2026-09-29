<?php
/** Geeft de inhoud van een post terug zoals hij in de database staat. */
$p = get_post( (int) $args[0] );
if ( ! $p ) { echo "\nGEENPOST\n"; return; }
echo "\n" . base64_encode( $p->post_content ) . "\n";
