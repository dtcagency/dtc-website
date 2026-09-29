<?php
/**
 * Plugin Name: DTC Lowercase
 * Description: Toont koppen en labels in kleine letters; lopende tekst houdt zijn eigen schrijfwijze. Bewust via CSS: de tekst zelf blijft ongewijzigd, dus zoekmachines, screenreaders en kopieren-plakken houden de juiste schrijfwijze.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', function () {
	if ( is_admin() ) {
		return;
	}
	?>
<style id="dtc-lowercase">
/* text-transform erft door, dus een regel op de buitenste wrapper raakt
   header, inhoud en footer in een keer. */
#page-container { text-transform: lowercase; }

/* Deze drie zetten zelf uppercase en zouden anders hoofdletters houden:
   de dienst-tags, de meta-regel op de hub-kaarten en de labels in het
   AEO-artikel. */
#page-container .dtc-sv .tag,
#page-container .dtc-hub .hmeta,
#page-container .dtc-aeo .cc .lab { text-transform: lowercase; }

/* Lopende tekst houdt zijn eigen schrijfwijze: een zin na een punt hoort met
   een hoofdletter te beginnen. Koppen blijven wel in kleine letters. */
#page-container p,
#page-container li,
#page-container dd,
#page-container dt,
#page-container td,
#page-container th,
#page-container blockquote,
#page-container figcaption,
#page-container .dtc-sv .cap,
#page-container .dtc-apart .qualities .d { text-transform: none; }

/* Deze staan als <p> in de opmaak maar zijn in feite een kop of label,
   dus die blijven klein. */
#page-container .sub,
#page-container .ey,
#page-container .pintro,
#page-container .statement,
#page-container .kop,
#page-container .mega,
#page-container .wm,
#page-container .q,
#page-container .et_pb_text h1,
#page-container .et_pb_text h2,
#page-container .et_pb_text h3,
#page-container .et_pb_text h4 { text-transform: lowercase; }

/* Twee uitzonderingen waar kleine letters echt fout zouden zijn. */
#page-container input,
#page-container textarea,
#page-container select,
#page-container [type="email"],
#page-container code,
#page-container pre { text-transform: none; }
</style>
	<?php
}, 99 );
