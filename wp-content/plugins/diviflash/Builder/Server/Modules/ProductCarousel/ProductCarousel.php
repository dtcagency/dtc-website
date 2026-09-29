<?php

namespace DIFL\Server\Modules\ProductCarousel;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Server\Traits\ProductUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;

class ProductCarousel implements DependencyInterface {
	use ModuleUtil;
	use HasProps;
	use StyleUtil;
	use ProductUtil;

	protected $config_file = 'ProductCarousel';

	protected $translate_values = [
		'top_left'      => 'top: 0px !important; left: 0 !important; transform: none !important;',
		'top_center'    => 'top: 0px !important; left: 50% !important; transform: translateX(-50%) !important;',
		'top_right'     => 'top:0px !important; left: 100% !important; transform: translate(-100%) !important;',
		'center_left'   => 'left: 0px !important; top: 50% !important; transform: translateY(-50%) !important;',
		'center_center' => 'left: 50% !important; top:50% !important; transform: translate(-50%, -50%) !important;',
		'center_right'  => 'left: 100% !important; top: 50% !important; transform: translate(-100%, -50%) !important;',
		'bottom_left'   => 'left:0px !important; top: 100% !important; transform: translateY(-100%) !important;',
		'bottom_center' => 'left: 50% !important; top:100% !important; transform: translate(-50% ,-100%) !important',
		'bottom_right'  => 'left: 100% !important; top: 100% !important; transform: translate(-100% ,-100%) !important;',
	];

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/productgrid')->customCssFields;
    }

	/**
	 * Arrow navigation
	 *
	 * @param Integer | $order_number
	 *
	 * @return String | HTML
	 */
	public function df_pc_arrow( $order_number ) {
		$prev_icon = $this->props['arrow_prev_icon_use_icon'] === 'on' && isset( $this->props['arrow_prev_icon_font_icon'] ) && ! empty( $this->props['arrow_prev_icon_font_icon'] ) ?
			esc_attr( Utils::process_font_icon( $this->props['arrow_prev_icon_font_icon'] ) ) : '4';
		$next_icon = $this->props['arrow_next_icon_use_icon'] === 'on' && isset( $this->props['arrow_next_icon_font_icon'] ) && ! empty( $this->props['arrow_next_icon_font_icon'] ) ?
			esc_attr( Utils::process_font_icon( $this->props['arrow_next_icon_font_icon'] ) ) : '5';

		return $this->props['arrow'] === 'on' ? sprintf( '
            <div class="df_pc_arrows">
                <div class="swiper-button-next bc-next-%1$s" data-icon="%3$s"></div>
                <div class="swiper-button-prev bc-prev-%1$s" data-icon="%2$s"></div>
            </div>
        ', $order_number, $prev_icon, $next_icon ) : '';
	}

	/**
	 * Dot pagination
	 *
	 * @param Integer | $order_number
	 *
	 * @return String | HTML
	 */
	public function df_pc_dots( $order_number ) {
		$dosts_style_class       = '';
		$active_dot_border_style = '';
		if ( isset( $this->props['dots_style_type'] ) && $this->props['dots_style_type'] !== '' ) {
			$dosts_style_class = 'dots_style_' . $this->props['dots_style_type'];
		}
		if ( isset( $this->props['active_dot_border_style_enable'] ) && $this->props['active_dot_border_style_enable'] === 'on' ) {
			$active_dot_border_style .= 'active_dot_border_style';
		}

		return $this->props['dots'] === 'on' ?
			sprintf( '<div class="swiper-pagination bc-dots-%1$s %2$s %3$s"></div>', $order_number, $dosts_style_class, $active_dot_border_style ) : '';
	}

	/**
	 * Arrow Position styles
	 *
	 * @param String | position
	 *
	 * @return String
	 */
	public function df_arrow_pos_styles( $value = 'middle' ) {
		$options = [
			'top'    => 'position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 0;',
			'middle' => 'position: absolute;
                        top: 50%;
                        left: 0;
                        right: 0;
                        transform: translateY(-50%);',
			'bottom' => 'position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 2;',
		];

		return $options[ $value ];
	}

	/**
	 * Get products for productCarousel module
	 *
	 * @return string  product markup
	 */
	public function df_get_products() {
		global $product, $paged, $wp_query, $wp_the_query, $wp_filter, $__et_blog_module_paged, $df_product_element, $df_product_items, $df_product_items_outside;
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$error_notice = "<div style='color:red'>" . esc_html__( "Please Before Using the Diviflash $this->name Module, you need to Activate and configure the WooCommerce Plugin", 'divi_flash' ) . '</div>';

			return $error_notice;
		}
		if ( empty( $df_product_items ) && empty( $df_product_items_outside ) ) {
			$shop = '';

			return $shop;
		}
		$post_id            = isset( $current_page['id'] ) ? (int) $current_page['id'] : 0;
		$type               = $this->props['type'];
		$related            = $this->props['related'];
		$posts_number       = $this->props['posts_number'];
		$orderby            = $this->props['orderby'];
		$order              = 'ASC';
		$product_categories = [];
		$product_tags       = [];
		$use_current_loop   = 'on' === $this->props['use_current_loop'];
		$use_current_loop   = $use_current_loop && ( is_post_type_archive( 'product' ) || is_search() || et_is_product_taxonomy() );
		$product_attribute  = '';
		$product_terms      = [];
		if ( $use_current_loop ) {
			$this->props['include_categories'] = 'all';

			if ( is_product_category() ) {
				$this->props['include_categories'] = (string) get_queried_object_id();
			} elseif ( is_product_tag() ) {
				$product_tags = [ get_queried_object()->slug ];
			} elseif ( is_product_taxonomy() ) {
				$term = get_queried_object();

				// Product attribute taxonomy slugs start with pa_ .
				if ( et_()->starts_with( $term->taxonomy, 'pa_' ) ) {
					$product_attribute = $term->taxonomy;
					$product_terms[]   = $term->slug;
				}
			}
		}

		if ( 'product_category' === $type || ( $use_current_loop && ! empty( $this->props['include_categories'] ) ) ) {
			$all_shop_categories = et_builder_get_shop_categories();


			$all_shop_categories_map = [];
			$raw_product_categories  = self::filter_include_categories( $this->props['include_categories'], $post_id, 'product_cat' );


			foreach ( $all_shop_categories as $term ) {
				if ( is_object( $term ) && is_a( $term, 'WP_Term' ) ) {
					$all_shop_categories_map[ $term->term_id ] = $term->slug;
				}
			}

			$product_categories = array_values( $all_shop_categories_map );

			if ( ! empty( $raw_product_categories ) ) {
				$product_categories = array_intersect_key(
					$all_shop_categories_map,
					array_flip( $raw_product_categories )
				);
			}
		}

		// Recent was the default option in Divi once, so it is added here for the websites created before the change.
		if ( 'default' === $orderby && ( 'default' === $type || 'recent' === $type ) ) {
			// Leave the attribute empty to allow WooCommerce to take over and use the default sorting.
			$orderby = '';
		}
		if ( 'latest' === $type ) {
			$orderby = 'date-desc';
		}

		if ( in_array( $orderby, [ 'price-desc', 'date-desc' ], true ) ) {
			// Supported orderby arguments (as defined by WC_Query->get_catalog_ordering_args() ):
			// rand | date | price | popularity | rating | title .
			$orderby = str_replace( '-desc', '', $orderby );
			// Switch to descending order if orderby is 'price-desc' or 'date-desc'.
			$order = 'DESC';
		}

		$ids = [];
		if ( 'on' === $related ) {
			$product_id = $wp_query->queried_object->ID;
			$ids = wc_get_related_products( $product_id );
		}
		$wc_custom_view  = '';
		$wc_custom_views = [
			'sale'         => [ 'on_sale', 'true' ],
			'best_selling' => [ 'best_selling', 'true' ],
			'top_rated'    => [ 'top_rated', 'true' ],
			'featured'     => [ 'visibility', 'featured' ],
		];

		if ( et_()->includes( array_keys( $wc_custom_views ), $type ) ) {
			$custom_view_data = $wc_custom_views[ $type ];
			$wc_custom_view   = sprintf( '%1$s="%2$s"', esc_attr( $custom_view_data[0] ), esc_attr( $custom_view_data[1] ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- reason wp_nonce is not required here as data from get requests go through something like "whitelisting" via `in_array` function.
		$request_orderby_value = et_()->array_get_sanitized( $_GET, 'orderby', '' );
		$shop_fields           = $this->attributes;
		// Checking if there is an orderby parameter in the GET-request and is its value is defined in the options via $this->get_fields() and contains `price` value.
		$maybe_fields_has_orderby_options           = ! empty( $shop_fields ) && isset( $shop_fields['orderby']['options'] );
		$maybe_request_price_value_in_order_options = ! empty( $request_orderby_value ) && $maybe_fields_has_orderby_options && in_array( $request_orderby_value, array_keys( $shop_fields['orderby']['options'] ), true ) && false !== strpos( strtolower( $request_orderby_value ), 'price' );
		if ( $maybe_request_price_value_in_order_options ) {
			$orderby = 'price';
			$order   = false !== strpos( strtolower( $request_orderby_value ), 'desc' ) ? 'DESC' : 'ASC';
		}

		$options            = [
			'module_type'                => 'carousel',
			'show_badge'                 => $this->props['show_badge'],
			'show_badge_in_image'        => $this->props['show_badge_in_image'],
			'on_sale_text'               => $this->props['on_sale_text'],
			'after_sale_text_enable'     => $this->props['after_sale_text_enable'],
			'after_sale_text'            => $this->props['after_sale_text'],
			'after_sale_text_type'       => $this->props['after_sale_text_type'],
			'enable_custom_soldout_text' => $this->props['enable_custom_soldout_text'],
			'custom_soldout_text'        => $this->props['custom_soldout_text'],
			'equal_height'               => $this->props['equal_height'],
		];
		$df_product_element = [
			'df_product_items'         => $df_product_items,
			'df_product_items_outside' => $df_product_items_outside,
		];

		do_action( 'df_pg_before_print', $df_product_element, $options );
		add_filter( 'post_class', 'df_product_carousel_class_add', 10, 3 );
		$shortcode = sprintf(
			'[products class="swiper-container"  columns="4" %1$s limit="%2$s" orderby="%3$s" %5$s order="%6$s" %7$s %8$s %9$s %10$s %11$s]',
			et_core_intentionally_unescaped( $wc_custom_view, 'fixed_string' ),
			esc_attr( $posts_number ),
			esc_attr( $orderby ),
			'',
			$product_categories ? sprintf( 'category="%s"', esc_attr( implode( ',', $product_categories ) ) ) : '',
			esc_attr( $order ),
			'',
			$ids ? sprintf( 'ids="%s"', esc_attr( implode( ',', $ids ) ) ) : '',
			$product_tags ? sprintf( 'tag="%s"', esc_attr( implode( ',', $product_tags ) ) ) : '',
			$product_attribute ? sprintf( 'attribute="%s"', esc_attr( $product_attribute ) ) : '',
			$product_terms ? sprintf( 'terms="%s"', esc_attr( implode( ',', $product_terms ) ) ) : ''
		);
		$shop      = do_shortcode( $shortcode );

		do_action( 'df_pg_after_print', $df_product_element, $options );
		$df_product_items         = [];
		$df_product_items_outside = [];
		$df_product_element       = [];

		return $shop;
	}

	public function render( $attrs, $content, $render_slug ) {

		wp_enqueue_script( 'swiper-script' );
		wp_enqueue_script( 'df-product-carousel' );

		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );
		$order_class  = $this->order_class;
		$order_number = str_replace( '_', '', str_replace( '', '', $order_class ) );
		$class        = '';

		$data = [
			'effect'              => $this->props['carousel_type'],
			'desktop'             => $this->props['item_desktop'],
			'tablet'              => $this->props['item_tablet'],
			'mobile'              => $this->props['item_mobile'],
			'loop'                => $this->props['loop'] === 'on' ? true : false,
			'item_spacing'        => $attrs['item_spacing']['innerContent']['desktop']['value'] ?? $this->props['item_spacing'],
			'item_spacing_tablet' => $attrs['item_spacing']['innerContent']['tablet']['value'] ?? $this->props['item_spacing_tablet'],
			'item_spacing_phone'  => $attrs['item_spacing']['innerContent']['phone']['value'] ?? $this->props['item_spacing_phone'],
			'arrow'               => $this->props['arrow'],
			'dots'                => $this->props['dots'],
			'autoplay'            => $this->props['autoplay'],
			'auto_delay'          => $this->props['autospeed'],
			'speed'               => $this->props['speed'],
			'pause_hover'         => $this->props['pause_hover'],
			'centeredSlides'      => $this->props['centered_slides'],
			'order'               => $order_number,
		];
		if ( $this->props['carousel_type'] === 'coverflow' ) {
			$data['slideShadows'] = $this->props['coverflow_shadow'];
			$data['rotate']       = $this->props['coverflow_rotate'];
			$data['stretch']      = $this->props['coverflow_stretch'];
			$data['depth']        = $this->props['coverflow_depth'];
			$data['modifier']     = $this->props['coverflow_modifier'];
		}

		// arrow position classes
		if ( $this->props['arrow'] === 'on' ) {
			$arrow_position = '' !== $this->props['arrow_position'] ? $this->props['arrow_position'] : 'middle';
			$class          .= ' arrow-' . $arrow_position;
		}


		return sprintf( '<div class="df_product_carousel_container%8$s" data-settings=\'%2$s\' data-item="%5$s" data-itemtablet="%6$s" data-itemphone="%7$s">
            %1$s%3$s%4$s
        </div>',
			$this->df_get_products(),
			wp_json_encode( $data ),
			$this->df_pc_arrow( $order_number ),
			$this->df_pc_dots( $order_number ),
			$this->props['item_desktop'],
			$this->props['item_tablet'],
			$this->props['item_mobile'],
			$class
		);
	}

	public function add_new_child_text() {
		return esc_html__( 'Add New Product Element', 'divi_flash' );
	}

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = $this->get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';
        $all_props  = $this->props;

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );


		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .swiper-wrapper",
			'attr'                => $attrs['type']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				return 'width: fit-content;';
			},
		] );


		if ( isset( $all_props['equal_height'] ) && $all_props['equal_height'] === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} ul.df-products li.product",
				'attr'                => $attrs['equal_height']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					return 'align-self: auto !important; height: auto !important;';
				},
                'important' => true
			] );
		}


		if ( isset( $all_props['coverflow_shadow'] ) && $all_props['coverflow_shadow'] === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .swiper-container-3d .swiper-slide-shadow-left",
				'attr'                => $attrs['coveflow_color_dark']['innerContent'] ?? null,
				'declarationFunction' => function ($props) use ( $all_props ) {
                    $dark  = $this->props['coveflow_color_dark'] ?? '';
					$light = $this->props['coveflow_color_light'] ?? '';

					return "background-image: linear-gradient(to left,{$dark},{$light}) !important;";
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .swiper-container-3d .swiper-slide-shadow-right",
				'attr'                => $attrs['coveflow_color_light']['innerContent'] ?? null,
				'declarationFunction' => function ($props) use ( $all_props ) {
					$dark  = $this->props['coveflow_color_dark'] ?? '';
					$light = $this->props['coveflow_color_light'] ?? '';

					return "background-image: linear-gradient(to right,{$dark},{$light}) !important;";
				},
			] );
		}


		if ( isset( $all_props['show_badge'] ) && $all_props['show_badge'] === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} span.df-onsale:not(.df-sale-badge)",
				'attr'                => $attrs['show_badge']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					return 'display: none;';
				},
			] );
		}


		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df-product-inner-wrap",
			'attr'      => $attrs['alignment']['innerContent'] ?? null,
			'property'  => 'text-align',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .woocommerce ul.df-products li.product .df-sale-badge.df-onsale",
			'attr'      => $attrs['badge_placement']['innerContent'] ?? null,
			'property'  => 'text-align',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .woocommerce ul.df-products li.product .df-sale-badge.df-onsale",
			'attr'                => $attrs['badge_placement']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$attrValue = $value['attrValue'] ?? '';

				return $this->translate_values[ $attrValue ] ?? '';
			},
		] );

		if ( isset( $all_props['arrow'] ) && $all_props['arrow'] === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df_pc_arrows",
				'attr'                => $attrs['arrow_position']['innerContent'] ?? null,
				'declarationFunction' => function ($props) use ( $all_props ) {
					return $this->arrow_pos_styles( $props['attrValue'] );
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df_pc_arrows",
				'attr'                => $attrs['arrow_align']['innerContent'] ?? null,
				'declarationFunction' => function ($props) use ( $all_props ) {
					return "justify-content: {$props['attrValue']};";
				},
			] );


			if ( isset( $all_props['arrow_circle'] ) && $all_props['arrow_circle'] === 'on' ) {
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} .df_pc_arrows > div",
					'attr'                => $attrs['arrow_circle']['innerContent'] ?? null,
					'declarationFunction' => function () {
						return 'border-radius: 50%;';
					},
				] );
			}

			$common_styles[] = CommonStyle::style( [
				'selector' => "{$orderClass} .df_pc_arrows div:after",
				'attr'     => $attrs['arrow_color']['innerContent'] ?? null,
				'property' => 'color',
			] );

			$common_styles[] = CommonStyle::style( [
				'selector' => "{$orderClass} .df_pc_arrows div",
				'attr'     => $attrs['arrow_background']['innerContent'] ?? null,
				'property' => 'background-color',
			] );

			$common_styles[] = CommonStyle::style( [
				'selector' => "{$orderClass} .df_pc_arrows div",
				'attr'     => $attrs['arrow_opacity']['innerContent'] ?? null,
				'declarationFunction' => function ($props) use ( $all_props ) {
                    $opacity = $props['attrValue'] ?? 0;

                    return "opacity: {$opacity}";
                },
			] );


			if ( isset( $all_props['loop'] ) && $all_props['loop'] === 'off' ) {
				$common_styles[] = CommonStyle::style( [
					'selector' => "{$orderClass} .df_pc_arrows div.swiper-button-disabled",
					'attr'     => $attrs['arrow_opacity_disable']['innerContent'] ?? null,
                    'declarationFunction' => function ($props) use ( $all_props ) {
                        $opacity = $props['attrValue'] ?? 0;

                        return "opacity: {$opacity}";
                    },
				] );
			}

			if ( isset( $all_props['disable_arrow_design'] ) && $all_props['disable_arrow_design'] === 'on' ) {
				$common_styles[] = CommonStyle::style( [
					'selector' => "{$orderClass} .df_pc_arrows div.swiper-button-disabled:after",
					'attr'     => $attrs['disable_arrow_color']['innerContent'] ?? null,
					'property' => 'color',
					'important' => true,
				] );
				$common_styles[] = CommonStyle::style( [
					'selector' => "{$orderClass} .df_pc_arrows div.swiper-button-disabled",
					'attr'     => $attrs['disable_arrow_background']['innerContent'] ?? null,
					'property' => 'background-color',
					'important' => true,
				] );
			}


			if ( ( $all_props['arrow_prev_icon_use_icon'] ?? '' ) === 'on' ) {
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} .df_pc_arrows div.swiper-button-prev:after",
					'attr'                => $attrs['arrow_prev_icon_font_icon']['innerContent'] ?? null,
					'declarationFunction' => function () use ( $all_props ) {
						$icon = $all_props['arrow_prev_icon_font_icon'] ?? '';

						return $this->get_font_style( $icon );
					},
				] );
			}

			if ( ( $all_props['arrow_next_icon_use_icon'] ?? '' ) === 'on' ) {
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} .df_pc_arrows div.swiper-button-next:after",
					'attr'                => $attrs['arrow_next_icon_font_icon']['innerContent'] ?? null,
					'declarationFunction' => function () use ( $all_props ) {
						$icon = $all_props['arrow_next_icon_font_icon'] ?? '';

						return $this->get_font_style( $icon );
					},
				] );
			}
		}

		if ( isset( $all_props['dots'] ) && $all_props['dots'] === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .swiper-pagination",
				'attr'                => $attrs['dots']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $all_props ) {
					$position = $all_props['dots_position'] ?? '';

					return $this->arrow_pos_styles( $position );
				},
			] );

            if ( isset( $all_props['custom_dot_style_enable'] ) && $all_props['custom_dot_style_enable'] === 'on' ) {
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet",
					'attr'                => $attrs['custom_dot_width']['innerContent'] ?? null,
					'declarationFunction' => function ($value) {
                        $custom_dot_width = $value['attrValue'];
						return "height: $custom_dot_width !important;width: $custom_dot_width !important;";
					},
                    'important' => true
				] );

                if ( isset( $all_props['active_dot_border_style_enable'] ) && $all_props['active_dot_border_style_enable'] === 'on' ) {
                    $common_styles[] = CommonStyle::style( [
                        'selector'            => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet",
                        'attr'                => $attrs['custom_dot_width']['innerContent'] ?? null,
                        'declarationFunction' => function ($value) {
                            $custom_dot_width = $value['attrValue'];
                            return "margin-right: $custom_dot_width !important;";
                        },
                        'important' => true
                    ] );

                }
			}


			if ( isset( $all_props['large_active_dot'] ) && $all_props['large_active_dot'] === 'on' ) {
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet-active",
					'attr'                => $attrs['large_active_dot']['innerContent'] ?? null,
					'declarationFunction' => function () {
						return 'border-radius: 20px !important;';
					},
                    'important' => true
				] );
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet-active",
					'attr'                => $attrs['large_active_dot']['innerContent'] ?? null,
					'declarationFunction' => function () {
						return 'width: 40px !important;';
					},
				] );
                $common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet-active",
					'attr'                => $attrs['large_active_dot_width']['innerContent'] ?? null,
					'declarationFunction' => function ($value) {
                        $width = $value['attrValue'] ?? $this->props['large_active_dot_width'];
						return "width: $width !important;";
					},
                    'important' => true
				] );
			}


			if ( isset( $all_props['dots_align'] ) && $all_props['dots_align'] ) {
				$common_styles[] = CommonStyle::style( [
					'selector' => "{$orderClass} .swiper-pagination",
					'attr'     => $attrs['dots_align']['innerContent'] ?? null,
					'property' => 'text-align',
				] );
			}


			$common_styles[] = CommonStyle::style( [
				'selector' => "{$orderClass} .swiper-pagination span",
				'attr'     => $attrs['dots_color']['innerContent'] ?? null,
				'property' => 'background',
			] );

			$common_styles[] = CommonStyle::style( [
				'selector' => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet-active",
				'attr'     => $attrs['active_dots_color']['innerContent'] ?? null,
				'property' => 'background',
			] );
			$common_styles[] = CommonStyle::style( [
				'selector' => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet-active",
				'attr'     => $attrs['active_dots_color']['innerContent'] ?? null,
				'property' => 'color',
                'important' => true
			] );
		}

		if ( ( $this->props['arrow_prev_icon_use_icon'] ?? '' ) === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_pc_arrows .swiper-button-prev:after",
				'attr'      => $attrs['arrow_prev_icon_icon_size']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					$icon_font_size = $props['attrValue'] ?? $this->props['arrow_prev_icon_icon_size'];

					return "font-size: $icon_font_size";
				},
				'important' => true,
			] );
		}

		if ( ( $this->props['arrow_next_icon_use_icon'] ?? '' ) === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_pc_arrows .swiper-button-next:after",
				'attr'      => $attrs['arrow_next_icon_icon_size']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					$icon_font_size = $props['attrValue'] ?? $this->props['arrow_next_icon_icon_size'];

					return "font-size: $icon_font_size";
				},
				'important' => true,
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df-product-outer-wrap",
			'attr'      => $attrs['outer_wrpper_visibility']['innerContent'] ?? null,
			'property'  => 'overflow',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df-product-inner-wrap",
			'attr'      => $attrs['inner_wrpper_visibility']['innerContent'] ?? null,
			'property'  => 'overflow',
			'important' => true,
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

	public function arrow_pos_styles( $value = 'middle' ) {
		$options = [
			'top'    => 'position: relative;
                top: auto;
                left: auto;
                right: auto;
                transform: translateY(0);
                order: 0;',
			'middle' => 'position: absolute;
                    top: 50%;
                    left: 0;
                    right: 0;
                    transform: translateY(-50%);',
			'bottom' => 'position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 1;',
		];

		return $options[ $value ] ?? '';
	}

}
