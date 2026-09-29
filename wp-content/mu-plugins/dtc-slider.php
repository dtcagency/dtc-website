<?php
/**
 * Plugin Name: DTC slider
 * Description: Zet een rij van drie onderwerpen (strategy / creation / growth) op de telefoon om in een slider die je opzij veegt, met tabs erboven. Op tablet en desktop verandert er niets.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_footer', 'dtc_slider_opmaak', 20 );
function dtc_slider_opmaak() {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-slider">
@media (max-width: 767px) {
	/* de rij wordt een horizontale slider */
	.et_pb_row.dtc-trio {
		display: flex !important;
		flex-wrap: nowrap !important;
		overflow-x: auto;
		scroll-snap-type: x mandatory;
		-webkit-overflow-scrolling: touch;
		scrollbar-width: none;
		gap: 5vw;
		padding-bottom: 6px;
		/* zonder deze regel schuift de eigen marge van de rij weg bij het vastklikken */
		scroll-padding-left: 12vw;
	}
	.et_pb_row.dtc-trio::-webkit-scrollbar { display: none; }

	.et_pb_row.dtc-trio > .et_pb_column {
		flex: 0 0 100% !important;
		width: 100% !important;
		max-width: 100% !important;
		margin: 0 !important;
		scroll-snap-align: start;
	}
	/* de bewegingseffecten van Divi werken niet in een zijwaartse slider */
	.et_pb_row.dtc-trio > .et_pb_column { transform: none !important; opacity: 1 !important; }

	/* de case-pagina's: de drie onderwerpen staan daar als losse tekstblokken
	   tussen de fotobanden; die worden hier tot slides samengevoegd. */
	.dtc-trio-slider {
		display: flex;
		flex-wrap: nowrap;
		overflow-x: auto;
		scroll-snap-type: x mandatory;
		-webkit-overflow-scrolling: touch;
		scrollbar-width: none;
		gap: 5vw;
		margin: 0 0 30px;
	}
	.dtc-trio-slider::-webkit-scrollbar { display: none; }
	.dtc-trio-slide {
		flex: 0 0 100%;
		scroll-snap-align: start;
	}
	.dtc-trio-slide > .et_pb_module { margin-bottom: 14px !important; }

	/* de tabs boven de slider */
	.dtc-trio-tabs {
		display: flex;
		gap: 8px;
		margin: 0 0 18px;
		padding: 0;
		list-style: none;
		font-family: 'Arboria', sans-serif;
	}
	.dtc-trio-tabs button {
		flex: 1 1 0;
		border: 1px solid #ddd3c4;
		background: none;
		border-radius: 999px;
		padding: 9px 4px 8px;
		font-family: inherit;
		font-weight: 500;
		font-size: 14px;
		color: #6b6258;
		cursor: pointer;
		transition: background .25s, color .25s, border-color .25s;
	}
	.dtc-trio-tabs button[aria-selected="true"] {
		background: #2d2824;
		border-color: #2d2824;
		color: #fff;
	}
}
@media (min-width: 768px) {
	.dtc-trio-tabs { display: none; }
}
</style>
<script id="dtc-slider-js">
( function () {
	var telefoon = window.matchMedia( '(max-width: 767px)' );

	function woord( kolom ) {
		var t = ( kolom.innerText || '' ).trim().split( '\n' )[0].trim();
		return t ? t.slice( 0, 18 ) : '';
	}

	function maakTabs( houder, slides ) {
		if ( houder.dataset.dtcSlider === 'klaar' || slides.length < 2 ) { return; }
		var tabs = document.createElement( 'div' );
		tabs.className = 'dtc-trio-tabs';
		tabs.setAttribute( 'role', 'tablist' );

		slides.forEach( function ( slide, i ) {
			var knop = document.createElement( 'button' );
			knop.type = 'button';
			knop.textContent = woord( slide ) || ( 'deel ' + ( i + 1 ) );
			knop.setAttribute( 'role', 'tab' );
			knop.setAttribute( 'aria-selected', i === 0 ? 'true' : 'false' );
			knop.addEventListener( 'click', function () {
				var marge = parseFloat( getComputedStyle( houder ).paddingLeft ) || 0;
				houder.scrollTo( { left: slide.offsetLeft - houder.offsetLeft - marge, behavior: 'smooth' } );
			} );
			tabs.appendChild( knop );
		} );

		var marge = getComputedStyle( houder ).paddingLeft;
		tabs.style.paddingLeft = marge;
		tabs.style.paddingRight = getComputedStyle( houder ).paddingRight;
		houder.style.scrollPaddingLeft = marge;
		houder.parentNode.insertBefore( tabs, houder );
		houder.dataset.dtcSlider = 'klaar';
		houder.scrollLeft = 0;

		var bezig;
		houder.addEventListener( 'scroll', function () {
			clearTimeout( bezig );
			bezig = setTimeout( function () {
				var m = parseFloat( getComputedStyle( houder ).paddingLeft ) || 0;
				var midden = houder.scrollLeft + m + houder.clientWidth / 2;
				var actief = 0;
				slides.forEach( function ( slide, i ) {
					if ( slide.offsetLeft - houder.offsetLeft <= midden ) { actief = i; }
				} );
				[].slice.call( tabs.children ).forEach( function ( knop, i ) {
					knop.setAttribute( 'aria-selected', i === actief ? 'true' : 'false' );
				} );
			}, 60 );
		}, { passive: true } );
	}

	function bouw( rij ) {
		var kolommen = [].slice.call( rij.children ).filter( function ( k ) {
			return k.classList.contains( 'et_pb_column' );
		} );
		maakTabs( rij, kolommen );
	}

	/* case-pagina's: 'strategy', 'creation' en 'growth' staan er als losse
	   tekstblokken tussen de fotobanden. Die halen we naar een slider. */
	function bouwCase() {
		var woorden = [ 'strategy', 'creation', 'growth' ];
		var koppen = [].slice.call( document.querySelectorAll( '.et_pb_text' ) ).filter( function ( m ) {
			if ( m.closest( '.dtc-trio' ) || m.closest( '.dtc-trio-slider' ) ) { return false; }
			var t = ( m.innerText || '' ).trim().toLowerCase();
			return woorden.indexOf( t ) > -1;
		} );
		/* alleen op de case-pagina's, waar alle drie de onderwerpen als los
		   tekstblok staan. Elders (zoals de homepage) blijft alles zoals het is. */
		var gevonden = koppen.map( function ( m ) { return ( m.innerText || '' ).trim().toLowerCase(); } );
		var compleet = woorden.every( function ( w ) { return gevonden.indexOf( w ) > -1; } );
		if ( koppen.length < 3 || ! compleet ) { return; }

		var anker = document.createElement( 'div' );
		koppen[0].parentNode.insertBefore( anker, koppen[0] );

		var slider = document.createElement( 'div' );
		slider.className = 'dtc-trio-slider';

		koppen.forEach( function ( kop ) {
			var slide = document.createElement( 'div' );
			slide.className = 'dtc-trio-slide';
			var stukken = [ kop ];
			var volgende = kop.nextElementSibling;
			while ( volgende && volgende.classList.contains( 'et_pb_text' ) && stukken.length < 3 ) {
				var nu = volgende;
				volgende = volgende.nextElementSibling;
				stukken.push( nu );
			}
			stukken.forEach( function ( el ) { slide.appendChild( el ); } );
			slider.appendChild( slide );
		} );

		anker.parentNode.replaceChild( slider, anker );
		maakTabs( slider, [].slice.call( slider.children ) );
	}

	function start() {
		if ( ! telefoon.matches ) { return; }
		[].slice.call( document.querySelectorAll( '.et_pb_row.dtc-trio' ) ).forEach( bouw );
		bouwCase();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
	if ( telefoon.addEventListener ) { telefoon.addEventListener( 'change', start ); }
} )();
</script>
	<?php
}
