<?php

namespace DIFL\Server;

use DIFL\Server\Traits\ProductUtil;
use DIFL\Server\Modules\ACFGallery\ACFGallery;
use DIFL\Server\Modules\SVGAnimator\SVGAnimator;
use DIFL\includes\functions\df_jsgallery_functions;
use ET\Builder\Framework\Controllers\RESTController;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\DynamicData\DynamicData;
use DIFL\admin\taxonomies\DiviFlash_Media_Category\DiviFlash_Media_Category;
use ET\Builder\FrontEnd\Module\Style;

class API {
    use ProductUtil;

	const BASE = 'difl/v5';

    protected array $props = [];

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public function register_rest_routes() {
		register_rest_route( self::BASE, '/get-lib-item', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'render_library_layout' ],
			'permission_callback' => function () {
				return true;
			},
			'args'                => [
				'id' => [
					'required'          => false,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				],
			],
		] );

		register_rest_route( self::BASE, '/get-shortcode-item', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'render_shortcode_layout' ],
			'permission_callback' => function () {
				return true;
			},
			'args'                => [
				'shortcode' => [
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => function ( $value ) {
						return is_string( $value ) ? $value : '';
					},
				],
			],
		] );

		register_rest_route( self::BASE, '/get-popup-item', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'render_popup_layout' ],
			'permission_callback' => function () {
				return true;
			},
			'args'                => [
				'id' => [
					'required'          => false,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				],
			],
		] );

		register_rest_route( self::BASE, '/get-acf-image-data', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_acf_image_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/render-svg-animator', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'render_svg_animator' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/dynamic-data', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_dynamic_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-justified-images', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_justified_images' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-packery-images', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_packery_images' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-gravity-form', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_gravity_form' ],
			'permission_callback' => function () {
				return true;
			},
		] );
		register_rest_route( self::BASE, '/get-breadcrumbs', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_breadcrumbs' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-wp-form', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_wp_form' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-cf-form', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_cf_form' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-google-sheet-data', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_google_sheet_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-chart-data', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_chart_data' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		] );

		register_rest_route( self::BASE, '/get-database-data', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_database_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-tablepress-data', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_tablepress_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-advanced-image-data', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_advanced_image_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-advanced-image-category-data', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_advanced_image_category_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-cptgrid-data', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_cptgrid_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-cptcarousel-data', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_cptcarousel_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-cptfilter-data', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_cptfilter_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-productcarousel-data', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_productcarousel_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-productgrid-data', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_productgrid_data' ],
			'permission_callback' => function () {
				return true;
			},
		] );

		register_rest_route( self::BASE, '/get-instagram-images', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_instagram_images' ],
			'permission_callback' => function () {
				return true;
			},
		] );
	}

	public function get_productgrid_data( $data ) {
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$response = [
				'data' => "<div style='color:red'>" . esc_html__( 'Please Before Using the Diviflash Product Grid Module, you need to Activate and configure the WooCommerce Plugin', 'divi_flash' ) . '</div>',
			];

			return RESTController::response_success( $response );
		}
		global $product, $paged, $wp_query, $wp_the_query, $wp_filter, $__et_blog_module_paged;

        // get json data.
        $data_params = $data->get_json_params();
		$product_items      = isset( $data['productItems'] ) ? $data['productItems'] : [];
		$product_item_inner = isset( $product_items['inner'] ) ? $product_items['inner'] : [];
		$product_item_outer = isset( $product_items['outer'] ) ? $product_items['outer'] : [];

		$posts_number            = isset( $data['posts_number'] ) ? intval( $data['posts_number'] ) : 10;
		$type                    = isset( $data['type'] ) ? $data['type'] : 'default';
		$columns                 = isset( $data['column'] ) ? $data['column'] : 'default';
		$order                   = '';
		$orderby                 = isset( $data['orderby'] ) ? $data['orderby'] : '1';
		$include_categories      = isset( $data['include_categories'] ) ? $data['include_categories'] : '';
		$include_tags            = isset( $data['include_tags'] ) ? $data['include_tags'] : '';
		$layout                  = isset( $data['layout'] ) ? $data['layout'] : 'grid';
		$use_image_as_background = isset( $data['use_image_as_background'] ) ? $data['use_image_as_background'] : 'off';
		$equal_height            = isset( $data['equal_height'] ) ? $data['equal_height'] : 'off';
		$pagination_value        = isset( $data['show_pagination'] ) ? $data['show_pagination'] : 'off';
		$pagination              = ( $pagination_value === 'off' ) ? false : true;

		$show_badge                 = isset( $data['show_badge'] ) ? $data['show_badge'] : 'off';
		$show_badge_in_image        = isset( $data['show_badge_in_image'] ) ? $data['show_badge_in_image'] : 'off';
		$on_sale_text               = isset( $data['on_sale_text'] ) ? $data['on_sale_text'] : 'sale!';
		$after_sale_text_enable     = isset( $data['after_sale_text_enable'] ) ? $data['after_sale_text_enable'] : 'off';
		$after_sale_text_type       = isset( $data['after_sale_text_type'] ) ? $data['after_sale_text_type'] : 'price-percentise';
		$after_sale_text            = isset( $data['after_sale_text'] ) ? $data['after_sale_text'] : '';
		$enable_custom_soldout_text = isset( $data['enable_custom_soldout_text'] ) ? $data['enable_custom_soldout_text'] : 'off';
		$custom_soldout_text        = isset( $data['custom_soldout_text'] ) ? $data['custom_soldout_text'] : 'Sold Out!';
		$use_current_loop           = isset( $data['use_current_loop'] ) ? $data['use_current_loop'] : 'off';

		$product_categories = [];
		$product_tags       = [];
		$use_current_loop   = 'on' === $use_current_loop;
		$use_current_loop   = $use_current_loop && ( is_post_type_archive( 'product' ) || is_search() || et_is_product_taxonomy() );
		$product_attribute  = '';
		$product_terms      = [];
		if ( $use_current_loop ) {
			$include_categories = 'all';

			if ( is_product_category() ) {
				$include_categories = (string) get_queried_object_id();
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

		if ( 'product_category' === $type || ( $use_current_loop && ! empty( $include_categories ) ) ) {

			//$all_shop_categories     = et_builder_get_shop_categories();
			$args                = [
				'taxonomy' => 'product_cat',
			];
			$all_shop_categories = get_terms( $args );

			$all_shop_categories_map = [];

			$raw_product_categories = is_array( $include_categories ) ? $include_categories : explode( ',', $include_categories );
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

		$ids             = [];
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

		// phpcs:ignore WordPress.Security.NonceVerification -- Read-only GET orderby for WC catalog sort; mirrors storefront query string, no state mutation.
		$request_orderby_value = et_()->array_get_sanitized( $_GET, 'orderby', '' );

		$maybe_request_price_value_in_order_options = ! empty( $request_orderby_value ) && false !== strpos( strtolower( $request_orderby_value ), 'price' );
		if ( $maybe_request_price_value_in_order_options ) {
			$orderby = 'price';
			$order   = false !== strpos( strtolower( $request_orderby_value ), 'desc' ) ? 'DESC' : 'ASC';
		}

		$options = [
			'show_badge'                 => $show_badge,
			'show_badge_in_image'        => $show_badge_in_image,
			'on_sale_text'               => $on_sale_text,
			'after_sale_text_enable'     => $after_sale_text_enable,
			'after_sale_text_type'       => $after_sale_text_type,
			'after_sale_text'            => $after_sale_text,
			'enable_custom_soldout_text' => $enable_custom_soldout_text,
			'custom_soldout_text'        => $custom_soldout_text,
			'layout'                     => $layout,
			'equal_height'               => $equal_height,
		];

		$df_product_element = [
			'df_product_items'         => $product_item_inner,
			'df_product_items_outside' => $product_item_outer,
		];

        // props assign to use product utils methods.
        $this->props['next_prev_icon'] = $data_params['next_prev_icon'];

		ob_start();

		do_action( 'df_pg_before_print', $df_product_element, $options, true );
        add_filter( 'woocommerce_pagination_args', [ $this, 'pagination_args' ], 10, 2 );

		$shortcode = sprintf(
			'[products columns="%4$s" %1$s limit="%2$s" orderby="%3$s" columns="%4$s" %5$s order="%6$s" %7$s %8$s %9$s %10$s %11$s]',
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

		echo do_shortcode( $shortcode );

		do_action( 'df_pg_after_print', $df_product_element, $options );
        remove_filter( 'woocommerce_pagination_args', [ $this, 'pagination_args' ], 10 );

		$shop = ob_get_contents();
		ob_end_clean();
		if ( empty( $product_item_inner ) && empty( $product_item_outer ) ) {
			$shop = '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Prodcut Element</strong> to continue.</h2>';
		}

		$response = [
			'data' => $shop,
		];

		return RESTController::response_success( $response );
	}

	public function get_productcarousel_data( $data ) {
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$response = [
				'data' => "<div style='color:red'>" . esc_html__( 'Please Before Using the Diviflash Product Grid Module, you need to Activate and configure the WooCommerce Plugin', 'divi_flash' ) . '</div>',
			];

			return RESTController::response_success( $response );
		}
		global $product, $paged, $wp_query, $wp_the_query, $wp_filter, $__et_blog_module_paged;

		$product_items      = isset( $data['productItems'] ) ? $data['productItems'] : [];
		$product_item_inner = $product_items['inner'];
		$product_item_outer = $product_items['outer'];

		$posts_number            = isset( $data['posts_number'] ) ? intval( $data['posts_number'] ) : 10;
		$type                    = isset( $data['type'] ) ? $data['type'] : 'default';
		$columns                 = isset( $data['column'] ) ? $data['column'] : 'default';
		$order                   = '';
		$orderby                 = isset( $data['orderby'] ) ? $data['orderby'] : '1';
		$include_categories      = isset( $data['include_categories'] ) ? $data['include_categories'] : '';
		$include_tags            = isset( $data['include_tags'] ) ? $data['include_tags'] : '';
		$layout                  = isset( $data['layout'] ) ? $data['layout'] : 'grid';
		$use_image_as_background = isset( $data['use_image_as_background'] ) ? $data['use_image_as_background'] : 'off';
		$equal_height            = isset( $data['equal_height'] ) ? $data['equal_height'] : 'off';
		$pagination              = false;

		$show_badge                 = isset( $data['show_badge'] ) ? $data['show_badge'] : 'off';
		$show_badge_in_image        = isset( $data['show_badge_in_image'] ) ? $data['show_badge_in_image'] : 'off';
		$on_sale_text               = isset( $data['on_sale_text'] ) ? $data['on_sale_text'] : 'sale!';
		$after_sale_text_enable     = isset( $data['after_sale_text_enable'] ) ? $data['after_sale_text_enable'] : 'off';
		$after_sale_text_type       = isset( $data['after_sale_text_type'] ) ? $data['after_sale_text_type'] : 'price-percentise';
		$after_sale_text            = isset( $data['after_sale_text'] ) ? $data['after_sale_text'] : '';
		$enable_custom_soldout_text = isset( $data['enable_custom_soldout_text'] ) ? $data['enable_custom_soldout_text'] : 'off';
		$custom_soldout_text        = isset( $data['custom_soldout_text'] ) ? $data['custom_soldout_text'] : 'Sold Out!';
		$use_current_loop           = isset( $data['use_current_loop'] ) ? $data['use_current_loop'] : 'off';

		$product_categories = [];
		$product_tags       = [];
		$use_current_loop   = 'on' === $use_current_loop;
		$use_current_loop   = $use_current_loop && ( is_post_type_archive( 'product' ) || is_search() || et_is_product_taxonomy() );
		$product_attribute  = '';
		$product_terms      = [];
		if ( $use_current_loop ) {
			$include_categories = 'all';

			if ( is_product_category() ) {
				$include_categories = (string) get_queried_object_id();
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

		if ( 'product_category' === $type || ( $use_current_loop && ! empty( $include_categories ) ) ) {

			//$all_shop_categories     = et_builder_get_shop_categories();
			$args                = [
				'taxonomy' => 'product_cat',
			];
			$all_shop_categories = get_terms( $args );

			$all_shop_categories_map = [];

			$raw_product_categories = is_array( $include_categories ) ? $include_categories : explode( ',', $include_categories );
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

		$ids             = [];
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

		// phpcs:ignore WordPress.Security.NonceVerification -- Read-only GET orderby for WC catalog sort; mirrors storefront query string, no state mutation.
		$request_orderby_value = et_()->array_get_sanitized( $_GET, 'orderby', '' );

		$maybe_request_price_value_in_order_options = ! empty( $request_orderby_value ) && false !== strpos( strtolower( $request_orderby_value ), 'price' );
		if ( $maybe_request_price_value_in_order_options ) {
			$orderby = 'price';
			$order   = false !== strpos( strtolower( $request_orderby_value ), 'desc' ) ? 'DESC' : 'ASC';
		}

		$options = [
			'show_badge'                 => $show_badge,
			'show_badge_in_image'        => $show_badge_in_image,
			'on_sale_text'               => $on_sale_text,
			'after_sale_text_enable'     => $after_sale_text_enable,
			'after_sale_text'            => $after_sale_text,
			'after_sale_text_type'       => $after_sale_text_type,
			'enable_custom_soldout_text' => $enable_custom_soldout_text,
			'custom_soldout_text'        => $custom_soldout_text,
			'layout'                     => $layout,
			'equal_height'               => $equal_height,
		];

		$df_product_element = [
			'df_product_items'         => $product_item_inner,
			'df_product_items_outside' => $product_item_outer,
		];
		ob_start();

		do_action( 'df_pg_before_print', $df_product_element, $options, true );
        add_filter( 'woocommerce_pagination_args', [ $this, 'df_pg_pagination' ], 10, 2 );

		add_filter( 'post_class', 'df_product_carousel_class_add', 10, 3 );

		$shortcode = sprintf(
			'[products class="swiper-container" columns="%4$s" %1$s limit="%2$s" orderby="%3$s" columns="%4$s" %5$s order="%6$s" %7$s %8$s %9$s %10$s %11$s]',
			et_core_intentionally_unescaped( $wc_custom_view, 'fixed_string' ),
			esc_attr( $posts_number ),
			esc_attr( $orderby ),
			esc_attr( $columns ),
			$product_categories ? sprintf( 'category="%s"', esc_attr( implode( ',', $product_categories ) ) ) : '',
			esc_attr( $order ),
			$pagination,
			$ids ? sprintf( 'ids="%s"', esc_attr( implode( ',', $ids ) ) ) : '',
			$product_tags ? sprintf( 'tag="%s"', esc_attr( implode( ',', $product_tags ) ) ) : '',
			$product_attribute ? sprintf( 'attribute="%s"', esc_attr( $product_attribute ) ) : '',
			$product_terms ? sprintf( 'terms="%s"', esc_attr( implode( ',', $product_terms ) ) ) : ''
		);

		echo do_shortcode( $shortcode );

		do_action( 'df_pg_after_print', $df_product_element, $options );
        remove_filter( 'woocommerce_pagination_args', [ $this, 'df_pg_pagination' ], 10 );

		$shop = ob_get_contents();
		ob_end_clean();
		if ( empty( $product_item_inner ) && empty( $product_item_outer ) ) {
			$shop = '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Product Element</strong> to continue.</h2>';
		}

		$response = [
			'data' => $shop,
		];

		return RESTController::response_success( $response );
	}

	public function get_cptfilter_data( $data ) {
		global $paged, $post, $wp_query, $wp_filter, $__et_blog_module_paged, $et_fb_processing_shortcode_object, $et_pb_rendering_column_content;

		$cpt_items = isset( $data['cptItems'] ) ? $data['cptItems'] : [];

		$post_type                = isset( $data['post_type'] ) ? $data['post_type'] : ' project';
		$posts_number             = isset( $data['posts_number'] ) ? intval( $data['posts_number'] ) : 10;
		$post_display             = isset( $data['post_display'] ) ? $data['post_display'] : 'recent';
		$use_search_bar           = isset( $data['use_search_bar'] ) ? $data['use_search_bar'] : 'off';
		$orderby                  = isset( $data['orderby'] ) ? $data['orderby'] : '1';
		$selected_taxonomy        = isset( $data['selected_taxonomy'] ) ? $data['selected_taxonomy'] : '';
		$selected_terms           = isset( $data['selected_terms'] ) ? $data['selected_terms'] : '';
		$offset_number            = isset( $data['offset_number'] ) ? $data['offset_number'] : '0';
		$all_items                = isset( $data['all_items'] ) ? $data['all_items'] : 'off';
		$all_items_text           = isset( $data['all_items_text'] ) ? $data['all_items_text'] : 'All';
		$selected_texonomy_list   = isset( $data['selected_texonomy_list'] ) ? $data['selected_texonomy_list'] : '';
		$acf_filter               = isset( $data['acf_filter'] ) ? $data['acf_filter'] : 'off';
		$acf_filter_options       = isset( $data['acf_filter_options'] ) ? $data['acf_filter_options'] : '';
		$pod_filter               = isset( $data['pod_filter'] ) ? $data['pod_filter'] : 'off';
		$pod_filter_options       = isset( $data['pod_filter_options'] ) ? $data['pod_filter_options'] : '';
		$entire_item_clickable    = isset( $data['entire_item_clickable'] ) ? $data['entire_item_clickable'] : 'off';
		$enable_mobile_responsive = isset( $data['enable_mobile_responsive'] ) ? sanitize_text_field( $data['enable_mobile_responsive'] ) : 'on';
		$use_author_filter        = isset( $data['use_author_filter'] ) ? sanitize_text_field( $data['use_author_filter'] ) : 'off';
		$author_filter_field_type = isset( $data['author_filter_field_type'] ) ? sanitize_text_field( $data['author_filter_field_type'] ) : 'select';
		$show_filter_label        = isset( $data['show_filter_label'] ) ? sanitize_text_field( $data['show_filter_label'] ) : 'off';
		$query_args               = [
			'posts_per_page' => $posts_number,
			'post_status'    => [ 'publish' ],
			'post_type'      => $post_type,
		];
		// orderby
		if ( '2' === $orderby ) {
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'ASC';
		} else if ( '3' === $orderby ) {
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'ASC';
		} else if ( '4' === $orderby ) {
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'DESC';
		} else if ( '5' === $orderby ) {
			$query_args['orderby'] = 'rand';
		} else if ( '6' === $orderby ) {
			$query_args['orderby'] = 'menu_order';
			$query_args['order']   = 'ASC';
		} else if ( '7' === $orderby ) {
			$query_args['orderby'] = 'menu_order';
			$query_args['order']   = 'DESC';
		} else {
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'DESC';
		}

		//by Multiple texonomy filter
		$multiple_cat = [];
		if ( $post_display === 'multiple_filter' ) {

			$multiple_cat = $selected_texonomy_list;


			if ( empty( $multiple_cat ) ) {
				$response = [
					'data' => '<h2 style="background:#eee; padding: 10px 20px;">Please select any <strong>Taxonomy</strong>.</h2>',
				];

				return RESTController::response_success( $response );
			}
			$terms_query = [];
			foreach ( $multiple_cat as $cat ) {

				$terms = get_terms( [
					'taxonomy'   => $cat,
					'hide_empty' => true,
				] );

				$permittedValues = array_values( $terms );

				//$all_terms = $str = implode (", ", array_column($permittedValues, 'term_id'));
				$all_terms     = $str = array_column( $permittedValues, 'term_id' ); // Fixed load data from first term id. Now data come from all term id.
				$terms_query[] = [
					'taxonomy' => $cat,
					'field'    => 'term_id',
					'terms'    => $all_terms,
				];

			}
			$multi_filter_type = isset( $data['multi_filter_type'] ) ? sanitize_text_field( $data['multi_filter_type'] ) : 'AND';
			if ( ! empty( $terms_query ) ) {

				$query_args['tax_query']             = $terms_query;
				$query_args['tax_query']['relation'] = 'OR';//$multi_filter_type;
			}

		} // by taxonomy
		else if ( ! empty( $selected_terms ) ) {
			$selected_terms_array = $selected_terms;
			$initial_term_id      = $selected_terms_array[0];
			if ( array_key_exists( 'current', $selected_terms_array ) && is_single() ) {
				$terms = $selected_terms;
			} else {
				$terms = 'current' !== $initial_term_id && 'on' !== $all_items ? $initial_term_id : $selected_terms_array;
			}
			$query_args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'AND',
				[
					'taxonomy' => $selected_taxonomy,
					'field'    => 'term_id',
					'terms'    => $terms,
				],
			];

		} else {
			$response = [
				'data' => '<h2 style="background:#eee; padding: 10px 20px;">Please select a <strong>Post Type</strong>, <strong>Taxonomy</strong> and <strong>Terms</strong>.</h2>',
			];

			return RESTController::response_success( $response );
		}

		$df_pg_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
		if ( is_front_page() ) {
			$paged = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
		if ( $__et_blog_module_paged > 1 ) {
			$df_pg_paged         = $__et_blog_module_paged;
			$paged               = $__et_blog_module_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$query_args['paged'] = $__et_blog_module_paged;
		}
		if ( ! is_search() ) {
			$paged               = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$query_args['paged'] = $df_pg_paged;
		} else {
			$query_args['paged'] = $df_pg_paged;
		}

		if ( '' !== $offset_number && ! empty( $offset_number ) ) {
			/**
			 * Offset + pagination don't play well. Manual offset calculation required
			 *
			 * @see: https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
			 */
			if ( $paged > 1 ) {
				$query_args['offset'] = ( ( $df_pg_paged - 1 ) * intval( $posts_number ) ) + intval( $offset_number );
			} else {
				$query_args['offset'] = intval( $offset_number );
			}
		}

		$cpt_grid_options = [
			'layout'                                   => isset( $data['layout'] ) ? $data['layout'] : 'grid',
			'cpt_item_inner'                           => isset( $cpt_items['inner'] ) ? $cpt_items['inner'] : [],
			'cpt_item_outer'                           => isset( $cpt_items['outer'] ) ? $cpt_items['outer'] : [],
			'equal_height'                             => isset( $data['equal_height'] ) ? $data['equal_height'] : 'off',
			'use_image_as_background'                  => isset( $data['use_image_as_background'] ) ? $data['use_image_as_background'] : 'off',
			'use_background_scale'                     => isset( $data['use_background_scale'] ) ? $data['use_background_scale'] : 'off',
			'load_more'                                => isset( $data['load_more'] ) ? $data['load_more'] : 'off',
			'use_load_more_icon'                       => isset( $data['use_load_more_icon'] ) ? $data['use_load_more_icon'] : '',
			'load_more_font_icon'                      => isset( $data['load_more_font_icon'] ) ? $data['load_more_font_icon'] : '',
			'load_more_icon_pos'                       => isset( $data['load_more_icon_pos'] ) ? $data['load_more_icon_pos'] : '',
			'use_load_more_text'                       => isset( $data['use_load_more_text'] ) ? $data['use_load_more_text'] : '',
			'use_search_bar'                           => isset( $data['use_search_bar'] ) ? $data['use_search_bar'] : 'off',
			'use_search_bar_icon'                      => isset( $data['use_search_bar_icon'] ) ? $data['use_search_bar_icon'] : '',
			'search_bar_font_icon'                     => isset( $data['search_bar_font_icon'] ) ? $data['search_bar_font_icon'] : '&#x55;',
			'use_only_search_bar_icon'                 => isset( $data['use_only_search_bar_icon'] ) ? $data['use_only_search_bar_icon'] : 'off',
			'search_button_icon_placement'             => isset( $data['search_button_icon_placement'] ) ? $data['search_button_icon_placement'] : 'right',
			'search_bar_button_text'                   => isset( $data['search_bar_button_text'] ) ? $data['search_bar_button_text'] : '',
			'search_bar_placeholder_text'              => isset( $data['search_bar_placeholder_text'] ) ? $data['search_bar_placeholder_text'] : '',
			'multi_filter_dropdown_placeholder_prefix' => isset( $data['multi_filter_dropdown_placeholder_prefix'] ) ? $data['multi_filter_dropdown_placeholder_prefix'] : '',
		];

		$post_orderby = ! empty( $query_args['orderby'] ) ? $query_args['orderby'] : 'date';

		ob_start();
		query_posts( $query_args ); // phpcs:ignore WordPress.WP.DiscouragedFunctions

		// render Cpt Filter navigation
		echo "<div class='filter_section " . ( ( 'on' === $enable_mobile_responsive && 'multiple_filter' === $post_display ) ? 'df_phn_resp' : '' ) . "'><div class='filter_wrapper'>";
		if ( 'multiple_filter' === $post_display ) {
			echo '<div class="difl_filter_short_desc_card_header">
            <div class="filter_elements">
                <div class="filter_element_card">
                    <span class="filter_element_card_text">Active Filter Data</span>
                    <a class="filter_element_card_close" href="javascript:void(0);">x</a>
                </div>
            </div>
         </div>';
		}
		echo "<div class='filter_field_wrapper'>";
		if ( $use_search_bar === 'on' ) {
			echo et_core_esc_previously( df_search_filter_html(
				$cpt_grid_options['use_search_bar_icon'],
				$cpt_grid_options['search_bar_font_icon'],
				$cpt_grid_options['search_bar_placeholder_text'],
				$cpt_grid_options['search_bar_button_text'],
				$cpt_grid_options['use_only_search_bar_icon'],
				$cpt_grid_options['search_button_icon_placement']
			) );
		}
		if ( 'on' === $use_author_filter && 'multiple_filter' !== $post_display ) {
			echo et_core_intentionally_unescaped( df_author_filter_html( $post_type, $post_display, $show_filter_label, '', $author_filter_field_type ), 'html' );
		}

		if ( $post_display === 'multiple_filter' ) {
			echo '<ul class="multi_filter_container">';
			echo et_core_esc_previously( generate_taxonomy_dropdown( $multiple_cat, $data ) );
			if ( 'on' === $use_author_filter && 'multiple_filter' === $post_display ) {
				echo et_core_intentionally_unescaped( df_author_filter_html(
					$post_type, $post_display,
					$data['use_multi_filter_label'],
					isset( $data['prefix_multi_filter_label'] ) ? $data['prefix_multi_filter_label'] : '',
					$author_filter_field_type
				), 'html' );
			}
			/* ACF Filter */
			if ( 'on' === $acf_filter ) {
				$cpt_filter = new \ACF_Data_Process_for_Builder( $post_type, $acf_filter_options );
				//				$cpt_filter->get_acf_filter_values();
				// $fields_storage                  = \Df_Acf_Fields::getInstance();
				// $df_acf_fields_for_filter        = $fields_storage->processed_acf_fields_for_filter_options();
				// $df_acf_field_details_for_filter = $fields_storage->acf_fields_with_details;
				echo et_core_esc_previously( generate_acf_filter_fields( $post_type, $acf_filter_options, $cpt_filter->df_acf_field_details_for_filter, $data ) );
			}
			/* ACF Filter Off */

			/* POD Filter On */
			if ( 'on' === $pod_filter ) {
				$cpt_filter = new \POD_Data_Process_for_Builder( $post_type, $pod_filter_options );
				// $cpt_filter->get_pod_filter_values();
				// $generatePodFilterFields = generate_pod_filter_fields( $post_type, $cpt_filter->selected_pod_filter_fields, $cpt_filter->df_pod_field_details_for_filter, $data );

				// $fields_storage                  = \Df_Pod_Fields::getInstance();
                $generatePodFilterFields = generate_pod_filter_fields( $post_type, $pod_filter_options, $cpt_filter->df_pod_field_details_for_filter, $data );

                echo et_core_esc_previously( $generatePodFilterFields );
			}
			/* POD Filter Off */

			echo '</ul>';
		}
		echo '</div>';
		echo '</div>';
		echo '</div>';
		if ( $post_display !== 'multiple_filter' ) {
			echo et_core_esc_previously( render_cpt_filter_nav(
				$post_type,
				$selected_taxonomy,
				$selected_terms,
				$all_items,
				$all_items_text,
				$post_orderby,
				$show_filter_label
			) );
		}
		echo '<div class="df-cpts-wrap layout-' . esc_attr( $cpt_grid_options['layout'] ) . '">';
		echo '<div class="df-cpts-inner-wrap">';
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$this_post_orderby = 'date';
				if ( 'date' === $post_orderby ) {
					$this_post_orderby = get_the_date( 'U' );
				} elseif ( 'title' === $post_orderby ) {
					$shorten_code      = str_replace( ' ', '', strtolower( get_the_title() ) );
					$this_post_orderby = substr( $shorten_code, 0, 10 );
				} else if ( 'menu_order' === $post_orderby ) {
					$this_post_orderby = $post->menu_order;
				}

				$equal_height_class = $cpt_grid_options['equal_height'] === 'on' ? ' df-equal-height' : '';
				?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( "df-cpt-item v2{$equal_height_class}" ) ?>
                         data-order="<?php echo esc_attr( $this_post_orderby ); ?>"
					<?php if ( 'on' === $entire_item_clickable ) {
						echo 'onclick="location.href=\'', esc_url( get_the_permalink() ), '\'" style="cursor:pointer;"';
					} ?>
                >
                    <div class="df-cpt-outer-wrap df-hover-trigger"
						<?php echo $cpt_grid_options['use_background_scale'] !== 'on' ? et_core_esc_previously( df_cpt_image_as_background( $cpt_grid_options['use_image_as_background'] ) ) : ''; ?>>
						<?php
						// render markup to achive the scale effect.
						if ( $cpt_grid_options['use_image_as_background'] === 'on' && $cpt_grid_options['use_background_scale'] === 'on' ) {
							echo '<div class="df-cpt-bg-on-hover"><div ' . et_core_esc_previously( df_cpt_image_as_background( $cpt_grid_options['use_image_as_background'] ) ) . '></div></div>';
						}
						if ( ! empty( $cpt_grid_options['cpt_item_outer'] ) ) {
							foreach ( $cpt_grid_options['cpt_item_outer'] as $cpt_item ) {

								if ( ! isset( $cpt_item['type'] ) ) {
									continue;
								}

								$callback = 'df_cpt_' . $cpt_item['type'];

								call_user_func( $callback, $cpt_item, true );

							} // end of foreach
						}
						?>
                        <div class="df-cpt-inner-wrap">
							<?php
							foreach ( $cpt_grid_options['cpt_item_inner'] as $cpt_item ) {

								if ( ! isset( $cpt_item['type'] ) ) {
									continue;
								}

								$callback = 'df_cpt_' . $cpt_item['type'];

								call_user_func( $callback, $cpt_item, true );

							} // end of foreach
							?>
                        </div>
                    </div>
                </article>
				<?php
			} // endwhile
		}
		echo '</div>';

		// loading load more pagination
		if ( 'on' === $cpt_grid_options['load_more'] ) {
			if ( $wp_query->max_num_pages > 1 ) {
				df_cpt_filter_load_more_btn(
					'all',
					$wp_query->max_num_pages,
					$cpt_grid_options['use_load_more_icon'],
					$cpt_grid_options['load_more_font_icon'],
					$cpt_grid_options['load_more_icon_pos'],
					$cpt_grid_options['use_load_more_text']
				);
			}
		}
		echo '</div>';

		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions
		$posts = ob_get_contents();
		ob_end_clean();

		if ( empty( $cpt_items ) ) {
			$posts = '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Item</strong> to continue.</h2>';
		}

		$response = [
			'data' => $posts,
		];

		return RESTController::response_success( $response );
	}

	public function get_cptcarousel_data( $data ) {
		global $paged, $post, $wp_query, $wp_filter, $__et_blog_module_paged;
		$cpt_items = isset( $data['cptItems'] ) ? $data['cptItems'] : [];

		$cpt_item_inner = isset( $cpt_items['inner'] ) ? $cpt_items['inner'] : [];
		$cpt_item_outer = isset( $cpt_items['outer'] ) ? $cpt_items['outer'] : [];

		if ( $data['post_type'] === 'select' ) {
			$response = [
				'data' => '<h2 style="background:#eee; padding: 10px 20px;">Please select a <strong>Custom Post Type</strong>.</h2>',
			];

			return RESTController::response_success( $response );
		}

		if ( empty( $cpt_item_inner ) && empty( $cpt_item_outer ) ) {
			$response = [
				'data' => '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Item</strong> to continue.</h2>',
			];

			return RESTController::response_success( $response );
		}

		$post_type = isset( $data['post_type'] ) ? $data['post_type'] : 'project';

		$posts_number      = isset( $data['posts_number'] ) ? intval( $data['posts_number'] ) : 10;
		$post_display      = isset( $data['post_display'] ) ? $data['post_display'] : 'recent';
		$orderby           = isset( $data['orderby'] ) ? $data['orderby'] : '1';
		$selected_taxonomy = isset( $data['selected_taxonomy'] ) ? $data['selected_taxonomy'] : '';
		$selected_terms    = isset( $data['selected_terms'] ) && ! empty( $data['selected_terms'] ) ? $data['selected_terms'] : '';
		$offset_number     = isset( $data['offset_number'] ) ? $data['offset_number'] : '0';
		$use_current_loop  = $data['use_current_loop'];
		$post_type_arch    = $data['post_type_arch'];
		$post_type         = $use_current_loop === 'on' ? $post_type_arch : $post_type;
		$query_args        = [
			'posts_per_page' => $posts_number,
			'post_status'    => [ 'publish' ],
			'perm'           => 'readable',
			'post_type'      => $post_type,
		];
		// orderby
		if ( $orderby === '5' ) {
			$query_args['orderby'] = 'rand';
		} else if ( $orderby === '2' ) {
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'ASC';
		} else if ( '3' === $orderby ) {
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'ASC';
		} else if ( '4' === $orderby ) {
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'DESC';
		} else {
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'DESC';
		}

		// by taxonomy
		if ( 'by_tax' === $post_display && '' !== $selected_terms ) {

			if ( ! in_array( 'current', $selected_terms, true ) ) { // If current term check then default query run.
				$query_args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
					[
						'taxonomy' => $selected_taxonomy,
						'field'    => 'term_id',
						'terms'    => $selected_terms,
					],
				];
			}
		}


		// by taxonomy
		// if ( 'by_tax' === $post_display && '' !== $selected_terms ) {
		// 	$selected_terms_array    = is_array( $selected_terms ) ? $selected_terms : explode( ',', $selected_terms );
		// 	$initial_term_id         = $selected_terms_array[0];
		// 	$query_args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery
		// 		'relation' => 'AND',
		// 		[
		// 			'taxonomy' => $selected_taxonomy,
		// 			'field'    => 'term_id',
		// 			'terms'    => $initial_term_id,
		// 		],
		// 	];
		// 	if ( array_key_exists( 'current', $selected_terms_array ) ) { // If current term check then default query run.
		// 		$query_args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery
		// 			'relation' => 'AND',
		// 			[
		// 				'taxonomy' => $selected_taxonomy,
		// 				'field'    => 'term_id',
		// 				'terms'    => $selected_terms_array,
		// 			],
		// 		];
		// 	}
		// }

		$df_pg_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
		if ( is_front_page() ) {
			$paged = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
		if ( $__et_blog_module_paged > 1 ) {
			$df_pg_paged         = $__et_blog_module_paged;
			$paged               = $__et_blog_module_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$query_args['paged'] = $__et_blog_module_paged;
		}
		if ( ! is_search() ) {
			$paged               = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$query_args['paged'] = $df_pg_paged;
		} else {
			$query_args['paged'] = $df_pg_paged;
		}


		if ( 'off' === $use_current_loop ) {
			query_posts( $query_args ); // phpcs:ignore WordPress.WP.DiscouragedFunctions
		} elseif ( is_singular() ) {
			// Force an empty result set in order to avoid loops over the current post.
			query_posts( [ 'post__in' => [ 0 ] ] ); // phpcs:ignore WordPress.WP.DiscouragedFunctions
			// $show_no_results_template = false;
		} else {
			// Only allow certain args when `Posts For Current Page` is set.
			$original = $wp_query->query_vars;
			$custom   = array_intersect_key( $query_args, array_flip( [ 'posts_per_page', 'offset', 'paged' ] ) );

			// Trick WP into reporting this query as the main query so third party filters
			// that check for is_main_query() are applied.
			$wp_the_query = $wp_query = new \WP_Query( array_merge( $original, $custom ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}


		if ( '' !== $offset_number && ! empty( $offset_number ) ) {
			/**
			 * Offset + pagination don't play well. Manual offset calculation required
			 *
			 * @see: https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
			 */
			if ( $paged > 1 ) {
				$query_args['offset'] = ( ( $df_pg_paged - 1 ) * intval( $posts_number ) ) + intval( $offset_number );
			} else {
				$query_args['offset'] = intval( $offset_number );
			}
		}

		$cpt_carousel_options = [
			'cpt_item_inner'          => isset( $cpt_items['inner'] ) ? $cpt_items['inner'] : [],
			'cpt_item_outer'          => isset( $cpt_items['outer'] ) ? $cpt_items['outer'] : [],
			'equal_height'            => isset( $data['equal_height'] ) ? $data['equal_height'] : 'off',
			'use_image_as_background' => isset( $data['use_image_as_background'] ) ? $data['use_image_as_background'] : 'off',
			'use_background_scale'    => isset( $data['use_background_scale'] ) ? $data['use_background_scale'] : 'off',
		];

		ob_start();
		query_posts( $query_args ); // phpcs:ignore WordPress.WP.DiscouragedFunctions


		//echo '<div class="df-cpts-inner-wrap">';
		if ( have_posts() ) {
			echo '<div class="df-cpts-wrap swiper-wrapper">';
			while ( have_posts() ) {
				the_post();

				$equal_height_class = $cpt_carousel_options['equal_height'] === 'on' ? ' df-equal-height' : '';
				?>
                <article
                        id="post-<?php the_ID(); ?>" <?php post_class( "df-cpt-item swiper-slide v2{$equal_height_class}" ) ?>>
                    <div class="df-cpt-outer-wrap df-hover-trigger"
						<?php echo $cpt_carousel_options['use_background_scale'] !== 'on' ? et_core_esc_previously( df_cpt_image_as_background( $cpt_carousel_options['use_image_as_background'] ) ) : ''; ?>>
						<?php
						// render markup to achive the scale effect.
						if ( $cpt_carousel_options['use_image_as_background'] === 'on' && $cpt_carousel_options['use_background_scale'] === 'on' ) {
							echo '<div class="df-cpt-bg-on-hover"><div ' . et_core_esc_previously( df_cpt_image_as_background( $cpt_carousel_options['use_image_as_background'] ) ) . '></div></div>';
						}
						if ( ! empty( $cpt_carousel_options['cpt_item_outer'] ) ) {
							foreach ( $cpt_carousel_options['cpt_item_outer'] as $cpt_item ) {

								if ( ! isset( $cpt_item['type'] ) ) {
									continue;
								}

								$callback = 'df_cpt_' . $cpt_item['type'];

								call_user_func( $callback, $cpt_item, true );

							} // end of foreach
						}
						?>
                        <div class="df-cpt-inner-wrap">
							<?php
							foreach ( $cpt_carousel_options['cpt_item_inner'] as $cpt_item ) {

								if ( ! isset( $cpt_item['type'] ) ) {
									continue;
								}

								$callback = 'df_cpt_' . $cpt_item['type'];

								call_user_func( $callback, $cpt_item, true );

							} // end of foreach
							?>
                        </div>
                    </div>
                </article>
				<?php
			} // endwhile
			echo '</div>';
		} else {
			echo "<h3 style='text-align:center;background:#eee; padding: 10px 20px;'>No Post Found.</h3>";
		}

		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions
		$posts = ob_get_contents();
		ob_end_clean();

		$response = [
			'data' => $posts,
		];

		return RESTController::response_success( $response );
	}


	public function get_cptgrid_data( $data ) {
		global $paged, $post, $wp_query, $wp_filter, $__et_blog_module_paged;

		$cpt_items = isset( $data['cptItems'] ) ? $data['cptItems'] : [];

		$cpt_item_inner = isset( $cpt_items['inner'] ) ? $cpt_items['inner'] : [];
		$cpt_item_outer = isset( $cpt_items['outer'] ) ? $cpt_items['outer'] : [];

		$post_type                   = isset( $data['post_type'] ) ? $data['post_type'] : ' project';
		$posts_number                = isset( $data['posts_number'] ) ? intval( $data['posts_number'] ) : 10;
		$post_display                = isset( $data['post_display'] ) ? $data['post_display'] : 'recent';
		$orderby                     = isset( $data['orderby'] ) ? $data['orderby'] : '1';
		$selected_taxonomy           = isset( $data['selected_taxonomy'] ) ? $data['selected_taxonomy'] : '';
		$selected_terms              = isset( $data['selected_terms'] ) && ! empty( $data['selected_terms'] ) ? $data['selected_terms'] : '';
		$offset_number               = isset( $data['offset_number'] ) ? $data['offset_number'] : '0';
		$layout                      = isset( $data['layout'] ) ? $data['layout'] : 'grid';
		$use_image_as_background     = isset( $data['use_image_as_background'] ) ? $data['use_image_as_background'] : 'off';
		$use_background_scale        = isset( $data['use_background_scale'] ) ? $data['use_background_scale'] : 'off';
		$equal_height                = isset( $data['equal_height'] ) ? $data['equal_height'] : 'off';
		$show_pagination             = isset( $data['show_pagination'] ) ? $data['show_pagination'] : 'off';
		$use_number_pagination       = isset( $data['use_number_pagination'] ) ? $data['use_number_pagination'] : 'off';
		$older_text                  = isset( $data['older_text'] ) ? $data['older_text'] : 'Older Entries';
		$newer_text                  = isset( $data['newer_text'] ) ? $data['newer_text'] : 'Next Entries';
		$use_current_loop            = $data['use_current_loop'];
		$post_type_arch              = $data['post_type_arch'];
		$use_icon_only_at_pagination = isset( $data['use_icon_only_at_pagination'] ) ? $data['use_icon_only_at_pagination'] : 'off';

		$post_type = $use_current_loop === 'on' ? $post_type_arch : $post_type;

		$query_args = [
			'posts_per_page' => $posts_number,
			'post_status'    => [ 'publish' ],
			'post_type'      => $post_type,
		];

		if ( empty( $post_type ) || 'select' === $post_type ) {
			$response = [
				'data' => '<h2 style="background:#eee; padding: 10px 20px;">Please select a <strong>Post Type</strong>.</h2>',
			];

			return RESTController::response_success( $response );

		}

		if ( empty( $cpt_item_inner ) && empty( $cpt_item_outer ) ) {
			$response = [
				'data' => '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Item</strong> to continue.</h2>',
			];

			return RESTController::response_success( $response );
		}

		// by taxonomy
		if ( 'by_tax' === $post_display && '' !== $selected_terms ) {

			if ( ! in_array( 'current', $selected_terms, true ) ) { // If current term check then default query run.
				$query_args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
					[
						'taxonomy' => $selected_taxonomy,
						'field'    => 'term_id',
						'terms'    => $selected_terms,
					],
				];
			}
		}
		// orderby

		if ( '2' === $orderby ) {
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'ASC';
		} else if ( '3' === $orderby
		) {
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'ASC';
		} else if ( '4' === $orderby
		) {
			$query_args['orderby'] = 'title';
			$query_args['order']   = 'DESC';
		} else if ( '5' === $orderby
		) {
			$query_args['orderby'] = 'rand';
		} else if ( '6' === $orderby
		) {
			$query_args['orderby'] = 'menu_order';
			$query_args['order']   = 'ASC';
		} else if ( '7' === $orderby
		) {
			$query_args['orderby'] = 'menu_order';
			$query_args['order']   = 'DESC';
		} else {
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'DESC';
		}

		$df_pg_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
		if ( is_front_page() ) {
			$paged = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
		if ( $__et_blog_module_paged > 1 ) {
			$df_pg_paged         = $__et_blog_module_paged;
			$paged               = $__et_blog_module_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$query_args['paged'] = $__et_blog_module_paged;
		}
		if ( ! is_search() ) {
			$paged               = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$query_args['paged'] = $df_pg_paged;
		} else {
			$query_args['paged'] = $df_pg_paged;
		}

		if ( '' !== $offset_number && ! empty( $offset_number ) ) {
			/**
			 * Offset + pagination don't play well. Manual offset calculation required
			 *
			 * @see: https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
			 */
			if ( $paged > 1 ) {
				$query_args['offset'] = ( ( $df_pg_paged - 1 ) * intval( $posts_number ) ) + intval( $offset_number );
			} else {
				$query_args['offset'] = intval( $offset_number );
			}
		}

		ob_start();
		query_posts( $query_args ); // phpcs:ignore WordPress.WP.DiscouragedFunctions

		echo '<div class="df-cpts-wrap layout-' . esc_attr( $layout ) . '">';
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$equal_height_class = $equal_height === 'on' ? ' df-equal-height' : '';
				?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( "df-cpt-item v2{$equal_height_class}" ) ?>>
                    <div
                            class="df-cpt-outer-wrap df-hover-trigger" <?php echo $use_background_scale !== 'on' ? et_core_esc_previously( df_cpt_image_as_background( $use_image_as_background ) ) : ''; ?>>
						<?php
						// render markup to achive the scale effect.
						if ( $use_image_as_background === 'on' && $use_background_scale === 'on' ) {
							echo '<div class="df-cpt-bg-on-hover"><div ' . et_core_esc_previously( df_cpt_image_as_background( $use_image_as_background ) ) . '></div></div>';
						}
						if ( ! empty( $cpt_item_outer ) ) {
							foreach ( $cpt_item_outer as $cpt_item ) {

								if ( ! isset( $cpt_item['type'] ) ) {
									continue;
								}

								$callback = 'df_cpt_' . $cpt_item['type'];

								call_user_func( $callback, $cpt_item, true );

							} // end of foreach
						}
						?>
                        <div class="df-cpt-inner-wrap">
							<?php
							foreach ( $cpt_item_inner as $cpt_item ) {

								if ( ! isset( $cpt_item['type'] ) ) {
									continue;
								}

								$callback = 'df_cpt_' . $cpt_item['type'];

								call_user_func( $callback, $cpt_item, true );

							} // end of foreach
							?>
                        </div>
                    </div>
                </article>
				<?php
			} // endwhile
		}
		echo '</div>';

		// ajax navigation
		if ( 'on' === $show_pagination ) {
			if ( function_exists( 'wp_pagenavi' ) ) {
				\wp_pagenavi();
			} else {
				add_filter( 'get_pagenum_link', [ __CLASS__, 'filter_pagination_url' ] );
				if ( $use_number_pagination !== 'on' ) {
					cpt_render_pagination( $older_text, $newer_text, $use_icon_only_at_pagination, true );
				} else {
					cpt_render_number_pagination( $older_text, $newer_text, $use_icon_only_at_pagination, true );
				}
				remove_filter( 'get_pagenum_link', [ __CLASS__, 'filter_pagination_url' ] );
			}
		}

		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions
		$posts = ob_get_contents();
		ob_end_clean();


		$response = [
			'data' => $posts,
		];

		return RESTController::response_success( $response );
	}

	public function get_tablepress_data( $data ) {
		$error_message = [];
		if ( ! df_is_table_press_activated() ) {
			$error_message[] = __( 'Install TablePress', 'divi_flash' );
		} else if ( empty( df_get_table_press_list() ) ) {
			$error_message[] = __( 'Create Table', 'divi_flash' );
		} else if ( empty( $data['table'] ) ) {
			$error_message[] = __( 'Select Table', 'divi_flash' );
		} else if ( $data['table'] === 'select' ) {
			$error_message[] = __( 'Must Select a Table', 'divi_flash' );
		}
		$table_data = [];
		if ( ! empty( $error_message ) ) {
			$table_data['error'] = $error_message;
		} else {
			$tables_option = get_option( 'tablepress_tables', '{}' );
			$tables_opt    = json_decode( $tables_option, true );
			$tables        = $tables_opt['table_post'];
			$table_id      = $tables[ $data['table'] ];
			$table_data    = get_post_field( 'post_content', $table_id );
			$tables        = json_decode( $table_data, true );
		}

		$response = [
			'data' => $tables,
		];

		return RESTController::response_success( $response );
	}

	public function get_database_data( $data ) {
		global $wpdb;

		$table_name    = $data['table'];
		$table_data    = [];
		$error_message = [];
		if ( empty( $table_name ) ) {
			$error_message[]     = __( 'Table shouldn\'t empty', 'divi_flash' );
			$error_data['error'] = $error_message;
		} else if ( $table_name === 'select' ) {
			$error_message[]     = __( 'Must select a table', 'divi_flash' );
			$error_data['error'] = $error_message;
		} else {
			// phpcs:disable WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			$table_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s', $table_name ), ARRAY_A ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery
			// phpcs:enable WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
		}

		$response = [
			'data' => $table_data,
		];

		return RESTController::response_success( $response );
	}

	public function get_google_sheet_data( $data ) {
		$error_message = [];
		if ( empty( $data['google_api_key'] ) ) {
			$error_message[] = __( 'Add API key', 'divi_flash' );
		} elseif ( empty( $data['google_sheet_id'] ) ) {
			$error_message[] = __( 'Add Google Sheets ID', 'divi_flash' );
		} elseif ( empty( $data['google_sheet_range'] ) ) {
			$error_message[] = __( 'Add Sheets Range', 'divi_flash' );
		}
		$table_data = [];
		if ( ! empty( $error_message ) ) {
			$table_data['error'] = $error_message;
		} else {
			$sheet_id   = esc_html( $data['google_sheet_id'] );
			$range      = $data['google_sheet_range'] ? str_replace( ':', '%3A', esc_html( trim( $data['google_sheet_range'] ) ) ) : '';
			$api_key    = esc_html( $data['google_api_key'] );
			$base_url   = 'https://sheets.googleapis.com/v4/spreadsheets/';
			$parameters = '?dateTimeRenderOption=FORMATTED_STRING&majorDimension=ROWS&valueRenderOption=FORMATTED_VALUE&key=';
			$url        = $base_url . $sheet_id . '/values/' . $range . $parameters . $api_key;

			$transient_key = $data['id'] . '_data_table_cash';
			$table_data    = get_transient( $transient_key );

			if ( false === $table_data ) {
				$data       = wp_remote_get( $url );
				$table_data = json_decode( wp_remote_retrieve_body( $data ), true );
				set_transient( $transient_key, $table_data, 0 );
			}
			if ( $data['google_cache_remove'] === true ) {
				delete_transient( $transient_key );
			}
		}

		$response = [
			'data' => $table_data,
		];

		return RESTController::response_success( $response );

	}

	/**
	 * URL-fetch chart data source (CSV / Google Sheets / JSON).
	 * Returns { labels, datasets } so the VB editor can preview without CORS.
	 */
	public function get_chart_data( $request ) {
		$url  = (string) $request->get_param( 'url' );
		$type = (string) $request->get_param( 'type' );
		$ttl  = (int)    $request->get_param( 'cache_ttl' );

		if ( ! class_exists( '\DIFL\Server\ChartData' ) ) {
			require_once __DIR__ . '/ChartData.php';
		}

		$data = \DIFL\Server\ChartData::fetch( $url, $type, $ttl );
		if ( empty( $data ) ) {
			return RESTController::response_success( [ 'data' => null, 'error' => 'fetch_failed' ] );
		}
		return RESTController::response_success( [ 'data' => $data ] );
	}

	public function get_cf_form( $options ) {

		global $paged, $post;

		$args = [
			'post_type' => 'wpcf7_contact_form',
		];

		if ( $options['cf7_forms'] === 'default' ) {
			$contact_forms = 'Please select an contact form!';
		} else {
			$contact_forms = do_shortcode( '[contact-form-7 id="' . $options['cf7_forms'] . '" ]' );
		}

		$posts = $contact_forms;


		$response = [
			'data' => $posts,
		];

		return RESTController::response_success( $response );
	}

	public function get_wp_form( $options ) {
		if ( ! is_plugin_active( 'wpforms-lite/wpforms.php' ) ) {
			$response = [
				'data' => [
					'content' => '<div class="error"><p>The <strong>WPForms Lite</strong> plugin is not active.</p></div>',
				],
			];

			return RESTController::response_success( $response );
		}

		if ( ! $options['wpforms'] || $options['wpforms'] === 'default' ) {
			$response = [
				'data' => [
					'content' => '<div class="error"><p>Please select a form.</p></div>',
				],
			];

			return RESTController::response_success( $response );
		}


		global $paged, $post, $wp_scripts, $wp_styles;

		unset( $wp_scripts->registered );

		$preview_template = DIFL_MAIN_DIR . '/template/preview.php';

		$shortcode = '[wpforms id="' . $options['wpforms'] . '"]';

		ob_start();
		et_core_esc_wp( include( $preview_template ) );
		$wpforms = ob_get_clean();

		$form = [
			'content' => $wpforms,
			'styles'  => $wp_styles,
			'scripts' => $wp_scripts,
		];

		$response = [
			'data' => $form,
		];

		return RESTController::response_success( $response );
	}

	public static function filter_pagination_url( $result ) {
		return add_query_arg( 'df_cpt', '', $result );
	}

	public function get_breadcrumbs( $settings ) {
		if ( ! function_exists( 'et_pb_post_format' ) ) {
			require_once get_template_directory() . '/includes/builder/functions.php';
		}
		if ( ! class_exists( 'df-breadcrumbs' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-class-breadcrumbs.php' );
		}

		if ( $settings['separator_text'] === '/' ) {
			$settings['separator_text'] = '/';
		}

		$post_id      = $settings['post_id'] ? $settings['post_id'] : '';
		$post_item    = $settings['post_item'] ? json_decode( $settings['post_item'] ) : '';
		$request_type = $settings['request_type'] ? $settings['request_type'] : '';
		$home_font_icon      = isset( $settings['home_font_icon'] ) && is_string( $settings['home_font_icon'] ) ? $settings['home_font_icon'] : '';
		$inner_font_icon     = isset( $settings['inner_icon'] ) && is_string( $settings['inner_icon'] ) ? $settings['inner_icon'] : '';
		$separator_font_icon = isset( $settings['separator_font_icon'] ) && is_string( $settings['separator_font_icon'] ) ? $settings['separator_font_icon'] : '';
		$home_icon    = '';
		$home_icon    = $settings['use_home_icon'] !== 'off' ?
			sprintf( '<span class="et-pb-icon df-home-icon">%1$s</span>',
				esc_attr( Utils::process_font_icon( json_decode( stripslashes( $home_font_icon ), true ) ) )
			) : '';

		$inner_icon = '';
		$inner_icon = $settings['use_icon_inner_item'] !== 'off' ?
			sprintf( '<span class="et-pb-icon df-inner-icon">%1$s</span>',
				esc_attr( Utils::process_font_icon( json_decode( stripslashes( $inner_font_icon ), true ) ) )
			) : '';
		$separator  = '';
		if ( 'on' === $settings['use_separator_icon'] ) {
			$icon      = sprintf( '<span class="et-pb-icon df-separator-icon">%1$s</span>',
				esc_attr( Utils::process_font_icon( json_decode( stripslashes( $separator_font_icon ), true ) ) )
			);
			$class     = 'df-breadcrumbs-separator-icon';
			$separator = sprintf( '<span class="%2$s"> %1$s </span>', $icon, $class );
		} else if ( 'off' === $settings['use_separator_icon'] && isset( $settings['separator_text'] ) && $settings['separator_text'] !== '' ) {
			$class     = 'df-breadcrumbs-separator-text';
			$separator = sprintf( '<span class="%2$s"> %1$s </span>', $settings['separator_text'], $class );
		}

		// Resolve dynamic data in label settings (e.g. $variable(...)$).
		$post_id_for_dynamic = ! empty( $post_id ) ? (int) $post_id : null;
		$resolve_label       = function ( $value ) use ( $post_id_for_dynamic ) {
			if ( null === $value || '' === $value ) {
				return $value;
			}

			if ( $post_id_for_dynamic && false !== strpos( (string) $value, '$variable(' ) ) {
				$resolved = DynamicData::get_processed_dynamic_data( (string) $value, $post_id_for_dynamic );
				return null !== $resolved ? $resolved : $value;
			}
			return $value;
		};

		$home_text_raw   = isset( $settings['home_text'] ) ? $settings['home_text'] : 'Home';
		$page_title_raw  = isset( $settings['page_title'] ) ? $settings['page_title'] : '';
		$search_title_raw = isset( $settings['search_title'] ) ? $settings['search_title'] : '';
		$error_404_raw   = isset( $settings['error_404_title'] ) ? $settings['error_404_title'] : '';

		$labels              = [
			'home'       => esc_html( $resolve_label( $home_text_raw ) ),
			'page_title' => esc_html( $resolve_label( $page_title_raw ) ),

			'use_page_custom_url'    => isset( $settings['use_page_custom_url'] ) ? esc_attr( $settings['use_page_custom_url'] ) : 'off',
			'page_custom_url'        => isset( $settings['page_custom_url'] ) ? esc_url( $settings['page_custom_url'] ) : '',
			'page_custom_url_target' => isset( $settings['page_custom_url_target'] ) ? esc_attr( $settings['page_custom_url_target'] ) : 'same_window',
			'search'                 => $search_title_raw !== '' ? esc_html( $resolve_label( $search_title_raw ) ) . ' %s' : '%s',
			'error_404'              => esc_html( $resolve_label( $error_404_raw ) ),
		];
		$home_icon_placement = isset( $settings['home_icon_placement'] ) ? $settings['home_icon_placement'] : 'left';
		$args                = [
			'request_type'        => $request_type,
			'visual_builder'      => isset( $settings['visual_builder'] ) ? $settings['visual_builder'] : false,
			'post_id'             => $post_id,
			'post_item'           => $post_item,
			'list_class'          => 'df-breadcrumbs',
			'item_class'          => 'df-breadcrumbs-item',
			'separator'           => $separator,
			'separator_class'     => 'df-breadcrumbs-separator',
			'home_icon'           => $home_icon,
			'home_icon_class'     => 'df-breadcrumbs-home-icon',
			'home_icon_placement' => $home_icon_placement,
			'use_icon_inner_item' => $settings['use_icon_inner_item'],
			'inner_icon'          => $inner_icon,
			'labels'              => $labels,
			'show_on_front'       => 'on' === $settings['show_on_front_page'] ? true : false,
			'show_title'          => $settings['show_title'],
		];

		$breadcrumb = new \Df_Breadcrumb( $args );

		$response = [
			'data' => $breadcrumb->trail()['breadcrumb'],
		];

		return rest_ensure_response( $response );
	}

	public function get_gravity_form( $args ) {
		global $paged, $post, $wp_scripts, $wp_styles;
		$styles = [];
		$form   = [];
		if ( $args['gravity_forms'] !== 'none' ) {
			$id          = intval( $args['gravity_forms'] );
			$title       = $args['show_title'] === 'on' ? 'true' : 'false';
			$description = $args['show_description'] === 'on' ? 'true' : 'false';
			$ajax        = $args['use_ajax'] === 'on' ? 'true' : 'false';

			$shortcode = "[gravityform id='{$id}' title={$title} description={$description} ajax={$ajax} ]";

			ob_start();
			echo do_shortcode( $shortcode );
			$gform = ob_get_clean();

			foreach ( $wp_styles->queue as $handle ) {
				$styles[] = $wp_styles->registered[ $handle ]->src;
			}

			$form = [
				'content' => $gform,
				'styles'  => $styles,
			];
		}

		$response = [
			'data' => $form,
		];

		return RESTController::response_success( $response );
	}

	public function render_library_layout( $request ) {
		$id = $request->get_param( 'id' );
		if ( $id === null || $id === '' ) {
			return RESTController::response_success( [ 'data' => '', 'styles' => '' ] );
		}
		$id   = absint( $id );
		$post = $id > 0 ? get_post( $id ) : null;

		if ( ! $post || ! isset( $post->post_content ) ) {
			return RESTController::response_success( [ 'data' => '', 'styles' => '' ] );
		}

		$styles = '';
		if ( class_exists( 'ET\Builder\FrontEnd\Module\Style' ) ) {
			Style::reset();
		}

		$content = do_shortcode( do_blocks( $post->post_content ) );
		if ( class_exists( 'ET\Builder\FrontEnd\Module\Style' ) ) {
			$styles = Style::render( 'default', 'module', 'post' );
		}

		$response = [
			'data'   => is_string( $content ) ? $content : (string) $content,
			'styles' => is_string( $styles ) ? $styles : (string) $styles,
		];

		return RESTController::response_success( $response );
	}

	public static function render_library_layout_by_id( $library_id ) {
		$empty      = [ 'data' => '', 'styles' => '' ];
		$library_id = absint( $library_id );
		if ( $library_id <= 0 ) {
			return $empty;
		}
		$post = get_post( $library_id );
		if ( ! $post || ! isset( $post->post_content ) ) {
			return $empty;
		}

		$styles = '';

		$content = do_shortcode( do_blocks( $post->post_content ) );
		if ( class_exists( 'ET\Builder\FrontEnd\Module\Style' ) ) {
			$styles = Style::render( 'default', 'module', 'post' );
		}

		return [
			'data'   => is_string( $content ) ? $content : (string) $content,
			'styles' => is_string( $styles ) ? $styles : (string) $styles,
		];
	}

	public function render_shortcode_layout( $request ) {
		$shortcode = $request->get_param( 'shortcode' );
		$shortcode = is_string( $shortcode ) ? $shortcode : '';

		$styles = '';
		if ( class_exists( 'ET\Builder\FrontEnd\Module\Style' ) ) {
			Style::reset();
		}

		$content = do_blocks( do_shortcode( $shortcode ) );
		if ( class_exists( 'ET\Builder\FrontEnd\Module\Style' ) ) {
			$styles = Style::render( 'default', 'module', 'post' );
		}

		$response = [
			'data'   => is_string( $content ) ? $content : (string) $content,
			'styles' => is_string( $styles ) ? $styles : (string) $styles,
		];

		return RESTController::response_success( $response );
	}

	public function render_popup_layout( $request ) {
		$id = $request->get_param( 'id' );
		if ( $id === null || $id === '' ) {
			return RESTController::response_success( [ 'data' => '', 'styles' => '' ] );
		}
		$id   = absint( $id );
		$post = $id > 0 ? get_post( $id ) : null;

		if ( ! $post || ! isset( $post->post_content ) ) {
			return RESTController::response_success( [ 'data' => '', 'styles' => '' ] );
		}

		$styles  = '';
		$content = do_shortcode( do_blocks( $post->post_content ) );
		if ( class_exists( 'ET\Builder\FrontEnd\Module\Style' ) ) {
			// Render both 'default' and 'critical' styles to capture all styles
			// Styles marked with 'critical' flag are filtered out when rendering 'default' type
			// So we need to render both types to get all styles
			$styles = Style::render( 'default', 'module', 'post' )
				. Style::render( 'critical', 'module', 'post' )
				. Style::render( 'default', 'presetNested', 'post' )
				. Style::render( 'critical', 'presetNested', 'post' )
				. Style::render( 'default', 'preset', 'post' )
				. Style::render( 'critical', 'preset', 'post' )
				. Style::render( 'default', 'presetGroup', 'post' )
				. Style::render( 'critical', 'presetGroup', 'post' );
		}

		$response = [
			'data'   => is_string( $content ) ? $content : (string) $content,
			'styles' => is_string( $styles ) ? $styles : (string) $styles,
		];

		return RESTController::response_success( $response );
	}

	public function get_acf_image_data( $request ) {

		$params   = ACFGallery::get_acf_gallery_data( $request->get_params() );
		$response = [];

		if ( empty( $params['error'] ) ) {
			$response = [
				'status' => true,
				'data'   => [
					'gallery'     => $params['gallery'],
					'image_array' => $params['image_array'],
					'post_id'     => $params['post_id'],
				],
			];
		} else {
			$response = [
				'status' => false,
				'error'  => $params['error'],
			];
		}

		return RESTController::response_success( $response );
	}

	public function render_svg_animator( $request ) {
		$args        = $request->get_params();
		$render_slug = $args['render_slug'];
		$response    = SVGAnimator::render_svg_animator( $args, $render_slug );

		$response = [
			'data' => $response,
		];

		return RESTController::response_success( $response );
	}

	public function get_dynamic_data( $request ) {
		$post_id = $request->get_param( 'postId' );
		$value   = $request->get_param( 'value' );

		$resolved_value = DynamicData::get_processed_dynamic_data(
			$value,
			$post_id
		);

		return RESTController::response_success( $resolved_value );
	}

	public function get_justified_images( $request ) {
		$args          = $request->get_params();
		$gallery_array = self::df_jsg_options( $args );
		$images        = self::df_jsg_render_gallery_markup( $gallery_array );

		$response = [
			'data' => $images,
		];

		return RESTController::response_success( $response );
	}

	/**
	 * JustifiedGallery: Return Options for Gallery
	 *
	 * @return $options
	 */
	public static function df_jsg_options( $object, $images = null ) {
		$options = [
			'gallery'                     => $images === null ? ( isset( $object['gallery'] ) ? $object['gallery'] : null ) : $images,
			'rowHeight'                   => isset( $object['rowheight'] ) ? $object['rowheight'] : '',
			'margin'                      => isset( $object['space_between'] ) ? $object['space_between'] : '',
			'show_caption'                => $object['show_caption'],
			'show_description'            => $object['show_description'],
			'load_more'                   => $object['load_more'],
			'image_count'                 => $object['image_count'],
			'ini_count'                   => $object['ini_count'],
			'show_content_lg'             => $object['show_content_lg'],
			'use_lightbox'                => isset( $object['use_lightbox'] ) ? $object['use_lightbox'] : 'off',
			'use_lightbox_download'       => isset( $object['use_lightbox_download'] ) ? $object['use_lightbox_download'] : 'off',
			'content_reveal_caption'      => $object['content_reveal_caption'],
			'content_reveal_description'  => $object['content_reveal_description'],
			'content_position'            => $object['content_position'],
			'image_scale'                 => $object['image_scale'],
			'overlay'                     => $object['overlay'],
			'image_size'                  => $object['image_size'],
			'caption_tag'                 => $object['caption_tag'],
			'description_tag'             => $object['description_tag'],
			'border_anim'                 => $object['border_anim'],
			'border_anm_style'            => $object['border_anm_style'],
			'use_url'                     => isset( $object['use_url'] ) ? $object['use_url'] : 'off',
			'url_target'                  => isset( $object['url_target'] ) ? $object['url_target'] : 'same_window',
			'show_pagination'             => $object['show_pagination'],
			'pagination_img_count'        => $object['pagination_img_count'],
			'use_number_pagination'       => $object['use_number_pagination'],
			'older_text'                  => isset( $object['older_text'] ) ? $object['older_text'] : 'Older Entries',
			'newer_text'                  => isset( $object['newer_text'] ) ? $object['newer_text'] : 'Next Entries',
			'use_icon_only_at_pagination' => $object['use_icon_only_at_pagination'],
		];

		return $options;
	}

	/**
	 * JustifiedGallery: Render markup for Gallery
	 *
	 * @param $options        array
	 * @param $load_more_type boolean, whether it is load more request or not
	 *
	 * @return $images | HTML Markup
	 */
	public static function df_jsg_render_gallery_markup( $options = [], $load_more_type = false ) {
		$default = [
			'gallery'                     => '',
			'margin'                      => '',
			'show_caption'                => 'off',
			'show_description'            => 'off',
			'image_size'                  => 'medium',
			'load_more'                   => 'off',
			'image_count'                 => 8,
			'ini_count'                   => 8,
			'show_content_lg'             => 'off',
			'content_reveal_caption'      => 'c4-fade-up',
			'content_reveal_description'  => 'c4-fade-up',
			'content_position'            => 'c4-layout-top-left',
			'image_scale'                 => 'no-image-scale',
			'caption_tag'                 => 'h4',
			'description_tag'             => 'p',
			'border_anim'                 => 'off',
			'border_anm_style'            => 'c4-border-fade',
			'use_url'                     => '',
			'url_target'                  => '',
			'overlay'                     => '',
			'show_pagination'             => 'off',
			'pagination_img_count'        => 8,
			'use_number_pagination'       => 'off',
			'older_text'                  => 'Older Entries',
			'newer_text'                  => 'Next Entries',
			'use_icon_only_at_pagination' => 'off',
		];

		$default_image = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTA4MCIgaGVpZ2h0PSI1NDAiIHZpZXdCb3g9IjAgMCAxMDgwIDU0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPHBhdGggZmlsbD0iI0VCRUJFQiIgZD0iTTAgMGgxMDgwdjU0MEgweiIvPgogICAgICAgIDxwYXRoIGQ9Ik00NDUuNjQ5IDU0MGgtOTguOTk1TDE0NC42NDkgMzM3Ljk5NSAwIDQ4Mi42NDR2LTk4Ljk5NWwxMTYuMzY1LTExNi4zNjVjMTUuNjItMTUuNjIgNDAuOTQ3LTE1LjYyIDU2LjU2OCAwTDQ0NS42NSA1NDB6IiBmaWxsLW9wYWNpdHk9Ii4xIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgICAgICA8Y2lyY2xlIGZpbGwtb3BhY2l0eT0iLjA1IiBmaWxsPSIjMDAwIiBjeD0iMzMxIiBjeT0iMTQ4IiByPSI3MCIvPgogICAgICAgIDxwYXRoIGQ9Ik0xMDgwIDM3OXYxMTMuMTM3TDcyOC4xNjIgMTQwLjMgMzI4LjQ2MiA1NDBIMjE1LjMyNEw2OTkuODc4IDU1LjQ0NmMxNS42Mi0xNS42MiA0MC45NDgtMTUuNjIgNTYuNTY4IDBMMTA4MCAzNzl6IiBmaWxsLW9wYWNpdHk9Ii4yIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgIDwvZz4KPC9zdmc+Cg==';

		$options = wp_parse_args( $options, $default );
		extract( $options ); // phpcs:ignore WordPress.PHP.DontExtract
		$image_ids = explode( ',', $gallery );
		$images    = '';
		$_i        = 0;

		$border_anim_class = $border_anim === 'on' ? $border_anm_style : '';

		foreach ( $image_ids as $id ) {
			if ( $load_more === 'on' ) {
				if ( $_i >= $ini_count && $load_more_type === false ) {
					break;
				}
			}
			if ( 'on' === $show_pagination && 'on' !== $load_more ) {
				if ( $_i >= $pagination_img_count ) {
					break;
				}
			}
			$_i ++;

			$details     = get_post( $id );
			$content_box = '';
			$caption     = '';
			$description = '';
			if ( ( 'on' == $show_content_lg || 'on' == $show_caption ) && $details ) {
				$caption = $details->post_excerpt !== '' ?
					sprintf( '<div class="%3$s %4$s"><%2$s class="df_jsg_caption">%1$s</%2$s></div>',
						wp_kses_post( $details->post_excerpt, 'divi_flash' ),
						esc_attr( $caption_tag ),
						esc_attr( $content_reveal_caption ),
						esc_attr( $show_caption != 'on' ? 'df-hide-title' : '' )
					) : '';
			}
			if ( ( 'on' == $show_content_lg || 'on' == $show_description ) && $details ) {
				$description = $details->post_content !== '' ?
					sprintf( '<div class="%3$s %4$s"><%2$s class="df_jsg_description">%1$s</%2$s></div>',
						wp_kses_post( $details->post_content, 'divi_flash' ),
						esc_attr( $description_tag ),
						esc_attr( $content_reveal_description ),
						esc_attr( $show_description != 'on' ? 'df-hide-description' : '' )
					) : '';
			}

			$content_box = sprintf( '%1$s%2$s', $caption, $description );

			$show_content_lightbox = $show_content_lg === 'on' && $content_box !== '' ?
				'data-sub-html=".df_jsg_content"' : '';

			$custom_url = $use_url === 'on' ?
				sprintf( 'data-customurl="%1$s" data-target="%2$s"',
					esc_attr( get_post_meta( $id, 'df_ig_url', true ) ),
					esc_attr( $url_target )
				)
				: '';

			$loading_class = 'image_loading';

			$image_url = $image_url_array = wp_get_attachment_image_src( $id, $image_size );

			$image_url = ! empty( $image_url ) && ! is_bool( $image_url[0] ) ? $image_url[0] : $default_image;

			$media_lightbox     = wp_get_attachment_image_src( $id, 'original' );
			$lightbox_image_url = $media_lightbox != '' && ! is_bool( $media_lightbox ) ? $media_lightbox[0] : $default_image;

			$image = sprintf( '<figure class="c4-izmir df_jsg_image %6$s %7$s %9$s %11$s" data-src="%8$s" %4$s %10$s>
                        %12$s

                        <img class="jsg-image" src="%1$s" alt="%2$s" class="" width="%13$s" height="%14$s"/>
                        <figcaption class="df_jsg_content %5$s">
                            %3$s
                        </figcaption>
                    </figure>',
				esc_attr( $image_url ),
				esc_attr( get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
				$content_box,
				$show_content_lightbox,
				esc_attr( $content_position ),
				esc_attr( $image_scale ),
				esc_attr( ' has_overlay' ),
				esc_attr( $lightbox_image_url ),
				esc_attr( $border_anim_class ),
				$custom_url,
				$loading_class,
				$overlay === 'on' ? '<span class="df-overlay"></span>' : '',
				esc_attr( $image_url_array[1] ),
				esc_attr( $image_url_array[2] )
			);

			$images .= $image;
		}

		return $images;
	}

	public function get_packery_images( $request ) {
		$args          = $request->get_params();
		$gallery_array = self::df_pg_options( $args );
		$images        = self::df_pg_render_gallery_markup( $gallery_array );

		$response = [
			'data' => $images,
		];

		return RESTController::response_success( $response );
	}

	/**
	 * Packery: Return Options for Gallery
	 *
	 * @return $options
	 */
	public static function df_pg_options( $object, $images = null ) {
		$options = [
			'gallery'                     => $images === null ? ( isset( $object['gallery'] ) ? $object['gallery'] : null ) : $images,
			'pg_layout'                   => isset( $object['pg_layout'] ) ? $object['pg_layout'] : 'one',
			'space_between'               => $object['space_between'],
			'show_caption'                => $object['show_caption'],
			'show_description'            => $object['show_description'],
			'load_more'                   => $object['load_more'],
			'image_count'                 => $object['image_count'],
			'init_count'                  => $object['init_count'],
			'use_lightbox_content'        => isset( $object['use_lightbox_content'] ) ? $object['use_lightbox_content'] : 'off',
			'use_lightbox'                => isset( $object['use_lightbox'] ) ? $object['use_lightbox'] : 'off',
			'use_lightbox_download'       => isset( $object['use_lightbox_download'] ) ? $object['use_lightbox_download'] : 'off',
			'content_reveal_caption'      => $object['content_reveal_caption'],
			'content_reveal_description'  => $object['content_reveal_description'],
			'content_position'            => $object['content_position'],
			'always_show_title'           => $object['always_show_title'],
			'always_show_description'     => $object['always_show_description'],
			'overlay'                     => $object['overlay'],
			'caption_tag'                 => $object['caption_tag'],
			'description_tag'             => $object['description_tag'],
			'border_anim'                 => $object['border_anim'],
			'border_anm_style'            => $object['border_anm_style'],
			'use_url'                     => isset( $object['use_url'] ) ? $object['use_url'] : 'off',
			'url_target'                  => isset( $object['url_target'] ) ? $object['url_target'] : 'same_window',
			'keep_dsk_tab'                => $object['keep_dsk_tab'],
			'show_pagination'             => $object['show_pagination'],
			'pagination_img_count'        => $object['pagination_img_count'],
			'use_number_pagination'       => $object['use_number_pagination'],
			'older_text'                  => isset( $object['older_text'] ) ? $object['older_text'] : 'Older Entries',
			'newer_text'                  => isset( $object['newer_text'] ) ? $object['newer_text'] : 'Next Entries',
			'use_icon_only_at_pagination' => $object['use_icon_only_at_pagination'],
		];

		return $options;
	}

	/**
	 * Packery: Render markup for Gallery
	 *
	 * @param $options        array
	 * @param $load_more_type boolean, whether it is load more request or not
	 *
	 * @return $images | HTML Markup
	 */
	public static function df_pg_render_gallery_markup( $options = [], $load_more_type = false, $load_count = 0 ) {
		$default = [
			'gallery'                     => '',
			'pg_layout'                   => '',
			'margin'                      => '',
			'show_caption'                => 'off',
			'show_description'            => 'off',
			'image_size'                  => 'large',
			'load_more'                   => 'off',
			'image_count'                 => 8,
			'init_count'                  => 8,
			'use_lightbox'                => '',
			'use_lightbox_content'        => '',
			'content_reveal_caption'      => 'c4-fade-up',
			'content_reveal_description'  => 'c4-fade-up',
			'content_position'            => 'c4-layout-top-left',
			'image_scale'                 => 'no-image-scale',
			'caption_tag'                 => 'h4',
			'description_tag'             => 'p',
			'border_anim'                 => 'off',
			'border_anm_style'            => 'c4-border-fade',
			'use_url'                     => '',
			'url_target'                  => '',
			'overlay'                     => '',
			'always_show_title'           => '',
			'always_show_description'     => '',
			'keep_dsk_tab'                => 'off',
			'show_pagination'             => 'off',
			'pagination_img_count'        => 8,
			'use_number_pagination'       => 'off',
			'older_text'                  => 'Older Entries',
			'newer_text'                  => 'Next Entries',
			'use_icon_only_at_pagination' => 'off',
		];

		$default_image = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTA4MCIgaGVpZ2h0PSI1NDAiIHZpZXdCb3g9IjAgMCAxMDgwIDU0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPHBhdGggZmlsbD0iI0VCRUJFQiIgZD0iTTAgMGgxMDgwdjU0MEgweiIvPgogICAgICAgIDxwYXRoIGQ9Ik00NDUuNjQ5IDU0MGgtOTguOTk1TDE0NC42NDkgMzM3Ljk5NSAwIDQ4Mi42NDR2LTk4Ljk5NWwxMTYuMzY1LTExNi4zNjVjMTUuNjItMTUuNjIgNDAuOTQ3LTE1LjYyIDU2LjU2OCAwTDQ0NS42NSA1NDB6IiBmaWxsLW9wYWNpdHk9Ii4xIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgICAgICA8Y2lyY2xlIGZpbGwtb3BhY2l0eT0iLjA1IiBmaWxsPSIjMDAwIiBjeD0iMzMxIiBjeT0iMTQ4IiByPSI3MCIvPgogICAgICAgIDxwYXRoIGQ9Ik0xMDgwIDM3OXYxMTMuMTM3TDcyOC4xNjIgMTQwLjMgMzI4LjQ2MiA1NDBIMjE1LjMyNEw2OTkuODc4IDU1LjQ0NmMxNS42Mi0xNS42MiA0MC45NDgtMTUuNjIgNTYuNTY4IDBMMTA4MCAzNzl6IiBmaWxsLW9wYWNpdHk9Ii4yIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgIDwvZz4KPC9zdmc+Cg==';

		$options = wp_parse_args( $options, $default );
		extract( $options ); // phpcs:ignore WordPress.PHP.DontExtract
		$image_ids = explode( ',', $gallery );
		$images    = '';
		$_i        = 0;

		$border_anim_class = $border_anim === 'on' ? $border_anm_style : '';

		foreach ( $image_ids as $id ) {
			if ( $load_more === 'on' ) {
				if ( $_i >= $init_count && $load_more_type === false ) {
					break;
				}
			}
			if ( 'on' === $show_pagination && 'on' !== $load_more ) {
				if ( $_i >= $pagination_img_count ) {
					break;
				}
			}
			$_i ++;

			$layout_index = $_i;
			if ( $load_more_type === true ) {
				//$layout_index = $_i + $load_count;
				$layout_index = (int) $_i + (int) $load_count;
			}

			$details            = $id !== '' ? get_post( $id ) : "";
			$content_box        = '';
			$caption            = '';
			$description        = '';
			$always_title       = $always_show_title === 'on' ?
				'always-show-title c4-fade-up' : $content_reveal_caption;
			$always_description = $always_show_description === 'on' ?
				'always-show-description c4-fade-up' : $content_reveal_description;

			if ( ( 'on' == $use_lightbox_content || 'on' == $show_caption ) && $details ) {
				$caption = $details->post_excerpt !== '' ?
					sprintf( '<div class="%3$s %4$s"><%2$s class="df_pg_caption">%1$s</%2$s></div>',
						wp_kses_post( $details->post_excerpt, 'divi_flash' ),
						esc_attr( $caption_tag ),
						esc_attr( $always_title ),
						esc_attr( $show_caption != 'on' ? 'df-hide-title' : '' )
					) : '';
			}
			if ( ( 'on' == $use_lightbox_content || 'on' == $show_description ) && $details ) {
				$description = $details->post_content !== '' ?
					sprintf( '<div class="%3$s %4$s"><%2$s class="df_pg_description">%1$s</%2$s></div>',
						wp_kses_post( $details->post_content, 'divi_flash' ),
						esc_attr( $description_tag ),
						esc_attr( $always_description ),
						esc_attr( $show_description != 'on' ? 'df-hide-description' : '' )
					) : '';
			}

			$content_box = sprintf( '%1$s%2$s', $caption, $description );

			$show_lightbox_content = $use_lightbox_content === 'on' && $content_box !== '' ?
				'data-sub-html=".df_pg_content"' : '';

			$custom_url = $use_url === 'on' ?
				sprintf( 'data-customurl="%1$s" data-target="%2$s"',
					esc_attr( get_post_meta( $id, 'df_ig_url', true ) ),
					esc_attr( $url_target )
				)
				: '';

			$image_url = wp_get_attachment_image_src( $id, $image_size );

			$image_url = ! empty( $image_url ) && ! is_bool( $image_url[0] ) ? $image_url[0] : $default_image;

			$media_lightbox     = wp_get_attachment_image_src( $id, 'original' );
			$lightbox_image_url = $media_lightbox != '' && ! is_bool( $media_lightbox ) ? $media_lightbox[0] : $default_image;

			$image = sprintf( '<div class="df_pg_item%11$s" data-src="%8$s" %4$s %10$s>
                    <figure class="c4-izmir df_pg_image %6$s %7$s %9$s" %10$s style="background-image:url(%1$s);">
                        %12$s
                        <figcaption class="df_pg_content %5$s">
                            %3$s
                        </figcaption>
                    </figure></div>',
				esc_attr( $image_url ),
				esc_attr( get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
				$content_box,
				$show_lightbox_content,
				esc_attr( $content_position ),
				esc_attr( $image_scale ),
				esc_attr( ' has_overlay' ),
				esc_attr( $lightbox_image_url ),
				esc_attr( $border_anim_class ),
				$custom_url,
				df_pg_layout_classes( $layout_index, $pg_layout ),
				$overlay === 'on' ? '<span class="df-overlay"></span>' : '',
				$use_lightbox
			);

			$images .= $image;
		}

		return $images;
	}

	public function get_advanced_image_data( $request ) {
		$data = $request->get_params();

		// create the display gallery code
		// $data = json_decode(file_get_contents('php://input'), true);
		// $_objects = json_decode($data['images']);

		$_objects     = json_decode( $data['images'] );
		$images_array = [];

		foreach ( $_objects as $_object ) {
			$title    = isset( $_object->gallery_title ) && $_object->gallery_title !== '' ?
				$_object->gallery_title : '';
			$category = isset( $_object->gallery_title ) && $_object->gallery_title !== '' ?
				strtolower( preg_replace( '/[^A-Za-z0-9-]/', "-", $_object->gallery_title ) ) : '';
			$ids      = [];
			if ( property_exists( $_object, 'gallery_ids' ) ) { // version 1.1.5 change from array_key_exists('gallery_ids', $_object)
				$ids = explode( ',', $_object->gallery_ids );
			}
			foreach ( $ids as $id ) {
				$images_array[ $id ][] = $category;
			}
		}

		/**
		 * image order
		 */
		if ( 'on' === $data['use_image_order'] ) {
			if ( '' === $data['image_order'] || 'random' === $data['image_order'] ) {
				$images_array = self::df_shuffle_assoc( $images_array );
			} else if ( 'asc' === $data['image_order'] ) {
				ksort( $images_array );
			} else if ( 'desc' === $data['image_order'] ) {
				krsort( $images_array );
			}
		}

		$options = self::df_ig_options(
			[ 'images_array' => $images_array ],
			$data
		);

		$gallery = self::df_ig_render_images( $options );

		$response = [
			'data' => $gallery,
		];

		return RESTController::response_success( $response );

		// wp_send_json_success($gallery);
	}

	public function get_advanced_image_category_data( $request ) {
		$data    = $request->get_params();
		$tax_ids = isset( $data['tax_ids'] ) ? $data['tax_ids'] : '';

		$tax_array = json_decode( $tax_ids, true );
		if ( ! is_array( $tax_array ) ) {
			return RESTController::response_error( 'Invalid tax_ids format', 400 );
		}

		$tax_ids = array_unique( array_map( fn( $item ) => strval( $item['id'] ), $tax_array ) );

		$images       = "";
		$request_data = [];
		$titles       = [];

		foreach ( $tax_ids as $tax_id ) {
			$args        = [
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => - 1,
				'tax_query'      => [
					[
						'taxonomy' => DiviFlash_Media_Category::TAXONOMY,
						'field'    => 'term_id',
						'terms'    => $tax_id,
					],
				],
			];
			$media_items = get_posts( $args );
			if ( $media_items ) {
				$gallery_title                                                   = get_term( $tax_id, DiviFlash_Media_Category::TAXONOMY )->name;
				$titles[ strtolower( str_replace( ' ', '-', $gallery_title ) ) ] = $gallery_title;
				$gallery_ids                                                     = "";
				foreach ( $media_items as $media_item ) {
					$gallery_ids = $gallery_ids . $media_item->ID . ",";
					$images      = $images . $media_item->ID . ",";
				}
			}
			$gallery_title  = isset( $gallery_title ) ? $gallery_title : "";
			$gallery_ids    = isset( $gallery_ids ) ? $gallery_ids : "";
			$request_data[] = [ "gallery_title" => $gallery_title, "gallery_ids" => rtrim( $gallery_ids, ',' ) ];
		}

		$response = [
			"body" => [ "images" => $images, "request_data" => $request_data, "titles" => $titles ],
		];

		return RESTController::response_success( $response );
	}

	public static function df_shuffle_assoc( $my_array ) {
		$keys = array_keys( $my_array );

		shuffle( $keys );

		foreach ( $keys as $key ) {
			$new[ $key ] = $my_array[ $key ];
		}

		$my_array = $new;

		return $my_array;
	}

	public static function df_ig_options( $iamges, $object ) {
		$options = [
			'show_caption'                => $object['show_caption'],
			'show_description'            => $object['show_description'],
			'image_size'                  => $object['image_size'],
			'use_orientation'             => $object['use_orientation'],
			'image_orientation'           => $object['image_orientation'],
			'filter_nav'                  => $object['filter_nav'],
			'layout_mode'                 => $object['layout_mode'],
			'load_more'                   => $object['load_more'],
			'init_count'                  => $object['init_count'],
			'image_count'                 => $object['image_count'],
			'use_lightbox'                => isset( $object['use_lightbox'] ) ? $object['use_lightbox'] : 'off',
			'use_lightbox_content'        => isset( $object['use_lightbox_content'] ) ? $object['use_lightbox_content'] : 'off',
			'use_lightbox_download'       => isset( $object['use_lightbox_download'] ) ? $object['use_lightbox_download'] : 'off',
			'caption_tag'                 => $object['caption_tag'],
			'description_tag'             => $object['description_tag'],
			'image_scale'                 => $object['image_scale'],
			'enable_content_position'     => $object['enable_content_position'],
			'content_position_outside'    => $object['content_position_outside'],
			'content_position'            => $object['content_position'],
			'content_reveal_caption'      => $object['content_reveal_caption'],
			'content_reveal_description'  => $object['content_reveal_description'],
			'border_anim'                 => $object['border_anim'],
			'border_anm_style'            => $object['border_anm_style'],
			'always_show_title'           => $object['always_show_title'],
			'always_show_description'     => $object['always_show_description'],
			'use_url'                     => isset( $object['use_url'] ) ? $object['use_url'] : 'off',
			'url_target'                  => isset( $object['url_target'] ) ? $object['url_target'] : 'same_window',
			'overlay'                     => $object['overlay'],
			'field_use_icon'              => $object['field_use_icon'],
			// 'field_font_icon'             => $object['field_font_icon'],
			'field_font_icon'             => isset( $object['field_font_icon'] ) ? $object['field_font_icon'] : [
				'unicode' => '&#x35;',
				'type'    => 'divi',
				'weight'  => '400',
			],
			'content_reveal_icon'         => $object['content_reveal_icon'],
			'show_pagination'             => $object['show_pagination'],
			'pagination_img_count'        => $object['pagination_img_count'],
			'use_number_pagination'       => $object['use_number_pagination'],
			'older_text'                  => isset( $object['older_text'] ) ? $object['older_text'] : 'Older Entries',
			'newer_text'                  => isset( $object['newer_text'] ) ? $object['newer_text'] : 'Next Entries',
			'use_icon_only_at_pagination' => $object['use_icon_only_at_pagination'],
		];

		return array_merge( $iamges, $options );
	}

	public static function df_ig_render_images( $options = [], $load_more_type = false ) {

		$default = [
			'images_array'                => [],
			'show_caption'                => 'off',
			'show_description'            => 'off',
			'image_size'                  => 'medium',
			'use_orientation'             => 'off',
			'image_orientation'           => 'landscape',
			'filter_nav'                  => 'off',
			'load_more'                   => 'off',
			'init_count'                  => 6,
			'image_count'                 => 3,
			'use_lightbox'                => 'off',
			'use_lightbox_content'        => 'off',
			'caption_tag'                 => '',
			'description_tag'             => '',
			'image_scale'                 => '',
			'enable_content_position'     => 'off',
			'content_position_outside'    => '',
			'content_position'            => '',
			'content_reveal_caption'      => '',
			'content_reveal_description'  => '',
			'border_anim'                 => 'off',
			'border_anm_style'            => '',
			'always_show_title'           => '',
			'always_show_description'     => '',
			'use_url'                     => 'off',
			'url_target'                  => 'same_window',
			'overlay'                     => '',
			'field_use_icon'              => 'off',
			'field_font_icon'             => [],
			'content_reveal_icon'         => '',
			'show_pagination'             => 'off',
			'pagination_img_count'        => 6,
			'use_number_pagination'       => 'off',
			'older_text'                  => 'Older Entries',
			'newer_text'                  => 'Next Entries',
			'use_icon_only_at_pagination' => 'off',
		];

		$default_image = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTA4MCIgaGVpZ2h0PSI1NDAiIHZpZXdCb3g9IjAgMCAxMDgwIDU0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPHBhdGggZmlsbD0iI0VCRUJFQiIgZD0iTTAgMGgxMDgwdjU0MEgweiIvPgogICAgICAgIDxwYXRoIGQ9Ik00NDUuNjQ5IDU0MGgtOTguOTk1TDE0NC42NDkgMzM3Ljk5NSAwIDQ4Mi42NDR2LTk4Ljk5NWwxMTYuMzY1LTExNi4zNjVjMTUuNjItMTUuNjIgNDAuOTQ3LTE1LjYyIDU2LjU2OCAwTDQ0NS42NSA1NDB6IiBmaWxsLW9wYWNpdHk9Ii4xIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgICAgICA8Y2lyY2xlIGZpbGwtb3BhY2l0eT0iLjA1IiBmaWxsPSIjMDAwIiBjeD0iMzMxIiBjeT0iMTQ4IiByPSI3MCIvPgogICAgICAgIDxwYXRoIGQ9Ik0xMDgwIDM3OXYxMTMuMTM3TDcyOC4xNjIgMTQwLjMgMzI4LjQ2MiA1NDBIMjE1LjMyNEw2OTkuODc4IDU1LjQ0NmMxNS42Mi0xNS42MiA0MC45NDgtMTUuNjIgNTYuNTY4IDBMMTA4MCAzNzl6IiBmaWxsLW9wYWNpdHk9Ii4yIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgIDwvZz4KPC9zdmc+Cg==';

		$options = wp_parse_args( $options, $default );
		extract( $options ); // phpcs:ignore WordPress.PHP.DontExtract
		$images  = '';
		$classes = '';
		$_i      = 0;

		if ( $load_more_type === true ) {
			$images_array = array_flip( $images_array );
		}

		$always_title = 'on' === $always_show_title || 'on' === $enable_content_position ? 'always-show-title ' : $content_reveal_caption;
		$always_title = 'on' === $always_show_title && 'on' !== $enable_content_position ? $always_title . 'c4-fade-up' : $always_title;

		$always_description = 'on' === $always_show_description || 'on' === $enable_content_position ? 'always-show-description ' : $content_reveal_description;
		$always_description = 'on' === $always_show_description && 'on' !== $enable_content_position ? $always_description . 'c4-fade-up' : $always_description;

		$border_anim_class = 'on' === $border_anim ? $border_anm_style : '';

		foreach ( $images_array as $id => $value ) {
			if ( 'on' === $load_more && 'on' !== $filter_nav ) {
				if ( $_i >= $init_count && $load_more_type === false ) {
					break;
				}
			}
			if ( 'on' === $show_pagination && 'on' !== $load_more ) {
				if ( $_i >= $pagination_img_count ) {
					break;
				}
			}
			$_i ++;
			if ( 'on' === $use_orientation ) {
				$width  = 400;
				$height = ( 'landscape' === $image_orientation ) ? 284 : 516;

				$width  = (int) apply_filters( 'et_pb_gallery_image_width', $width );
				$height = (int) apply_filters( 'et_pb_gallery_image_height', $height );
				$media  = wp_get_attachment_image_src( $id, [ $width, $height ] );

			} else {
				$media = wp_get_attachment_image_src( $id, $image_size );
			}

			$media_lightbox     = wp_get_attachment_image_src( $id, 'original' );
			$lightbox_image_url = '' !== $media_lightbox && ! is_bool( $media_lightbox ) ? $media_lightbox[0] : $default_image;

			$content_box = '';

			$details     = '' !== $id ? get_post( $id ) : "";
			$caption     = '';
			$description = '';
			if ( ( 'on' === $use_lightbox_content || 'on' === $show_caption ) && $details ) {
				$caption = '' !== $details->post_excerpt ?
					sprintf( '<div class="%3$s %4$s"><%2$s class="df_ig_caption">%1$s</%2$s></div>',
						wp_kses_post( $details->post_excerpt, 'divi_flash' ),
						esc_attr( $caption_tag ),
						esc_attr( $always_title ),
						esc_attr( 'on' !== $show_caption ? 'df-hide-title' : '' )
					) : '';
			}
			if ( ( 'on' === $use_lightbox_content || 'on' === $show_description ) && $details ) {
				$description = '' !== $details->post_content ?
					sprintf( '<div class="%3$s %4$s"><%2$s class="df_ig_description">%1$s</%2$s></div>',
						wp_kses_post( $details->post_content, 'divi_flash' ),
						esc_attr( $description_tag ),
						esc_attr( $always_description ),
						esc_attr( 'on' !== $show_description ? 'df-hide-description' : '' )
					) : '';
			}

			$content_box = sprintf( '%1$s%2$s', $caption, $description );

			$data_lightbox_html = 'on' === $use_lightbox_content && '' !== $content_box ?
				'data-sub-html=".df_ig_content"' : '';

			if ( $load_more_type === false ) {
				$classes = implode( " ", $value );
			}

			$custom_url = $use_url === 'on' ?
				sprintf( 'data-url="%1$s"', esc_attr( get_post_meta( $id, 'df_ig_url', true ) ) )
				: '';

			$empty_class = $content_box === '' ? ' empty_content' : '';

			if ( empty( $media[0] ) ) {
				continue;
			}
			$image_url = ! empty( $media[0] ) ? $media[0] : $default_image;

			$conent_positioning = sprintf( '<%5$s class="df_ig_content %2$s %3$s %4$s">
                                %1$s
                            </%5$s>',
				$content_box,
				'on' === $enable_content_position ? esc_attr( $content_position_outside ) : esc_attr( $content_position ),
				esc_attr( $empty_class ),
				'on' === $enable_content_position ? esc_attr( 'outside' ) : '',
				'on' === $enable_content_position ? 'div' : 'figcaption'
			);

			$content_outside_positioning = "";
			if ( 'on' === $enable_content_position && 'on' === $use_lightbox_content && $details ) {
				$lightbox_caption            = '' !== $details->post_excerpt ?
					sprintf( '<div class="ig_is_lightbox"><%2$s class="df_ig_caption">%1$s</%2$s></div>',
						wp_kses_post( $details->post_excerpt, 'divi_flash' ),
						esc_attr( $caption_tag )
					) : "";
				$lightbox_description        = '' !== $details->post_content ?
					sprintf( '<div class="ig_is_lightbox"><%2$s class="df_ig_description">%1$s</%2$s></div>',
						wp_kses_post( $details->post_content, 'divi_flash' ),
						esc_attr( $description_tag )
					) : '';
				$content_outside_positioning = sprintf( '<figcaption class="df_ig_content">%1$s%2$s</figcaption>', $lightbox_caption, $lightbox_description );
			}

			// Lightbox Caption : https://sachinchoolur.github.io/lightgallery.js/demos/captions.html
			$image  = sprintf( '<div class="df_ig_image grid-item %1$s" data-src="%2$s" %3$s>
                    <div class="item-content %4$s" %10$s>
                        %11$s
                        <figure class="%5$s c4-izmir %6$s">
                            %7$s
                            <img class="ig-image" src="%8$s" alt="%9$s" class=""/>
                            %12$s
                        </figure>
                        %13$s
                    </div>
                </div>',
				esc_attr( $classes ),
				esc_attr( $lightbox_image_url ),
				$data_lightbox_html,
				esc_attr( $image_scale ),
				esc_attr( $border_anim_class ),
				esc_attr( ' has_overlay' ),
				'on' === $overlay ? sprintf( '<span class="df-overlay">%1$s</span>', 'on' === $field_use_icon ? self::df_get_processed_font_content( $field_font_icon, $content_reveal_icon ) : '' ) : '',
				esc_attr( $image_url ),
				esc_attr( get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
				$custom_url,
				'on' === $enable_content_position && in_array( $content_position_outside, [
					'c4-layout-top-left',
					'c4-layout-top-center',
					'c4-layout-top-right',
				] ) ? $conent_positioning : "",
				'on' !== $enable_content_position ? $conent_positioning : $content_outside_positioning,
				'on' === $enable_content_position && in_array( $content_position_outside, [
					'c4-layout-bottom-left',
					'c4-layout-bottom-center',
					'c4-layout-bottom-right',
				] ) ? $conent_positioning : ""
			);
			$images .= $image;
		}

		return $images;
	}

	public static function df_get_processed_font_content( $field_font_icon, $content_reveal_icon ) {
		$icon_data = is_array( $field_font_icon ) ? $field_font_icon : json_decode( $field_font_icon, true );

		return sprintf( '<div class="df_ig_icon_wrap %2$s"><span class="et-pb-icon">%1$s</span></div>',
			esc_attr( Utils::process_font_icon( $icon_data ) ),
			$content_reveal_icon
		);
	}

	/**
	 * InstagramGallery: Return Options for Gallery
	 *
	 * @return $options
	 */
	public static function df_intagram_gallery_options( $iamges, $object ) {
		$options = [
			'show_caption'                   => $object['show_caption'],
			'show_instagram_user_info'       => $object['show_instagram_user_info'],
			'instagram_user_profile_picture' => $object['instagram_user_profile_picture'],
			'instagram_username_text'        => $object['instagram_username_text'],
			'date_formate'                   => $object['date_formate'],
			'instagram_icon'                 => $object['instagram_icon'],
			'instagram_icon_enable'          => ! empty( $object['instagram_icon_enable'] ) ? $object['instagram_icon_enable'] : '',
			'instagram_post_only_image'      => $object['instagram_post_only_image'],
			'autoplay_video'                 => $object['autoplay_video'],
			'cache_time'                     => ! empty( $object['cache_time'] ) ? $object['cache_time'] : '-1',
			'cache_time_type'                => ! empty( $object['cache_time_type'] ) ? $object['cache_time_type'] : 'minute',
			'image_size'                     => ! empty( $object['image_size'] ) ? $object['image_size'] : '',
			'layout_mode'                    => $object['layout_mode'],
			'load_more'                      => $object['load_more'],
			'item_limit'                     => ! empty( $object['item_limit'] ) ? $object['item_limit'] : '',
			'total_item'                     => ! empty( $object['total_item'] ) ? $object['total_item'] : '',
			'init_count'                     => $object['init_count'],
			'image_count'                    => $object['image_count'],
			'caption_tag'                    => $object['caption_tag'],
			'image_scale'                    => $object['image_scale'],
			'content_position'               => $object['content_position'],
			'content_reveal_caption'         => $object['content_reveal_caption'],
			'border_anim'                    => $object['border_anim'],
			'border_anm_style'               => $object['border_anm_style'],
			'always_show_title'              => $object['always_show_title'],
			'use_icon'                       => isset ( $object['use_icon'] ) ? $object['use_icon'] : 'off',
			'hover_icon'                     => $object['hover_icon'],
			'use_url'                        => isset ( $object['use_url'] ) ? $object['use_url'] : 'off',
			'url_target'                     => isset( $object['url_target'] ) ? $object['url_target'] : 'same_window',
			'overlay'                        => $object['overlay'],
			'instagram_user_token'           => isset( $object['instagram_user_token'] ) ? $object['instagram_user_token'] : '',
			'unique_module_name'             => $object['unique_module_name'],
			'user_token_changed'             => isset( $object['user_token_changed'] ) ? $object['user_token_changed'] : false,

		];

		return array_merge( $iamges, $options );
	}

	/**
	 * InstagramGallery: Image markup for gallery
	 *
	 * @param $options        array
	 * @param $load_more_type boolean, whether it is load more request or not
	 *
	 * @return $images | HTML Markup
	 */

	public static function df_ing_render_images( $options = [], $load_more_type = false ) {

		$default = [
			'images_array'                   => [],
			'show_instagram_user_info'       => 'off',
			'instagram_icon_enable'          => 'on',
			'instagram_user_profile_picture' => '',
			'instagram_username_text'        => '',
			'autoplay_video'                 => 'off',
			'show_caption'                   => 'off',
			'show_description'               => 'off',
			'image_size'                     => 'medium',
			'filter_nav'                     => 'off',
			'load_more'                      => 'off',
			'init_count'                     => 6,
			'image_count'                    => 3,
			'use_lightbox'                   => 'off',
			'use_lightbox_content'           => 'off',
			'caption_tag'                    => '',
			'description_tag'                => '',
			'image_scale'                    => '',
			'content_position'               => '',
			'content_reveal_caption'         => '',
			'content_reveal_description'     => '',
			'border_anim'                    => 'off',
			'border_anm_style'               => '',
			'always_show_title'              => '',
			'always_show_description'        => '',
			'use_url'                        => 'off',
			'url_target'                     => 'same_window',
			'overlay'                        => '',
		];

		$options = wp_parse_args( $options, $default );


		extract( $options ); // phpcs:ignore WordPress.PHP.DontExtract
		$images  = '';
		$classes = '';
		$_i      = 0;

		// print_r($images_array);

		$always_title       = $always_show_title === 'on' ?
			'always-show-title c4-fade-up' : $content_reveal_caption;
		$always_description = $always_show_description === 'on' ?
			'always-show-description c4-fade-up' : $content_reveal_description;

		$border_anim_class = $border_anim === 'on' ? $border_anm_style : '';

		$instagram_username = $instagram_username_text !== '' ?
			sprintf( '<span class="df-instagram-user-name">%1$s</span>', esc_attr( $instagram_username_text ) )
			: '';

		$instagram_user_profile_picture_html = $instagram_user_profile_picture !== '' ?
			sprintf( '<div class="df-instagram-user-profile-picture">
                    <img src="%1$s" alt="%2$s">
                </div>', $instagram_user_profile_picture, esc_attr( $instagram_username_text )
			)
			: '';
		$post_date_formate                   = $date_formate ? $date_formate : "M j, Y";
		foreach ( $images_array as $item ) {
			if ( $load_more === 'on' ) {
				if ( $_i >= $init_count && $load_more_type === false ) {
					break;
				}
			}
			$_i ++;
			$image_upper_icon = $use_icon === 'on' ?
				sprintf( '<span class="et-pb-icon hover_icon">%1$s</span>', $hover_icon ? esc_attr( Utils::process_font_icon( is_array( $hover_icon ) ? $hover_icon : json_decode( $hover_icon, true ) ) ) : 'P' ) : '';

			$content_box = '';
			$caption     = '';
			if ( $show_caption === 'on' && ! empty( $item->caption ) ) {
				$caption .= $show_caption === 'on' ?
					sprintf( '<div class="%3$s"><%2$s class="df_ing_caption">%1$s</%2$s></div>',
						esc_html__( $item->caption, 'divi_flash' ),
						esc_attr( $caption_tag ?? 'h4' ),
						esc_attr( $always_title ) ) : '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="Layer_1" x="0px" y="0px" width="32px" height="32px" viewBox="0 0 32 32" style="enable-background:new 0 0 32 32;" xml:space="preserve"><path d="M23,32H9c-5,0-9-4-9-9V9c0-5,4-9,9-9h14c5,0,9,4,9,9v14C32,28,28,32,23,32z M9,2C5.1,2,2,5.1,2,9v14c0,3.9,3.1,7,7,7h14  c3.9,0,7-3.1,7-7V9c0-3.9-3.1-7-7-7H9z"></path><path d="M16,24.2c-4.5,0-8.2-3.7-8.2-8.2c0-4.5,3.7-8.2,8.2-8.2c4.5,0,8.2,3.7,8.2,8.2C24.2,20.5,20.5,24.2,16,24.2z M16,9.8  c-3.4,0-6.2,2.8-6.2,6.2s2.8,6.2,6.2,6.2s6.2-2.8,6.2-6.2S19.4,9.8,16,9.8z"></path><circle cx="16" cy="16" r="1.9"></circle></svg>';

			}
			$content_box = sprintf( '%1$s %2$s', $image_upper_icon, $caption );
			$empty_class = $content_box === '' ? ' empty_content' : '';

			$data_lightbox_html = $use_lightbox_content === 'on' ?
				'data-sub-html=".df_ing_content"' : '';


			$custom_url = $use_url === 'on' ?
				sprintf( 'data-url="%1$s"', $item->permalink )
				: '';

			$media_type             = ( 'VIDEO' == $item->media_type ) ? 'video' : 'img';
			$autoplay_attribute     = $autoplay_video === 'on' ? 'autoplay' : '';
			$instagram_item_caption = isset( $item->caption ) && $item->caption ? $item->caption : '';
			$media_html             = '';
			if ( $item->media_type !== 'VIDEO' ) {
				$media_html .= sprintf( '<%1$s class="ing-image" src="%2$s" alt="%4$s" title="Image by: %3$s"/>', $media_type, $item->media_url, $item->username, $instagram_item_caption );
			} else {
				$media_html .= sprintf( '<video id="instagram-video" class="ing-image instagram-video"  controls %2$s>  <source src="%1$s" type="video/mp4" /></video>', $item->media_url, $autoplay_attribute );
			}

			$instagram_date = strtotime( $item->timestamp );
			$instagram_date = gmdate( $post_date_formate, $instagram_date );

			$instagram_icon_html = $instagram_icon_enable == 'on' ?
				sprintf( '<a class="df-instagram-feed-icon" href="%1$s" target="_blank">
                        <span class="et-pb-icon instagram_icon">%2$s</span>
                    </a>',
					$item->permalink ? $item->permalink : "",
					$instagram_icon ? esc_attr( Utils::process_font_icon( is_array( $instagram_icon ) ? $instagram_icon : json_decode( $instagram_icon, true ) ) ) : "4"
				) : '';


			$instagram_user_info_html = $show_instagram_user_info === 'on' ?
				sprintf( '<div class="df-instagram-user-info">
                            <a class="df-instagram-user" href="https://www.instagram.com/%1$s" target="_blank">
                                %2$s
                                <div class="df-instagram-username-and-postdate">
                                    %3$s
                                    <span class="df-instagram-postdate">  %4$s </span>
                                </div>
                            </a>
                            %5$s
                        </div>',
					$item->username,
					$instagram_user_profile_picture_html,
					$instagram_username !== '' ? $instagram_username : '<span class="df-instagram-user-name">' . $item->username . '</span>',
					$instagram_date,
					$instagram_icon_html
				)
				: '';
			// Lightbox Caption : https://sachinchoolur.github.io/lightgallery.js/demos/captions.html

			$image  = sprintf( '<div class="df_ing_image grid-item %3$s" data-src="%8$s" %4$s>
                    <div class="item-content" %10$s >

                        %12$s
                        <div class="image-container %5$s %14$s">
                            <figure %10$s class="%7$s c4-izmir %9$s">
                                %13$s
                                %1$s
                                <figcaption class="df_ing_content %6$s %11$s">
                                    %2$s
                                </figcaption>
                            </figure>
                        </div>
                    </div>
                </div>',
				$media_html,
				$content_box,
				esc_attr( $classes ),
				$data_lightbox_html,
				esc_attr( $image_scale ),
				esc_attr( $content_position ),
				esc_attr( $border_anim_class ),
				esc_attr( $item->media_url ),
				esc_attr( ' has_overlay' ),
				$custom_url,
				esc_attr( $empty_class ),
				$instagram_user_info_html,
				// esc_attr(get_post_meta($id , '_wp_attachment_image_alt', true)),
				$overlay === 'on' ? '<span class="df-overlay"></span>' : '',
				$item->media_type == 'VIDEO' ? 'media_type_video' : ''
			);
			$images .= $image;
		}

		return $images;
	}

	public function get_instagram_images( $request ) {
		$args          = $request->get_params();
		$gallery_array = self::df_jsg_options( $args );
		$images        = self::df_jsg_render_gallery_markup( $gallery_array );


		// create the display gallery code
		$data = $request->get_params();
		// $verifyNonce = isset($data['difl_5']) && $data['difl_5'] === 'difl_5' ? 'et_code_snippets_library_get_items' : 'et_admin_load_nonce';

		// Verify nonce for security
		// if (! wp_verify_nonce( $data['et_admin_load_nonce'], $verifyNonce )) {
		// wp_die();
		// }

		$images_array            = [];
		$builder                 = ! empty( $data['user_token_changed'] ) ? $data['user_token_changed'] : false;
		$instagram_client_id     = ( ! empty( $data['instagramClientID'] ) ) ? $data['instagramClientID'] : '';
		$instagram_client_secret = ( ! empty( $data['instagramClientSecret'] ) ) ? $data['instagramClientSecret'] : '';
		$instagram_user_token    = ( ! empty( $data['instagramUserToken'] ) ) ? $data['instagramUserToken'] : '';
		$item_limit              = ( ! empty( $data['item_limit'] ) ) ? $data['item_limit'] : '6';
		$cache_time              = ( ! empty( $data['cache_time'] ) ) ? $data['cache_time'] : '-1';
		$cache_time_type         = ( ! empty( $data['cache_time_type'] ) ) ? $data['cache_time_type'] : 'minute';
		$settings_data           = [
			'item_limit'      => $item_limit,
			'cache_time'      => $cache_time,
			'cache_time_type' => $cache_time_type,
		];
		$instagram_obj           = new \DF_Instagram_Process( $instagram_client_id, $instagram_client_secret, $instagram_user_token, $builder );
		$unique_module_name      = $data['unique_module_name'];
		$images_array            = $instagram_obj->get_instagram_data( $settings_data, $unique_module_name );
		if ( $data['instagram_post_only_image'] == 'on' ) {

			$media_type   = 'IMAGE';
			$images_array = array_filter(
				$images_array,
				function ( $value ) use ( $media_type ) {
					return $value->media_type == $media_type;
				},
				ARRAY_FILTER_USE_BOTH
			);
		}

		$options = self::df_intagram_gallery_options(
			[ 'images_array' => $images_array ],
			$data
		);

		$gallery        = [];
		$instagram_data = true;
		if ( $instagram_user_token === '' ) {
			$gallery['error'] = 'Please input access token';
		} else if ( isset( $instagram_obj->get_instagram_account_id( $instagram_user_token )->status ) && $instagram_obj->get_instagram_account_id( $instagram_user_token )->status === 400 ) {
			$gallery['error'] = 'Your Instagram User access token is not valid';

		} else if ( intval( count( $images_array ) ) < 1 ) {

			if ( $item_limit < 0 ) {
				$gallery['error'] = 'Item limit should more then 0';
			} else {
				$gallery['error'] = 'No data found from instagram';
			}
		} else {
			// $gallery['error'] = array(
			//     'status' => false,
			//     'msg'    => "success"
			// );
			$gallery = self::df_ing_render_images( $options );
		}

		$response = [
			'data' => $gallery,
		];

		return RESTController::response_success( $response );
	}

}

new API();
