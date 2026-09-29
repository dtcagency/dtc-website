<?php
/**
 * Plugin Name: DTC video
 * Description: Achtergrondvideo's laden pas als ze in beeld komen. Tot die tijd staat er een stilstaand beeld uit de video zelf, zodat de pagina meteen compleet oogt. Scheelt op een case-pagina zo'n 18 MB bij het openen.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** het stilstaande beeld dat bij een video hoort, of leeg als het er niet is */
function dtc_video_poster( $mp4 ) {
	$pad = wp_parse_url( $mp4, PHP_URL_PATH );
	if ( ! $pad ) { return ''; }

	$poster = preg_replace( '/(-web)?\.mp4$/i', '-poster.webp', $pad );
	if ( $poster === $pad ) { return ''; }

	$uploads = wp_get_upload_dir();
	$deel    = strstr( $poster, '/uploads/' );
	if ( ! $deel ) { return ''; }
	$bestand = $uploads['basedir'] . substr( $deel, strlen( '/uploads' ) );
	if ( ! file_exists( $bestand ) ) { return ''; }

	return $uploads['baseurl'] . substr( $deel, strlen( '/uploads' ) );
}

add_filter( 'the_content', 'dtc_video_uitstellen', 1000 );
function dtc_video_uitstellen( $inhoud ) {
	if ( is_admin() || is_feed() || empty( $inhoud ) ) { return $inhoud; }
	if ( false === stripos( $inhoud, 'et_pb_section_video_bg' ) ) { return $inhoud; }

	return preg_replace_callback(
		'#<span class="et_pb_section_video_bg([^"]*)">(.*?)</span>#s',
		function ( $m ) {
			$blok = $m[2];
			if ( ! preg_match( '#<source[^>]+type="video/mp4"[^>]+src="([^"]+)"#i', $blok, $s ) ) {
				return $m[0];
			}
			$mp4    = $s[1];
			$poster = dtc_video_poster( $mp4 );

			/* De bron blijft gewoon staan, maar zonder autoplay en met
			   preload="none": dan haalt de browser er niets van op tot wij
			   play() aanroepen. Een <video> zonder src laten staan kan niet:
			   de mediaspeler van WordPress loopt daarop vast en breekt dan de
			   rest van de scripts op de pagina af.
			   Divi zet er ook een tweede bron bij met type webm maar hetzelfde
			   mp4-bestand; die kan weg. */
			$video = '<video class="dtc-video-lui" loop="loop" playsinline muted preload="none"'
				. ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . '>'
				. '<source type="video/mp4" src="' . esc_url( $mp4 ) . '" />'
				. '</video>';

			/* het stilstaande beeld ook als achtergrond, want Divi verbergt de
			   video zelf tot hij speelt */
			$stijl = $poster
				? ' style="background-image:url(' . esc_url( $poster ) . ');background-size:cover;background-position:center"'
				: '';

			return '<span class="et_pb_section_video_bg dtc-video-vak' . $m[1] . '"' . $stijl . '>' . $video . '</span>';
		},
		$inhoud
	);
}

/* Divi verbergt een videosectie tot de video speelt (et_pb_preload) en zet er
   een grijs vlak met een spinner overheen. Dat werkt niet meer als de video
   pas later laadt, dus dat nemen we hier over met het stilstaande beeld. */
add_filter( 'the_content', function ( $inhoud ) {
	if ( is_admin() || false === stripos( $inhoud, 'et_pb_preload' ) ) { return $inhoud; }
	return preg_replace_callback(
		'#class="([^"]*et_pb_section_video[^"]*)"#',
		function ( $m ) {
			return 'class="' . trim( preg_replace( '/\s*et_pb_preload\b/', '', $m[1] ) ) . '"';
		},
		$inhoud
	);
}, 1001 );

