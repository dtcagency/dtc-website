<?php
/**
 * Plugin Name: DTC Hub
 * Description: Kennisomgeving op /hub/<thema>/<artikel>/ — eigen post type met een thema-taxonomie, plus landings- en themapagina's. Het beheer zit onder het menu dtc agency.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const DTC_HUB_CPT      = 'hub';
const DTC_HUB_TAX      = 'hub_topic';
const DTC_HUB_VERSION  = '1.0.3';
const DTC_HUB_DIR      = __DIR__ . '/dtc-hub';

/* -------------------------------------------------------------------------
 * Post type en taxonomie
 * ---------------------------------------------------------------------- */

add_action( 'init', function () {

	register_taxonomy( DTC_HUB_TAX, DTC_HUB_CPT, array(
		'labels'            => array(
			'name'          => 'Thema\'s',
			'singular_name' => 'Thema',
			'menu_name'     => 'Thema\'s',
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		// /hub/seo/ — zonder with_front, anders komt de blogprefix ervoor.
		'rewrite'           => array( 'slug' => 'hub', 'with_front' => false ),
	) );

	register_post_type( DTC_HUB_CPT, array(
		'labels'        => array(
			'name'          => 'Hub',
			'singular_name' => 'Hub-artikel',
			'add_new_item'  => 'Nieuw hub-artikel',
			'edit_item'     => 'Hub-artikel bewerken',
		),
		'public'        => true,
		'has_archive'   => 'hub',
		'menu_icon'     => 'dashicons-book-alt',
		'menu_position' => 21,
		// onder het dtc agency-menu, of als eigen item als die plugin er niet is
		'show_in_menu'  => defined( 'DTC_TK_SLUG' ) ? DTC_TK_SLUG : true,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
		'taxonomies'    => array( DTC_HUB_TAX ),
		'show_in_rest'  => true,
		// %hub_topic% wordt hieronder vervangen door de themaslug.
		'rewrite'       => array( 'slug' => 'hub/%' . DTC_HUB_TAX . '%', 'with_front' => false ),
	) );
} );

/**
 * Vult %hub_topic% in de permalink. Zonder thema valt het terug op
 * "algemeen", zodat een artikel nooit een kapotte URL krijgt.
 */
add_filter( 'post_type_link', function ( $link, $post ) {
	if ( DTC_HUB_CPT !== $post->post_type ) {
		return $link;
	}
	$slug  = 'algemeen';
	$terms = get_the_terms( $post->ID, DTC_HUB_TAX );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$slug = $terms[0]->slug;
	}
	return str_replace( '%' . DTC_HUB_TAX . '%', $slug, $link );
}, 10, 2 );

/**
 * mu-plugins kennen geen activatiehook, dus verversen we de rewrite rules
 * wanneer het versienummer verandert.
 */
add_action( 'init', function () {
	if ( get_option( 'dtc_hub_version' ) !== DTC_HUB_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'dtc_hub_version', DTC_HUB_VERSION );
	}
}, 99 );

/* De beheerschermen van de Hub in dezelfde stijl als de rest van dtc agency. */
add_action( 'admin_init', function () {
	if ( function_exists( 'dtc_tk_huisstijl' ) ) {
		dtc_tk_huisstijl( array( DTC_HUB_CPT, DTC_HUB_TAX ) );
	}
} );

/* -------------------------------------------------------------------------
 * Oude URL's
 * ---------------------------------------------------------------------- */

/**
 * Artikelen die van /<slug>/ naar /hub/<thema>/<slug>/ zijn verhuisd. Zonder
 * deze omleiding levert de oude URL een 404 op, en die staat nog in links,
 * bookmarks en zoekresultaten.
 */
function dtc_hub_moved() {
	return array(
		'aeo-is-the-new-seo' => 'aeo-is-the-new-seo',
	);
}

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = trim( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?? '', '/' );
	$moved = dtc_hub_moved();
	if ( ! isset( $moved[ $path ] ) ) {
		return;
	}
	$post = get_page_by_path( $moved[ $path ], OBJECT, DTC_HUB_CPT );
	if ( $post ) {
		wp_safe_redirect( get_permalink( $post ), 301 );
		exit;
	}
}, 1 );

