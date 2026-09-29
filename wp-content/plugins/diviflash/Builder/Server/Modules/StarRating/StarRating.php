<?php
/**
 * StarRating Module class.
 *
 * @package DIVIFLASH5\Modules\StarRating;
 */

namespace DIFL\Server\Modules\StarRating;

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


class StarRating implements DependencyInterface
{
    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use \DIFL\Server\Traits\StyleUtil;

    protected $styles;

    protected function resolvedValue($props, $subkey, $defaultValue)
    {
        if ($props['breakpoint'] === 'desktop' && $props['state'] === 'value') {
            return $props['attrValue'][$subkey] ?? $defaultValue;
        } else {
            return $props['attrValue'][$subkey] ?? null;
        }
    }

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/ratingbox')->customCssFields;
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
        $instance->styles = $instance->renderUnifiedStyles($elements, $attrs, $order_class, 'star-rating');

        $styles = [];
        if (($attrs['rating_obj_settings']['innerContent']['desktop']['value']['enable_custom_icon'] ?? "off") === 'on') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_wrapper .df_rating_icon span.df_rating_icon_fill::before',
                'attr' => $attrs['rating_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'content: attr(data-icon) !important;',
            ]);
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_wrapper .df_rating_icon span.et-pb-icon',
                'attr' => $attrs['rating_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['rating_icon'] ?? '' ),
            ]);

            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_wrapper .df_rating_icon span.df_rating_icon_empty::after',
                'attr' => $attrs['rating_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'display: none !important;',
            ]);
        }

        if (($attrs['title_obj_settings']['innerContent']['desktop']['value']['title_display_type'] ?? "block") === 'block') {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_title',
                'attr' => $attrs['title_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'display: block; width: 100%',
            ]);

            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_wrapper',
                'attr' => $attrs['rating_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) use ($instance) {
                    if ($instance->resolvedValue($props, 'rating_icon_align', 'center')) {
                        return 'align-items: ' . $instance->resolvedValue($props, 'rating_icon_align', 'center') . ' !important;';
                    }
                    return '';
                }
            ]);

            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_wrapper',
                'attr' => $attrs['title_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if (($props['attrValue']['title_placement_top_bottom'] ?? 'top') === 'top') {
                        return 'flex-direction: column-reverse;';
                    } elseif (($props['attrValue']['title_placement_top_bottom'] ?? 'top') === 'bottom') {
                        return 'flex-direction: column;';
                    }

                    return '';
                },
            ]);
        }

        if (
            ($attrs['title_obj_settings']['innerContent']['desktop']['value']['title_display_type'] ?? 'block') === 'inline' &&
            ($attrs['title_obj_settings']['innerContent']['desktop']['value']['enable_title'] ?? "off") === 'on'
        ) {
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_wrapper',
                'attr' => $attrs['rating_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) use ($instance) {
                    if ($instance->resolvedValue($props, 'rating_icon_align', 'center')) {
                        return 'justify-content: ' . ($instance->resolvedValue($props, 'rating_icon_align', 'center')) . ' !important; align-items: center !important;';
                    }
                }
            ]);
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_icon',
                'attr' => $attrs['title_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn($props) => 'display: flex; align-items: center !important;',
            ]);

            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_wrapper',
                'attr' => $attrs['title_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if (($props['attrValue']['title_placement_left_right'] ?? 'left') === 'left') {
                        return 'flex-direction: row-reverse;';
                    } elseif (($props['attrValue']['title_placement_left_right'] ?? 'left') === 'right') {
                        return 'flex-direction: row;';
                    }

                    return '';
                },
            ]);

            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . ' .df_rating_title',
                'attr' => $attrs['title_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    if (($props['attrValue']['title_placement_left_right'] ?? 'left') === 'left') {
                        return 'margin-left: 5px;';
                    } elseif (($props['attrValue']['title_placement_left_right'] ?? 'left') === 'right') {
                        return 'margin-right: 5px;';
                    }

                    return '';
                },
            ]);


            //mobile
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . '  .df_rating_title',
                'attr' => $attrs['rating_obj_settings']['innerContent'] ?? null,
                "atRules" => '@media only screen and (max-width: 767px)',
                'declarationFunction' => function ($props) use ($instance) {
                    return "width: 100% !important; margin:0px auto !important;";
                }
            ]);
            $styles[] = CommonStyle::style([
                'selector' => $args['orderClass'] . '  .df_rating_wrapper',
                'attr' => $attrs['rating_obj_settings']['innerContent'] ?? null,
                "atRules" => '@media only screen and (max-width: 767px)',
                'declarationFunction' => function ($props) use ($instance) {
                    return "align-items: unset !important; flex-direction: column-reverse !important;";
                }
            ]);

        }

        //mobile rating align
        $styles[] = CommonStyle::style([
            'selector' => $args['orderClass'] . ' .df_rating_wrapper .df_rating_icon',
            'attr' => $attrs['rating_obj_settings']['innerContent'] ?? null,
            "atRules" => '@media only screen and (max-width: 767px)',
            'declarationFunction' => function ($props) use ($instance) {
                return "width: fit-content !important; justify-content: " . ($instance->resolvedValue($props, 'rating_icon_align', 'center')) . "!important;";
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

                ],
            ]
        );
    }

    public function render()
    {
        $schema = "";
        if ($this->props['enable_schema'] && $this->props['enable_schema'] === 'on') {
            $rating_val = $this->props['rating_scale_type'] === "5"
                ? $this->props['rating_value_5']
                : $this->props['rating_value_10'];

            $json = [
                '@context' => 'https://schema.org',
                '@type' => 'Rating',
                "ratingValue" => intval($rating_val)
            ];

            $schema = '<script type="application/ld+json">' . wp_json_encode($json) . '</script>';
        }
        // Display frontend
        $output = sprintf(
            '%3$s
                <div class="df_rating_box_container">
                %1$s
                %2$s
            </div>',
            $this->df_render_rating_wrapper(),
            $this->df_render_content(),
            $schema
        );

        return $output;
    }

    public function df_render_rating_wrapper()
    {
        // Rating Title
        $title_tag = isset($this->props['rating_title_tag']) ? $this->props['rating_title_tag'] : "h4";

        $title = $this->props['enable_title'] === 'on' && !empty($this->props['title']) ? sprintf(
            '<%1$s class="df_rating_title">%2$s</%1$s>',
            $title_tag,
            $this->props['title']
        ) : "";

        // Rating Icon only
        $get_rating_icon = $this->props['enable_custom_icon'] === 'on'
            ? Utils::process_font_icon( $this->props['rating_icon'] ?? '' )
            : "☆";

        // Rating scale type
        $rating_scale_type = $this->props['enable_single_rating'] == 'off' ?
            (!empty($this->props['rating_scale_type']) ? $this->props['rating_scale_type'] : 5) : 1;

        $rating_value = $rating_scale_type == 5
            ? ($this->props['rating_value_5'] <= 5 && $this->props['rating_value_5'] >= 0
                ? $this->props['rating_value_5'] : 5) : ($this->props['rating_value_10'] <= 10 && $this->props['rating_value_10'] >= 0
                ? $this->props['rating_value_10'] : 10);

        // Get float value
        $get_float = explode('.', $rating_value);

        $rating_icon = '';
        $rating_active_class = '';

        // Display Rating Icon
        for ($i = 1; $i <= $rating_scale_type; $i++) {
            if (!isset($rating_value)) {
                $rating_active_class = '';
            } else if ($i <= $rating_value) {
                $rating_active_class = 'df_rating_icon_fill';
            } else if ($i == $get_float[0] + 1 && isset($get_float[1]) && $get_float[1] != '' && $get_float[1] != 0) {
                $rating_active_class = 'df_rating_icon_fill df_rating_icon_empty df_fill_' . $get_float[1];
            } else {
                $rating_active_class = 'df_rating_icon_empty';
            }
            $rating_icon .= '<span class="et-pb-icon ' . $rating_active_class . '" data-icon="' . $get_rating_icon . '">' . $get_rating_icon . '</span>';
        }

        // Get single rating value
        $rating_value_single = $this->props['rating_scale_type'] === "5" ? $this->props['rating_value_5'] : $this->props['rating_value_10'];

        // Show rating number
        $rating_number = '';
        if ($this->props['enable_rating_number'] === 'on') {
            if ($this->props['enable_single_rating'] !== 'on') {
                if ($this->props['rating_number_type'] === 'number_with_bracket') {
                    $rating_number = sprintf(
                        '<span class="df_rating_number">(%1$s/%2$s)</span>',
                        $rating_value,
                        $rating_scale_type
                    );
                } elseif ($this->props['rating_number_type'] === 'number_without_bracket') {
                    $rating_number = sprintf(
                        '<span class="df_rating_number">%1$s/%2$s</span>',
                        $rating_value,
                        $rating_scale_type
                    );
                } else {
                    $rating_number = sprintf('<span class="df_rating_number">%1$s</span>', $rating_value_single);
                }
            } else {
                $rating_number = sprintf('<span class="df_rating_number">%1$s</span>', $rating_value_single);
            }
        } else {
            $rating_number = '';
        }

        // Rating Number placement
        $rating_icon_and_number_placement = '';
        $this->props['rating_number_placement_left_right'] === 'left' ?
            $rating_icon_and_number_placement = $rating_number . $rating_icon : $rating_icon_and_number_placement = $rating_icon . $rating_number;

        return sprintf(
            '<div class="df_rating_wrapper">
                <div class="df_rating_icon">
                    %1$s
                </div>
                %2$s
            </div>',
            $rating_icon_and_number_placement,
            $title
        );
    }

    public function df_render_content()
    {
        $content = $this->props['enable_content'] === 'on' && !empty($this->props['content'])
            ? sprintf(
                '<div class="df_rating_content">%1$s</div>',
                $this->props['content']
            ) : "";

        return $content;
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $instance = new static();
        $instance->initializeProps($elements, $attrs);


        wp_enqueue_script('df-star-rating');

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
                'classnamesFunction' => [StarRating::class, 'module_classnames'],
                'moduleCategory' => $block->block_type->category,
                'stylesComponent' => [StarRating::class, 'module_styles'],
                'scriptDataComponent' => [StarRating::class, 'module_script_data'],
                'parentAttrs' => $parent->attrs ?? [],
                'parentId' => $parent->id ?? '',
                'parentName' => $parent->blockName ?? '',
                'children' => $elements->style_components(
                    [
                        'attrName' => 'module',
                    ]
                ) . $instance->render()
            ]
        );
    }

    public function load()
    {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/star-rating';

        add_action(
            'init',
            function () use ($module_json_folder_path) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [StarRating::class, 'render_callback'],
                    ]
                );
            }
        );
    }

}
