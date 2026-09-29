<?php
/**
 * Plugin Name: dtc agency
 * Description: Het eigen beheerscherm van dtc: een tab per pagina waar de teksten en afbeeldingen van de site aangepast kunnen worden, zonder de Divi-editor te openen. De instellingen van de inlogpagina hangen hier als subpagina onder.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const DTC_TK_CAP  = 'edit_pages';
const DTC_TK_SLUG = 'dtc-teksten';

/* ============================================================ pagina's */

/** de pagina's die in de tabs komen, gesplitst in hoofdpagina's en overige */
function dtc_tk_paginas( $groep = 'alles' ) {
	$voorkeur = array( 'home', 'about', 'our-vision', 'our-method', 'team', 'services', 'hubspot', 'insights', 'contact' );

	$paginas = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	$hubspot = get_page_by_path( 'hubspot' );
	$hub_id  = $hubspot ? $hubspot->ID : 0;

	$eerst = array();
	$hubs  = array();
	$rest  = array();
	foreach ( $paginas as $p ) {
		$i = array_search( $p->post_name, $voorkeur, true );
		if ( false !== $i ) {
			$eerst[ $i ] = $p;
		} elseif ( $hub_id && (int) $p->post_parent === $hub_id ) {
			$hubs[] = $p;
		} else {
			$rest[] = $p;
		}
	}
	ksort( $eerst );

	$hoofd = array_merge( array_values( $eerst ), $hubs );
	$rest  = array_merge( $rest, dtc_tk_lay_outs() );

	if ( 'hoofd' === $groep ) { return $hoofd; }
	if ( 'overig' === $groep ) { return $rest; }
	return array_merge( $hoofd, $rest );
}

