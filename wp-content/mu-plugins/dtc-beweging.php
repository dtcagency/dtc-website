<?php
/**
 * Plugin Name: DTC beweging
 * Description: Koppen, tekst en beelden komen rustig in beeld als je ernaartoe scrollt, en grote getallen tellen door. Wat al bij het openen in beeld staat blijft ongemoeid, zodat de pagina net zo snel zichtbaar is als daarvoor. Uit bij "verminder beweging" in het systeem.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* De klasse komt via een klein script in de head, niet vanuit PHP. Zonder
   JavaScript wordt er dus niets verborgen en blijft de pagina gewoon leesbaar. */
add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<script>
( function () {
	if ( window.matchMedia && matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }
	document.documentElement.className += ' dtc-beweegt';
} )();
</script>
<style id="dtc-beweging">
html.dtc-beweegt [data-dtc]{
	opacity:0;
	will-change:opacity,transform;
	transition:opacity .75s cubic-bezier(.22,.61,.36,1),transform .75s cubic-bezier(.22,.61,.36,1);
	transition-delay:var(--dtc-wacht,0ms);
}
html.dtc-beweegt [data-dtc="kop"]{transform:translateY(20px)}
html.dtc-beweegt [data-dtc="tekst"]{transform:translateY(14px)}
html.dtc-beweegt [data-dtc="knop"]{transform:translateY(12px)}
html.dtc-beweegt [data-dtc="beeld"]{transform:translateY(26px) scale(1.04);transition-duration:1s}

html.dtc-beweegt [data-dtc].dtc-zichtbaar{opacity:1;transform:none}

/* na afloop het rekenwerk weer loslaten */
html.dtc-beweegt [data-dtc].dtc-klaar{will-change:auto;transition:none}
</style>
	<?php
}, 1 );

