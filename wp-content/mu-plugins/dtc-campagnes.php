<?php
/**
 * Plugin Name: DTC campagnes
 * Description: De kaarten op de insights-pagina (campaigns en trends) als gewone items in het beheer, in plaats van als vaste code in de pagina. Beheer zit onder het menu dtc agency; de pagina haalt ze op met een plaatshouder in de opmaak.
 * Version: 1.0
 *
 * Terugdraaien = dit bestand verwijderen. De pagina toont dan niets meer op de
 * plek van de rails; de kaarten zelf blijven in de database staan.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

const DTC_CA_CPT  = 'campagne';
const DTC_CA_TAX  = 'campagne_rail';
const DTC_CA_LINK = '_dtc_campagne_link';
const DTC_CA_NAAM = '_dtc_campagne_naam';

/* ============================================================ post type */

add_action( 'init', function () {

	register_taxonomy( DTC_CA_TAX, DTC_CA_CPT, array(
		'labels'            => array(
			'name'          => 'Rails',
			'singular_name' => 'Rail',
			'menu_name'     => 'Rails',
			'add_new_item'  => 'Nieuwe rail',
		),
		'public'            => false,
		'show_ui'           => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_menu'      => defined( 'DTC_TK_SLUG' ) ? DTC_TK_SLUG : true,
	) );

	register_post_type( DTC_CA_CPT, array(
		'labels'        => array(
			'name'          => 'Campagnes',
			'singular_name' => 'Kaart',
			'menu_name'     => 'Campagnes',
			'add_new_item'  => 'Nieuwe kaart',
			'edit_item'     => 'Kaart bewerken',
			'search_items'  => 'Kaarten zoeken',
			'not_found'     => 'Nog geen kaarten.',
		),
		/* Deze kaarten hebben geen eigen pagina: ze verwijzen naar een case of
		   een hub-artikel. Vandaar public = false, dat scheelt ook een
		   overbodige URL in de sitemap. */
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => defined( 'DTC_TK_SLUG' ) ? DTC_TK_SLUG : true,
		'menu_icon'     => 'dashicons-images-alt2',
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		'taxonomies'    => array( DTC_CA_TAX ),
	) );

	/* de twee rails die de insights-pagina gebruikt, één keer aanmaken */
	foreach ( array( 'campaigns' => 'campaigns', 'trends' => 'trends' ) as $slug => $naam ) {
		if ( ! term_exists( $slug, DTC_CA_TAX ) ) {
			wp_insert_term( $naam, DTC_CA_TAX, array( 'slug' => $slug ) );
		}
	}
} );

/* De volgorde in het beheer is de volgorde op de pagina. */
add_filter( 'pre_get_posts', function ( $vraag ) {
	if ( ! is_admin() || ! $vraag->is_main_query() ) { return; }
	if ( DTC_CA_CPT !== $vraag->get( 'post_type' ) ) { return; }
	if ( ! $vraag->get( 'orderby' ) ) {
		$vraag->set( 'orderby', 'menu_order title' );
		$vraag->set( 'order', 'ASC' );
	}
	/* de keuzelijst geeft de slug door; zonder deze regel zoekt WordPress op id */
	if ( ! empty( $_GET[ DTC_CA_TAX ] ) && '0' !== $_GET[ DTC_CA_TAX ] ) {
		$vraag->set( DTC_CA_TAX, sanitize_key( $_GET[ DTC_CA_TAX ] ) );
	}
} );

/* ============================================================ het linkveld */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dtc-campagne-link', 'Waar wijst de kaart naartoe', function ( $post ) {
		$link = get_post_meta( $post->ID, DTC_CA_LINK, true );
		wp_nonce_field( 'dtc_campagne_link', 'dtc_campagne_nonce' );
		?>
		<p>
			<input type="text" name="dtc_campagne_link" value="<?php echo esc_attr( $link ); ?>"
			       class="large-text" placeholder="/nissan/ of https://...">
		</p>
		<p class="description">Een pad op deze site (<code>/nissan/</code>) of een volledig adres. Leeg = de kaart is niet aanklikbaar.</p>
		<?php $toon = get_post_meta( $post->ID, DTC_CA_NAAM, true ); ?>
		<p><label>
			<input type="checkbox" name="dtc_campagne_naam" value="1" <?php checked( '' === $toon ? '1' : $toon, '1' ); ?>>
			de naam onder de kaart tonen
		</label></p>
		<p class="description">Uit = alleen het beeld, zonder tekst en pijl. De titel blijft wel staan om de kaart hier terug te vinden.</p>
		<?php
	}, DTC_CA_CPT, 'normal', 'high' );
} );

