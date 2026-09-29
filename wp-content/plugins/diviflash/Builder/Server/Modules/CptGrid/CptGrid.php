<?php

namespace DIFL\Server\Modules\CptGrid;

use \Df_Cpt_Taxonomy_Support;
use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\FrontEnd\Assets\DynamicAssetsUtils;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class CptGrid implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	public $slug = 'difl_cptgrid';
	public $vb_support = 'on';
	public $child_slug = 'difl_cptitem';
	public $icon_path;
	use Df_Cpt_Taxonomy_Support;

	protected $config_file = 'CptGrid';

    // public static function module_script_data($args)
    // {
    //     $elements = $args['elements'];

    //     // Expose module attrs to FE script
    //     $elements->script_data(
    //         [
    //             'attrName' => 'module',
    //         ]
    //     );
    // }

	protected $module_credits = [
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	];

	// public function init() {
	// 	$this->name             = esc_html__( 'CPT Grid', 'divi_flash' );
	// 	$this->main_css_element = "%%order_class%%";
	// 	$this->icon_path        = DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/cpt-grid.svg';
	// 	$this->init_cpt_tax(
	// 		'general',
	// 		'main_content',
	// 		[
	// 			'post_display' => 'by_tax',
	// 		]
	// 	);
	// }

	public function get_settings_modal_toggles() {
		return [
			'general'  => [
				'toggles' => [
					'main_content'    => esc_html__( 'CPT Settings', 'divi_flash' ),
					'item_background' => esc_html__( 'Item Background', 'divi_flash' ),
					'loader'          => esc_html__( 'Loader', 'divi_flash' ),
				],
			],
			'advanced' => [
				'toggles' => [
					'content_align'      => esc_html__( 'Alignment', 'divi_flash' ),
					'layout'             => esc_html__( 'Layout', 'divi_flash' ),
					'item_outer_wrapper' => esc_html__( 'Item Outer Wrapper', 'divi_flash' ),
					'item_inner_wrapper' => esc_html__( 'Item Inner Wrapper', 'divi_flash' ),
					'pagination'         => esc_html__( 'Pagination Button Styles', 'divi_flash' ),
					'active_pagination'  => esc_html__( 'Active Pagination Number', 'divi_flash' ),
					'df_overflow'        => esc_html__( 'Overflow', 'divi_flash' ),
				],
			],
		];
	}

	public function get_advanced_fields_config() {
		$advanced_fields                 = [];
		$advanced_fields['text']         = false;
		$advanced_fields['link_options'] = false;

		$advanced_fields['fonts'] = [
			// 'default'   => false,
			'pagination'        => [
				'label'       => et_builder_i18n( 'Pagination' ),
				'css'         => [
					'main'  => "%%order_class%% .pagination .page-numbers",
					'hover' => "%%order_class%% .pagination .page-numbers:hover",
				],
				'line_height' => [
					'default' => floatval( et_get_option( 'body_font_height', '1.7' ) ) . 'em',
				],
				'font_size'   => [
					'default' => '16px',
				],
				'toggle_slug' => 'pagination',
			],
			'active_pagination' => [
				'label'       => et_builder_i18n( 'Pagination' ),
				'css'         => [
					'main'  => "%%order_class%% .pagination .page-numbers.current",
					'hover' => "%%order_class%% .pagination .page-numbers.current:hover",
				],
				'line_height' => [
					'default' => floatval( et_get_option( 'body_font_height', '1.7' ) ) . 'em',
				],
				'font_size'   => [
					'default' => '16px',
				],
				'toggle_slug' => 'active_pagination',
			],
		];

		$advanced_fields['borders'] = [
			'item_outer'        => [
				'css'          => [
					'main' => [
						'border_radii'        => '%%order_class%% .df-cpt-outer-wrap',
						'border_radii_hover'  => '%%order_class%% .df-cpt-outer-wrap:hover',
						'border_styles'       => '%%order_class%% .df-cpt-outer-wrap',
						'border_styles_hover' => '%%order_class%% .df-cpt-outer-wrap:hover',
					],
				],
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'item_outer_wrapper',
				'label_prefix' => 'Item',
			],
			'item'              => [
				'css'          => [
					'main' => [
						'border_radii'        => '%%order_class%% .df-cpt-inner-wrap',
						'border_radii_hover'  => '%%order_class%% .df-cpt-inner-wrap:hover',
						'border_styles'       => '%%order_class%% .df-cpt-inner-wrap',
						'border_styles_hover' => '%%order_class%% .df-cpt-inner-wrap:hover',
					],
				],
				'tab_slug'     => 'advanced',
				'toggle_slug'  => 'item_inner_wrapper',
				'label_prefix' => 'Item',
			],
			'pagination'        => [
				'css'         => [
					'main' => [
						'border_radii'        => '%%order_class%% .pagination .page-numbers:not(.dots)',
						'border_radii_hover'  => '%%order_class%% .pagination .page-numbers:not(.dots):hover',
						'border_styles'       => '%%order_class%% .pagination .page-numbers:not(.dots)',
						'border_styles_hover' => '%%order_class%% .pagination .page-numbers:not(.dots):hover',
					],
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'pagination',
				// 'label_prefix'      => 'Pagination'
			],
			'active_pagination' => [
				'css'         => [
					'main' => [
						'border_radii'        => '%%order_class%% .pagination .page-numbers.current',
						'border_radii_hover'  => '%%order_class%% .pagination .page-numbers.current:hover',
						'border_styles'       => '%%order_class%% .pagination .page-numbers.current',
						'border_styles_hover' => '%%order_class%% .pagination .page-numbers.current:hover',
					],
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'active_pagination',
				// 'label_prefix'      => 'Pagination'
			],
		];

		$advanced_fields['box_shadow'] = [
			'item_outer'        => [
				'css'         => [
					'main'  => "%%order_class%% .df-cpt-outer-wrap",
					'hover' => "%%order_class%% .df-cpt-outer-wrap:hover",
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'item_outer_wrapper',
			],
			'item'              => [
				'css'         => [
					'main'  => "%%order_class%% .df-cpt-inner-wrap",
					'hover' => "%%order_class%% .df-cpt-inner-wrap:hover",
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'item_inner_wrapper',
			],
			'pagination'        => [
				'css'         => [
					'main'  => "%%order_class%% .pagination .page-numbers:not(.dots)",
					'hover' => "%%order_class%% .pagination .page-numbers:not(.dots):hover",
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'pagination',
			],
			'active_pagination' => [
				'css'         => [
					'main'  => "%%order_class%% .pagination .page-numbers.current",
					'hover' => "%%order_class%% .pagination .page-numbers.current:hover",
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'active_pagination',
			],
		];

		$advanced_fields['filters'] = false;

		return $advanced_fields;
	}

	/**
	 * Get blog posts for cptgrid module
	 *
	 * @return string blog post markup
	 */
	public function get_posts() {

		global $post, $paged, $wp_query, $wp_the_query, $wp_filter, $__et_blog_module_paged, $df_cpt_items, $df_cpt_items_outside;

		$main_query = $wp_the_query;

		$use_current_loop        = isset( $this->props['use_current_loop'] ) ? $this->props['use_current_loop'] : 'off';
		$offset_number           = $this->props['offset_number'];
		$posts_number            = $this->props['posts_number'];
		$post_display            = $this->props['post_display'];
		$orderby                 = ! empty( $this->props['orderby'] ) ? $this->props['orderby'] : '';
		$layout                  = $this->props['layout'];
		$use_image_as_background = $this->props['use_image_as_background'];
		$use_background_scale    = $this->props['use_background_scale'];
		$current_post_id         = isset( $post->ID ) && ! empty( $post->ID ) ? $post->ID : 0;
		if ( 'off' === $use_current_loop && ( ! isset( $this->props['post_type'] ) || 'select' === $this->props['post_type'] ) ) {
			return;
		}

		$query_args = [
			'posts_per_page' => intval( $this->props['posts_number'] ),
			'post_status'    => [ 'publish' ],
			'perm'           => 'readable',
			'post_type'      => $this->props['post_type'],
		];

		// display post_types by taxonomies
		if ( 'by_tax' === $post_display ) {
			$this->get_taxonomy_values();

			if ( '' != $this->selected_terms ) {

				if ( is_array( $this->selected_terms ) && in_array( 'current', $this->selected_terms, true ) ) {
					if ( is_single() ) {
						// Get the actual current post ID using ET_Post_Stack (more reliable than global $post)
						$main_query_post = \ET_Post_Stack::get_main_post();
						$actual_post_id = ( null !== $main_query_post && isset( $main_query_post->ID ) ) ? $main_query_post->ID : $current_post_id;
						
						$current_terms = implode(",", array_column(wp_get_post_terms($actual_post_id , $this->selected_taxonomy), "term_taxonomy_id") );
	                    $terms = explode(',', $current_terms);
					}
					if ( is_archive() && empty( $terms ) ) {
						$terms = get_queried_object()->term_id;
					}
				} else {
					$terms = is_array( $this->selected_terms ) ? $this->selected_terms : explode( ',', $this->selected_terms );
				}
				$query_args['tax_query'] = [ //phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
					[
						'taxonomy' => $this->selected_taxonomy,
						'field'    => 'term_id',
						'terms'    => $terms,
					],
				];

			}
		}

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


		if ( is_single() ) { // Exclude Current post. This feature added from version 1.2.10
			$main_query_post = \ET_Post_Stack::get_main_post();

			if ( null !== $main_query_post ) {
				$query_args['post__not_in'][] = $main_query_post->ID;
			}
		}
		$df_pg_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
		if ( is_front_page() ) {
			$paged = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		if ( $__et_blog_module_paged > 1 ) {
			$df_pg_paged         = $__et_blog_module_paged;
			$paged               = $__et_blog_module_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$query_args['paged'] = $__et_blog_module_paged;
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

			$custom['orderby'] = $query_args['orderby'];
			$custom['order']   = $query_args['order'];

			// Trick WP into reporting this query as the main query so third party filters
			// that check for is_main_query() are applied.
			$wp_the_query = $wp_query = new \WP_Query( array_merge( $original, $custom ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}

		// Manually set the max_num_pages to make the `next_posts_link` work
		if ( '' !== $offset_number && ! empty( $offset_number ) ) {
			global $wp_query;
			$wp_query->found_posts   = max( 0, $wp_query->found_posts - intval( $offset_number ) );
			$posts_number            = intval( $posts_number );
			$wp_query->max_num_pages = $posts_number > 1 ? ceil( $wp_query->found_posts / $posts_number ) : 1;
		}

		echo '<div class="df-cpts-wrap layout-' . esc_attr( $layout ) . '">';

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();

				$width = 'on' === 1080;
				// $width = 'on' === $fullwidth ? 1080 : 400;
				$width = (int) apply_filters( 'et_pb_blog_image_width', $width );

				$height             = 'on' === 675;
				$height             = (int) apply_filters( 'et_pb_blog_image_height', $height );
				$equal_height_class = $this->props['equal_height'] === 'on' ? ' df-equal-height' : '';

				$outer_content = '';
				$inner_content = '';

				?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( "df-cpt-item v2{$equal_height_class}" ) ?>
					<?php if ( 'on' === $this->props['entire_item_clickable'] ) {
						echo 'onclick="location.href=\'', esc_url( get_the_permalink() ), '\'" style="cursor:pointer;"';
					} ?>
                >
                    <div class="df-cpt-outer-wrap df-hover-trigger" <?php echo 'on' !== $use_background_scale ? et_core_esc_previously( df_cpt_image_as_background( $use_image_as_background ) ) : ''; ?>>
						<?php
						// render markup to achive the scale effect.
						if ( 'on' === $use_image_as_background && 'on' === $use_background_scale ) {
							echo '<div class="df-cpt-bg-on-hover"><div ' . et_core_esc_previously( df_cpt_image_as_background( $use_image_as_background ) ) . '></div></div>';
						}
						if ( ! empty( $df_cpt_items_outside ) ) {
							foreach ( $df_cpt_items_outside as $post_item ) {

								if ( ! isset( $post_item['type'] ) ) {
									continue;
								}

								$callback = 'df_cpt_' . $post_item['type'];

								call_user_func( $callback, $post_item );

							} // end of foreach
						}
						?>
                        <div class="df-cpt-inner-wrap">
							<?php
							foreach ( $df_cpt_items as $post_item ) {

								if ( ! isset( $post_item['type'] ) ) {
									continue;
								}

								$callback = 'df_cpt_' . $post_item['type'];

								call_user_func( $callback, $post_item );

							} // end of foreach
							?>
                        </div>
                    </div>
                </article>
				<?php
			} // endwhile
		}

		echo '</div>'; // end of df-pg-posts

		// ajax navigation
		if ( 'on' === $this->props['show_pagination'] ) {
			if ( function_exists( 'wp_pagenavi' ) ) {
				wp_pagenavi();
			} else {
				add_filter( 'get_pagenum_link', [ self::class, 'filter_pagination_url' ] );
				if ( 'on' !== $this->props['use_number_pagination'] ) {
					cpt_render_pagination(
						$this->props['older_text'],
						$this->props['newer_text'],
						$this->props['use_icon_only_at_pagination']
					);
				} else {
					cpt_render_number_pagination(
						$this->props['older_text'],
						$this->props['newer_text'],
						$this->props['use_icon_only_at_pagination']
					);
				}
				remove_filter( 'get_pagenum_link', [ self::class, 'filter_pagination_url' ] );
			}
		}

		$wp_the_query = $wp_query = $main_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions

		$posts = ob_get_contents();
		ob_end_clean();

		if ( empty( $df_cpt_items ) ) {
			$posts = '';
		}
		$df_cpt_items         = [];
		$df_cpt_items_outside = [];

		return $posts;
	}

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/cptgrid' )->customCssFields;
	}

    public function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = $this->get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';

		$common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .pagination .older:before, {$orderClass} .pagination .prev:before",
			'attr'                => $attrs['next_prev_icon']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$attrValue = $value['attrValue'] ?? 'set_1';

				return "content: '" . $this->arrow_icon( $attrValue, 'prev' ) . "'";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .pagination .newer:after, {$orderClass} .pagination .next:after",
			'attr'                => $attrs['next_prev_icon']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$attrValue = $value['attrValue'] ?? 'set_1';

				return "content: '" . $this->arrow_icon( $attrValue, 'next' ) . "'";
			},
		] );

        if( $this->props['alignment'] ) {
            $common_styles[] = CommonStyle::style( [
                'selector'  => "{$orderClass} .df-cpt-inner-wrap",
                'attr'      => $attrs['alignment']['innerContent'] ?? null,
                'property'  => 'text-align',
                'important' => true,
            ] );
        }

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .pagination",
			'attr'      => $attrs['pagination_align']['innerContent'] ?? null,
			'property'  => 'justify-content',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cpt-item",
			'attr'                => $attrs['column']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$attrValue = $value['attrValue'] ?? '';
				$width     = ! empty( $attrValue ) ? round( 100 / (int) $attrValue, 2 ) . '%' : '';

				return "width: {$width};";
			},
		] );

		if ( ( $all_props['use_image_as_background'] ?? '' ) === 'on' && ( $all_props['use_background_scale'] ?? '' ) === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => ".difl_cptgrid{$orderClass}",
				'attr'                => $attrs['use_image_as_background']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'z-index: 1;';
				},
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cpt-item",
			'attr'                => $attrs['gutter']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$attrValue   = $value['attrValue'] ?? '';
				$gutterValue = ! empty( $attrValue ) ? (int) $attrValue : 0;
				$halfGutter  = $gutterValue / 2;

				return "padding-left: {$halfGutter}px; padding-right: {$halfGutter}px; padding-bottom: {$gutterValue}px;";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_cptgrid_container .df-cpts-wrap",
			'attr'                => $attrs['gutter']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$attrValue   = $value['attrValue'] ?? '';
				$gutterValue = ! empty( $attrValue ) ? (int) $attrValue : 0;
				$halfGutter  = $gutterValue / 2;

				return "margin-left: -{$halfGutter}px; margin-right: -{$halfGutter}px;";
			},
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cpts-wrap .df-cpt-outer-wrap",
			'attr'     => $attrs['outer_wrpper_visibility']['innerContent'] ?? null,
            'property'  => 'overflow',
			'important' => true,
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cpts-wrap .df-cpt-inner-wrap",
			'attr'     => $attrs['inner_wrpper_visibility']['innerContent'] ?? null,
            'property'  => 'overflow',
			'important' => true,
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass}",
			'attr'     => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cpt-outer-wrap",
			'attr'     => $attrs['item_outer']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cpt-inner-wrap",
			'attr'     => $attrs['item_inner']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .page-numbers",
			'attr'     => $attrs['pagination']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .page-numbers.current",
			'attr'     => $attrs['active_pagination']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
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

	/**
	 * Module render method.
	 *
	 * @return String
	 */
	public function render( $attrs, $content, $render_slug ) {
		global $df_cpt_items, $df_cpt_items_outside;
		wp_enqueue_script( 'imageload' );
		wp_enqueue_script( 'df-imagegallery-lib' );
		if ( 'off' !== $this->props['show_pagination'] ) {
			wp_enqueue_script( 'fitvids' );
		}
		wp_enqueue_script( 'df-cpt-grid' );

        df_load_pagination_script();

		$loader_data = [
			'status'     => isset( $this->props['enable_loader'] ) ? $this->props['enable_loader'] : 'off',
			'color'      => isset( $this->props['loader_color'] ) ? $this->props['loader_color'] : '#284cc1',
			'size'       => isset( $this->props['loader_size'] ) ? $this->props['loader_size'] : '10px',
			'type'       => isset( $this->props['loader_type'] ) ? $this->props['loader_type'] : 'bar',
			'background' => isset( $this->props['loader_bg_color'] ) ? $this->props['loader_bg_color'] : '#ffffff',
			'alignment'  => isset( $this->props['loader_alignment'] ) ? $this->props['loader_alignment'] : 'center',
			'margin'     => isset( $this->props['loader_margin'] ) ? array_slice( explode( "|", $this->props['loader_margin'] ), 0, 4 ) : [
				'0px',
				'0px',
				'0px',
				'0px',
			],
		];

		$data = [
			'layout'                  => $this->props['layout'],
			'on_scroll_load'          => $this->props['on_scroll_load'],
			'use_current_loop'        => $this->props['use_current_loop'],
			'post_type'               => $this->props['post_type'],
			'posts_number'            => $this->props['posts_number'],
			'post_display'            => $this->props['post_display'],
			'orderby'                 => $this->props['orderby'],
			'offset_number'           => $this->props['offset_number'],
			'use_image_as_background' => $this->props['use_image_as_background'],
			'use_background_scale'    => $this->props['use_background_scale'],
			'equal_height'            => $this->props['equal_height'],
			'entire_item_clickable'   => $this->props['entire_item_clickable'],
			'taxonomy_values'         => $this->get_taxonomy_values(),
			'selected_terms'          => $this->selected_terms,
			'selected_taxonomy'       => $this->selected_taxonomy,
			'df_cpt_items'            => $df_cpt_items,
			'df_cpt_items_outside'    => $df_cpt_items_outside,
			'loader'                  => $loader_data,
		];

		return sprintf( '<div class="et_pb_module_inner"><div class="df_cptgrid_container et_pb_ajax_pagination_container" data-settings=\'%2$s\'>%1$s</div></div>',
			$this->get_posts(),
			wp_json_encode( $data )
		);
	}

	/**
	 * next and prev icons
	 *
	 * @param String set of the icon to use
	 * @param String next/prev
	 */
	public function arrow_icon( $set = 'set_1', $type = 'next' ) {
		$icons = [
			'set_1' => [
				'next' => '5',
				'prev' => '4',
			],
			'set_2' => [
				'next' => '$',
				'prev' => '#',
			],
			'set_3' => [
				'next' => '9',
				'prev' => '8',
			],
			'set_4' => [
				'next' => 'E',
				'prev' => 'D',
			],
		];

		return $icons[ $set ][ $type ];
	}

	public static function filter_pagination_url( $result ) {
		return add_query_arg( 'df_cpt', '', $result );
	}

}
