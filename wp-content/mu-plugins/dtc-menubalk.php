<?php
/**
 * Plugin Name: DTC menubalk
 * Description: Zet de let's talk-knop op de telefoon precies in het midden van de menubalk. Hij stond op een vaste afstand van het logo, waardoor hij op bredere telefoons naar links wegliep.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-menubalk">
/* Blokken die van rand tot rand lopen (de waaier, de cijferstrook, de
   beeldenrij op services) rekenen met 100vw. Die maat telt op een gewone
   computer de breedte van de schuifbalk mee, waardoor de pagina een paar
   pixels te breed werd en er onderaan een horizontale schuifbalk verscheen.
   Met clip wordt dat weggeknipt zonder dat sticky-elementen stuk gaan. */
html { overflow-x: clip; }
body { overflow-x: clip; }
/* Divi zet op de paginawikkel overflow-x:auto. Alles wat daar een paar pixels
   buiten valt (een knop met een negatieve marge in de footer, een draaiende
   kaart in de waaier) gaf daardoor een horizontale schuifbalk. */
#page-container { overflow-x: clip; }

@media (max-width: 767px) {
	/* Alle tussenkoppen staan op de telefoon op 70px. Ze liepen van 24 tot
	   70px door elkaar heen; nu is het overal dezelfde maat.
	   De afbreekregels zijn een vangnet: 'composition' is bij 70px breder
	   dan een telefoonscherm en zou anders half buiten beeld vallen. */
	#page-container h2 {
		font-size: 70px !important;
		line-height: 0.95em !important;
		hyphens: auto;
		-webkit-hyphens: auto;
		overflow-wrap: break-word;
	}

	/* De footer stond op de telefoon in een smalle kolom links: sitemap,
	   de tweede linklijst en social media onder elkaar. Ze staan nu naast
	   elkaar over de volle breedte. */
	.et-l--footer .et_pb_row_1_tb_footer {
		display: flex;
		flex-wrap: wrap;
		align-items: flex-start;
	}
	.et-l--footer .et_pb_column_0_tb_footer {
		flex: 0 0 100%;
		width: 100% !important;
	}
	.et-l--footer .et_pb_row_1_tb_footer::before,
	.et-l--footer .et_pb_row_1_tb_footer::after { display: none !important; }
	.et-l--footer .et_pb_column_1_tb_footer,
	.et-l--footer .et_pb_column_2_tb_footer,
	.et-l--footer .et_pb_column_3_tb_footer {
		flex: 0 0 33.333%;
		width: 33.333% !important;
		min-width: 0;
		margin: 0 !important;
		padding-right: 8px;
		box-sizing: border-box;
	}
	/* de tweede linklijst stond met een vaste negatieve marge naast de eerste;
	   in de nieuwe indeling staat hij gewoon in zijn eigen kolom */
	.et-l--footer .et_pb_column_2_tb_footer .et_pb_text {
		margin: 0 !important;
	}
	.et-l--footer .et_pb_column_1_tb_footer .et_pb_text,
	.et-l--footer .et_pb_column_3_tb_footer .et_pb_text {
		margin-left: 0 !important;
		margin-right: 0 !important;
	}
	/* de drie kolommen beginnen op dezelfde hoogte */
	.et-l--footer .et_pb_column_1_tb_footer > .et_pb_module:first-child,
	.et-l--footer .et_pb_column_2_tb_footer > .et_pb_module:first-child,
	.et-l--footer .et_pb_column_3_tb_footer > .et_pb_module:first-child {
		margin-top: 0 !important;
	}

	/* De merknaam onder de kaarten in de slider (nissan., mazda, ...) is op
	   de telefoon overbodig: de naam staat al in het beeld zelf. */
	.difl_contentcarousel .df_cc_title { display: none !important; }

	/* Our vision: de decoratieve pijltjes in de drie slides passen op de
	   telefoon niet in hun kolom. Ze stonden buiten beeld en trokken met hun
	   negatieve bovenmarge (-80px) de tekst eronder omhoog, dwars door de kop
	   heen. Op desktop passen ze wel, dus daar blijven ze staan. */
	body.page-id-139 .et_pb_image:has(img[src*="Button_down"]) { display: none !important; }
	body.page-id-139 #page-container .et_pb_column { overflow-x: clip; }

	/* De voettekst eindigt op de telefoon met het dtc-logo; daaronder hoeft
	   geen loze ruimte meer te staan. */
	/* Divi zet hier zelf 70px met !important en een even zware selector; die
	   staat later in de cascade, dus hier moet het zwaarder. */
	/* Het logo steekt 27px onder zijn kolom uit (vaste waarde op elke
	   breedte); 28px is dus precies genoeg om het heel te houden zonder
	   loze ruimte eronder. Divi stond hier op 70px. */
	/* Divi zet op dit logo een scroll-effect: aan het eind van de pagina
	   staat het 26px naar beneden geschoven (en 1.04x geschaald). Een
	   transform telt niet mee voor de hoogte van het document, dus het logo
	   eindigde precies op de onderrand en viel op de telefoon achter de
	   browserbalk. Zonder die verschuiving blijft het heel. */
	body #page-container #et-boc .et-l--footer .et_pb_row_3_tb_footer.et_pb_row { padding-bottom: 0 !important; }
	body #page-container #et-boc .et-l--footer .et_pb_image_1_tb_footer.et_pb_module { margin-bottom: 0 !important; transform: none !important; }

	/* Cases: het visitekaartje (foto, naam, telefoon, mail) stond op de
	   telefoon onder elkaar en nam een half scherm in beslag. Naast elkaar
	   is het net zo leesbaar en een stuk compacter. De kolom is een raster:
	   de foto links over alle drie de regels, de rest rechts. */
	.et_pb_column:has(> .et_pb_image img[src*="Bridget"]) {
		display: grid;
		grid-template-columns: 120px 1fr;
		grid-template-rows: auto auto auto;
		column-gap: 16px;
		align-content: start;
		align-items: center;
	}
	.et_pb_column:has(> .et_pb_image img[src*="Bridget"]) > .et_pb_image {
		grid-column: 1;
		grid-row: 1 / 4;
		margin-bottom: 0 !important;
	}
	.et_pb_column:has(> .et_pb_image img[src*="Bridget"]) > *:not(.et_pb_image) {
		grid-column: 2;
		margin-bottom: 0 !important;
	}
	.et_pb_column:has(> .et_pb_image img[src*="Bridget"]) > .et_pb_text p {
		font-size: 22px !important;
		line-height: 1.3em !important;
	}

	/* De twee knoppen onderaan (voorwaarden en privacy) zijn 270 en 300px
	   breed met een negatieve rechtermarge. Tussen 480 en 700px liep de
	   tweede daardoor het scherm uit. Zonder die marge vallen ze netjes
	   onder elkaar. */
	.et-l--footer .difl_advanced_blurb,
	footer .difl_advanced_blurb {
		margin-right: 0 !important;
		max-width: 100% !important;
	}

	/* De twee knoppen stonden onder elkaar en namen zo een half scherm in.
	   Naast elkaar passen ze precies. De kolom van de tweede knop wordt
	   doorzichtig gemaakt (display:contents), zodat de knop en het dtc-logo
	   eronder ieder een eigen plek in de rij krijgen: de knoppen delen de
	   breedte, het logo gaat er over de volle breedte onder. */
	.et-l--footer .et_pb_row_3_tb_footer { flex-wrap: wrap; column-gap: 16px; }
	.et-l--footer .et_pb_column_6_tb_footer { display: contents; }
	.et-l--footer .et_pb_column_5_tb_footer,
	.et-l--footer .difl_advanced_blurb_1_tb_footer {
		flex: 0 0 calc(50% - 8px);
		max-width: calc(50% - 8px);
		margin-top: 0 !important;
	}
	.et-l--footer .et_pb_image_1_tb_footer { flex: 0 0 100%; margin-top: 20px !important; margin-bottom: 0 !important; }

	/* De balk was: [logo + knop] links, menu-icoon rechts. De knop stond dus op
	   een vaste afstand van het logo en liep op bredere telefoons naar links weg.
	   Nu is de balk een raster van drie: logo links, knop in het midden,
	   menu-icoon rechts — ongeacht hoe breed het logo of het icoon is. */
	.et-l--header .df-am-row .row-inner {
		display: grid;
		grid-template-columns: 1fr auto 1fr;
		align-items: center;
	}
	.et-l--header .df-am-row .row-inner > .df-am-col.left { display: contents; }
	/* het logo is zelf ook een menu-item, vandaar de plaats in de rij:
	   eerste item = logo (links), tweede item = de knop (midden) */
	.et-l--header .df-am-row .row-inner > .df-am-col.left > .df-am-item:first-child {
		justify-self: start;
	}
	.et-l--header .df-am-row .row-inner > .df-am-col.left > .df-am-item:nth-child(2) {
		justify-self: center;
		margin: 0 !important;
	}
	.et-l--header .df-am-row .row-inner > .df-am-col.right { justify-self: end; }
}

</style>
<script id="dtc-menubalk-js">
( function () {
	/* In het hamburgermenu opent een tik op een item met een uitklapmenu
	   (Our vision, Hubspot) alleen dat uitklapmenu; de pagina zelf was zo
	   niet te bereiken. Een tweede tik op hetzelfde item gaat nu wel naar
	   de pagina. */
	var telefoon = window.matchMedia( '(max-width: 767px)' );

	document.addEventListener( 'click', function ( e ) {
		if ( ! telefoon.matches ) { return; }

		var link = e.target.closest( '.df-menu-nav-level-0 > li.menu-item-has-children > a' );
		if ( ! link ) { return; }

		var item = link.parentElement;
		/* eerste tik: het uitklapmenu laten openen door de menu-plugin zelf */
		if ( ! item.classList.contains( 'df-show-dropdown' ) ) { return; }

		var adres = link.getAttribute( 'href' );
		if ( ! adres || '#' === adres ) { return; }

		/* tweede tik: naar de pagina */
		e.preventDefault();
		e.stopPropagation();
		window.location.href = adres;
	}, true );
} )();
</script>
	<?php
}, 99 );
