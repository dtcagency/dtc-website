<?php
/**
 * TypingText Module class.
 *
 * @package DIVIFLASH5\Modules\TypingText;
 */

namespace DIFL\Server\Modules\TypingText;

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
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;


/**
 * Class TypingText
 *
 * @package DIVIFLASH5\Modules\TypingText
 */
class TypingText implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use \DIFL\Server\Traits\StyleUtil;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/typewriter-text')->customCssFields;
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
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/typewriter-text');

        if (($attrs['prefix_obj_settings']['innerContent']['desktop']['value']['prefix_clip_enable_bg_clip']??"off") === 'on' &&
            ($attrs['prefix_obj_settings']['innerContent']['desktop']['value']['prefix_clip_enable_clip']??"off") === 'on'
        ) {
            $instance->styles[] = CommonStyle::style(
                [
                    'selector' => "{$order_class} .df-twt .prefix",
                    'attr' => $attrs['prefix_obj_settings']['innerContent'] ?? [],
                    'declarationFunction' => fn($props) => "-webkit-background-clip: text !important;"
                ]
            );
        }
        if (($attrs['suffix_obj_settings']['innerContent']['desktop']['value']['suffix_clip_enable_bg_clip']??"off") === 'on' &&
            ($attrs['suffix_obj_settings']['innerContent']['desktop']['value']['suffix_clip_enable_clip']??"off") === 'on'
        ) {
            $instance->styles[] = CommonStyle::style(
                [
                    'selector' => "{$order_class} .df-twt .suffix",
                    'attr' => $attrs['suffix_obj_settings']['innerContent'] ?? [],
                    'declarationFunction' => fn($props) => "-webkit-background-clip: text !important;"
                ]
            );
        }
        if (($attrs['typed_obj_settings']['innerContent']['desktop']['value']['typed_clip_enable_bg_clip']??"off")  === 'on'&&
            ($attrs['typed_obj_settings']['innerContent']['desktop']['value']['typed_clip_enable_clip']??"off") === 'on'
        ) {
            $instance->styles[] = CommonStyle::style(
                [
                    'selector' => "{$order_class} .df-twt-element",
                    'attr' => $attrs['typed_obj_settings']['innerContent'] ?? [],
                    'declarationFunction' => fn($props) => "-webkit-background-clip: text !important;"
                ]
            );
        }

        if (($attrs['cursor_obj_settings']['innerContent']['desktop']['value']['cursor_use_icon']??"off")  === 'on'
        ) {
            $instance->styles[] = CommonStyle::style(
                [
                    'selector' => "{$order_class} .et-pb-icon",
                    'attr' => $attrs['cursor_obj_settings']['innerContent'] ?? [],
                    'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['cursor_font_icon'] ?? $instance->props['cursor_font_icon'] )
                ]
            );
        }

        $instance->styles = array_merge( $instance->styles, \difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ) );

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
                ],
            ]
        );
    }

    public function typed_text()
    {
        $lists = $this->props['typed_text_list'];
        $lists = $lists;
        $string_list = array();
        if(gettype($lists) === 'array') {
            foreach ($lists as $key => $item) {
                if (isset($item['value'])) {
                    array_push($string_list, $item['value']);
                }
            }
        }
        return $string_list;
    }

    public function render()
    {
        wp_enqueue_script('type-writer-lib');
        wp_enqueue_script('df-type-text-script');


        $classes = '';
        $link_attrs = '';
        if ($this->props['main_wrap_tag'] === 'a') {
            $link_attrs .= sprintf('href=%1$s',
                !empty($this->props['df_link']) ? esc_attr($this->props['df_link']) : ''
            );
            if ($this->props['df_link_target'] === '_blank') {
                $link_attrs .= ' target=%1$s';
            }
        }

        $cursor_text = $this->props['cursor_use_icon'] === 'on' && $this->props['cursor_font_icon'] !== '' ? esc_attr(Utils::process_font_icon($this->props['cursor_font_icon'])) : wp_kses_post($this->props['cursorchar']);
        $classes .= $this->props['cursor_use_icon'] === 'on' && $this->props['cursor_font_icon'] !== '' ? ' twt-cursor-icon' : '';
        $cursor_class = $this->props['cursor_use_icon'] === 'on' && $this->props['cursor_font_icon'] !== '' ? 'et-pb-icon Typewriter__cursor' : 'Typewriter__cursor sad';

        $data_options = array(
            'loop' => $this->props['loop'],
            'speed' => $this->props['speed'],
            'deleteSpeed' => $this->props['deletespeed'],
            'cursorchar' => $cursor_text,
            'cursorClassName' => $cursor_class,
            'cursor' => $this->props['cursor'],
            'pauseFor' => $this->props['next_delay']
        );

        $prefix = !empty($this->props['prefix']) && $this->props['prefix'] !== '' ? sprintf('<span class="prefix">%1$s</span> ', wp_kses_post($this->props['prefix'])) : '';
        $suffix = !empty($this->props['suffix']) && $this->props['suffix'] !== '' ? sprintf(' <span class="suffix">%1$s</span>', wp_kses_post($this->props['suffix'])) : '';

        return sprintf('<div class="df-twt-container%7$s" data-options=\'%4$s\'>
                <%5$s class="df-twt" %6$s>%1$s<span class="df-twt-element" data-content=\'%2$s\'></span>%3$s</%5$s>
            </div>',
            et_core_esc_previously($prefix),
            wp_json_encode($this->typed_text(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE),
            et_core_esc_previously($suffix),
            wp_json_encode($data_options),
            esc_html($this->props['main_wrap_tag']),
            esc_attr($link_attrs),
            $classes
        );
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);
        wp_enqueue_script('df-typing-text');

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
                'classnamesFunction' => [TypingText::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [TypingText::class, 'module_styles'],
                'scriptDataComponent' => [TypingText::class, 'module_script_data'],
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
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/typing-text';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [TypingText::class, 'render_callback'],
                    ]
                );
            }
        );
    }


}
