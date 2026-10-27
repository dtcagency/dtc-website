<?php
/**
 * Plugin Name: DTC subpagina-hero's
 * Description: Geeft de bovenkant van de subpagina's beweging bij het laden. Uit de websitecheck: 'hero's van subpagina's voelen aan de bovenkant nog wat saai op desktop'. De tweekleurige titel komt nu binnen: eerst het eerste woord, dan het beige woord, van onscherp naar scherp met dezelfde veerkrachtige curve als de rest van de site. De grote pijl op methode en visie zweeft rustig op en neer. De homepage heeft een eigen hero en doet hier niet aan mee.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() || is_front_page() || is_page( 30 ) ) { return; }
	?>
<style id="dtc-hero-sub">
@media (prefers-reduced-motion: no-preference) {
	@keyframes dtc-titel-in {
		from { translate: 0 .35em; filter: blur( 8px ); opacity: 0; }
		to   { translate: 0 0;     filter: blur( 0 );   opacity: 1; }
	}
	/* De titel zelf en daarna het beige woord. Er wordt met 'translate'
	   gewerkt en niet met 'transform', zodat Divi's eigen scroll-effecten
	   op deze modules gewoon blijven werken. */
	#page-container h1 {
		animation: dtc-titel-in .9s cubic-bezier( .34, 1.56, .64, 1 ) both;
	}
	#page-container h1 span {
		display: inline-block;
		animation: dtc-titel-in .9s cubic-bezier( .34, 1.56, .64, 1 ) .18s both;
	}

	@keyframes dtc-zweef {
		0%, 100% { translate: 0 0; }
		50%      { translate: 0 -18px; }
	}
	@media ( min-width: 981px ) {
		#page-container img[src*='Brown_Arrow'] {
			animation: dtc-zweef 6s ease-in-out 1s infinite;
		}
	}
}
</style>
	<?php
}, 99 );
