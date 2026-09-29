<?php
/**
 * Plugin Name: DTC paginaovergang
 * Description: Bij het klikken op een link vervaagt de pagina rustig in de volgende in plaats van wit te knipperen. De menubalk en de voettekst blijven daarbij staan. Werkt in Chrome, Edge en Safari; in browsers die het nog niet kennen laadt de pagina gewoon zoals eerst.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-overgang">
/* Zet de overgang tussen twee pagina's aan. Browsers die dit niet kennen
   negeren de regel en laden de pagina zoals altijd. */
@view-transition { navigation: auto; }

@media (prefers-reduced-motion: reduce) {
	@view-transition { navigation: none; }
}

@keyframes dtc-overgang-in {
	from { opacity: 0; transform: translateY(16px); }
}
@keyframes dtc-overgang-uit {
	to { opacity: 0; transform: translateY(-8px); }
}

::view-transition-old(root) {
	animation: dtc-overgang-uit .26s cubic-bezier(.4,0,1,1) both;
}
::view-transition-new(root) {
	animation: dtc-overgang-in .42s cubic-bezier(.22,.61,.36,1) both;
}

/* De menubalk en de voettekst staan op elke pagina op dezelfde plek. Door ze
   een eigen naam te geven blijven ze staan in plaats van mee te vervagen;
   dat is precies het verschil tussen "de pagina laadt opnieuw" en "ik blijf
   op dezelfde site". */
.et-l--header .et_pb_section { view-transition-name: dtc-menubalk; }
.et-l--footer { view-transition-name: dtc-voet; }

::view-transition-group(dtc-menubalk),
::view-transition-group(dtc-voet) { animation-duration: .3s; }

::view-transition-old(dtc-menubalk),
::view-transition-new(dtc-menubalk),
::view-transition-old(dtc-voet),
::view-transition-new(dtc-voet) { animation: none; mix-blend-mode: normal; }

/* De momentopname van de oude pagina mag niet blijven hangen aan de
   scrollpositie van de nieuwe. */
::view-transition-group(root) { animation-duration: .42s; }
</style>
	<?php
}, 2 );
