<?php

namespace DIFL\Server\Modules\ProductItem;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\StyleLibrary\Utils\Utils as StyleUtils;

class ProductItem implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'ProductItem';

	public $slug = 'difl_productitem';
	public $vb_support = 'on';
	public $type = 'child';
	public $child_title_var = 'type';
	public $child_title_fallback_var = 'admin_label';

	protected $module_credits = [
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	];

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/acfgallery' )->customCssFields;
	}

    public static function font_style($font) {
        return static::get_font_style($font);
    }

	public function font_selector( $type = 'main' ) {
		$selector = [
			'main'  => [
				"{$this->main_css_element} .df-product-title",
				"{$this->main_css_element} .df-product-title a",
				"{$this->main_css_element}.df-item-wrap > span:not(.et-pb-icon , .df-sale-badge)",
				"{$this->main_css_element}.df-item-wrap a",
				"{$this->main_css_element}.df-item-wrap",
				"{$this->main_css_element}.df-item-wrap p",
				"{$this->main_css_element} .df-product-read-more",
				"{$this->main_css_element} .df-product-custom-text",
				".woocommerce ul.products li.product {$this->main_css_element} .price",
				"{$this->main_css_element} .df-product-price.price",
				"{$this->main_css_element}.df-item-wrap a.add_to_cart_button"
			],
			'hover' => [
				".df-product-outer-wrap:hover {$this->main_css_element} .df-product-title",
				".df-product-outer-wrap:hover {$this->main_css_element} .df-product-title a",
				".df-product-outer-wrap:hover {$this->main_css_element}.df-item-wrap > span:not(.et-pb-icon)",
				".df-product-outer-wrap:hover {$this->main_css_element} .df-product-custom-text",
				".woocommerce ul.products li.product .df-product-outer-wrap:hover {$this->main_css_element} .price",
				".df-product-outer-wrap:hover {$this->main_css_element} .df-product-price.price",
				".df-product-outer-wrap:hover {$this->main_css_element}.df-item-wrap:not(.df-product-add-to-cart-wrap)",
				"{$this->main_css_element}.df-item-wrap:not(.df-product-add-to-cart-wrap):hover a",
				"{$this->main_css_element}.df-item-wrap:not(span.df-sale-badge):not(.df-product-add-to-cart-wrap):hover",
				"{$this->main_css_element}.df-item-wrap:hover .df-product-read-more",
				"{$this->main_css_element}.df-product-add-to-cart-wrap a.add_to_cart_button:hover"
			]
		];

		if ( $type === 'main' ) {
			return implode( ', ', $selector['main'] );
		} else if ( $type === 'hover' ) {
			//return '.df-product-outer-wrap:hover ' . implode(',.df-product-outer-wrap:hover ',  $selector['main']);
			return implode( ', ', $selector['hover'] );
		}

	}

	public function get_settings_modal_toggles() {
		return [
			'general'  => [
				'toggles' => [
					'type'               => esc_html__( 'Element', 'divi_flash' ),
					'icon_settings'      => esc_html__( 'Icon Settings', 'divi_flash' ),
					'animation_settings' => esc_html__( 'Animation Settings', 'divi_flash' ),
					'overlay'            => esc_html__( 'Overlay & Scale', 'divi_flash' ),
					'divider_line'       => esc_html__( 'Divider Line', 'divi_flash' )
				]
			],
			'advanced' => [
				'toggles' => [
					'image'              => esc_html__( 'Image Settings', 'divi_flash' ),
					'text'               => esc_html__( 'Text', 'divi_flash' ),
					'text_style'         => esc_html__( 'Text Styles', 'divi_flash' ),
					'pricing_style'      => [
						'title'             => esc_html__( 'Pricing Styles', 'divi_flash' ),
						'tabbed_subtoggles' => true,
						'sub_toggles'       => [
							'off_price_text'     => [
								'name' => 'Off Price'
							],
							'regular_price_text' => [
								'name' => 'Regular Price'
							]
						]
					],
					'add_to_cart_design' => esc_html__( 'Add To Cart', 'divi_flash' ),
					'rating'             => esc_html__( 'Rating', 'divi_flash' ),
					'category_tag'       => esc_html__( 'Category & Tag', 'divi_flash' ),
					'read_more'          => esc_html__( 'Read More', 'divi_flash' ),
					'custom_spacing'     => esc_html__( 'Spacing', 'divi_flash' )
				]
			]
		];
	}

    private function df_user_device() {
        // library: Mobile Detect
        $user_agent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        if ( class_exists( '\Detection\DIFL_MobileDetect' ) ) {
            $detect = new \Detection\DIFL_MobileDetect;
        }

        $get_devices = [];
        $detect->setUserAgent( $user_agent );

        if ( $detect->isTablet() ) {
            array_push( $get_devices, "tablet" );
        }

        if ( $detect->isMobile() && ! in_array( "tablet", $get_devices ) ) {
            array_push( $get_devices, "mobile" );
        }

        if ( ! empty( $get_devices ) ) {
            return $get_devices[0];
        }

        return false;
    }

	public function get_transition_fields_css_props() {
		// $fields                         = parent::get_transition_fields_css_props();
		$add_to_cart                    = '.df-product-outer-wrap %%order_class%%.df-product-add-to-cart-wrap a.add_to_cart_button';
		$fields['icon_color']           = [ 'color' => '%%order_class%%.df-item-wrap .et-pb-icon' ];
		$fields['rating_color']         = [ 'color' => '%%order_class%%.df-product-rating-wrap .star-rating span::before' ];
		$fields['disable_rating_color'] = [ 'color' => '%%order_class%%.df-product-rating-wrap .star-rating::before' ];
		$fields['element_margin']       = [ 'margin' => '%%order_class%%' ];
		$fields['element_padding']      = [ 'padding' => '%%order_class%%' ];
		$fields['icon_margin']          = [ 'margin' => '%%order_class%%.df-item-wrap .et-pb-icon' ];
		$fields['button_margin']        = [ 'margin' => '%%order_class%% a' ];
		$fields['button_padding']       = [ 'padding' => '%%order_class%% a' ];
		$fields['image_margin']         = [ 'margin' => '%%order_class%% img' ];
		$fields['divider_margin']       = [ 'margin' => '%%order_class%% span' ];
		$fields['divider_padding']      = [ 'padding' => '%%order_class%% span' ];
		$fields['add_to_cart_margin']   = [ 'margin' => $add_to_cart ];
		$fields['add_to_cart_padding']  = [ 'padding' => $add_to_cart ];

		$fields['type']         = [ 'opacity' => '%%order_class%%' ];
		$fields['display_type'] = [ 'transform' => '%%order_class%%' ];
		//border
		$fields = $this->df_fix_border_transition(
			$fields,
			'add_to_cart',
			$add_to_cart
		);

		// box-shadow
		$fields = $this->df_fix_box_shadow_transition(
			$fields,
			'add_to_cart',
			$add_to_cart
		);

		return $fields;
	}

    public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
        $orderClass    = $args['orderClass'] ?? '';
        $elements      = $args['elements'];
        $styles        = [];
		$common_styles = [];

        $meta = [
			'rating',
			'add_to_cart',
			'button',
			'price',
			'categories',
			'tags',
			'custom_text'
		]; // Remove 'quick_view',

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

        // Text (groupSlug: text) styles: carousel/grid + Theme Builder (standalone) so title, price, add to cart, button etc. get styles.
        $text_selector = ".difl_product_carousel .df-product-inner-wrap {$orderClass}, .difl_productgrid .df-product-inner-wrap {$orderClass}, .difl_product_carousel .df-product-outer-wrap {$orderClass}, .difl_productgrid .df-product-outer-wrap {$orderClass}";
        $common_styles[] = CommonStyle::style([
            'selector' => $text_selector,
            'attr' => $attrs['product_item_text']['advanced']['text']['text'] ?? null,
            'declarationFunction' => function ($props) {
                $orientation = isset($props['attrValue']['orientation']) ? $props['attrValue']['orientation'] : null;
                if ($orientation) {
                    return "text-align: {$orientation}";
                }
                return "";
            }
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => $text_selector,
            'attr' => $attrs['product_item_text']['advanced']['text']['textShadow'] ?? null,
            'declarationFunction' => function ($props) {
                $horizontal = isset($props['attrValue']['horizontal']) ? $props['attrValue']['horizontal'] : "0em";
                $vertical = isset($props['attrValue']['vertical']) ? $props['attrValue']['vertical'] : "0em";
                $blur = isset($props['attrValue']['blur']) ? $props['attrValue']['blur'] : "0em";
                $color = isset($props['attrValue']['color']) ? $props['attrValue']['color'] : "rgba(0,0,0,0.4)";
                return "text-shadow: {$horizontal} {$vertical} {$blur} {$color} !important;";
            }
        ]);


		if ( in_array( $this->props['type'], $meta ) ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => ".df-item-wrap{$orderClass}",
                'attr'                => $attrs['meta_display']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $meta_display = $props['attrValue'] ?? $this->props['meta_display'];

                    return sprintf( 'display: %1$s !important;', $meta_display );
                },
                'important' => true,
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'            => ".df-item-wrap{$orderClass}",
                'attr'                => $attrs['meta_display']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $meta_display = $props['attrValue'] ?? $this->props['meta_display'];

                    if ( $meta_display === 'inline-block' ) {
                        return "vertical-align: middle !important;";
                    }
                },
                'important' => true,
            ] );
		}

		if ( $this->props['type'] === 'button' && $this->props['meta_display'] === 'block' ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => ".df-item-wrap{$orderClass}.df-product-button-wrap a.df-product-read-more",
                'attr'                => $attrs['meta_display']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $meta_display = $props['attrValue'] ?? $this->props['meta_display'];

                    return "display: block !important;";
                },
                'important' => true,
            ] );
		}

        if(isset($this->props['type']) && "image" === $this->props['type']) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => ".df-item-wrap{$orderClass}",
                'attr'                => $attrs['type']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    return "overflow: hidden !important;";
                },
                'important' => true,
            ] );

            // overlay: use attrs so overlay applies on first render (before save) when toggled in VB
            $overlay_attr_value = $attrs['overlay_obj_settings']['innerContent']['desktop']['value'] ?? [];
            $overlay_on = ( isset( $this->props['overlay'] ) && $this->props['overlay'] === 'on' )
                || ( isset( $overlay_attr_value['overlay'] ) && $overlay_attr_value['overlay'] === 'on' );

            if ( $overlay_on ) {
                $common_styles[] = CommonStyle::style( [
                    'selector'            => ".df-item-wrap{$orderClass} .df-overlay",
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? [],
                    'declarationFunction' => function ( $props ) {
                        $overlay_primary   = $props['attrValue']['overlay_primary'] ?? $this->props['overlay_primary'] ?? '#00B4DB';
                        $overlay_secondary = $props['attrValue']['overlay_secondary'] ?? $this->props['overlay_secondary'] ?? '#0083B0';
                        $overlay_direction = $props['attrValue']['overlay_direction'] ?? $this->props['overlay_direction'];

                        return sprintf( 'background-image: linear-gradient(%4$s, %1$s 0, %2$s %3$s);',
                            $overlay_primary,
                            $overlay_secondary,
                            '100%',
                            $overlay_direction
                        );
                    },
                ] );

                $common_styles[] = CommonStyle::style( [
                    'selector'            => ".df-item-wrap{$orderClass} .df-icon-overlay",
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? [],
                    'declarationFunction' => function ( $props ) {
                        $size = $props['attrValue']['overlay_icon_size'] ?? $this->props['overlay_icon_size'] ?? '35px';
                        $size = is_numeric( $size ) ? $size . 'px' : $size;
                        return "font-size: {$size} !important;";
                    },
                    'important' => true,
                ] );

                $common_styles[] = CommonStyle::style( [
                    'selector'            => ".df-item-wrap{$orderClass} .df-icon-overlay",
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? [],
                    'declarationFunction' => function ( $props ) {
                        $color = $props['attrValue']['overlay_icon_color'] ?? $this->props['overlay_icon_color'] ?? '';
                        return $color !== '' ? "color: {$color} !important;" : '';
                    },
                    'important' => true,
                ] );

                // Disable overlay on mobile: apply only at phone breakpoint (max-width 767px).
                if ( isset( $this->props['overlay_off_at_mobile'] ) && $this->props['overlay_off_at_mobile'] === 'on' ) {
                    $common_styles[] = CommonStyle::style( [
                        'selector'            => ".df-item-wrap{$orderClass} .df-hover-effect .df-overlay",
                        'attr'                => $attrs['overlay_off_at_mobile']['innerContent'] ?? [],
                        'declarationFunction' => function ( $props ) {
                            return 'display: none !important';
                        },
                        'important'           => true,
                        'atRules'             => '@media only screen and ( max-width: 767px )',
                    ] );
                }

            }
        }

        // Divider style: support both same-element and ancestor orderClass (Theme Builder uses block wrapper).
        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} span.df-product-ele-divider, .df-item-wrap{$orderClass} span.df-product-ele-divider",
            'attr'                => $attrs['divider_obj_settings']['innerContent'] ?? [],
            'declarationFunction' => function ( $props ) {
                $divider_obj = $props['attrValue'] ?? [];
                $divider_color_direction  = $divider_obj['divider_color_direction'] ?? '90deg';
                $divider_color_primary    = $divider_obj['divider_color_primary'] ?? '#e02b20';
                $divider_color_secondary  = $divider_obj['divider_color_secondary'] ?? '#fc7069';
                $divider_color_start      = $divider_obj['divider_color_start'] ?? '0%';
                $divider_color_end        = $divider_obj['divider_color_end'] ?? '100%';
                $divider_line_height      = $divider_obj['divider_line_height'] ?? '3px';

                return "background-image: linear-gradient(
                    $divider_color_direction,
                    $divider_color_primary
                    $divider_color_start,
                    $divider_color_secondary
                    $divider_color_end
                ) !important;
                height: $divider_line_height !important;
            ";
            },
            'important'           => true,
        ] );

        // Image as Icon.
        if ( $this->props['use_image_as_icon'] === 'on' ) {
            $common_styles[] = CommonStyle::style( [
                'selector'  => ".df-item-wrap{$orderClass} a.df_button img",
                'attr'      => $attrs['image_as_icon_width']['innerContent'] ?? [],
                'property'  => "width",
                'important' => true
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'  => "{$orderClass}.df-product-add-to-cart-wrap a.added_to_cart.wc-forward::after, {$orderClass}.df-product-add-to-cart-wrap a.added_to_cart.wc-forward::before",
                'attr'      => $attrs['image_as_icon_width']['innerContent'] ?? [],
                'property'  => "font-size",
                'important' => true
            ] );
		}

        // Icon image placement (float left/right) – same as VB CommonStyle on .df-item-wrap:not(.only_icon_in_cart) a.df_button img
        $common_styles[] = CommonStyle::style( [
            'selector'            => ".df-item-wrap{$orderClass}:not(.only_icon_in_cart) a.df_button img",
            'attr'                => $attrs['image_icon_placement']['innerContent'] ?? [],
            'declarationFunction' => function ( $props ) {
                $placement = $props['attrValue'] ?? $this->props['image_icon_placement'] ?? 'right';
                $float_val = ( $placement === 'left' ) ? 'left' : 'right';
                return "float: {$float_val} !important;";
            },
            'important'            => true,
        ] );

        // Rating — selectors match v4 (df_process_color + hover on .df-product-outer-wrap).
        if ( isset( $this->props['type'] ) && 'rating' === $this->props['type'] ) {
            $common_styles[] = CommonStyle::style( [
                'selectors' => [
                    'desktop' => [
                        'value' => "{$orderClass}.df-product-rating-wrap .star-rating span::before",
                        'hover' => ".df-product-outer-wrap:hover {$orderClass}.df-product-rating-wrap .star-rating span::before",
                    ],
                ],
                'attr'      => $attrs['rating_color']['innerContent'] ?? [],
                'property'  => 'color',
                'important' => true,
            ] );
            $common_styles[] = CommonStyle::style( [
                'selectors' => [
                    'desktop' => [
                        'value' => "{$orderClass}.df-product-rating-wrap .star-rating::before",
                        'hover' => ".df-product-outer-wrap:hover {$orderClass}.df-product-rating-wrap .star-rating::before",
                    ],
                ],
                'attr'      => $attrs['disable_rating_color']['innerContent'] ?? [],
                'property'  => 'color',
                'important' => true,
            ] );
            $common_styles[] = CommonStyle::style( [
                'selectors' => [
                    'desktop' => [
                        'value' => "{$orderClass}.df-product-rating-wrap .star-rating",
                        'hover' => ".df-product-outer-wrap:hover {$orderClass}.df-product-rating-wrap .star-rating",
                    ],
                ],
                'attr'      => $attrs['rating_size']['innerContent'] ?? [],
                'property'  => 'font-size',
                'important' => true,
            ] );
        }

        // Text Show On Hover: hide button text by default, show on hover (Add to Cart and Button / Read More).
        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass}.df-item-wrap.df-product-add-to-cart-wrap a.df_button",
            'attr'                => $attrs['text_show_on_hover']['innerContent'] ?? [],
            'declarationFunction' => function ( $props ) {
                $text_show_on_hover = $props['attrValue'] ?? $this->props['text_show_on_hover'];
                if ( 'on' === $text_show_on_hover ) {
                    return 'font-size: 0px !important;';
                }
                return '';
            },
            'important'           => true,
        ] );
        $common_styles[] = CommonStyle::style( [
            'selector'            => ".df-product-outer-wrap:hover {$orderClass}.df-item-wrap.df-product-add-to-cart-wrap a.df_button",
            'attr'                => $attrs['text_show_on_hover']['innerContent'] ?? [],
            'declarationFunction' => function ( $props ) {
                $text_show_on_hover = $props['attrValue'] ?? $this->props['text_show_on_hover'];
                if ( 'on' === $text_show_on_hover ) {
                    return 'font-size: inherit !important;';
                }
                return '';
            },
            'important'           => true,
        ] );
        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass}.df-item-wrap.df-product-button-wrap a.df-product-read-more",
            'attr'                => $attrs['text_show_on_hover']['innerContent'] ?? [],
            'declarationFunction' => function ( $props ) {
                $text_show_on_hover = $props['attrValue'] ?? $this->props['text_show_on_hover'];
                if ( 'on' === $text_show_on_hover ) {
                    return 'font-size: 0px !important;';
                }
                return '';
            },
            'important'           => true,
        ] );
        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass}.df-item-wrap.df-product-button-wrap a.df-product-read-more:hover",
            'attr'                => $attrs['text_show_on_hover']['innerContent'] ?? [],
            'declarationFunction' => function ( $props ) {
                $text_show_on_hover = $props['attrValue'] ?? $this->props['text_show_on_hover'];
                if ( 'on' === $text_show_on_hover ) {
                    return 'font-size: inherit !important;';
                }
                return '';
            },
            'important'           => true,
        ] );

		$common_styles[] = CommonStyle::style( [
            'selector'            => ".df-item-wrap{$orderClass} .et-pb-icon",
            'attr'                => $attrs['icon_size']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $icon_size = $props['attrValue'] ?? $this->props['icon_size'];

                return "font-size: $icon_size !important;";
            },
        ] );

        if($this->props['use_only_icon'] !== 'on') {
            $common_styles[] = CommonStyle::style( [
                'selector'            => ".df-item-wrap{$orderClass} .et-pb-icon, .df-item-wrap{$orderClass} a.df_button img.df_product_icon_image",
                'attr'                => $attrs['space_btw_text_icon']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $space_btw_text_icon = $props['attrValue'] ?? $this->props['space_btw_text_icon'] ?? '';

                    $property = 'margin-left';
                    if(isset($this->props['image_icon_placement']) && $this->props['image_icon_placement'] === 'left') {
                        $property = 'margin-right';
                    }

                    return "$property: $space_btw_text_icon !important;";
                },
                'important' => true
            ] );
        }


        $common_styles[] = CommonStyle::style( [
            'selector'            => ".df-item-wrap{$orderClass} .et-pb-icon",
            'attr'                => $attrs['icon_color']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $icon_color = $props['attrValue'] ?? $this->props['icon_color'];

                return "color: $icon_color !important;";
            },
        ] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => ".df-item-wrap{$orderClass} .et-pb-icon",
            'attr'                => $attrs['font_icon']['innerContent'] ?? [],
            'declarationFunction' => function( $props ) {
				return self::font_style( $props['attrValue'] );
			}
        ] );

        // Animation
		$common_styles[] = CommonStyle::style( [
            'selector'            => ".df-item-wrap{$orderClass}",
            'attr'                => $attrs['display_type']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $display_type = $props['attrValue'] ?? $this->props['display_type'];

                $opacity = 1;
                if($display_type === 'show_on_hover') {
                    $opacity = 0;
                }

                return "opacity: $opacity !important; transition: opacity 300ms ease 0ms, margin 300ms ease 0ms, transform 300ms ease 0ms !important;";
            },
        ] );

        // Display type hover: exclude Add to Cart so it only responds to its own hover, not card hover.
        $common_styles[] = CommonStyle::style( [
            'selector'            => ".df-product-outer-wrap:hover .df-item-wrap{$orderClass}",
            'attr'                => $attrs['display_type']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $display_type = $props['attrValue'] ?? $this->props;

                if($props['state'] === 'hover') {
                    return;
                }

                $opacity = 1;
                if($display_type === 'show_on_hover') {
                    $opacity = 1;
                }

                if($display_type === 'hide_on_hover') {
                    $opacity = 0;
                }

                return "opacity: $opacity !important;";
            },
        ] );

        // Always Show On Mobile: apply only at phone breakpoint (max-width 767px).
        if ( isset( $this->props['always_show_on_mobile'] ) && $this->props['always_show_on_mobile'] === 'on' ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => ".df-product-outer-wrap .df-item-wrap{$orderClass}",
                'attr'                => $attrs['always_show_on_mobile']['innerContent'] ?? [],
                'declarationFunction' => function ( $props ) {
                    $always_show_on_mobile = $props['always_show_on_mobile'] ?? $this->props['always_show_on_mobile'];
                    if ( $always_show_on_mobile === 'on' ) {
                        return 'opacity: 1 !important;';
                    }
                    return '';
                },
                'important'           => true,
                'atRules'             => '@media only screen and ( max-width: 767px )',
            ] );
        }

        // Module alignment (Sizing): left/center/right -> margin (like D4).
        $alignment_to_margin = function ( $align ) {
            $v = is_string( $align ) ? strtolower( $align ) : '';
            if ( $v === 'center' ) {
                return 'margin-left: auto !important; margin-right: auto !important;';
            }
            if ( $v === 'right' ) {
                return 'margin-left: auto !important; margin-right: 0 !important;';
            }
            if ( $v === 'left' || $v !== '' ) {
                return 'margin-left: 0 !important; margin-right: auto !important;';
            }
            return '';
        };
        $sizing_attr = $attrs['module']['decoration']['sizing'] ?? null;
        if ( $sizing_attr ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => ".df-item-wrap{$orderClass}",
                'attr'                => $sizing_attr,
                'declarationFunction' => function ( $props ) use ( $alignment_to_margin ) {
                    $val = $props['attrValue']['alignSelf'] ?? $props['attrValue'] ?? '';
                    if ( is_array( $val ) ) {
                        $val = $val['desktop'] ?? $val['value'] ?? '';
                    }
                    return $alignment_to_margin( $val );
                },
                'important' => true,
            ] );
        }
        // D4 props: module_alignment, module_alignment_tablet, module_alignment_phone.
        $mod_align = $this->props['module_alignment'] ?? '';
        if ( $mod_align !== '' ) {
            $common_styles[] = CommonStyle::style( [
                'selector'  => ".df-item-wrap{$orderClass}",
                'attr'      => null,
                'declarationFunction' => function () use ( $alignment_to_margin, $mod_align ) {
                    return $alignment_to_margin( $mod_align );
                },
                'important' => true,
            ] );
        }

        // Prevent product_font_style hover from applying font-size: 0 to Read More link when hover font is not set.
        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass}.df-product-button-wrap a.df-product-read-more:hover",
            'attr'                => $attrs['product_font_style']['decoration']['font']['innerContent'] ?? null,
            'declarationFunction' => function ( $params ) {
                $attr_value = $params['attrValue'] ?? [];
                $hover_val  = $attr_value['hover'] ?? $attr_value;
                $size       = is_array( $hover_val ) ? ( $hover_val['size'] ?? null ) : null;
                if ( null === $size || '' === $size || 0 === $size || '0' === $size || '0px' === $size ) {
                    return 'font-size: inherit !important;';
                }
                return '';
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

	public function render( $attrs, $content, $render_slug ) {
		global $df_product_items, $df_product_items_outside, $df_product_element;
		$type_settings = [];

        // echo '<pre>';
        // print_r($this->props);
        // echo '</pre>';

		if ( ! empty( $this->props['type'] ) && 'select' !== $this->props['type'] ) {
			$type_settings['type']  = $this->props['type'];
			$type_settings['class'] = $this->order_class;
			// title
			$type_settings['title_tag']    = $this->props['title_tag'];

			$type_settings['product_link'] = $this->props['product_link'];


			$type_settings['price_tag']    = $this->props['price_tag'];

			// content
			$type_settings['use_product_excrpt'] = $this->props['use_product_excrpt'];
			$type_settings['excerpt_length']     = $this->props['excerpt_length'];
			// rating
			$type_settings['show_rating_all_item'] = $this->props['show_rating_all_item'];
			// Read More
			$type_settings['read_more_text'] = $this->props['read_more_text'];
			// icon
			$type_settings['use_icon']  = $this->props['use_icon'];
			$type_settings['font_icon'] = isset( $this->props['font_icon'] ) && $this->props['font_icon'] !== '' ?
				esc_attr( Utils::process_font_icon( $this->props['font_icon'] ) ) : '5';

			$type_settings['use_image_as_icon']    = $this->props['use_image_as_icon'];
			$type_settings['image_as_icon']        = $this->props['image_as_icon'];
			$type_settings['image_alt_text']       = $this->props['image_alt_text'] !== '' ? $this->props['image_alt_text'] : '';
			$type_settings['image_as_icon_width']  = $this->props['image_as_icon_width'];
			$type_settings['image_icon_placement'] = $this->props['image_icon_placement'];
			// Categories
			$type_settings['use_category_link']     = $this->props['use_category_link'];
			$type_settings['use_separator']         = $this->props['use_separator'];
			$type_settings['category_separator']    = isset( $this->props['category_separator'] ) ? $this->props['category_separator'] : '|';
			$type_settings['category_open_new_tab'] = $this->props['category_open_new_tab'];

			// placement
			$type_settings['placement'] = $this->props['outside_wrapper'];
			// hover
			$type_settings['image_scale'] = $this->props['image_scale'];
			// overlay: use attrs so overlay DOM renders on first load (before save)
			$overlay_attr = $attrs['overlay_obj_settings']['innerContent']['desktop']['value'] ?? [];
			$type_settings['overlay']             = isset( $this->props['overlay'] ) ? $this->props['overlay'] : ( $overlay_attr['overlay'] ?? 'off' );
			$type_settings['overlay_icon']        = isset( $this->props['overlay_icon'] ) ? $this->props['overlay_icon'] : ( $overlay_attr['overlay_icon'] ?? 'off' );
			$type_settings['overlay_icon_reveal'] = isset( $this->props['overlay_icon_reveal'] ) ? $this->props['overlay_icon_reveal'] : ( $overlay_attr['overlay_icon_reveal'] ?? 'df-fade-up' );
			$overlay_icon_on = ( isset( $this->props['overlay_icon'] ) && $this->props['overlay_icon'] === 'on' ) || ( $overlay_attr['overlay_icon'] ?? '' ) === 'on';
			$type_settings['overlay_font_icon']   = $overlay_icon_on
				? ( isset( $this->props['overlay_font_icon'] ) && $this->props['overlay_font_icon'] !== '' ? $this->props['overlay_font_icon'] : ( $overlay_attr['overlay_font_icon'] ?? '%%16%%' ) )
				: '%%16%%';

			// custom text
			$type_settings['custom_text']      = $this->props['custom_text'];
			$type_settings['add_to_cart_text'] = ! empty( $this->props['add_to_cart_text'] ) ? $this->props['add_to_cart_text'] : 'Add To Cart';
			$type_settings['use_only_icon']    = $this->props['use_only_icon'];

			// background
			$type_settings['background_enable_mask_style']    = isset( $this->props['background_enable_mask_style'] ) ? $this->props['background_enable_mask_style'] : 'off';
			$type_settings['background_enable_pattern_style'] = isset( $this->props['background_enable_pattern_style'] ) ? $this->props['background_enable_pattern_style'] : 'off';

		}

		if ( $this->props['outside_wrapper'] === 'on' ) {
			$df_product_items_outside[] = $type_settings;
		} else if ( ! empty( $this->props['type'] ) && 'select' !== $this->props['type'] ) {
			$df_product_items[] = $type_settings;
		}

		$df_product_element = [
			'df_product_items'         => $df_product_items,
			'df_product_items_outside' => $df_product_items_outside
		];

		return;
	}

	/**
	 * Process transform options.
	 *
	 * @param string $function_name Function name.
	 *
	 * @since 4.6.0 Add sticky style support.
	 */
	public function process_transform( $function_name ) {

		$transform = self::$_->array_get( $this->advanced_fields, 'transform', [] );

		if ( false === $transform || ! is_array( $transform ) ) {
			return;
		}

		// @codingStandardsIgnoreLine
		$selector = self::$_->array_get( $transform, 'css.main', '%%order_class%%' );

		/**
		 * The "a" element of the button module is the one that should be
		 * scaled and not its wrapper
		 */
		if ( 'et_pb_button' === $function_name ) {
			$selector .= ' a';
		}

		$important             = self::$_->array_get( $transform, 'css.important', false );
		$hover                 = et_pb_hover_options();
		$sticky                = et_pb_sticky_options();
		$is_hover_enabled      = $hover->is_enabled( 'transform_styles', $this->props );
		$is_sticky_enabled     = $sticky->is_enabled( 'transform_styles', $this->props );
		$is_responsive_enabled = isset( $this->props['transform_styles_last_edited'] ) && et_pb_get_responsive_status( $this->props['transform_styles_last_edited'] );
		$responsive_direction  = isset( $this->props['animation_direction_last_edited'] ) && et_pb_get_responsive_status( $this->props['animation_direction_last_edited'] );
		$animation_type        = self::$_->array_get( $this->props, 'animation_style', 'none' );

		/**
		 * Transform instance.
		 *
		 * @var ET_Builder_Module_Field_Transform $class Transform field class instance.
		 */
		$class = ET_Builder_Module_Fields_Factory::get( 'Transform' );
		$class->set_props( $this->props + [ 'transforms_important' => $important ] );
		$position_locations = $this->get_position_locations();

		$is_enabled = $this->_features_manager->get(
		// Transforms is enabled.
			'tra',
			function () use ( $class, $position_locations ) {
				return $class->is_used( $this->props, $position_locations );
			}
		);

		if ( ! $is_enabled ) {
			return;
		}

		$views = [ 'desktop' ];
		if ( $is_hover_enabled || isset( $position_locations['hover'] ) ) {
			array_push( $views, 'hover' );
		}

		if ( $is_sticky_enabled || isset( $position_locations['sticky'] ) ) {
			array_push( $views, 'sticky' );
		}

		if ( $is_responsive_enabled || ( 'none' !== $animation_type && $responsive_direction )
		     || ( isset( $position_locations['hover'] ) || isset( $position_locations['phone'] ) ) ) {
			array_push( $views, 'tablet', 'phone' );
		}
		foreach ( $views as $view ) {
			$view_selector = $selector;
			$device        = $view;
			if ( ! $is_responsive_enabled && in_array( $view, [
					'phone',
					'tablet'
				], true ) || ( 'hover' === $view && ! $is_hover_enabled ) || ( 'sticky' === $view && ! $is_sticky_enabled ) ) {
				$device = 'desktop';
			}
			$elements    = $class->get_elements( $device );
			$media_query = [];

			if ( 'hover' === $view ) {
				$view_selector = '.df-hover-trigger:hover ' . $selector;
			} elseif ( 'sticky' === $view ) {
				$view_selector = $sticky->add_sticky_to_selectors( $selector, $this->is_sticky_module );
			} elseif ( 'tablet' === $view ) {
				$media_query = [
					'media_query' => self::get_media_query( 'max_width_980' ),
				];
			} elseif ( 'phone' === $view ) {
				$media_query = [
					'media_query' => self::get_media_query( 'max_width_767' ),
				];
			}

			if ( isset( $position_locations[ $view ] ) ) {
				$default_strpos = strpos( $position_locations[ $view ], '_is_default' );
				$location       = $position_locations[ $view ];
				if ( false !== $default_strpos ) {
					$location = substr( $position_locations[ $view ], 0, $default_strpos );
				}
				if ( ! isset( $elements['transform']['translateX'] ) ) {
					if ( in_array( $location, [ 'top_center', 'bottom_center', 'center_center' ], true ) ) {
						$elements['transform']['translateX'] = '-50%';
					} elseif ( 'desktop' !== $view ) {
						$elements['transform']['translateX'] = '0px';
					}
				}
				if ( ! isset( $elements['transform']['translateY'] ) ) {
					if ( in_array( $location, [ 'center_left', 'center_right', 'center_center' ], true ) ) {
						$elements['transform']['translateY'] = '-50%';
					} elseif ( 'desktop' !== $view ) {
						$elements['transform']['translateY'] = '0px';
					}
				}
			}

			if ( ! empty( $elements['transform'] ) || ! empty( $elements['origin'] ) ) {

				if ( 'hover' !== $view && 'sticky' !== $view && ! empty( $animation_type ) && 'none' !== $animation_type && 'fade' !== $animation_type ) {

					$transformed_animation = $class->transformedAnimation( $animation_type, $elements, $function_name, $device );

					if ( ! empty( $transformed_animation ) ) {
						self::set_style( $function_name, $transformed_animation['keyframe'] + $media_query );
						self::set_style( $function_name, $transformed_animation['animationRules'] + $media_query );
						$el_style = [
							            'selector'    => $view_selector,
							            'declaration' => $transformed_animation['declaration'],
							            'priority'    => $this->_style_priority,
						            ] + $media_query;
						self::set_style( $function_name, $el_style );
					}
				} else {
					$declaration = '';
					if ( ! empty( $elements['transform'] ) ) {
						$declaration .= $class->getTransformDeclaration( $elements['transform'], $view );
					}

					if ( ! empty( $elements['origin'] ) ) {
						if ( $important ) {
							array_push( $elements['origin'], '!important' );
						}
						$declaration .= sprintf( 'transform-origin:%s;', implode( ' ', $elements['origin'] ) );
					}

					$el_style = [
						            'selector'    => $view_selector,
						            'declaration' => $declaration,
						            'priority'    => $this->_style_priority,
					            ] + $media_query;
					self::set_style( $function_name, $el_style );

					// Flag sticky module that uses transform uses classname on module wrapper
					// so its offset calculation can be adjusted (jQuery offsets() gets the edge
					// of transformed module so its offset will be outside its parent).
					if ( 'sticky' !== $view && $this->is_sticky_module ) {
						$this->add_classname( 'et_pb_sticky--has-transform' );
					}
				}
			}
		}
	}
}
