<?php
defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Default ordering in admin list: menu_order ASC
// ---------------------------------------------------------------------------

add_action( 'pre_get_posts', function ( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->get( 'post_type' ) !== 'dtc_case' ) {
		return;
	}
	// Only override when no explicit orderby is requested by the user.
	if ( $query->get( 'orderby' ) ) {
		return;
	}
	$query->set( 'orderby', 'menu_order' );
	$query->set( 'order', 'ASC' );
} );

// ---------------------------------------------------------------------------
// Add a drag-handle column to the cases list table
// ---------------------------------------------------------------------------

add_filter( 'manage_dtc_case_posts_columns', function ( array $columns ): array {
	// Insert drag-handle column right after the checkbox.
	$result = [];
	foreach ( $columns as $key => $label ) {
		if ( 'cb' === $key ) {
			$result[ $key ]       = $label;
			$result['dtc_order']  = '<span class="screen-reader-text">Volgorde</span>';
		} else {
			$result[ $key ] = $label;
		}
	}
	return $result;
} );

add_action( 'manage_dtc_case_posts_custom_column', function ( string $column, int $post_id ): void {
	if ( 'dtc_order' !== $column ) {
		return;
	}
	echo '<span class="dtc-drag-handle" title="Verslepen om te hersorteren" aria-hidden="true">'
		. '<svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor">'
		. '<rect x="3" y="3"  width="12" height="2" rx="1"/>'
		. '<rect x="3" y="8"  width="12" height="2" rx="1"/>'
		. '<rect x="3" y="13" width="12" height="2" rx="1"/>'
		. '</svg>'
		. '</span>';
}, 10, 2 );

// Make the column non-sortable and narrow.
add_filter( 'manage_edit-dtc_case_sortable_columns', function ( array $columns ): array {
	unset( $columns['dtc_order'] );
	return $columns;
} );

// ---------------------------------------------------------------------------
// Enqueue assets only on the dtc_case list screen
// ---------------------------------------------------------------------------

add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
	if ( 'edit.php' !== $hook ) {
		return;
	}
	if ( ( $_GET['post_type'] ?? '' ) !== 'dtc_case' ) {
		return;
	}

	wp_enqueue_script( 'jquery-ui-sortable' );

	wp_enqueue_style(
		'dtc-cases-admin',
		DTC_CASES_URL . 'assets/css/dtc-cases-admin.css',
		[],
		DTC_CASES_VERSION
	);

	wp_enqueue_script(
		'dtc-cases-order',
		DTC_CASES_URL . 'assets/js/dtc-cases-order.js',
		[ 'jquery', 'jquery-ui-sortable' ],
		DTC_CASES_VERSION,
		true
	);

	wp_localize_script( 'dtc-cases-order', 'dtcCasesOrder', [
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'dtc_cases_order_nonce' ),
		'saved'   => __( 'Volgorde opgeslagen', 'dtc-cases' ),
		'saving'  => __( 'Opslaan…', 'dtc-cases' ),
		'error'   => __( 'Fout bij opslaan', 'dtc-cases' ),
	] );
} );

// ---------------------------------------------------------------------------
// Inline CSS for the drag handle column width (output in <head>)
// ---------------------------------------------------------------------------

add_action( 'admin_head', function (): void {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-dtc_case' !== $screen->id ) {
		return;
	}
	echo '<style>
		.column-dtc_order { width: 36px !important; padding: 8px 6px !important; }
	</style>';
} );

// ---------------------------------------------------------------------------
// AJAX: save new order
// ---------------------------------------------------------------------------

add_action( 'wp_ajax_dtc_cases_save_order', function (): void {
	check_ajax_referer( 'dtc_cases_order_nonce', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( 'Unauthorized', 403 );
	}

	$order = isset( $_POST['order'] ) ? array_map( 'absint', (array) $_POST['order'] ) : [];

	foreach ( $order as $position => $post_id ) {
		wp_update_post( [
			'ID'         => $post_id,
			'menu_order' => (int) $position,
		] );
	}

	wp_send_json_success();
} );
