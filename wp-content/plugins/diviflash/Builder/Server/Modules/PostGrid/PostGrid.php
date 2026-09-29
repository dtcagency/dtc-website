<?php
/**
 * PostGrid Module class.
 *
 * @package DIVIFLASH5\Modules\PostGrid;
 */

namespace DIFL\Server\Modules\PostGrid;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
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
 * Class PostGrid
 *
 * @package DIVIFLASH5\Modules\PostGrid
 */
class PostGrid implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/postgrid')->customCssFields;
    }

    public static function module_classnames($args)
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

    public static function module_script_data($args)
    {
        $elements = $args['elements'];

        // Element Script Data Options.
        $elements->script_data(
            [
                'attrName' => 'module',
            ]
        );
    }

    protected function getDefaultKeys($args)
    {
        $attrs = $args['attrs'];

        $metaData = $args['elements']->module_metadata->attributes;
        foreach ($metaData as $key => $value) {
            if ( isset( $attrs[ $key ] ) && ! is_array( $attrs[ $key ] ) ) {
                continue;
            }
            if (empty($attrs[$key])) {
                $attrs[$key] = [
                    'innerContent' => [
                        'desktop' => [
                            'value' => []
                        ]
                    ],
                ];
            }
        }
        return $attrs;
    }
    protected function arrow_icon( $set = 'set_1', $type = 'next' ) {
        $icons = array(
            'set_1'  => array(
                'next' => '5',
                'prev' => '4'
            ),
            'set_2' => array(
                'next' => '$',
                'prev' => '#'
            ),
            'set_3' => array(
                'next' => '9',
                'prev' => '8'
            ),
            'set_4' => array(
                'next' => 'E',
                'prev' => 'D'
            )
        );
        return $icons[$set][$type];
    }
    public static function module_styles($args)
    {
        $instance = new static();
        $attrs = $instance->getDefaultKeys($args);;
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $orderClass = $args['orderClass'];
        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $orderClass, 'difl/postgrid');
        $styles = [];

        $styles[]=CommonStyle::style([
            'selector' => "{$orderClass}.difl_postgrid .df-post-item",
            'attr' => $attrs['column_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($props) {
                $width = 100 / intval((isset($props['attrValue']['column']) && $props['attrValue']['column'] !='')?$props['attrValue']['column']:3);
                return "width:{$width}% !important;";
            },
        ]);

        $styles[]=CommonStyle::style([
            'selector' => "{$orderClass} .pagination .newer:after, {$orderClass} .pagination .next:after",
            'attr' => $attrs['pagination_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($props) use ($instance) {
                return 'content:"' . $instance->arrow_icon($instance->props['next_prev_icon'], 'next') . '" !important';
            },
        ]);

        $styles[]=CommonStyle::style([
            'selector' => "{$orderClass} .pagination .older:before, {$orderClass} .pagination .prev:before",
            'attr' => $attrs['pagination_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($props) use ($instance) {
                return 'content:"' . $instance->arrow_icon($instance->props['next_prev_icon'], 'prev') . '" !important';
            },
        ]);

        $styles[]=CommonStyle::style([
            'selector' => "{$orderClass}.difl_postgrid .df-post-item",
            'attr' => $attrs['gutter_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($props) {
                $leftRightGutter = (intval($props['attrValue']['gutter']??"20") / 2) . 'px';
                $topBottomGutter = (intval($props['attrValue']['gutter']??"20") / 2) . 'px';
                $bottomGutter = intval($props['attrValue']['gutter']??"20") . 'px';
                return "
            padding-left:{$leftRightGutter} !important;
            padding-right:{$leftRightGutter} !important;
            padding-bottom:{$bottomGutter} !important;
        ";
            },
        ]);

        $styles[]=CommonStyle::style([
            'selector' => "{$orderClass} .df_postgrid_container .df-posts-wrap",
            'attr' => $attrs['gutter_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($props) {
                $leftRightGutter = ( intval( $props['attrValue']['gutter'] ?? "20" ) / 2) . 'px';
                return "
            margin-left:-{$leftRightGutter} !important;
            margin-right:-{$leftRightGutter} !important;
        ";
            },
        ]);

        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $orderClass, $attrs, $elements->module_metadata->attributes ?? [] ) );

        Style::add(
            [
                'id' => $args['id'],
                'name' => $args['name'],
                'orderIndex' => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles' => [
                    // Element: Module.
                    $elements->style(
                        [
                            'attrName' => 'module',
                            'styleProps' => [
                                'disabledOn' => [
                                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                                ],
                            ],
                        ]
                    ),
                    CssStyle::style(
                        [
                            'selector' => $args['orderClass'],
                            'attr' => $attrs['css'] ?? [],
                            'cssFields' => self::custom_css(),
                        ]
                    ),

                    ...$instance->styles,
                    ...$styles,

                    //  CommonStyle::style(
                    //      [
                    //          'selector'            => "{$order_class} .df_text_reveal_main_container",
                    //          'attr'                => $attrs['settings__reveal_color']['decoration'] ?? [],
                    //          'property' => '--secondary-reveal-color'
                    //      ]
                    //  ),
                ],
            ]
        );
    }

    public function render()
    {

        // Enqueue all required scripts for AJAX pagination


        wp_enqueue_script('imageload');
        wp_enqueue_script('df-imagegallery-lib');
        if ($this->props['show_pagination'] !== 'off') {
            wp_enqueue_script('fitvids');
        }
        wp_enqueue_style('wp-mediaelement');
        wp_enqueue_script('wp-mediaelement');

        wp_enqueue_script('df-posts');

        $data = array(
            'layout' => $this->props['layout']
        );
        df_load_pagination_script();
        return sprintf('<div class="df_postgrid_container et_pb_ajax_pagination_container" data-settings=\'%2$s\'>%1$s</div>',
            $this->get_posts(),
            wp_json_encode($data)
        );
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-post-grid');

        return Module::render(
            [
                // FE only.
                'orderIndex' => $block->parsed_block['orderIndex'],
                'storeInstance' => $block->parsed_block['storeInstance'],

                // VB equivalent.
                'attrs' => $attrs,
                'elements' => $elements,
                'id' => $block->parsed_block['id'],
                'moduleClassName' => '',
                'name' => $block->block_type->name,
                'classnamesFunction' => [PostGrid::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [PostGrid::class, 'module_styles'],
                'scriptDataComponent' => [PostGrid::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => $elements->style_components(
                        [
                            'attrName' => 'module',
                        ]
                    ) . $instance->render(),
            ]
        );
    }

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/post-grid';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [PostGrid::class, 'render_callback'],
                    ]
                );
            }
        );
    }

    public function get_posts()
    {
        global $post, $paged, $wp_query, $wp_the_query, $wp_filter, $__et_blog_module_paged, $df_post_items, $df_post_items_outside;

        $main_query = $wp_the_query;

        $use_current_loop = isset($this->props['use_current_loop']) ? $this->props['use_current_loop'] : 'off';
        $offset_number = $this->props['offset_number'];
        $related_posts = $this->props['related_posts'];
        $posts_number = $this->props['posts_number'];
        $post_display = $this->props['post_display'];
        $orderby = $this->props['orderby'];
        $layout = $this->props['layout'];
        $use_image_as_background = $this->props['use_image_as_background'];
        $use_background_scale = $this->props['use_background_scale'];
        $current_post_id = empty($post) ? 0 : $post->ID;
        $query_args = [
            'posts_per_page' => intval($this->props['posts_number']),
            'post_status' => ['publish'],
            'perm' => 'readable',
            'post_type' => 'post',
        ];

        // post by categories
        if ('by_category' === $post_display && 'on' !== $related_posts) {
            $include_categories = $this->props['include_categories'];

            if (is_array($include_categories) && count($include_categories) === 1 && $include_categories[0] === 'current') {
                if (is_category()) {
                    $current_cat = get_queried_object();
                    if ($current_cat && isset($current_cat->term_id)) {
                        $query_args['cat'] = $current_cat->term_id;
                    }
                } else {
                    // Fallback: try to get the current post's categories
                    if (!empty($post)) {
                        $categories = wp_get_post_categories($wp_query->queried_object->ID);
                        if (!empty($categories)) {
                            $query_args['cat'] = $categories[0];
                        }
                    }
                }
            } else {
                $query_args['cat'] = $include_categories;
            }
        }
        // post by tag
        if ('by_tag' == $post_display) {
            $query_args['tag__in'] = $this->props['include_tags'];
        }
        // orderby
        if ('recent' == $post_display) {
            if ('3' === $orderby) {
                $query_args['orderby'] = 'rand';
            } else if ('2' === $orderby) {
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'ASC';
            } else {
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
            }
        }
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
            $df_pg_paged = $__et_blog_module_paged;
            $paged = $__et_blog_module_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
            $query_args['paged'] = $__et_blog_module_paged;
        }

        $query_args['paged'] = $df_pg_paged;

        if ('' !== $offset_number && !empty($offset_number)) {
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
        if ('on' === $related_posts) {
            $post_id = $wp_query->queried_object->ID;
            $categories = wp_get_post_categories($post_id);
            if (empty($categories)) {
                return [];
            }
            $query_args['category__in'] = $categories;
        }
        $wp_query->query_vars['ignore_sticky_posts'] = true;
        ob_start();

        if ('off' === $use_current_loop) {
            query_posts($query_args); // phpcs:ignore WordPress.WP.DiscouragedFunctions
        } elseif (is_singular()) {
            // Force an empty result set in order to avoid loops over the current post.
            query_posts(array('post__in' => array(0))); // phpcs:ignore WordPress.WP.DiscouragedFunctions
            // $show_no_results_template = false;
        } else {
            // Only allow certain args when `Posts For Current Page` is set.
            $original = $wp_query->query_vars;
            $custom = array_intersect_key($query_args, array_flip(array('posts_per_page', 'offset', 'paged')));

            // Trick WP into reporting this query as the main query so third party filters
            // that check for is_main_query() are applied.
            $wp_the_query = $wp_query = new \WP_Query(array_merge($original, $custom)); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
        }

        // Manually set the max_num_pages to make the `next_posts_link` work
        if ('' !== $offset_number && !empty($offset_number)) {
            global $wp_query;
            $wp_query->found_posts = max(0, $wp_query->found_posts - intval($offset_number));
            $posts_number = intval($posts_number);
            $wp_query->max_num_pages = $posts_number > 1 ? ceil($wp_query->found_posts / $posts_number) : 1;
        }

        echo '<div class="df-posts-wrap layout-' . esc_attr($layout) . '">';

        if (have_posts()) {
            while (have_posts()) {
                the_post();

                $width = 'on' === 1080;
                // $width = 'on' === $fullwidth ? 1080 : 400;
                $width = (int)apply_filters('et_pb_blog_image_width', $width);

                $height = 'on' === 675;
                $height = (int)apply_filters('et_pb_blog_image_height', $height);
                $equal_height_class = $this->props['equal_height'] === 'on' ? ' df-equal-height' : '';

                $outer_content = '';
                $inner_content = '';

                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class("df-post-item v2{$equal_height_class}") ?>>
                    <div
                        class="df-post-outer-wrap df-hover-trigger" <?php echo $use_background_scale !== 'on' ? et_core_esc_previously(df_post_image_as_background($use_image_as_background)) : ''; ?>>
                        <?php
                        // render markup to achive the scale effect.
                        if ($use_image_as_background === 'on' && $use_background_scale === 'on') {
                            echo '<div class="df-postgrid-bg-on-hover"><div ' . et_core_esc_previously(df_post_image_as_background($use_image_as_background)) . '></div></div>';
                        }
                        if (!empty($df_post_items_outside)) {
                            foreach ($df_post_items_outside as $post_item) {

                                if (!isset($post_item['type'])) {
                                    continue;
                                }

                                $callback = 'df_post_' . $post_item['type'];

                                call_user_func($callback, $post_item);

                            } // end of foreach
                        }
                        ?>
                        <div class="df-post-inner-wrap">
                            <?php
                            if(is_array($df_post_items) && count($df_post_items) > 0) {
                                foreach ($df_post_items as $post_item) {
                                    if (!isset($post_item['type'])) {
                                        continue;
                                    }

                                    $callback = 'df_post_' . $post_item['type'];
                                    call_user_func($callback, $post_item);
                                }
                            }
                            ?>
                        </div>
                    </div>
                </article>
                <?php
            } // endwhile
        }

        echo '</div>'; // end of df-pg-posts

        // ajax navigation
        if ('on' === $this->props['show_pagination']) {
            if (function_exists('wp_pagenavi')) {
                wp_pagenavi();
            } else {
                add_filter('get_pagenum_link', [self::class, 'filter_pagination_url']);
                if ($this->props['use_number_pagination'] !== 'on') {
                    render_pagination(
                        $this->props['older_text'],
                        $this->props['newer_text']
                    );
                } else {
                    render_number_pagination(
                        $this->props['older_text'],
                        $this->props['newer_text']
                    );
                }
                remove_filter('get_pagenum_link', [self::class, 'filter_pagination_url']);
            }
        }

        $wp_the_query = $wp_query = $main_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
        wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions

        $posts = ob_get_contents();
        ob_end_clean();
        if (empty($df_post_items)) {
            $posts = '';
        }
        $df_post_items = array();
        $df_post_items_outside = array();

        return $posts;
    }


    public static function filter_pagination_url($result)
    {
        return add_query_arg('df_blog', '', $result);
    }


    function render_pagination()
    {
        echo '<div class="pagination clearfix">';
        echo '<div class="alignleft">';
        next_posts_link(esc_html__("&laquo; {$this->props['older_text']}", 'divi_flash'));
        echo '</div>';
        echo '<div class="alignright">';
        previous_posts_link(esc_html__("{$this->props['newer_text']} &raquo;", 'divi_flash'));
        echo '</div>';
        echo '</div>';
    }


    function render_number_pagination()
    {
        global $wp_query;
        $big = 9999999; // need an unlikely integer
        echo '<div class="pagination clearfix">';
        echo paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $wp_query->max_num_pages,
            'prev_text' => esc_html__("&laquo; {$this->props['older_text']}", 'divi_flash'),
            'next_text' => esc_html__("{$this->props['newer_text']} &raquo;", 'divi_flash')
        ));
        echo '</div>';
    }

}
