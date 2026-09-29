<?php
/**
 * Global preset attrs map for all DIFL modules.
 * Ensures every attribute (including "render": false in module.json) is included
 * when creating/saving presets, so "New Preset From Current Styles" stores all values.
 *
 * @package DIFL\Server
 */

namespace DIFL\Server;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use ET\Builder\Packages\Module\Options\ModuleOptionsPresetAttrs;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use WP_Block_Type_Registry;

/**
 * Class PresetAttrsMapGlobal
 *
 * For any module whose name starts with difl/, traverses the block's attribute
 * settings and adds preset map entries for every group-item and group (spacing,
 * background, border, etc.) so that render:false fields are also saved in presets.
 */
class PresetAttrsMapGlobal {

	const DIFL_PREFIX = 'difl/';
	private static $module_map_cache = [];

	/**
	 * Per-module content-group blacklist. Top-level attr groups listed here
	 * are skipped when building the preset attrs map, so a saved preset
	 * never captures fields that belong to per-instance content (e.g. the
	 * target date on a Countdown Timer, or the labels and expiration copy).
	 *
	 * Why save-time exclusion: filtering only at apply time still leaves the
	 * content data inside the saved preset, where it bloats storage and
	 * leaks through any future read path that bypasses our apply-time guard.
	 *
	 * Add a module's slug here when its preset should be styles-only.
	 *
	 * @var array<string, string[]>
	 */
	private static $module_content_groups = [
		'difl/countdowntimer' => [ 'timer_settings', 'label_settings', 'expiration_settings' ],
		'difl/barchart'       => [ 'data_source', 'chart_labels' ],
		'difl/linechart'      => [ 'data_source', 'chart_labels' ],
		'difl/piechart'       => [ 'data_source' ],
	];

	/**
	 * Filter: extend the preset attrs map for all difl modules.
	 *
	 * @param array  $map         Existing preset attributes map.
	 * @param string $module_name Module name (e.g. difl/advanced-person).
	 * @return array Merged map including all attributes (including render:false).
	 */
	public static function get_map( array $map, string $module_name ) {
		if ( strpos( $module_name, self::DIFL_PREFIX ) !== 0 ) {
			return $map;
		}

		if ( isset( self::$module_map_cache[ $module_name ] ) ) {
			return self::merge_maps( $map, self::$module_map_cache[ $module_name ] );
		}

		$block = WP_Block_Type_Registry::get_instance()->get_registered( $module_name );
		if ( ! $block ) {
			$block = ModuleRegistration::get_module_settings( $module_name );
		}
		$block_attrs = $block->attributes ?? [];
		if ( empty( $block_attrs ) ) {
			return $map;
		}
		$block_attrs = is_array( $block_attrs ) ? $block_attrs : (array) $block_attrs;

		$content_groups = self::$module_content_groups[ $module_name ] ?? [];
		$extra = self::collect_preset_map_from_attributes( $block_attrs, $content_groups );
		self::$module_map_cache[ $module_name ] = $extra;

		return self::merge_maps( $map, $extra );
	}

	private static function merge_maps( array $target, array $source ) {
		foreach ( $source as $key => $value ) {
			$target[ $key ] = $value;
		}

		return $target;
	}

	/**
	 * Recursively traverse block attributes and collect preset map entries for every group-item/group.
	 *
	 * @param array $attributes Block attributes (from module metadata).
	 * @return array Map key => [ attrName, preset, subName? ].
	 */
	private static function collect_preset_map_from_attributes( array $attributes, array $skip_groups = [] ) {
		$result = [];

		foreach ( $attributes as $attr_name => $attr_data ) {
			if ( $attr_name === 'module' ) {
				continue;
			}
			// Per-module content groups are skipped so a saved preset never
			// captures fields like time data, labels, or expiration text.
			if ( in_array( $attr_name, $skip_groups, true ) ) {
				continue;
			}
			$attr_data = is_array( $attr_data ) ? $attr_data : (array) $attr_data;
			if ( empty( $attr_data ) ) {
				continue;
			}
			$settings = $attr_data['settings'] ?? [];
			if ( empty( $settings ) ) {
				continue;
			}

			foreach ( $settings as $attrs_type => $setting_items ) {
				$setting_items = is_array( $setting_items ) ? $setting_items : (array) $setting_items;
				if ( empty( $setting_items ) ) {
					continue;
				}
				foreach ( $setting_items as $setting_item_key => $setting_item ) {
					$setting_item = is_array( $setting_item ) ? $setting_item : (array) $setting_item;
					if ( empty( $setting_item ) ) {
						continue;
					}
					$full_attr_name = $attr_name . '.' . $attrs_type . '.' . $setting_item_key;
					$group_type     = $setting_item['groupType'] ?? null;

					if ( $group_type === 'group-item' ) {
						$item = $setting_item['item'] ?? null;
						$item = is_array( $item ) ? $item : (array) $item;
						$component = $item['component'] ?? [];
						$component = is_array( $component ) ? $component : (array) $component;
						if ( ! empty( $item['attrName'] ) && ! empty( $component['name'] ) ) {
							$item_attr_name = $item['attrName'];
							$group_name     = $component['name'];
							$group_map      = ModuleOptionsPresetAttrs::get_preset_attrs_from_group( $group_name, $item_attr_name );
							$result         = self::merge_maps( $result, $group_map );
						}
					} elseif ( $group_type === 'group-items' ) {
						$items = $setting_item['items'] ?? [];
						$items = is_array( $items ) ? $items : (array) $items;
						foreach ( $items as $item ) {
							$item = is_array( $item ) ? $item : (array) $item;
							if ( empty( $item ) ) {
								continue;
							}
							$component = $item['component'] ?? [];
							$component = is_array( $component ) ? $component : (array) $component;
							if ( ! empty( $item['attrName'] ) && ! empty( $component['name'] ) ) {
								$group_map = ModuleOptionsPresetAttrs::get_preset_attrs_from_group(
									$component['name'],
									$item['attrName']
								);
								$result = self::merge_maps( $result, $group_map );
							}
						}
					} elseif ( $group_type === 'group' ) {
						$group_name = $setting_item['groupName'] ?? ModuleOptionsPresetAttrs::get_the_group_name_by_key( $attrs_type, $setting_item_key );
						if ( ! empty( $group_name ) ) {
							$args       = [];
							$group_map  = ModuleOptionsPresetAttrs::get_preset_attrs_from_group( $group_name, $full_attr_name, $args );
							$result     = self::merge_maps( $result, $group_map );
						}
					} else {
						$group_name = ModuleOptionsPresetAttrs::get_the_group_name_by_key( $attrs_type, $setting_item_key );
						if ( ! empty( $group_name ) ) {
							$group_map = ModuleOptionsPresetAttrs::get_preset_attrs_from_group( $group_name, $full_attr_name );
							$result    = self::merge_maps( $result, $group_map );
						}
					}
				}
			}
		}

		return $result;
	}
}
