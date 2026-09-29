<?php
defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Admin asset enqueue
// ---------------------------------------------------------------------------

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	global $post;

	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	if ( ! $post || 'dtc_case' !== $post->post_type ) {
		return;
	}

	wp_enqueue_media();

	wp_enqueue_script(
		'dtc-cases-admin',
		DTC_CASES_URL . 'assets/js/dtc-cases-admin.js',
		[ 'jquery', 'jquery-ui-sortable' ],
		DTC_CASES_VERSION,
		true
	);

	wp_enqueue_style(
		'dtc-cases-admin',
		DTC_CASES_URL . 'assets/css/dtc-cases-admin.css',
		[],
		DTC_CASES_VERSION
	);
} );

// ---------------------------------------------------------------------------
// Register meta boxes
// ---------------------------------------------------------------------------

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dtc_case_page_settings', 'Page Template',                          'dtc_cases_mb_page_settings', 'dtc_case', 'side',   'high' );
	add_meta_box( 'dtc_case_hero',          'Hero',                                   'dtc_cases_mb_hero',          'dtc_case', 'normal', 'high' );
	add_meta_box( 'dtc_case_brand',         'Sectie 1 — The Brand (tekst + portret)', 'dtc_cases_mb_brand',         'dtc_case', 'normal', 'high' );
	add_meta_box( 'dtc_case_details',       'Project Details (3 kolommen)',            'dtc_cases_mb_details',       'dtc_case', 'normal', 'high' );
	add_meta_box( 'dtc_case_collage',       'Sectie 2 — Collage (grote + kleine afbeelding)', 'dtc_cases_mb_collage', 'dtc_case', 'normal', 'high' );
	add_meta_box( 'dtc_case_strategy',      'Sectie 3 — Strategy (tekst + hoge afbeelding)', 'dtc_cases_mb_strategy', 'dtc_case', 'normal', 'high' );
	add_meta_box( 'dtc_case_creation',      'Sectie 4 — Creation (tekst + 3 afbeeldingen)', 'dtc_cases_mb_creation', 'dtc_case', 'normal', 'high' );
	add_meta_box( 'dtc_case_fullwidth',     'Sectie 5 — Full Width Afbeelding',        'dtc_cases_mb_fullwidth',    'dtc_case', 'normal', 'high' );
	add_meta_box( 'dtc_case_growth',        'Sectie 6 — Growth (tekst + hoge afbeelding)', 'dtc_cases_mb_growth',   'dtc_case', 'normal', 'high' );
	add_meta_box( 'dtc_case_contact',       'Contact Person (Want to Know More?)',     'dtc_cases_mb_contact',      'dtc_case', 'normal', 'high' );
} );

// ---------------------------------------------------------------------------
// Render helpers
// ---------------------------------------------------------------------------

function dtc_cases_text_field( WP_Post $post, string $key, string $label, string $type = 'text' ): void {
	$val = esc_attr( (string) get_post_meta( $post->ID, $key, true ) );
	printf(
		'<p class="dtc-cases-field">
			<label for="%1$s"><strong>%2$s</strong></label>
			<input type="%3$s" id="%1$s" name="%1$s" value="%4$s" class="widefat" />
		</p>',
		esc_attr( $key ),
		esc_html( $label ),
		esc_attr( $type ),
		$val
	);
}

function dtc_cases_textarea_field( WP_Post $post, string $key, string $label ): void {
	$val = esc_textarea( (string) get_post_meta( $post->ID, $key, true ) );
	printf(
		'<p class="dtc-cases-field">
			<label for="%1$s"><strong>%2$s</strong></label>
			<textarea id="%1$s" name="%1$s" rows="6" class="widefat">%3$s</textarea>
		</p>',
		esc_attr( $key ),
		esc_html( $label ),
		$val
	);
}

