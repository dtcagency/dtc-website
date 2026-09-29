<?php

namespace DIFL\Server\Modules\CptFilter;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use Df_Cpt_Taxonomy_Support;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class CptCarousel implements DependencyInterface
{
    use ModuleUtil;
    use HasProps;

    protected $config_file = 'CptCarousel';

    public $slug = 'difl_cptcarousel';
    public $vb_support = 'on';
    public $child_slug = 'difl_cptitem';
    public $icon_path;
    use Df_Cpt_Taxonomy_Support;

    protected $module_credits = [
        'module_uri' => '',
        'author'     => 'DiviFlash',
        'author_uri' => '',
    ];

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/cptcarousel' )->customCssFields;
	}

    public function init()
    {
        $this->name             = esc_html__('CPT Carousel', 'divi_flash');
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        = DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/cpt-carousel.svg';
        $this->init_cpt_tax(
            'general',
            'settings',
            [
                'post_display' => 'by_tax',
            ]
        );
    }

    public function get_settings_modal_toggles()
    {
        return [
            'general'  => [
                'toggles' => [
                    'settings'              => esc_html__('Custom Post Settings', 'divi_flash'),
                    'carousel_settings'     => esc_html__('Carousel Settings', 'divi_flash'),
                    'advanced_settings'     => esc_html__('Advanced Settings', 'divi_flash'),
                    'item_outer_background' => esc_html__('Item Outer Wrapper Background', 'divi_flash'),
                    'item_inner_background' => esc_html__('Item Inner wrapper Background', 'divi_flash'),
                ],
            ],
            'advanced' => [
                'toggles' => [
                    'content_align'      => esc_html__('Alignment', 'divi_flash'),
                    // 'layout'                => esc_html__('Layout', 'divi_flash'),
                    'item_outer_wrapper' => esc_html__('Item Outer Wrapper', 'divi_flash'),
                    'item_inner_wrapper' => esc_html__('Item Inner Wrapper', 'divi_flash'),
                    'arrows'             => esc_html__('Arrows', 'divi_flash'),
                    'dots'               => esc_html__('Dots', 'divi_flash'),
                ],
            ],
        ];
    }

    public function get_advanced_fields_config()
    {
        $advanced_fields                 = [];
        $advanced_fields['text']         = false;
        $advanced_fields['link_options'] = false;

        $advanced_fields['fonts'] = [
            // 'default'   => false,
        ];

        $advanced_fields['borders'] = [
            'item_outer' => [
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
            'item'       => [
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
        ];

        $advanced_fields['box_shadow'] = [
            'item_outer' => [
                'css'         => [
                    'main'  => "%%order_class%% .df-cpt-outer-wrap",
                    'hover' => "%%order_class%% .df-cpt-outer-wrap:hover",
                ],
                'tab_slug'    => 'advanced',
                'toggle_slug' => 'item_outer_wrapper',
            ],
            'item'       => [
                'css'         => [
                    'main'  => "%%order_class%% .df-cpt-inner-wrap",
                    'hover' => "%%order_class%% .df-cpt-inner-wrap:hover",
                ],
                'tab_slug'    => 'advanced',
                'toggle_slug' => 'item_inner_wrapper',
            ],
        ];

        $advanced_fields['transform'] = false;
        $advanced_fields['filters']   = false;

        return $advanced_fields;
    }

    /**
     * Get custom post type posts for CPT Carousel module
     *
     * @return string cpt post markup
     */
    public function get_posts()
    {
        global $post, $paged, $wp_query, $wp_the_query, $wp_filter, $__et_blog_module_paged, $df_cpt_items, $df_cpt_items_outside;

        $main_query = $wp_the_query;

        $use_current_loop        = isset($this->props['use_current_loop']) ? $this->props['use_current_loop'] : 'off';
        $post_type               = $this->props['post_type'];
        $offset_number           = $this->props['offset_number'];
        $posts_number            = $this->props['posts_number'];
        $post_display            = $this->props['post_display'];
        $orderby                 = ! empty($this->props['orderby']) ? $this->props['orderby'] : '';
        $use_image_as_background = $this->props['use_image_as_background'];
        $use_background_scale    = $this->props['use_background_scale'];
        $current_post_id         = isset( $post->ID ) && ! empty( $post->ID ) ? $post->ID : 0;
        $query_args              = [
            'posts_per_page' => intval($this->props['posts_number']),
            'post_status'    => ['publish'],
            'perm'           => 'readable',
            'post_type'      => $this->props['post_type'],
        ];

        // display post_types by taxonomies
        if ('by_tax' === $post_display) {
            $this->get_taxonomy_values();
            // if (! is_array($this->selected_terms)) {
            //     $this->selected_terms = explode(',', $this->selected_terms);
            // }
            if ('' != $this->selected_terms) {
                // $query_args['tax_query'] = array( //phpcs:ignore WordPress.DB.SlowDBQuery
                //     'relation' => 'AND',
                //     array(
                //         'taxonomy'  => $this->selected_taxonomy,
                //         'field'     => 'term_id',
                //         'terms'     => explode(',', $this->selected_terms)
                //     )
                // );
                if (is_array( $this->selected_terms ) && in_array( 'current', $this->selected_terms, true )) {
                    if (is_single()) {
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
                    $terms = is_array($this->selected_terms) ? $this->selected_terms : explode(',', $this->selected_terms);
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
        // if ( 'recent' == $post_display) {
        if ('5' === $orderby) {
            $query_args['orderby'] = 'rand';
        } else if ('2' === $orderby) {
            $query_args['orderby'] = 'date';
            $query_args['order']   = 'ASC';
        } else if ('3' === $orderby) {
            $query_args['orderby'] = 'title';
            $query_args['order']   = 'ASC';
        } else if ('4' === $orderby) {
            $query_args['orderby'] = 'title';
            $query_args['order']   = 'DESC';
        } else {
            $query_args['orderby'] = 'date';
            $query_args['order']   = 'DESC';
        }
        //}
        if (is_single()) { // Exclude Current post. This feature added from version 1.2.10
            $main_query_post = \ET_Post_Stack::get_main_post();

            if (null !== $main_query_post) {
                $query_args['post__not_in'][] = $main_query_post->ID;
            }
        }
        $df_pg_paged = is_front_page() ? get_query_var('page') : get_query_var('paged');
        if (is_front_page()) {
            $paged = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
        }

        if ($__et_blog_module_paged > 1) {
            $df_pg_paged         = $__et_blog_module_paged;
            $paged               = $__et_blog_module_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
            $query_args['paged'] = $__et_blog_module_paged;
        } else {
            $query_args['paged'] = $df_pg_paged;
        }

        if ('' !== $offset_number && ! empty($offset_number)) {
            /**
             * Offset + pagination don't play well. Manual offset calculation required
             *
             * @see: https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
             */
            if ($paged > 1) {
                $query_args['offset'] = (($df_pg_paged - 1) * intval($posts_number)) + intval($offset_number);
            } else {
                $query_args['offset'] = intval($offset_number);
            }
        }

        ob_start();

        if ('off' === $use_current_loop) {
            query_posts($query_args); // phpcs:ignore WordPress.WP.DiscouragedFunctions
        } elseif (is_singular()) {
            // Force an empty result set in order to avoid loops over the current post.
            query_posts(['post__in' => [0]]); // phpcs:ignore WordPress.WP.DiscouragedFunctions
            // $show_no_results_template = false;
        } else {
            // Only allow certain args when `Posts For Current Page` is set.
            $original = $wp_query->query_vars;
            $custom   = array_intersect_key($query_args, array_flip(['posts_per_page', 'offset', 'paged']));

            // Trick WP into reporting this query as the main query so third party filters
            // that check for is_main_query() are applied.
            $wp_the_query = $wp_query = new \WP_Query(array_merge($original, $custom)); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
        }

        // Manually set the max_num_pages to make the `next_posts_link` work
        if ('' !== $offset_number && ! empty($offset_number)) {
            global $wp_query;
            $wp_query->found_posts   = max(0, $wp_query->found_posts - intval($offset_number));
            $posts_number            = intval($posts_number);
            $wp_query->max_num_pages = $posts_number > 1 ? ceil($wp_query->found_posts / $posts_number) : 1;
        }


        if (have_posts()) {
            echo '<div class="df-cpts-wrap swiper-wrapper">';
            while (have_posts()) {
                the_post();

                $width = 'on' === 1080;
                // $width = 'on' === $fullwidth ? 1080 : 400;
                $width = (int) apply_filters('et_pb_blog_image_width', $width);

                $height             = 'on' === 675;
                $height             = (int) apply_filters('et_pb_blog_image_height', $height);
                $equal_height_class = $this->props['equal_height'] === 'on' ? ' df-equal-height' : '';

                $outer_content = '';
                $inner_content = '';

?>
                <article
                    id="post-<?php the_ID(); ?>" <?php post_class("df-cpt-item swiper-slide v2{$equal_height_class}") ?>
                    <?php if ('on' === $this->props['entire_item_clickable']) {
                        echo 'onclick="location.href=\'', esc_url(get_the_permalink()), '\'" style="cursor:pointer;"';
                    } ?>>
                    <div class="df-cpt-outer-wrap df-hover-trigger" <?php echo 'on' !== $use_background_scale ? et_core_esc_previously(df_cpt_image_as_background($use_image_as_background)) : ''; ?>>
                        <?php
                        // render markup to achive the scale effect.
                        if ('on' === $use_image_as_background && 'on' === $use_background_scale) {
                            echo '<div class="df-blogcarousel-bg-on-hover"><div ' . et_core_esc_previously(df_cpt_image_as_background($use_image_as_background)) . '></div></div>';
                        }
                        if (! empty($df_cpt_items_outside)) {
                            foreach ($df_cpt_items_outside as $post_item) {

                                if (! isset($post_item['type'])) {
                                    continue;
                                }

                                $callback = 'df_cpt_' . $post_item['type'];

                                call_user_func($callback, $post_item);
                            } // end of foreach
                        }
                        ?>
                        <div class="df-cpt-inner-wrap">
                            <?php
                            foreach ($df_cpt_items as $post_item) {

                                if (! isset($post_item['type'])) {
                                    continue;
                                }

                                $callback = 'df_cpt_' . $post_item['type'];

                                call_user_func($callback, $post_item);
                            } // end of foreach
                            ?>
                        </div>
                    </div>
                </article>

<?php
            } // endwhile

            echo '</div>'; // end of df-pg-posts
        } else {
            echo "<h3 style='text-align:center;background:#eee; padding: 10px 20px;'>No Post Found.</h3>";
        }


        $wp_the_query = $wp_query = $main_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
        wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions

        $posts = ob_get_contents();
        ob_end_clean();
        if (empty($df_cpt_items) && empty($df_cpt_items_outside)) {
            $posts = '<h3>Please add new post element.</h3>';
        }

        $df_cpt_items         = [];
        $df_cpt_items_outside = [];

        return $posts;
    }

    function getCoverflowColorStyle($attr, $position)
    {
        $color_dark   = $attr['coverflow_color_dark'] ?? 'rgba(0,0,0,1)';
        $color_light = $attr['coverflow_color_light'] ?? 'rgba(0,0,0,0)';

        return "background-image: linear-gradient(to " . $position . ", " . $color_dark . ", " . $color_light . ");";
    }

    public function addOverflowHidden($props)
    {
        return (
            isset($props['attrValue']['radius']['bottomLeft']) &&
            isset($props['attrValue']['radius']['bottomRight']) &&
            isset($props['attrValue']['radius']['topLeft']) &&
            isset($props['attrValue']['radius']['topRight'])
        ) ? 'overflow: hidden !important;' : '';
    }

    public function get_common_styles($args)
    {
        $attrs      = $this->get_merged_attrs($args);
        $attrs      = self::build_attr_for_styles($attrs);
        $attrs      = $this->get_default_value_for_styles($attrs);
        $orderClass = $args['orderClass'] ?? '';

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .swiper-wrapper",
            'attr'                => $attrs['post_type']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return 'width:fit-content;';
            },
        ]);


        if ('on' === ($this->props['equal_height'])) {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df-cpt-item",
                'attr'                => $attrs['equal_height']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return 'align-self: auto;';
                },
            ]);
        }


        if( $this->props['alignment'] ) {
            $common_styles[] = CommonStyle::style([
                'selector'  => "{$orderClass} .df-cpt-inner-wrap",
                'attr'      => $attrs['alignment']['innerContent'] ?? null,
                'property'  => 'text-align',
                'important' => true,
            ]);
        }

        if ('on' === ($this->props['coverflow_shadow'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-container-3d .swiper-slide-shadow-left",
                'attr'     => $attrs['coverflow']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    return $this->getCoverflowColorStyle($props['attrValue'] ?? [], 'left');
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-container-3d .swiper-slide-shadow-right",
                'attr'     => $attrs['coverflow']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    return $this->getCoverflowColorStyle($props['attrValue'] ?? [], 'right');
                }
            ]);
        }


        if ('on' === ($this->props['arrow'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cptc_arrows",
                'attr'                => $attrs['arrow_position']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    return $this->arrow_pos_styles($props['attrValue'] ?? '');
                },
            ]);

            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cptc_arrows",
                'attr'                => $attrs['arrow_align']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    return "justify-content: {$props['attrValue']};";
                },
            ]);


            if ('on' === ($this->props['arrow_circle'] ?? '')) {
                $common_styles[] = CommonStyle::style([
                    'selector'            => "{$orderClass} .df_cptc_arrows > div",
                    'attr'                => $attrs['arrow_circle']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return 'border-radius: 50%;';
                    },
                ]);
            }


            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_cptc_arrows div",
                'attr'     => $attrs['arrow_color']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if (!($props['breakpoint'] === 'desktop' && $props['state'] === 'hover')) {
                        return 'color: ' . $props['attrValue'];
                    }
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}:hover .df_cptc_arrows div",
                'attr'     => $attrs['arrow_color']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if ($props['breakpoint'] === 'desktop' && $props['state'] === 'hover') {
                        return 'color: ' . $props['attrValue'];
                    }
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_cptc_arrows div",
                'attr'     => $attrs['arrow_background']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if (!($props['breakpoint'] === 'desktop' && $props['state'] === 'hover')) {
                        return 'background-color: ' . $props['attrValue'];
                    }
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}:hover .df_cptc_arrows div",
                'attr'     => $attrs['arrow_background']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if ($props['breakpoint'] === 'desktop' && $props['state'] === 'hover') {
                        return 'background-color: ' . $props['attrValue'];
                    }
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_cptc_arrows div",
                'attr'     => $attrs['arrow_opacity']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if (!($props['breakpoint'] === 'desktop' && $props['state'] === 'hover')) {
                        return 'opacity: ' . $props['attrValue'];
                    }
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}:hover .df_cptc_arrows div",
                'attr'     => $attrs['arrow_opacity']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if ($props['breakpoint'] === 'desktop' && $props['state'] === 'hover') {
                        return 'opacity: ' . $props['attrValue'];
                    }
                }
            ]);

            if ('off' === ($this->props['loop'] ?? '')) {
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} .df_cptc_arrows div.swiper-button-disabled",
                    'attr'     => $attrs['arrow_opacity_disable']['innerContent'] ?? null,
                    'declarationFunction' => function ($props) {
                        if (!($props['breakpoint'] === 'desktop' && $props['state'] === 'hover')) {
                            return 'opacity: ' . $props['attrValue'];
                        }
                    }
                ]);

                $common_styles[] = CommonStyle::style([
                    'selector' => "{$orderClass}:hover .df_cptc_arrows div.swiper-button-disabled",
                    'attr'     => $attrs['arrow_opacity_disable']['innerContent'] ?? null,
                    'declarationFunction' => function ($props) {
                        if ($props['breakpoint'] === 'desktop' && $props['state'] === 'hover') {
                            return 'opacity: ' . $props['attrValue'];
                        }
                    }
                ]);
            }

            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cptc_arrows div.swiper-button-prev:after",
                'attr'                => $attrs['prev_arrow_font_icon']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return $this->get_font_style($this->props['prev_arrow_font_icon'] ?? '');
                },
            ]);

            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cptc_arrows div.swiper-button-next:after",
                'attr'                => $attrs['next_arrow_font_icon']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return $this->get_font_style($this->props['next_arrow_font_icon'] ?? '');
                },
            ]);

            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cptc_arrows div.swiper-button-prev:after",
                'attr'                => $attrs['arrow_prev_icon_icon_size']['innerContent'] ?? null,
                'property'            => 'font-size',
            ]);

            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cptc_arrows div.swiper-button-next:after",
                'attr'                => $attrs['arrow_next_icon_icon_size']['innerContent'] ?? null,
                'property'            => 'font-size',
            ]);
        }


        if ('on' === ($this->props['dots'] ?? '')) {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .swiper-pagination",
                'attr'                => $attrs['dots_position']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return $this->arrow_pos_styles($this->props['dots_position'] ?? '');
                },
            ]);


            if ('on' === ($this->props['large_active_dot'] ?? '')) {
                $common_styles[] = CommonStyle::style([
                    'selector'            => "{$orderClass} .swiper-pagination .swiper-pagination-bullet-active",
                    'attr'                => $attrs['large_active_dot']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return 'width: 40px; border-radius: 20px;';
                    },
                ]);
            }


            if (! empty($this->props['dots_align'])) {
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$orderClass} .swiper-pagination",
                    'attr'     => $attrs['dots_align']['innerContent'] ?? null,
                    'property' => 'text-align',
                ]);
            }


            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-pagination span",
                'attr'     => $attrs['dots_color']['innerContent'] ?? null,
                'property' => 'background',
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet-active",
                'attr'     => $attrs['active_dots_color']['innerContent'] ?? null,
                'property' => 'background',
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass}",
            'attr'     => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function ($props) {
                return $this->addOverflowHidden($props);
            }
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df-cpt-outer-wrap",
            'attr'     => $attrs['item_outer']['decoration']['border'] ?? null,
            'declarationFunction' => function ($props) {
                return $this->addOverflowHidden($props);
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df-cpt-inner-wrap",
            'attr'     => $attrs['item_inner']['decoration']['border'] ?? null,
            'declarationFunction' => function ($props) {
                return $this->addOverflowHidden($props);
            },
        ]);

        foreach ($common_styles as $common_style) {
            if (empty($common_style)) {
                continue;
            }

            foreach ($common_style as $style) {
                $styles[] = $style;
            }
        }

        return $styles ?? [];
    }

    public function arrow_pos_styles($value = 'middle')
    {
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

        return $options[$value] ?? '';
    }

    public function render($attrs, $content, $render_slug)
    {
        wp_enqueue_script('swiper-script');
        wp_enqueue_script('df-cpt-carousel');

        wp_enqueue_style('wp-mediaelement');
        wp_enqueue_script('wp-mediaelement');

        $order_class  = $this->order_class;
        $order_number = str_replace('_', '', str_replace($this->slug, '', $order_class));
        $class        = '';

        $item_spacing_desktop = $attrs['item_spacing']['innerContent']['desktop']['value'] ?? '30px';
        $item_spacing_tablet = $attrs['item_spacing']['innerContent']['tablet']['value'] ?? '';
        $item_spacing_phone = $attrs['item_spacing']['innerContent']['phone']['value'] ?? '';

        $data = [
            'effect'              => $this->props['carousel_type'],
            'desktop'             => $this->props['item_desktop'],
            'tablet'              => $this->props['item_tablet'],
            'mobile'              => $this->props['item_mobile'],
            'loop'                => 'on' === $this->props['loop'] ? true : false,
            'item_spacing'        => $item_spacing_desktop,
            'item_spacing_tablet' => $item_spacing_tablet,
            'item_spacing_phone'  => $item_spacing_phone,
            'arrow'               => $this->props['arrow'],
            'dots'                => $this->props['dots'],
            'autoplay'            => $this->props['autoplay'],
            'auto_delay'          => $this->props['autospeed'],
            'speed'               => $this->props['speed'],
            'pause_hover'         => $this->props['pause_hover'],
            'centeredSlides'      => $this->props['centered_slides'],
            'order'               => $order_number,
        ];
        if ('coverflow' === $this->props['carousel_type']) {
            $data['slideShadows'] = $this->props['coverflow_shadow'];
            $data['rotate']       = $this->props['coverflow_rotate'];
            $data['stretch']      = $this->props['coverflow_stretch'];
            $data['depth']        = $this->props['coverflow_depth'];
            $data['modifier']     = $this->props['coverflow_modifier'];
        }

        // arrow position classes
        if ('on' === $this->props['arrow']) {
            $arrow_position = '' !== $this->props['arrow_position'] ? $this->props['arrow_position'] : 'middle';
            $class          .= ' arrow-' . $arrow_position;
        }

        return sprintf(
            '<div class="et_pb_module_inner"><div class="df_cptcarousel_container%8$s" data-settings=\'%2$s\' data-item="%5$s" data-itemtablet="%6$s" data-itemphone="%7$s">
                <div class="swiper-container">%1$s</div>%3$s
            </div>%4$s</div>',
            $this->get_posts(),
            wp_json_encode($data),
            $this->df_bc_arrow($order_number),
            $this->df_bc_dots($order_number),
            $this->props['item_desktop'],
            $this->props['item_tablet'],
            $this->props['item_mobile'],
            $class
        );
    }

    /**
     * Arrow navigation
     *
     * @param Integer | $order_number
     *
     * @return String | HTML
     */
    public function df_bc_arrow($order_number)
    {
        $prev_icon = $this->props['arrow_prev_icon_use_icon'] === 'on' && isset($this->props['prev_arrow_font_icon']) && ! empty($this->props['prev_arrow_font_icon']) ?
            esc_attr(Utils::process_font_icon($this->props['prev_arrow_font_icon'])) : '4';
        $next_icon = $this->props['arrow_next_icon_use_icon'] === 'on' && isset($this->props['next_arrow_font_icon']) && ! empty($this->props['next_arrow_font_icon']) ?
            esc_attr(Utils::process_font_icon($this->props['next_arrow_font_icon'])) : '5';

        return $this->props['arrow'] === 'on' ? sprintf('
            <div class="df_cptc_arrows">
                <div class="swiper-button-next cptc-next-%1$s" data-icon="%3$s"></div>
                <div class="swiper-button-prev cptc-prev-%1$s" data-icon="%2$s"></div>
            </div>
        ', $order_number, $prev_icon, $next_icon) : '';
    }

    /**
     * Dot pagination
     *
     * @param Integer | $order_number
     *
     * @return String | HTML
     */
    public function df_bc_dots($order_number)
    {
        return $this->props['dots'] === 'on' ?
            sprintf('<div class="swiper-pagination cptc-dots-%1$s"></div>', $order_number) : '';
    }

    public function add_new_child_text()
    {
        return esc_html__('Add New Custom Post Element', 'divi_flash');
    }
}
