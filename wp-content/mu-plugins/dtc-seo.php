<?php
/**
 * Plugin Name: DTC SEO
 * Description: Verzorgt titels, meta descriptions, Open Graph, Twitter cards en schema.org voor de dtc-site. Bewust klein gehouden - geen SEO-plugin met honderd instellingen, alleen wat deze site nodig heeft.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DTC_SEO_TITLE = '_dtc_seo_title';
const DTC_SEO_DESC  = '_dtc_seo_description';

/** Standaard deelafbeelding als een pagina geen uitgelichte afbeelding heeft. */
function dtc_seo_default_image() {
	return home_url( '/wp-content/uploads/2025/12/SVM_DTC_070725_0341-1280x853.webp' );
}

/* -------------------------------------------------------------------------
 * Bewerkbare velden in de editor
 * ---------------------------------------------------------------------- */

add_action( 'add_meta_boxes', function () {
	foreach ( array( 'page', 'post', 'hub' ) as $type ) {
		add_meta_box( 'dtc-seo', 'SEO', 'dtc_seo_meta_box', $type, 'normal', 'high' );
	}
} );

function dtc_seo_meta_box( $post ) {
	wp_nonce_field( 'dtc_seo_save', 'dtc_seo_nonce' );
	$title = get_post_meta( $post->ID, DTC_SEO_TITLE, true );
	$desc  = get_post_meta( $post->ID, DTC_SEO_DESC, true );
	?>
	<p>
		<label for="dtc_seo_title"><strong>Titel voor Google</strong> (leeg = paginatitel, streef naar 50-60 tekens)</label><br />
		<input type="text" id="dtc_seo_title" name="dtc_seo_title" style="width:100%"
			value="<?php echo esc_attr( $title ); ?>" maxlength="120" />
	</p>
	<p>
		<label for="dtc_seo_description"><strong>Omschrijving voor Google</strong> (streef naar 140-160 tekens)</label><br />
		<textarea id="dtc_seo_description" name="dtc_seo_description" rows="3" style="width:100%"
			maxlength="320"><?php echo esc_textarea( $desc ); ?></textarea>
	</p>
	<?php
}

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['dtc_seo_nonce'] ) || ! wp_verify_nonce( $_POST['dtc_seo_nonce'], 'dtc_seo_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( DTC_SEO_TITLE => 'dtc_seo_title', DTC_SEO_DESC => 'dtc_seo_description' ) as $meta => $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta );
		} else {
			update_post_meta( $post_id, $meta, $value );
		}
	}
} );

/* -------------------------------------------------------------------------
 * Tekst uit Divi-content halen voor de fallback-omschrijving
 * ---------------------------------------------------------------------- */

function dtc_seo_text_from_content( $content ) {
	// Divi stopt CSS en JS in code-modules; die mag nooit in een omschrijving belanden.
	$content = preg_replace( '#<style[^>]*>.*?</style>#is', ' ', $content );
	$content = preg_replace( '#<script[^>]*>.*?</script>#is', ' ', $content );
	// Shortcode-attributen bevatten kleurcodes en paden, niet leesbaar.
	$content = preg_replace( '#\[/?[a-z_0-9]+[^\]]*\]#i', ' ', $content );
	// Regeleindes en blokgrenzen zijn woordgrenzen; zonder dit wordt
	// "about<br />us" tot "aboutus".
	$content = preg_replace( '#<br\s*/?>#i', ' ', $content );
	$content = preg_replace( '#</(p|div|h[1-6]|li|span)>#i', ' ', $content );
	$content = wp_strip_all_tags( $content );
	$content = html_entity_decode( $content, ENT_QUOTES, 'UTF-8' );
	$content = preg_replace( '/\s+/u', ' ', $content );
	return trim( $content );
}

function dtc_seo_trim( $text, $limit = 158 ) {
	$text = trim( $text );
	if ( mb_strlen( $text ) <= $limit ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, $limit );
	$sp  = mb_strrpos( $cut, ' ' );
	if ( $sp > 60 ) {
		$cut = mb_substr( $cut, 0, $sp );
	}
	return rtrim( $cut, " ,;:-" ) . '…';
}

