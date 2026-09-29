<?php
/**
 * Plugin Name: DTC case-pagina op de telefoon
 * Description: Kort de casepagina's in op de telefoon: hero, het merkblok, één beeld met tekst, de slider met strategy/creation/growth, twee foto's naast elkaar en dan "want to know more". De overige beeldbanden blijven daar weg. Op tablet en desktop verandert er niets.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_footer', 'dtc_case_mobiel', 21 );
function dtc_case_mobiel() {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-case-mobiel">
@media (max-width: 767px) {
	/* de twee foto's die naast elkaar komen te staan */
	.dtc-case-paar {
		display: flex;
		gap: 10px;
		align-items: stretch;
		margin: 0 0 30px;
	}
	.dtc-case-paar > * {
		flex: 1 1 0 !important;
		width: auto !important;
		max-width: none !important;
		min-width: 0 !important;
		margin: 0 !important;
		transform: none !important;
		left: auto !important;
		right: auto !important;
		top: auto !important;
	}
	.dtc-case-paar > * { height: 240px !important; padding: 0 !important; }
	.dtc-case-verborgen { display: none !important; }
}
</style>
<script id="dtc-case-mobiel-js">
( function () {
	var telefoon = window.matchMedia( '(max-width: 767px)' );

	function heeftBeeld( el ) {
		var b = getComputedStyle( el ).backgroundImage || '';
		return b && 'none' !== b && ! /gradient/.test( b );
	}

	function bouw() {
		if ( ! telefoon.matches ) { return; }

		var slider = document.querySelector( '.dtc-trio-slider' );
		if ( ! slider ) { return; }   /* alleen op een casepagina met de drie onderwerpen */
		var sliderRij = slider.closest( '.et_pb_row' );
		if ( ! sliderRij ) { return; }

		/* In het ontwerp staat boven aan elke casepagina een grijs vlak
		   zonder beeld (#d9d9d9). Op desktop is dat een smalle streep van
		   111px, maar op de telefoon staat er 200px padding boven en onder,
		   waardoor het een leeg grijs blok van ruim 400px wordt. Weg ermee. */
		[].slice.call( document.querySelectorAll( '#page-container [class*="et_pb_divider_"]' ) )
			.filter( function ( b ) {
				return ! heeftBeeld( b ) && b.offsetHeight > 120;
			} )
			.forEach( function ( b ) { b.classList.add( 'dtc-case-verborgen' ); } );

		/* de beeldbanden in dezelfde rij als de slider */
		var banden = [].slice.call( sliderRij.querySelectorAll( '[class*="et_pb_divider_"]' ) )
			.filter( heeftBeeld )
			.filter( function ( b ) { return ! b.closest( '.dtc-case-paar' ); } );

		var paarRij = document.querySelector( '.dtc-case-paar' );
		if ( ! paarRij ) {
			if ( banden.length < 2 ) { return; }
			paarRij = document.createElement( 'div' );
			paarRij.className = 'dtc-case-paar';
			slider.parentNode.insertBefore( paarRij, slider.nextSibling );
			banden.slice( 0, 2 ).forEach( function ( b ) { paarRij.appendChild( b ); } );
			banden = banden.slice( 2 );
		}

		/* alles wat daarna nog aan losse banden overblijft, laten we weg */
		banden.forEach( function ( b ) { b.classList.add( 'dtc-case-verborgen' ); } );

		/* de losse woorden die over die banden lagen, gaan mee weg */
		var woorden = [ 'expand evolve progress', 'be bold', '360', 'expand evolve' ];
		var gezien = 0;
		[].slice.call( document.querySelectorAll( '#page-container .et_pb_text' ) ).forEach( function ( t ) {
			var tekst = ( t.innerText || '' ).trim().toLowerCase().replace( /\s+/g, ' ' );
			if ( woorden.indexOf( tekst ) === -1 ) { return; }
			gezien++;
			if ( gezien > 1 ) { t.classList.add( 'dtc-case-verborgen' ); }
		} );

		/* alles tussen de slider en "want to know more" is beeld dat we op de
		   telefoon overslaan (losse fotobanden en het videoblok) */
		var slotRij = [].slice.call( document.querySelectorAll( '#page-container .et_pb_row' ) ).find( function ( r ) {
			return /want to know more/i.test( r.innerText || '' );
		} );
		if ( slotRij ) {
			var rijen = [].slice.call( document.querySelectorAll( '#page-container .et_pb_row' ) );
			var van = rijen.indexOf( sliderRij ), tot = rijen.indexOf( slotRij );
			if ( van > -1 && tot > van ) {
				rijen.slice( van + 1, tot ).forEach( function ( r ) { r.classList.add( 'dtc-case-verborgen' ); } );
			}
		}
	}

	function start() {
		/* de slider wordt door dtc-slider.php gebouwd; even wachten tot die klaar is,
		   en nog een keer als alle beelden geladen zijn */
		/* een paar keer proberen: de banden krijgen hun achtergrond pas als
		   Divi klaar is met laden */
		[ 80, 400, 1200, 2500 ].forEach( function ( ms ) { setTimeout( bouw, ms ); } );
		window.addEventListener( 'load', function () { setTimeout( bouw, 150 ); } );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
</script>
	<?php
}
