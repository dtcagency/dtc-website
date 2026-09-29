<?php
/**
 * Divi 5 whole-site migration processor for DiviFlash imports.
 */

if ( ! function_exists( 'df_divi5_site_migrator_should_run' ) ) {
	function df_divi5_site_migrator_should_run() {
		return function_exists( 'et_builder_d5_enabled' )
			&& et_builder_d5_enabled();
	}
}

if ( ! function_exists( 'df_divi5_site_migrator_get_cookies' ) ) {
	function df_divi5_site_migrator_get_cookies() {
		$cookies = [];

		foreach ( $_COOKIE as $name => $value ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- forwarded for authenticated loopback request.
			$cookies[] = new \WP_Http_Cookie(
				[
					'name'  => $name,
					'value' => wp_unslash( $value ),
				]
			);
		}

		return $cookies;
	}
}

if ( ! function_exists( 'df_divi5_site_migrator_dispatch' ) ) {
	function df_divi5_site_migrator_dispatch( $source = '' ) {
		if ( ! df_divi5_site_migrator_should_run() ) {
			// error_log( '[DIFL D5 MIGRATION] dispatch skipped: Divi 5 auto conversion is unavailable.' );
			return false;
		}

		$source       = is_string( $source ) ? $source : '';
		$request_args = [
			'timeout'   => 600,
			'blocking'  => true,
			'sslverify' => false,
			'body'      => [
				'action' => 'difl_run_divi5_site_migration',
				'nonce'  => wp_create_nonce( 'difl_run_divi5_site_migration' ),
				'source' => $source,
			],
			'cookies'   => df_divi5_site_migrator_get_cookies(),
		];

		// error_log( sprintf( '[DIFL D5 MIGRATION] dispatch requested source=%s', $source ?: 'unspecified' ) );

		$response = wp_remote_post( admin_url( 'admin-ajax.php' ), $request_args );

		if ( is_wp_error( $response ) ) {
			// error_log( sprintf( '[DIFL D5 MIGRATION] dispatch failed source=%s error=%s', $source ?: 'unspecified', $response->get_error_message() ) );
			return false;
		}

		// error_log(
		// 	sprintf(
		// 		'[DIFL D5 MIGRATION] dispatch response source=%s code=%s body=%s',
		// 		$source ?: 'unspecified',
		// 		wp_remote_retrieve_response_code( $response ),
		// 		wp_remote_retrieve_body( $response )
		// 	)
		// );

		return true;
	}
}

if ( ! function_exists( 'df_divi5_site_migrator_bootstrap' ) ) {
	function df_divi5_site_migrator_bootstrap() {
		if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
			// error_log( '[DIFL D5 MIGRATION] bootstrap skipped: Divi 5 is not enabled.' );
			return false;
		}

		$template_dir      = untrailingslashit( get_template_directory() );
		$d5_readiness_file = $template_dir . '/d5-readiness/d5-readiness.php';

		if ( file_exists( $d5_readiness_file ) ) {
			require_once $d5_readiness_file;
		}

		if ( class_exists( '\ET_D5_Readiness' ) ) {
			\ET_D5_Readiness::includes();
		}

		$required_files = [
			$template_dir . '/includes/builder-5/server/Packages/GlobalData/GlobalPresetItem.php',
			$template_dir . '/includes/builder-5/server/Packages/GlobalData/GlobalPresetItemUtils.php',
			$template_dir . '/includes/builder-5/server/Packages/GlobalData/GlobalPresetItemGroup.php',
			$template_dir . '/includes/builder-5/server/Packages/GlobalData/GlobalPresetItemGroupUtils.php',
			$template_dir . '/includes/builder-5/server/Packages/GlobalData/GlobalPresetItemGroupAttrNameResolved.php',
			$template_dir . '/includes/builder-5/server/Packages/GlobalData/GlobalPresetItemGroupAttrNameResolver.php',
			$template_dir . '/includes/builder-5/server/Packages/GlobalData/Utils/PresetContentUtils.php',
			$template_dir . '/includes/builder-5/server/Packages/GlobalData/GlobalPreset.php',
			$template_dir . '/includes/builder-5/server/VisualBuilder/Saving/SavingUtility.php',
		];

		foreach ( $required_files as $required_file ) {
			if ( file_exists( $required_file ) ) {
				require_once $required_file;
			}
		}

		$is_ready = class_exists( '\Divi\D5_Readiness\Server\Conversion' )
			&& class_exists( '\Divi\D5_Readiness\Server\PostTypes' )
			&& class_exists( '\ET\Builder\Packages\Conversion\Conversion' )
			&& class_exists( '\ET\Builder\Packages\GlobalData\GlobalPreset' )
			&& function_exists( '\Divi\D5_Readiness\Helpers\clear_modules_conversation_cache' );

		// error_log(
		// 	sprintf(
		// 		'[DIFL D5 MIGRATION] bootstrap status conversion=%s posttypes=%s builder_conversion=%s global_preset=%s helpers=%s',
		// 		class_exists( '\Divi\D5_Readiness\Server\Conversion' ) ? 'yes' : 'no',
		// 		class_exists( '\Divi\D5_Readiness\Server\PostTypes' ) ? 'yes' : 'no',
		// 		class_exists( '\ET\Builder\Packages\Conversion\Conversion' ) ? 'yes' : 'no',
		// 		class_exists( '\ET\Builder\Packages\GlobalData\GlobalPreset' ) ? 'yes' : 'no',
		// 		function_exists( '\Divi\D5_Readiness\Helpers\clear_modules_conversation_cache' ) ? 'yes' : 'no'
		// 	)
		// );

		return $is_ready;
	}
}