function dtc_seo_description() {
	if ( is_singular() ) {
		$post = get_queried_object();
		$set  = get_post_meta( $post->ID, DTC_SEO_DESC, true );
		if ( $set ) {
			return $set;
		}
		$text = dtc_seo_text_from_content( $post->post_content );
		if ( mb_strlen( $text ) > 40 ) {
			return dtc_seo_trim( $text );
		}
	}
	if ( is_post_type_archive( 'hub' ) ) {
		return 'Everything we keep learning about strategy, creation, growth, media and HubSpot, written down so you can use it.';
	}
	if ( is_tax( 'hub_topic' ) ) {
		$term = get_queried_object();
		if ( $term && ! empty( $term->description ) ) {
			return dtc_seo_trim( $term->description );
		}
	}
	if ( is_home() || is_front_page() ) {
		$tagline = get_bloginfo( 'description' );
		if ( $tagline ) {
			return $tagline;
		}
	}
	return '';
}

/** Korte themanamen zijn afkortingen (ai, seo); die horen in hoofdletters. */
function dtc_seo_label( $name ) {
	return mb_strlen( $name ) <= 3 ? mb_strtoupper( $name ) : ucfirst( $name );
}

/* -------------------------------------------------------------------------
 * Titel
 * ---------------------------------------------------------------------- */

/**
 * Divi hangt elegant_titles_filter op pre_get_document_title (prioriteit 10)
 * en kortsluit daarmee document_title_parts volledig - een filter daarop
 * wordt nooit uitgevoerd. Vandaar dat we hier inhaken, ná Divi.
 */
add_filter( 'pre_get_document_title', function ( $title ) {
	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), DTC_SEO_TITLE, true );
		if ( $custom ) {
			// Eigen titel is compleet; geen sitenaam erachter plakken.
			return $custom;
		}
	}
	if ( is_post_type_archive( 'hub' ) ) {
		return 'The hub: what we know about marketing | dtc /always on';
	}
	if ( is_tax( 'hub_topic' ) ) {
		$term = get_queried_object();
		if ( $term ) {
			return sprintf( '%s in the dtc hub | dtc /always on', dtc_seo_label( $term->name ) );
		}
	}
	// Ruimt de losse pipe op die de voorpagina liet zien ("dtc agency |").
	$title = trim( (string) $title );
	$title = trim( $title, " \t\n\r\0\x0B|-–—" );
	return trim( $title );
}, 99 );

add_filter( 'document_title_separator', function () {
	return '|';
} );

/* -------------------------------------------------------------------------
 * head-output
 * ---------------------------------------------------------------------- */

function dtc_seo_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		if ( $src ) {
			return $src[0];
		}
	}
	return dtc_seo_default_image();
}

add_action( 'wp_head', function () {
	if ( is_404() || is_search() ) {
		return;
	}

	$title = wp_get_document_title();
	$desc  = dtc_seo_description();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	$image = dtc_seo_image();
	$name  = get_bloginfo( 'name' );

	echo "\n<!-- dtc seo -->\n";

	if ( $desc ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $desc ) );
	}

	// WordPress zet rel=canonical alleen op losse pagina's; de hub-archieven
	// zouden er anders zonder zitten.
	if ( ! is_singular() && ( is_post_type_archive( 'hub' ) || is_tax( 'hub_topic' ) ) ) {
		$canon = is_tax( 'hub_topic' )
			? get_term_link( get_queried_object() )
			: get_post_type_archive_link( 'hub' );
		if ( $canon && ! is_wp_error( $canon ) ) {
			printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canon ) );
		}
	}

	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $name ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", ( is_singular( 'post' ) || is_singular( 'hub' ) ) ? 'article' : 'website' );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	if ( $desc ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $desc ) );
	}
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
		echo '<meta property="og:image:width" content="1280" />' . "\n";
		echo '<meta property="og:image:height" content="853" />' . "\n";
	}

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
	if ( $desc ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $desc ) );
	}
	if ( $image ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );
	}

	dtc_seo_json_ld( $title, $desc, $url, $image, $name );

	echo "<!-- /dtc seo -->\n\n";
}, 5 );

