<?php
/**
 * Plugin Name: DTC diepte
 * Description: Geeft de kaarten en het zwevende menu een zachte schaduw. De site had nergens schaduw, waardoor alles even ver naar voren lag en beeldblokken op een witte pagina geen rand hadden. De kleur is niet zwart maar het donkerbruin uit de huisstijl met weinig dekking, zodat het warm blijft. Bij hover komt de kaart een paar pixels omhoog.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-diepte">
:root {
	--dtc-schaduw:       0 2px 8px rgba(45,40,36,.06), 0 12px 32px rgba(45,40,36,.08);
	--dtc-schaduw-hover: 0 4px 12px rgba(45,40,36,.09), 0 22px 48px rgba(45,40,36,.15);
}

/* kaarten die als los vlak op de pagina liggen */
#page-container .dtc-cases .c,
#page-container .dtc-stat-card,
#page-container .waaier-card,
#page-container .difl_contentcarouselitem,
#page-container .card {
	box-shadow: var(--dtc-schaduw);
	transition: box-shadow .35s cubic-bezier(.22,.61,.36,1), transform .35s cubic-bezier(.34,1.56,.64,1);
}
#page-container .dtc-cases .c:hover,
#page-container .dtc-stat-card:hover,
#page-container .card:hover {
	box-shadow: var(--dtc-schaduw-hover);
	transform: translateY(-4px);
}

/* de menubalk zweeft over de pagina; zonder schaduw plakt hij erop vast */
.et-l--header .difl_advancedmenu {
	box-shadow: 0 2px 10px rgba(45,40,36,.05), 0 14px 40px rgba(45,40,36,.09);
}

/* het volledige-schermmenu op de telefoon */
@media (max-width: 980px) {
	.dtc-menu { box-shadow: 0 24px 60px rgba(0,0,0,.35); }
}

@media (prefers-reduced-motion: reduce) {
	#page-container .dtc-cases .c:hover,
	#page-container .dtc-stat-card:hover,
	#page-container .card:hover { transform: none; }
}
</style>
	<?php
}, 99 );
