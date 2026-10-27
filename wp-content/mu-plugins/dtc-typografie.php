<?php
/**
 * Plugin Name: DTC typografie
 * Description: Eén schaal voor de koppen. De paginatitels stonden op 100, 115, 138 en 150px met regelhoogtes van 0,70 tot 0,98; de sectiekoppen op acht verschillende maten met regelhoogtes van 0,80 tot 1,15. Elke kop wordt nu naar de dichtstbijzijnde stap op de schaal gezet, met een vaste regelhoogte per stap.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<script id="dtc-typografie">
( function () {
	/* De schaal: vanaf welke gemeten maat hoort een kop bij welke stap, en
	   welke regelhoogte past daarbij. Van groot naar klein. */
	var SCHAAL = [
		{ vanaf: 90, regelhoogte: 0.78 },   /* paginatitel  */
		{ vanaf: 56, regelhoogte: 0.85 },   /* sectiekop    */
		{ vanaf: 30, regelhoogte: 1.05 },   /* tussenkop    */
		{ vanaf: 0,  regelhoogte: 1.25 }    /* kleine kop   */
	];

	function stap( maat ) {
		for ( var i = 0; i < SCHAAL.length; i++ ) {
			if ( maat >= SCHAAL[ i ].vanaf ) { return SCHAAL[ i ]; }
		}
		return SCHAAL[ SCHAAL.length - 1 ];
	}

	function loop() {
		var koppen = document.querySelectorAll( '#page-container h1, #page-container h2, #page-container h3' );
		for ( var i = 0; i < koppen.length; i++ ) {
			var el = koppen[ i ];
			var r = el.getBoundingClientRect();
			if ( r.height < 6 ) { continue; }
			var cs = window.getComputedStyle( el );

			/* De knoplabels in de blurb-modules zijn ook h3'en; die horen bij
			   de knop en niet bij de kopschaal. */
			var maat = parseFloat( cs.fontSize );
			/* Knoplabels zijn in Divi ook h3'en. Een kop binnen een knop of
			   blurb telt daarom alleen mee als hij groot is: de slotkop
			   'ready when you are' zit in zo'n module maar is een echte kop. */
			if ( maat < 30 && el.closest( '.difl_advanced_blurb, a, button' ) ) { continue; }
			if ( ! maat ) { continue; }
			el.style.setProperty( 'line-height', stap( maat ).regelhoogte + 'em', 'important' );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', loop );
	} else {
		loop();
	}
	/* Blokken die pas bij het scrollen verschijnen hebben bij het laden nog
	   geen hoogte en worden dan overgeslagen; vandaar een tweede ronde. */
	window.addEventListener( 'load', function () { setTimeout( loop, 400 ); } );
	window.addEventListener( 'scroll', function () { clearTimeout( window.__dtcTypoS ); window.__dtcTypoS = setTimeout( loop, 400 ); }, { passive: true } );
	window.addEventListener( 'resize', function () { clearTimeout( window.__dtcTypo ); window.__dtcTypo = setTimeout( loop, 200 ); } );
} )();
</script>
	<?php
}, 99 );