/** globale blokken en de footer van de theme builder: die tekst staat op elke pagina */
function dtc_tk_lay_outs() {
	global $wpdb;
	$ids = array();

	$inhouden = $wpdb->get_col( "SELECT post_content FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish'" );
	foreach ( $inhouden as $c ) {
		if ( preg_match_all( '/global_module="(\d+)"/', $c, $m ) ) {
			foreach ( $m[1] as $id ) { $ids[ (int) $id ] = true; }
		}
	}

	$tb = get_posts( array(
		'post_type'      => array( 'et_footer_layout', 'et_header_layout' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	) );
	foreach ( $tb as $p ) { $ids[ $p->ID ] = true; }

	$uit = array();
	foreach ( array_keys( $ids ) as $id ) {
		$p = get_post( $id );
		if ( $p && ! empty( $p->post_content ) ) { $uit[] = $p; }
	}
	return $uit;
}

/* ============================================================ teksten uit de inhoud halen */

/** de modules waar tekst in staat */
function dtc_tk_modules( $inhoud ) {
	$modules = array();
	if ( preg_match_all( '/\[(et_pb_fullwidth_code|et_pb_code|et_pb_text)\b[^\]]*\](.*?)\[\/\1\]/s', $inhoud, $sets, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) ) {
		foreach ( $sets as $set ) {
			$modules[] = array( 'html' => $set[2][0], 'start' => $set[2][1] );
		}
	}
	return $modules;
}

/** lege elementen: die sluiten niet */
function dtc_tk_leeg_element( $naam ) {
	return in_array( $naam, array( 'br', 'img', 'hr', 'input', 'source', 'meta', 'link', 'path', 'circle', 'polyline', 'line', 'use', 'col', 'area', 'embed', 'track', 'wbr' ), true );
}

/** staat er, los van entiteiten als &#x24;, echte tekst in? */
function dtc_tk_heeft_letters( $tekst ) {
	$kaal = preg_replace( '/&[#a-zA-Z0-9]+;/', '', $tekst );
	return (bool) preg_match( '/\p{L}/u', $kaal );
}

/**
 * Alle bewerkbare teksten van een pagina.
 * Geeft een lijst terug met: offset (in post_content), tekst, kop (voor de groep), soort.
 */
function dtc_tk_teksten( $inhoud ) {
	$uit = array();

	foreach ( dtc_tk_modules( $inhoud ) as $mod ) {
		$html  = $mod['html'];
		$start = $mod['start'];
		$kop   = '';
		$stapel = array();
		$laatste = null; // index in $uit van het vorige tekstveld in deze module
		$laatste_eind = -1;

		// alles in stukjes hakken: commentaar, tags en tekst
		$re = '/<!--.*?-->|<\/([a-zA-Z0-9]+)\s*>|<([a-zA-Z0-9]+)((?:"[^"]*"|\'[^\']*\'|[^>"\'])*)>|[^<]+/s';
		if ( ! preg_match_all( $re, $html, $stukken, PREG_OFFSET_CAPTURE | PREG_SET_ORDER ) ) { continue; }

		foreach ( $stukken as $stuk ) {
			$ruw = $stuk[0][0];
			$pos = $stuk[0][1];

			// commentaar overslaan
			if ( 0 === strpos( $ruw, '<!--' ) ) { continue; }

			// sluitende tag
			if ( isset( $stuk[1] ) && '' !== $stuk[1][0] && -1 !== $stuk[1][1] ) {
				$naam = strtolower( $stuk[1][0] );
				for ( $i = count( $stapel ) - 1; $i >= 0; $i-- ) {
					if ( $stapel[ $i ] === $naam ) { $stapel = array_slice( $stapel, 0, $i ); break; }
				}
				continue;
			}

			// openende tag
			if ( isset( $stuk[2] ) && '' !== $stuk[2][0] && -1 !== $stuk[2][1] ) {
				$naam = strtolower( $stuk[2][0] );
				$attr = isset( $stuk[3] ) ? $stuk[3][0] : '';
				if ( ! dtc_tk_leeg_element( $naam ) && '/' !== substr( rtrim( $attr ), -1 ) ) {
					$stapel[] = $naam;
				}
				continue;
			}

			// tekst
			$tekst = $ruw;
			$ouder = $stapel ? end( $stapel ) : '';

			if ( in_array( 'style', $stapel, true ) || in_array( 'script', $stapel, true ) ) { continue; }
			if ( '' === trim( $tekst ) ) { continue; }
			if ( false !== strpos( $tekst, '{' ) || false !== strpos( $tekst, '}' ) ) { continue; }
			if ( ! dtc_tk_heeft_letters( $tekst ) ) { continue; }
			// losse shortcodes (zoals [dtc_vacatures]) zijn geen tekst
			if ( preg_match( '/^\s*\[[a-z0-9_\-]+[^\]]*\]\s*$/i', $tekst ) ) { continue; }

			// tekst die alleen door een <br> van de vorige gescheiden is: samenvoegen
			if ( null !== $laatste && $laatste_eind > -1 ) {
				$tussen = substr( $html, $laatste_eind, $pos - $laatste_eind );
				if ( '' !== $tussen && preg_match( '/^(\s*<br\s*\/?>\s*)+$/i', $tussen ) ) {
					$vorig = $uit[ $laatste ];
					$uit[ $laatste ]['tekst'] = substr( $html, $vorig['offset'] - $start, ( $pos + strlen( $tekst ) ) - ( $vorig['offset'] - $start ) );
					$laatste_eind = $pos + strlen( $tekst );
					continue;
				}
			}

			if ( in_array( $ouder, array( 'h1', 'h2', 'h3' ), true ) ) {
				$kop = trim( html_entity_decode( wp_strip_all_tags( $tekst ), ENT_QUOTES, 'UTF-8' ) );
			}

			$uit[] = array(
				'offset' => $start + $pos,
				'tekst'  => $tekst,
				'kop'    => $kop,
				'soort'  => $ouder,
			);
			$laatste      = count( $uit ) - 1;
			$laatste_eind = $pos + strlen( $tekst );
		}
	}

	return $uit;
}

/** shortcodes die op de pagina staan en dus elders vandaan komen */
function dtc_tk_shortcodes( $inhoud ) {
	$gevonden = array();
	foreach ( dtc_tk_modules( $inhoud ) as $mod ) {
		if ( preg_match_all( '/\[([a-z][a-z0-9_\-]*)[^\]]*\]/i', $mod['html'], $m ) ) {
			foreach ( $m[1] as $naam ) {
				if ( shortcode_exists( $naam ) ) { $gevonden[ $naam ] = true; }
			}
		}
	}
	return array_keys( $gevonden );
}

/* ============================================================ opslaan */

add_action( 'admin_post_dtc_tk_opslaan', 'dtc_tk_opslaan' );
function dtc_tk_opslaan() {
	if ( ! current_user_can( DTC_TK_CAP ) ) { wp_die( 'Geen rechten.' ); }
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	check_admin_referer( 'dtc_tk_' . $post_id );

	$post = get_post( $post_id );
	if ( ! $post ) { wp_die( 'Pagina niet gevonden.' ); }

	$inhoud = $post->post_content;
	$velden = isset( $_POST['veld'] ) && is_array( $_POST['veld'] ) ? wp_unslash( $_POST['veld'] ) : array();
	$hashes = isset( $_POST['hash'] ) && is_array( $_POST['hash'] ) ? wp_unslash( $_POST['hash'] ) : array();

	// de teksten opnieuw uitlezen: dat is de enige betrouwbare bron voor de
	// originele tekst (een verborgen veld raakt onderweg zijn entiteiten kwijt)
	$huidig = array();
	foreach ( dtc_tk_teksten( $inhoud ) as $t ) { $huidig[ (int) $t['offset'] ] = $t['tekst']; }

	// van achter naar voren vervangen, dan blijven de posities kloppen
	$sleutels = array_map( 'intval', array_keys( $velden ) );
	rsort( $sleutels, SORT_NUMERIC );

	$gewijzigd = 0;
	$mislukt   = 0;

	foreach ( $sleutels as $offset ) {
		if ( ! isset( $huidig[ $offset ] ) ) { $mislukt++; continue; }
		$origineel = $huidig[ $offset ];

		// staat er nog hetzelfde als toen het formulier werd opgebouwd?
		if ( isset( $hashes[ $offset ] ) && md5( $origineel ) !== $hashes[ $offset ] ) { $mislukt++; continue; }

		$nieuw = (string) $velden[ $offset ];
		$nieuw = str_replace( array( "\r\n", "\r" ), "\n", $nieuw );
		$nieuw = htmlspecialchars( $nieuw, ENT_NOQUOTES, 'UTF-8', false );
		$nieuw = str_replace( "\n", '<br />', $nieuw );

		// alleen echt gewijzigde tekst terugschrijven (&#8217; en ’ zijn hetzelfde)
		if ( dtc_tk_kaal( $nieuw ) === dtc_tk_kaal( $origineel ) ) { continue; }

		if ( substr( $inhoud, $offset, strlen( $origineel ) ) !== $origineel ) { $mislukt++; continue; }

		$inhoud = substr_replace( $inhoud, $nieuw, $offset, strlen( $origineel ) );
		$gewijzigd++;
	}

	if ( $gewijzigd ) {
		wp_save_post_revision( $post_id );
		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array( 'post_content' => $inhoud, 'post_modified' => current_time( 'mysql' ), 'post_modified_gmt' => current_time( 'mysql', 1 ) ),
			array( 'ID' => $post_id )
		);
		clean_post_cache( $post_id );
		dtc_tk_leeg_divi_cache( $post_id );
		dtc_tk_markeer( $post_id );
	}

	wp_safe_redirect( add_query_arg( array(
		'page'      => DTC_TK_SLUG,
		'pagina'    => $post_id,
		'gewijzigd' => $gewijzigd,
		'mislukt'   => $mislukt,
	), admin_url( 'admin.php' ) ) );
	exit;
}

