<?php
/**
 * Plugin Name: DTC ruimte
 * Description: Brengt op de telefoon de staande witruimte terug. Raakt alleen de standaardmaten van het ontwerp (40px tussen tekstblokken, 70px onder een rij, 30px onder een kolom), zodat afwijkende, met opzet gekozen maten blijven staan en het ritme overal gelijk blijft. Op tablet en desktop verandert er niets.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_footer', 'dtc_ruimte_script', 30 );
function dtc_ruimte_script() {
	if ( is_admin() ) { return; }
	?>
<script id="dtc-ruimte">
( function () {
	var telefoon = window.matchMedia( '(max-width: 767px)' );

	/* De site werkt met een vast ritme. Op de telefoon vallen die maten te ruim
	   uit, dus schalen we ze terug. Alles wat een andere maat heeft is bewust
	   zo gezet en blijft staan. */
	var regels = [
		{ kies: '.et_pb_text',    eigenschap: 'margin-bottom',  van: 40, naar: 24 },
		{ kies: '.et_pb_text',    eigenschap: 'padding-bottom', van: 40, naar: 24 },
		{ kies: '.et_pb_text',    eigenschap: 'padding-top',    van: 40, naar: 24 },
		{ kies: '.et_pb_row',     eigenschap: 'padding-bottom', van: 70, naar: 45 },
		{ kies: '.et_pb_row',     eigenschap: 'padding-top',    van: 70, naar: 45 },
		{ kies: '.et_pb_column',  eigenschap: 'margin-bottom',  van: 30, naar: 20 },
		{ kies: '.et_pb_module',  eigenschap: 'margin-bottom',  van: 30, naar: 20 }
	];

	function pas_toe() {
		if ( ! telefoon.matches ) { return; }
		regels.forEach( function ( regel ) {
			var gevonden = document.querySelectorAll( '#page-container ' + regel.kies );
			[].slice.call( gevonden ).forEach( function ( el ) {
				/* De laatste rij van de voettekst eindigt met het dtc-logo en
				   krijgt zijn ruimte elders geregeld; hier niets aan doen,
				   anders staat er een inline maat die niet meer te sturen is. */
				if ( el.closest( '.et_pb_row_3_tb_footer' ) ) { return; }
				if ( el.dataset[ 'dtcRuimte' + regel.eigenschap.replace( /-/g, '' ) ] ) { return; }
				var nu = parseFloat( getComputedStyle( el )[ regel.eigenschap ] );
				if ( Math.round( nu ) !== regel.van ) { return; }
				el.style.setProperty( regel.eigenschap, regel.naar + 'px', 'important' );
				el.dataset[ 'dtcRuimte' + regel.eigenschap.replace( /-/g, '' ) ] = '1';
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', pas_toe );
	} else {
		pas_toe();
	}
	/* de sliders en de mobiele case-opmaak hangen modules om; daarna nog een ronde */
	[ 500, 1400, 2600 ].forEach( function ( ms ) { setTimeout( pas_toe, ms ); } );
	window.addEventListener( 'load', pas_toe );
	if ( telefoon.addEventListener ) { telefoon.addEventListener( 'change', pas_toe ); }
} )();
</script>
	<?php
}
