<?php
use ET\Builder\FrontEnd\Assets\DynamicAssetsUtils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;

/**
 * Add et_icons_fa (icons_fa_all.css) to the given assets list.
 * Same structure as Divi: feature_state->early_global_asset_list['et_icons_fa'].
 *
 * @param array $assets Current assets list (passed by filter).
 * @return array Modified assets list.
 */
function difl_global_fa_icon_asset( $assets ) {
	if ( isset( $assets['et_icons_fa'] ) ) {
		return $assets;
	}
	$assets_prefix = null;
	if ( class_exists( 'ET\Builder\FrontEnd\Assets\DynamicAssetsUtils' ) ) {
		$assets_prefix = \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::get_dynamic_assets_path();
	} elseif ( function_exists( 'et_get_dynamic_assets_path' ) ) {
		$assets_prefix = et_get_dynamic_assets_path();
	}

	if ( $assets_prefix ) {
		$assets['et_icons_all'] = [
			'css' => $assets_prefix . '/css/icons_all.css',
		];

        // font-awesome icon asset.
		$assets['et_icons_fa'] = [
			'css' => $assets_prefix . '/css/icons_fa_all.css',
		];
	}

	return $assets;
}

/**
 * Add et_icons_fa to the early global assets list (Divi 5).
 * Filter passes ( $early_global_asset_list, $assets_args, $list_builder ).
 */
function difl_early_global_fa_icon_asset( $early_global_asset_list, $assets_args, $list_builder ) {
	return difl_global_fa_icon_asset( $early_global_asset_list );
}

// Filter to add type="module" to the Script tags
function difl_add_module_type_attribute( $tag, $handle, $src ) {
    // List of script handles that should be modules
    $module_scripts = [ 'df-spline-viewer-lib' ];

    if ( in_array( $handle, $module_scripts ) ) {
        // Remove type='text/javascript' and add type="module"
        $tag = str_replace( "type='text/javascript'", "type='module'", $tag );
        // Also handle double quotes
        $tag = str_replace( 'type="text/javascript"', 'type="module"', $tag );

        // If type attribute doesn't exist at all, add it
        if ( strpos( $tag, 'type=' ) === false ) {
            $tag = str_replace( ' src=', ' type="module" src=', $tag );
        }
    }

    return $tag;
}

add_filter( 'script_loader_tag', 'difl_add_module_type_attribute', 10, 3 );

/**
 * Register FontAwesome asset globally. We cannot set feature_state->use_fa_icons (private);
 * we add the same asset via Divi's early and late list filters instead.
 */
function difl_register_global_fa_icon_asset() {
	// Divi 5 early asset filter.
	add_filter( 'divi_frontend_assets_dynamic_assets_global_assets_list', 'difl_early_global_fa_icon_asset', 10, 3 );

	// Divi 5 late asset filter.
	add_filter( 'divi_frontend_assets_dynamic_assets_late_global_assets_list', 'difl_global_fa_icon_asset', 10, 1 );
}

add_action( 'init', 'difl_register_global_fa_icon_asset', 5 );

function df_load_pagination_script() {
    \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::enqueue_pagination_script();
    \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::enqueue_blog_script();
    $reinit_script = "
    (function($) {

        function initDFPagination() {
            if (typeof window.et_pb_init_pagination === 'function') {
                window.et_pb_init_pagination();
                return true;
            }
            return false;
        }

        if (!initDFPagination()) {
            // Wait for document ready
            $(function() {
                if (!initDFPagination()) {
                    // Re-trigger the init event after a short delay
                    setTimeout(function() {
                        if (typeof window.et_pb_init_pagination === 'function') {
                            window.et_pb_init_pagination();
                        } else {
                            $(window).trigger('et_pb_init_modules');
                        }
                    }, 500);
                }
            });
        }

        // Also listen for the event in case it fires later
        $(window).on('et_pb_init_modules', function() {
            initDFPagination();
        });

    })(jQuery);
    ";
    wp_add_inline_script('divi-script-library-pagination', $reinit_script);
}
function D4ToD5CheckboxValueConversion($value) {
    $value = is_string( $value ) ? $value : '';
    $devicesStatus = explode('|', $value);
    $convertedValue = [];

    if (isset($devicesStatus[0]) && $devicesStatus[0] === 'on') {
        $convertedValue[] = 'desktop';
    }
    if (isset($devicesStatus[1]) && $devicesStatus[1] === 'on') {
        $convertedValue[] = 'small';
    }

    return $convertedValue;
}
function D4ToD5ConvertTaxonomyValue( $value ) {
    if ( ! is_string( $value ) || $value === '' ) {
        return [];
    }
    return explode(',', $value);
}

function D4ToD5ConvertDeviceValue( $value ) {
    $value = is_string( $value ) ? $value : '';
    $newValue = explode('|', $value);
    $devices = ['desktop', 'tablet', 'mobile'];

    $updatedValue = [];

    foreach ($newValue as $index => $val) {
        if ($val === 'on' && isset($devices[$index])) {
            $updatedValue[] = $devices[$index];
        }
    }

    return $updatedValue;
}


