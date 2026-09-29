<?php

namespace DIFL\Server;

use ET\Builder\Framework\Utility\Conditions;
use ET\Builder\Framework\Utility\StringUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\GlobalData\GlobalPreset;
use ET\Builder\Packages\GlobalData\GlobalPresetItem;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Options\Sticky\StickyUtils;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use WP_Block;
use WP_Block_Type;

class Registration extends ModuleRegistration {
	private static $_metadata_folders = [];

	public static function register_module( string $metadata_folder, array $args = [] ): ?WP_Block_Type {
		/*
		 * Normalize the path to this file and the passed metadata folder path before comparing them.
		 * (Do not use ET_BUILDER_5_DIR yet, because this causes false negatives when the Divi theme is
		 * symlinked into a WordPress themes directory. Once the core module check is complete, we'll
		 * replace the metadata folder path with ET_BUILDER_5_DIR.)
		 */
		$metadata_folder = wp_normalize_path( trailingslashit( realpath( $metadata_folder ) ) );
		$et_builder_dir  = wp_normalize_path( trailingslashit( realpath( dirname( __DIR__, 3 ) ) ) );
		$core_module_dir = wp_normalize_path( $et_builder_dir . '/visual-builder/packages/module-library/' );

		/*
		 * Get an array of metadata from a PHP file.
		 * This improves performance for core blocks as it's only necessary to read a single PHP file
		 * instead of reading a JSON file per-block, and then decoding from JSON to PHP.
		 * Using a static variable ensures that the metadata is only read once per request.
		 */
		static $core_modules_metadata;
		if ( ! $core_modules_metadata ) {
			$core_modules_metadata = require __DIR__ . '/all_metadata.php';
		}

		// Get the metadata file path.
		$metadata_file = wp_normalize_path( $metadata_folder . '/module.json' );

		// Check if the metadata directory is a core module directory.
		$is_core_module = str_starts_with( $metadata_folder, $core_module_dir );

		// If the module is not a core module, the metadata file must exist.
		$metadata_file_exists = $is_core_module || file_exists( $metadata_file );

		/*
		 * Exit early.
		 * If no metadata file exists and the module isn't a core module, we don't have anything to add.
		 */
		if ( ! $metadata_file_exists ) {
			return null;
		}

		// Use the same base path if $metadata_folder and ET_BUILDER_5_DIR resolve to the same location.
		if ( StringUtility::starts_with( $metadata_folder, $et_builder_dir ) ) {
			$metadata_folder = str_replace( $et_builder_dir, ET_BUILDER_5_DIR, $metadata_folder );
		}

		// Try to get metadata from the static cache for core modules.
		$metadata = [];
		if ( $is_core_module ) {
			$core_module_name = self::get_core_module_name_from_metadata_folder( $metadata_folder );
			if ( ! empty( $core_modules_metadata[ $core_module_name ] ) ) {
				$metadata = $core_modules_metadata[ $core_module_name ];
			}
		}

		// If metadata is not found in the static cache, read it from the file.
		if ( empty( $metadata ) ) {
			// modeling after WP's wp_json_file_decode() function.
			// but wihh silent failing allowed, whereas
			// wp_json_file_decode() will trigger_error() if it fails.
			$filename = wp_normalize_path( realpath( $metadata_file ) );

			if ( ! $filename ) {
				return null;
			}

			$metadata = json_decode( file_get_contents( $filename ), true );
		}

		if ( JSON_ERROR_NONE !== json_last_error() || empty( $metadata ) ) {
			return null;
		}

		self::$_metadata_folders[ $metadata['name'] ] = $metadata_folder;

		$base_args_defaults = [
			'title'      => 'Module',
			'titles'     => 'Modules',
			'moduleIcon' => 'divi/module',
			'category'   => 'module',
			'attributes' => [],
		];

		$register_args = array_merge( $base_args_defaults, $metadata, $args );

		// Generate default, default printed style, and default settings attributes from module metadata.
		if ( isset( $register_args['render_callback'] ) ) {
			$render_callback = $register_args['render_callback'];

			// Modify module's render callback. Insert generated defaults attributes and ModuleElements instance.
			$register_args['render_callback'] = function ( $block_attributes, $content, WP_Block $block ) use ( $render_callback, $metadata ) {

				$default_printed_style_attrs = ModuleRegistration::get_default_attrs( $block->name, 'defaultPrintedStyle', $metadata );

				// Get preset attributes for this module.
				$item_preset      = GlobalPreset::get_selected_preset(
					[
						'moduleName'  => $block->name,
						'moduleAttrs' => $block_attributes ?? [],
					]
				);
				$preset_attrs_raw = $item_preset->get_data_attrs();
				$preset_attrs     = ModuleUtils::remove_matching_values( $preset_attrs_raw, $default_printed_style_attrs );

				$preset_render_attrs = $item_preset->get_data_render_attrs();

				// Remove preset attributes that are presents in block attributes.
				if ( $preset_attrs && $block_attributes ) {
					$preset_attrs = ModuleUtils::remove_matching_attrs( $preset_attrs, $block_attributes );
				}

				// Get default attributes for this module.
				$default_attributes = ModuleRegistration::get_default_attrs( $block->name, 'default', $metadata );

				// Replace default attributes with corresponding preset attributes.
				if ( $default_attributes && $preset_attrs ) {
					$default_attributes = ModuleUtils::replace_matching_attrs( $default_attributes, $preset_attrs );
				}

				// Get group presets and their render attributes.
				$group_presets = GlobalPreset::get_selected_group_presets(
					[
						'moduleName'  => $block->name,
						'moduleAttrs' => $block_attributes ?? [],
					]
				);

				$group_render_attrs = [];
				foreach ( $group_presets as $group_id => $group_preset_item ) {
					if ( $group_preset_item instanceof GlobalPresetItem ) {
						$group_render_attrs = array_replace_recursive(
							$group_render_attrs,
							$group_preset_item->get_data_render_attrs()
						);
					}
				}

				// Merge default attributes, preset attributes, group render attributes and user defined attributes. This ensures every module's attribute parameter
				// has considered default, preset, group render and user defined attributes on rendering component.
				$module_attrs_with_default = array_replace_recursive(
					$default_attributes,
					$preset_render_attrs,
					$group_render_attrs,
					$block_attributes
				);

				$filter_args = [
					'name'          => $block->name,
					'attrs'         => $module_attrs_with_default,
					'id'            => $block->parsed_block['id'],
					'storeInstance' => $block->parsed_block['storeInstance'],
				];

				$module_attrs = $module_attrs_with_default;

				if ( 'child-module' === $block->block_type->category ) {
					$only_block_attributes     = array_diff_multidimensional( $block_attributes, $default_attributes, true ); // WP merge default attributes with $block_attributes. But we need to only block attributes without default attributes.
					$parent                    = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
					$parent_attrs              = $parent->attrs;
					$parent_default_attributes = ModuleRegistration::get_default_attrs( $parent->blockName ); // phpcs:ignore ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block

					// Get preset attributes for parent module.
					$parent_item_preset  = GlobalPreset::get_selected_preset(
						[
							'moduleName'  => $parent->blockName,
							// phpcs:ignore ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
							'moduleAttrs' => $parent_attrs ?? [],
						]
					);
					$parent_preset_attrs = $parent_item_preset->get_data_attrs();

					$parent_group_presets = GlobalPreset::get_selected_group_presets(
						[
							'moduleName'  => $parent->blockName,
							// phpcs:ignore ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP use snakeCase in \WP_Block_Parser_Block
							'moduleAttrs' => $parent_attrs ?? [],
						]
					);

					$parent_group_child_attrs = [];
					foreach ( $parent_group_presets as $group_id => $group_preset_item ) {
						if ( $group_preset_item instanceof GlobalPresetItem ) {
							$parent_group_preset_attrs = $group_preset_item->get_data_attrs();
							$parent_group_child_attrs  = array_replace_recursive(
								$parent_group_child_attrs,
								$parent_group_preset_attrs['children'] ?? []
							);
						}
					}

					$module_attrs = array_replace_recursive(
						$parent_default_attributes['children'] ?? [],
						$default_attributes ?? [],
						$parent_preset_attrs['children'] ?? [],
						$parent_group_child_attrs,
						$preset_render_attrs,
						$group_render_attrs,
						$parent_attrs['children'] ?? [],
						$only_block_attributes
					);

					$filter_args['parentAttrs'] = $parent_attrs;
				}

				$module_attrs = apply_filters( 'divi_module_library_register_module_attrs', $module_attrs, $filter_args );

				// Check whether the current module is inside another sticky module or not. The FE
				// implementation is bit different than VB where we use store related function due
				// to we need access to the store instance to get all blocks. Meanwhile in FE, we
				// can directly check all blocks from the parsed block.
				$is_inside_sticky_module = StickyUtils::is_inside_sticky_module(
					$block->parsed_block['id'],
					BlockParserStore::get_all( $block->parsed_block['storeInstance'] )
				);

				// Check if the current module is nested. Only module with `nestable` property set to true will be
				// check for actual nested module condition for performance reason (most module isn't nestable, it is faster
				// to skip the check for them).
				$is_nested_module = self::is_nestable( $block->name ) && BlockParserStore::is_nested_module(
						$block->parsed_block['id'],
						$block->parsed_block['storeInstance']
					);

				// Create instance of ModuleElements and pass the instance as parameter for consistency and simplicity.
				$elements = new ModuleElements(
					[
						'id'                       => $block->parsed_block['id'],
						'is_custom_post_type'      => Conditions::is_custom_post_type(),
						'is_inside_sticky_module'  => $is_inside_sticky_module,
						'is_nested_module'         => $is_nested_module,
						'name'                     => $block->name,
						'moduleAttrs'              => $module_attrs,
						'defaultPrintedStyleAttrs' => $default_printed_style_attrs,
						'moduleMetadata'           => $block->block_type,
						'orderIndex'               => $block->parsed_block['orderIndex'],
						'storeInstance'            => $block->parsed_block['storeInstance'],
					]
				);

				/**
				 * Filters the module render block.
				 *
				 * Useful for disabling/enabling module render block functionality.
				 *
				 * @param boolean True to display the module render block, False to hide it.
				 *
				 * @since ??
				 *
				 */
				$is_displayable = apply_filters( 'divi_module_library_register_module_render_block', true, $block, $module_attrs );

				if ( ! $is_displayable ) {
					return '';
				}

				return call_user_func(
					$render_callback,
					$module_attrs,
					$content,
					$block,
					$elements,
					$default_printed_style_attrs
				);
			};
		}

		// TODO, create the equivalent of this TS implementation in PHP:
		// const conversionOutline = getModuleConversionOutline(config, metadata?.d4Shortcode);
		// const getModuleConversionOutline = (
		// 	config: Omit<ModuleLibrary.Module.RegisterDefinition, 'metadata'>,
		// 	d4Shortcode = '',
		//   ): ModuleConversionOutline => {
		// 	if (config?.conversionOutline) {
		// 	  return config.conversionOutline;
		// 	}

		// 	if (! d4Shortcode) {
		// 	  return {};
		// 	}

		// 	return getPossibleModuleConversionOutline(d4Shortcode);
		// };

		$conversion_outline_file = $metadata_folder . 'conversion-outline.json';

		// We need a conversion outline when we import d4 library items to d5 with presets.
		// We can't load the conversion file without a specific hook, for performance reasons.
		add_action(
			'et_pb_before_library_preset_import',
			function () use ( $metadata, $conversion_outline_file ) {
				ModuleRegistration::processe_conversion_outline( $metadata, $conversion_outline_file );
			}
		);

		// Let's not do all conversion processing here, if not needed,
		// because this will be a performance hit, as this code runs on every page load,
		// and were not going to be converting modules on every page load.
		// is admin or is PHP Unit test
		if ( ( is_admin() || defined( 'WP_TESTS_DOMAIN' ) ) && file_exists( $conversion_outline_file ) ) {
			self::processe_conversion_outline( $metadata, $conversion_outline_file );
		}

		// we need to roll our own version of register_block_type_from_metadata()
		// because inside of that, there is file_exists check, and also fetching and json decoding the file,
		// which we just did above, so lets save the time from doing that all again
		// additionally, they have the concept of a PHP array, so they can even skip the json_decode step
		// so lets do that as well, and we can skip the file_exists check, because we know it exists
		// for OUR core modules.
		// The old way: register_block_type( $metadata['name'], $register_args ).
		$registered_block_type = self::register_block_type_from_metadata( $metadata['name'], $metadata_file, $register_args );

		if ( false === $registered_block_type ) {
			return null;
		}

		return $registered_block_type;
	}
}