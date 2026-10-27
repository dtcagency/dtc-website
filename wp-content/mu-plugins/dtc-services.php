<?php
/**
 * Plugin Name: DTC services
 * Description: Houdt de tabs (Strategy / Creation / Growth) in beeld tijdens het scrollen. Het blok eronder is op desktop 2400px en op de telefoon 3500px lang; wie halverwege was, zag niet meer welk onderdeel open stond en moest terug naar boven om te wisselen. De tabbalk schuift nu mee onder het menu, en wie onderweg van tab wisselt, gaat terug naar het begin van dat onderdeel.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-services">
/* De balk zelf is een pill met een eigen achtergrond; met een schaduw
   valt hij los van de inhoud die eronderdoor schuift. */
.dtc-sv .tabs {
	position: sticky;
	top: 104px;
	z-index: 20;
	transition: box-shadow .3s ease;
}
.dtc-sv .tabs.dtc-plakt {
	box-shadow: 0 2px 10px rgba(45,40,36,.06), 0 14px 32px rgba(45,40,36,.12);
}
@media (max-width: 980px) {
	.dtc-sv .tabs { top: 98px; }
}
</style>
<script id="dtc-services-js">
( function () {
	function start() {
		var balk = document.querySelector( '.dtc-sv .tabs' );
		if ( ! balk ) { return; }

		/* schaduw alleen zolang de balk daadwerkelijk vastzit */
		function stand() {
			var boven = parseFloat( window.getComputedStyle( balk ).top ) || 0;
			balk.classList.toggle( 'dtc-plakt', balk.getBoundingClientRect().top <= boven + 1 );
		}
		window.addEventListener( 'scroll', stand, { passive: true } );
		stand();

		/* Wisselen terwijl je halverwege zit: terug naar het begin van het
		   nieuwe onderdeel, anders sta je midden in een tekst. */
		balk.addEventListener( 'click', function ( e ) {
			if ( ! e.target.closest( 'button' ) ) { return; }
			if ( ! balk.classList.contains( 'dtc-plakt' ) ) { return; }
			var blok = balk.closest( '.dtc-sv' );
			var y = blok.getBoundingClientRect().top + window.scrollY - 100;
			setTimeout( function () { window.scrollTo( { top: y, behavior: 'smooth' } ); }, 30 );
		} );
	}
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
} )();
</script>
	<?php
}, 99 );