function D4ToD5ConvertStickyPosts( $ignore_specific_sticky ) {
    $value = [];
    if(function_exists('df_get_sticky_post_ids')) {

        $sticky_key = df_get_sticky_post_ids();
        if (!empty($ignore_specific_sticky) && !is_array($ignore_specific_sticky)) {
            $ignore_specific_sticky = explode('|', $ignore_specific_sticky);
            sort($sticky_key);
            if ($ignore_specific_sticky[0] === 'on') {
                $post_not_in = array_diff($sticky_key, [0]);
                $value = array_merge(["all"], $post_not_in);
            } else {
                if ( count( $sticky_key ) !== count( $ignore_specific_sticky ) ) {
                    return [];
                }
                $sticky_key_values = array_combine($sticky_key, $ignore_specific_sticky);
                $post_not_in = array_keys(array_filter($sticky_key_values, function($v) {
                    return $v === 'on';
                }));
                $value = array_merge($value, $post_not_in);
            }

        }
    }
    if (!empty($value)) {
        $value = array_map('strval', $value);
    }
    return $value;
}
function D4ToD5SortableList( $value ) {
    if ( ! is_string( $value ) || $value === '' ) {
        return [];
    }
    $value  = str_replace( [ '%91', '%93', '%22' ], [ '[', ']', '"' ], $value );
    $parsed = json_decode( $value, true );

    return is_array( $parsed ) ? $parsed : [];
}

function ChangeAlignValue($value) {
    switch ($value) {
        case 'text-top':
            return 'flex-start';
        case 'text-bottom':
            return 'flex-end';
        case 'middle':
            return 'center';
        default:
            return 'flex-start';
    }
}

function generateModuleInnerContentConversionOutline( $module_json ) {
    $attributes            = $module_json['attributes'] ?? [];
    $module_attribute_keys = [];

    foreach ( $attributes as $parent_key => $parent_value ) {
        if ( $parent_key === 'module' ) {
            continue;
        }

        if ( isset( $parent_value['settings']['innerContent']['items'] ) ) {
            $child_elements = $parent_value['settings']['innerContent']['items'];
            foreach ( $child_elements as $child_key => $child_value ) {
                $key_to_use                           = $child_value['subName'] ?? $child_key;
                $module_attribute_keys[ $key_to_use ] = "{$parent_key}.innerContent.*.{$key_to_use}";
            }
        } elseif ( isset( $parent_value['settings']['innerContent']['item'] ) ) {
            $key_to_use                           = $parent_key;
            $module_attribute_keys[ $key_to_use ] = "{$parent_key}.innerContent.*";
        }
    }

    return $module_attribute_keys;
}

function convertTransitionValue( $value ) {
    $transition = [
        'ease'        => 'ease',
        'ease_in'     => 'ease-in',
        'ease_in_out' => 'ease-in-out',
        'ease_out'    => 'ease-out',
        'linear'      => 'linear',
        'bounce'      => 'cubic-bezier(.2,.85,.4,1.275)',
    ];

    return $transition[ $value ] ?? $value;
}

/**
 * Convert D4 gradient position value to D5 format (strip %).
 * If value is empty and cannot be migrated, default to 0 for start_position and 100 for end_position.
 *
 * @param mixed  $value   The attribute value from D4.
 * @param array $context  Optional. Context passed by conversion: attrs, desktopName, moduleName, viewport, state.
 * @return string
 */
function D4ToD5HandleGradientColorPosition( $value, $context = [] ) {
    if ( is_string( $value ) && strpos( $value, '%' ) !== false ) {
        $value = str_replace( '%', '', $value );
    }

    $is_empty = ( $value === null || $value === '' || ( is_string( $value ) && trim( (string) $value ) === '' ) );
    if ( $is_empty && ! empty( $context['desktopName'] ) ) {
        $name = $context['desktopName'];
        if ( strpos( $name, 'start_position' ) !== false ) {
            return '0';
        }
        if ( strpos( $name, 'end_position' ) !== false ) {
            return '100';
        }
    }

    return $value??0;
}

function D4ToD5HandleGradientType( $value ) {
    if ( $value === 'radial' ) {
        $value = 'circular';
    }

    return $value;
}

/**
 * Convert a position value (e.g. "50px", "50%", "50") to a 0-100 percentage string.
 *
 * @param string|int|float $position Position value, possibly with unit (px, %, em, etc.).
 * @return string Position as 0-100 string.
 */
function difl_gradient_position_to_percent( $position ) {
    if ( $position === null || $position === '' ) {
        return '0';
    }
    $str = trim( (string) $position );
    if ( $str === '' ) {
        return '0';
    }
    $num = (float) preg_replace( '/[^0-9.]/', '', $str );
    $num = max( 0, min( 100, round( $num ) ) );
    return (string) (int) $num;
}

/**
 * Whether a gradient stop array has at least one stop with color or position (D5 hover often has colors only).
 *
 * @param array $gradient Gradient subtree (enabled, stops, …).
 * @return bool
 */
function difl_gradient_has_usable_stops( array $gradient ) {
    if ( ! isset( $gradient['stops'] ) || ! is_array( $gradient['stops'] ) ) {
        return false;
    }
    foreach ( $gradient['stops'] as $stop ) {
        if ( ! is_array( $stop ) ) {
            continue;
        }
        $c = isset( $stop['color'] ) ? $stop['color'] : '';
        $p = isset( $stop['position'] ) ? $stop['position'] : '';
        if ( ( $c !== '' && $c !== null ) || ( $p !== '' && $p !== null ) ) {
            return true;
        }
    }
    return false;
}

