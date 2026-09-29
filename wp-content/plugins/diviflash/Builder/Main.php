<?php

namespace DIFL\Builder;

class Main {
	/**
	 * Static property to track unmatched attributes for CPT filter module.
	 * This is used to dynamically add them to deprecatedMap.
	 *
	 * @var array<string>
	 */
	private static $unmatched_attributes = [];

	/**
	 * Static flag to prevent recursion in dynamic_deprecated_map.
	 *
	 * @var bool
	 */
	private static $processing_deprecated_map = false;

	/**
	 * Cached unmatched attributes from transient to avoid repeated lookups.
	 * Keyed by module name (e.g. 'difl/cptfilter', 'difl/cptitem').
	 *
	 * @var array<string, array>|null
	 */
	private static $cached_unmatched_attrs = null;

	/**
	 * Module names that use dynamic deprecation of unmatched attributes.
	 *
	 * @var array<string>
	 */
	private static $modules_with_dynamic_deprecation = [ 'difl/cptfilter', 'difl/cptitem', 'difl/cptgrid', 'difl/cptcarousel' ];

	/**
	 * Manual list of attributes that should always be in deprecatedMap,
	 * even if they're not encountered during conversion.
	 * These are attributes that were previously deprecated but removed from the conversion outline.
	 *
	 * @var array<string>
	 */
	private static $manual_deprecated_attrs = [];

