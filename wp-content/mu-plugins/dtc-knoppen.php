<?php
/**
 * Plugin Name: DTC knoppen
 * Description: Eén maat en vorm voor alle knoppen. Ze liepen uiteen van 44 tot 62px hoog, met lettergroottes van 14 tot 18px en rondingen van 20 tot 999px; op de servicespagina stond de tekst bovendien in Divi's standaardblauw. Alles staat nu op 52px hoog, 16px tekst en een volledige pill.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-knoppen">
/* De maatvoering van de huisknop: 52px hoog, 16px tekst, volledige ronding.
   Deze knoppen zaten verspreid over losse codeblokken per pagina en groeiden
   daardoor uit elkaar. */
#page-container a.waaier__cta,
#page-container a.learn,
#page-container a.all,
#page-container a.hknop,
#page-container a.btn,
#page-container button.hbknop {
	min-height: 52px !important;
	font-size: 16px !important;
	line-height: 1.3 !important;
	border-radius: 100px !important;
	display: inline-flex !important;
	align-items: center !important;
	box-sizing: border-box;
}

/* De hoogte werd bepaald door het ronde pijltje erin: dat was 39, 42 of zelfs
   44px groot, met daarbovenop 6 tot 14px lucht. Eén maat voor het rondje en
   dezelfde lucht eromheen brengt elke knop op 52px. */
#page-container a.waaier__cta > span,
#page-container a.learn > span,
#page-container a.all > span,
#page-container a.hknop > span,
#page-container a.btn > span,
#page-container button.hbknop > span {
	height: 39px !important;
	min-height: 39px !important;
}
#page-container a.waaier__cta,
#page-container a.learn,
#page-container a.all,
#page-container a.hknop,
#page-container a.btn,
#page-container button.hbknop {
	padding-top: 6px !important;
	padding-bottom: 6px !important;
}

/* Op de servicespagina erfden twee knoppen de standaard linkkleur van Divi
   (#2ea3f2). Dat is een felblauw dat nergens in de huisstijl voorkomt en op
   het donkere vlak nauwelijks leesbaar is. */
#page-container a.learn,
#page-container a.learn:visited {
	color: #ffffff !important;
}

/* Eén pijlvariant, altijd links in een rondje. Op de methodepagina stond het
   pijltje rechts achter de tekst, bij 'view all work' op de casepagina's
   ontbrak het. */
#page-container button.hbknop {
	flex-direction: row-reverse;
	gap: 12px;
	padding-left: 6px !important;
	padding-right: 24px !important;
}
#page-container button.hbknop > span {
	width: 39px;
	border: 2px solid #c0ab94;
	border-radius: 72px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	transform: none !important;
}
#page-container a.all {
	gap: 12px;
	padding-left: 6px !important;
	padding-right: 24px !important;
}
#page-container a.all::before {
	content: "\24";
	font-family: 'ETmodules';
	font-size: 22px;
	line-height: 22px;
	width: 39px;
	height: 39px;
	flex: 0 0 39px;
	border: 2px solid #c0ab94;
	border-radius: 72px;
	box-sizing: border-box;
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

/* De twee juridische knoppen in de footer hadden geen enkele hoverstand: wie
   eroverheen ging, zag niets veranderen. De titel stond bovendien in puur
   zwart (#000) in plaats van de huiskleur. Nu hetzelfde gedrag als de andere
   knoppen: het pijlvlak wordt beige en de tekst kleurt mee. */
.et-l--footer .difl_advanced_blurb .df_ab_blurb_title {
	color: #2d2824 !important;
	transition: color .2s ease;
}
.et-l--footer .difl_advanced_blurb .et-pb-icon {
	transition: background-color .2s ease, color .2s ease;
}
.et-l--footer .difl_advanced_blurb:hover .df_ab_blurb_title {
	color: #8a7257 !important;
}
.et-l--footer .difl_advanced_blurb:hover .et-pb-icon {
	background-color: #c0ab94 !important;
	color: #2d2824 !important;
}

/* De knop in de menubalk was het enige element met een halve ronding (20px);
   daardoor oogde hij als een ander soort knop dan de rest. */
.et-l--header .df-menu-button {
	border-radius: 100px !important;
}
</style>
	<?php
}, 99 );
