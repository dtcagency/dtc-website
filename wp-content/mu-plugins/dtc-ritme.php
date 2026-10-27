<?php
/**
 * Plugin Name: DTC ritme
 * Description: Eén afstand onder alinea's. De losse codeblokken per pagina hanteerden elk hun eigen maat: 6, 14, 18, 24, 26, 34, 40 en 46px door elkaar. Alles wat een eigen ondermarge had gaat naar 26px. Alinea's zonder marge blijven zoals ze zijn: die zitten in blokken die hun ruimte met gap of padding regelen, en een extra marge zou dat ritme juist breken.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	?>
<script id="dtc-ritme">
( function () {
	var MAAT = 26;

	function loop() {
		var alinea = document.querySelectorAll( '#page-container p' );
		for ( var i = 0; i < alinea.length; i++ ) {
			var p = alinea[ i ];
			var r = p.getBoundingClientRect();
			if ( r.height < 8 || r.width < 60 ) { continue; }
			var mb = parseFloat( window.getComputedStyle( p ).marginBottom );
			if ( ! mb || mb < 3 ) { continue; }          /* ruimte komt van de ouder */
			if ( Math.abs( mb - MAAT ) < 1 ) { continue; }
			p.style.setProperty( 'margin-bottom', MAAT + 'px', 'important' );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', loop );
	} else {
		loop();
	}
	window.addEventListener( 'load', function () { setTimeout( loop, 400 ); } );
} )();
</script>
	<?php
}, 99 );
