<?php
/**
 * Plugin Name: DTC contrast
 * Description: Maakt de dunne beige teksten leesbaar. Beige (#c0ab94) op wit haalt een contrastratio van 2,21 — ver onder de norm van 4,5 voor lopende tekst en 3,0 voor grote tekst. Lopende tekst krijgt #6b6258 (5,98), tussenkoppen vanaf 24px het warmere #8a7257 (4,54); beide staan al in de huisstijl. De grote merkwoorden (ons 'method', 'services', 'insights') blijven beige: bij die maat speelt leesbaarheid geen rol. Beige vlakken met alleen witte tekst worden verdiept; staat er ook donkere tekst op, dan wordt juist die witte tekst donker.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-contrast">
/* De tussenkoppen in de losse codeblokken, meteen goed zodat ze niet eerst
   in het te lichte beige oplichten. */
#page-container p.sub,
#page-container p.pintro,
#page-container p.statement,
#page-container .dtc-sv .svsub,
#page-container .dtc-duo .sub {
	color: #8a7257 !important;
}
/* Dit blok heeft alleen witte tekst; een dieper vlak maakt die leesbaar. */
#page-container .dtc-hb {
	background-color: #8a7257 !important;
}
/* De knop in de menubalk was beige met witte letters (2,21). */
.et-l--header .df-menu-button {
	background-color: #8a7257 !important;
}
</style>
<script id="dtc-contrast-js">
( function () {
	var BEIGE = 'rgb(192, 171, 148)';
	var DIEP  = '#8a7257';
	var LOPEND = '#6b6258';
	var DONKER = '#2d2824';

	function helderheid( kleur ) {
		var m = kleur.match( /[\d.]+/g );
		if ( ! m ) { return null; }
		if ( m[ 3 ] !== undefined && parseFloat( m[ 3 ] ) < 0.5 ) { return null; }
		function f( v ) { v = v / 255; return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 ); }
		return 0.2126 * f( +m[ 0 ] ) + 0.7152 * f( +m[ 1 ] ) + 0.0722 * f( +m[ 2 ] );
	}

	function isWit( kleur ) {
		var h = helderheid( kleur );
		return h !== null && h > 0.8;
	}

	function heeftEigenTekst( el ) {
		for ( var i = 0; i < el.childNodes.length; i++ ) {
			var n = el.childNodes[ i ];
			if ( n.nodeType === 3 && n.textContent.trim().length > 2 ) { return true; }
		}
		return false;
	}

	/* het dichtstbijzijnde element met een eigen, dekkende achtergrond */
	function vlakOnder( el ) {
		var n = el;
		while ( n && n !== document.documentElement ) {
			var h = helderheid( window.getComputedStyle( n ).backgroundColor );
			if ( h !== null ) { return n; }
			n = n.parentElement;
		}
		return null;
	}

	function bevatDonkereTekst( vlak ) {
		var kinderen = vlak.querySelectorAll( '*' );
		for ( var i = 0; i < kinderen.length; i++ ) {
			var k = kinderen[ i ];
			if ( ! heeftEigenTekst( k ) ) { continue; }
			var r = k.getBoundingClientRect();
			if ( r.width < 20 || r.height < 6 ) { continue; }
			var h = helderheid( window.getComputedStyle( k ).color );
			if ( h !== null && h < 0.25 ) { return true; }
		}
		return false;
	}

	function loop() {
		var alles = document.querySelectorAll( '#page-container *' );
		for ( var i = 0; i < alles.length; i++ ) {
			var el = alles[ i ];
			if ( ! heeftEigenTekst( el ) ) { continue; }
			var cs = window.getComputedStyle( el );
			var maat = parseFloat( cs.fontSize );

			/* beige tekst: verdiepen. De merkwoorden van 40px en groter
			   blijven staan, daar is leesbaarheid geen probleem. */
			if ( cs.color === BEIGE && maat < 40 ) {
				el.style.setProperty( 'color', maat >= 24 ? DIEP : LOPEND, 'important' );
				continue;
			}

			/* Op een crèmekleurig vlak haalt het diepe bruin net niet genoeg
			   voor lopende tekst (4,03 tegen de vereiste 4,5); daar gaat de
			   kleinere tekst een stap verder omlaag. */
			if ( cs.color === 'rgb(138, 114, 87)' && maat < 24 ) {
				el.style.setProperty( 'color', LOPEND, 'important' );
			}

			/* witte tekst op een beige vlak */
			if ( isWit( cs.color ) ) {
				var vlak = vlakOnder( el );
				if ( ! vlak || window.getComputedStyle( vlak ).backgroundColor !== BEIGE ) { continue; }
				if ( bevatDonkereTekst( vlak ) ) {
					/* gemengd vlak: de witte tekst wordt donker, het vlak blijft beige */
					el.style.setProperty( 'color', DONKER, 'important' );
				} else {
					vlak.style.setProperty( 'background-color', DIEP, 'important' );
				}
			}
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', loop );
	} else {
		loop();
	}
	window.addEventListener( 'load', function () { setTimeout( loop, 300 ); } );
} )();
</script>
	<?php
}, 99 );
