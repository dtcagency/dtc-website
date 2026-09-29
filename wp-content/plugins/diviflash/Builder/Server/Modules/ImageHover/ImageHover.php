<?php
/**
 * ImageHover Module class.
 *
 * @package DIFL\Server\Modules\ImageHover;
 */

namespace DIFL\Server\Modules\ImageHover;

if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use DIFL\Server\Traits\StyleUtil;
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
 * Class ImageHover
 *
 * @package DIFL\Server\Modules\ImageHover
 */
class ImageHover implements DependencyInterface {

	use StyleUtil;

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/imagehover' )->customCssFields;
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
		$styles = [
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
		];
		$styles[] = $elements->style( [
			'attrName' => 'icon',
		] );

		$styles[] = $elements->style( [
			'attrName' => 'icon_wrapper',
		] );

		$styles[] = $elements->style( [
			'attrName' => 'title',
		] );
		$styles[] = $elements->style( [
			'attrName' => 'child_filters_target',
		] );

		if ( ( $attrs['overlay']['innerContent']['desktop']['value']['useOverlay'] ?? '' ) !== 'on' ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .c4-izmir',
				'attr'                => $attrs['overlay']['innerContent'] ?? null,
				'declarationFunction' => fn( $props ) => '--image-opacity: 1;',
			] );
		}

		if ( ( $attrs['overlay']['innerContent']['desktop']['value']['useOverlay'] ?? '' ) === 'on' ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'],
				'attr'                => $attrs['overlay']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					$styles = '';

					if ( !empty( $props['attrValue']['primary_color'] ) ) {
						$styles .= "--overlay-color1: " . $props['attrValue']['primary_color'] . ";";
					}

					if ( !empty( $props['attrValue']['secondary_color'] ) ) {
						$styles .= "--overlay-color2: " . $props['attrValue']['secondary_color'] . ";";
					}

					if ( !empty( $props['attrValue']['direction'] ) ) {
						$styles .= "--overlay-direction: " . $props['attrValue']['direction'] . ";";
					}

					return $styles;
				},
			] );
		}

		if ( ( $attrs['border_anim']['innerContent']['desktop']['value']['enable'] ?? '' ) === 'on' ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .c4-izmir',
				'attr'                => $attrs['border_anim']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					return '--border-color:' . ( $props['attrValue']['color'] ?? '#ffffff' ) . ';' .
						'--border-width:' . ( $props['attrValue']['width'] ?? '3px' ) . ';' .
						'--border-margin:' . ( $props['attrValue']['margin'] ?? '15px' ) . ';';
				},
			] );
		}

		$styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .c4-izmir',
			'attr'                => $attrs['anm_content_padding']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) {
				return '--padding:' . ( $props['attrValue'] ?? '1em' ) . ';';
			},
		] );

		if ( ( $attrs['image']['innerContent']['desktop']['value']['scale_type'] ?? '' ) === 'c4-image-rotate-left' ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .c4-image-rotate-left:hover img,' . $args['orderClass'] . ' :focus.c4-image-rotate-left img',
				'attr'                => $attrs['image']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					return 'transform: scale(' . ( $props['attrValue']['scale_hover'] ?? '1.3' ) . ') rotate(-15deg);';
				},
			] );
		}

		if ( ( $attrs['image']['innerContent']['desktop']['value']['scale_type'] ?? '' ) === 'c4-image-rotate-right' ) {

			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .c4-image-rotate-right:hover img,' . $args['orderClass'] . ' :focus.c4-image-rotate-right img',
				'attr'                => $attrs['image']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					return 'transform: scale(' . ( $props['attrValue']['scale_hover'] ?? '1.3' ) . ') rotate(15deg);';
				},
			] );
		}

		$styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .et-pb-icon',
			'selectorFunction'    => function ( $props ) use ( $args ) {
				if ( $props['state'] === 'hover' ) {
					return $args['orderClass'] . ':hover .et-pb-icon';
				}
				return $props['selector'];
			},
			'attr'                => $attrs['icon']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) {
				$color = $props['attrValue']['color'] ?? null;
				$size  = $props['attrValue']['size'] ?? null;
				$icon  = $props['attrValue']['icon'] ?? null;
				$out   = self::get_font_style( $icon );
				if ( $color ) {
					$out .= 'color:' . $color . ';';
				}
				if ( $size ) {
					$out .= 'font-size:' . $size . ';';
				}
				return $out ?: null;
			},
		] );

		if ( ( $attrs['title']['innerContent']['desktop']['value']['always_show_title'] ?? '' ) !== 'on' ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .ihb_title_wrap, ' . $args['orderClass'] . ' .ihb_title_wrap > *',
				'attr'                => $attrs['title']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					return 'transition-delay: ' . ( $props['attrValue']['title_anm_delay'] ?? 0 ) . 'ms !important;';
				},
			] );
		}

		if ( ( $attrs['icon']['innerContent']['desktop']['value']['always_show_icon'] ?? '' ) !== 'on' ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .ihb_icon_wrap, ' . $args['orderClass'] . ' .ihb_icon_wrap > *',
				'attr'                => $attrs['icon']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					return 'transition-delay: ' . ( $props['attrValue']['icon_anm_delay'] ?? 0 ) . 'ms !important;';
				},
			] );
		}

		$styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ) );

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => $styles
			]
		);
	}

	public static function render_callback( $attrs, $content, $block, $elements )
	{


		wp_enqueue_script( 'df-image-hover' );

		$title_value = $attrs['title']['innerContent']['desktop']['value'] ?? [];
		$icon_value = $attrs['icon']['innerContent']['desktop']['value'] ?? [];
		$image_value = $attrs['image']['innerContent']['desktop']['value'] ?? [];
		$border_anim_value = $attrs['border_anim']['innerContent']['desktop']['value'] ?? [];

		$title_reveal_class = ( $title_value['always_show_title'] ?? '' ) === 'on'
			? 'always-show-title c4-fade-up'
			: ( $title_value['title_reveal'] ?? 'c4-fade-up' );

		$icon_reveal_class = ( $icon_value['always_show_icon'] ?? '' ) === 'on'
			? 'always-show-title c4-fade-up'
			: ( $icon_value['icon_reveal'] ?? 'c4-fade-up' );

		$TitleTag = $title_value['tag'] ?? 'h3';

		$image = !empty( $image_value['src'] )
			? '<img src="' . esc_attr( $image_value['src'] ) . '" alt="' . esc_attr( $image_value['alt'] ?? '' ) . '" />'
			: '';

		$title = !empty( $image_value['titleText'] )
			? '<div class="ihb_title_wrap ' . esc_attr( $title_reveal_class ) . '"><' . $TitleTag . ' class="df_ihb_title">' . esc_html( $image_value['titleText'] ) . '</' . $TitleTag . '></div>'
			: '';

		$icon = '';

		$icon = isset( $icon_value['enable'] ) && $icon_value['enable'] === 'on' ?
			sprintf( '<div class="ihb_icon_wrap %2$s"><span class="et-pb-icon">%1$s</span></div>',
				isset( $icon_value['icon'] ) && $icon_value['icon'] !== '' ?
					esc_html( Utils::process_font_icon( $icon_value['icon'] ) ?? '5' ) : '5',
				esc_attr( $icon_reveal_class )
			) : '';


		$border_anm_style = ( $border_anim_value['enable'] ?? '' ) === 'on'
			? ( $border_anim_value['anm_style'] ?? 'c4-border-fade' )
			: '';

		$container_class = 'df_ihb_container ' . ( $attrs['image']['innerContent']['desktop']['value']['scale_type'] ?? 'no-image-scale' );
		$figure_class = 'c4-izmir df_ihb_image_wrap ' . ( $border_anm_style ?? '' );
		$content_position_class = $attrs['content_position']['innerContent']['desktop']['value'] ?? 'c4-layout-top-left';
		$use_overlay = ( $attrs['overlay']['innerContent']['desktop']['value']['useOverlay'] ?? '' ) === 'on';

		$overlay_html = $use_overlay ? '<span class="df-overlay"></span>' : '';

		$content = sprintf(
			'<div class="%s">
        <figure class="%s">
            %s
            %s
            <figcaption class="df_ihb_content %s">
                %s
                %s
            </figcaption>
        </figure>
    </div>',
			esc_attr( $container_class ),
			esc_attr( $figure_class ),
			$overlay_html,
			$image,
			esc_attr( $content_position_class ),
			$icon,
			$title
		);


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
				'classnamesFunction'  => [ ImageHover::class, 'module_classnames' ],
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => [ ImageHover::class, 'module_styles' ],
				'scriptDataComponent' => [ ImageHover::class, 'module_script_data' ],
				'parentAttrs'         => $parent->attrs ?? [],
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $elements->style_components(
						[
							'attrName' => 'module',
						]
					) . $content,
			]
		);
	}

	public function load()
	{
		$module_json_folder_path = DIFL_MODULES_JSON_PATH . '/image-hover';

		add_action(
			'init',
			function () use ( $module_json_folder_path ) {
				ModuleRegistration::register_module(
					$module_json_folder_path,
					[
						'render_callback' => [ ImageHover::class, 'render_callback' ],
					]
				);
			}
		);
	}
}