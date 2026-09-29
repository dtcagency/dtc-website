<?php
    /*
    Plugin Name: DiviFlash
    Plugin URI:  http://www.diviflash.com
    Description: Most advanced Divi plugin with powerful Divi modules, extensions, and premade layouts.
    Version:     5.2.0
    Author:      DiviFlash
    Author URI:  http://www.diviflash.com
    License:     GPL2
    License URI: https://www.gnu.org/licenses/gpl-2.0.html
    Text Domain: divi_flash
    Domain Path: /languages
    Tested up to: 6.9
    Requires at least: 5.6
    Requires PHP: 7.4
    Update URI: https://www.diviflash.com
    */

	if(!defined('DIFL_MAIN_DIR')) {
        define('DIFL_MAIN_DIR', __DIR__);
    }
    if(!defined('DIFL_ADMIN_DIR')) {
        define('DIFL_ADMIN_DIR', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'admin/');
    }
	if ( ! defined( 'DIFL_MAIN_PATH' ) ) {
		define( 'DIFL_MAIN_PATH', plugin_dir_path( __FILE__ ) );
	}
    if(!defined('DIFL_ADMIN_DIR_PATH')) {
        define('DIFL_ADMIN_DIR_PATH', plugin_dir_path( __FILE__ ) . 'admin/');
    }
    if(!defined('DIFL_PUBLIC_DIR')) {
        define('DIFL_PUBLIC_DIR', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'public/');
    }
    if(!defined('DIFL_PUBLIC_DIR_PATH')) {
        define('DIFL_PUBLIC_DIR_PATH', plugin_dir_path( __FILE__ ) . 'public/');
    }
    if(!defined('DIFL_MAIN_FILE_PATH')) {
        define('DIFL_MAIN_FILE_PATH', __FILE__);
    }


	if ( ! defined( 'DIFL_MODULES_JSON_PATH' ) ) {
		define( 'DIFL_MODULES_JSON_PATH', DIFL_MAIN_PATH . 'Builder/Server/modules-json/' );
	}

    if(!defined('DIFL_VERSION')) {
        define('DIFL_VERSION','5.2.0');
    }

    if ( ! defined( 'DIFL_BASENAME' ) ) {
        define( 'DIFL_BASENAME', plugin_basename( __FILE__ ) );
    }

    if( !defined('DIFL_PLACEHOLDER_LOGO')) {
        define('DIFL_PLACEHOLDER_LOGO', "https://diviflash.website/media/df-logo.png");
    }
	if ( !defined( 'DIFL5_MODULE_PATH' ) ) {
		define( 'DIFL5_MODULE_PATH', plugin_dir_path( __FILE__ ) );
	}
	if ( !defined( 'DIFL5_MODULE_URL' ) ) {
		define( 'DIFL5_MODULE_URL', plugin_dir_url( __FILE__ ) );
	}
	if ( !defined( 'DIFL5_MODULES_JSON_PATH' ) ) {
		define( 'DIFL5_MODULES_JSON_PATH', DIFL5_MODULE_PATH . 'divi-5/visual-builder/build/modules-json/' );
	}
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-diviflash-init.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/handler/Fa_Icon_Handler.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/handler/Divi_Compat.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/handler/Plugin.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/feature/Admin.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/feature/Contact_Form.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/feature/General.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/feature/Maintenance.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/feature/Custom_Login.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/df-divi5-site-migrator.php';
/**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     */
    function run_diviflash() {

        $plugin = new DiviFlashInit();
        $plugin->run();
	    $module_manager = new Diviflash_Module_Manage();

	    if( get_option( 'difl_inactive_modules' ) ) { // if upgrade from v1.1.2
            update_option( 'df_inactive_modules', get_option( 'difl_inactive_modules' ) ); // clone from another key.
            delete_option( 'difl_inactive_modules' ); //clean up previouse option key
        }

	    if ( get_option( 'df_inactive_modules' ) ) { // upgrade from v1.1.5
		    $get_all_modules                       = $module_manager->get_all_modules();
		    $get_default_inactive_module           = json_decode( $module_manager->get_inactive_modules() );
		    $diff_module                           = array_diff( array_keys( $get_all_modules ), $get_default_inactive_module );
		    $default_inactive_latest_array         = array_filter( $module_manager->get_all_modules(), function ( $var ) {
			    if ( isset( $var['release_version'] ) ) {
				    return $var['release_version'] === DIFL_VERSION && $var['is_default_active'] === false;
			    }
		    } );
		    $default_inactive_latest_array_modules = array_keys( $default_inactive_latest_array );
		    $update_modules                        = array_diff( $diff_module, $default_inactive_latest_array_modules );

		    update_option( 'df_active_modules', wp_json_encode( array_values( $update_modules ) ) );
		    update_option( 'df_inactive_modules_added', DIFL_VERSION );
		    delete_option( 'df_inactive_modules' ); //clean up previouse option key
	    }

        if ( ! get_option('df_active_modules') ){	 // if First time purchase
            update_option( 'df_active_modules', wp_json_encode($module_manager->get_default_active_modules()) );
            update_option( 'df_inactive_modules_added', DIFL_VERSION );
        }
    }

    run_diviflash();

    /**
     * Auto-deactivate DiviFlash Plugin if inactive Divi/Extra child or parent theme or Divi Builder
     *
     */
    function df_deactivate_if_theme_builder_uses() {
        // Don't do anything if the user isn't logged in
        if ( ! is_user_logged_in() ) {
            return;
        }

	    if ( class_exists( 'ET_Core_API_ElegantThemes' ) || df_divi_is_theme() || df_divi_is_plugin()  ) {
		    return;
	    }

        if ( current_user_can( 'activate_plugins' ) ) {
            add_action( 'admin_notices', 'df_auto_deactivate_notice' );
            add_action( 'admin_init', 'df_auto_deactivate' );
        }
    }
    add_action( 'init', 'df_deactivate_if_theme_builder_uses' );

    /**
     * Deactivate this plugin
     *
     */
    function df_auto_deactivate() {
        deactivate_plugins( DIFL_BASENAME );
    }

    /**
     * Print a WP Admin notice when DiviFlash has deactivated
     *
     */
    function df_auto_deactivate_notice() {
        $classes = 'notice notice-warning is-dismissible';
        $message = __( 'First You should activate Divi/Extra theme or Divi Builder to use diviflash.', 'et_builder' );

        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $classes ), esc_html( $message ) );

        if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification
        }
    }

	add_filter( 'et_builder_load_requests', function ( $request ) {
		$request['action'][] = 'difl_layout_import';
		return $request;
	}, PHP_INT_MAX );

	if ( ! ( df_divi_is_theme() || df_divi_is_plugin() ) ) {
		return;
	}

	if ( is_admin() && ( df_divi_is_theme() || df_divi_is_plugin() ) ) {
		if ( ! defined( 'WP_LOAD_IMPORTERS' ) && ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === 'diviflash' ) ) { // phpcs:disable WordPress.Security.NonceVerification.Recommended -- as this is general handling nonce checking can be escaped
			define( 'WP_LOAD_IMPORTERS', true );
		}

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) && wp_doing_ajax() && ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'difl_layout_import' ) ) { // phpcs:disable WordPress.Security.NonceVerification.Recommended -- as this is general handling nonce checking can be escaped
			define( 'WP_LOAD_IMPORTERS', true );
		}

		if ( ! class_exists( 'ET_Core_Portability' ) ) {
			require_once df_load_portability_class();
		}
		require plugin_dir_path( __FILE__ ) . 'admin/RemoteData.php';
		require plugin_dir_path( __FILE__ ) . 'admin/Dashboard.php';
	}