if ( ! function_exists( 'df_divi5_site_migrator_maybe_migrate_app_preferences' ) ) {
	function df_divi5_site_migrator_maybe_migrate_app_preferences() {
		$d5_modal_always_collapse_groups = et_get_option( 'et_fb_pref_modal_always_collapse_groups' );

		if ( $d5_modal_always_collapse_groups ) {
			return;
		}

		$d4_builder_display_modal_settings = et_get_option( 'et_fb_pref_builder_display_modal_settings' );

		if ( 'true' === $d4_builder_display_modal_settings ) {
			et_update_option( 'et_fb_pref_modal_always_collapse_groups', 'false' );
		}
	}
}

if ( ! function_exists( 'df_divi5_site_migrator_run' ) ) {
	function df_divi5_site_migrator_run() {
		if ( ! df_divi5_site_migrator_should_run() ) {
			// error_log( '[DIFL D5 MIGRATION] skipped: Divi 5 auto conversion is unavailable.' );
			return false;
		}

		if (
			! class_exists( '\Divi\D5_Readiness\Server\PostTypes' )
			|| ! class_exists( '\ET\Builder\Packages\Conversion\Conversion' )
			|| ! class_exists( '\ET\Builder\Packages\GlobalData\GlobalPreset' )
			|| ! function_exists( '\Divi\D5_Readiness\Helpers\clear_modules_conversation_cache' )
		) {
			// error_log( '[DIFL D5 MIGRATION] skipped: required Divi migration stack is missing.' );
			return false;
		}

		// error_log( '[DIFL D5 MIGRATION] started full-site migration scan.' );
		$post_ids = [];

		foreach ( \Divi\D5_Readiness\Server\PostTypes::get_post_type_slugs() as $post_type ) {
			$pending_ids_with_meta    = \Divi\D5_Readiness\Server\Conversion::get_posts_pending_conversion( $post_type, true );
			$pending_ids_without_meta = \Divi\D5_Readiness\Server\Conversion::get_posts_pending_conversion( $post_type, false );
			$pending_ids             = array_values(
				array_unique(
					array_map(
						'intval',
						array_merge(
							is_array( $pending_ids_with_meta ) ? $pending_ids_with_meta : [],
							is_array( $pending_ids_without_meta ) ? $pending_ids_without_meta : []
						)
					)
				)
			);

			// error_log(
			// 	sprintf(
			// 		'[DIFL D5 MIGRATION] post_type=%s pending_count_meta=%d pending_count_nometa=%d merged_pending_count=%d ids=%s',
			// 		$post_type,
			// 		is_array( $pending_ids_with_meta ) ? count( $pending_ids_with_meta ) : 0,
			// 		is_array( $pending_ids_without_meta ) ? count( $pending_ids_without_meta ) : 0,
			// 		count( $pending_ids ),
			// 		empty( $pending_ids ) ? 'none' : implode( ',', $pending_ids )
			// 	)
			// );

			if ( empty( $pending_ids ) ) {
				continue;
			}

			$post_ids = array_merge( $post_ids, $pending_ids );
		}

		$post_ids = array_values( array_unique( array_map( 'intval', $post_ids ) ) );

		// error_log(
		// 	sprintf(
		// 		'[DIFL D5 MIGRATION] total_pending_posts=%d ids=%s',
		// 		count( $post_ids ),
		// 		empty( $post_ids ) ? 'none' : implode( ',', $post_ids )
		// 	)
		// );

		if ( empty( $post_ids ) ) {
			// error_log( '[DIFL D5 MIGRATION] nothing to migrate.' );
			return true;
		}

		\ET\Builder\Packages\Conversion\Conversion::initialize_shortcode_framework();
		\ET\Builder\Packages\GlobalData\GlobalPreset::maybe_convert_legacy_presets();

		// error_log( '[DIFL D5 MIGRATION] initialized shortcode framework and converted global presets.' );

		foreach ( $post_ids as $post_id ) {
			// error_log( sprintf( '[DIFL D5 MIGRATION] converting_post_id=%d', $post_id ) );
			\Divi\D5_Readiness\Server\Conversion::convert_single_post( $post_id );
		}

		et_update_option( 'et_d5_readiness_conversion_finished', true );
		df_divi5_site_migrator_maybe_migrate_app_preferences();
		\Divi\D5_Readiness\Helpers\clear_modules_conversation_cache();

		if ( class_exists( '\ET_Core_PageResource' ) ) {
			\ET_Core_PageResource::remove_static_resources( 'all', 'all', true );
		}

		// error_log( '[DIFL D5 MIGRATION] full-site migration completed successfully.' );

		return true;
	}
}

if ( ! function_exists( 'df_divi5_site_migrator_ajax_action' ) ) {
	function df_divi5_site_migrator_ajax_action() {
		check_ajax_referer( 'difl_run_divi5_site_migration', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error(
				[
					'message' => __( 'Permission denied.', 'divi_flash' ),
				],
				403
			);
		}

		$source = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '';

		@set_time_limit( 0 );
		wp_raise_memory_limit();
		add_filter( 'et_builder_should_load_framework', '__return_true' );

		// error_log( sprintf( '[DIFL D5 MIGRATION] dedicated migration request started source=%s', $source ?: 'unspecified' ) );

		if ( ! df_divi5_site_migrator_bootstrap() ) {
			// error_log( '[DIFL D5 MIGRATION] dedicated migration request bootstrap failed.' );
			wp_send_json_error(
				[
					'message' => __( 'Divi 5 migration bootstrap failed.', 'divi_flash' ),
				],
				500
			);
		}

		$result = df_divi5_site_migrator_run();

		wp_send_json_success(
			[
				'success' => (bool) $result,
				'source'  => $source,
			]
		);
	}
}

add_action( 'wp_ajax_difl_run_divi5_site_migration', 'df_divi5_site_migrator_ajax_action' );