function dtc_cases_images_field( WP_Post $post, string $key, string $label ): void {
	$ids = get_post_meta( $post->ID, $key, true );
	if ( ! is_array( $ids ) ) {
		$ids = [];
	}

	echo '<div class="dtc-cases-field">';
	echo '<p><strong>' . esc_html( $label ) . '</strong></p>';
	echo '<div class="dtc-cases-images-container" data-name="' . esc_attr( $key ) . '">';

	foreach ( $ids as $id ) {
		$id = absint( $id );
		if ( ! $id ) {
			continue;
		}
		$thumb = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( ! $thumb ) {
			continue;
		}
		echo '<div class="dtc-cases-img-item" data-id="' . esc_attr( $id ) . '">';
		echo '<span class="dtc-cases-drag-handle" title="Sleep om te herordenen"><svg width="10" height="14" viewBox="0 0 10 14" fill="currentColor" aria-hidden="true"><circle cx="2.5" cy="2" r="1.5"/><circle cx="7.5" cy="2" r="1.5"/><circle cx="2.5" cy="7" r="1.5"/><circle cx="7.5" cy="7" r="1.5"/><circle cx="2.5" cy="12" r="1.5"/><circle cx="7.5" cy="12" r="1.5"/></svg></span>';
		echo '<img src="' . esc_url( $thumb ) . '" />';
		echo '<button type="button" class="dtc-cases-remove-image" aria-label="Remove image">&times;</button>';
		echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $id ) . '" />';
		echo '</div>';
	}

	echo '</div>';
	echo '<button type="button" class="button dtc-cases-upload-multiple">+ Add Images</button>';
	echo '</div>';
}

// ---------------------------------------------------------------------------
// Visual layout slot helpers
// ---------------------------------------------------------------------------

/**
 * Named image slots per section layout.
 * Order matches the template: index 0 = feature, index 1 = secondary.
 */
function dtc_cases_get_layout_slots( string $layout ): array {
	$map = [
		// Sectie 1 — Brand: 1 portret naast tekst
		'brand' => [
			[ 'label' => 'Portret afbeelding (rechts naast tekst)' ],
		],
		// Sectie 2 — Collage: grote afbeelding (achtergrond) + kleine absolute afbeelding
		'collage' => [
			[ 'label' => 'Grote afbeelding (achtergrond, rechts)' ],
			[ 'label' => 'Kleine afbeelding (absoluut, linksboven)' ],
		],
		// Sectie 3 — Strategy: 1 hoge afbeelding rechts
		'strategy' => [
			[ 'label' => 'Afbeelding rechterkolom (hoog formaat)' ],
		],
		// Sectie 4 — Creation: 2 abs afbeeldingen links + 1 rechts onder tekst
		'creation' => [
			[ 'label' => 'Linker afbeelding boven (groot, achtergrond)' ],
			[ 'label' => 'Linker afbeelding onder (absoluut, overlappend)' ],
			[ 'label' => 'Rechter afbeelding (onder tekst)' ],
		],
		// Sectie 6 — Growth: 1 hoge afbeelding rechts
		'growth' => [
			[ 'label' => 'Grote afbeelding rechterkolom (hoog formaat)' ],
		],
	];
	return $map[ $layout ] ?? $map['brand'];
}

/**
 * Renders a visual layout preview: proportional gray placeholder boxes,
 * one per image slot, clickable to open the WP media picker.
 */
function dtc_cases_layout_images_field( WP_Post $post, string $key, string $layout ): void {
	$ids   = get_post_meta( $post->ID, $key, true );
	if ( ! is_array( $ids ) ) {
		$ids = [];
	}
	$slots = dtc_cases_get_layout_slots( $layout );

	echo '<div class="dtc-cases-field">';
	echo '<div class="dtc-cases-layout-preview dtc-layout-' . esc_attr( $layout ) . '" data-name="' . esc_attr( $key ) . '">';

	foreach ( $slots as $i => $slot ) {
		$img_id = isset( $ids[ $i ] ) ? absint( $ids[ $i ] ) : 0;
		$thumb  = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
		$cls    = $thumb ? ' has-image' : '';

		echo '<div class="dtc-cases-slot' . esc_attr( $cls ) . '" data-slot="' . (int) $i . '" data-label="' . esc_attr( $slot['label'] ) . '">';

		// Actual image shown when a photo is selected
		if ( $thumb ) {
			echo '<img class="dtc-cases-slot__img" src="' . esc_url( $thumb ) . '" alt="" />';
			echo '<button type="button" class="dtc-cases-slot__remove" aria-label="Verwijder">&times;</button>';
		}

		// Placeholder / "click to add" state
		echo '<span class="dtc-cases-slot__placeholder">';
		echo '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
		echo '<rect x="3" y="3" width="18" height="18" rx="2"/>';
		echo '<circle cx="8.5" cy="8.5" r="1.5"/>';
		echo '<polyline points="21 15 16 10 5 21"/>';
		echo '</svg>';
		echo '<span class="dtc-cases-slot__label">' . esc_html( $slot['label'] ) . '</span>';
		echo '</span>';

		// Always-present hidden input preserves slot order on form submit
		echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $img_id ) . '" />';

		echo '</div>'; // .dtc-cases-slot
	}

	echo '</div>'; // .dtc-cases-layout-preview
	echo '</div>'; // .dtc-cases-field
}