/* -------------------------------------------------------------------------
 * JSON-LD
 * ---------------------------------------------------------------------- */

function dtc_seo_json_ld( $title, $desc, $url, $image, $name ) {
	$home  = home_url( '/' );
	$graph = array();

	$org_id = $home . '#organization';
	$org    = array(
		'@type'  => 'Organization',
		'@id'    => $org_id,
		'name'   => $name,
		'url'    => $home,
		'email'  => 'hello@dtc.agency',
		'telephone' => '+31306868583',
		'address' => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Europalaan 101',
			'postalCode'      => '3526 KR',
			'addressLocality' => 'Utrecht',
			'addressCountry'  => 'NL',
		),
	);
	$icon = get_site_icon_url( 512 );
	if ( $icon ) {
		$org['logo'] = array( '@type' => 'ImageObject', 'url' => $icon );
	}
	$graph[] = $org;

	$graph[] = array(
		'@type'     => 'WebSite',
		'@id'       => $home . '#website',
		'url'       => $home,
		'name'      => $name,
		'publisher' => array( '@id' => $org_id ),
	);

	if ( is_singular() ) {
		$post = get_queried_object();

		if ( is_singular( 'post' ) || is_singular( 'hub' ) ) {
			$graph[] = array(
				'@type'            => 'Article',
				'@id'              => $url . '#article',
				'headline'         => wp_strip_all_tags( get_the_title( $post ) ),
				'description'      => $desc,
				'datePublished'    => get_the_date( 'c', $post ),
				'dateModified'     => get_the_modified_date( 'c', $post ),
				'author'           => array( '@id' => $org_id ),
				'publisher'        => array( '@id' => $org_id ),
				'image'            => $image,
				'mainEntityOfPage' => $url,
			);
		} else {
			$graph[] = array(
				'@type'       => 'WebPage',
				'@id'         => $url . '#webpage',
				'url'         => $url,
				'name'        => $title,
				'description' => $desc,
				'isPartOf'    => array( '@id' => $home . '#website' ),
			);
		}

		$crumbs = dtc_seo_breadcrumbs( $post );
		if ( count( $crumbs ) > 1 ) {
			$items = array();
			foreach ( $crumbs as $i => $crumb ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => $crumb['name'],
					'item'     => $crumb['url'],
				);
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'@id'             => $url . '#breadcrumbs',
				'itemListElement' => $items,
			);
		}
	}

	$data = array( '@context' => 'https://schema.org', '@graph' => $graph );
	echo '<script type="application/ld+json">'
		. wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. "</script>\n";
}

/**
 * Kruimelpad. Volgt de bovenliggende pagina's; voor berichten de categorie.
 */
function dtc_seo_breadcrumbs( $post ) {
	$crumbs = array( array( 'name' => 'home', 'url' => home_url( '/' ) ) );

	if ( 'hub' === $post->post_type ) {
		$crumbs[] = array( 'name' => 'hub', 'url' => get_post_type_archive_link( 'hub' ) );
		$topics = get_the_terms( $post->ID, 'hub_topic' );
		if ( $topics && ! is_wp_error( $topics ) ) {
			$crumbs[] = array(
				'name' => $topics[0]->name,
				'url'  => get_term_link( $topics[0] ),
			);
		}
	} elseif ( 'post' === $post->post_type ) {
		$cats = get_the_category( $post->ID );
		if ( $cats ) {
			$crumbs[] = array(
				'name' => $cats[0]->name,
				'url'  => get_category_link( $cats[0]->term_id ),
			);
		}
	} else {
		$ancestors = array_reverse( get_post_ancestors( $post ) );
		foreach ( $ancestors as $id ) {
			$crumbs[] = array( 'name' => get_the_title( $id ), 'url' => get_permalink( $id ) );
		}
	}

	$crumbs[] = array( 'name' => wp_strip_all_tags( get_the_title( $post ) ), 'url' => get_permalink( $post ) );
	return $crumbs;
}