/* -------------------------------------------------------------------------
 * Sjablonen
 * ---------------------------------------------------------------------- */

add_filter( 'template_include', function ( $template ) {
	if ( is_post_type_archive( DTC_HUB_CPT ) ) {
		$file = DTC_HUB_DIR . '/archive-hub.php';
		if ( file_exists( $file ) ) {
			return $file;
		}
	}
	if ( is_tax( DTC_HUB_TAX ) ) {
		$file = DTC_HUB_DIR . '/taxonomy-hub-topic.php';
		if ( file_exists( $file ) ) {
			return $file;
		}
	}
	return $template;
}, 99 );

/* -------------------------------------------------------------------------
 * Hulpfuncties voor de sjablonen
 * ---------------------------------------------------------------------- */

/**
 * De thema's in inhoudelijke volgorde (zoals Our Method ze aanhoudt),
 * niet alfabetisch. Onbekende thema's komen erachter.
 */
function dtc_hub_topics() {
	$terms = get_terms( array(
		'taxonomy'   => DTC_HUB_TAX,
		'hide_empty' => false,
	) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	$order = array( 'strategy', 'creation', 'growth', 'media', 'hubspot', 'ai' );
	usort( $terms, function ( $a, $b ) use ( $order ) {
		$ia = array_search( $a->slug, $order, true );
		$ib = array_search( $b->slug, $order, true );
		$ia = ( false === $ia ) ? 99 : $ia;
		$ib = ( false === $ib ) ? 99 : $ib;
		return $ia === $ib ? strcmp( $a->name, $b->name ) : $ia - $ib;
	} );
	return $terms;
}

function dtc_hub_card_image( $post_id ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'large' );
		if ( $src ) {
			return $src[0];
		}
	}
	return home_url( '/wp-content/uploads/2025/12/SVM_DTC_070725_0341-1080x720.webp' );
}

/** Geschatte leestijd; toont niets bij hele korte teksten. */
function dtc_hub_reading_time( $post ) {
	$words = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	$min   = (int) max( 1, round( $words / 200 ) );
	return $words > 120 ? $min . ' min read' : '';
}

function dtc_hub_excerpt( $post, $limit = 150 ) {
	$text = $post->post_excerpt;
	if ( ! $text ) {
		$text = function_exists( 'dtc_seo_text_from_content' )
			? dtc_seo_text_from_content( $post->post_content )
			: wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	}
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	if ( mb_strlen( $text ) <= $limit ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, $limit );
	$sp  = mb_strrpos( $cut, ' ' );
	return rtrim( $sp > 60 ? mb_substr( $cut, 0, $sp ) : $cut, ' ,;:-' ) . '…';
}

/** Kaartje voor een hub-artikel; gedeeld door beide sjablonen. */
function dtc_hub_card( $post ) {
	$topics = get_the_terms( $post->ID, DTC_HUB_TAX );
	$topic  = ( $topics && ! is_wp_error( $topics ) ) ? $topics[0] : null;
	$read   = dtc_hub_reading_time( $post );
	?>
	<a class="hcard" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<span class="himg" style="background-image:url('<?php echo esc_url( dtc_hub_card_image( $post->ID ) ); ?>')"></span>
		<span class="hmeta">
			<?php if ( $topic ) : ?>
				<span class="htopic"><?php echo esc_html( $topic->name ); ?></span>
			<?php endif; ?>
			<?php if ( $read ) : ?>
				<span class="hread"><?php echo esc_html( $read ); ?></span>
			<?php endif; ?>
		</span>
		<span class="htitle"><?php echo esc_html( wp_strip_all_tags( get_the_title( $post ) ) ); ?></span>
		<span class="hexcerpt"><?php echo esc_html( dtc_hub_excerpt( $post ) ); ?></span>
		<span class="hgo">read
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
				stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<line x1="5" y1="12" x2="19" y2="12"></line>
				<polyline points="12 5 19 12 12 19"></polyline>
			</svg>
		</span>
	</a>
	<?php
}

