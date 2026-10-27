<?php
/**
 * Plugin Name: DTC sliders
 * Description: Twee dingen die uit de websitecheck kwamen. (1) De carrousel in de hero werd op de telefoon nooit door Swiper opgestart: negen slides van 234px stonden in een rij van 371px en de rest werd afgeknipt, zodat je nooit bij het einde kwam. Hij schuift nu native, net als de andere sliders op de site. (2) De campagnerij had op desktop geen bediening; daar staan nu een vorige- en volgende-knop bij.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-sliders">
/* (1) De carrousel die Swiper niet opstart, schuift zelf. */
.difl_contentcarousel .swiper-wrapper {
	overflow-x: auto !important;
	scroll-snap-type: x mandatory;
	-webkit-overflow-scrolling: touch;
	scrollbar-width: none;
}
.difl_contentcarousel .swiper-wrapper::-webkit-scrollbar { display: none; }
.difl_contentcarousel .swiper-slide { scroll-snap-align: start; }

/* (3) De trendsrij op insights. De kaarten stonden op een vaste 300px, ook op
   een scherm van 1440 waar de rij 1267px breed is: vier smalle kaarten met
   een rafelig randje. Ze groeien nu mee tot 380px, zodat er ruim drie in
   beeld staan. De pijlen gaan grijs als je aan het begin of eind bent. */
@media (min-width: 981px) {
	#rail-blogs .c {
		flex: 0 0 clamp( 280px, 25vw, 380px ) !important;
		width: auto !important;
	}
	/* De campagnekaarten op de homepage stonden op 240px, bijna vijf naast
	   elkaar: klein en druk. Uit de check: 'kunnen iets groter voor meer
	   body'. Nu ruim vier in beeld. */
	#rail-home .c {
		flex: 0 0 clamp( 240px, 20vw, 300px ) !important;
		width: auto !important;
	}
}
.dtc-ins .pijl[disabled] { opacity: .35; cursor: default; pointer-events: none; }

/* (2) Bediening bij de campagnerij. Op de telefoon veeg je; daar zijn de
   knoppen overbodig. */
.dtc-cases__kop { position: relative; }
.dtc-rail-knoppen { display: none; }
@media (min-width: 981px) {
	.dtc-rail-knoppen {
		display: flex;
		gap: 10px;
		position: absolute;
		right: 0;
		bottom: 6px;
	}
	.dtc-rail-knop {
		width: 44px;
		height: 44px;
		border: 2px solid #c0ab94;
		border-radius: 72px;
		background: transparent;
		color: #2d2824;
		font-family: 'ETmodules';
		font-size: 20px;
		line-height: 1;
		cursor: pointer;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		transition: background .2s ease, color .2s ease, opacity .2s ease;
	}
	.dtc-rail-knop:hover { background: #2d2824; color: #fff; }
	.dtc-rail-knop[disabled] { opacity: .35; cursor: default; }
	.dtc-rail-knop[disabled]:hover { background: transparent; color: #2d2824; }
}
</style>
<script id="dtc-sliders-js">
( function () {
	function bouw() {
		if ( window.innerWidth < 981 ) { return; }
		var rails = document.querySelectorAll( '.dtc-cases .rail' );
		for ( var i = 0; i < rails.length; i++ ) {
			var rail = rails[ i ];
			if ( rail.dataset.dtcKnoppen ) { continue; }
			var blok = rail.closest( '.dtc-cases' );
			var kop = blok ? blok.querySelector( '.dtc-cases__kop' ) : null;
			if ( ! kop ) { continue; }
			if ( rail.scrollWidth <= rail.clientWidth + 8 ) { continue; }
			rail.dataset.dtcKnoppen = '1';

			var houder = document.createElement( 'div' );
			houder.className = 'dtc-rail-knoppen';
			var terug = document.createElement( 'button' );
			terug.type = 'button';
			terug.className = 'dtc-rail-knop';
			terug.setAttribute( 'aria-label', 'vorige campagnes' );
			terug.innerHTML = '&#x23;';           /* ETmodules: pijl naar links */
			var verder = document.createElement( 'button' );
			verder.type = 'button';
			verder.className = 'dtc-rail-knop';
			verder.setAttribute( 'aria-label', 'volgende campagnes' );
			verder.innerHTML = '&#x24;';          /* ETmodules: pijl naar rechts */
			houder.appendChild( terug );
			houder.appendChild( verder );
			kop.appendChild( houder );

			( function ( rail, terug, verder ) {
				function stap() {
					var kaart = rail.querySelector( '.c' );
					return kaart ? kaart.getBoundingClientRect().width + 24 : rail.clientWidth * 0.8;
				}
				function stand() {
					terug.disabled = rail.scrollLeft <= 4;
					verder.disabled = rail.scrollLeft >= rail.scrollWidth - rail.clientWidth - 4;
				}
				terug.addEventListener( 'click', function () { rail.scrollBy( { left: -stap(), behavior: 'smooth' } ); } );
				verder.addEventListener( 'click', function () { rail.scrollBy( { left: stap(), behavior: 'smooth' } ); } );
				rail.addEventListener( 'scroll', function () { clearTimeout( rail.__t ); rail.__t = setTimeout( stand, 80 ); }, { passive: true } );
				stand();
			} )( rail, terug, verder );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', bouw );
	} else {
		bouw();
	}
	window.addEventListener( 'load', function () { setTimeout( bouw, 400 ); } );
} )();
</script>
	<?php
}, 99 );
