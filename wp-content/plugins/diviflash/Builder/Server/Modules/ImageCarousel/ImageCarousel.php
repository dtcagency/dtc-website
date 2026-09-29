<?php

/**
 * ImageCarousel Module class.
 *
 * @package DIVIFLASH5\Modules\ImageCarousel;
 */

namespace DIFL\Server\Modules\ImageCarousel;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use DIFL\Server\Traits\StyleUtil;
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
 * Class ImageCarousel
 *
 * @package DIVIFLASH5\Modules\ImageCarousel
 */
class ImageCarousel implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use StyleUtil;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/imagecarousel')->customCssFields;
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
        $instance = new static();
        if (!empty($instance->props['use_lightbox']) && $instance->props['use_lightbox'] === 'on') {
            wp_enqueue_script('lightgallery-script');
            $classnames_instance->add(
                'has_lightbox'
            );
        }

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
            if (empty($attrs[$key]['decoration'])) {
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
        ;
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'];

        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/imagecarousel');
        $styles = [];
        $resolve_value = function ($props, $key, $instance) {
            return $props['attrValue'][$key] ?? $instance->props[$key];
        };
        if ($instance->props['ic_equal_height'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .difl_imagecarouselitem",
                'attr' => $attrs['ic_obj_settings']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "height:auto;",
            ]);
        }
        if ($instance->props['content_hover'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .content",
                'attr' => $attrs['content_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) use ($instance) {
                    $anim_direction = $props['attrValue']['anim_direction'] ?? $instance->props['anim_direction'];

                    return "opacity: 0; transform:" . $instance->df_transform_values($anim_direction, 'default') . ";";
                },
            ]);
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .difl_imagecarouselitem:hover .content",
                'attr' => $attrs['content_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) use ($instance) {
                    $anim_direction = $props['attrValue']['anim_direction'] ?? $instance->props['anim_direction'];

                    return "opacity: 1; transform:" . $instance->df_transform_values($anim_direction, 'hover') . ";";
                },
            ]);
        }
        ;
        if ($instance->props['item_overflow'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .difl_imagecarouselitem > div",
                'attr' => $attrs['ic_obj_settings']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "overflow: hidden;",
            ]);
        }
        if (($instance->props['coverflow_shadow'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .swiper-container-3d .swiper-slide-shadow-left",
                'attr' => $attrs['settings_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "background-image: linear-gradient(to left,{$resolve_value($props, 'coveflow_color_dark', $instance)},{$resolve_value($props, 'coveflow_color_light', $instance)}); !important;"
            ]);

            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .swiper-container-3d .swiper-slide-shadow-right",
                'attr' => $attrs['settings_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "background-image: linear-gradient(to right,{$resolve_value($props, 'coveflow_color_dark', $instance)},{$resolve_value($props, 'coveflow_color_light', $instance)}); !important;"
            ]);
        }
        //Start arrow positioning
        if (($instance->props['arrow'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_ic_arrows",
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

            if ( ( $instance->props['arrow_prev_icon_use_icon'] ?? null ) === 'on' ) {
                $styles[] = CommonStyle::style( [
                    'selector'            => "{$order_class} .swiper-button-prev:after",
                    'attr'                => $attrs['arrow_prev_obj_settings']['innerContent'] ?? $attrs['arrow_prev_obj_settings']['settings']['innerContent'] ?? null,
                    'declarationFunction' => function () use ( $instance ) {
                        return self::get_font_style( $instance->props['arrow_prev_icon_font_icon'] ?? '' );
                    },
                ] );
            }

            if ( ( $instance->props['arrow_next_icon_use_icon'] ?? null ) === 'on' ) {
                $styles[] = CommonStyle::style( [
                    'selector'            => "{$order_class} .swiper-button-next:after",
                    'attr'                => $attrs['arrow_next_obj_settings']['innerContent'] ?? $attrs['arrow_next_obj_settings']['settings']['innerContent'] ?? null,
                    'declarationFunction' => function () use ( $instance ) {
                        return self::get_font_style( $instance->props['arrow_next_icon_font_icon'] ?? '' );
                    },
                ] );
            }

        }
        //End arrow positioning

        //Start Dots
        if (($instance->props['large_active_dot'] ?? null) === "on") {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .swiper-pagination .swiper-pagination-bullet-active",
                'attr' => $attrs['dots_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => "width: 40px; border-radius: 20px;"
            ]);
        }
        //End Dots
        if (
            !empty($attrs['module']['decoration']['border']['desktop']['value']['radius']) ||
            !empty($attrs['module']['decoration']['border']['tablet']['value']['radius']) ||
            !empty($attrs['module']['decoration']['border']['phone']['value']['radius'])
        ) {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class} .difl_imagecarouselitem > div",
                'attr' => [
                    'desktop' => [
                        'value' => 'default',
                    ],
                ],
                'declarationFunction' => fn($props) => "overflow: hidden !important;",
            ]);
        }

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

    public function render($attrs, $content)
    {
        if ($content === '') {
            return sprintf(
                '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Image Item.</strong></h2>'
            );
        }


        wp_enqueue_script('swiper-script');
        wp_enqueue_script('df-imagecarousel');

        $class = '';
        $order_number = wp_rand( 1, 99999999 );
        $data = [
            'effect' => $this->props['carousel_type'],
            'desktop' => $this->props['item_desktop'],
            'tablet' => $this->props['item_tablet'],
            'mobile' => $this->props['item_mobile'],
            'variable_width' => $this->props['variable_width'],
            'loop' => $this->props['loop'] === 'on' ? true : false,
            'item_spacing' => $this->props['item_spacing'] ?? 10,
            'item_spacing_tablet' => $attrs['item_obj_settings']['innerContent']['tablet']['value']['item_spacing'] ?? $this->props['item_spacing'] ?? 10,
            'item_spacing_phone' => $attrs['item_obj_settings']['innerContent']['phone']['value']['item_spacing'] ?? $attrs['item_obj_settings']['innerContent']['tablet']['value']['item_spacing'] ?? $this->props['item_spacing'] ?? 10,
            'arrow' => $this->props['arrow'],
            'dots' => $this->props['dots'],
            'autoplay' => $this->props['autoplay'],
            'auto_delay' => $this->props['autospeed'],
            'speed' => $this->props['speed'],
            'pause_hover' => $this->props['pause_hover'],
            'centeredSlides' => $this->props['centered_slides'],
            'order' => $order_number,
            'use_lightbox' => $this->props['use_lightbox'],
            'use_lightbox_caption' => $this->props['use_lightbox_caption']
        ];
        if ($this->props['carousel_type'] === 'coverflow') {
            $data['slideShadows'] = $this->props['coverflow_shadow'];
            $data['rotate'] = $this->props['coverflow_rotate'];
            $data['stretch'] = $this->props['coverflow_stretch'];
            $data['depth'] = $this->props['coverflow_depth'];
            $data['modifier'] = $this->props['coverflow_modifier'];
        }

        if (isset($this->props['variable_width']) && $this->props['variable_width'] === 'on') {
            $class .= ' variable-width';
        }

        // arrow position classes
        if ($this->props['arrow'] === 'on') {
            $arrow_position = '' !== $this->props['arrow_position'] ? $this->props['arrow_position'] : 'middle';
            $class .= ' arrow-' . $arrow_position;
        }

        return sprintf(
            '<div class="df_ic_container%5$s" data-settings=\'%4$s\' data-item="%6$s" data-itemtablet="%7$s" data-itemphone="%8$s">
                <div class="df_ic_inner_wrapper">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            %1$s
                        </div>
                    </div>
                    %2$s
                </div>
                %3$s
            </div>',
            et_core_sanitized_previously($content),
            $this->df_ic_arrow($order_number),
            $this->df_ic_dots($order_number),
            wp_json_encode($data),
            $class,
            $this->props['item_desktop'],
            $this->props['item_tablet'],
            $this->props['item_mobile']
        );
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-image-carousel');
        $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
            function ($inner_block) {
                return $inner_block['id'];
            },
            $block->parsed_block['innerBlocks']
        ) : [];

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
                'classnamesFunction' => [ImageCarousel::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [ImageCarousel::class, 'module_styles'],
                'scriptDataComponent' => [ImageCarousel::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'childrenIds' => $children_ids,
                'children' => $elements->style_components(
                    [
                        'attrName' => 'module',
                    ]
                ) . $instance->render($attrs, $content),
            ]
        );
    }

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/image-carousel';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ImageCarousel::class, 'render_callback'],
                    ]
                );
            }
        );
    }

    public function df_arrow_pos_styles($value)
    {
        $options = array(
            'top' => 'position: relative;
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
        );

        return $options[$value];
    }


    public function df_ic_arrow($order_number)
    {
        $prev_icon = $this->props['arrow_prev_icon_use_icon'] === 'on' && isset( $this->props['arrow_prev_icon_font_icon'] ) && ! empty( $this->props['arrow_prev_icon_font_icon'] ) ?
            esc_attr( Utils::process_font_icon( $this->props['arrow_prev_icon_font_icon'] ) ?? '4' ) : '4';
        $next_icon = $this->props['arrow_next_icon_use_icon'] === 'on' && isset( $this->props['arrow_next_icon_font_icon'] ) && ! empty( $this->props['arrow_next_icon_font_icon'] ) ?
            esc_attr( Utils::process_font_icon( $this->props['arrow_next_icon_font_icon'] ) ?? '5' ) : '5';

        return $this->props['arrow'] === 'on' ? sprintf('
            <div class="df_ic_arrows">
                <div class="swiper-button-next ic-next-%1$s" data-icon="%3$s"></div>
                <div class="swiper-button-prev ic-prev-%1$s" data-icon="%2$s"></div>
            </div>
        ', $order_number, $prev_icon, $next_icon) : '';
    }


    public function df_ic_dots($order_number)
    {
        return $this->props['dots'] === 'on' ?
            sprintf('<div class="swiper-pagination ic-dots-%1$s"></div>', $order_number) : '';
    }


    public function df_transform_values($key = 'top', $state = 'default')
    {
        $transform_values = array(
            'top' => [
                'default' => 'translateY(-60px)',
                'hover' => 'translateY(0px)'
            ],
            'bottom' => [
                'default' => 'translateY(60px)',
                'hover' => 'translateY(0px)'
            ],
            'left' => [
                'default' => 'translateX(-60px)',
                'hover' => 'translateX(0px)'
            ],
            'right' => [
                'default' => 'translateX(60px)',
                'hover' => 'translateX(0px)'
            ],
            'center' => [
                'default' => 'scale(0)',
                'hover' => 'scale(1)'
            ],
            'top_right' => [
                'default' => 'translateX(50px) translateY(-50px)',
                'hover' => 'translateX(0px) translateY(0px)'
            ],
            'top_left' => [
                'default' => 'translateX(-50px) translateY(-50px)',
                'hover' => 'translateX(0px) translateY(0px)'
            ],
            'bottom_right' => [
                'default' => 'translateX(50px) translateY(50px)',
                'hover' => 'translateX(0px) translateY(0px)'
            ],
            'bottom_left' => [
                'default' => 'translateX(-50px) translateY(50px)',
                'hover' => 'translateX(0px) translateY(0px)'
            ]
        );

        return $transform_values[$key][$state];
    }
}
