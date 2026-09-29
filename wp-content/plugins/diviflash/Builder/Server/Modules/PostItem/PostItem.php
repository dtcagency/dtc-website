<?php
/**
 * PostItem Module class.
 *
 * @package DIVIFLASH5\Modules\PostItem;
 */

namespace DIFL\Server\Modules\PostItem;

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
 * Class PostItem
 *
 * @package DIVIFLASH5\Modules\PostItem
 */
class PostItem implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use \DIFL\Server\Traits\StyleUtil;

    protected $styles;
    protected $acf_extension;

    public function __construct()
    {
        $this->acf_extension = class_exists('ACF') ? 'on' : 'off';
    }

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/postitem')->customCssFields;
    }

    public static function font_style($font) {
        return static::get_font_style($font);
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
        $attrs = $instance->getDefaultKeys($args);
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'];
        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/postitem');
        $styles=[];
        if ( $instance->props['type'] === "divider" ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$order_class}.df-item-wrap .df-post-ele-divider",
                'selectorFunction'    => function ( $props ) use ( $args ) {
                    if ( $props['state'] === 'hover' ) {
                        return ' .df-post-item:hover ' . $args["orderClass"] . '.df-item-wrap .df-post-ele-divider';
                    }

                    return "{$args["orderClass"]}.df-item-wrap .df-post-ele-divider";
                },
                'attr'                => $attrs['divider_obj_settings']['innerContent']??[],
                'declarationFunction' => function ( $props ) use ( $instance ) {
                    return "background-image: linear-gradient(
            " . ( $props['attr'][ $props['breakpoint'] ][ $props['state'] ]['divider_color_direction'] ?? $instance->props['divider_color_direction'] ) . ",
            " . ( $props['attr'][ $props['breakpoint'] ][ $props['state'] ]['divider_color_primary'] ?? $instance->props['divider_color_primary'] ) . "
            " . ( $props['attr'][ $props['breakpoint'] ][ $props['state'] ]['divider_color_start'] ?? $instance->props['divider_color_start'] ) . ",
            " . ( $props['attr'][ $props['breakpoint'] ][ $props['state'] ]['divider_color_secondary'] ?? $instance->props['divider_color_secondary'] ) . "
            " . ( $props['attr'][ $props['breakpoint'] ][ $props['state'] ]['divider_color_end'] ?? $instance->props['divider_color_end'] ) . "
        );";
                }
            ] );
        }
        if("on" === $instance->props['overlay_icon']) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$order_class} .df-icon-overlay",
                'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['overlay_font_icon'] ?? $instance->props['overlay_font_icon'] ) ,
            ] );
        }

        if("on" === $instance->props['use_icon']) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$order_class}.df-item-wrap .et-pb-icon",
                'attr'                => $attrs['icon_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['font_icon'] ?? $instance->props['font_icon'] ) ,
            ] );
        }
        $styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-hover-effect .df-overlay",
            'attr' => $attrs['overlay_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function($props) use ($instance, $attrs) {
                // Helper to resolve the value similarly to JS version
                $getOverlayValue = function($key) use ($props, $instance) {
                    // Prioritize $props['attrValue'][$key], fallback to $instance->props
                    if (!empty($props['attrValue'][$key])) {
                        return $props['attrValue'][$key];
                    }
                    if (!empty($instance->props[$key])) {
                        return $instance->props[$key];
                    }
                    return '';
                };
                return sprintf(
                    "background-image: linear-gradient(
                        %s,
                        %s 0,
                        %s 100%%) !important;",
                    $getOverlayValue('overlay_direction'),
                    $getOverlayValue('overlay_primary'),
                    $getOverlayValue('overlay_secondary')
                );
            }
        ]);
        if ("on" === ($instance->props['icon_vertical_alignment'] ?? null)) {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class}.df-item-wrap .df-post-read-more",
                'attr' => $attrs['icon_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function() {
                    return "
                        display: flex;
                        align-items: center;
                    ";
                }
            ]);
        }
        $styles[] = CommonStyle::style([
            'selector' => "{$order_class}",
            'attr' => $attrs['post_style_typo_obj_settings']['advanced']['text']['text'] ?? null,
            'declarationFunction' => function ($props) {
                $orientation = isset($props['attrValue']['orientation']) ? $props['attrValue']['orientation'] : null;
                if ($orientation) {
                    return "text-align: {$orientation} !important;";
                }
                return "";
            }
        ]);

        $styles[] = CommonStyle::style([
            'selector' => "{$order_class}",
            'attr' => $attrs['post_style_typo_obj_settings']['advanced']['text']['textShadow'] ?? null,
            'declarationFunction' => function ($props) {
                $horizontal = isset($props['attrValue']['horizontal']) ? $props['attrValue']['horizontal'] : "0em";
                $vertical = isset($props['attrValue']['vertical']) ? $props['attrValue']['vertical'] : "0em";
                $blur = isset($props['attrValue']['blur']) ? $props['attrValue']['blur'] : "0em";
                $color = isset($props['attrValue']['color']) ? $props['attrValue']['color'] : "rgba(0,0,0,0.4)";
                return "text-shadow: {$horizontal} {$vertical} {$blur} {$color} !important;";
            }
        ]);
        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ) );
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

    public function render($class)
    {
//        $this->handle_fa_icon();
        global $df_post_items, $df_post_items_outside;
        $type_settings = array();

        if ('select' !== $this->props['type']) {
            $type_settings['type'] = $this->props['type'];
            $type_settings['class'] = $class;
            // title
            $type_settings['title_tag'] = $this->props['title_tag'];
            // content
            $type_settings['post_content'] = $this->props['post_content'];
            $type_settings['use_post_excrpt'] = $this->props['use_post_excrpt'];
            $type_settings['excerpt_length'] = $this->props['excerpt_length'];
            // meta
            $type_settings['date_format'] = str_replace('%92', '\\', $this->props['date_format']);
            $type_settings['show_author_image'] = $this->props['show_author_image'];
            $type_settings['hide_author_text'] = $this->props['hide_author_text'];
            $type_settings['comment_text'] = $this->props['comment_text'];
            // Read More
            $type_settings['read_more_text'] = $this->props['read_more_text'];
            // icon
            $type_settings['image_icon'] = $this->props['image_icon'];
            $type_settings['image_alt_text'] = $this->props['image_alt_text'] ? $this->props['image_alt_text'] : '';
            $type_settings['use_icon'] = $this->props['use_icon'];
            $type_settings['font_icon'] = isset($this->props['font_icon']) && $this->props['font_icon'] !== '' ?
                esc_attr(Utils::process_font_icon($this->props['font_icon'])) : '5';
            // image
            $type_settings['image_size'] = $this->props['image_size'];

            // placement
            $type_settings['placement'] = $this->props['outside_wrapper'];
            // hover
            $type_settings['image_scale'] = $this->props['image_scale'];
            $type_settings['overlay'] = $this->props['overlay'];
            // overlay icon
            $type_settings['overlay_icon'] = $this->props['overlay_icon'];
            $type_settings['overlay_icon_reveal'] = $this->props['overlay_icon_reveal'];
            $type_settings['overlay_font_icon'] = !empty($this->props['overlay_font_icon']) ?
                $this->props['overlay_font_icon'] : '%%16%%';

            // custom text
            $type_settings['custom_text'] = $this->props['custom_text'];
            $type_settings['custom_field_before_label'] = $this->props['custom_field_before_label'] != '' ? $this->props['custom_field_before_label'] : '';
            $type_settings['custom_field_after_label'] = $this->props['custom_field_after_label'] != '' ? $this->props['custom_field_after_label'] : '';

            //custom field name
            $type_settings['custom_field'] = $this->props['custom_field'];
            $type_settings['use_custom_field'] = $this->props['use_custom_field'];           // custom text

            // acf fields
            if ($this->props['type'] == 'acf_fields' && class_exists('ACF') && $this->acf_extension === 'on') {
                $type_settings['post_type_for_acf'] = $this->props['post_type_for_acf'];
                $type_settings['acf_field'] = $this->props['post_type_for_acf'] != '' && $this->props['post_type_for_acf'] != 'select' ?
                    $this->props[$this->props['post_type_for_acf']] : '';
                $type_settings['acf_before_label'] = $this->props['acf_before_label'] != '' ?
                    $this->props['acf_before_label'] : '';
                $type_settings['acf_after_label'] = $this->props['acf_after_label'] != '' ?
                    $this->props['acf_after_label'] : '';
                $type_settings['acf_url_text'] = $this->props['acf_url_text'] !== '' ?
                    $this->props['acf_url_text'] : '';
                $type_settings['acf_url_new_window'] = $this->props['acf_url_new_window'] !== '' ?
                    $this->props['acf_url_new_window'] : 'off';
                $type_settings['acf_email_text'] = $this->props['acf_email_text'] !== '' ?
                    $this->props['acf_email_text'] : '';
            }

            // background
            $type_settings['background_enable_mask_style'] = isset($this->props['background_enable_mask_style']) ? $this->props['background_enable_mask_style'] : 'off';
            $type_settings['background_enable_pattern_style'] = isset($this->props['background_enable_pattern_style']) ? $this->props['background_enable_pattern_style'] : 'off';
            // Reading Time
            $type_settings['reading_time_before_label'] = $this->props['reading_time_before_label'] != '' ? $this->props['reading_time_before_label'] : '';
            $type_settings['reading_time_after_label'] = $this->props['reading_time_after_label'] != '' ? $this->props['reading_time_after_label'] : '';
        }

//        if (empty(DIFL\Handler\Divi_Compat::is_displayable($this, 'render', $this))) {
//            $type_settings = [];
//        }

        if ($this->props['outside_wrapper'] === 'on') {
            $df_post_items_outside[] = $type_settings;
        } else {
            $df_post_items[] = $type_settings;
        }

        return;
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $instance = new static();

        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-post-item');
        $class = str_replace(['-', '/'], '_', $elements->id);
        if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_header_layout' ) {
            $class .= '_tb_header';
        }
        if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_footer_layout' ) {
            $class .= '_tb_footer';
        }
        if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_body_layout' ) {
            $class .= '_tb_body';
        }
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
                'classnamesFunction' => [PostItem::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [PostItem::class, 'module_styles'],
                'scriptDataComponent' => [PostItem::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => $elements->style_components(
                        [
                            'attrName' => 'module',
                        ]
                    ) . $instance->render($class),
            ]
        );
    }

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/post-item';
        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [PostItem::class, 'render_callback'],
                    ]
                );
            }
        );
    }
}
