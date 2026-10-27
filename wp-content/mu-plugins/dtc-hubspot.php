<?php
/**
 * Plugin Name: DTC HubSpot-overzicht
 * Description: Zet de zes HubSpot-kaarten op /hubspot/ in een rustig raster. De bovenste rij trapte af omdat vier kaarten elk een eigen parallax-snelheid hadden (die staat nu uit in de pagina zelf); daarnaast zakte de knop mee met de tekst, zodat kaarten met een titel over twee regels hun knop lager hadden, en 'implementation' werd op 37px in een vak van 263px afgebroken tot 'implementati-on'.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( ! is_page( 282761 ) ) { return; }
	?>
<style id="dtc-hubspot">
@media (min-width: 981px) {
	/* gelijke hoogte per rij */
	body.page-id-282761 .et_pb_row:has(> .et_pb_column_1_3 .difl_advanced_blurb) {
		display: flex !important;
		align-items: stretch !important;
	}
	body.page-id-282761 .et_pb_row:has(> .et_pb_column_1_3 .difl_advanced_blurb) > .et_pb_column_1_3 {
		display: flex !important;
		flex-direction: column !important;
	}
	/* de knop onderaan, dus op één lijn binnen de rij */
	body.page-id-282761 .et_pb_row:has(> .et_pb_column_1_3 .difl_advanced_blurb) > .et_pb_column_1_3 > .difl_advanced_blurb {
		margin-top: auto !important;
	}
	/* titel past op één regel en wordt niet midden in een woord afgebroken */
	body.page-id-282761 .et_pb_row:has(> .et_pb_column_1_3 .difl_advanced_blurb) .et_pb_image + .et_pb_text p {
		font-size: 32px !important;
		hyphens: manual !important;
		-webkit-hyphens: manual !important;
		overflow-wrap: normal !important;
	}
}
</style>
	<?php
}, 99 );
