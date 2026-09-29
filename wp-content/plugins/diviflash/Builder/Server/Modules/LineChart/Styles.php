<?php

namespace DIFL\Server\Modules\LineChart;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use DIFL\Server\Traits\ProcessStylePreset;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait Styles
{
    use ProcessStylePreset;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/linechart')->customCssFields;
    }

    public static function styles($args)
    {
        $attrs        = self::processStylePreset(
            $args['attrs'] ?? [],
            $args['name'] ?? '',
            ['module', 'chart_settings', 'axis_settings', 'line_settings', 'animation_settings',
             'title_font', 'legend_font', 'tooltip_font', 'axis_tick_font', 'axis_title_font']
        );
        if (function_exists('difl_resolve_color_values_in_attrs')) {
            $attrs = difl_resolve_color_values_in_attrs($attrs);
        }
        $parent_attrs = $args['parentAttrs'] ?? [];
        $order_class  = $args['orderClass'];
        $elements     = $args['elements'];
        $settings     = $args['settings'] ?? [];

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => [
                    $elements->style(
                        [
                            'attrName'   => 'module',
                            'styleProps' => [
                                'disabledOn' => [
                                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                                ],
                                'background' => [
                                    'featureSelectors' => [
                                        'mask' => [
                                            'desktop' => [
                                                'value' => "{$order_class} .et_pb_background_mask",
                                            ],
                                        ],
                                        'pattern' => [
                                            'desktop' => [
                                                'value' => "{$order_class} .et_pb_background_pattern",
                                            ],
                                        ],
                                    ],
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

                    CommonStyle::style(
                        [
                            'selector' => "{$order_class} .df_line_chart_wrapper",
                            'attr'     => $attrs['line_settings']['innerContent'] ?? [],
                            'declarationFunction' => [self::class, 'chart_height_style'],
                        ]
                    ),
                ],
            ]
        );
    }

    public static function chart_height_style(array $args): string
    {
        $height = intval($args['attrValue']['chart_height'] ?? 0);
        if ($height > 0) {
            return "height: {$height}px;";
        }
        return '';
    }
}