remove_action( 'wp_ajax_et_core_portability_export', 'et_core_portability_ajax_export' );
	if ( ! function_exists( 'et_core_portability_load_extended' ) ) {
		if ( ! class_exists( 'ET_Core_Portability' ) ) {
			require_once df_load_portability_class();
		}
		function et_core_portability_load_extended( $context ) {
			return new ET_Core_Portability_Extended( $context );
		}
	}

	class ET_Core_Portability_Extended extends ET_Core_Portability {
		protected function get_data_images( $data, $force = false ) {
			if ( empty( $data ) ) {
				return [];
			}

			$images     = [];
			$images_src = [];
			$basenames  = [
				'field_image',
				'hotsopt_image',
				'text_img',
				'line_top_image',
				'line_bottom_image',
				'content_image',
				'list_item_icon_lottie_src_upload',
				'list_item_image',
				'divider_image',
				'answer_image',
				'sticky_logo',
				'logo_upload',
				'ap_photo',
				'ap_alternative_photo',
				'marker_img',
				'company_logo',
				'different_lightbox_image',
				'scroll_image',
				'instagram_user_profile_picture',
				'open_question_image',
				'tab_image',
				'before_image',
				'after_image',
				'image_icon',
				'image_as_icon',
				'close_question_image' .
				'src',
				'image_url',
				'background_image',
				'image',
				'url',
				'bg_img_?\d?',
			];
			$suffixes   = [
				'__hover',
				'_tablet',
				'_phone',
			];

			foreach ( $basenames as $basename ) {
				$images_src[] = $basename;
				foreach ( $suffixes as $suffix ) {
					$images_src[] = $basename . $suffix;
				}
			}

			foreach ( $data as $value ) {
				// If the $value is an object and there is no post_content property,
				// it's unlikely to contain any image data so we can continue with the next iteration.
				if ( is_object( $value ) && ! property_exists( $value, 'post_content' ) ) {
					continue;
				}

				if ( is_array( $value ) || is_object( $value ) ) {
					// If the $value contains the post_content property, set $value to use
					// this object's property value instead of the entire object.
					if ( is_object( $value ) && property_exists( $value, 'post_content' ) ) {
						$value = $value->post_content;
					}

					$images = array_merge( $images, $this->get_data_images( (array) $value ) );
					continue;
				}

				// Extract images from HTML or shortcodes.
				if ( preg_match_all( '/(' . implode( '|', $images_src ) . ')="(?P<src>\w+[^"]*)"/i', $value, $matches ) ) {
					foreach ( array_unique( $matches['src'] ) as $key => $src ) {
						$images = array_merge( $images, $this->get_data_images( [ $key => $src ] ) );
					}
				}

				// Extract images from shortcodes gallery.
				if ( preg_match_all( '/gallery_ids="(?P<ids>\w+[^"]*)"/i', $value, $matches ) ) {
					foreach ( array_unique( $matches['ids'] ) as $galleries ) {
						$explode = explode( ',', str_replace( ' ', '', $galleries ) );

						foreach ( $explode as $image_id ) {
							$images = array_merge( $images, $this->get_data_images( [ (int) $image_id ], true ) );
						}
					}
				}

				if ( preg_match( '/^.+?\.(jpg|jpeg|jpe|png|gif|svg|webp)/', $value, $match ) || $force ) {
					$basename = basename( $value );

					// Skip if the value is not a valid URL or an image ID (integer).
					if ( ! ( wp_http_validate_url( $value ) || is_int( $value ) ) ) {
						continue;
					}

					// Skip if the images array already contains the value to avoid duplicates.
					if ( isset( $images[ $value ] ) ) {
						continue;
					}

					$images[ $value ] = $value;
				}
			}

			return $images;
		}
	}

	function et_core_portability_ajax_export_extended() {
		if ( ! isset( $_POST['context'] ) ) {
			wp_send_json_error();

			return;
		}

		$context = sanitize_text_field( $_POST['context'] );

		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, WordPress.CodeAnalysis.AssignmentInCondition.Found -- Existing codebase.
		if ( ! $capability = et_core_portability_cap( $context ) ) {
			wp_send_json_error();

			return;
		}

		if ( ! et_core_security_check_passed( $capability, 'et_core_portability_export', 'nonce' ) ) {
			wp_send_json_error();

			return;
		}

		$return = isset( $_POST['return'] ) && sanitize_text_field( $_POST['return'] );

		if ( $return ) {
			$data = et_core_portability_load_extended( $context )->export( $return );

			wp_send_json_success( $data );
		} else {
			et_core_portability_load_extended( $context )->export();
		}

		wp_send_json_error();
	}
	add_action( 'wp_ajax_et_core_portability_export', 'et_core_portability_ajax_export_extended' );


	add_action('init', function () {
		require plugin_dir_path( __FILE__ ) . 'customizer/Base_Customizer.php';
		require plugin_dir_path( __FILE__ ) . 'customizer/Main.php';
	});