add_action( 'wp_footer', 'dtc_video_script', 6 );
function dtc_video_script() {
	if ( is_admin() ) { return; }
	?>
<style id="dtc-video">
.dtc-video-vak{background-repeat:no-repeat;visibility:visible!important}
.dtc-video-lui{opacity:0;transition:opacity .6s ease}
.dtc-video-lui.dtc-video-aan{opacity:1}
</style>
<script id="dtc-video-js">
( function () {
	var videos = [].slice.call( document.querySelectorAll( 'video.dtc-video-lui' ) );
	if ( ! videos.length ) { return; }

	/* De browser mag een gedempte video zonder aanraking afspelen, maar niet
	   elke browser doet dat even makkelijk. Daarom: eerst de eigenschappen
	   goed zetten, dan play() proberen, en anders nog eens zodra er genoeg
	   binnen is of bij de eerste aanraking. */
	function probeer( video ) {
		if ( ! video.paused ) { return; }
		var p = video.play();
		if ( p && p.catch ) { p.catch( function () { bijAanraking( video ); } ); }
	}

	function bijAanraking( video ) {
		if ( video.dataset.dtcGebaar ) { return; }
		video.dataset.dtcGebaar = '1';
		var eenmaal = function () { video.play().catch( function () {} ); };
		[ 'pointerdown', 'touchstart', 'keydown', 'wheel' ].forEach( function ( naam ) {
			addEventListener( naam, eenmaal, { once: true, passive: true } );
		} );
	}

	/* De video staat doorzichtig tot hij beeld geeft; daaronder ligt het
	   stilstaande frame. Divi start deze video's zelf ook (het heeft eigen
	   waypoints), en als dat eerder gebeurt dan dit script, is het
	   playing-event al voorbij en zouden we hem nooit meer laten zien.
	   Daarom op meerdere manieren tegelijk. */
	function toon( video ) {
		video.classList.add( 'dtc-video-aan' );
	}

	function houdInDeGaten( video ) {
		if ( ! video.paused && video.readyState > 2 ) { toon( video ); return; }
		[ 'playing', 'timeupdate', 'loadeddata', 'canplay' ].forEach( function ( naam ) {
			video.addEventListener( naam, function () {
				if ( ! video.paused || video.readyState > 2 ) { toon( video ); }
			}, { once: true } );
		} );
		/* en als laatste redmiddel gewoon na een seconde kijken */
		[ 900, 2500, 5000 ].forEach( function ( ms ) {
			setTimeout( function () { if ( ! video.paused ) { toon( video ); } }, ms );
		} );
	}

	/* Divi kan een video starten voordat dit script erbij is; daarom meteen
	   alle video's in de gaten houden, los van het laden. */
	videos.forEach( houdInDeGaten );

	function start( video ) {
		if ( video.dataset.dtcGestart ) { return; }
		video.dataset.dtcGestart = '1';

		/* muted als eigenschap, niet alleen als kenmerk: de mediaspeler van
		   WordPress zet het kenmerk soms om, en zonder demping weigert de
		   browser te starten. */
		video.muted = true;
		video.defaultMuted = true;
		video.playsInline = true;
		video.autoplay = true;

		houdInDeGaten( video );
		video.addEventListener( 'loadeddata', function () { probeer( video ); }, { once: true } );
		video.addEventListener( 'canplay', function () { probeer( video ); }, { once: true } );

		if ( 'auto' !== video.preload ) { video.preload = 'auto'; video.load(); }
		probeer( video );
	}

	/* wie data wil sparen of nauwelijks verbinding heeft, houdt het stilstaande beeld */
	var net = navigator.connection;
	if ( net && ( net.saveData || /2g/.test( net.effectiveType || '' ) ) ) { return; }

	if ( ! ( 'IntersectionObserver' in window ) ) {
		videos.forEach( start );
		return;
	}

	/* Twee stappen. Ruim voordat je er bent begint de video te laden, zodat
	   hij al klaarstaat; pas als hij echt in beeld komt gaat hij spelen. Zonder
	   die eerste stap zie je bij het scrollen eerst het stilstaande beeld
	   terwijl het bestand nog binnenkomt. */
	var vooruit = Math.max( 900, Math.round( window.innerHeight * 1.5 ) );

	var lader = new IntersectionObserver( function ( regels ) {
		regels.forEach( function ( regel ) {
			if ( ! regel.isIntersecting ) { return; }
			lader.unobserve( regel.target );
			var video = regel.target;
			if ( video.dataset.dtcGestart ) { return; }
			video.preload = 'auto';
			video.load();
		} );
	}, { rootMargin: vooruit + 'px 0px' } );

	var kijker = new IntersectionObserver( function ( regels ) {
		regels.forEach( function ( regel ) {
			if ( ! regel.isIntersecting ) { return; }
			kijker.unobserve( regel.target );
			start( regel.target );
		} );
	}, { rootMargin: '150px 0px' } );

	videos.forEach( function ( v ) { kijker.observe( v ); } );

	/* Het vooruit bufferen begint pas zodra iemand gaat scrollen. Wie de
	   pagina opent en blijft kijken naar wat er staat, haalt dus geen enkele
	   video op. Staat er al een video in beeld, dan telt dat als "onderweg". */
	var bufferenGestart = false;
	function begin() {
		if ( bufferenGestart ) { return; }
		bufferenGestart = true;
		videos.forEach( function ( v ) { lader.observe( v ); } );
	}
	addEventListener( 'scroll', begin, { once: true, passive: true } );
	addEventListener( 'wheel', begin, { once: true, passive: true } );
	addEventListener( 'touchstart', begin, { once: true, passive: true } );
	addEventListener( 'keydown', begin, { once: true } );

	/* En anders begint het bufferen vanzelf zodra de pagina klaar is met laden.
	   Wie meteen scrolt merkt daar niets van; wie eerst even kijkt heeft de
	   video al klaarstaan tegen de tijd dat hij erbij is. */
	if ( document.readyState === 'complete' ) {
		setTimeout( begin, 1500 );
	} else {
		addEventListener( 'load', function () { setTimeout( begin, 1500 ); } );
	}
} )();
</script>
	<?php
}