function dtc_cases_single_image_field( WP_Post $post, string $key, string $label ): void {
	$id          = absint( get_post_meta( $post->ID, $key, true ) );
	$preview_img = '';

	if ( $id ) {
		$thumb = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( $thumb ) {
			$preview_img = '<img src="' . esc_url( $thumb ) . '" />';
		}
	}

	echo '<div class="dtc-cases-field">';
	echo '<p><strong>' . esc_html( $label ) . '</strong></p>';
	echo '<div class="dtc-cases-single-image-wrap">';
	echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $id ) . '" />';
	echo '<button type="button" class="button dtc-cases-upload-single">Select Image</button>';
	echo '<div class="dtc-cases-single-preview">' . $preview_img . '</div>';
	if ( $id ) {
		echo '<button type="button" class="button dtc-cases-remove-single">Remove</button>';
	}
	echo '</div>';
	echo '</div>';
}

// ---------------------------------------------------------------------------
// Meta box callbacks
// ---------------------------------------------------------------------------

function dtc_cases_mb_page_settings( WP_Post $post ): void {
	$current = (string) get_post_meta( $post->ID, 'dtc_case_page_template', true );
	if ( ! $current ) { $current = 'default'; }

	echo '<p style="margin:0 0 8px;"><strong>Paginatemplate</strong></p>';
	echo '<select name="dtc_case_page_template" style="width:100%;">';

	// ── WordPress opties ──────────────────────────────────────────────
	echo '<optgroup label="— WordPress —">';
	printf( '<option value="default"%s>Standaard (thema-menu &amp; footer)</option>', selected( $current, 'default', false ) );
	printf( '<option value="blank"%s>Fullscreen (geen menu &amp; footer)</option>',   selected( $current, 'blank',   false ) );
	echo '</optgroup>';

	// ── Divi Theme Builder templates ──────────────────────────────────
	$divi_templates = post_type_exists( 'et_theme_builder_template' )
		? get_posts( [
			'post_type'      => 'et_theme_builder_template',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		] )
		: [];

	if ( ! empty( $divi_templates ) ) {
		echo '<optgroup label="— Divi Theme Builder —">';
		foreach ( $divi_templates as $tpl ) {
			$val = 'divi_' . $tpl->ID;
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $val ),
				selected( $current, $val, false ),
				esc_html( $tpl->post_title )
			);
		}
		echo '</optgroup>';
	}

	echo '</select>';

	if ( empty( $divi_templates ) ) {
		echo '<p style="margin:6px 0 0;color:#666;font-size:12px;">Geen Divi Theme Builder templates gevonden. Maak ze aan via <em>Divi → Theme Builder</em>.</p>';
	} else {
		echo '<p style="margin:6px 0 0;color:#666;font-size:12px;">Kies een Divi-template voor een custom header &amp; footer, of <em>Fullscreen</em> voor een volledig blanco pagina.</p>';
	}
}

function dtc_cases_mb_hero( WP_Post $post ): void {
	wp_nonce_field( 'dtc_cases_save_meta', 'dtc_cases_nonce' );
	dtc_cases_text_field( $post, 'dtc_case_hero_subtitle', 'Hero Subtitle (e.g., "one connected retail partnership")' );
}

function dtc_cases_mb_brand( WP_Post $post ): void {
	echo '<p style="color:#888;font-size:12px;margin-bottom:12px;">Linkerkolom: naam + tekst. Rechterkolom: 1 portretfoto.</p>';
	dtc_cases_text_field( $post, 'dtc_case_brand_subtitle',       'Intro zin (bijv. "For over ten years…")' );
	dtc_cases_textarea_field( $post, 'dtc_case_brand_text',       'Body tekst' );
	dtc_cases_text_field( $post, 'dtc_case_brand_cta_text',       'CTA knop tekst' );
	dtc_cases_text_field( $post, 'dtc_case_brand_cta_url',        'CTA knop URL' );
	dtc_cases_textarea_field( $post, 'dtc_case_brand_image_text', 'Overlay tekst op afbeelding' );
	dtc_cases_layout_images_field( $post, 'dtc_case_brand_images', 'brand' );
}

