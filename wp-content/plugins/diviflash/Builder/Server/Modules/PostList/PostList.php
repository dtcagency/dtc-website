<?php
/**
 * PostList Module class.
 *
 * @package DIVIFLASH5\Modules\PostList;
 */

namespace DIFL\Server\Modules\PostList;

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
 * Class PostList
 *
 * @package DIVIFLASH5\Modules\PostList
 */
class PostList implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;
    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/postlist')->customCssFields;
    }

    function df_sticky_post_ids_by_ignore_setting($ignore_specific_sticky)
    {

        $sticky_key = df_get_sticky_post_ids();

        /**
         * D5 provide array and D4 provide string
         * If ignore_specific_sticky is not an array, explode it by |
         */
        if (!empty($ignore_specific_sticky) && !is_array($ignore_specific_sticky)) {
            $ignore_specific_sticky = explode('|', $ignore_specific_sticky);
            sort($sticky_key);
            if ($ignore_specific_sticky[0] === 'on') {
                $post_not_in = array_diff($sticky_key, [0]);
            } else {
                $id_values = $ignore_specific_sticky;
                $post_not_in = $ignore_specific_sticky;
            }
        } else if (!empty($ignore_specific_sticky) && is_array($ignore_specific_sticky)) {
            if (in_array('all', $ignore_specific_sticky)) {
                $post_not_in = $sticky_key;
            } else {
                $id_values = $ignore_specific_sticky;
                $post_not_in = $ignore_specific_sticky;
            }
        }


        return $post_not_in;
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
            if ( ! isset( $attrs[ $key ] ) ) {
                $attrs[ $key ] = array();
            }
            if (empty($attrs[$key]['innerContent'])) {
                $attrs[$key]['innerContent'] = [
                    'desktop' => [
                        'value' => []
                    ]
                ];
            }
            if(empty($attrs[$key]['decoration'])) {
                $attrs[$key]['decoration'] = [
                    'desktop' => [
                        'value' => []
                    ]
                ];
            }
        }
        return $attrs;
    }

    public static function module_styles($args)
    {
        $instance = new static();
        $attrs = $instance->getDefaultKeys($args);;
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $orderClass = $args['orderClass'];

        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $orderClass, 'difl/postlist');
        $styles = [];
        $styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .pagination .newer:after, {$orderClass} .pagination .next:after",
            'attr' => $attrs['pagination_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn($props) => 'content: "' . $instance->arrow_icon($props['attrValue']['next_prev_icon'] ?? 'set_1', 'next') . '" !important;',
        ]);

        $styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .pagination .older:before, {$orderClass} .pagination .prev:before",
            'attr' => $attrs['pagination_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn($props) => 'content: "' . $instance->arrow_icon($props['attrValue']['next_prev_icon'] ?? 'set_1', 'prev') . '" !important;',
        ]);
        $breakpoint = $instance->props['use_breakpoint'] === 'on' ? sprintf('@media only screen and ( max-width: %1$s )', $instance->props['breakpoint'] !== '' ? $instance->props['breakpoint'] : '980px'):'@media only screen and ( max-width: 980px )';
        $styles[] = CommonStyle::style([
            'selector' => "{$orderClass} ",
            'attr' => $attrs['layout_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($prop) use ($instance) {
                return sprintf('
                --flex-direction: column;
                --align-items: center;
                --order-2: unset;
                --collapse-value: 0;
                --gap: %1$s;',
                    $instance->props['item_gap_mobile'] ? $instance->props['item_gap_mobile'] : '30px'
                );
            },
            "atRules" => $breakpoint
        ]);
        $styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df-postlist-featured-image",
            'attr' => $attrs['layout_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($prop) use ($instance) {
                return sprintf('
                    width: %1$s;
                ',
                    $instance->props['mobile_image_size'] ? $instance->props['mobile_image_size'] : '100%'
                );
            },
            "atRules" => $breakpoint
        ]);
        $styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df-post-outer-wrap",
            'attr' => $attrs['layout_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ($prop) use ($instance) {
                return sprintf('
                    width: %1$s;
                ',
                    $instance->props['mobile_content_size'] ? $instance->props['mobile_content_size'] : '100%'
                );
            },
            "atRules" => $breakpoint
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
                    //          'selector'            => "{$orderClass} .df_text_reveal_main_container",
                    //          'attr'                => $attrs['settings__reveal_color']['decoration'] ?? [],
                    //          'property' => '--secondary-reveal-color'
                    //      ]
                    //  ),
                ],
            ]
        );
    }

    /**
     * Get blog posts for postlist module
     *
     * @return string blog post markup
     */
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
        $layout = $this->props['layout'] ? $this->props['layout'] : 'layout-1';
        $collapse = $this->props['collapse'] === 'on' && $this->props['vertical_align'] !== 'stretch' ? 'layout-collapse' : '';
        $current_post_id =  $post->ID ?? 0;
        $image_size = $this->props['image_size'] !== '' ? $this->props['image_size'] : 'large';
        $image_scale = $this->props['image_scale'] ? $this->props['image_scale'] : 'no-image-scale';
        $equal_height = $this->props['equal_height'] ? $this->props['equal_height'] : 'off';
        $use_iamge = $this->props['use_iamge'] ? $this->props['use_iamge'] : 'on';
        $use_icon = $this->props['use_icon'] ? $this->props['use_icon'] : 'off';
        $icon_image =  esc_attr(Utils::process_font_icon($this->props['icon_image']));
        $query_args = array(
            'posts_per_page' => intval($this->props['posts_number']),
            'post_status' => array('publish'),
            'perm' => 'readable',
            'post_type' => 'post',
            'ignore_sticky_posts' => true,
        );
        $ignore_sticky_post = isset($this->props['ignore_sticky_post']) ? $this->props['ignore_sticky_post'] : 'off';

        $ignore_specific_sticky = 'on' === $ignore_sticky_post ? $this->df_sticky_post_ids_by_ignore_setting($this->props['ignore_specific_sticky']) : [];
        $query_args['post__not_in'] = $ignore_specific_sticky;

        if ('by_category' === $post_display && 'on' !== $related_posts && 'on' !== $use_current_loop) {
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
        // post by categories
        if ('by_category' === $post_display ) {
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
        // post by tag
        if ('by_tag' == $post_display) {
            $query_args['tag__in'] = $this->props['include_tags'];
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
                $query_args['category__in'] = [];
            }
            $query_args['category__in'] = $categories;
        }

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

        echo '<div class="df-posts-wrap list-' . esc_attr($layout) . '">';

        if (have_posts()) {
            while (have_posts()) {
                the_post();

                $width = 'on' === 1080;
                // $width = 'on' === $fullwidth ? 1080 : 400;
                $width = (int)apply_filters('et_pb_blog_image_width', $width);

                $height = 'on' === 675;
                $height = (int)apply_filters('et_pb_blog_image_height', $height);

                $outer_content = '';
                $inner_content = '';
                $featured_image = $use_iamge === 'on' ? df_post_image_render(array(
                    'image_size' => $image_size,
                    'image_scale' => $image_scale,
                    'equal_height' => $equal_height
                )) : '';
                $collapse_class = !empty($featured_image) && $equal_height !== 'on' ? $collapse : '';

                $classes = array(
                    'df-post-item',
                    $collapse_class,
                    empty($featured_image) ? 'no-thumbnail' : '',
                    $equal_height === 'on' ? 'equal-height' : '',
                    $use_icon === 'on' ? 'has-icon' : ''
                );
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class($classes) ?>>
                    <?php if (!empty($featured_image)): ?>
                        <div class="df-postlist-featured-image">
                            <?php echo et_core_esc_previously($featured_image); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($use_iamge != 'on' && $use_icon === 'on'): ?>
                        <div class="df-pl-icon"><?php echo esc_html($icon_image); ?></div>
                    <?php endif; ?>
                    <div class="df-post-outer-wrap df-hover-trigger">
                        <?php
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
                            foreach ($df_post_items as $post_item) {

                                if (!isset($post_item['type'])) {
                                    continue;
                                }

                                $callback = 'df_post_' . $post_item['type'];

                                call_user_func($callback, $post_item);

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
        if ('on' === $this->props['show_pagination']) {
            if (function_exists('wp_pagenavi')) {
                wp_pagenavi();
            } else {
            add_filter('get_pagenum_link', array(self::class, 'filter_pagination_url'));
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
            remove_filter('get_pagenum_link', array(self::class, 'filter_pagination_url'));
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

    public function render()
    {
//        if ($this->content === '') {
//            return sprintf(
//                '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Post Element.</strong></h2>'
//            );
//        }
        // wp_enqueue_script('imageload');
        if ($this->props['show_pagination'] !== 'off') {
            wp_enqueue_script('fitvids');
        }
        wp_enqueue_style('wp-mediaelement');
        wp_enqueue_script('wp-mediaelement');
        if (class_exists('\ET\Builder\FrontEnd\Assets\DynamicAssetsUtils')) {
            \ET\Builder\FrontEnd\Assets\DynamicAssetsUtils::enqueue_pagination_script();
        }
        $data = array(
            'layout' => $this->props['layout']
        );
        df_load_pagination_script();
        return sprintf('<div class="df_postlist_container et_pb_ajax_pagination_container" data-settings=\'%2$s\'>%1$s</div>',
            $this->get_posts(),
            wp_json_encode($data)
        );
    }

    /**
     * Render simple pagination
     *
     * need to remove
     *
     */
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

    /**
     * Render numbered pagination
     *
     * need to remove
     *
     */
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

    /**
     * next and prev icons
     */
    public function arrow_icon($set = 'set_1', $type = 'next')
    {
        $icons = array(
            'set_1' => array(
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

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-post-list');

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
                'classnamesFunction' => [PostList::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [PostList::class, 'module_styles'],
                'scriptDataComponent' => [PostList::class, 'module_script_data'],
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
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/post-list';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [PostList::class, 'render_callback'],
                    ]
                );
            }
        );
    }


}
