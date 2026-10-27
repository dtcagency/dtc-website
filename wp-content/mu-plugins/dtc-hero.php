<?php
/**
 * Plugin Name: DTC Homepage
 * Description: Opmaakcorrecties op de homepage: de hero op elk schermformaat, en de tussenruimte in de fotosectie.
 * Version: 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * De homepage heeft twee hero's: een brede voor desktop (rij 0, met de
 * beeldkolommen ernaast) en een korte voor telefoon en tablet (rij 1 en 2).
 * Ze staan in de pagina onder elkaar en Divi zet met disabled_on de juiste
 * aan. Die twee samenvoegen ging niet: de rijen eronder zijn met negatieve
 * ruimte op elkaar afgestemd en de hero schoof er dan overheen.
 *
 * Wat hier gebeurt is dus per versie:
 *
 * - desktop: de titel stond op een vaste 100px en de kolom op 80px padding.
 *   Tussen 981px en 1400px werd de titel daardoor te groot voor zijn kolom.
 *   Nu schaalt hij mee; boven ~1280px blijft hij op de oorspronkelijke 100px.
 *
 * - telefoon en tablet: de titel stond op 60px en brak midden in "marketing"
 *   af, de rij begon tegen het menu aan en de knoptekst viel over twee
 *   regels. Dat is hieronder rechtgezet.
 */