/**
 * D5 hover gradients often omit `enabled` or use D4-style `on|desktop`; core only checked `enabled === 'on'`,
 * skipped stop normalization, and the builder emitted NaN for missing stop positions.
 *
 * @param array $gradient Reference to gradient subtree; may normalize `enabled` to `on` for `on|*` strings.
 * @return bool
 */
function difl_gradient_is_effectively_enabled( array &$gradient ) {
    if ( isset( $gradient['enabled'] ) && is_string( $gradient['enabled'] ) && preg_match( '/^on\|/', $gradient['enabled'] ) ) {
        $gradient['enabled'] = 'on';
    }
    if ( ! array_key_exists( 'enabled', $gradient ) ) {
        return difl_gradient_has_usable_stops( $gradient );
    }
    $e = $gradient['enabled'];
    if ( $e === 'off' || false === $e || '' === $e ) {
        return false;
    }
    if ( $e === 'on' || true === $e ) {
        return true;
    }
    return difl_gradient_has_usable_stops( $gradient );
}

// Default gradient start color when no color/position from D4 (position 0).
if ( ! defined( 'DIFL_GRADIENT_DEFAULT_COLOR_START' ) ) {
    define( 'DIFL_GRADIENT_DEFAULT_COLOR_START', '#2B87DA' );
}
// Default gradient end color when no color/position from D4 (position 100).
if ( ! defined( 'DIFL_GRADIENT_DEFAULT_COLOR_END' ) ) {
    define( 'DIFL_GRADIENT_DEFAULT_COLOR_END', '#29C4A9' );
}

/**
 * Process gradient stops after D4→D5 conversion. Only runs when gradient is enabled.
 *
 * Scenarios:
 * 1. Gradient enabled is off → do nothing; leave gradient unchanged.
 * 2. Gradient enabled, no stops key → push default stops: 0 (#2B87DA), 100 (#29C4A9).
 * 3. Gradient enabled, stops exist but none have color or position → same default stops.
 * 4. Gradient enabled, one stop with color → push two stops: 0 (user color), 100 (default end #29C4A9).
 * 5. Gradient enabled, stops have color but no position → set position 0 (first), 100 (last), interpolate middle.
 * 6. Gradient enabled, stops have position but no color → assign default colors (first #2B87DA, last #29C4A9).
 * 7. Gradient enabled, stops have both color and position → convert position to % (strip px/other units).
 *
 * @param array $arr Nested array (converted attrs or subtree). Modified by reference.
 */