/** Gedeelde opmaak van beide hub-sjablonen. */
function dtc_hub_styles() {
	$css = "
	/* zelfde achtergrondpatroon als de case- en 404-pagina's; overflow-x omdat
	   het patroon breder is dan het scherm */
	.dtc-hub{font-family:'Arboria',sans-serif;color:#2d2824;background-color:#fff;background-image:url('/wp-content/uploads/2025/10/Achtergrond-patroon-30.svg');background-repeat:no-repeat;background-position:center 40px;background-size:min(1500px,130%) auto;overflow-x:hidden;padding:clamp(120px,11vw,170px) 0 70px}
	.dtc-hub .in{width:76%;max-width:100%;margin:0 auto}
	.dtc-hub .kick{display:block;font-size:14px;font-weight:600;color:#8a7257;text-transform:lowercase;margin-bottom:16px}
	.dtc-hub h1{font-weight:500;font-size:clamp(44px,9vw,120px);line-height:.9;margin:0 0 26px;text-transform:lowercase}
	.dtc-hub h1 span{color:#c0ab94}
	.dtc-hub .lead{font-weight:300;font-size:18px;line-height:1.8;color:#2d2824;max-width:620px;margin:0 0 54px}
	.dtc-hub .topics{display:flex;flex-wrap:wrap;gap:10px;margin:0 0 64px;padding:6px;background:#f2ede5;border:1px solid #e5dccd;border-radius:999px;width:fit-content}
	.dtc-hub .topics a{padding:11px 26px;border-radius:999px;text-decoration:none;color:#4a443e;font-weight:500;font-size:16px;text-transform:lowercase;transition:background .2s,color .2s}
	.dtc-hub .topics a:hover{background:#fff;color:#231d1c}
	.dtc-hub .topics a.on{background:#231d1c;color:#fff}
	.dtc-hub .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:44px 34px}
	.dtc-hub .hcard{display:flex;flex-direction:column;text-decoration:none;color:inherit}
	.dtc-hub .himg{display:block;width:100%;aspect-ratio:16/10;border-radius:10px;background-size:cover;background-position:center;transition:transform .5s ease}
	.dtc-hub .hcard:hover .himg{transform:scale(1.02)}
	.dtc-hub .hmeta{display:flex;gap:14px;align-items:center;margin:18px 0 10px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#8a7257;font-weight:500}
	.dtc-hub .hread{color:#9a9186}
	.dtc-hub .htitle{display:block;font-weight:500;font-size:24px;line-height:1.2;margin-bottom:10px;text-transform:lowercase}
	.dtc-hub .hexcerpt{display:block;font-weight:300;font-size:18px;line-height:1.8;color:#2d2824}
	.dtc-hub .hgo{display:inline-flex;align-items:center;gap:8px;margin-top:16px;font-weight:500;font-size:15px;color:#c0ab94}
	.dtc-hub .hcard:hover .hgo{color:#8a7257}
	.dtc-hub .empty{border:1px dashed #ddd3c4;border-radius:14px;padding:48px 34px;color:#4a443e;font-weight:300;font-size:17px;max-width:620px}
	@media(max-width:900px){.dtc-hub{padding:14vh 0 70px}.dtc-hub .in{width:88%}.dtc-hub .grid{gap:34px 24px}}
	@media(max-width:560px){.dtc-hub .topics{width:100%;justify-content:flex-start}}
	";
	echo '<style>' . preg_replace( '/\s+/', ' ', $css ) . '</style>';
}

/** Themabalk; markeert het actieve thema. */
function dtc_hub_topic_bar( $current = null ) {
	$topics = dtc_hub_topics();
	if ( is_wp_error( $topics ) || ! $topics ) {
		return;
	}
	echo '<nav class="topics">';
	printf(
		'<a href="%s" class="%s">all</a>',
		esc_url( get_post_type_archive_link( DTC_HUB_CPT ) ),
		$current ? '' : 'on'
	);
	foreach ( $topics as $t ) {
		printf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( get_term_link( $t ) ),
			( $current && $current->term_id === $t->term_id ) ? 'on' : '',
			esc_html( $t->name )
		);
	}
	echo '</nav>';
}
