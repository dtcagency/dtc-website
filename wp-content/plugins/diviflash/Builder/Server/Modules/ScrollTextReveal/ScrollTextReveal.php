<?php
/**
 * ScrollTextReveal Module class.
 *
 * @package DIVIFLASH5\Modules\ScrollTextReveal;
 */

namespace DIFL\Server\Modules\ScrollTextReveal;

if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;


/**
 * Class ScrollTextReveal
 *
 * @package DIVIFLASH5\Modules\ScrollTextReveal
 */
class ScrollTextReveal implements DependencyInterface
{

	use GetPropsTrait;
	use UnifiedStyleRenderer;

	protected $styles;

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/text-reveal' )->customCssFields;
	}

	public static function module_classnames( $args )
	{
		$classnames_instance = $args['classnamesInstance'];
		$attrs = $args['attrs'];

		// Text Options.
		$classnames_instance->add(
			TextClassnames::text_options_classnames(
				$attrs['module']['advanced']['text'] ?? [],
				[
					'orientation' => false,
				]
			),
			true
		);

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				[
					'attrs' => $attrs['module']['decoration'] ?? [],
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? [],
						[
							'link' => $attrs['module']['advanced']['link'] ?? [],
						]
					),
				]
			)
		);
	}

	public static function module_script_data( $args )
	{
		$elements = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);
	}

	public static function module_styles( $args )
	{
		$attrs = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];
		$order_class = $args['orderClass'];

		$instance = new static();
		$instance->initializeProps( $elements, $attrs );
		$instance->styles = $instance->renderUnifiedStyles( $elements, $attrs, $order_class, 'difl/text-reveal' );

		$instance->styles = array_merge( $instance->styles, \difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ) );

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					// Element: Module.
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'disabledOn' => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
							],
						]
					),
					CssStyle::style(
						[
							'selector'  => $args['orderClass'],
							'attr'      => $attrs['css'] ?? [],
							'cssFields' => self::custom_css(),
						]
					),

					...$instance->styles,
				],
			]
		);
	}

	public function render()
	{

		$content = $this->props['settings__content'];
		$settings__trigger_type = $this->props['settings__trigger_type'];
		$settings__split_content = $this->props['settings__split_content'];
		$settings__reveal_by = $this->props['settings__reveal_by'];
		$settings__reveal_color = $this->props['settings__reveal_color'] ? $this->props['settings__reveal_color'] : '';
		$settings__reveal_duration = $this->props['settings__reveal_duration'] ? $this->props['settings__reveal_duration'] : '';
		$settings__reveal_delay = $this->props['settings__reveal_delay'] ? $this->props['settings__reveal_delay'] : '';
		$settings__reveal_initial_opacity = $this->props['settings__reveal_initial_opacity'] ? $this->props['settings__reveal_initial_opacity'] : '';
		$settings__reveal_viewport_offset_value_top = $this->props['settings__reveal_viewport_offset_value_top'] ? $this->props['settings__reveal_viewport_offset_value_top'] : '';
		$settings__reveal_viewport_offset_value_bottom = $this->props['settings__reveal_viewport_offset_value_bottom'] ? $this->props['settings__reveal_viewport_offset_value_bottom'] : '';

		$args = [
			'settings__reveal_color'                        => $settings__reveal_color,
			'settings__reveal_duration'                     => $settings__reveal_duration,
			'settings__reveal_delay'                        => $settings__reveal_delay,
			'settings__reveal_initial_opacity'              => $settings__reveal_initial_opacity,
			'settings__reveal_viewport_offset_value_top'    => $settings__reveal_viewport_offset_value_top,
			'settings__reveal_viewport_offset_value_bottom' => $settings__reveal_viewport_offset_value_bottom,
		];

		return sprintf(
			'<div data-settings="%5$s" class="df_text_reveal_main_container %3$s  %2$s %4$s" >%1$s</div>',
			$content,
			esc_html( $settings__trigger_type ),
			esc_html( $settings__split_content ),
			esc_html( $settings__reveal_by ),
			esc_attr( wp_json_encode( $args ) )
		);
	}

	public static function render_callback( $attrs, $content, $block, $elements )
	{

		$instance = new static();
		$instance->initializeProps( $elements, $attrs );
		wp_enqueue_script( 'df-text-reveal' );

		return Module::render(
			[
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'moduleClassName'     => '',
				'name'                => $block->block_type->name,
				'classnamesFunction'  => [ ScrollTextReveal::class, 'module_classnames' ],
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => [ ScrollTextReveal::class, 'module_styles' ],
				'scriptDataComponent' => [ ScrollTextReveal::class, 'module_script_data' ],
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $elements->style_components(
						[
							'attrName' => 'module',
						]
					) . $instance->render(),
			]
		);
	}

	public function load()
	{
		$module_json_folder_path = DIFL_MODULES_JSON_PATH . '/scroll-text-reveal';

		add_action(
			'init',
			function () use ( $module_json_folder_path ) {
				ModuleRegistration::register_module(
					$module_json_folder_path,
					[
						'render_callback' => [ ScrollTextReveal::class, 'render_callback' ],
					]
				);
			}
		);
	}

}