	/**
	 * Transient key for storing unmatched attributes per module.
	 *
	 * @param string $module_name e.g. 'difl/cptfilter', 'difl/cptitem'.
	 * @return string
	 */
	private static function transient_key_for_module( $module_name ) {
		return str_replace( '/', '_', $module_name ) . '_unmatched_attrs';
	}

	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'normalize_builder_global_presets' ], 20 );
		$this->load_server();
		add_action( 'divi_visual_builder_assets_before_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
		require_once __DIR__ . '/Server/Localizer.php';

        add_filter( 'divi.conversion.legacyAttributeNames', function( $attributes ) {
            $plugin_attributes = [
                'default_text_shadow_style',
                'default_text_shadow_horizontal_length',
                'default_text_shadow_vertical_length',
                'default_text_shadow_blur_strength',
                'default_text_shadow_color',
            ];
            return array_merge( $attributes, $plugin_attributes );
        } );

		add_filter('divi.conversion.moduleLibrary.conversionMap',[ $this, 'dynamic_conversion_map' ],999);
		add_filter('divi.moduleLibrary.conversion.moduleConversionOutline',[ $this, 'dynamic_conversion_value_expression_map' ],10, 2);
		// Add filter to dynamically populate deprecatedMap after dynamic_conversion_map completes
		add_filter('divi.moduleLibrary.conversion.moduleConversionOutline',[ $this, 'dynamic_deprecated_map' ],20, 2);
		// Add filter to modify conversion map's deprecatedMap directly (runs after dynamic_conversion_map)
		add_filter('divi.conversion.moduleLibrary.conversionMap',[ $this, 'add_unmatched_to_conversion_map_deprecated' ],1000, 1);
		// Add filter to dynamically deprecate unmatched attributes during conversion
		add_filter('divi_conversion_deprecated_attribute',[ $this, 'dynamic_deprecate_unmatched_attribute' ],10, 4);

		// Global preset attrs map for all difl modules: include render:false attributes when creating/saving presets.
		require_once __DIR__ . '/Server/PresetAttrsMapGlobal.php';
		add_filter( 'divi_conversion_presets_attrs_map', [ \DIFL\Server\PresetAttrsMapGlobal::class, 'get_map' ], 10, 2 );
		add_filter( 'divi.conversion.postConvertAttrs', [ $this, 'ensure_gradient_stops_positions' ], 10, 2 );
		add_filter( 'divi.conversion.postConvertAttrs', [ $this, 'fix_button_link_phone_conversion' ], 10, 3 );

		// Resolve global colors in module attrs so divi/color-picker values are concrete before $props['attrValue'].
		add_filter( 'divi_module_library_register_module_attrs', 'difl_resolve_global_color_variable_values', 10, 2 );

		// WP 6.9.1 added a strict-mode _doing_it_wrong() notice when wp_enqueue_script
		// receives dep handles that aren't registered yet. Our VB bundle is enqueued
		// from `divi_visual_builder_assets_before_enqueue_scripts`, which fires before
		// Divi registers `divi-module-library` / `divi-app-ui` / `divi-vendor-wp-hooks`.
		// The dep graph still resolves correctly at print time (Divi registers those
		// shortly after, and WP looks them up at print, not enqueue) — so the warning
		// is purely cosmetic for our handle. Suppress only this specific message.
		add_filter( 'doing_it_wrong_trigger_error', function ( $trigger, $function_name = '', $message = '' ) {
			if ( 'WP_Scripts::add' === $function_name
				&& is_string( $message )
				&& false !== strpos( $message, '"difl-divi5-new"' ) ) {
				return false;
			}
			return $trigger;
		}, 10, 3 );
	}

	public function normalize_builder_global_presets() {
		if ( ! function_exists( 'et_get_option' ) || ! function_exists( 'et_update_option' ) ) {
			return;
		}

		$all_builder_presets = et_get_option( 'builder_global_presets_ng', (object) [], '', true, false, '', '', true );

		if ( empty( $all_builder_presets ) || ! is_array( $all_builder_presets ) && ! is_object( $all_builder_presets ) ) {
			return;
		}

		$normalized = is_object( $all_builder_presets ) ? get_object_vars( $all_builder_presets ) : $all_builder_presets;
		$changed    = false;

		foreach ( $normalized as $module => $module_presets ) {
			if ( is_array( $module_presets ) ) {
				$module_presets      = (object) $module_presets;
				$normalized[ $module ] = $module_presets;
				$changed             = true;
			}

			if ( ! is_object( $module_presets ) || empty( $module_presets->presets ) ) {
				continue;
			}

			$preset_items = is_object( $module_presets->presets ) ? get_object_vars( $module_presets->presets ) : $module_presets->presets;

			if ( ! is_array( $preset_items ) ) {
				continue;
			}

			foreach ( $preset_items as $preset_id => $preset_data ) {
				if ( is_array( $preset_data ) ) {
					$preset_items[ $preset_id ] = (object) $preset_data;
					$changed                    = true;
				}
			}

			$module_presets->presets = is_object( $module_presets->presets ) ? (object) $preset_items : $preset_items;
			$normalized[ $module ]   = $module_presets;
		}

		if ( $changed ) {
			et_update_option(
				'builder_global_presets_ng',
				is_object( $all_builder_presets ) ? (object) $normalized : $normalized,
				false,
				'',
				'',
				true
			);
		}
	}

	/**
	 * Ensures gradient stops have default positions (0, 100) when missing after D4→D5 conversion.
	 *
	 * @param array  $converted_attrs Converted attributes.
	 * @param string $module_name.
	 * @return array
	 */
	public function ensure_gradient_stops_positions( $converted_attrs, $module_name ) {
		if ( ! is_array( $converted_attrs ) ) {
			return $converted_attrs;
		}
		difl_ensure_gradient_stops_positions( $converted_attrs );
		return $converted_attrs;
	}

	/**
	 * Fixes button_link_phone conversion issue - manually adds it if missing.
	 *
	 * @param array  $converted_attrs The converted attributes array.
	 * @param string $module_name     The module name (e.g., 'difl/advancedbutton').
	 * @param array  $original_attrs  The original D4 attributes.
	 * @return array
	 */
	public function fix_button_link_phone_conversion( $converted_attrs, $module_name, $original_attrs = null ) {
		if ( 'difl/advancedbutton' !== $module_name || ! is_array( $original_attrs ) || ! is_array( $converted_attrs ) ) {
			return $converted_attrs;
		}

		// Check if button_link_phone exists in original but not in converted (desktop only)
		if ( ! isset( $original_attrs['button_link_phone'] ) || empty( $original_attrs['button_link_phone'] ) ) {
			return $converted_attrs;
		}

		// Check if value already exists in converted at desktop.value (core conversion might have worked)
		if ( isset( $converted_attrs['contentLink']['innerContent']['desktop']['value']['button_link_phone'] ) ) {
			return $converted_attrs;
		}

		// Ensure contentLink structure exists
		if ( ! isset( $converted_attrs['contentLink'] ) ) {
			$converted_attrs['contentLink'] = [];
		}
		if ( ! isset( $converted_attrs['contentLink']['innerContent'] ) ) {
			$converted_attrs['contentLink']['innerContent'] = [];
		}

		// Get the phone value directly (no processing needed, it's just text)
		$phone_value = $original_attrs['button_link_phone'];

		// Set it at desktop.value level (not default, and only desktop, not tablet/phone)
		if ( ! isset( $converted_attrs['contentLink']['innerContent']['desktop'] ) ) {
			$converted_attrs['contentLink']['innerContent']['desktop'] = [];
		}
		if ( ! isset( $converted_attrs['contentLink']['innerContent']['desktop']['value'] ) ) {
			$converted_attrs['contentLink']['innerContent']['desktop']['value'] = [];
		}
		$converted_attrs['contentLink']['innerContent']['desktop']['value']['button_link_phone'] = $phone_value;

		return $converted_attrs;
	}

	/**
	 * Adds dynamic conversion map keys.
	 *
	 * @param array $conversion_map
	 * @return array
	 */
	public function dynamic_conversion_map( $conversion_map ) {
		$attribute_map = [];
		$filtarablecpt = [];
		$cpttexonomies = [];
		/**
		 * Get post types, taxonomies, and ACF fields
		 */
		$post_types = get_post_types(['public' => true], 'names');
		$tax_list = get_taxonomies([], 'names');

		$acf_lists = [];
		if ( function_exists('acf_get_field_groups') ) {
			$field_groups = acf_get_field_groups();
			if ( ! empty( $field_groups ) ) {
				foreach ( $field_groups as $field_group ) {
					$fields = acf_get_fields( $field_group['key'] );
					if ( ! empty( $fields ) ) {
						foreach ( $fields as $field ) {
							if ( isset( $field['name'] ) ) {
								$acf_lists[] = $field['name'];
							}
						}
					}
				}
			}
		}
		$pod_lists = [];
		if ( class_exists( 'Pods' ) ) {
			$all_pods = pods_api()->load_pods();
			if ( ! empty( $all_pods ) && is_array( $all_pods ) ) {
				foreach ( $all_pods as $pod_name => $pod_data ) {
					// $pod_data has 'fields' key which is the pod fields
					if ( isset( $pod_data['fields'] ) && is_array( $pod_data['fields'] ) ) {
						foreach ( $pod_data['fields'] as $field_name => $field_info ) {
							$pod_lists[] = $field_name;
						}
					}
				}
			}
		}
		/**
		 * Create attribute map for post types, taxonomies, and ACF fields
		 */
		foreach ($post_types as $post_type) {
			// metabox-$post_type
			$df4key1 = 'metabox-' . $post_type;
			$df5key1 = 'metabox_fields_for_' . $post_type;
			$value1 = $df5key1 . '.innerContent.*';
			$attribute_map[$df4key1] = $value1;

			// pod-$post_type
			$df4key2 = 'pod-' . $post_type;
			$df5key2 = 'pod_fields_for_' . $post_type;
			$value2 = $df5key2 . '.innerContent.*';
			$attribute_map[$df4key2] = $value2;

			// acf-$post_type
			$df4key3 = $post_type;
			$df5key3 = 'acf_' . $post_type;
			$value3 = $df5key3 . '.innerContent.*';
			$attribute_map[$df4key3] = $value3;

            // $post_type
            // $key3 = $post_type;
            // $value3 = $key3 . '.innerContent.*';
            // $attribute_map[$key3] = $value3;

			// tax_for_
            $key7 = 'tax_for_'.$post_type;
            $value7 = $key7 . '.innerContent.*';
			$attribute_map[$key7] = $value7;

            // pod_filter_
            $key4 = 'pod_filter_'.$post_type;
            $value4 = $key4 . '.innerContent.*';
            $filtarablecpt[$key4] = $value4;

            // pod_filter_option_
            $key5 = 'pod_filter_option_'.$post_type;
            $value5 = $key5 . '.innerContent.*';
            $filtarablecpt[$key5] = $value5;

            // acf_filter_
            $key5 = 'acf_filter_'.$post_type;
            $value5 = $key5 . '.innerContent.*';
            $filtarablecpt[$key5] = $value5;

            // acf_filter_option_
            $key6 = 'acf_filter_option_'.$post_type;
            $value6 = $key6 . '.innerContent.*';
            $filtarablecpt[$key6] = $value6;

            // tax_list_for_
            $key7 = 'tax_list_for_'.$post_type;
            $value7 = $key7 . '.innerContent.*';
            $filtarablecpt[$key7] = $value7;

			$taxonomies = get_object_taxonomies( $post_type, 'names' );

			// if ( ! empty( $taxonomies ) ) {
				foreach ( $taxonomies as $tax ) {
					// _terms_
					$key12 = $post_type.'_terms_'.$tax;
					$value12 = $key12 . '.innerContent.*';
					$filtarablecpt[$key12] = $value12;
					$cpttexonomies[$key12] = $value12;
				}
			// }

			foreach ($tax_list as $tax_name) {
				$key9 = 'tax_filter_field_type_'.$post_type.'_'.$tax_name;
				$value9 = $key9 . '.innerContent.*';
				$filtarablecpt[$key9] = $value9;
			}

            foreach($acf_lists as $acf_list) {
                $key10 = 'acf_filter_field_type_'.$post_type.'_'.$acf_list;
                $value10 = $key10 . '.innerContent.*';
                $filtarablecpt[$key10] = $value10;
            }
			foreach($pod_lists as $pod_list) {
				$key11 = 'pod_filter_field_type_'.$post_type.'_'.$pod_list;
				$value11 = $key11 . '.innerContent.*';
				$filtarablecpt[$key11] = $value11;
			}
        }
        $custom = [
            'difl/cptitem' => [
                'attributeMap' => $attribute_map,
            ],
        ];
        $filterableCpt = [
            'difl/cptfilter' => [
                'attributeMap' => $filtarablecpt,
            ],
        ];
		$cptgrid = [
            'difl/cptgrid' => [
                'attributeMap' => $cpttexonomies,
            ],
        ];
		$cptcarousel = [
            'difl/cptcarousel' => [
                'attributeMap' => $cpttexonomies,
            ],
        ];
        return array_replace_recursive( $conversion_map, $custom,$filterableCpt, $cptgrid, $cptcarousel );
    }

	public function dynamic_conversion_value_expression_map( $conversion_outline, $module_name ) {
		// Only for our module
        if (
			('difl/cptfilter' !== $module_name ) &&
			('difl/cptgrid' !== $module_name ) &&
			('difl/cptcarousel' !== $module_name ) &&
			( 'difl/advancedbutton' !== $module_name )
		) {
            return $conversion_outline;
        }

        // Get existing mappings (handle both arrays and immutable structures)
        $existing_map = isset( $conversion_outline['valueExpansionFunctionMap'] ) ? (array) $conversion_outline['valueExpansionFunctionMap'] : [];
        $new_map = [];

		if ( 'difl/advancedbutton' === $module_name ) {
			// No value expansion function needed for button_link_phone - it's just text
		} else {
			// Get all public post types
			$post_types = get_post_types( [ 'public' => true ], 'names' );
			if ( ! empty( $post_types ) ) {
				foreach ( $post_types as $pt ) {
					// Map: <postType>_terms_<taxonomy> - comma-separated term IDs
					$taxonomies = get_object_taxonomies( $pt, 'names' );
					if ( ! empty( $taxonomies ) ) {
						foreach ( $taxonomies as $tax ) {
							$key = sprintf( '%1$s_terms_%2$s', $pt, $tax );
							// Use the global conversion function defined in Server/Utils/functions.php
							$new_map[ $key ] = 'D4ToD5ConvertTaxonomyValue';
						}
					}
				}
			}
		}

        // Merge existing and new mappings without modifying immutable structures
        if ( ! empty( $new_map ) ) {
            $conversion_outline['valueExpansionFunctionMap'] = array_merge( $existing_map, $new_map );
        }

        return $conversion_outline;
	}

	/**
	 * Dynamically populates deprecatedMap for modules using dynamic deprecation.
	 * Intentionally no-op here: outline is requested during module registration (every load).
	 * Deprecated list is merged only during conversion via add_unmatched_to_conversion_map_deprecated.
	 *
	 * @param array  $conversion_outline The conversion outline.
	 * @param string $module_name        The module name.
	 * @return array Unchanged outline (work is done only when conversion map is requested during migration).
	 */
	public function dynamic_deprecated_map( $conversion_outline, $module_name ) {
		return $conversion_outline;
	}

	/**
	 * Whether the conversion map filter is being called from Divi's Conversion class (actual migration).
	 *
	 * @return bool
	 */
	private static function is_conversion_running() {
		$stack = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 25 );
		foreach ( $stack as $frame ) {
			if ( isset( $frame['class'] ) && $frame['class'] === 'ET\Builder\Packages\Conversion\Conversion' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Adds unmatched attributes to the conversion map's deprecatedMap.
	 * Runs only when the map is requested during actual D4→D5 conversion (not at registration or d5-readiness).
	 *
	 * @param array $conversion_map The conversion map.
	 * @return array Modified conversion map.
	 */
	public function add_unmatched_to_conversion_map_deprecated( $conversion_map ) {
		if ( ! self::is_conversion_running() ) {
			return $conversion_map;
		}

		foreach ( self::$modules_with_dynamic_deprecation as $module_name ) {
			if ( ! isset( $conversion_map[ $module_name ] ) ) {
				continue;
			}

			// Initialize deprecatedMap if not exists
			if ( ! isset( $conversion_map[ $module_name ]['deprecatedMap'] ) ) {
				$conversion_map[ $module_name ]['deprecatedMap'] = [];
			}

			// Ensure deprecatedMap is an array
			if ( ! is_array( $conversion_map[ $module_name ]['deprecatedMap'] ) ) {
				$conversion_map[ $module_name ]['deprecatedMap'] = (array) $conversion_map[ $module_name ]['deprecatedMap'];
			}

			// Cache transient lookup per module
			if ( ! is_array( self::$cached_unmatched_attrs ) ) {
				self::$cached_unmatched_attrs = [];
			}
			if ( ! isset( self::$cached_unmatched_attrs[ $module_name ] ) ) {
				$transient_key = self::transient_key_for_module( $module_name );
				$unmatched_attrs = get_transient( $transient_key );
				self::$cached_unmatched_attrs[ $module_name ] = ( false === $unmatched_attrs ) ? [] : $unmatched_attrs;
			}
			$unmatched_attrs = self::$cached_unmatched_attrs[ $module_name ];

			// Add unmatched attributes to deprecatedMap (only if not already present)
			if ( ! empty( $unmatched_attrs ) && is_array( $unmatched_attrs ) ) {
				foreach ( $unmatched_attrs as $attr_name ) {
					if ( ! in_array( $attr_name, $conversion_map[ $module_name ]['deprecatedMap'], true ) ) {
						$conversion_map[ $module_name ]['deprecatedMap'][] = $attr_name;
					}
				}
			}

			// Also check static property for current request
			if ( ! empty( self::$unmatched_attributes[ $module_name ] ) ) {
				$unmatched = self::$unmatched_attributes[ $module_name ];
				foreach ( $unmatched as $attr_name ) {
					if ( ! in_array( $attr_name, $conversion_map[ $module_name ]['deprecatedMap'], true ) ) {
						$conversion_map[ $module_name ]['deprecatedMap'][] = $attr_name;
					}
				}
			}

			// Add manual deprecated attributes (attributes that should always be deprecated)
			foreach ( self::$manual_deprecated_attrs as $attr_name ) {
				if ( ! in_array( $attr_name, $conversion_map[ $module_name ]['deprecatedMap'], true ) ) {
					$conversion_map[ $module_name ]['deprecatedMap'][] = $attr_name;
				}
			}
		}

		return $conversion_map;
	}

	/**
	 * Dynamically deprecates attributes that don't have a match in the conversion map.
	 * This is called during actual conversion when we have access to original attributes.
	 *
	 * @param bool   $should_deprecate Whether the attribute should be deprecated. Default false.
	 * @param string $desktop_name     The desktop name of the attribute (without responsive/hover/sticky suffixes).
	 * @param string $module_name      The module name (e.g., 'difl/cptfilter').
	 * @param array  $attrs            The full attributes array for the module.
	 * @return bool True if the attribute should be deprecated, false otherwise.
	 */
	public function dynamic_deprecate_unmatched_attribute( $should_deprecate, $desktop_name, $module_name, $attrs ) {
		// Only for modules that use dynamic deprecation (cptfilter, cptitem)
		if ( ! in_array( $module_name, self::$modules_with_dynamic_deprecation, true ) ) {
			return $should_deprecate;
		}

		// If already deprecated, return early
		if ( $should_deprecate ) {
			return $should_deprecate;
		}

		// Get the conversion map
        // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores,WordPress.NamingConventions.ValidHookName.NotLowercase -- This is a valid hook name.
		$module_library_conversion_map = apply_filters( 'divi.conversion.moduleLibrary.conversionMap', [] );
		$module_conversion_map = $module_library_conversion_map[ $module_name ] ?? [];
		$attribute_map = $module_conversion_map['attributeMap'] ?? [];

		// Get deprecatedMap from conversion map (this is what's actually checked during conversion)
		$deprecated_map = $module_conversion_map['deprecatedMap'] ?? [];

		// Check if attribute is already in deprecatedMap
		if ( in_array( $desktop_name, $deprecated_map, true ) ) {
			return true;
		}

		// List of known internal attributes that should not be converted (from Divi core)
		$known_internal_attrs = [
			'fb_built',
			'sticky_enabled',
			'hover_enabled',
			'_dynamic_attributes',
			'_address',
			'_i',
		];

		// Skip known internal attributes
		if ( in_array( $desktop_name, $known_internal_attrs, true ) ) {
			return false;
		}

		// Check if attribute has a match in attributeMap
		$has_match = isset( $attribute_map[ $desktop_name ] ) && ! empty( $attribute_map[ $desktop_name ] );

		// If not, it should be deprecated to avoid legacy errors
		if ( ! $has_match ) {
			// Check if it's a known attribute that should be handled differently
			$known_attrs = [
				'admin_label',
				'theme_builder_area',
				'global_colors_info',
				'on',
				'locked',
				'open',
				'global_module',
				'global_parent',
				'nonconvertible',
				'shortcodeName',
				'_builder_version',
				'_module_preset',
			];

			// Don't deprecate known attributes that are handled by core conversion
			if ( ! in_array( $desktop_name, $known_attrs, true ) ) {
				// Track this unmatched attribute in static property (current request)
				if ( ! isset( self::$unmatched_attributes[ $module_name ] ) ) {
					self::$unmatched_attributes[ $module_name ] = [];
				}
				if ( ! in_array( $desktop_name, self::$unmatched_attributes[ $module_name ], true ) ) {
					self::$unmatched_attributes[ $module_name ][] = $desktop_name;
				}

				// Also store in transient for persistence across requests
				$transient_key = self::transient_key_for_module( $module_name );
				$unmatched_attrs = get_transient( $transient_key );
				if ( false === $unmatched_attrs ) {
					$unmatched_attrs = [];
				}
				if ( ! in_array( $desktop_name, $unmatched_attrs, true ) ) {
					$unmatched_attrs[] = $desktop_name;
					set_transient( $transient_key, $unmatched_attrs, 30 * DAY_IN_SECONDS );
					if ( ! is_array( self::$cached_unmatched_attrs ) ) {
						self::$cached_unmatched_attrs = [];
					}
					self::$cached_unmatched_attrs[ $module_name ] = $unmatched_attrs;
				}

				// This attribute doesn't have a match, deprecate it
				return true;
			}
		}

		return $should_deprecate;
	}

	protected function load_server() {
		require __DIR__ . '/vendor/autoload.php';
		require_once __DIR__ . '/Server/API.php';
		require_once __DIR__ . '/Server/ChartData.php';
		require_once __DIR__ . '/Server/Modules/Main.php';
		// Ensure utility functions (conversion helpers) are available
		require_once __DIR__ . '/Server/Utils/functions.php';
		require_once(DIFL_MAIN_DIR . '/includes/classes/df-vertical-menu-walker.php');
		require_once(DIFL_MAIN_DIR . '/includes/classes/df-menu-walker.php');

	}

	public function enqueue_scripts() {

		if ( ! et_builder_d5_enabled() && ! et_core_is_fb_enabled() ) {
			return;
		}

		$plugin_path  = plugin_dir_path( __DIR__ );
		$plugin_url   = plugin_dir_url( __DIR__ );
		$path         = 'Builder/Visual-Builder/build/';
		$assets_path  = 'Builder/Assets/';
		$handle       = 'difl-divi5-new';
		$dependencies = [];

		if ( file_exists( $plugin_path . $path . 'bundle.asset.php' ) ) {
			$dependencies = include_once $plugin_path . $path . 'bundle.asset.php';
		}

		wp_enqueue_style(
			$handle . '-lib',
			$plugin_url . $assets_path . 'styles/df_lib_styles.css',
			[ 'wp-components' ],
			DIFL_VERSION );

		wp_enqueue_style(
			$handle,
			$plugin_url . $path . 'styles/bundle.css',
			[ 'wp-components' ],
			DIFL_VERSION );

		// Only pass dependencies that are already registered to avoid WP_Scripts::add notice.
		// $requested_deps = [
		// 	'divi-module-library',
		// 	'divi-app-ui',
		// 	'divi-vendor-wp-hooks',
		// 	'data-table-script',
		// ];
		// $wp_scripts    = wp_scripts();
		// $deps          = array_filter( $requested_deps, function ( $dep ) use ( $wp_scripts ) {
		// 	return $wp_scripts->query( $dep, 'registered' );
		// } );

		wp_enqueue_script(
			$handle,
			$plugin_url . $path . 'bundle.js',
			[
				'divi-module-library',
				'divi-app-ui',
				'divi-vendor-wp-hooks',
				'data-table-script',
			],
			1,
			true
		);

		// Pass server-built preset attrs map for all difl modules (single source of truth; no duplicate JS logic).
		$preset_attrs_maps = $this->get_difl_preset_attrs_maps_for_script();
		wp_localize_script( $handle, 'diflPresetAttrsMaps', $preset_attrs_maps );

		// Expose the WP site timezone to the VB so date-aware modules
		// (CountdownTimer's "WP Default Timezone" option) can compute the
		// same wall-clock-to-UTC conversion as the server. Without this,
		// the VB falls back to the browser's local TZ for `target_tz: wp`
		// and a viewer in a different timezone sees a countdown that's
		// hours (or even a full day, near the dateline) off from the FE.
		wp_localize_script( $handle, 'diflWpTimezone', [
			'string' => function_exists( 'wp_timezone_string' ) ? wp_timezone_string() : 'UTC',
			'offset' => floatval( get_option( 'gmt_offset', 0 ) ),
		] );
	}

	/**
	 * Get preset attrs map for every registered difl module (for VB "New Preset From Current Styles").
	 * Uses Divi's Conversion::get_preset_attrs_mapping() so PHP is the single source of truth.
	 *
	 * @return array<string, array> Map of module name => preset attrs map.
	 */
	protected function get_difl_preset_attrs_maps_for_script() {
		$out = [];
		if ( ! class_exists( '\ET\Builder\Packages\Conversion\Conversion' ) ) {
			return $out;
		}
		$registry = \WP_Block_Type_Registry::get_instance();
		$blocks   = $registry->get_all_registered();
		if ( empty( $blocks ) ) {
			return $out;
		}
		foreach ( $blocks as $name => $block ) {
			if ( strpos( $name, 'difl/' ) !== 0 ) {
				continue;
			}
			try {
				$map = \ET\Builder\Packages\Conversion\Conversion::get_preset_attrs_mapping( $name );
				if ( ! empty( $map ) ) {
					$out[ $name ] = $map;
				}
			} catch ( \Throwable $e ) {
				// Skip module if map cannot be built (e.g. missing block attrs).
				continue;
			}
		}
		return $out;
	}

	public function enqueue_frontend_scripts() {
		if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
			return;
		}

		$plugin_path = plugin_dir_path( __DIR__ );
		$plugin_url  = plugin_dir_url( __DIR__ );
		$path        = 'Builder/Visual-Builder/build/';
		$handle      = 'difl-divi5-new';
		$assets_path = 'Builder/assets/';

		wp_enqueue_style(
			$handle . '-lib',
			$plugin_url . $assets_path . 'styles/df_lib_styles.css',
			[ 'wp-components' ],
			DIFL_VERSION );

		wp_enqueue_style(
			$handle,
			$plugin_url . $path . 'styles/bundle.css',
			[ 'wp-components' ],
			DIFL_VERSION );

		wp_enqueue_script('lightgallery-script');
		wp_enqueue_script( 'swiper-script' );
		wp_enqueue_script( 'df-contentcarousel' );
	}
}

new Main();