function difl_ensure_gradient_stops_positions( array &$arr ) {
    foreach ( $arr as $key => &$value ) {
        if ( ! is_array( $value ) ) {
            continue;
        }
        if ( $key === 'gradient' ) {
            $enabled = difl_gradient_is_effectively_enabled( $value );

            // Ensure stops is always an array when present (desktop, hover, tablet, phone). Prevents
            // "r.stops.map is not a function" in JS declarationFunction when hover/state has stops as object or wrong shape.
            if ( array_key_exists( 'stops', $value ) && ! is_array( $value['stops'] ) ) {
                $value['stops'] = $enabled
                    ? [ [ 'position' => '0', 'color' => DIFL_GRADIENT_DEFAULT_COLOR_START ], [ 'position' => '100', 'color' => DIFL_GRADIENT_DEFAULT_COLOR_END ] ]
                    : [];
            }
            // When stops is associative (e.g. from path "stops.0.color" / "stops.1.color"), convert to indexed array
            // so JSON encodes as [...], and JS can safely call .map on it. ksort first: Divi may merge stop 1 before stop 0;
            // array_values alone preserves insertion order and would swap D4 start/end (especially on hover).
            if ( isset( $value['stops'] ) && is_array( $value['stops'] ) ) {
                $keys = array_keys( $value['stops'] );
                $n    = count( $value['stops'] );
                if ( $n > 0 && $keys !== range( 0, $n - 1 ) ) {
                    ksort( $value['stops'], SORT_NUMERIC );
                    $value['stops'] = array_values( $value['stops'] );
                }
            }

            // Scenario 1: Gradient enabled is off — do nothing else.
            if ( ! $enabled ) {
                difl_ensure_gradient_stops_positions( $value );
                continue;
            }

            $has_stops = isset( $value['stops'] ) && is_array( $value['stops'] );

            // Scenario 2: Enabled but no stops key — push default two stops.
            if ( $enabled && ! $has_stops ) {
                $value['stops'] = [
                    [ 'position' => '0', 'color' => DIFL_GRADIENT_DEFAULT_COLOR_START ],
                    [ 'position' => '100', 'color' => DIFL_GRADIENT_DEFAULT_COLOR_END ],
                ];
                continue;
            }

            if ( ! $has_stops ) {
                difl_ensure_gradient_stops_positions( $value );
                continue;
            }

            // Collect stops that have at least color or position.
            $raw_stops = $value['stops'];
            $to_process = [];
            foreach ( $raw_stops as $stop ) {
                if ( ! is_array( $stop ) ) {
                    continue;
                }
                $c = isset( $stop['color'] ) ? $stop['color'] : '';
                $p = isset( $stop['position'] ) ? $stop['position'] : '';
                $has_color = $c !== '' && $c !== null;
                $has_position = $p !== '' && $p !== null;
                if ( ! $has_color && ! $has_position ) {
                    continue;
                }
                $to_process[] = $stop;
            }

            // Scenario 3: Enabled but no usable stops — push default two stops.
            if ( $enabled && empty( $to_process ) ) {
                $value['stops'] = [
                    [ 'position' => '0', 'color' => DIFL_GRADIENT_DEFAULT_COLOR_START ],
                    [ 'position' => '100', 'color' => DIFL_GRADIENT_DEFAULT_COLOR_END ],
                ];
                continue;
            }

            $n = count( $to_process );

            // Scenario 4: Only one stop — push two. Keep user's position if provided; keep user's color if provided.
            if ( $n === 1 ) {
                $single = $to_process[0];
                $c = isset( $single['color'] ) && $single['color'] !== '' && $single['color'] !== null ? $single['color'] : DIFL_GRADIENT_DEFAULT_COLOR_START;
                $p = isset( $single['position'] ) && $single['position'] !== '' && $single['position'] !== null ? difl_gradient_position_to_percent( $single['position'] ) : '0';
                $to_process = [
                    [ 'position' => $p, 'color' => $c ],
                    [ 'position' => '100', 'color' => DIFL_GRADIENT_DEFAULT_COLOR_END ],
                ];
                $n = 2;
            }

            $last = $n - 1;

            // Scenarios 5–7: Fill missing positions (0/100/interpolate), missing colors (defaults), normalize position to %.
            foreach ( $to_process as $i => &$stop ) {
                $c = isset( $stop['color'] ) ? $stop['color'] : '';
                $has_color = $c !== '' && $c !== null;
                if ( ! $has_color ) {
                    $stop['color'] = ( 0 === $i ) ? DIFL_GRADIENT_DEFAULT_COLOR_START : ( ( $i === $last ) ? DIFL_GRADIENT_DEFAULT_COLOR_END : DIFL_GRADIENT_DEFAULT_COLOR_START );
                }
                $has_position = isset( $stop['position'] ) && $stop['position'] !== '' && $stop['position'] !== null;
                if ( $has_position ) {
                    $stop['position'] = difl_gradient_position_to_percent( $stop['position'] );
                } else {
                    if ( 0 === $i ) {
                        $stop['position'] = '0';
                    } elseif ( $i === $last ) {
                        $stop['position'] = '100';
                    } else {
                        $stop['position'] = $n > 1 ? (string) (int) round( ( 100 * $i ) / $last ) : '0';
                    }
                }
            }
            unset( $stop );
            $value['stops'] = $to_process;
            continue;
        }
        difl_ensure_gradient_stops_positions( $value );
    }
    unset( $value );
}