/** tekst zonder entiteiten en zonder losse regeleindes, om te vergelijken */
function dtc_tk_kaal( $tekst ) {
	$t = str_replace( array( '<br />', '<br>', '<br/>' ), "\n", $tekst );
	return html_entity_decode( $t, ENT_QUOTES, 'UTF-8' );
}

/**
 * Noteert dat deze pagina hier is bewerkt. De synchronisatie met de lokale kopie
 * leest dit uit, zodat een push niet zomaar over andermans wijziging heen schrijft.
 */
function dtc_tk_markeer( $post_id ) {
	$gebruiker = wp_get_current_user();
	update_post_meta( $post_id, '_dtc_tk_bewerkt', current_time( 'mysql' ) );
	update_post_meta( $post_id, '_dtc_tk_bewerkt_door', $gebruiker ? $gebruiker->user_login : 'onbekend' );
}

/** Divi bewaart per pagina een css-bestand; dat moet weg na een wijziging */
function dtc_tk_leeg_divi_cache( $post_id ) {
	$map = WP_CONTENT_DIR . '/et-cache/' . (int) $post_id;
	if ( ! is_dir( $map ) ) { return; }
	foreach ( (array) glob( $map . '/*' ) as $bestand ) {
		if ( is_file( $bestand ) ) { @unlink( $bestand ); }
	}
	@rmdir( $map );
}


/* ============================================================ afbeeldingen */

const DTC_TK_TYPES = 'jpg|jpeg|png|gif|webp|avif|svg|mp4|webm|m4v';

/**
 * Alle afbeeldingen (en video's) die op een pagina gebruikt worden, gegroepeerd per url.
 * Kijkt zowel naar de html in codemodules als naar de instellingen van Divi-modules
 * (src, background_image, image, logo, background_video_mp4) en naar url() in css.
 */
function dtc_tk_afbeeldingen( $inhoud ) {
	$types = DTC_TK_TYPES;
	$vondsten = array();

	// 1. alles wat in een attribuut of css-url staat
	$patronen = array(
		'/(?:src|image|logo|background_image|background_video_mp4|background_image_tablet|background_image_phone|thumbnail|poster)\s*=\s*"([^"]+\.(?:' . $types . ')(?:\?[^"]*)?)"/i',
		'/url\(\s*[\'"]?([^\'")]+\.(?:' . $types . ')(?:\?[^\'")]*)?)[\'"]?\s*\)/i',
	);

	foreach ( $patronen as $re ) {
		if ( preg_match_all( $re, $inhoud, $m ) ) {
			foreach ( $m[1] as $url ) {
				$url = trim( $url );
				if ( '' === $url ) { continue; }
				if ( ! isset( $vondsten[ $url ] ) ) { $vondsten[ $url ] = 0; }
				$vondsten[ $url ]++;
			}
		}
	}

	$uit = array();
	foreach ( $vondsten as $url => $aantal ) {
		$uit[] = array(
			'url'    => $url,
			'aantal' => substr_count( $inhoud, $url ),
			'naam'   => basename( parse_url( $url, PHP_URL_PATH ) ),
			'video'  => (bool) preg_match( '/\.(mp4|webm|m4v)$/i', $url ),
		);
	}
	return $uit;
}

add_action( 'admin_post_dtc_tk_beelden', 'dtc_tk_beelden_opslaan' );
function dtc_tk_beelden_opslaan() {
	if ( ! current_user_can( DTC_TK_CAP ) ) { wp_die( 'Geen rechten.' ); }
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	check_admin_referer( 'dtc_tk_beeld_' . $post_id );

	$post = get_post( $post_id );
	if ( ! $post ) { wp_die( 'Pagina niet gevonden.' ); }

	$inhoud = $post->post_content;
	$oud    = isset( $_POST['oud'] ) && is_array( $_POST['oud'] ) ? wp_unslash( $_POST['oud'] ) : array();
	$nieuw  = isset( $_POST['nieuw'] ) && is_array( $_POST['nieuw'] ) ? wp_unslash( $_POST['nieuw'] ) : array();

	$gewijzigd = 0;
	$mislukt   = 0;

	foreach ( $oud as $i => $oude_url ) {
		$oude_url   = trim( $oude_url );
		$nieuwe_url = isset( $nieuw[ $i ] ) ? trim( $nieuw[ $i ] ) : '';
		if ( '' === $nieuwe_url || $nieuwe_url === $oude_url ) { continue; }
		if ( ! preg_match( '/\.(' . DTC_TK_TYPES . ')(\?|$)/i', $nieuwe_url ) ) { $mislukt++; continue; }
		if ( false === strpos( $inhoud, $oude_url ) ) { $mislukt++; continue; }

		$inhoud = str_replace( $oude_url, esc_url_raw( $nieuwe_url ), $inhoud );
		$gewijzigd++;
	}

	if ( $gewijzigd ) {
		wp_save_post_revision( $post_id );
		global $wpdb;
		$wpdb->update(
			$wpdb->posts,
			array( 'post_content' => $inhoud, 'post_modified' => current_time( 'mysql' ), 'post_modified_gmt' => current_time( 'mysql', 1 ) ),
			array( 'ID' => $post_id )
		);
		clean_post_cache( $post_id );
		dtc_tk_leeg_divi_cache( $post_id );
		dtc_tk_markeer( $post_id );
	}

	wp_safe_redirect( add_query_arg( array(
		'page'      => DTC_TK_SLUG,
		'pagina'    => $post_id,
		'deel'      => 'beeld',
		'gewijzigd' => $gewijzigd,
		'mislukt'   => $mislukt,
	), admin_url( 'admin.php' ) ) );
	exit;
}

