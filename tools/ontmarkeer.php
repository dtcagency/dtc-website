<?php
/** Haalt de "hier bewerkt"-markering weg, nadat die wijziging is overgenomen. */
foreach ( $args as $id ) {
	$id = (int) $id;
	delete_post_meta( $id, '_dtc_tk_bewerkt' );
	delete_post_meta( $id, '_dtc_tk_bewerkt_door' );
	echo "  markering weg bij $id\n";
}