function D4ToD5Background( $d4_key, $d5_key ) {
    // Keep in sync with Builder/Visual-Builder/src/utils/conversion/index.js → D4ToD5Background.
    return [
        "{$d4_key}_bgcolor" => "{$d5_key}.decoration.background.*.color",
        "{$d4_key}_bg_color" => "{$d5_key}.decoration.background.*.color",
        "{$d4_key}_bg_enable_color" => "{$d5_key}.decoration.background.enable",
        "{$d4_key}_use_gradient" => "{$d5_key}.decoration.background.*.gradient.enabled",
        "{$d4_key}_bg_use_color_gradient" => "{$d5_key}.decoration.background.*.gradient.enabled",
        "{$d4_key}_color_gradient_1" => "{$d5_key}.decoration.background.*.gradient.stops.0.color",
        "{$d4_key}_bg_color_gradient_stops" => "{$d5_key}.decoration.background.*.gradient.stops.0.color",
        // Legacy PHP alias (older migrations); same target as _bg_color_gradient_stops.
        "{$d4_key}_color_gradient_stops" => "{$d5_key}.decoration.background.*.gradient.stops.0.color",
        "{$d4_key}_color_gradient_2" => "{$d5_key}.decoration.background.*.gradient.stops.1.color",
        "{$d4_key}_gradient_type" => "{$d5_key}.decoration.background.*.gradient.type",
        "{$d4_key}_bg_color_gradient_type" => "{$d5_key}.decoration.background.*.gradient.type",
        "{$d4_key}_radial_direction" => "{$d5_key}.decoration.background.*.gradient.directionRadial",
        "{$d4_key}_gradient_direction" => "{$d5_key}.decoration.background.*.gradient.direction",
        "{$d4_key}_bg_color_gradient_direction" => "{$d5_key}.decoration.background.*.gradient.direction",
        "{$d4_key}_start_position" => "{$d5_key}.decoration.background.*.gradient.stops.0.position",
        "{$d4_key}_end_position" => "{$d5_key}.decoration.background.*.gradient.stops.1.position",
        "{$d4_key}_above_image" => "{$d5_key}.decoration.background.*.gradient.overlaysImage",
        "{$d4_key}_bg_color_gradient_overlays_image" => "{$d5_key}.decoration.background.*.gradient.overlaysImage",
        "{$d4_key}_bg_enable_image" => "{$d5_key}.decoration.background.*.gradient.overlaysImage",
        "{$d4_key}_bg_blend" => "{$d5_key}.decoration.background.*.image.blend",
        "{$d4_key}_background_image" => "{$d5_key}.decoration.background.*.image.url",
        "{$d4_key}_bg_image" => "{$d5_key}.decoration.background.*.image.url",
        "{$d4_key}_background_image_size" => "{$d5_key}.decoration.background.*.image.size",
        "{$d4_key}_size_width" => "{$d5_key}.decoration.background.*.image.width",
        "{$d4_key}_size_height" => "{$d5_key}.decoration.background.*.image.height",
        "{$d4_key}_background_image_position" => "{$d5_key}.decoration.background.*.image.position",
        "{$d4_key}_position_horizontal" => "{$d5_key}.decoration.background.*.image.horizontalOffset",
        "{$d4_key}_position_vertical" => "{$d5_key}.decoration.background.*.image.verticalOffset",
        "{$d4_key}_background_image_repeat" => "{$d5_key}.decoration.background.*.image.repeat",
        "{$d4_key}_bg_color_gradient_repeat" => "{$d5_key}.decoration.background.*.image.repeat",
        // D4 hover gradient → D5: hover is per breakpoint (desktop.hover, …), not background.hover.
        // Same stop index semantics as desktop (gradient_1/start → stops.0, gradient_2/end → stops.1); order is enforced in difl_ensure_gradient_stops_positions.
        "{$d4_key}_color_gradient_1__hover" => "{$d5_key}.decoration.background.desktop.hover.gradient.stops.0.color",
        "{$d4_key}_color_gradient_2__hover" => "{$d5_key}.decoration.background.desktop.hover.gradient.stops.1.color",
        "{$d4_key}_color_gradient_1__hover_enabled" => "{$d5_key}.decoration.background.desktop.hover.gradient.enabled",
        "{$d4_key}_color_gradient_2__hover_enabled" => "{$d5_key}.decoration.background.desktop.hover.gradient.enabled",
        "{$d4_key}_gradient_type__hover" => "{$d5_key}.decoration.background.desktop.hover.gradient.type",
        "{$d4_key}_radial_direction__hover" => "{$d5_key}.decoration.background.desktop.hover.gradient.directionRadial",
        "{$d4_key}_gradient_direction__hover" => "{$d5_key}.decoration.background.desktop.hover.gradient.direction",
        "{$d4_key}_bg_color_gradient_direction__hover" => "{$d5_key}.decoration.background.desktop.hover.gradient.direction",
        "{$d4_key}_start_position__hover" => "{$d5_key}.decoration.background.desktop.hover.gradient.stops.0.position",
        "{$d4_key}_end_position__hover" => "{$d5_key}.decoration.background.desktop.hover.gradient.stops.1.position",
    ];
}
function D4ToD5HandleBGImagePosition( $value ) {
    $position_map = [
        'top_left' => 'left top',
        'top_center' => 'center top',
        'top_right' => 'right top',
        'center_left' => 'left center',
        'center_center' => 'center center',
        'center_right' => 'right center',
        'bottom_left' => 'left bottom',
        'bottom_center' => 'center bottom',
        'bottom_right' => 'right bottom',
    ];

    return $position_map[ $value ] ?? $value;
}
function convertBorderRadious( $value ) {
    $valueArray = explode('|', $value);

    $isSingleValue = (strpos($value, '|') === false);

    return [
        'sync' => $isSingleValue ? 'on' : ($valueArray[0] ?? ''),
        'topLeft' => $isSingleValue ? $value : ($valueArray[1] ?? ''),
        'topRight' => $isSingleValue ? $value : ($valueArray[2] ?? ''),
        'bottomRight' => $isSingleValue ? $value : ($valueArray[3] ?? ''),
        'bottomLeft' => $isSingleValue ? $value : ($valueArray[4] ?? ''),
    ];
}
function dfConvertPosition($value) {
    switch ($value) {
        case "top_center":
            return "top center";
        case "top_left":
            return "top left";
        case "top_right":
            return "top right";
        case "bottom_center":
            return "bottom center";
        case "bottom_left":
            return "bottom left";
        case "bottom_right":
            return "bottom right";
        default:
            return $value;
    }
}
function convertVerticalMenuAlignment($value) {
    switch ($value) {
        case "df-vertical-menu-alignment-left":
            return "flex-start";
        case "df-vertical-menu-alignment-right":
            return "flex-end";
        case "df-vertical-menu-alignment-center":
            return "center";
        case "df-vertical-menu-alignment-justified":
            return "left";
    }
    return $value;
}

function D4ToD5BackgroundBulkConversion( $obj ) {
    $bg_obj = [];
    foreach ( $obj as $d4_key => $d5_key ) {
        $bg_obj = array_merge( $bg_obj, D4ToD5Background( $d4_key, $d5_key ) );
    }

    return $bg_obj;
}
function D4ToD5Unit( $value ) {
    if (
        !is_null( $value ) &&
        $value !== '' &&
        !is_bool( $value ) &&
        !( is_float( $value ) && is_nan( $value ) ) &&
        !preg_match( '/[a-z%]+$/i', strval($value) )
    ) {
        return $value . 'px';
    }
    return $value;
}

