<?php

namespace DIFL\Server\Modules\PieChart;

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

    public static function styles($args)
    {
        $attrs        = self::processStylePreset(
            $args['attrs'] ?? [],
            $args['name'] ?? '',
            ['module', 'chart_settings', 'pie_settings', 'animation_settings',
             'title_font', 'legend_font', 'tooltip_font']
        );
        if (function_exists('difl_resolve_color_values_in_attrs')) {
            $attrs = difl_resolve_color_values_in_attrs($attrs);
        }
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
                            ],
                        ]
                    ),
                    CssStyle::style(
                        [
                            'selector'  => $args['orderClass'],
                            'attr'      => $attrs['css'] ?? null,
                            'cssFields' => [
                                'chart_wrapper' => [
                                    'selectorSuffix' => ' .df_pie_chart_wrapper',
                                ],
                                'chart_canvas' => [
                                    'selectorSuffix' => ' .df_pie_chart_canvas',
                                ],
                            ],
                        ]
                    ),

                    CommonStyle::style(
                        [
                            'selector' => "{$order_class} .df_pie_chart_wrapper",
                            'attr'     => $attrs['pie_settings']['innerContent'] ?? [],
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