function dtc_cases_mb_collage( WP_Post $post ): void {
	echo '<p style="color:#888;font-size:12px;margin-bottom:12px;">Grote afbeelding op de achtergrond (rechts verschoven) + kleine afbeelding linksboven eroverheen.</p>';
	dtc_cases_layout_images_field( $post, 'dtc_case_collage_images', 'collage' );
}

function dtc_cases_mb_strategy( WP_Post $post ): void {
	echo '<p style="color:#888;font-size:12px;margin-bottom:12px;">Linkerkolom: tekst. Rechterkolom: 1 hoge afbeelding.</p>';
	dtc_cases_text_field( $post, 'dtc_case_strategy_subtitle',       'Intro zin' );
	dtc_cases_textarea_field( $post, 'dtc_case_strategy_text',       'Body tekst' );
	dtc_cases_text_field( $post, 'dtc_case_strategy_cta_text',       'CTA knop tekst' );
	dtc_cases_text_field( $post, 'dtc_case_strategy_cta_url',        'CTA knop URL' );
	dtc_cases_layout_images_field( $post, 'dtc_case_strategy_images', 'strategy' );
}

function dtc_cases_mb_creation( WP_Post $post ): void {
	echo '<p style="color:#888;font-size:12px;margin-bottom:12px;">Linkerkolom: 2 overlappende afbeeldingen. Rechterkolom: tekst + 1 afbeelding eronder.</p>';
	dtc_cases_text_field( $post, 'dtc_case_creation_subtitle',       'Intro zin' );
	dtc_cases_textarea_field( $post, 'dtc_case_creation_text',       'Body tekst' );
	dtc_cases_text_field( $post, 'dtc_case_creation_cta_text',       'CTA knop tekst' );
	dtc_cases_text_field( $post, 'dtc_case_creation_cta_url',        'CTA knop URL' );
	dtc_cases_layout_images_field( $post, 'dtc_case_creation_images', 'creation' );
}

function dtc_cases_mb_fullwidth( WP_Post $post ): void {
	echo '<p style="color:#888;font-size:12px;margin-bottom:12px;">Één afbeelding die 100% breed wordt weergegeven.</p>';
	dtc_cases_single_image_field( $post, 'dtc_case_fullwidth_image', 'Full width afbeelding' );
}

function dtc_cases_mb_growth( WP_Post $post ): void {
	echo '<p style="color:#888;font-size:12px;margin-bottom:12px;">Linkerkolom: tekst. Rechterkolom: 1 grote hoge afbeelding.</p>';
	dtc_cases_text_field( $post, 'dtc_case_growth_subtitle',       'Intro zin' );
	dtc_cases_textarea_field( $post, 'dtc_case_growth_text',       'Body tekst' );
	dtc_cases_text_field( $post, 'dtc_case_growth_cta_text',       'CTA knop tekst' );
	dtc_cases_text_field( $post, 'dtc_case_growth_cta_url',        'CTA knop URL' );
	dtc_cases_layout_images_field( $post, 'dtc_case_growth_images', 'growth' );
}

function dtc_cases_mb_details( WP_Post $post ): void {
	echo '<p style="color:#666;font-style:italic;margin-bottom:12px;">Vul maximaal 3 kolommen in. Elk item op een nieuwe regel in het tekstveld.</p>';
	for ( $i = 1; $i <= 3; $i++ ) {
		echo '<div style="background:#f9f9f9;border:1px solid #e0e0e0;border-radius:4px;padding:12px 16px;margin-bottom:12px;">';
		echo '<p style="margin:0 0 6px;font-weight:600;">Kolom ' . $i . '</p>';
		dtc_cases_text_field( $post, 'dtc_case_detail_' . $i . '_label', 'Label (e.g., "Project details")' );
		dtc_cases_textarea_field( $post, 'dtc_case_detail_' . $i . '_text', 'Items (één per regel)' );
		echo '</div>';
	}
}

