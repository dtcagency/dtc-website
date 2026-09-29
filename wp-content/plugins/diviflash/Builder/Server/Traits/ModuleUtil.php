<?php

namespace DIFL\Server\Traits;

use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils as ETModuleUtils;

trait ModuleUtil {
	use ParentProps;
	use StyleUtil;
    use ProcessAttrWithPreset;

	protected $order_class;

	protected $elements;

	protected $attributes;

	protected $attrs;

	protected $parent_attributes = [];

	protected $module_slug;

	public function load(): void {
		$module_json_folder_path = DIFL_MODULES_JSON_PATH . $this->config_file . '/';
		add_action(
			'init',
			function () use ( $module_json_folder_path ) {
				ModuleRegistration::register_module(
					$module_json_folder_path,
					[
						'render_callback' => [ $this, 'render_callback' ],
					]
				);
			}
		);
	}

	public function module_script_data( $args ) {
		$this->elements->script_data(
			[
				'attrName' => 'module',
			]
		);
	}

	public function module_classnames( array $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		if ( ! empty( $this->module_classnames ) ) {
			if ( is_array( $this->module_classnames ) ) {
				foreach ( $this->module_classnames as $classname ) {
					$classnames_instance->add( $classname );
				}
			} else {
				$classnames_instance->add( $this->module_classnames );
			}
		}

		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'link' => $args['attrs']['module']['advanced']['link'] ?? [],
						]
					),
				]
			)
		);
	}

	public function render_callback( array $attrs, string $content, \WP_Block $block, ModuleElements $elements, array $default_printed_style_attrs ): string {

		$parent      = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$this->attrs = $attrs;
		$this->attrs = self::processAttrWithPreset($attrs, $elements->name);
		$attrs = self::processAttrWithPreset($attrs, $elements->name);

		if ( empty( $this->parent_attributes ) ) {
			$reflection = new \ReflectionObject( $block );
			$property   = $reflection->getProperty( 'registry' );
			$property->setAccessible( true );
			$instances   = $property->getValue( $block );
			$registered  = $instances->get_all_registered();
			$parent_block = $registered[ $parent->blockName ] ?? null;
			$this->parent_attributes = ( $parent_block && isset( $parent_block->attributes ) )
				? $parent_block->attributes
				: [];
		}

		$this->elements   = $elements;
		$this->attributes = $elements->module_metadata->attributes;
		$this->set_props( $attrs );

		if ( property_exists( $parent, 'attrs' ) ) {
			$this->set_props_parent( $parent->attrs );
		}

		$this->order_class = ETModuleUtils::get_module_order_class_name( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );


		return Module::render(
			[
				'orderIndex'    => $block->parsed_block['orderIndex'],
				'storeInstance' => $block->parsed_block['storeInstance'],

				'attrs'               => $attrs,
				'id'                  => $block->parsed_block['id'],
				'elements'            => $elements,
				'name'                => $block->block_type->name,
				'classnamesFunction'  => [ $this, 'module_classnames' ],
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => [ $this, 'module_styles' ],
				'scriptDataComponent' => [ $this, 'module_script_data' ],
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $elements->style_components(
						[
							'attrName' => 'module',
						]
					) . $this->render( $attrs, $content, $block, $elements ),
			]
		);
	}

}