/* ============================================================ scherm */

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( false !== strpos( $hook, DTC_TK_SLUG ) ) { wp_enqueue_media(); }
} );

add_action( 'admin_menu', function () {
	/* het pijltje uit het dtc-logo als menu-icoon */
	$icoon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCA3NiA3Nic+PHBhdGggZD0nTTE5LjM3IDAuNzd2NzQuNDZMNTYuNjMgMzggMTkuMzcgMC43N1onIGZpbGw9JyNhN2FhYWQnLz48L3N2Zz4=';
	add_menu_page( 'dtc agency', 'dtc agency', DTC_TK_CAP, DTC_TK_SLUG, 'dtc_tk_scherm', $icoon, 3 );
} );

function dtc_tk_scherm() {
	if ( ! current_user_can( DTC_TK_CAP ) ) { wp_die( 'Geen rechten.' ); }

	$paginas = dtc_tk_paginas();
	if ( ! $paginas ) {
		echo '<div class="wrap dtc-tk">';
		dtc_tk_titelbalk();
		echo '<p>Geen pagina\'s gevonden.</p></div>';
		dtc_tk_stijl();
		return;
	}

	$huidig_id = isset( $_GET['pagina'] ) ? absint( $_GET['pagina'] ) : 0;
	$huidig    = null;
	foreach ( $paginas as $p ) { if ( $p->ID === $huidig_id ) { $huidig = $p; break; } }
	if ( ! $huidig ) { $huidig = $paginas[0]; }

	$teksten = dtc_tk_teksten( $huidig->post_content );
	?>
	<div class="wrap dtc-tk">
		<?php dtc_tk_titelbalk( 'Pas hier de teksten en afbeeldingen van de site aan, zonder de Divi-editor te openen.' ); ?>

		<?php if ( isset( $_GET['gewijzigd'] ) ) : $g = absint( $_GET['gewijzigd'] ); $f = absint( $_GET['mislukt'] ); ?>
			<?php if ( $g ) : ?>
				<?php $wat = ( isset( $_GET['deel'] ) && 'beeld' === $_GET['deel'] ) ? array( 'afbeelding', 'afbeeldingen' ) : array( 'tekst', 'teksten' ); ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( '%d %s opgeslagen.', $g, 1 === $g ? $wat[0] : $wat[1] ) ); ?></p></div>
			<?php else : ?>
				<div class="notice notice-info is-dismissible"><p>Niets gewijzigd.</p></div>
			<?php endif; ?>
			<?php if ( $f ) : ?>
				<div class="notice notice-warning is-dismissible"><p><?php echo esc_html( sprintf( '%d tekst(en) konden niet worden teruggevonden — de pagina is ondertussen ergens anders aangepast. Herlaad dit scherm en probeer die opnieuw.', $f ) ); ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<nav class="nav-tab-wrapper dtc-tk-tabs">
			<?php foreach ( dtc_tk_paginas( 'hoofd' ) as $p ) :
				$actief = $p->ID === $huidig->ID ? ' nav-tab-active' : '';
				$url    = add_query_arg( array( 'page' => DTC_TK_SLUG, 'pagina' => $p->ID ), admin_url( 'admin.php' ) ); ?>
				<a class="nav-tab<?php echo $actief; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $p->post_title ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="dtc-tk-overig">
			<span>overige pagina&rsquo;s en vaste blokken:</span>
			<?php foreach ( dtc_tk_paginas( 'overig' ) as $p ) :
				$actief = $p->ID === $huidig->ID ? ' actief' : '';
				$url    = add_query_arg( array( 'page' => DTC_TK_SLUG, 'pagina' => $p->ID ), admin_url( 'admin.php' ) ); ?>
				<a class="dtc-tk-chip<?php echo $actief; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $p->post_title ); ?></a>
			<?php endforeach; ?>
		</div>

		<?php $bewerkt = get_post_meta( $huidig->ID, '_dtc_tk_bewerkt', true ); ?>
		<?php if ( $bewerkt ) : ?>
			<div class="notice notice-info inline"><p>Deze pagina is hier voor het laatst aangepast op
			<?php echo esc_html( mysql2date( 'j F Y, H:i', $bewerkt ) ); ?>
			door <?php echo esc_html( get_post_meta( $huidig->ID, '_dtc_tk_bewerkt_door', true ) ?: 'onbekend' ); ?>.</p></div>
		<?php endif; ?>

		<div class="dtc-tk-kop">
			<p>
				<strong><?php echo esc_html( $huidig->post_title ); ?></strong> &mdash;
				<?php if ( 'page' === $huidig->post_type ) : ?>
					<a href="<?php echo esc_url( get_permalink( $huidig ) ); ?>" target="_blank" rel="noopener">pagina bekijken</a> &middot;
				<?php else : ?>
					<em>vast blok &mdash; deze tekst staat op meerdere pagina&rsquo;s</em> &middot;
				<?php endif; ?>
				<a href="<?php echo esc_url( get_edit_post_link( $huidig->ID ) ); ?>">in de editor openen</a>
			</p>
		</div>

		<?php
		$deel     = ( isset( $_GET['deel'] ) && 'beeld' === $_GET['deel'] ) ? 'beeld' : 'tekst';
		$beelden  = dtc_tk_afbeeldingen( $huidig->post_content );
		$url_t    = add_query_arg( array( 'page' => DTC_TK_SLUG, 'pagina' => $huidig->ID ), admin_url( 'admin.php' ) );
		$url_b    = add_query_arg( 'deel', 'beeld', $url_t );
		?>
		<div class="dtc-tk-deel">
			<a class="<?php echo 'tekst' === $deel ? 'aan' : ''; ?>" href="<?php echo esc_url( $url_t ); ?>">teksten (<?php echo count( $teksten ); ?>)</a>
			<a class="<?php echo 'beeld' === $deel ? 'aan' : ''; ?>" href="<?php echo esc_url( $url_b ); ?>">afbeeldingen (<?php echo count( $beelden ); ?>)</a>
		</div>

		<?php if ( 'beeld' === $deel ) : ?>
			<?php if ( ! $beelden ) : ?>
				<p>Op deze pagina staan geen afbeeldingen.</p>
			<?php else : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="dtc_tk_beelden">
				<input type="hidden" name="post_id" value="<?php echo (int) $huidig->ID; ?>">
				<?php wp_nonce_field( 'dtc_tk_beeld_' . $huidig->ID ); ?>

				<div class="dtc-tk-beelden">
				<?php foreach ( $beelden as $i => $b ) : ?>
					<div class="dtc-tk-beeld">
						<div class="voorbeeld">
							<?php if ( $b['video'] ) : ?>
								<video src="<?php echo esc_url( $b['url'] ); ?>" muted playsinline preload="metadata"></video>
							<?php else : ?>
								<img src="<?php echo esc_url( $b['url'] ); ?>" alt="">
							<?php endif; ?>
						</div>
						<p class="naam"><?php echo esc_html( $b['naam'] ); ?></p>
						<p class="hoe"><?php echo esc_html( $b['aantal'] > 1 ? $b['aantal'] . '× op deze pagina' : '1× op deze pagina' ); ?></p>
						<p class="nieuw" style="display:none"></p>
						<button type="button" class="button dtc-tk-kies">Vervangen&hellip;</button>
						<input type="hidden" name="oud[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $b['url'] ); ?>">
						<input type="hidden" class="dtc-tk-nieuw" name="nieuw[<?php echo (int) $i; ?>]" value="">
					</div>
				<?php endforeach; ?>
				</div>

				<p class="dtc-tk-opslaan"><?php submit_button( 'Afbeeldingen opslaan', 'primary', 'submit', false ); ?></p>
			</form>
			<?php endif; ?>
			<?php dtc_tk_stijl(); dtc_tk_media_script(); ?>
			</div>
			<?php return; ?>
		<?php endif; ?>

		<?php $codes = dtc_tk_shortcodes( $huidig->post_content ); ?>
		<?php if ( $codes ) : ?>
			<div class="notice notice-info inline"><p>Let op: deze pagina gebruikt ook <code><?php echo esc_html( '[' . implode( '] [', $codes ) . ']' ); ?></code>. Die tekst komt uit een plugin (bijvoorbeeld de vacatures van jobs.dtc.agency) en is hier niet te bewerken.</p></div>
		<?php endif; ?>

		<?php if ( ! $teksten ) : ?>
			<p>Op deze pagina staan geen teksten die hier te bewerken zijn.</p>
		<?php else : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="dtc_tk_opslaan">
			<input type="hidden" name="post_id" value="<?php echo (int) $huidig->ID; ?>">
			<?php wp_nonce_field( 'dtc_tk_' . $huidig->ID ); ?>

			<?php
			$vorige_kop = null;
			$open       = false;
			foreach ( $teksten as $t ) :
				if ( $t['kop'] !== $vorige_kop ) {
					if ( $open ) { echo '</div></div>'; }
					$titel = $t['kop'] ? $t['kop'] : 'begin van de pagina';
					echo '<div class="dtc-tk-groep"><h2>' . esc_html( $titel ) . '</h2><div class="dtc-tk-velden">';
					$vorige_kop = $t['kop'];
					$open       = true;
				}

				$waarde = html_entity_decode( str_replace( array( '<br />', '<br>', '<br/>' ), "\n", $t['tekst'] ), ENT_QUOTES, 'UTF-8' );
				$regels = max( 1, min( 10, (int) ceil( mb_strlen( $waarde ) / 78 ) + substr_count( $waarde, "\n" ) ) );
				$label  = dtc_tk_label( $t['soort'] );
				?>
				<div class="dtc-tk-veld">
					<span class="dtc-tk-label"><?php echo esc_html( $label ); ?></span>
					<textarea name="veld[<?php echo (int) $t['offset']; ?>]" rows="<?php echo (int) $regels; ?>" class="<?php echo esc_attr( 'soort-' . $t['soort'] ); ?>"><?php echo esc_textarea( $waarde ); ?></textarea>
					<input type="hidden" name="hash[<?php echo (int) $t['offset']; ?>]" value="<?php echo esc_attr( md5( $t['tekst'] ) ); ?>">
				</div>
			<?php endforeach; ?>
			<?php if ( $open ) { echo '</div></div>'; } ?>

			<p class="dtc-tk-opslaan"><?php submit_button( 'Teksten opslaan', 'primary', 'submit', false ); ?></p>
		</form>
		<?php endif; ?>
	</div>

	<?php dtc_tk_stijl(); ?>
	<?php
}

