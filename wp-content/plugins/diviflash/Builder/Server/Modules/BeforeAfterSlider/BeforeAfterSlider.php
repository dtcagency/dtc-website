<?php
/**
 * BeforeAfterSlider Module class.
 *
 * @package DIVIFLASH5\Modules\BeforeAfterSlider;
 */

namespace DIFL\Server\Modules\BeforeAfterSlider;

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
 * Class BeforeAfterSlider
 *
 * @package DIVIFLASH5\Modules\BeforeAfterSlider
 */
class BeforeAfterSlider implements DependencyInterface
{

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/compareimage')->customCssFields;
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
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'difl/compareimage');
        $styles=[];

        if (($attrs['cm_obj_settings']['innerContent']['desktop']['value']['cm_vertical_mode'] ?? "off") === 'on' &&
            ($attrs['cm_obj_settings']['innerContent']['desktop']['value']['use_lebel_left_position'] ?? "off") === 'off'
        ) {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_cm_content.icv__icv--vertical span.icv__label.vertical',
                'attr' => $attrs['cm_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'transform: translateX(-50%);',
            ]);
        }

        if (($attrs['cm_obj_settings']['innerContent']['desktop']['value']['cm_vertical_mode'] ?? "off") === 'off' &&
            ($attrs['cm_obj_settings']['innerContent']['desktop']['value']['use_lebel_top_position'] ?? "off") === 'off') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_cm_content.icv__icv--horizontal span.icv__label',
                'attr' => $attrs['cm_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'transform: translateY(-50%);',
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
        wp_enqueue_script('compare-image-script');
        wp_enqueue_script('df-compareimage');
        $before_image_html = $this->df_render_image('before_image');
        $after_image_html = $this->df_render_image('after_image');




        $html_code = sprintf('<div class="df_cm_content">
                 %1$s
                 %2$s
            </div>', $before_image_html, $after_image_html);
        $data_options = array(
            'cm_sarting_point' => isset($this->props['cm_sarting_point']) ? $this->props['cm_sarting_point'] : 50,
            'cm_vertical_mode' => $this->props['cm_vertical_mode'] === 'on' ? true : false,
            'cm_control_hover' => $this->props['cm_control_hover'] === 'on' ? true : false,
            'cm_control_color' => isset($this->props['cm_control_color']) ? $this->props['cm_control_color'] : "#333333",
            'cm_control_shadow' => $this->props['cm_control_shadow'] === 'on' ? true : false,
            'cm_add_circle' => $this->props['cm_add_circle'] === 'on' ? true : false,
            'cm_add_circle_blur' => $this->props['cm_add_circle_blur'] === 'on' ? true : false,
            'cm_smoothing' => $this->props['cm_smoothing'] === 'on' ? true : false,
            'cm_smoothing_amount' => isset($this->props['cm_smoothing_amount']) ? $this->props['cm_smoothing_amount'] : 100,
            'cm_enable_show_lebel' => $this->props['cm_enable_show_lebel'] === 'on' ? true : false,
            'cm_before_lebel_text' => $this->props['cm_before_lebel_text'] !== '' ? $this->props['cm_before_lebel_text'] : 'Before',
            'cm_after_lebel_text' => $this->props['cm_after_lebel_text'] !== '' ? $this->props['cm_after_lebel_text'] : 'After',
            'cm_level_show_on_hover' => $this->props['cm_level_show_on_hover'] === 'on' ? true : false
        );
        return sprintf('<div class="df_cm_container" data-options=\'%2$s\'>
            %1$s
        </div>', $html_code, wp_json_encode($data_options));
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {

        $instance = new static();
        $instance->initializeProps($elements, $attrs);

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
                'classnamesFunction' => [BeforeAfterSlider::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [BeforeAfterSlider::class, 'module_styles'],
                'scriptDataComponent' => [BeforeAfterSlider::class, 'module_script_data'],
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

    public function df_render_image($props_key)
    {
        if (isset($this->props[$props_key]) && $this->props[$props_key] !== '') {
            $image_alt = $this->props[$props_key . '_alt_text'] !== '' ? $this->props[$props_key . '_alt_text'] : df_image_alt_by_url($this->props[$props_key]);
            $src = 'src';
            return sprintf(
                '<img class="%4$s" %3$s="%1$s" alt="%2$s" />',
                $this->props[$props_key],
                $image_alt,
                $src,
                $props_key
            );
        } else {
            return '';
        }
    }

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/before-after-slider';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [BeforeAfterSlider::class, 'render_callback'],
                    ]
                );
            }
        );
    }

}