function df_block_to_flex_align ($value) {
    switch ($value) {
        case 'text-top':
            return 'flex-start';
        case 'text-bottom':
            return 'flex-end';
        case 'middle':
            return 'center';
    }
    return $value;
}
function D4ToD5Spacing( $value ) {
    if ( ! is_string( $value ) || $value === '' ) {
        return [
            'top'            => '',
            'right'          => '',
            'bottom'         => '',
            'left'           => '',
            'syncHorizontal' => 'off',
            'syncVertical'   => 'off',
        ];
    }
    $parts = explode( '|', $value );
    return [
        'top'            => $parts[0] ?? '',
        'right'          => $parts[1] ?? '',
        'bottom'         => $parts[2] ?? '',
        'left'           => $parts[3] ?? '',
        'syncHorizontal' => 'true' === ( $parts[4] ?? '' ) ? 'on' : 'off',
        'syncVertical'   => 'true' === ( $parts[5] ?? '' ) ? 'on' : 'off',
    ];
}

function D4ToD5Icon( $value ) {
    $value = is_string( $value ) ? $value : '';
    $parts = explode( '|', $value );

    return [
        'unicode' => $parts[0] ?? '',
        'type'    => $parts[2] ?? '',
        'weight'  => $parts[4] ?? '',
    ];
}

function D4ToD5RoundedCorner( $value ) {
    $value = is_string( $value ) ? $value : '';
    $parts = explode( '|', $value );

    return [
        'radius' => [
            'sync'        => $parts[0] ?? '',
            'topLeft'     => $parts[1] ?? '',
            'topRight'    => $parts[2] ?? '',
            'bottomLeft'  => $parts[3] ?? '',
            'bottomRight' => $parts[4] ?? '',
        ],
    ];
}

function D4ToD5CustomMargin( $value ) {
    $value = is_string( $value ) ? $value : '';
    $parts = explode( '|', $value );

    return [
        'padding' => [
            'top'            => $parts[0] ?? '',
            'right'          => $parts[1] ?? '',
            'bottom'         => $parts[2] ?? '',
            'left'           => $parts[3] ?? '',
            'syncVertical'   => ( isset( $parts[4] ) && $parts[4] === 'true' ) ? 'on' : 'off',
            'syncHorizontal' => ( isset( $parts[5] ) && $parts[5] === 'true' ) ? 'on' : 'off',
        ],
    ];
}

function convertMultiCheckbox( $value, $target ) {
    $value  = is_string( $value ) ? $value : '';
    $values = explode( '|', $value );
    $result = array();

    foreach ( $values as $index => $v ) {
        if ( $v === 'on' && isset( $target[ $index ] ) ) {
            $result[] = $target[ $index ];
        }
    }

    return $result;
}

function convertHeadingTags( $value ) {
    return convertMultiCheckbox( $value, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) );
}

function ConvertAlignment( $value ) {
    $d5_value = 'auto auto';

    if ( 'left' === $value ) {
        $d5_value = 'auto auto auto 0';
    } elseif ( 'right' === $value ) {
        $d5_value = 'auto 0 auto auto';
    }

    return $d5_value;
}

function df_set_flex_position($value) {
    switch ($value) {
        case 'left':
            return 'start';
        case 'right':
            return 'end';
        case 'center':
            return 'center';
        default:
            return $value;
    }
}

function D4ToD5HandleFlexDirection( $value ) {
    $position_map = [
        'flex_top'    => 'column',
        'flex_bottom' => 'column-reverse',
        'flex_left'   => 'row',
        'flex_right'  => 'row-reverse',
    ];

    return $position_map[ $value ] ?? $value;
}

function D4ToD5HandleFlexPosition( $value ) {
    if ( ! is_string( $value ) ) {
        return $value;
    }
    return str_replace( 'flex_', '', $value );
}

function D4ToD5HandleAlignment( $value ) {
    $position_map = [
        'left'  => 'start',
        'right' => 'end',
    ];

    return $position_map[ $value ] ?? $value;
}

if ( ! function_exists( 'df_sanitize_src' ) ) {
    /**
     * Sanitize an image source (URL or Base64).
     *
     * Ensures that the provided image source is safe for use in an HTML `src` attribute.
     * Supports both standard URLs (absolute or relative) and Base64-encoded image data URIs.
     *
     * - If the source is a valid URL (absolute or starts with `/`), it returns an escaped URL.
     * - If the source is a valid Base64 image (png, jpeg, gif, webp, svg+xml), it returns an escaped attribute.
     * - Otherwise, it returns an empty string.
     *
     * @since 1.0.0
     *
     * @param string $src The image source to sanitize. Can be a URL or Base64 string.
     * @return string Sanitized and safe image source. Empty string if invalid.
     */
    function df_sanitize_src( $src ) {
        $src = trim( $src );

        // Valid full or relative URL.
        if ( filter_var( $src, FILTER_VALIDATE_URL ) || str_starts_with( $src, '/' ) ) {
            return esc_url( $src );
        }

        // Valid Base64 image (safe formats only).
        if ( preg_match( '/^data:image\/(png|jpe?g|gif|webp|svg\+xml);base64,[A-Za-z0-9+\/=]+$/', $src ) ) {
            return esc_attr( $src );
        }

        // keep empty when Invalid or unsafe source.
        return '';
    }
}