/** de kopbalk met het dtc-logo, zodat het beheerscherm bij de site hoort */
function dtc_tk_titelbalk( $uitleg = '' ) {
	$logo = '/wp-content/uploads/2025/10/logo.svg';
	if ( function_exists( 'dtc_login_opties' ) ) {
		$o = dtc_login_opties();
		if ( ! empty( $o['logo'] ) ) { $logo = $o['logo']; }
	}
	?>
	<h1 class="dtc-kopbalk">
		<img src="<?php echo esc_url( $logo ); ?>" alt="dtc">
		<span class="woord">agency</span>
		<?php if ( $uitleg ) : ?><span class="uitleg"><?php echo esc_html( $uitleg ); ?></span><?php endif; ?>
	</h1>
	<?php
}

function dtc_tk_label( $soort ) {
	$kaart = array(
		'h1'      => 'titel',
		'h2'      => 'kop',
		'h3'      => 'tussenkop',
		'h4'      => 'tussenkop',
		'p'       => 'tekst',
		'span'    => 'tekst',
		'li'      => 'lijstitem',
		'a'       => 'knop / link',
		'button'  => 'knop',
		'strong'  => 'tekst (vet)',
		'b'       => 'tekst (vet)',
		'summary' => 'regel',
		'div'     => 'tekst',
	);
	return isset( $kaart[ $soort ] ) ? $kaart[ $soort ] : 'tekst';
}

