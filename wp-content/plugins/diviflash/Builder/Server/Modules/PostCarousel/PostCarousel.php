<?php
/**
 * PostCarousel Module class.
 *
 * @package DIVIFLASH5\Modules\PostCarousel;
 */

namespace DIFL\Server\Modules\PostCarousel;

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
 * Class PostCarousel
 *
 * @package DIVIFLASH5\Modules\PostCarousel
 */
class PostCarousel implements DependencyInterface {

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use \DIFL\Server\Traits\StyleUtil;

    protected $styles;

    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/blogcarousel' )->customCssFields;
    }

    public static function font_style($font) {
        return static::get_font_style($font);
    }

    public static function module_classnames( $args ) {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'];

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

    public static function module_script_data( $args ) {
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

    public static function module_styles( $args ) {
        $instance = new static();
        $attrs = $instance->getDefaultKeys($args);
        ;
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $orderClass  = $args['orderClass'];

        $instance->initializeProps( $elements, $attrs );
        $instance->styles = $instance->renderUnifiedStyles( $elements, $attrs, $orderClass, 'difl/blogcarousel' );
        $styles = [];
        $resolve_value = function($props, $key, $instance) {
            return $props['attrValue'][$key] ?? $instance->props[$key];
        };
        if (($instance->props['coverflow_shadow'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-container-3d .swiper-slide-shadow-left",
                'attr' => $attrs['coverflow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "background-image: linear-gradient(to left,{$resolve_value($props, 'coveflow_color_dark', $instance)},{$resolve_value($props, 'coveflow_color_light', $instance)}); !important;"
            ]);

            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-container-3d .swiper-slide-shadow-right",
                'attr' => $attrs['coverflow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "background-image: linear-gradient(to right,{$resolve_value($props, 'coveflow_color_dark', $instance)},{$resolve_value($props, 'coveflow_color_light', $instance)}); !important;"
            ]);
        }

        if (($instance->props['arrow_circle'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_bc_arrows > div",
                'attr' => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "border-radius: 50%;"
            ]);
        }

        if (($instance->props['large_active_dot'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-pagination .swiper-pagination-bullet-active",
                'attr' => $attrs['dots_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "width: 40px; border-radius: 20px;"
            ]);
        }

        if (($instance->props['arrow'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_bc_arrows",
                'attr' => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) use ($instance) {
                    $options = [
                        'top' => "position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 0;",
                        'middle' => "position: absolute;
                    top: 50%;
                    left: 0;
                    right: 0;
                    transform: translateY(-50%);",
                        'bottom' => "position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 1;"
                    ];
                    $val = $props['attrValue']['arrow_position'] ?? $instance->props['arrow_position'];
                    return $options[$val] ?? '';
                }
            ]);

            if (($instance->props['arrow_prev_icon_use_icon'] ?? null) === "on") {
                $styles[] = CommonStyle::style( [
                    'selector'            => "{$orderClass} .df_bc_arrows .swiper-button-prev:after",
                    'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['arrow_prev_icon_font_icon'] ?? $instance->props['arrow_prev_icon_font_icon'] ) ,
                ] );
            }

            if (($instance->props['arrow_next_icon_use_icon'] ?? null) === "on") {
                $styles[] = CommonStyle::style( [
                    'selector'            => "{$orderClass} .df_bc_arrows .swiper-button-next:after",
                    'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['arrow_next_icon_font_icon'] ?? $instance->props['arrow_next_icon_font_icon'] ) ,
                ] );
            }

        }
        if (($instance->props['equal_height'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-slide",
                'attr' => $attrs['posts_obj_settings']['innerContent'],
                'declarationFunction' => fn($props) => "align-self: auto !important;"
            ]);
        }

        if (($instance->props['dots'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .swiper-pagination",
                'attr' => $attrs['dots_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) use ($instance) {
                    $options = [
                        'top' => "position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 0;",
                        'middle' => "position: absolute;
                    top: 50%;
                    left: 0;
                    right: 0;
                    transform: translateY(-50%);",
                        'bottom' => "position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 1;"
                    ];
                    $val = $props['attrValue']['dots_position'] ?? $instance->props['dots_position'];
                    return $options[$val] ?? '';
                }
            ]);
        }

        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $orderClass, $attrs, $elements->module_metadata->attributes ?? [] ) );

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

                    ...$instance->styles,
                    ...$styles,
                ],
            ]
        );
    }

    public function render( $attrs ) {
        wp_enqueue_script('swiper-script');
        wp_enqueue_script('df-blog-carousel');

        wp_enqueue_style( 'wp-mediaelement' );
        wp_enqueue_script( 'wp-mediaelement' );

//        $order_class     = self::get_module_order_class($render_slug);
//        $order_number    = str_replace('_', '', str_replace($this->slug, '', $order_class));
        $order_number=wp_rand( 1, 99999999 );
        $class = '';

        $data = array(
            'effect' => $this->props['carousel_type'],
            'desktop' => $this->props['item_desktop'],
            'tablet' => $this->props['item_tablet'],
            'mobile' => $this->props['item_mobile'],
            'loop' => $this->props['loop'] === 'on' ? true : false,
            'item_spacing' => $this->props['item_spacing'],
            'item_spacing_tablet' => $attrs['posts_obj_settings']['innerContent']['tablet']['value']['item_spacing']??'',
            'item_spacing_phone' => $attrs['posts_obj_settings']['innerContent']['phone']['value']['item_spacing']??'',
            'arrow' => $this->props['arrow'],
            'dots' => $this->props['dots'],
            'autoplay' => $this->props['autoplay'],
            'auto_delay' => $this->props['autospeed'],
            'speed' => $this->props['speed'],
            'pause_hover' => $this->props['pause_hover'],
            'centeredSlides' => $this->props['centered_slides'],
            'order' => $order_number
        );
        if ($this->props['carousel_type'] === 'coverflow') {
            $data['slideShadows'] = $this->props['coverflow_shadow'];
            $data['rotate'] = $this->props['coverflow_rotate']??0;
            $data['stretch'] = $this->props['coverflow_stretch']??0;
            $data['depth'] = $this->props['coverflow_depth']??0;
            $data['modifier'] = $this->props['coverflow_modifier']??0;
        }

        // arrow position classes
        if($this->props['arrow'] === 'on') {
            $arrow_position = '' !== $this->props['arrow_position'] ? $this->props['arrow_position'] : 'middle';
            $class .= ' arrow-' . $arrow_position;
        }

        return sprintf('<div class="df_blogcarousel_container%8$s" data-settings=\'%2$s\' data-item="%5$s" data-itemtablet="%6$s" data-itemphone="%7$s">
                <div class="swiper-container">%1$s</div>%3$s
            </div>%4$s',
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

    public function get_posts() {
        global $post, $paged, $wp_query, $wp_the_query, $wp_filter, $__et_blog_module_paged, $df_post_items, $df_post_items_outside;

        $main_query = $wp_the_query;

        $use_current_loop           = isset( $this->props['use_current_loop'] ) ? $this->props['use_current_loop'] : 'off';
        $offset_number              = $this->props['offset_number'];
        $related_posts = $this->props['related_posts'];
        $posts_number               = $this->props['posts_number'];
        $post_display               = $this->props['post_display'];
        $orderby                    = $this->props['orderby'];
        $use_image_as_background    = $this->props['use_image_as_background'];
        $use_background_scale       = $this->props['use_background_scale'];
        $query_args = array(
            'posts_per_page' => intval($this->props['posts_number']),
            'post_status'    => array( 'publish' ),
            'perm'           => 'readable',
            'post_type'      => 'post',
        );

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
        if ( 'by_tag' == $post_display) {
            $query_args['tag__in'] = $this->props['include_tags'];
        }
        // orderby
        if ( 'recent' == $post_display) {
            if ( '3' === $orderby ) {
                $query_args['orderby'] = 'rand';
            } else if('2' === $orderby) {
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'ASC';
            } else {
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
            }
        }

        $df_pg_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
        if ( is_front_page() ) {
            $paged = $df_pg_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
        }

        if ( $__et_blog_module_paged > 1 ) {
            $df_pg_paged            = $__et_blog_module_paged;
            $paged                  = $__et_blog_module_paged; //phpcs:ignore WordPress.WP.GlobalVariablesOverride
            $query_args['paged']    = $__et_blog_module_paged;
        }

        $query_args['paged'] = $df_pg_paged;

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

        if (is_single()) { // Exclude Current post.
            $main_query_post = \ET_Post_Stack::get_main_post();

            if (null !== $main_query_post) {
                $query_args['post__not_in'][] = $main_query_post->ID;
            }
        }

        if ('on' === $related_posts) {
            $post_id = $wp_query->queried_object->ID;
            $categories = wp_get_post_categories($post_id);
            if (empty($categories)) {
                $query_args['category__in']= [];
            }
            $query_args['category__in'] = $categories;
        }


        ob_start();

        if ( 'off' === $use_current_loop ) {
            query_posts( $query_args ); // phpcs:ignore WordPress.WP.DiscouragedFunctions
        } elseif ( is_singular() && 'on' === $use_current_loop) {
            $custom   = array_intersect_key( $query_args, array_flip( array( 'posts_per_page', 'offset', 'paged' ) ) );

            query_posts($custom);
        } else {
            // Only allow certain args when `Posts For Current Page` is set.
            $original = $wp_query->query_vars;
            $custom   = array_intersect_key( $query_args, array_flip( array( 'posts_per_page', 'offset', 'paged' ) ) );

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

        echo '<div class="df-posts-wrap swiper-wrapper">';

        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();

                $width = 'on' === 1080;
                // $width = 'on' === $fullwidth ? 1080 : 400;
                $width = (int) apply_filters( 'et_pb_blog_image_width', $width );

                $height    = 'on' === 675;
                $height    = (int) apply_filters( 'et_pb_blog_image_height', $height );
                $equal_height_class = $this->props['equal_height'] === 'on' ? ' df-equal-height' : '';

                $outer_content = '';
                $inner_content = '';

                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( "df-post-item swiper-slide v2{$equal_height_class}" ) ?>>
                    <div class="df-post-outer-wrap df-hover-trigger" <?php echo $use_background_scale !== 'on' ? et_core_esc_previously(df_post_image_as_background($use_image_as_background)): '';?>>
                        <?php
                        // render markup to achive the scale effect.
                        if($use_image_as_background === 'on' && $use_background_scale === 'on') {
                            echo '<div class="df-blogcarousel-bg-on-hover"><div ' .et_core_esc_previously(df_post_image_as_background($use_image_as_background)) .'></div></div>';
                        }
                        if( !empty($df_post_items_outside) ) {
                            foreach( $df_post_items_outside as $post_item ) {

                                if( !isset($post_item['type'])) {
                                    continue;
                                }

                                $callback = 'df_post_' . $post_item['type'];

                                call_user_func($callback, $post_item);

                            } // end of foreach
                        }
                        ?>
                        <div class="df-post-inner-wrap">
                            <?php
                            foreach( $df_post_items as $post_item ) {

                                if( !isset($post_item['type'])) {
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

        $wp_the_query = $wp_query = $main_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
        wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions

        $posts = ob_get_contents();
        ob_end_clean();
        if(empty($df_post_items)) {
            $posts = '<h2>Please create post element.</h3>';
        }
        $df_post_items = array();
        $df_post_items_outside = array();

        return $posts;
    }

    public function df_bc_arrow($order_number)
    {
        $prev_icon = $this->props['arrow_prev_icon_use_icon'] === 'on' && isset($this->props['arrow_prev_icon_font_icon']) && !empty($this->props['arrow_prev_icon_font_icon']) ?
            esc_attr(Utils::process_font_icon($this->props['arrow_prev_icon_font_icon'])) : '4';
        $next_icon = $this->props['arrow_next_icon_use_icon'] === 'on' && isset($this->props['arrow_next_icon_font_icon']) && !empty($this->props['arrow_next_icon_font_icon']) ?
            esc_attr(Utils::process_font_icon($this->props['arrow_next_icon_font_icon'])) : '5';

        return $this->props['arrow'] === 'on' ? sprintf('
            <div class="df_bc_arrows">
                <div class="swiper-button-next bc-next-%1$s" data-icon="%3$s"></div>
                <div class="swiper-button-prev bc-prev-%1$s" data-icon="%2$s"></div>
            </div>
        ', $order_number, $prev_icon, $next_icon) : '';
    }

    public function df_bc_dots($order_number)
    {
        return $this->props['dots'] === 'on' ?
            sprintf('<div class="swiper-pagination bc-dots-%1$s"></div>', $order_number) : '';
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps( $elements, $attrs );
        wp_enqueue_script('df-post-carousel');

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
                'classnamesFunction' => [PostCarousel::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [PostCarousel::class, 'module_styles'],
                'scriptDataComponent' => [PostCarousel::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => $elements->style_components(
                        [
                            'attrName' => 'module',
                        ]
                    ) . '<div>'.$instance->render($attrs).'</div>',
            ]
        );
    }

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/post-carousel';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ PostCarousel::class, 'render_callback' ],
                    ]
                );
            }
        );
    }


//    public function df_arrow_pos_styles($value = 'middle')
//    {
//        $options = array(
//            'top' => 'position: relative;
//                    top: auto;
//                    left: auto;
//                    right: auto;
//                    transform: translateY(0);
//                    order: 0;',
//            'middle' => 'position: absolute;
//                        top: 50%;
//                        left: 0;
//                        right: 0;
//                        transform: translateY(-50%);',
//            'bottom' => 'position: relative;
//                    top: auto;
//                    left: auto;
//                    right: auto;
//                    transform: translateY(0);
//                    order: 2;',
//        );
//        return $options[$value];
//    }
//



}