function dtc_cases_mb_contact( WP_Post $post ): void {
	dtc_cases_text_field( $post, 'dtc_case_contact_name',  'Full Name' );
	dtc_cases_text_field( $post, 'dtc_case_contact_role',  'Role / Job Title' );
	dtc_cases_text_field( $post, 'dtc_case_contact_phone', 'Phone Number' );
	dtc_cases_text_field( $post, 'dtc_case_contact_email', 'Email Address', 'email' );
	dtc_cases_single_image_field( $post, 'dtc_case_contact_image', 'Contact Photo' );
}

// ---------------------------------------------------------------------------
// Save meta
// ---------------------------------------------------------------------------

add_action( 'save_post_dtc_case', function ( int $post_id ): void {
	// Bail on autosave, revisions, or without our nonce.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['dtc_cases_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dtc_cases_nonce'] ) ), 'dtc_cases_save_meta' )
	) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Page template selector.
	if ( isset( $_POST['dtc_case_page_template'] ) ) {
		$raw = sanitize_text_field( wp_unslash( $_POST['dtc_case_page_template'] ) );
		// Allow 'default', 'blank', or 'divi_{integer}'.
		if ( in_array( $raw, [ 'default', 'blank' ], true ) ) {
			$tpl = $raw;
		} elseif ( preg_match( '/^divi_(\d+)$/', $raw, $m ) && get_post( (int) $m[1] ) ) {
			$tpl = $raw;
		} else {
			$tpl = 'default';
		}
		update_post_meta( $post_id, 'dtc_case_page_template', $tpl );
	}

	// Plain text fields.
	$text_fields = [
		'dtc_case_hero_subtitle',
		'dtc_case_contact_name',
		'dtc_case_contact_role',
		'dtc_case_contact_phone',
		'dtc_case_contact_email',
		'dtc_case_detail_1_label',
		'dtc_case_detail_2_label',
		'dtc_case_detail_3_label',
		'dtc_case_brand_subtitle',
		'dtc_case_brand_cta_text',
		'dtc_case_brand_cta_url',
		'dtc_case_strategy_subtitle',
		'dtc_case_strategy_cta_text',
		'dtc_case_strategy_cta_url',
		'dtc_case_creation_subtitle',
		'dtc_case_creation_cta_text',
		'dtc_case_creation_cta_url',
		'dtc_case_growth_subtitle',
		'dtc_case_growth_cta_text',
		'dtc_case_growth_cta_url',
	];
	foreach ( $text_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta(
				$post_id,
				$field,
				sanitize_text_field( wp_unslash( $_POST[ $field ] ) )
			);
		}
	}

	// Textarea fields.
	$textarea_fields = [
		'dtc_case_brand_text',
		'dtc_case_brand_image_text',
		'dtc_case_strategy_text',
		'dtc_case_strategy_image_text',
		'dtc_case_creation_text',
		'dtc_case_creation_image_text',
		'dtc_case_growth_text',
		'dtc_case_growth_image_text',
		'dtc_case_detail_1_text',
		'dtc_case_detail_2_text',
		'dtc_case_detail_3_text',
	];
	foreach ( $textarea_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta(
				$post_id,
				$field,
				sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) )
			);
		}
	}

	// Multiple-image fields (arrays of attachment IDs).
	$multi_image_fields = [
		'dtc_case_brand_images',
		'dtc_case_collage_images',
		'dtc_case_strategy_images',
		'dtc_case_creation_images',
		'dtc_case_growth_images',
	];
	foreach ( $multi_image_fields as $field ) {
		if ( isset( $_POST[ $field ] ) && is_array( $_POST[ $field ] ) ) {
			$ids = array_map( 'absint', (array) wp_unslash( $_POST[ $field ] ) );
			// Preserve internal zeros (empty slots keep their position) but trim trailing zeros.
			while ( ! empty( $ids ) && 0 === end( $ids ) ) {
				array_pop( $ids );
			}
			update_post_meta( $post_id, $field, $ids );
		} else {
			update_post_meta( $post_id, $field, [] );
		}
	}

	// Single image fields.
	foreach ( [ 'dtc_case_contact_image', 'dtc_case_fullwidth_image' ] as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, absint( $_POST[ $field ] ) );
		}
	}
} );