add_action( 'save_post_' . DTC_CA_CPT, function ( $post_id ) {
	if ( ! isset( $_POST['dtc_campagne_nonce'] ) || ! wp_verify_nonce( $_POST['dtc_campagne_nonce'], 'dtc_campagne_link' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	update_post_meta( $post_id, DTC_CA_NAAM, empty( $_POST['dtc_campagne_naam'] ) ? '0' : '1' );

	$ruw = trim( (string) wp_unslash( $_POST['dtc_campagne_link'] ?? '' ) );
	if ( '' === $ruw ) {
		delete_post_meta( $post_id, DTC_CA_LINK );
		return;
	}
	/* een pad op deze site mag gewoon blijven staan; de rest langs esc_url_raw */
	$schoon = ( '/' === substr( $ruw, 0, 1 ) ) ? '/' . ltrim( sanitize_text_field( $ruw ), '/' ) : esc_url_raw( $ruw );
	update_post_meta( $post_id, DTC_CA_LINK, $schoon );
} );

/* een kolom in de lijst, zodat je in één oogopslag ziet waar alles heen gaat */
add_filter( 'manage_' . DTC_CA_CPT . '_posts_columns', function ( $kolommen ) {
	$nieuw = array();
	foreach ( $kolommen as $sleutel => $naam ) {
		$nieuw[ $sleutel ] = $naam;
		if ( 'title' === $sleutel ) {
			$nieuw['dtc_beeld'] = 'Beeld';
			$nieuw['dtc_link']  = 'Link';
		}
	}
	return $nieuw;
} );

add_action( 'manage_' . DTC_CA_CPT . '_posts_custom_column', function ( $kolom, $post_id ) {
	if ( 'dtc_link' === $kolom ) {
		$link = get_post_meta( $post_id, DTC_CA_LINK, true );
		echo $link ? '<code>' . esc_html( $link ) . '</code>' : '<span style="color:#a99a86">—</span>';
	}
	if ( 'dtc_beeld' === $kolom ) {
		echo has_post_thumbnail( $post_id )
			? get_the_post_thumbnail( $post_id, array( 54, 54 ), array( 'style' => 'border-radius:8px;object-fit:cover;width:54px;height:54px' ) )
			: '<span style="color:#b4492f">ontbreekt</span>';
	}
}, 10, 2 );

/* een keuzelijst om op rail te filteren, en standaard op rail sorteren */
add_action( 'restrict_manage_posts', function ( $post_type ) {
	if ( DTC_CA_CPT !== $post_type ) { return; }
	$gekozen = isset( $_GET[ DTC_CA_TAX ] ) ? sanitize_key( $_GET[ DTC_CA_TAX ] ) : '';
	wp_dropdown_categories( array(
		'taxonomy'         => DTC_CA_TAX,
		'name'             => DTC_CA_TAX,
		'show_option_all'  => 'Alle rails',
		'value_field'      => 'slug',
		'selected'         => $gekozen,
		'hierarchical'     => true,
		'hide_empty'       => false,
		'show_count'       => true,
	) );
} );

/* de beheerschermen in de huisstijl van dtc agency */
add_action( 'admin_init', function () {
	if ( function_exists( 'dtc_tk_huisstijl' ) ) {
		dtc_tk_huisstijl( array( DTC_CA_CPT, DTC_CA_TAX ) );
	}
} );

/* ============================================================ op de site */

/** de kaarten van één rail ophalen */
function dtc_ca_kaarten( $rail ) {
	return get_posts( array(
		'post_type'      => DTC_CA_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'tax_query'      => array( array(
			'taxonomy' => DTC_CA_TAX,
			'field'    => 'slug',
			'terms'    => $rail,
		) ),
	) );
}

/** de rail als HTML, precies zoals de insights-pagina hem verwacht */
function dtc_ca_rail( $rail, $richting, $id = '', $meer = '' ) {
	$kaarten = dtc_ca_kaarten( $rail );
	if ( ! $kaarten ) { return ''; }

	$pijl = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"'
		. ' stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
		. '<line x1="5" y1="12" x2="19" y2="12"></line>'
		. '<polyline points="12 5 19 12 12 19"></polyline></svg>';

	$uit = '<div' . ( $id ? ' id="' . esc_attr( $id ) . '"' : '' )
		. ' class="rail rail--' . esc_attr( $richting ) . '">';
	foreach ( $kaarten as $kaart ) {
		$link  = get_post_meta( $kaart->ID, DTC_CA_LINK, true );
		$naam  = $kaart->post_title;
		$beeld = get_the_post_thumbnail( $kaart->ID, 'large', array(
			'alt'      => $naam,
			'loading'  => 'lazy',
			'decoding' => 'async',
		) );
		if ( ! $beeld ) { continue; }

		/* een kaart kan bewust alleen het beeld tonen; dat staat zo bij de
		   meeste kaarten in de trends-rail. */
		$toon  = get_post_meta( $kaart->ID, DTC_CA_NAAM, true );
		$tekst = ( '0' !== $toon && '' !== trim( $naam ) )
			? '<span class="nm">' . esc_html( $naam ) . ' ' . $pijl . '</span>'
			: '';

		$binnen = '<span class="im">' . $beeld . '</span>' . $tekst;

		$uit .= $link
			? '<a class="c" href="' . esc_url( $link ) . '">' . $binnen . '</a>'
			: '<span class="c">' . $binnen . '</span>';
	}

	/* afsluitende kaart die doorverwijst, bijvoorbeeld naar de
	   insights-pagina waar alle campagnes staan */
	if ( $meer ) {
		$pagina = get_page_by_path( trim( $meer, '/' ) );
		$adres  = $pagina ? get_permalink( $pagina ) : home_url( '/' . trim( $meer, '/' ) . '/' );
		$uit .= '<a class="c c--meer" href="' . esc_url( $adres ) . '">'
			. '<span class="im im--meer"><span class="cirkel">'
			. '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"'
			. ' stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
			. '<line x1="5" y1="12" x2="19" y2="12"></line>'
			. '<polyline points="12 5 19 12 12 19"></polyline></svg>'
			. '</span></span>'
			. '<span class="nm">see more ' . $pijl . '</span></a>';
	}

	return $uit . '</div>';
}

/**
 * De insights-pagina is één codeblok. Shortcodes worden daarbinnen niet
 * uitgevoerd, dus staat er een plaatshouder in de vorm van een HTML-opmerking:
 * <!--dtc-rail:campaigns:rechts:rail-werk-->. Het derde deel is het id dat de
 * pijltjesknoppen naast de rail gebruiken om hem op te schuiven.
 */
add_filter( 'the_content', function ( $inhoud ) {
	if ( is_admin() || false === strpos( $inhoud, '<!--dtc-rail:' ) ) { return $inhoud; }
	return preg_replace_callback(
		'/<!--dtc-rail:([a-z0-9_-]+)(?::(links|rechts))?(?::([a-z0-9_-]+))?(?::meer=([a-z0-9_\/-]+))?-->/i',
		function ( $m ) {
			return dtc_ca_rail(
				$m[1],
				isset( $m[2] ) && $m[2] ? $m[2] : 'rechts',
				isset( $m[3] ) ? $m[3] : '',
				isset( $m[4] ) ? $m[4] : ''
			);
		},
		$inhoud
	);
}, 9 );

/* en voor wie hem buiten een codeblok wil gebruiken */
add_shortcode( 'dtc_campagnes', function ( $atts ) {
	$a = shortcode_atts( array( 'rail' => 'campaigns', 'richting' => 'rechts', 'id' => '', 'meer' => '' ), $atts );
	return dtc_ca_rail( sanitize_key( $a['rail'] ), 'links' === $a['richting'] ? 'links' : 'rechts', sanitize_key( $a['id'] ), sanitize_text_field( $a['meer'] ) );
} );