add_action( 'wp_footer', 'dtc_beweging_script', 8 );
function dtc_beweging_script() {
	if ( is_admin() ) { return; }
	?>
<script id="dtc-beweging-js">
( function () {
	var wortel = document.documentElement;
	if ( wortel.className.indexOf( 'dtc-beweegt' ) === -1 ) { return; }
	if ( ! ( 'IntersectionObserver' in window ) ) { return; }

	/* Wat waar meedoet. De koppen van deze site staan vaak als <p> met een
	   eigen klasse in een tekstmodule, vandaar die extra namen. */
	var soorten = [
		[ 'kop',   '.et_pb_text h1, .et_pb_text h2, .et_pb_text h3, .et_pb_text h4, .et_pb_text .kop, .et_pb_text .mega, .et_pb_text .statement' ],
		[ 'tekst', '.et_pb_text > .et_pb_text_inner > p, .et_pb_text .sub, .et_pb_text .ey, .et_pb_text .pintro, .et_pb_text li' ],
		[ 'beeld', '.et_pb_image, .difl_imagereveal, .et_pb_module.et_pb_section_video' ],
		[ 'knop',  '.difl_advanced_blurb, .et_pb_button_module_wrapper, .dtc-vac details' ]
	];

	/* Deze stukken hebben hun eigen beweging of schuiven zijwaarts; daar zou
	   een tweede animatie alleen maar in de weg zitten. */
	var nietIn = '.et-l--header, .waaier, .dtc-trio, .dtc-trio-slider, .dtc-stats, .difl_contentcarousel, .dtc-case-paar, .et_pb_slider, .dtc-video-vak';

	var alles = [];
	soorten.forEach( function ( paar ) {
		var soort = paar[0];
		[].slice.call( document.querySelectorAll( '#page-container ' + paar[1].split( ',' ).join( ', #page-container ' ) ) ).forEach( function ( el ) {
			if ( el.closest( nietIn ) ) { return; }
			if ( el.dataset.dtc ) { return; }
			if ( ! el.textContent.trim() && ! el.querySelector( 'img, video, svg' ) ) { return; }
			/* wat nu verborgen is (bijvoorbeeld een variant voor een ander
			   schermformaat) laten we met rust; anders blijft het onzichtbaar */
			if ( ! el.offsetParent && getComputedStyle( el ).position !== 'fixed' ) { return; }
			el.dataset.dtc = soort;
			alles.push( el );
		} );
	} );

	if ( ! alles.length ) { return; }

	/* Alles wat bij het openen al in beeld staat doet niet mee: dat mag niet
	   later zichtbaar worden dan nu, anders wordt de pagina trager beoordeeld. */
	var vouw = window.innerHeight * 0.9;
	alles = alles.filter( function ( el ) {
		if ( el.getBoundingClientRect().top < vouw ) {
			delete el.dataset.dtc;
			el.removeAttribute( 'data-dtc' );
			return false;
		}
		return true;
	} );

	/* Binnen dezelfde kolom komen de onderdelen kort na elkaar in plaats van
	   allemaal tegelijk. */
	var perVak = new Map();
	alles.forEach( function ( el ) {
		var vak = el.closest( '.et_pb_column, .et_pb_row' ) || document.body;
		var rij = perVak.get( vak ) || 0;
		el.style.setProperty( '--dtc-wacht', Math.min( rij * 70, 280 ) + 'ms' );
		perVak.set( vak, rij + 1 );
	} );

	/* Grote getallen tellen door zodra ze in beeld komen. */
	function isTeller( el ) {
		if ( el.classList.contains( 'dtc-stat-number' ) ) { return false; }
		var kind = el.querySelector( 'b, strong, span' );
		var doel = kind && /^[0-9][0-9.]*\s*[+%]?$/.test( kind.textContent.trim() ) ? kind : null;
		if ( ! doel && /^[0-9][0-9.]*\s*[+%]?$/.test( el.textContent.trim() ) ) { doel = el; }
		if ( ! doel ) { return false; }
		return parseFloat( getComputedStyle( doel ).fontSize ) >= 34 ? doel : false;
	}

	function telDoor( el ) {
		var tekst  = el.textContent.trim();
		var staart = tekst.replace( /[0-9.]/g, '' );
		var eind   = parseInt( tekst.replace( /[^0-9]/g, '' ), 10 );
		if ( ! eind || eind > 1000000 ) { return; }
		var duur = 1300, begin = null;
		el.style.fontVariantNumeric = 'tabular-nums';
		function stap( tijd ) {
			if ( begin === null ) { begin = tijd; }
			var d = Math.min( ( tijd - begin ) / duur, 1 );
			var e = 1 - Math.pow( 1 - d, 3 );
			el.textContent = Math.round( eind * e ).toLocaleString( 'nl-NL' ) + staart;
			if ( d < 1 ) { requestAnimationFrame( stap ); }
			else { el.textContent = tekst; }
		}
		requestAnimationFrame( stap );
	}

	var kijker = new IntersectionObserver( function ( regels ) {
		regels.forEach( function ( regel ) {
			if ( ! regel.isIntersecting ) { return; }
			var el = regel.target;
			kijker.unobserve( el );
			el.classList.add( 'dtc-zichtbaar' );
			var teller = isTeller( el );
			if ( teller ) { telDoor( teller ); }
			setTimeout( function () { el.classList.add( 'dtc-klaar' ); }, 1400 );
		} );
	}, { rootMargin: '0px 0px -8% 0px', threshold: 0.01 } );

	alles.forEach( function ( el ) { kijker.observe( el ); } );

	/* Vangnet. De waarnemer laat een onderdeel pas los als het echt in beeld
	   komt; bij een pagina die na het laden nog van hoogte verandert (de
	   sliders op de telefoon) kan er eentje tussendoor glippen. Deze controle
	   maakt alles zichtbaar wat de bezoeker al gepasseerd is, zodat er nooit
	   iets onzichtbaar blijft staan. */
	var wacht = false;
	function vangnet() {
		wacht = false;
		var rest = false;
		alles.forEach( function ( el ) {
			if ( el.classList.contains( 'dtc-zichtbaar' ) ) { return; }
			if ( el.getBoundingClientRect().top > window.innerHeight * 0.95 ) { rest = true; return; }
			kijker.unobserve( el );
			el.classList.add( 'dtc-zichtbaar' );
			var teller = isTeller( el );
			if ( teller ) { telDoor( teller ); }
		} );
		return rest;
	}
	function plan() {
		if ( wacht ) { return; }
		wacht = true;
		requestAnimationFrame( vangnet );
	}
	addEventListener( 'scroll', plan, { passive: true } );
	addEventListener( 'resize', plan );
	addEventListener( 'load', plan );
	[ 1200, 2600, 4000 ].forEach( function ( ms ) { setTimeout( plan, ms ); } );

} )();
</script>
	<?php
}