// Load Frontend after theme setup otherwise necessary function could miss

	add_action( 'init', function () {
		require plugin_dir_path( __FILE__ ) . 'customizer/frontend/Frontend.php';
	}, PHP_INT_MAX );
	add_filter('et_builder_should_load_all_module_data', '__return_true');
	add_filter( 'the_content', function ( $content ) {
		$post_data = $_REQUEST;
		$keys      = array_keys( $post_data );
		if ( ! empty( $keys ) ) {
			foreach ( $keys as $key ) {
				if ( str_starts_with( $key, 'et_pb_contact_email_fields' ) ) {
					$pattern = '/\[et_pb_contact_field[^[]*field_type="file_upload"[^[]*\[\/et_pb_contact_field\]/';
					$content = preg_replace( $pattern, '', $content );
					break;
				}
			}
		}

		return $content;
	}, -PHP_INT_MAX );

	add_action( 'et_builder_modules_loaded', function () {
		if ( ! class_exists( '\ET_Builder_Module_Contact_Form_Item' ) ) {
			return;
		}
		if ( et_builder_should_load_all_module_data() ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/feature/CF_Attachment.php';
		}
	} );


//****************************************//
//**************   DIVI 5   **************//
//****************************************//
require plugin_dir_path( __FILE__ ) . 'Builder/Main.php';