function customGetResponsiveValue($props, $field, $fallback = null)
{
    $breakpoints = isset($props['breakpointNames']) ? $props['breakpointNames'] : [
        "ultraWide",
        "widescreen",
        "desktop",
        "tabletWide",
        "tablet",
        "phoneWide",
        "phone"
    ];
    $value = null;
    $fromIndex = 0;

    if (isset($props['breakpoint']) && ($idx = array_search($props['breakpoint'], $breakpoints)) !== false) {
        $fromIndex = $idx;
    }
    for ($i = $fromIndex; $i < count($breakpoints); $i++) {
        $bp = $breakpoints[$i];
        if (
            isset($props['attr'][$bp]['value'][$field])
        ) {
            $value = $props['attr'][$bp]['value'][$field];
            break;
        }
    }

    if ($value === null && $fromIndex > 0) {
        for ($i = $fromIndex - 1; $i >= 0; $i--) {
            $bp = $breakpoints[$i];
            if (
                isset($props['attr'][$bp]['value'][$field])
            ) {
                $value = $props['attr'][$bp]['value'][$field];
                break;
            }
        }
    }

    if ($value === null) {
        $value = $fallback;
    }

    return $value;
}
/**
 * Recursively resolve global color references to concrete values in an attrs array.
 * For divi/color-picker (and any color) values: passes each string through
 * StyleUtils::resolve_global_color_to_value() so $props['attrValue'] gets concrete colors.
 *
 * @param array $attrs Module attributes (or any nested array of attrs).
 * @return array The same structure with color strings resolved.
 */
function difl_resolve_color_values_in_attrs( array $attrs ) {
	$style_utils = 'ET\Builder\Packages\StyleLibrary\Utils\Utils';
	if ( ! class_exists( $style_utils ) ) {
		return $attrs;
	}
	foreach ( $attrs as $key => $value ) {
		if ( is_array( $value ) ) {
			$attrs[ $key ] = difl_resolve_color_values_in_attrs( $value );
		} elseif ( is_string( $value ) && $value !== '' ) {
			$resolved = $style_utils::resolve_global_color_to_value( $value );
			if ( $resolved !== $value ) {
				$attrs[ $key ] = $resolved;
			}
		}
	}
	return $attrs;
}

/**
 * Filter callback for divi_module_library_register_module_attrs.
 * Resolves global colors so divi/color-picker values are concrete before $props['attrValue'].
 *
 * @param array $module_attrs Merged module attributes.
 * @param array $filter_args  Context: name, attrs, id, storeInstance, parentAttrs (optional).
 * @return array Modified module attrs.
 */
function difl_resolve_global_color_variable_values( array $module_attrs, array $filter_args ) {
	return difl_resolve_color_values_in_attrs( $module_attrs );
}

/**
 * Replace {{selector}} / {{baseSelector}} with the module order class (mirrors VB resolveBgSelectorTemplate).
 *
 * @param string $template Selector template from module.json.
 * @param string $order_class Module order class (e.g. .difl_xxx_0).
 * @return string
 */
function difl_resolve_bg_selector_template( $template, $order_class ) {
	if ( ! is_string( $template ) || $template === '' || ! is_string( $order_class ) || $order_class === '' ) {
		return is_string( $template ) ? $template : '';
	}
	return str_replace(
		[ '{{selector}}', '{{baseSelector}}' ],
		[ $order_class, $order_class ],
		$template
	);
}

/**
 * @param array $def Single attribute definition (module metadata shape).
 * @return bool
 */
function difl_attr_def_has_hide_panels_background( array $def ) {
	$bg = $def['settings']['decoration']['background'] ?? null;
	if ( ! is_array( $bg ) ) {
		return false;
	}
	$props = [];
	if ( isset( $bg['item']['component']['props'] ) && is_array( $bg['item']['component']['props'] ) ) {
		$props = $bg['item']['component']['props'];
	} elseif ( isset( $bg['component']['props'] ) && is_array( $bg['component']['props'] ) ) {
		$props = $bg['component']['props'];
	}
	return isset( $props['hidePanels'] ) && is_array( $props['hidePanels'] ) && count( $props['hidePanels'] ) > 0;
}

/**
 * @param array $def Single attribute definition.
 * @return string Non-empty selector template or ''.
 */
function difl_get_selector_template_for_custom_bg_attr( array $def ) {
	if ( isset( $def['selector'] ) && is_string( $def['selector'] ) && trim( $def['selector'] ) !== '' ) {
		return trim( $def['selector'] );
	}
	$bg_sel = $def['styleProps']['background']['selectors']['desktop']['value'] ?? null;
	if ( is_string( $bg_sel ) && trim( $bg_sel ) !== '' ) {
		return trim( $bg_sel );
	}
	$sp = $def['styleProps']['selector'] ?? null;
	if ( is_string( $sp ) && trim( $sp ) !== '' ) {
		return trim( $sp );
	}
	return '';
}

