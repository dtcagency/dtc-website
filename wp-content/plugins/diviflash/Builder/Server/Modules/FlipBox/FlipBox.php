<?php
/**
 * FlipBox Module class.
 *
 * @package DIVIFLASH5\Modules\FlipBox;
 */

namespace DIFL\Server\Modules\FlipBox;

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
 * Class FlipBox
 *
 * @package DIVIFLASH5\Modules\FlipBox
 */
class FlipBox implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use \DIFL\Server\Traits\StyleUtil;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/flipbox')->customCssFields;
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

    public static function module_styles($args)
    {
        $instance = new static();
        $attrs = $instance->getDefaultKeys($args);
        $elements = $args['elements'];
        $settings = $args['settings'] ?? [];
        $order_class = $args['orderClass'];

        $instance->initializeProps($elements, $attrs);
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/flipbox');

        $styles = [];
        if ($instance->props['image_front_use_icon'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_fb_front .et-pb-icon',
                'attr' => $attrs['front_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['image_front_font_icon'] ),
            ]);
        }

        if ($instance->props['image_back_use_icon'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_fb_back .et-pb-icon',
                'attr' => $attrs['back_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['image_back_font_icon'] ),
            ]);
        }

        if ($instance->props['image_front_circle_icon'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_fb_front .et-pb-icon',
                'attr' => $attrs['front_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'border-radius: 50% !important;',
            ]);
        }

        if ($instance->props['image_back_circle_icon'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_fb_back .et-pb-icon',
                'attr' => $attrs['back_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'border-radius: 50% !important;',
            ]);
        }

        if ($instance->props['image_back_full_width'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_fb_back .df_fb_image_container img',
                'attr' => $attrs['back_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'width: 100% !important;',
            ]);
        }

        if ($instance->props['image_front_full_width'] === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_fb_front .df_fb_image_container img',
                'attr' => $attrs['front_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'width: 100% !important;',
            ]);
        }
        if($instance->props['fb_animation'??'']==="slide"){
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'],
                'attr'                => [
                    'desktop' => [
                        'value' => 'default',
                    ],
                ],
                'declarationFunction' => fn($props) => 'overflow:hidden;',
            ]);
        }

        if (($instance->props['fb_animation'] ?? '') === 'rotate' && ($instance->props['fb_content_float'] ?? '') === 'on') {
            $translate = $instance->props['fb_cf_translate'] ?? '50px';
            $scale = $instance->props['fb_cf_scale'] ?? '.9';

            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_flipbox_container.rotate.fb_floating_content .df_fb_front .fb_inner_content',
                'attr' => $attrs['fb_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => sprintf('transform: translateZ(%1$s) scale(%2$s);', $translate, $scale),
            ]);
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_flipbox_container.rotate.fb_floating_content .df_fb_front .fb_inner',
                'attr' => $attrs['fb_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'overflow: visible;',
            ]);
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_flipbox_container.rotate.fb_floating_content .df_fb_back .fb_inner_content',
                'attr' => $attrs['fb_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => sprintf('transform: translateZ(%1$s) scale(%2$s);', $translate, $scale),
            ]);
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_flipbox_container.rotate.fb_floating_content .df_fb_back .fb_inner',
                'attr' => $attrs['fb_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'overflow: visible;',
            ]);
        }

        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $args['orderClass'] ?? '', $attrs, $elements->module_metadata->attributes ?? [] ) );

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
                    ...$styles

                ],
            ]
        );
    }

    public function render()
    {
        $front_title = isset($this->props['title_front']) && $this->props['title_front'] !== '' ?
            sprintf('<%2$s class="title">%1$s</%2$s>',
                esc_html($this->props['title_front']),
                esc_attr($this->props['front_title_tag'])
            ) : '';
        $front_content = isset($this->props['content_front']) && $this->props['content_front'] !== '' ?
            sprintf('<div class="fb-text">%1$s</div>', $this->props['content_front']) : '';
        $back_title = isset($this->props['title_back']) && $this->props['title_back'] !== '' ?
            sprintf('<%2$s class="title">%1$s</%2$s>',
                esc_html($this->props['title_back']),
                esc_attr($this->props['back_title_tag'])
            ) : '';
        $back_content = isset($this->props['content_back']) && $this->props['content_back'] !== '' ?
            sprintf('<div class="fb-text">%1$s</div>', $this->props['content_back']) : '';


        $front_content_markup = $front_title !== '' || !empty($front_content) ?
            sprintf('%1$s%2$s', $front_title,
                $front_content) : '';

        $back_content_markup = $back_title !== '' || !empty($back_content) ?
            sprintf('%1$s%2$s', $back_title,
                $back_content) : '';


        return sprintf('<div class="df_flipbox_container%6$s">
                <div class="df_flipbox_body">
                    <div class="df_fb_front">
                        <div class="fb_inner">
                            <div class="fb_inner_content">%2$s%1$s</div>
                        </div>
                    </div>
                    <div class="df_fb_back">
                        <div class="fb_inner">
                            <div class="fb_inner_content">%4$s%3$s%5$s</div>
                        </div>
                    </div>
                </div>
            </div>',
            $front_content_markup,
            $this->df_render_image('image_front', 'img_front'),
            $back_content_markup,
            $this->df_render_image('image_back', 'img_back'),
            $this->df_render_button('fb_btn'),
            $this->fb_animation_class()
        );
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-flip-box');

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
                'classnamesFunction' => [FlipBox::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [FlipBox::class, 'module_styles'],
                'scriptDataComponent' => [FlipBox::class, 'module_script_data'],
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
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/flip-box';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [FlipBox::class, 'render_callback'],
                    ]
                );
            }
        );
    }

    public function df_render_image($key = '', $ad_class = '')
    {
        if (isset($this->props[$key . '_use_icon']) && $this->props[$key . '_use_icon'] === 'on') {
            return sprintf('<div class="df_fb_image_container">
                    <span class="et-pb-icon %2$s">%1$s</span>
                </div>',
                isset($this->props[$key . '']) && !empty($this->props[$key . '_font_icon']) && $this->props[$key . '_font_icon'] !== '' ?
                    esc_attr(Utils::process_font_icon($this->props[$key . '_font_icon'])) : '5',
                $ad_class
            );
        } else if (isset($this->props[$key . '_image']) && !empty($this->props[$key . '_image']) && $this->props[$key . '_image'] !== '') {
            $image_alt = $this->props[$key . '_alt_text'] !== '' ? $this->props[$key . '_alt_text'] : df_image_alt_by_url($this->props[$key . '_image']);
            return sprintf('<div class="df_fb_image_container">
                    <img alt="%2$s" src="%1$s" />
                </div>',
                esc_attr($this->props[$key . '_image']),
                $image_alt
            );
        }
    }


    public function df_render_button($key)
    {
        $text = isset($this->props[$key . '_button_text']) ? $this->props[$key . '_button_text'] : '';
        $url = isset($this->props[$key . '_button_url']) ? $this->props[$key . '_button_url'] : '';
        $target = $this->props[$key . '_button_url_new_window'] === 'on' ?
            'target="_blank"' : '';
        if ($text !== '' || $url !== '') {
            return sprintf('<div class="df_fb_button_wrapper">
                <a class="df_fb_button" href="%1$s" %3$s>%2$s</a>
            </div>',
                esc_attr($url), esc_html($text), $target);
        } else {
            return '';
        }
    }


    public function fb_animation_class()
    {
        $animation_class = '';
        // rotate animation
        if (isset($this->props['fb_animation']) && $this->props['fb_animation'] === 'rotate' && isset($this->props['fb_flip_direction'])) {
            $animation_class = ' ' . $this->props['fb_animation'] . ' ' . $this->props['fb_flip_direction'];
            if ($this->props['fb_content_float'] === 'on') {
                $animation_class .= ' fb_floating_content';
            }
        }
        // slide animation
        if (isset($this->props['fb_animation']) && $this->props['fb_animation'] === 'slide' && isset($this->props['fb_slide_direction'])) {
            $animation_class = ' ' . $this->props['fb_animation'] . ' ' . $this->props['fb_slide_direction'];
        }
        // zoom animation
        if (isset($this->props['fb_animation']) && $this->props['fb_animation'] === 'zoom' && isset($this->props['fb_zoom_direction'])) {
            $animation_class = ' ' . $this->props['fb_animation'] . ' ' . $this->props['fb_zoom_direction'];
        }
        // fade animation
        if (isset($this->props['fb_animation']) && $this->props['fb_animation'] === 'fade') {
            $animation_class = ' ' . $this->props['fb_animation'];
        }
        // 3d mode
        if (isset($this->props['fb_animation']) && $this->props['fb_animation'] === 'box_cube') {
            $animation_class = ' ' . $this->props['fb_animation'];
        }

        return $animation_class;
    }


}