function dtc_tk_stijl() {
	static $gedaan = false;
	if ( $gedaan ) { return; }
	$gedaan = true;
	?>
	<style>
		/* Het lettertype en de kleuren van de site, zodat het beheerscherm
		   dezelfde stijl heeft als dtc.agency en de inlogpagina. */
		@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Medium.woff2') format('woff2');font-weight:500;font-display:swap}
		@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Light.woff2') format('woff2');font-weight:300;font-display:swap}
		@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Book.woff2') format('woff2');font-weight:400;font-display:swap}

		#wpwrap,#wpcontent,#wpbody,#wpbody-content,#wpfooter{background:#f4f1ec}
		#wpfooter{color:#a99a86}

		.dtc-tk,.dtc-tk .notice,.dtc-tk .button,.dtc-tk input,.dtc-tk textarea,.dtc-tk select{
			font-family:'Arboria',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
		}
		.dtc-tk{color:#2d2824;max-width:1240px}

		/* ---------- de kopbalk ---------- */
		.dtc-tk .dtc-kopbalk{
			display:flex;
			align-items:center;
			gap:16px;
			margin:14px 0 20px;
			padding:20px 26px;
			background:#fff;
			border:1px solid #e8e0d4;
			border-radius:16px;
			font-weight:500;
		}
		.dtc-tk .dtc-kopbalk img{width:92px;height:auto;display:block}
		.dtc-tk .dtc-kopbalk .woord{font-size:30px;line-height:1;letter-spacing:-.01em;color:#2d2824}
		.dtc-tk .dtc-kopbalk .uitleg{
			margin-left:auto;
			max-width:440px;
			text-align:right;
			font-size:14px;
			font-weight:300;
			line-height:1.6;
			color:#8a7257;
		}

		/* ---------- de tabs met de pagina's ---------- */
		.dtc-tk .nav-tab-wrapper.dtc-tk-tabs{
			display:flex;
			flex-wrap:wrap;
			gap:8px;
			border:0;
			margin:0;
			padding:0;
		}
		.dtc-tk .nav-tab{
			margin:0;
			padding:8px 18px;
			background:#fff;
			border:1px solid #e0d6c6;
			border-radius:999px;
			font-size:14px;
			font-weight:500;
			line-height:1.5;
			color:#6b6258;
			transition:border-color .2s,color .2s,background .2s;
		}
		.dtc-tk .nav-tab:hover{border-color:#c0ab94;color:#2d2824;background:#fff}
		.dtc-tk .nav-tab-active,
		.dtc-tk .nav-tab-active:hover,
		.dtc-tk .nav-tab-active:focus{background:#2d2824;border-color:#2d2824;color:#fff}

		.dtc-tk-overig{margin:16px 0 0;padding:14px 0 0;border-top:1px solid #e8e0d4;font-size:13px;color:#a99a86}
		.dtc-tk-overig span{margin-right:8px}
		.dtc-tk-chip{
			display:inline-block;
			margin:0 6px 6px 0;
			padding:4px 12px;
			background:#fff;
			border:1px solid #e0d6c6;
			border-radius:999px;
			text-decoration:none;
			color:#6b6258;
			font-size:13px;
		}
		.dtc-tk-chip:hover{border-color:#8a7257;color:#2d2824}
		.dtc-tk-chip.actief{background:#2d2824;border-color:#2d2824;color:#fff}

		/* ---------- welke pagina, en de keuze tekst / afbeeldingen ---------- */
		.dtc-tk-kop{margin:18px 0 2px;font-size:15px}
		.dtc-tk-kop strong{font-weight:500}
		.dtc-tk a{color:#8a7257}
		.dtc-tk a:hover{color:#2d2824}
		.dtc-tk-deel{margin:14px 0 4px;border-bottom:1px solid #e8e0d4}
		.dtc-tk-deel a{
			display:inline-block;
			margin-right:22px;
			padding:6px 0 10px;
			text-decoration:none;
			font-size:15px;
			color:#8a7257;
			border-bottom:2px solid transparent;
		}
		.dtc-tk-deel a.aan{color:#2d2824;font-weight:500;border-bottom-color:#c0ab94}

		/* ---------- de blokken met velden ---------- */
		.dtc-tk-groep{
			background:#fff;
			border:1px solid #e8e0d4;
			border-radius:16px;
			padding:22px 26px 8px;
			margin:18px 0;
		}
		.dtc-tk-groep > h2{
			margin:0 0 16px;
			padding-bottom:12px;
			border-bottom:1px solid #f0ebe2;
			font-size:18px;
			font-weight:500;
			color:#2d2824;
		}
		.dtc-tk-veld{margin:0 0 18px}
		.dtc-tk-label{
			display:block;
			margin-bottom:5px;
			font-size:11px;
			font-weight:500;
			text-transform:uppercase;
			letter-spacing:.08em;
			color:#a99a86;
		}
		.dtc-tk textarea,
		.dtc-tk input[type=text],
		.dtc-tk input[type=number],
		.dtc-tk input[type=email],
		.dtc-tk input[type=url]{
			background:#faf8f5;
			border:1px solid #ddd3c4;
			border-radius:10px;
			padding:9px 13px;
			box-shadow:none;
			color:#2d2824;
			font-size:15px;
			line-height:1.7;
		}
		.dtc-tk textarea:focus,
		.dtc-tk input[type=text]:focus,
		.dtc-tk input[type=number]:focus,
		.dtc-tk input[type=email]:focus,
		.dtc-tk input[type=url]:focus{
			border-color:#c0ab94;
			box-shadow:0 0 0 3px rgba(45,40,36,.06);
			outline:none;
		}
		.dtc-tk-veld textarea{width:100%;max-width:900px}
		.dtc-tk-veld textarea.soort-h1,
		.dtc-tk-veld textarea.soort-h2{font-size:18px;font-weight:500}

		/* vinkjes in dezelfde stijl als op de inlogpagina */
		.dtc-tk input[type=checkbox]{
			-webkit-appearance:none;
			appearance:none;
			width:18px;
			height:18px;
			min-width:18px;
			margin:0 8px 0 0;
			border:1px solid #ddd3c4;
			border-radius:5px;
			background:#faf8f5;
			box-shadow:none;
			transition:background .2s,border-color .2s;
		}
		.dtc-tk input[type=checkbox]:checked{background:#2d2824;border-color:#2d2824}
		.dtc-tk input[type=checkbox]:checked::before{
			content:'';
			display:block;
			width:100%;
			height:100%;
			margin:0;
			background:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23fff' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round' d='M3.4 8.4l3.1 3.1 6.1-6.5'/%3E%3C/svg%3E") center/12px no-repeat;
		}
		.dtc-tk input[type=checkbox]:focus{border-color:#c0ab94;box-shadow:0 0 0 3px rgba(45,40,36,.06);outline:none}

		/* ---------- knoppen ---------- */
		.dtc-tk .button{
			background:#fff;
			border:1px solid #e0d6c6;
			border-radius:999px;
			padding:5px 18px;
			height:auto;
			line-height:1.7;
			font-size:14px;
			font-weight:500;
			color:#6b6258;
			box-shadow:none;
			text-shadow:none;
		}
		.dtc-tk .button:hover,
		.dtc-tk .button:focus{background:#fff;border-color:#8a7257;color:#2d2824;box-shadow:none}
		.dtc-tk .button-primary,
		.dtc-tk .button-primary:focus{
			background:#2d2824;
			border-color:#2d2824;
			color:#fff;
			padding:8px 28px;
			font-size:15px;
		}
		.dtc-tk .button-primary:hover{background:#c0ab94;border-color:#c0ab94;color:#2d2824}

		.dtc-tk-opslaan{
			position:sticky;
			bottom:0;
			margin:0;
			padding:14px 0;
			background:rgba(244,241,236,.94);
			border-top:1px solid #e8e0d4;
			box-shadow:0 -6px 16px rgba(45,40,36,.06);
		}
		.dtc-tk form{padding-bottom:6px}

		/* ---------- meldingen ---------- */
		.dtc-tk .notice{
			background:#fff;
			border:1px solid #e8e0d4;
			border-left-width:4px;
			border-radius:12px;
			box-shadow:none;
			font-size:14px;
			line-height:1.6;
		}
		.dtc-tk .notice-success{border-left-color:#7d9068}
		.dtc-tk .notice-info{border-left-color:#c0ab94}
		.dtc-tk .notice-warning{border-left-color:#c58b3f}
		.dtc-tk .notice-error{border-left-color:#b4492f}
		.dtc-tk .notice code{background:#f4f1ec;border-radius:5px;padding:2px 6px}

		/* ---------- de afbeeldingen ---------- */
		.dtc-tk-beelden{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px;margin:18px 0}
		.dtc-tk-beeld{background:#fff;border:1px solid #e8e0d4;border-radius:14px;padding:14px}
		.dtc-tk-beeld .voorbeeld{
			height:130px;
			display:flex;
			align-items:center;
			justify-content:center;
			background:#f4f1ec;
			border-radius:10px;
			overflow:hidden;
			margin-bottom:10px;
		}
		.dtc-tk-beeld .voorbeeld img,
		.dtc-tk-beeld .voorbeeld video{max-width:100%;max-height:130px;object-fit:contain}
		.dtc-tk-beeld .naam{margin:0;font-size:12px;font-weight:500;color:#2d2824;word-break:break-all}
		.dtc-tk-beeld .hoe{margin:2px 0 10px;font-size:11px;color:#a99a86}
		.dtc-tk-beeld .nieuw{margin:0 0 8px;font-size:11px;color:#5f7a4e;font-weight:500;word-break:break-all}

		/* ---------- de instellingen van de inlogpagina ---------- */
		.dtc-tk .form-table{
			background:#fff;
			border:1px solid #e8e0d4;
			border-radius:16px;
			padding:6px 26px 14px;
			margin-top:10px;
		}
		.dtc-tk .form-table th{font-weight:500;color:#2d2824;font-size:14px;padding-top:18px}
		.dtc-tk .form-table td{padding-top:14px}
		.dtc-tk .description{font-weight:300;font-size:13px;color:#8a7257}
		.dtc-tk p.submit{padding-top:4px}

		@media (max-width:782px){
			.dtc-tk .dtc-kopbalk{flex-wrap:wrap;gap:10px}
			.dtc-tk .dtc-kopbalk .uitleg{margin-left:0;text-align:left;max-width:none;flex:1 0 100%}
		}
	</style>
	<?php
}

/**
 * De huisstijl over een gewoon beheerscherm van WordPress heen (de lijsten en
 * het bewerkscherm van Hub en Campagnes). Aanzetten door op dat scherm
 * dtc_tk_huisstijl() aan te roepen; die zet een klasse op de <body>.
 */
function dtc_tk_huisstijl( $schermen ) {
	add_filter( 'admin_body_class', function ( $klassen ) use ( $schermen ) {
		$scherm = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $scherm ) { return $klassen; }
		$bij = in_array( $scherm->post_type, (array) $schermen, true )
			|| in_array( $scherm->taxonomy, (array) $schermen, true );
		return $bij ? $klassen . ' dtc-huisstijl' : $klassen;
	} );
	add_action( 'admin_head', 'dtc_tk_stijl_admin' );
}

function dtc_tk_stijl_admin() {
	static $gedaan = false;
	if ( $gedaan ) { return; }
	$gedaan = true;
	?>
	<style id="dtc-huisstijl">
		@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Medium.woff2') format('woff2');font-weight:500;font-display:swap}
		@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Light.woff2') format('woff2');font-weight:300;font-display:swap}
		@font-face{font-family:'Arboria';src:url('/wp-content/uploads/et-fonts/Arboria-Book.woff2') format('woff2');font-weight:400;font-display:swap}

		body.dtc-huisstijl #wpwrap,
		body.dtc-huisstijl #wpcontent,
		body.dtc-huisstijl #wpbody,
		body.dtc-huisstijl #wpbody-content,
		body.dtc-huisstijl #wpfooter{background:#f4f1ec}
		body.dtc-huisstijl #wpfooter{color:#a99a86}

		body.dtc-huisstijl,
		body.dtc-huisstijl .wrap,
		body.dtc-huisstijl .button,
		body.dtc-huisstijl input,
		body.dtc-huisstijl textarea,
		body.dtc-huisstijl select,
		body.dtc-huisstijl .wp-list-table{
			font-family:'Arboria',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
		}
		body.dtc-huisstijl .wrap > h1{font-weight:500;color:#2d2824}
		body.dtc-huisstijl a{color:#8a7257}
		body.dtc-huisstijl a:hover{color:#2d2824}

		body.dtc-huisstijl .wp-list-table,
		body.dtc-huisstijl .postbox,
		body.dtc-huisstijl #poststuff .postbox,
		body.dtc-huisstijl .form-table{
			background:#fff;border:1px solid #e8e0d4;border-radius:14px;box-shadow:none;
		}
		body.dtc-huisstijl .wp-list-table{overflow:hidden}
		body.dtc-huisstijl .wp-list-table th,
		body.dtc-huisstijl .wp-list-table td{border-bottom-color:#f0ebe2}
		body.dtc-huisstijl .postbox .postbox-header{border-bottom:1px solid #f0ebe2}
		body.dtc-huisstijl .postbox h2.hndle{font-weight:500;color:#2d2824}

		body.dtc-huisstijl input[type=text],
		body.dtc-huisstijl input[type=url],
		body.dtc-huisstijl input[type=email],
		body.dtc-huisstijl input[type=search],
		body.dtc-huisstijl input[type=number],
		body.dtc-huisstijl textarea,
		body.dtc-huisstijl select,
		body.dtc-huisstijl #titlediv #title{
			background:#faf8f5;border:1px solid #ddd3c4;border-radius:10px;padding:8px 12px;
			box-shadow:none;color:#2d2824;
		}
		body.dtc-huisstijl input:focus,
		body.dtc-huisstijl textarea:focus,
		body.dtc-huisstijl select:focus,
		body.dtc-huisstijl #titlediv #title:focus{
			border-color:#c0ab94;box-shadow:0 0 0 3px rgba(45,40,36,.06);outline:none;
		}

		body.dtc-huisstijl .button{
			background:#fff;border:1px solid #e0d6c6;border-radius:999px;padding:4px 16px;height:auto;
			line-height:1.7;font-weight:500;color:#6b6258;box-shadow:none;text-shadow:none;
		}
		body.dtc-huisstijl .button:hover{border-color:#8a7257;color:#2d2824;background:#fff}
		body.dtc-huisstijl .button-primary,
		body.dtc-huisstijl .button-primary:focus,
		body.dtc-huisstijl #publishing-action .button-primary{
			background:#2d2824;border-color:#2d2824;color:#fff;padding:6px 24px;
		}
		body.dtc-huisstijl .button-primary:hover{background:#c0ab94;border-color:#c0ab94;color:#2d2824}
		body.dtc-huisstijl .page-title-action{border-radius:999px;border-color:#e0d6c6;color:#6b6258}
		body.dtc-huisstijl .notice{border-radius:12px;border-left-width:4px;background:#fff;border-color:#e8e0d4;box-shadow:none}
	</style>
	<?php
}

function dtc_tk_media_script() {
	?>
	<script>
	jQuery( function ( $ ) {
		$( document ).on( 'click', '.dtc-tk-kies', function () {
			var kaart = $( this ).closest( '.dtc-tk-beeld' );
			var kader = wp.media( { title: 'Kies een afbeelding', button: { text: 'Deze gebruiken' }, multiple: false } );
			kader.on( 'select', function () {
				var bestand = kader.state().get( 'selection' ).first().toJSON();
				kaart.find( '.dtc-tk-nieuw' ).val( bestand.url );
				kaart.find( '.nieuw' ).text( 'wordt: ' + bestand.filename ).show();
				var voorbeeld = kaart.find( '.voorbeeld img' );
				if ( voorbeeld.length && bestand.type === 'image' ) { voorbeeld.attr( 'src', bestand.url ); }
			} );
			kader.open();
		} );
	} );
	</script>
	<?php
}