add_action( 'wp_head', function () {
	if ( ! is_page( 30 ) ) {
		return;
	}
	$css = "
	/* ---------- de brede hero (desktop) ---------- */
	body.page-id-30 .et_pb_row_0 .et_pb_text_0,
	body.page-id-30 .et_pb_row_0 .et_pb_text_0 p,
	body.page-id-30 .et_pb_row_0 .et_pb_text_0 h1,
	body.page-id-30 .et_pb_row_0 .et_pb_text_0 h2{
		font-size:clamp(38px,7.8vw,100px)!important;
		line-height:.92em!important;
		overflow-wrap:break-word;
	}
	body.page-id-30 .et_pb_row_0 .et_pb_column_3{
		padding-left:clamp(20px,4.5vw,80px)!important;
		padding-right:clamp(20px,4.5vw,80px)!important;
	}
	body.page-id-30 .et_pb_row_0 .et_pb_text_1{width:min(100%,560px)!important}
	/* De knop stond op een vaste 240px en werd daardoor breder dan de kolom
	   op een smal scherm. width:auto rekte hem juist op tot de hele kolom,
	   dus hier de oorspronkelijke maat met een bovengrens. */
	body.page-id-30 .et_pb_row_0 .difl_advanced_blurb_0{width:min(240px,100%)!important}


	/* ---------- het blok 'we create a creative composition' ---------- */
	/* Dit blok breekt uit de Divi-kolom naar de volle schermbreedte met
	   left:50% plus een negatieve marge. Dat werkt alleen zolang de kolom
	   precies gecentreerd staat; op tablet is dat niet zo en schoof het blok
	   ruim 200px het scherm uit. Deze schrijfwijze rekent met de eigen
	   breedte en is niet afhankelijk van die aanname. */
	body.page-id-30 .waaier{
		left:auto!important;right:auto!important;
		margin-left:calc(50% - 50vw)!important;
		margin-right:calc(50% - 50vw)!important;
	}
	/* De tekstkolom kon niet krimpen (rasterkolommen hebben van zichzelf een
	   minimumbreedte gelijk aan hun inhoud) en duwde de waaier plat: op 1024px
	   hield die nog 198px over voor kaarten van 320px. */
	body.page-id-30 .waaier__inner > *{min-width:0}
	/* De kaarten stonden op vaste pixels, dus staken ze buiten hun kolom zodra
	   het scherm smaller werd. Nu zijn maat en plek een percentage van de
	   waaier zelf, met dezelfde verhoudingen als in het oorspronkelijke
	   ontwerp (320x500 op een vlak van 650x640). */
	body.page-id-30 .waaier__cards{height:auto!important;aspect-ratio:650/640}
	body.page-id-30 .waaier-card{
		width:49%!important;height:auto!important;aspect-ratio:320/500;
	}
	body.page-id-30 .waaier-card--1{left:0!important;top:0!important}
	body.page-id-30 .waaier-card--2{left:25%!important;top:8.6%!important}
	body.page-id-30 .waaier-card--3{left:45%!important;top:19.5%!important}
	/* De hoogte van het grijze vlak stond vast op 700px, waardoor er onder de
	   kleinere waaier een gat viel. */
	body.page-id-30 .waaier__inner{min-height:clamp(420px,36vw,700px)!important}
	/* Vanaf tablet mogen de kaarten groter en mag de waaier links een stukje
	   buiten het grijze blok vallen. Het blok stond op overflow:hidden - dat
	   was er voor de decoratieve cirkel rechtsboven, die maar voor een kwart
	   in beeld hoort. Die cirkel knipt zichzelf nu bij, zodat het blok het
	   overhangende deel van de eerste kaart niet meer afsnijdt. */
	@media(min-width:901px){
		body.page-id-30 .waaier__inner{overflow:visible!important}
		body.page-id-30 .waaier__decor{clip-path:inset(120px 80px 0 0)}
		/* het vak is hoger dan breed geworden, want grotere kaarten met
		   dezelfde trapsgewijze verspringing hebben meer hoogte nodig */
		/* De kaarten zijn breder gemaakt. Het vak eronder groeit mee: een
		   bredere kaart is ook hoger, en de derde kaart begint op 19,5% van
		   die hoogte - zonder die extra hoogte zou hij er onderuit steken.
		   De derde kaart schuift naar links mee, zodat hij binnen het vak
		   blijft (28% + 72% = precies de volle breedte). */
		body.page-id-30 .waaier__cards{aspect-ratio:650/858}
		body.page-id-30 .waaier-card{width:68%!important}
		body.page-id-30 .waaier-card--1{left:-21%!important;top:0!important}
		body.page-id-30 .waaier-card--2{left:2.5%!important;top:8.6%!important}
		body.page-id-30 .waaier-card--3{left:26%!important;top:19.5%!important}
	}
	/* Op de telefoon staat de waaier boven het grijze vlak. Het vlak begon
	   daar boven de kaarten, waardoor de waaier er middenin lag. Nu begint
	   het grijs halverwege de waaier, zodat de kaarten er overheen vallen.
	   29% van de blokhoogte komt op elke telefoonmaat vrijwel precies op het
	   midden van de waaier uit (op 390px: 337px tegenover 335px). */
	@media(max-width:900px){
		body.page-id-30 .waaier__inner{background:transparent!important}
		body.page-id-30 .waaier__inner::before{
			content:'';position:absolute;left:0;right:0;top:21%;bottom:0;
			background:#f4f3f1;border-radius:24px;z-index:0;
		}
		body.page-id-30 .waaier__cards,
		body.page-id-30 .waaier__content{position:relative;z-index:1}
		/* De kaarten stonden in een vak van 320x500 terwijl de campagne-
		   beelden zelf veel minder hoog zijn (ongeveer 0,85 breed op hoog).
		   Met background-size:contain bleef onderin elke kaart een strook
		   leeg; samen met de ruimte onder het vak gaf dat een gat van ruim
		   130px voor de kop. Elke kaart heeft nu de verhouding van zijn
		   eigen beeld, en het vak eronder is navenant korter. */
		body.page-id-30 .waaier-card--1{aspect-ratio:534/627!important}
		body.page-id-30 .waaier-card--2{aspect-ratio:585/668!important}
		body.page-id-30 .waaier-card--3{aspect-ratio:687/742!important}
		body.page-id-30 .waaier__cards{aspect-ratio:650/570!important}
	}

	/* De cijferkaarten zijn op de telefoon een zijwaartse slider. Die stond
	   binnen de marge van de pagina, waardoor de laatste kaart halverwege
	   werd afgesneden. Nu loopt de slider van rand tot rand door, met de
	   eerste kaart nog netjes op de marge van de site. */
	@media(max-width:767px){
		html body.page-id-30 .dtc-stats-wrapper{
			padding-left:0!important;padding-right:0!important;
			width:100vw!important;max-width:100vw!important;
			margin-left:calc(50% - 50vw)!important;margin-right:calc(50% - 50vw)!important;
		}
		html body.page-id-30 .dtc-stats-container{
			padding-left:5vw!important;padding-right:5vw!important;
			scroll-padding-left:5vw;
			scroll-snap-type:x proximity;
		}
		html body.page-id-30 .dtc-stat-card{scroll-snap-align:start}
	}

	/* De titel van de korte hero vulde op de telefoon maar de helft van de
	   kolom: 36px op een breedte van 351px. Hij mag daar fors groter; de
	   tablet (768-980px) houdt zijn eigen maat. */
	@media(max-width:767px){
		html body.page-id-30 .et_pb_row_1 .et_pb_text:not(.dtc-hero-mobiel),
		html body.page-id-30 .et_pb_row_1 .et_pb_text:not(.dtc-hero-mobiel) p,
		html body.page-id-30 .et_pb_row_1 .et_pb_text:not(.dtc-hero-mobiel) h1,
		html body.page-id-30 .et_pb_row_1 .et_pb_text:not(.dtc-hero-mobiel) h2{
			font-size:clamp(48px,17.4vw,134px)!important;
			line-height:.92em!important;
		}
	}

	/* Op de telefoon bleef de waaier klein: de kaarten stonden op 49% van een
	   vak dat maar 303px breed was. Ze mogen daar groter, met dezelfde
	   trapsgewijze verspringing. Het vak wordt hoger meegerekend, anders
	   steekt de derde kaart er onderuit. */
	@media(max-width:767px){
		/* het blok mag de kaarten laten overhangen, maar de sectie eromheen
		   knipt af: anders steken de draaiende kaarten en de decoratieve
		   cirkel buiten het scherm en kun je de pagina opzij schuiven */
		body.page-id-30 .waaier{overflow-x:clip!important}
		body.page-id-30 .waaier__inner{padding:40px 16px!important;overflow:visible!important}
		/* de waaier loopt van rand tot rand, zodat de kaarten fors groter kunnen */
		/* Het vak was op de hoogte van kaarten van 320x500 gerekend. Sinds
		   elke kaart de verhouding van zijn eigen beeld heeft, is het vak
		   korter nodig; anders blijft er een gat van bijna 200px tussen de
		   waaier en de kop eronder. */
		body.page-id-30 .waaier__cards{
			aspect-ratio:650/573!important;
			width:100vw!important;max-width:100vw!important;
			margin-left:calc(50% - 50vw)!important;margin-right:calc(50% - 50vw)!important;
		}
		body.page-id-30 .waaier-card{width:68%!important}
		body.page-id-30 .waaier-card--1{left:0!important;top:0!important}
		body.page-id-30 .waaier-card--2{left:16%!important;top:9%!important}
		body.page-id-30 .waaier-card--3{left:32%!important;top:20%!important}
	}
	/* op een grote telefoon hoeft de waaier niet mee te groeien tot het
	   hele scherm; daar houdt hij een bovengrens */
	@media(min-width:481px) and (max-width:767px){
		body.page-id-30 .waaier__cards{
			width:100%!important;max-width:560px!important;
			margin-left:auto!important;margin-right:auto!important;
		}
	}
	/* De titel stond op 75px en sprong bij 980px ineens naar 36px. */
	body.page-id-30 .waaier__title{
		font-size:clamp(30px,3.9vw,75px)!important;line-height:.85!important;
	}
	
	/* Tussen 768 en 980px geeft Divi de rij een vaste breedte van 1400px met
	   een negatieve rechtermarge. De uitbraak rekent met de helft van de
	   ouderbreedte en kwam daardoor 283px te ver naar rechts. In die band
	   begint de rij al bij de linkerrand, dus is er niets te compenseren. */
	@media(min-width:768px) and (max-width:980px){
		body.page-id-30 .waaier{
			margin-left:0!important;margin-right:0!important;width:100vw!important;
		}
	}
	/* ---------- de korte hero (telefoon en tablet) ---------- */
	@media(max-width:980px){
		/* de rij begon bovenaan de pagina, dus onder de zwevende menubalk */
		body.page-id-30 .et_pb_row_1{padding-top:16vh!important}
		/* 60px was te groot: 'marketing' brak middenin af */
		body.page-id-30 .et_pb_row_1 .et_pb_text:not(.dtc-hero-mobiel),
		body.page-id-30 .et_pb_row_1 .et_pb_text:not(.dtc-hero-mobiel) p,
		body.page-id-30 .et_pb_row_1 .et_pb_text:not(.dtc-hero-mobiel) h1,
		body.page-id-30 .et_pb_row_1 .et_pb_text:not(.dtc-hero-mobiel) h2{
			font-size:clamp(34px,9.2vw,72px)!important;
			line-height:.95em!important;
			overflow-wrap:break-word;
			hyphens:none;
		}
		/* de introtekst die hier is bijgezet houdt zijn eigen maat */
		body.page-id-30 .dtc-hero-mobiel,
		body.page-id-30 .dtc-hero-mobiel p,
		body.page-id-30 .dtc-hero-mobiel h3{
			font-size:18px!important;line-height:1.6em!important;
		}
		body.page-id-30 .dtc-hero-mobiel h3{margin-bottom:14px}
		body.page-id-30 .dtc-hero-mobiel{text-align:left}
		/* de knoptekst viel over twee regels */
		body.page-id-30 .et_pb_row_2 .difl_advanced_blurb .df_ab_blurb_title{
			white-space:nowrap;
		}
		body.page-id-30 .et_pb_row_2{padding-top:0!important}
	}

	/* ---------- hero: het beeldenveld links beweegt ----------
	   Uit de websitecheck: 'element links nog niet goed benut, bijv.
	   animeren'. De drie kolommen met casebeelden drijven nu langzaam tegen
	   elkaar in, elk op een eigen tempo, zodat het nooit synchroon of
	   mechanisch oogt. Er wordt met de losse eigenschap 'translate' gewerkt
	   in plaats van 'transform': Divi zet op deze kolommen zelf al een
	   transform voor het scroll-effect, en zo komen de twee bovenop elkaar
	   in plaats van dat de ene de andere wegdrukt. */
	@keyframes dtc-drijf-op   { 0%,100% { translate: 0 0 } 50% { translate: 0 -40px } }
	@keyframes dtc-drijf-neer { 0%,100% { translate: 0 0 } 50% { translate: 0 40px } }
	@media (min-width: 981px) and (prefers-reduced-motion: no-preference) {
		body.page-id-30 .et_pb_row:has(> .et_pb_column_1_6) > .et_pb_column_1_6:nth-child(1) {
			animation: dtc-drijf-op 15s ease-in-out infinite;
		}
		body.page-id-30 .et_pb_row:has(> .et_pb_column_1_6) > .et_pb_column_1_6:nth-child(2) {
			animation: dtc-drijf-neer 18s ease-in-out infinite;
		}
		body.page-id-30 .et_pb_row:has(> .et_pb_column_1_6) > .et_pb_column_1_6:nth-child(3) {
			animation: dtc-drijf-op 21s ease-in-out infinite;
		}
	}

	/* ---------- hero: de boodschap moet scanbaar zijn ----------
	   '360° marketing agency' is een categorie; de eigenlijke boodschap is
	   'design for attention, build for action'. Die stond op 22px in een
	   grijs (#333) dat nergens anders in de huisstijl voorkomt, en verdween
	   daardoor tussen de grote titel en de lopende tekst. */
	@media (min-width: 981px) {
		body.page-id-30 .et_pb_row:has(> .et_pb_column_1_6) h3 {
			font-size: 30px !important;
			line-height: 1.15 !important;
			color: #2d2824 !important;
			margin-bottom: 18px !important;
		}
	}

	/* ---------- merken: meer contrast ----------
	   Uit de check: 'mist wat contrast / pop'. Het logoveld was één grijze
	   afbeelding op wit en viel weg naast de tekst. Het staat nu op een
	   donker vlak in de huiskleur, met de logo's in wit: een duidelijk
	   focuspunt dat aansluit bij de donkere blokken elders op de site. */
	body.page-id-30 .et_pb_image:has(img[src*='Vector_Logos']) {
		background: #2d2824;
		border-radius: 24px;
		padding: clamp( 28px, 4vw, 56px ) !important;
		box-shadow: 0 2px 10px rgba(45,40,36,.08), 0 22px 50px rgba(45,40,36,.18);
	}
	body.page-id-30 img[src*='Vector_Logos'] {
		filter: brightness( 0 ) invert( 1 );
		opacity: .92;
	}

	/* ---------- strategie-blok: tekst op ooghoogte met het beeld ----------
	   De tekstkolom stond bovenaan uitgelijnd terwijl het beeld ernaast veel
	   hoger is; daardoor hing de tekst tegen de bovenrand. De rij is nu een
	   flexrij die beide kolommen centreert. De onderruimte bij de tekstkolom
	   compenseert het onderschrift onder het beeld (explore our site with a
	   tune on) dat
	   onder het beeld staat: zonder die correctie centreert de tekst op de
	   hele kolom in plaats van op het beeld zelf.
	   De alinea houdt op desktop zijn eigen breedte (80% van de kolom), zodat
	   de regels niet te lang worden. */
	@media(min-width:981px){
		body.page-id-30 #strategy{display:flex;align-items:center}
		body.page-id-30 #strategy > .et_pb_column:first-child{margin-bottom:210px}
	}
	/* Op de telefoon staat de alinea juist wel over de volle breedte; op 80%
	   bleef er rechts een strook van 70px ongebruikt. */
	@media(max-width:767px){
		body.page-id-30 #strategy .et_pb_text_6{width:100%!important;max-width:100%!important}
	}

	/* ---------- gelijke zijmarges op tablet en telefoon ---------- */
	/* De rijen hielden er ieder hun eigen maat op na. Op tablet begon de ene
	   rij op 38px en de andere op 130px, op telefoon liep het van 10 tot 46px.
	   Alles staat nu op dezelfde marge als de rest van de site: 8vw op tablet
	   (61px bij 768) en 5vw op telefoon (20px bij 390). */
	@media(max-width:980px){
		body.page-id-30 .et_pb_row{
			width:auto!important;max-width:100%!important;
			margin-left:0!important;margin-right:0!important;
			padding-left:8vw!important;padding-right:8vw!important;
		}
		/* Deze twee lopen met opzet door tot de rand: de merkencarrousel en
		   de rij met de cijferkaarten schuiven horizontaal. */
		body.page-id-30 .et_pb_row_4,
		body.page-id-30 .et_pb_row:has(.dtc-stats-wrapper){padding-left:0!important;padding-right:0!important}
		/* De waaier breekt zelf uit naar de volle schermbreedte; die rij moet
		   daarom vlak blijven, anders komt de uitbraak scheef te staan. */
		body.page-id-30 .et_pb_row_7{padding-left:0!important;padding-right:0!important}
		body.page-id-30 .waaier{padding-left:8vw!important;padding-right:8vw!important}
		/* De kolom van de videorij heeft zelf nog 5% linkerruimte; samen met
		   die van de rij sprong de inhoud daar dubbel in. */
		body.page-id-30 .et_pb_row_6 .et_pb_column{
			padding-left:0!important;padding-right:0!important;
		}
		/* Het slotblok brengt zijn eigen zijruimte mee (het is een globaal
		   blok dat op meer pagina's staat); die van de rij zou daar bovenop
		   komen. */
		body.page-id-30 #contact{padding-left:0!important;padding-right:0!important}
	}
	@media(max-width:767px){
		body.page-id-30 .et_pb_row{
			padding-left:5vw!important;padding-right:5vw!important;
		}
		body.page-id-30 .et_pb_row_4{padding-left:5vw!important;padding-right:0!important}
		body.page-id-30 .et_pb_row_7,
		body.page-id-30 .et_pb_row:has(.dtc-stats-wrapper){padding-left:0!important;padding-right:0!important}
		body.page-id-30 .waaier{padding-left:5vw!important;padding-right:5vw!important}
		body.page-id-30 #contact{padding-left:0!important;padding-right:0!important}
	}

	/* ---------- fotosectie: overal dezelfde tussenruimte ---------- */
	/* De drie kaarten staan 20px uit elkaar, maar de rij eronder begon pas
	   70px lager, want elke Divi-rij heeft die ruimte als blokafstand. Binnen
	   deze sectie horen de beelden als een raster te lezen, dus verticaal
	   dezelfde 20px. De 70px onder de onderste rij blijft staan: dat is de
	   afstand naar het volgende blok. */
	body.page-id-30 .et_pb_row:has(.dtc-stats-wrapper){
		padding-bottom:20px!important;
	}
	/* de kaarten stonden op smallere schermen 15px uit elkaar */
	body.page-id-30 .dtc-stats-container{gap:20px!important}
	/* tussen 701 en 980px hield de kaartenrij 6vw aan de zijkant aan en het
	   blok eronder 12vw, waardoor de randen niet op een lijn lagen */
	/* De kaartenrij schuift horizontaal en had daarvoor nog 20px eigen ruimte
	   aan weerszijden. De eerste kaart begon daardoor 20px verder dan de tekst
	   erboven, terwijl de rij die ruimte zelf al levert. */
	@media(max-width:980px){
		body.page-id-30 .dtc-stats-container{
			padding-left:0!important;padding-right:0!important;
		}
	}
	/* Op tablet hielden de cijferkaarten de telefoonmaat aan: 90% van de rij,
	   oftewel 581px bij een scherm van 768. Daardoor paste er nog maar één
	   kaart in beeld en viel de rij uit de toon met de rest van de pagina.
	   Twee kaarten met een randje van de derde leest als een rij. */
	@media(min-width:701px) and (max-width:980px){
		body.page-id-30 .dtc-stat-card{flex:0 0 46%!important}
	}

	/* De kaartenrij hield 12vw aan, van voor de uitlijnronde hierboven. Nu
	   dezelfde 8vw als de rest, anders staat hij op tablet ingesprongen. */
	@media(min-width:701px) and (max-width:980px){
		body.page-id-30 .dtc-stats-wrapper{
			padding-left:8vw!important;padding-right:8vw!important;
		}
	}
	
	@media(max-width:480px){
		body.page-id-30 .et_pb_row_1{padding-top:14vh!important}
		body.page-id-30 .et_pb_row_2 .difl_advanced_blurb .df_ab_blurb_title{
			font-size:13px;
		}
	}

	/* 'brands we work with': dit blok had helemaal geen ruimte boven zich,
	   waardoor het tegen de fotoband erboven aan plakte. De logo's zitten in
	   een enkele svg, dus de ruimte tussen de logo's zelf ligt vast; wat hier
	   kan is lucht eromheen. */
	body.page-id-30 .et_pb_row_12{
		padding-top:70px!important;
		padding-bottom:110px!important;
	}
	body.page-id-30 .et_pb_row_12 .et_pb_image_11{
		padding:10px 0 10px 24px!important;
	}
	@media(max-width:980px){
		body.page-id-30 .et_pb_row_12{
			padding-top:50px!important;
			padding-bottom:70px!important;
		}
		body.page-id-30 .et_pb_row_12 .et_pb_image_11{padding:8px 0 0!important}
	}
	@media(max-width:767px){
		body.page-id-30 .et_pb_row_12{
			padding-top:36px!important;
			padding-bottom:52px!important;
		}
	}
	";
	echo '<style id="dtc-hero">' . preg_replace( '/\s+/', ' ', $css ) . '</style>';
}, 20 );