/**
 * Hover selector template for supplemental hidePanels background CSS (module.json `hover` or styleProps.background.selectors.desktop.hover).
 *
 * @param array $def Single attribute definition.
 * @return string Non-empty hover template or ''.
 */
function difl_get_hover_selector_template_for_custom_bg_attr( array $def ) {
	if ( isset( $def['hover'] ) && is_string( $def['hover'] ) && trim( $def['hover'] ) !== '' ) {
		return trim( $def['hover'] );
	}
	$h = $def['styleProps']['background']['selectors']['desktop']['hover'] ?? null;
	if ( is_string( $h ) && trim( $h ) !== '' ) {
		return trim( $h );
	}
	return '';
}

/**
 * Supplemental background-size / position / repeat when gradient is on or image URL is set (mirrors processBgLike4.js).
 *
 * @param array $props CommonStyle declaration props (expects attrValue).
 * @return string
 */
function difl_process_bg_like4_declarations( array $props ) {
	$value    = $props['attrValue'] ?? null;
	$attr     = $props['attr'] ?? null;
	$bp       = $props['breakpoint'] ?? 'desktop';
	$state    = $props['state'] ?? 'value';
	if ( 'value' !== $state && is_array( $attr ) && isset( $attr[ $bp ]['value'] ) && is_array( $attr[ $bp ]['value'] ) && is_array( $value ) ) {
		$value = array_replace_recursive( $attr[ $bp ]['value'], $value );
	}
	if ( ! is_array( $value ) ) {
		return '';
	}
	$gradient_on = isset( $value['gradient']['enabled'] ) && $value['gradient']['enabled'] === 'on';
	$url         = $value['image']['url'] ?? '';
	$has_image   = is_string( $url ) && $url !== '';
	if ( ! $gradient_on && ! $has_image ) {
		return '';
	}
	$is_custom = ( $value['image']['size'] ?? '' ) === 'custom';
	$size      = $value['image']['size'] ?? 'cover';
	if ( $is_custom ) {
		$w    = $value['image']['width'] ?? '50%';
		$h    = $value['image']['height'] ?? '50%';
		$size = "{$w} {$h}";
	}
	$position = $value['image']['position'] ?? 'center';
	$repeat   = $value['image']['repeat'] ?? 'no-repeat';
	return "background-size: {$size};background-position: {$position};background-repeat: {$repeat};";
}

/**
 * Style chunks from CommonStyle::style for attrs whose decoration.background uses hidePanels (mirrors CustomDecorationBackgroundStyles.js).
 *
 * Each chunk is the return value of CommonStyle::style (array of rules). Use with spread in Style::add or array_merge into $styles.
 *
 * @param string               $order_class Order class.
 * @param array                $attrs       Merged module attrs.
 * @param array<string, mixed> $attribute_definitions module_metadata->attributes.
 * @return array<int, array>
 */
function difl_custom_decoration_background_style_chunks( $order_class, array $attrs, $attribute_definitions ) {
	$chunks = [];
	if ( ! is_string( $order_class ) || $order_class === '' || ! is_array( $attribute_definitions ) || ! class_exists( CommonStyle::class ) ) {
		return $chunks;
	}
	foreach ( $attribute_definitions as $key => $def ) {
		if ( ! is_array( $def ) || ! difl_attr_def_has_hide_panels_background( $def ) ) {
			continue;
		}
		$template = difl_get_selector_template_for_custom_bg_attr( $def );
		if ( $template === '' ) {
			continue;
		}
		$selector       = difl_resolve_bg_selector_template( $template, $order_class );
		$hover_template = difl_get_hover_selector_template_for_custom_bg_attr( $def );
		$bg_attr        = $attrs[ $key ]['decoration']['background'] ?? null;
		if ( $hover_template !== '' ) {
			$hover_selector = difl_resolve_bg_selector_template( $hover_template, $order_class );
			// Divi CommonStyle::style always reads $args['selector']; pass value selector even when using `selectors` (PHP 8+).
			$style_args     = [
				'selector'            => $selector,
				'selectors'           => [
					'desktop' => [
						'value' => $selector,
						'hover' => $hover_selector,
					],
				],
				'attr'                => $bg_attr,
				'declarationFunction' => 'difl_process_bg_like4_declarations',
			];
		} else {
			$style_args = [
				'selector'            => $selector,
				'attr'                => $bg_attr,
				'declarationFunction' => 'difl_process_bg_like4_declarations',
			];
		}
		$chunk = CommonStyle::style( $style_args );
		if ( ! empty( $chunk ) ) {
			$chunks[] = $chunk;
		}
	}
	return $chunks;
}

/**
 * Merge supplemental background chunks into a flat list (StyleUtil / module_styles inner array).
 *
 * @param array<int, mixed> $styles Target list (by ref).
 */
function difl_merge_custom_decoration_background_into_flat_styles( array &$styles, $order_class, array $attrs, $attribute_definitions ) {
	foreach ( difl_custom_decoration_background_style_chunks( $order_class, $attrs, $attribute_definitions ) as $chunk ) {
		foreach ( $chunk as $row ) {
			$styles[] = $row;
		}
	}
}

