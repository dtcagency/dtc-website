<?php

namespace DIFL\Server\Modules\PricingTableItem;


use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Server\Traits\ParentProps;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class PricingTableItem implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'PricingTableItem';

	protected $module_classnames = 'et_pb_module';

	protected $attributes;

	private static $processed_field = [];

	public function set_margin_padding_styles() {
		return '';
	}

	public function generate_styles() {
		return '';
	}

	public function df_process_bg() {
		return '';
	}

	public function init() {
		$this->name                        = esc_html__( 'Advanced Pricing TableItem', 'divi_flash' );
		$this->plural                      = esc_html__( 'Advanced Pricing TableItems', 'divi_flash' );
		$this->slug                        = 'difl_pricingtableitem';
		$this->vb_support                  = 'on';
		$this->type                        = 'child';
		$this->child_title_var             = 'admin_label';
		$this->child_title_fallback_var    = 'item_type';
		$this->settings_text               = esc_html__( 'Pricing TableItem Settings', 'divi_flash' );
		$this->advanced_setting_title_text = esc_html__( 'Pricing TableItem', 'divi_flash' );
	}

	public function __get( $name ) {
		if ( is_array( $this->props ) && array_key_exists( $name, $this->props ) ) {
			return $this->props[ $name ];
		}

		if ( is_object( $this->props ) && property_exists( $this->props, $name ) ) {
			return $this->props->{ $name };
		}

		if ( $this->props instanceof \ArrayAccess && isset( $this->props[ $name ] ) ) {
			return $this->props[ $name ];
		}

		return null;
	}

	public function __set( $name, $value ) {
		if ( is_array( $this->props ) ) {
			$this->props[ $name ] = $value;
			return;
		}

		if ( $this->props instanceof \ArrayAccess ) {
			$this->props[ $name ] = $value;
			return;
		}

		if ( is_object( $this->props ) ) {
			$this->props->{ $name } = $value;
			return;
		}

		$this->props[ $name ] = $value;
	}

	protected function item_content_by_keys( $fields, $parent_class = '' ) {
		if ( empty( $fields ) ) {
			return '';
		}

		$types = [ 'text', 'tiny_mce', 'select', 'select_icon', 'range', 'upload', 'color-alpha' ];

		$content = '';

		foreach ( $fields as $key => $field ) {
			$type  = str_replace( 'divi/', '', $field['settings']['innerContent']['item']['component']['name'] );
			$value = $this->{$key};

			if ( ! in_array( $type, $types ) || empty( $value ) ) {
				continue;
			}

			if ( ! empty( $field['show_if'] ) ) {
				foreach ( $field['show_if'] as $if_key => $slug ) {
					$dependent_value = $this->{$if_key};
					if ( $slug !== $dependent_value ) {
						continue 2;
					}
				}
			}

			switch ( $type ) {
				case 'text':
				case 'tiny_mce':
					$content .= sprintf( '<span class="%2$s">%1$s</span>', $value, $key );
					break;
				case 'select_icon':
					$content .= sprintf( '<span class="et-pb-icon %2$s">%1$s</span>', $key !== '' ? esc_attr( Utils::process_font_icon( $value ) ) : '5', $key );
					break;
				default:
					break;
			}
		}

		return $content;
	} // Seperate this for every single item and that way we can add manipulate class order etc

	protected function generate_icon_style( $base_attr, $selector ) {
		$this->generate_styles(
			[
				'utility_arg'    => 'icon_font_family',
				'render_slug'    => $this->slug,
				'base_attr_name' => $base_attr,
				'important'      => true,
				'selector'       => $selector,
				'processor'      => [
					'ET_Builder_Module_Helper_Style_Processor',
					'process_extended_icon',
				],
			]
		);
	}

	protected function generate_generic_style( $key, $selector, $type = 'color' ) {
        //		$this->generate_styles(
        //			[
        //				'type'                            => $type,
        //				'render_slug'                     => $this->slug,
        //				'base_attr_name'                  => $key,
        //				'css_property'                    => $type,
        //				'selector'                        => $selector,
        //				'hover_pseudo_selector_location'  => 'suffix',
        //				'sticky_pseudo_selector_location' => 'prefix',
        //			]
        //		);
	}

	protected function generate_font_size( $key, $selector, $type = 'range' ) {
		$this->generate_styles(
			[
				'type'                            => $type,
				'render_slug'                     => $this->slug,
				'base_attr_name'                  => $key,
				'selector'                        => $selector,
				'hover_pseudo_selector_location'  => 'suffix',
				'sticky_pseudo_selector_location' => 'prefix',
				'css_property'                    => 'font-size',
				'processor'                       => [
					'ET_Builder_Module_Helper_Style_Processor',
					'process_toggle_title_icon_font_size',
				],
			]
		);
	}

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/pricingtableitem' )->customCssFields;
	}

	public function get_common_styles( $args ) {
		$attrs          = $this->get_merged_attrs( $args );
		$attrs          = self::build_attr_for_styles( $attrs );
		$attrs          = $this->get_default_value_for_styles( $attrs );
		$orderClass     = $args['orderClass'] ?? '';
        $elements      = $args['elements'];
		$icon_selector  = "$orderClass .item-icon .et-pb-icon";
		$image_selector = "$orderClass  .item-image img";
		$ribbon_icon    = "$orderClass .item-ribbon .et-pb-icon";

		$styles = [];

		$common_styles[] = CssStyle::style( [
			'selector'  => $orderClass,
			'attr'      => $attrs['css'] ?? [],
			'cssFields' => self::custom_css(),
		] );

        if(isset($attrs['button']['decoration']['button']['decoration'])) {
            $common_styles[] = $elements->style([
                'attrName' => 'button',
                'styleProps' => [
                    'type' => 'button',
                    'attrs' => $attrs['button']['decoration']['button']['decoration']
                ],
            ]);
        }

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .item-price",
			'attr'     => $attrs['price_content']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$priceGap = $value['attrValue']['price_gap'] ?? '';
				if($priceGap) return 'gap: '.$priceGap.';';
			}
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .item-feature",
			'attr'     => $attrs['feature_icon_content']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$featureIconGap = $value['attrValue']['price_icon_gap'] ?? '';
				if($featureIconGap) return 'gap: '.$featureIconGap.';';
			}
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .item-icon",
			'attr'     => $attrs['item_icon']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$imageIconAlignment = $value['attrValue']['image_icon_alignment'] ?? '';
				if($imageIconAlignment) return 'text-align: '.$imageIconAlignment.';';
			}
		] );

		$style_maps = [
			'feature_icon_size'           => [
				'selector' => "{$orderClass} .item-feature .et-pb-icon",
				'property' => 'font-size',
			],
			'feature_icon_color'          => [
				'selector' => "{$orderClass} .item-feature .et-pb-icon",
				'property' => 'color',
			],
			'feature_icon_bg_color'       => [
				'selector' => "{$orderClass} .item-feature .et-pb-icon",
				'property' => 'background-color',
			],
			'icon_color'                  => [
				'selector' => $icon_selector,
				'property' => 'color',
			],
			'image_icon_width'            => [
				'selector' => $icon_selector,
				'property' => 'font-size',
			],
			'image_icon_alignment'            => [
				'selector' => "{$orderClass} .item-icon",
				'property' => 'text-align',
			],
			'image_icon_background_color' => [
				'selector' => $icon_selector,
				'property' => 'background-color',
			],
			'rating_icon_label_gap'       => [
				'selector' => "{$orderClass} .item-rating",
				'property' => 'gap',
			],
			'rating_icon_gap'             => [
				'selector' => "{$orderClass} .item-rating span.et-pb-icon+span.et-pb-icon",
				'property' => 'margin-inline-start',
			],
			'rating_icon_size'            => [
				'selector' => "{$orderClass} .item-rating .star span.et-pb-icon",
				'property' => 'font-size',
			],
			'ribbon_icon_color'           => [
				'selector' => $ribbon_icon,
				'property' => 'color',
			],
			'ribbon_icon_size'            => [
				'selector' => $ribbon_icon,
				'property' => 'font-size',
			],
			'ribbon_icon_bg_color'        => [
				'selector' => $ribbon_icon,
				'property' => 'background-color',
			],
			'ribbon_image_width'          => [
				'selector' => "{$orderClass} .item-ribbon .ribbon-image",
				'property' => 'width',
			],
			'divider_height'              => [
				'selector' => "{$orderClass} .item-divider",
				'property' => 'height',
			],
			'feature_tooltip_arrow_color' => [
				'selector' => '.tippy-box[data-theme~="' . $orderClass . '"] .tippy-arrow',
				'property' => 'color',
			],
			'feature_tooltip_bg_color'    => [
				'selector' => '.tippy-box[data-theme~="' . $orderClass . '"]',
				'property' => 'background-color',
			],
		];

		if ( 'on' === $this->props['rating_enable_custom_icon'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .item-rating .star span.df_rating_icon_fill::before",
				'attr'                => $attrs['rating_settings']['innerContent'] ?? [],
				'declarationFunction' => function () {
					return 'content: attr(data-icon) !important;' . self::get_font_style( $this->props['rating_icon'] ?? '' );
				},
				'important'           => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .item-rating .star span.df_rating_icon_fill::after",
				'attr'                => $attrs['rating_settings']['innerContent'] ?? [],
				'declarationFunction' => function () {
					return 'display: none !important;';
				},
				'important'           => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-rating span.et-pb-icon:not(.df_rating_icon_empty), {$orderClass} .item-rating .df_rating_icon_fill::before",
				'attr'      => $attrs['rating_color']['innerContent'] ?? [],
				'property'  => 'color',
				'important' => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-rating .df_rating_icon_empty",
				'attr'      => $attrs['rating_color_inactive']['innerContent'] ?? [],
				'property'  => 'color',
				'important' => true,
			] );
		}

		if ( 'off' === $this->props['rating_enable_custom_icon'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-rating span.et-pb-icon:not(.df_rating_icon_empty), {$orderClass} .item-rating .df_rating_icon_fill::before",
				'attr'      => $attrs['rating_color']['innerContent'] ?? [],
				'property'  => 'color',
				'important' => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-rating .df_rating_icon_empty",
				'attr'      => $attrs['rating_color_inactive']['innerContent'] ?? [],
				'property'  => 'color',
				'important' => true,
			] );
		}

        if ( 'Button' === $this->props['item_type'] && 'on' !== $this->props['button_full_width'] ) {
            $common_styles[] = CommonStyle::style( [
                'selector' => "{$orderClass} .item-button:not(.full-width)",
                'attr'     => $attrs['button']['innerContent'] ?? [],
                'declarationFunction' => function ( $value ) {
                    $attrVal = $value['attrValue']['button_alignment'] ?? null;
					switch( $attrVal ) {
						case 'left':
							return 'place-content: start;';
							break;
						case 'right':
							return 'place-content: end;';
							break;
						default:
							return 'place-content: center;';
							break;
					}
                },
            ] );
        }

		foreach ( $style_maps as $attr_key => $style_map ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => $style_map['selector'],
				'attr'      => $attrs[ $attr_key ]['innerContent'] ?? null,
				'property'  => $style_map['property'],
				'important' => true,
			] );
		}

		$icon_style_map = [
			'feature_icon' => [
				'selector' => "{$orderClass} .item-feature .et-pb-icon",
				'attr'     => 'feature_icon_content',
			],
			'item_icon'    => [
				'selector' => $icon_selector,
				'attr'     => 'item_icon',
			],
			'ribbon_icon'  => [
				'selector' => $ribbon_icon,
				'attr'     => 'ribbon_settings',
			],
			'rating_icon'  => [
				'selector' => "{$orderClass} .item-rating .star.star span.et-pb-icon",
				'attr'     => 'rating_settings',
			],
		];

		foreach ( $icon_style_map as $attr_key => $style_map ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => $style_map['selector'],
				'attr'                => $attrs[ $style_map['attr'] ]['innerContent'] ?? [],
				'declarationFunction' => function () use ( $attr_key ) {
					return self::get_font_style( $this->props[ $attr_key ] ?? '' );
				},
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => $image_selector,
			'attr'      => $attrs['image_icon_width']['innerContent'] ?? [],
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $image_selector,
			'attr'      => $attrs['image_icon_background_color']['innerContent'] ?? [],
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-image",
			'attr'      => $attrs['image_icon_alignment']['innerContent'] ?? [],
			'property'  => 'justify-content',
			'important' => true,
		] );

		// support for overflow: hidden
		$common_styles [] = CommonStyle::style( [ 
			'selector' => "{$orderClass}", 
			'attr' => $attrs['module']['decoration']['border'] ?? null, 
			'declarationFunction' => function ( $props ) { 
				if(isset($props['attrValue']['radius'])){
					return 'overflow: hidden !important';
				}
			} 
		]); 

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass}",
			'attr'                => $attrs['ribbon_settings']['innerContent'] ?? [],
			'declarationFunction' => function ( $attr_value ) use ( $attrs ) {
				if ( ! array_key_exists( 'ribbon_transform_x', $attr_value['attrValue'] ) ) {
					$attr_value['attrValue']['ribbon_transform_x'] = $this->props['ribbon_transform_x'];
				}
				if ( ! array_key_exists( 'ribbon_transform_y', $attr_value['attrValue'] ) ) {
					$attr_value['attrValue']['ribbon_transform_y'] = $this->props['ribbon_transform_y'];
				}

				return "transform: translate({$attr_value['attrValue']['ribbon_transform_x']},{$attr_value['attrValue']['ribbon_transform_y']});";
			},
		] );

		foreach ( $common_styles as $common_style ) {
			if ( empty( $common_style ) ) {
				continue;
			}

			foreach ( $common_style as $style ) {
				$styles[] = $style;
			}
		}

		return $styles ?? [];
	}

	// Types -- text, tiny_mce, select, select_icon, range, upload, color-alpha
	public function render_item_Text() {
		global $wp_embed;
		add_shortcode( 'embed', [ $wp_embed, 'shortcode' ] );

		return sprintf( '<div class="item-text">%1$s</div>', do_shortcode( html_entity_decode( preg_replace( [
			'/<\/p>/',
			'/<p>\s*<\/p>/',
			'/<p>\s*$/',
		], [ '', '', '' ], $this->text_content ) ) ) );
	}

	public function render_item_Price() {
		$original_price = '';
		if ( 'on' === $this->enable_original_price ) {
			// $this->generate_generic_style( 'price_gap', '%%order_class%% .item-price', 'gap' );
			$prefix = sprintf( '<span class="price_prefix %2$s">%1$s</span>', esc_html( $this->original_price_prefix ), $this->original_price_prefix_placement );
			$price  = sprintf( '<span class="price">%1$s</span>', esc_html( $this->original_price ) );
			$suffix = sprintf( '<span class="price_suffix %2$s">%1$s</span>', esc_html( $this->original_price_suffix ), $this->original_price_suffix_placement );

			$original_price = sprintf( '<span class="original-price %4$s">%1$s %2$s %3$s</span>', $prefix, $price, $suffix, $this->original_price_placement );
		}
		$prefix     = sprintf( '<span class="price_prefix %2$s">%1$s</span>', esc_html( $this->price_prefix ), $this->price_prefix_placement );
		$price      = sprintf( '<span class="price">%1$s</span>', esc_html( $this->price ) );
		$suffix     = sprintf( '<span class="price_suffix %2$s">%1$s</span>', esc_html( $this->price_suffix ), $this->price_suffix_placement );
		$sale_price = sprintf( '<span class="sale-price">%1$s %2$s %3$s</span>', $prefix, $price, $suffix );

		return sprintf( '<div class="item-price %3$s">%1$s %2$s</div>', $original_price, $sale_price, $this->price_alignemnt );
	}

    public function render_item_Feature() {
		$icon_placement = $this->feature_icon_placement;
		$parent_class   = "item-feature icon-$icon_placement";
		$icon_selector  = '%%order_class%% .item-feature .et-pb-icon';
		$this->set_margin_padding_styles(
			[
				'render_slug' => $this->slug,
				'slug'        => "feature_icon_spacing_padding",
				'type'        => 'padding',
				'selector'    => "$icon_selector",
				'hover'       => "$icon_placement:hover",
			]
		);
		$tooltip_selector = '.tippy-box[data-theme~="%%order_class%%"]';
		$this->set_margin_padding_styles(
			[
				'render_slug' => $this->slug,
				'slug'        => 'feature_tooltip_spacing_padding',
				'type'        => 'padding',
				'selector'    => "$tooltip_selector",
				'hover'       => "$tooltip_selector:hover",
			]
		);
		$this->generate_generic_style( 'price_icon_gap', '%%order_class%% .item-feature', 'gap' );
		$this->generate_icon_style( 'feature_icon', $icon_selector );
		$this->generate_generic_style( 'feature_icon_color', $icon_selector );
		$this->generate_generic_style( 'feature_icon_bg_color', $icon_selector, 'background-color' );
		$this->generate_font_size( 'feature_icon_size', $icon_selector );
		$this->generate_generic_style( 'feature_tooltip_bg_color', '.tippy-box[data-theme~="%%order_class%%"]', 'background-color' );
		$this->generate_generic_style( 'feature_tooltip_arrow_color', '.tippy-box[data-theme~="%%order_class%%"] .tippy-arrow' );
        //		$tooltip_fileds  = array_filter( $this->get_fields(), function ( $item, $key ) {
        //			return str_starts_with( $key, 'feature_text_tooltip_' );
        //		}, 1 );
		$tooltip_options = [];
		$tooltip_fileds  = [ 'feature_text_tooltip_main_content' ];
		foreach ( $tooltip_fileds as $key ) {
			$tooltip_options[ str_replace( 'feature_text_tooltip_', '', $key ) ] = $this->{$key};
		}

		$feature_content = sprintf( '<span class="%2$s">%1$s</span>', $this->props['feature_text'], 'feature_text' );

		if ( ! empty( $this->props['feature_icon'] ) ) {
			$feature_content .= sprintf( '<span class="et-pb-icon %2$s">%1$s</span>', esc_html( Utils::process_font_icon( $this->props['feature_icon'] ) ?? '' ), 'feature_icon' );
		}

		return sprintf(
			'<div class="%s" data-tooltip="%s">%s</div>',
			esc_attr( $parent_class ),
			esc_attr( wp_json_encode( $tooltip_options ) ),
			wp_kses_post( $feature_content )
		);
	}

	public function render_item_Icon() {
		$parent_class = 'item-icon';
		/**
		 * // this->props['image_icon_alignment'] >> center | left | right  
		 * i changed it with css so this class will no effect on frontend
		 * */
		
		return sprintf( '<div class="%2$s">%1$s</div>', sprintf( '<span class="et-pb-icon %2$s">%1$s</span>', esc_html( Utils::process_font_icon( $this->item_icon ) ?? '' ), 'item_icon' ), $parent_class );
	}

	public function render_item_Image() {
		$img = sprintf( '<img src="%1$s" alt="%2$s"/>', $this->item_image, $this->item_image_alt );
		/**
		 * // this->props['image_icon_alignment'] >> center | left | right  
		 * i changed it with css so this class will no effect on frontend
		 * */
		return sprintf( '<span class="item-image">%1$s</span>', $img );
	}

	public function render_item_Ribbon() {
		$animation    = 'on' === $this->ribbon_animation ? 'difl_bounce_in ' : '';
		$content      = '';
		$ribbon_type  = esc_attr( $this->ribbon_type );
		$icon_enable  = 'text_icon' === $ribbon_type || 'icon_only' === $ribbon_type;
		$image_enable = 'image_only' === $ribbon_type || 'text_image' === $ribbon_type;
		$text_enable  = 'text_icon' === $ribbon_type || 'text_image' === $ribbon_type || 'text_only' === $ribbon_type;

		$icon_placement = '';

		if ( $icon_enable ) {
			$icon_placement = 'icon-' . $this->ribbon_icon_placement;
			$content        .= sprintf( '<span class="et-pb-icon ribbon_icon">%1$s</span>', esc_html( Utils::process_font_icon( $this->props['ribbon_icon'] ) ?? '' ) );
		}

		if ( $image_enable ) {
			$image_src = esc_attr( $this->ribbon_image );
			if ( ! empty( $image_src ) ) {
				$content .= sprintf( '<img src="%1$s" class="ribbon-image" alt="%2$s"/>', $image_src, esc_attr( $this->ribbon_image_alt ) );
			}
		}

		if ( $text_enable ) {
			$content .= sprintf( '<span class="ribbon_text %2$s">%1$s</span>', esc_html( $this->ribbon_text ), esc_attr( $this->ribbon_orientation ) );
		}

		return sprintf( '<div class="item-ribbon %2$s %3$s %4$s">%1$s</div>', $content, esc_attr( $this->ribbon_position ), $animation, $icon_placement );
	}

	public function render_item_Divider() {
		$selector = '%%order_class%% .item-divider';
		$this->df_process_bg( [
			'render_slug' => $this->slug,
			'slug'        => 'divider_color',
			'selector'    => $selector,
			'hover'       => "$selector:hover",
		] );

		$this->generate_styles(
			[
				'type'                            => 'divider_height',
				'render_slug'                     => $this->slug,
				'base_attr_name'                  => 'divider_height',
				'css_property'                    => 'height',
				'selector'                        => $selector,
				'hover_pseudo_selector_location'  => 'suffix',
				'sticky_pseudo_selector_location' => 'prefix',
			]
		);

		return '<span class="item-divider"></span>';
	}

	public function render_item_Button( $attrs ) {
		$badge_content    = '';

		// Get button icon from attrs (same path as VB Edit.js). Use $this->attrs (processed with preset). Try full path first, then fallback for frontend structure.
		$attrs_for_icon = $this->attrs ?? $attrs;
		$icon_data      = [];
		if ( ! empty( $attrs_for_icon['button']['decoration']['button']['decoration']['button']['desktop']['value']['icon'] ) && is_array( $attrs_for_icon['button']['decoration']['button']['decoration']['button']['desktop']['value']['icon'] ) ) {
			$icon_data = $attrs_for_icon['button']['decoration']['button']['decoration']['button']['desktop']['value']['icon'];
		} elseif ( ! empty( $attrs_for_icon['button']['decoration']['button']['desktop']['value']['icon'] ) && is_array( $attrs_for_icon['button']['decoration']['button']['desktop']['value']['icon'] ) ) {
			$icon_data = $attrs_for_icon['button']['decoration']['button']['desktop']['value']['icon'];
		}
		$button_icon = $icon_data['settings'] ?? '';
		$icon_enable = $icon_data['enable'] ?? 'off';
		$buttonIcon  = ( 'on' === $icon_enable && $button_icon !== '' ) ? Utils::process_font_icon( $button_icon ) : '';
		$buttonIcon  = $buttonIcon !== '' && $buttonIcon !== null ? $buttonIcon : '5';

		if ( 'on' === $this->button_badge ) {
			if ( 'on' === $this->button_badge_animation ) {
				wp_enqueue_style( 'df-animate-styles' );
			}

			$badge_animation = 'on' === $this->button_badge_animation ? 'difl_bounce_in ' : '';
            $badge_text      = ! empty( $this->button_badge_text ) ? $this->button_badge_text : 'text';
			$badge_content   = sprintf( '<span class="button-badge %4$s %2$s %3$s" style="animation-duration: 7s; animation-iteration-count: infinite">%1$s</span>', $badge_text, $this->button_badge_position, $this->button_badge_position_vertically, $badge_animation );
		}

		$button_rel = '';

		if(is_array($this->button_rel) ){
			$button_rel = implode( ' ', $this->button_rel );
		}

        $anchor = sprintf(
            '<a href="%1$s" class="et_pb_button difl-pricingtableitem-button" rel="%5$s" target="%2$s" data-icon="%3$s">%4$s</a>',
            esc_url( $this->button_url ),
            'on' === ( $this->button_url_new_window ?? $this->button_url_new_tab ?? '' ) ? '_blank' : '',
            esc_attr( $buttonIcon ),
            ! empty( $this->props['text'] ) ? $this->props['text'] : 'Click Here',
			$button_rel
        );

        return sprintf(
			'<div class="item-button %3$s">
					%2$s
					%1$s
                </div>', $anchor, $badge_content, 'on' === $this->button_full_width ? 'full-width' : '' );
	}

	// protected function get_rendered_button() {
	// 	return 'button';
	// 	$button_text   = $this->props['button_text'];
	// 	$button_target = 'on' === $this->props['button_url_new_window'] ? 'on' : 'off';
	// 	$button_icon   = $this->props['button_icon'];
	// 	$button_link   = $this->props['button_url'];
	// 	$button_rel    = $this->props['button_rel'];

	// 	$custom_icon_values = et_pb_responsive_options()->get_property_values( $this->props, 'button_icon' );
	// 	$custom_icon        = isset( $custom_icon_values['desktop'] ) ? $custom_icon_values['desktop'] : '';
	// 	$custom_icon_tablet = isset( $custom_icon_values['tablet'] ) ? $custom_icon_values['tablet'] : '';
	// 	$custom_icon_phone  = isset( $custom_icon_values['phone'] ) ? $custom_icon_values['phone'] : '';

	// 	$output = $this->render_button( [
	// 		'button_classname'   => [ 'difl-pricingtableitem-button' ],
	// 		'button_custom'      => $this->props['custom_button'],
	// 		'button_rel'         => $button_rel,
	// 		'button_text'        => $button_text,
	// 		'button_url'         => $button_link,
	// 		'custom_icon'        => $custom_icon,
	// 		'custom_icon_tablet' => $custom_icon_tablet,
	// 		'custom_icon_phone'  => $custom_icon_phone,
	// 		'has_wrapper'        => false,
	// 		'url_new_window'     => $button_target,
	// 	] );

	// 	return $output;
	// }

	public function render_item_Rating() {
		$rating           = '';
		$label            = '';
		$is_custom        = $this->rating_enable_custom_icon === 'on';
		$rating_icon_star = $is_custom ? ( Utils::process_font_icon( $this->rating_icon ) ?? '☆' ) : '☆';

		$rating_value = $this->rating_number;
		$get_float    = explode( '.', $rating_value );

		$rating_icon         = '';
		$rating_active_class = '';

		for ( $i = 1; $i <= 5; $i ++ ) {
			if ( ! isset( $rating_value ) ) {
				$rating_active_class = '';
			} else if ( $i <= $rating_value ) {
				$rating_active_class = 'df_rating_icon_fill';
			} else if ( $i == $get_float[0] + 1 && isset( $get_float[1] ) && $get_float[1] != '' && $get_float[1] != 0 ) {
				$rating_active_class = 'df_rating_icon_fill df_rating_icon_empty df_fill_' . $get_float[1];
			} else {
				$rating_active_class = 'df_rating_icon_empty';
			}
			$rating_icon .= '<span class="et-pb-icon ' . esc_attr( $rating_active_class ) . '" data-icon="' . esc_attr( $rating_icon_star ) . '">' . esc_html( $rating_icon_star ) . '</span>';
		}
		$label_text = $this->rating_label;
		if ( $label_text ) {
			$label .= sprintf( '<span class="label">%1$s</span>', $label_text );
		}

		return sprintf( '<div class="item-rating %3$s"><span class="star">%1$s</span><span class="label">%2$s</span></div>', $rating_icon, $label, $this->rating_alignment );
	}

	public function render( $attrs, $content, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {
		$type = $this->item_type;

		$method = "render_item_{$type}";

		return $this->$method($attrs);
	}
}
