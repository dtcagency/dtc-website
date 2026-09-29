<?php

namespace DIFL\Server\Modules\ProductGrid;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Server\Traits\ProductUtil;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;

class ProductGrid implements DependencyInterface {
	use ModuleUtil;
	use HasProps;
	use ProductUtil;

	protected $config_file = 'ProductGrid';

	public $slug = 'difl_productgrid';
	public $vb_support = 'on';
	public $child_slug = 'difl_productitem';
	public $icon_path;

	protected $module_credits = [
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	];

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
	 * Get products for productgrid module
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
		$post_id = isset( $current_page['id'] ) ? (int) $current_page['id'] : 0;

		$related      = $this->props['related'];
		$type         = $this->props['type'];
		$posts_number = $this->props['posts_number'];
		$orderby      = $this->props['orderby'];
		$order        = 'ASC';
		$columns      = $this->props['column'];

		$pagination         = ( $this->props['show_pagination'] === 'on' ) ? true : false;
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

		if ( 'default_order' === $orderby && ( 'default' === $type || 'recent' === $type ) ) {
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
			$ids = wc_get_related_products( $product_id );		}

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
			'show_badge'                 => $this->props['show_badge'],
			'show_badge_in_image'        => $this->props['show_badge_in_image'],
			'on_sale_text'               => $this->props['on_sale_text'],
			'after_sale_text_enable'     => $this->props['after_sale_text_enable'],
			'after_sale_text_type'       => $this->props['after_sale_text_type'],
			'after_sale_text'            => $this->props['after_sale_text'],
			'enable_custom_soldout_text' => $this->props['enable_custom_soldout_text'],
			'custom_soldout_text'        => $this->props['custom_soldout_text'],
			'next_prev_icon'             => $this->props['next_prev_icon'],
			'layout'                     => $this->props['layout'],
			'equal_height'               => $this->props['equal_height'],
		];
		$df_product_element = [
			'df_product_items'         => $df_product_items,
			'df_product_items_outside' => $df_product_items_outside,
		];

		do_action( 'df_pg_before_print', $df_product_element, $options );
		add_filter( 'woocommerce_pagination_args', [ $this, 'pagination_args' ], 10, 2 );

		if ( 'default_order' === $orderby ) {
			add_filter( 'woocommerce_default_catalog_orderby', [ $this, 'set_default_orderby' ] );
		}

		if ( 'product_category' === $type || $use_current_loop ) {
			add_filter( 'woocommerce_shortcode_products_query', [ $this, 'filter_products_query' ] );
		}

		$shortcode = sprintf(
			'[products columns="%4$s" %1$s limit="%2$s" orderby="%3$s" %5$s order="%6$s" %7$s %8$s %9$s %10$s %11$s]',
			et_core_intentionally_unescaped( $wc_custom_view, 'fixed_string' ),
			esc_attr( $posts_number ),
			esc_attr( $orderby ),
			esc_attr( $columns ),
			$product_categories ? sprintf( 'category="%s"', esc_attr( implode( ',', $product_categories ) ) ) : '',
			esc_attr( $order ),
			$pagination ? 'paginate="true"' : '',
			$ids ? sprintf( 'ids="%s"', esc_attr( implode( ',', $ids ) ) ) : '',
			$product_tags ? sprintf( 'tag="%s"', esc_attr( implode( ',', $product_tags ) ) ) : '',
			$product_attribute ? sprintf( 'attribute="%s"', esc_attr( $product_attribute ) ) : '',
			$product_terms ? sprintf( 'terms="%s"', esc_attr( implode( ',', $product_terms ) ) ) : ''
		);
		$shop      = do_shortcode( $shortcode );

		do_action( 'df_pg_after_print', $df_product_element, $options );
		remove_filter( 'woocommerce_pagination_args', [ $this, 'pagination_args' ], 10 );

		if ( 'default_order' === $orderby ) {
			remove_filter( 'woocommerce_default_catalog_orderby', [ $this, 'set_default_orderby' ] );
		}

		if ( 'product_category' === $type || $use_current_loop ) {
			remove_filter( 'woocommerce_shortcode_products_query', [ $this, 'filter_products_query' ] );
		}

		$df_product_items         = [];
		$df_product_items_outside = [];
		$df_product_element       = [];

		return $shop;
	}

	/**
	 * Set correct default value for the orderby menu depending on module settings.
	 *
	 * @param string $default_orderby default orderby value from woocommerce settings.
	 *
	 * @return string updated orderby value for current module
	 */
	public function set_default_orderby( $default_orderby ) {
		$orderby = $this->props['orderby'];

		if ( '' === $orderby || 'default_order' === $orderby ) {
			return $default_orderby;
		}

		// Should check this explicitly since it's the only option which supports '-desc' suffix.
		if ( 'price-desc' === $orderby ) {
			return 'price-desc';
		}

		// Remove '-desc' suffix from other options where Divi may add it.
		$orderby = str_replace( '-desc', '', $orderby );

		return $orderby;
	}

	/**
	 * Filter the products query arguments.
	 *
	 * @param array $query_args Query array.
	 *
	 * @return array
	 */
	public function filter_products_query( $query_args ) {
		if ( is_search() ) {
			$query_args['s'] = get_search_query();
		}

		if ( function_exists( 'WC' ) ) {
			$query_args['meta_query'] = WC()->query->get_meta_query( et_()->array_get( $query_args, 'meta_query', [] ), true );
			$query_args['tax_query']  = WC()->query->get_tax_query( et_()->array_get( $query_args, 'tax_query', [] ), true );
			$query_args['nocache']    = microtime( true );
		}

		return $query_args;
	}

    public function get_common_styles( $args ) {
        $attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
        $orderClass    = $args['orderClass'] ?? '';
        $elements      = $args['elements'];
        $styles        = [];
		$common_styles = [];

        $common_styles[] = CssStyle::style([
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ]);

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .woocommerce .woocommerce-result-count",
            'attr'                => $attrs['show_pagination_result_count']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $show_result_count = $props['attrValue'] ?? $this->props['show_pagination_result_count'];

                if("off" === $show_result_count) {
                    return "display: none;";
                }
            }
        ] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .woocommerce .woocommerce-ordering",
            'attr'                => $attrs['show_pagination_sorting']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $show_pagination_sorting = $props['attrValue'] ?? $this->props['show_pagination_sorting'];

                if("off" === $show_pagination_sorting) {
                    return "display: none;";
                }
            }
        ] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .woocommerce ul.products li.product, {$orderClass} .woocommerce-page ul.products li.product",
            'attr'                => $attrs['layout']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                return "width: 100% !important;";
            }
        ] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .woocommerce ul.products li.product",
            'attr'                => $attrs['gutter']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $gutter = ($props['attrValue'] ?? $this->props['gutter']) ?? '20';
                $gutter = intval( $gutter );
                $gutter_calc_space = $gutter / 2;

                if($gutter > 0) {
                    return sprintf( 'padding-left: %1$spx !important; padding-right: %1$spx !important; padding-bottom: %2$spx !important;',
                        $gutter_calc_space, intval( $gutter )
                    );
                }

            },
            'important' => true
        ] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .woocommerce ul.products",
            'attr'                => $attrs['gutter']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $gutter = $props['attrValue'] ?? $this->props['gutter'];
                $gutter_calc_space = intval( $gutter ) / 2;

                return sprintf( 'margin-left: -%1$spx !important; margin-right: -%1$spx !important; padding: 0 !important;', $gutter_calc_space );
            },
            'important' => true
        ] );

        if('masonry' !== $this->props['layout']) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .woocommerce ul.products",
                'attr'                => $attrs['column']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $column = $props['attrValue'] ?? $this->props['column'];
                    $column = intval($column);

                    return "display: grid;grid-template-columns: repeat($column, 1fr) !important;justify-content: space-between;margin: 0;";
                }
            ] );
        }else {
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .woocommerce ul.products li.product",
                'attr'                => $attrs['column']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $column = $props['attrValue'] ?? $this->props['column'];
                    $column = 100 / intval($column) . '%';

                    return sprintf('width: %1$s !important;', $column);
                },
                'important' => true
            ] );
        }

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .df-product-inner-wrap",
            'attr'                => $attrs['alignment']['innerContent'] ?? [],
            'property'            => 'text-align',
            'important'           => true
        ] );

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

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .woocommerce ul.products li.product .df-sale-badge.df-onsale",
            'attr'                => $attrs['badge_placement']['innerContent'] ?? [],
            'property'            => 'text-align',
            'important'           => true
        ] );

        $common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .woocommerce ul.products li.product .df-sale-badge.df-onsale",
			'attr'                => $attrs['badge_placement']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$attrValue = $value['attrValue'] ?? '';

				return $this->translate_values[ $attrValue ] ?? '';
			},
		] );


        if('on' === $this->props['show_pagination']) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .woocommerce nav.woocommerce-pagination",
                'attr'                => $attrs['pagination_align']['innerContent'] ?? [],
                'property'            => 'text-align',
				'important'           => true
            ] );
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .woocommerce nav.woocommerce-pagination ul li .prev, {$orderClass} .woocommerce nav.woocommerce-pagination ul li .next",
                'attr'                => $attrs['next_prev_icon_color']['innerContent'] ?? [],
                'property'            => 'color',
				'important'           => true
            ] );
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .woocommerce nav.woocommerce-pagination ul li .page-numbers span.et-pb-icon",
                'attr'                => $attrs['next_prev_icon_size']['innerContent'] ?? [],
                'property'            => 'font-size',
				'important'           => true
            ] );
        }

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

		wp_enqueue_script( 'imageload' );
		wp_enqueue_script( 'df-imagegallery-lib' );
		wp_enqueue_script( 'df-products' );

		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );
		$layout = $this->props['layout'];
		$data   = [
			'layout' => $this->props['layout'],
		];

		return sprintf(
			'<div class="df_productgrid_container et_pb_ajax_pagination_container" data-settings=\'%2$s\'>
                                <div class="df-products-wrap layout-' . $layout . '">
                                    %1$s 
                                </div>
                            </div>',
			$this->df_get_products(),
			wp_json_encode( $data )
		);
	}

	public function add_new_child_text() {
		return esc_html__( 'Add New Product Element', 'divi_flash' );
	}

}
