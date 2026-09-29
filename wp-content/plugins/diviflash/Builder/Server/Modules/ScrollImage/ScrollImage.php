<?php

/**
 * ScrollImage Module class.
 *
 * @package DIFL\Server\Modules\ScrollImage;
 */

namespace DIFL\Server\Modules\ScrollImage;

if ( !defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

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
 * Class ScrollImage
 *
 * @package DIFL\Server\Modules\ScrollImage
 */
class ScrollImage implements DependencyInterface
{
    use \DIFL\Server\Traits\StyleUtil;

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/scrollimage' )->customCssFields;
	}

    public static function font_style($font) {
        return static::get_font_style($font);
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


		$getModuleSettings = function ( $key ) use ( $attrs ) {
			$searchObject = function ( $obj, $targetKey ) use ( &$searchObject ) {
				if ( !$obj || ( !is_array( $obj ) && !is_object( $obj ) ) ) {
					return null;
				}

				if ( ( is_array( $obj ) && array_key_exists( $targetKey, $obj ) ) ||
					( is_object( $obj ) && property_exists( $obj, $targetKey ) )
				) {
					return is_array( $obj ) ? $obj[ $targetKey ] : $obj->$targetKey;
				}

				foreach ( $obj as $prop => $value ) {
					if ( $prop !== "decoration" ) {
						$result = $searchObject( $value, $targetKey );
						if ( $result !== null ) {
							return $result;
						}
					}

				}

				return null;
			};

			return $searchObject( $attrs, $key );
		};
		$translate_values = [
			'top_left'      =>
				"top: 0px !important; left: 0 !important; transform: none !important;",
			'top_center'    =>
				"top: 0px !important; left: 50% !important; transform: translateX(-50%) !important;",
			'top_right'     =>
				"top:0px !important; right: 0 !important; transform: translate(0%) !important;",
			'center_left'   =>
				"left: 0px !important; top: 50% !important; transform: translateY(-50%) !important;",
			'center_center' =>
				"left: 50% !important; top:50% !important; transform: translate(-50%, -50%) !important;",
			'center_right'  =>
				"right: 0 !important; top: 50% !important; transform: translate(0%, -50%) !important;",
			'bottom_left'   =>
				"left:0px !important; top: 100% !important; transform: translateY(-100%) !important;",
			'bottom_center' =>
				"left: 50% !important; top:100% !important; transform: translate(-50% ,-100%) !important;",
			'bottom_right'  =>
				"right: 0 !important; top: 100% !important; transform: translate(0% ,-100%) !important;",
		];

		$styles[] = $elements->style( [
			'attrName'   => 'scroll',
			'styleProps' => [
				'selector'         => $args['orderClass'] . ' .df_scroll_image_holder:not(.df_device_slider),' . $args['orderClass'] . ' .df_scroll_image_holder .scroll_image_section',
				'selectorFunction' => function ( $props ) use ( $args ) {
					if ( $props['state'] === 'hover' ) {
						return $args['orderClass'] . ' .df_scroll_image_holder:not(.df_device_slider):hover,' . $args['orderClass'] . ' .df_scroll_image_holder:hover .scroll_image_section';
					}

					return $props['setector'];
				},
			],
		] );

		$styles[] = $elements->style( [
			'attrName' => 'badge',
		] );

		$styles[] = $elements->style( [
			'attrName' => 'icon',
		] );

		$styles[] = $elements->style( [
			'attrName' => 'caption',
		] );

		$styles[] = $elements->style( [
			'attrName' => 'badge_icon_spacing',
		] );

		$styles[] = CommonStyle::style( [
			'selector' => $args['orderClass'] . ' .df_text_reveal_main_container',
			'attr'     => $attrs['settings__reveal_color']['decoration'] ?? null,
			'property' => '--secondary-reveal-color',
		] );

		$styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .df_scroll_image_container .df_scroll_image_holder .df_scroll_image',
			'attr'                => $attrs['scroll']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) {
				$duration = $props['attrValue']['scroll_transition_duration'] ?? '';
				$curve = $props['attrValue']['scroll_transition_curve'] ?? '';
				$delay = $props['attrValue']['scroll_transition_delay'] ?? '';

				return "transition-duration: {$duration};
				transition-delay:{$delay};
				transition-timing-function:{$curve};
						";
			},
		] );

		$styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .df_link_area',
			'attr'                => $attrs['icon']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) use ( $translate_values ) {

				if ( !empty( $props['attrValue']['link_position'] ) ) {
					return $translate_values[ $props['attrValue']['link_position'] ];
				}

				return '';
			},
		] );
		if ( ( $getModuleSettings( 'use_image_as_icon' ) ?? "off" ) === "on" ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .df_link_area',
				'attr'                => $attrs['icon']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					if ( !empty( $props['attrValue']['image_container_width'] ) ) {
						return "width:" . $props['attrValue']['image_container_width'] ?? "50px" . " !important";
					} else {
						return "width:50px !important";
					}
				},
			] );
		}
		$styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .et-pb-icon.df-sl-link-icon',
			'selectorFunction' => function ( $props ) use ( $args ) {
				if ( $props['state'] === 'hover' ) {
					return $args['orderClass'] . ' .df_link_area:hover .et-pb-icon.df-sl-link-icon';
				}

				return $props['selector'];
			},
			'attr'                => $attrs['icon']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) {
				$color = !empty( $props['attrValue']['link_icon_color'] ) ? $props['attrValue']['link_icon_color'] : '';
				$size = !empty( $props['attrValue']['icon_size'] ) ? $props['attrValue']['icon_size'] : '';

				$style = '';
				if ( $color ) {
					$style .= "color: {$color};";
				}
				if ( $size ) {
					$style .= "font-size: {$size};";
				}

				return $style;
			},
		] );

        $styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .et-pb-icon.df-sl-link-icon',
			'selectorFunction' => function ( $props ) use ( $args ) {
				if ( $props['state'] === 'hover' ) {
					return $args['orderClass'] . ' .df_link_area:hover .et-pb-icon.df-sl-link-icon';
				}

				return $props['selector'];
			},
			'attr'                => $attrs['icon']['innerContent'] ?? null,
			'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['link_icon'] ?? '' )
        ] );

		$styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .df_scroll_image_badge .badge_icon.et-pb-icon',
			'attr'                => $attrs['badge']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) {
				$color = $props['attrValue']['badge_icon_color'] ?? '';
				$size = $props['attrValue']['badge_icon_size'] ?? '';

				return "color: {$color}; font-size: {$size};";
			},
		] );

        $styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .df_scroll_image_badge .badge_icon.et-pb-icon',
			'attr'                => $attrs['badge']['innerContent'] ?? null,
			'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['badge_icon'] ?? '' ),
		] );

		$styles[] = CommonStyle::style( [
			'selector'            => $args['orderClass'] . ' .df_scroll_image_badge',
			'attr'                => $attrs['badge']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) use ( $translate_values ) {
				$position = !empty( $props['attrValue']['badge_position'] ) ? $translate_values[ $props['attrValue']['badge_position'] ] : '';

				return "{$position};";
			},
		] );
		if ( $getModuleSettings( "enable_badge" ) === "on" && ( ($getModuleSettings( 'badge_position' )??"top_center") === "top_center" || ($getModuleSettings( 'badge_position' )??"top_center") === "center_center" || ($getModuleSettings( 'badge_position' )??"top_center") === "bottom_center" ) ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .df_scroll_image_badge',
				'attr'                => $attrs['badge']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) use ( $translate_values ) {
					$width = $props['attrValue']['badge_container_width'] ?? '25%';

					return " width: {$width};";
				},
			] );
		}
		if ( empty( $getModuleSettings( 'enable_frame' ) ) || $getModuleSettings( 'enable_frame' ) === "off" ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .df_scroll_image_container .df_scroll_image_holder .df_scroll_image',
				'attr'                => $attrs['scroll']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) use ( $translate_values ) {
					$image_min_height = !empty( $props['attrValue']['image_min_height'] ) ? $props['attrValue']['image_min_height'] : '450px';

					return "min-height:" . $image_min_height . "!important;";
				},
			] );
		}

		if ( $getModuleSettings( 'overlay' ) === 'on' ) {
			$styles[] = CommonStyle::style( [
				'selector'            => $args['orderClass'] . ' .df-overlay',
				'attr'                => $attrs['overlay_settings']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					$direction = $props['attrValue']['overlay_direction'] ?? 'to bottom';
					$primary = $props['attrValue']['overlay_primary'] ?? "#00B4DB";
					$secondary = $props['attrValue']['overlay_secondary'] ?? "#0083B0";

					return "background-image: linear-gradient({$direction}, {$primary} 0, {$secondary} 100%);";
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

					...$styles
				],
			]
		);
	}

	public static function markup( $attrs, $content, $elements )
	{

		$getModuleSettings = function ( $key ) use ( $attrs ) {
			$searchObject = function ( $obj, $targetKey ) use ( &$searchObject ) {
				if ( !$obj || ( !is_array( $obj ) && !is_object( $obj ) ) ) {
					return null;
				}

				if ( ( is_array( $obj ) && array_key_exists( $targetKey, $obj ) ) ||
					( is_object( $obj ) && property_exists( $obj, $targetKey ) )
				) {
					return is_array( $obj ) ? $obj[ $targetKey ] : $obj->$targetKey;
				}

				foreach ( $obj as $prop => $value ) {
					if ( $prop !== 'decoration' ) {
						$result = $searchObject( $value, $targetKey );
						if ( $result !== null ) {
							return $result;
						}
					}
				}

				return null;
			};

			return $searchObject( $attrs, $key );
		};

		$df_render_badge_icon = function () use ( $getModuleSettings ) {
			$badge_icon_enable = $getModuleSettings( 'badge_icon_enable' );
			if ( $badge_icon_enable !== null && $badge_icon_enable === 'on' ) {
				$badge_icon = $getModuleSettings( 'badge_icon' );

				return sprintf(
					'<span class="et-pb-icon badge_icon">%1$s</span>',
					$badge_icon !== null && $badge_icon !== '' ?
						esc_attr( Utils::process_font_icon( $badge_icon ) ) : '5'
				);
			}

			return '';
		};
		$df_render_image_icon = function () use ( $getModuleSettings ) {
			$enable_icon = $getModuleSettings( 'enable_icon' );
			$use_image_as_icon = $getModuleSettings( 'use_image_as_icon' );

			if ( $enable_icon !== null && $enable_icon === 'on' && 'on' !== $use_image_as_icon ) {
				$link_icon = $getModuleSettings( 'link_icon' );

				return sprintf(
					'<span class="et-pb-icon df-sl-link-icon">%1$s</span>',
					$link_icon !== null && $link_icon !== '' ?
						esc_attr( Utils::process_font_icon( $link_icon ) ) : '5'
				);
			} else {
				$image = $getModuleSettings( 'image' );
				if ( $image !== null && $image !== '' && "on" === $use_image_as_icon ) {
					$src = 'src';
					$alt_text = $getModuleSettings( 'alt_text' );
					$image_alt = $alt_text !== '' ? $alt_text : df_image_alt_by_url( $image );

					return sprintf(
						'<img class="df_sm_image_icon" %3$s="%1$s" alt="%2$s" />',
						$image,
						$image_alt,
						$src
					);
				}
			}

			return ''; // Added explicit return for cases when no conditions are met
		};

		wp_enqueue_script( 'lightgallery-script' );
		wp_enqueue_script( 'df-scroll-image' );

		$scroll_image_value = $getModuleSettings( 'scroll_image' );
		$scroll_image = ( $scroll_image_value !== null && $scroll_image_value !== '' ) ? $scroll_image_value : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTA4MCIgaGVpZ2h0PSI1NDAiIHZpZXdCb3g9IjAgMCAxMDgwIDU0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPHBhdGggZmlsbD0iI0VCRUJFQiIgZD0iTTAgMGgxMDgwdjU0MEgweiIvPgogICAgICAgIDxwYXRoIGQ9Ik00NDUuNjQ5IDU0MGgtOTguOTk1TDE0NC42NDkgMzM3Ljk5NSAwIDQ4Mi42NDR2LTk4Ljk5NWwxMTYuMzY1LTExNi4zNjVjMTUuNjItMTUuNjIgNDAuOTQ3LTE1LjYyIDU2LjU2OCAwTDQ0NS42NSA1NDB6IiBmaWxsLW9wYWNpdHk9Ii4xIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgICAgICA8Y2lyY2xlIGZpbGwtb3BhY2l0eT0iLjA1IiBmaWxsPSIjMDAwIiBjeD0iMzMxIiBjeT0iMTQ4IiByPSI3MCIvPgogICAgICAgIDxwYXRoIGQ9Ik0xMDgwIDM3OXYxMTMuMTM3TDcyOC4xNjIgMTQwLjMgMzI4LjQ2MiA1NDBIMjE1LjMyNEw2OTkuODc4IDU1LjQ0NmMxNS42Mi0xNS42MiA0MC45NDgtMTUuNjIgNTYuNTY4IDBMMTA4MCAzNzl6IiBmaWxsLW9wYWNpdHk9Ii4yIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgIDwvZz4KPC9zdmc+Cg==';
		$image_scroll_type_value = $getModuleSettings( 'image_scroll_type' );
		$image_scroll_type = $image_scroll_type_value !== null ? $image_scroll_type_value : "top_bottom";

		$enable_caption = $getModuleSettings( 'enable_caption' );
		$caption_text_value = $getModuleSettings( 'caption_text' );
		$caption_text = $enable_caption === 'on' && $caption_text_value !== null && $caption_text_value !== '' ?


			sprintf(
				'<figcaption class="df_scroll_image_caption df_caption_text">
                        %1$s
                    </figcaption>',
				preg_replace( "/<p[^>]*>(?:\s|&nbsp;)*<\/p>/", '', $caption_text_value )
			) : '';

		$badge_position_value = ($getModuleSettings( 'badge_position' )??"top_center");
		$badge_position = $badge_position_value !== null ? $badge_position_value : "top_center";
		$badge_icon_placement = $getModuleSettings( 'badge_icon_placement' );
		$enable_badge = $getModuleSettings( 'enable_badge' );
		$badge_text_value = $getModuleSettings( 'badge_text' );
		$badge_text = $enable_badge === 'on' && $badge_text_value !== null && $badge_text_value !== '' ?
			sprintf(
				'<div class="df_scroll_image_badge df_position_%2$s %5$s %6$s">
                        %4$s
                        <span class="df_badge">%1$s</span>
                        %3$s
                    </div>',
				wp_kses_post( $badge_text_value ),
				$badge_position,
				$badge_icon_placement !== 'left' ? $df_render_badge_icon() : '',
				$badge_icon_placement === 'left' ? $df_render_badge_icon() : '',
				$getModuleSettings( 'show_badge_on_hover' ) === 'on' ? 'show_badge_on_hover' : '',
				$getModuleSettings( 'hide_badge_on_hover' ) === 'on' ? 'hide_badge_on_hover' : ''
			)
			: '';

		$link_position_value = $getModuleSettings( 'link_position' );
		$link_position = $link_position_value !== null ? $link_position_value : "center_center";

		$enable_custom_link = $getModuleSettings( 'enable_custom_link' );
		$link_url_value = $getModuleSettings( 'link_url' );
		$link_url = $enable_custom_link === 'on' && $link_url_value !== null && $link_url_value !== '' ? $link_url_value : "";

		$use_light_box = $getModuleSettings( 'use_light_box' );
		$use_different_lightbox_image = $getModuleSettings( 'use_different_lightbox_image' );
		$different_lightbox_image = $getModuleSettings( 'different_lightbox_image' );
		$lightbox_image_link = $use_light_box === 'on' && $use_different_lightbox_image === 'on' && $different_lightbox_image !== null ?
			$different_lightbox_image
			:
			$scroll_image;

		$custom_url = $enable_custom_link === 'on' && $link_url !== '' ?
			sprintf( 'data-url="%1$s"', $link_url )
			: '';

		$use_image_as_icon = $getModuleSettings( 'use_image_as_icon' );
		$image = $getModuleSettings( 'image' );
		$link_main = 'on' === $use_image_as_icon && $image === '' ?

			''
			:
			sprintf(
				'<div class="df_link_area df_position_small df_position_%2$s %3$s%4$s%5$s">
                    <span class="df_link_icon df_scroll_image_lightbox_item">
                        %1$s
                    </span>
                </div>',
				$df_render_image_icon(),
				$link_position,
				$getModuleSettings( 'show_on_hover' ) === 'on' ? 'show_on_hover' : '',
				$getModuleSettings( 'hide_on_hover' ) === 'on' ? 'hide_on_hover' : '',
				$getModuleSettings( 'icon_motion' ) === 'on' ? ( $image_scroll_type === 'top_bottom' || $image_scroll_type === 'bottom_top' || $image_scroll_type === 'off' ) ? ' vertical_motion' : 'horizontal_motion' : ''
			);

		$enable_icon = $getModuleSettings( 'enable_icon' );
		$link_html = $enable_icon !== null && $enable_icon === 'on' ?
			sprintf(
				' %1$s ',
				$link_main
			)
			:
			'';

		$overlay = $getModuleSettings( 'overlay' );
		$main_image = sprintf(
			'%3$s %4$s<div class="df_scroll_image df_scroll_image_%2$s" style="background-image: url(%1$s);"></div>
                        ',
			$scroll_image,
			$image_scroll_type,
			$overlay === 'on' ? '<span class="df-overlay"></span>' : '',
			$link_html,
			$lightbox_image_link
		);

		$frame_type_value = $getModuleSettings( 'frame_type' );
		$frame_type = $frame_type_value !== null ? $frame_type_value : 'desktop';

		$frame_url = DIFL_PUBLIC_DIR . 'img/devices/' . $frame_type . '.svg';
		$frame_html = sprintf( '<img class="frame_image" src="%1$s" alt="%2$s">', $frame_url, $frame_type );

		$enable_frame = $getModuleSettings( 'enable_frame' );
		$image_html = $enable_frame === 'on' ?
			sprintf(
				'<div class="df_device_slider_device">
                        %1$s
                        <div class="df_responsive_width scroll_image_section">
                            %2$s
                        </div>
                    </div>
                    ',
				$frame_html,
				$main_image
			)

			: $main_image;

		$data = [
			'use_light_box'   => ( ( ( $enable_custom_link ?? "off" ) === "off" ) && $use_light_box !== null ) ? $use_light_box : 'off',
			'link_url'        => $use_light_box === 'off' && $link_url_value !== null && $link_url_value !== '' ? $link_url_value : '',
			'link_url_target' => ( $link_url_target = $getModuleSettings( 'link_url_target' ) ) !== null ? $link_url_target : 'same_window',
			'frame'           => $enable_frame !== null ? $enable_frame : 'off',
			'frame_type'      => $frame_type_value !== null ? $frame_type_value : 'desktop'
		];
		$frame_class = $enable_frame === 'on' ?
			sprintf( 'df_device_slider df_device_slider_%1$s', $frame_type ) : '';
		$link_lightbox_class = $use_light_box === 'on' || $enable_custom_link === 'on' ? ' link_lightbox' : '';

		return sprintf(
			'<div class="df_scroll_image_container" data-settings=\'%4$s\'>
                            <div class="df_scroll_image_wrapper%9$s" data-src="%8$s">
                                <div class="df_scroll_image_holder %5$s" %7$s>
                                        %1$s
                                        %2$s
                                </div>
                                %3$s
                                %6$s
                            </div>
                        </div>',
			$image_html,
			$enable_frame === 'on' ? $caption_text : '',
			$badge_text,
			wp_json_encode( $data ),
			$frame_class,
			$enable_frame !== 'on' ? $caption_text : '',
			$custom_url,
			$lightbox_image_link,
			$link_lightbox_class
		);
	}

	public static function render_callback( $attrs, $content, $block, $elements )
	{
		$content = self::markup( $attrs, $content, $elements );

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
				'classnamesFunction'  => [ ScrollImage::class, 'module_classnames' ],
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => [ ScrollImage::class, 'module_styles' ],
				'scriptDataComponent' => [ ScrollImage::class, 'module_script_data' ],
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
		$module_json_folder_path = DIFL_MODULES_JSON_PATH . '/scroll-image';

		add_action(
			'init',
			function () use ( $module_json_folder_path ) {
				ModuleRegistration::register_module(
					$module_json_folder_path,
					[
						'render_callback' => [ ScrollImage::class, 'render_callback' ],
					]
				);
			}
		);
	}
}
