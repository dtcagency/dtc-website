<?php

namespace DIFL\Server\Modules\Counter;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait Styles
{
    /**
     * Extract a subfield from style_settings innerContent as a responsive attr structure.
     */
    private static function extract_style_attr($attrs, $field, $default = '', $suffix = 'px') {
        $result = [];
        foreach (['desktop', 'tablet', 'phone'] as $bp) {
            $base  = $attrs['style_settings']['innerContent'][$bp]['value'][$field] ?? null;
            $hover = $attrs['style_settings']['innerContent'][$bp]['hover'][$field] ?? null;
            $bp_entry = [];
            if ($base !== null && $base !== '') {
                $bp_entry['value'] = $base . $suffix;
            }
            if ($hover !== null && $hover !== '') {
                $bp_entry['hover'] = $hover . $suffix;
            }
            if (!empty($bp_entry)) {
                $result[$bp] = $bp_entry;
            }
        }
        if (empty($result) && $default !== '') {
            $result['desktop'] = ['value' => $default . $suffix];
        }
        return $result;
    }

    public static function styles($args)
    {
        $attrs        = $args['attrs'] ?? [];
        $elements     = $args['elements'];
        $settings     = $args['settings'] ?? [];
        $order_class  = $args['orderClass'];

        $style_fields = [
            // digit_gap is emitted twice (once for each digits container) so
            // it works for both Rolling/Typewriter (.df_counter_digits) and
            // Odometer (.df_counter_odo). The selector loop below prepends
            // the order class to a single selector each time, so multi-
            // selector strings can't be used.
            ['digit_gap',           '2',  'px', '.df_counter_digits', 'gap'],
            ['digit_gap',           '2',  'px', '.df_counter_odo',    'gap'],
            ['prefix_suffix_gap',   '',   'px', '.df_counter_content', 'gap'],
            // Odometer uses .df_counter_odo_sep for separators; Rolling
            // and Typewriter share .df_counter_sep. Emit the color
            // against both so the setting applies regardless of style.
            ['separator_color',     '',   '',   '.df_counter_sep',     'color'],
            ['separator_color',     '',   '',   '.df_counter_odo_sep', 'color'],
            // Per-digit decoration applies to every animation style that
            // renders separate digit cells: Rolling (.df_counter_digit),
            // Odometer (.df_counter_odo_digit), and Typewriter
            // (.df_counter_char). Count Up and None render the whole
            // number in a single span and have no per-digit cell. Same
            // single-selector limitation as digit_gap above, so emit one
            // row per selector.
            ['digit_padding',       '',   'px', '.df_counter_digit',     'padding'],
            ['digit_padding',       '',   'px', '.df_counter_odo_digit', 'padding'],
            ['digit_padding',       '',   'px', '.df_counter_char',      'padding'],
            ['digit_bg_color',      '',   '',   '.df_counter_digit',     'background-color'],
            ['digit_bg_color',      '',   '',   '.df_counter_odo_digit', 'background-color'],
            ['digit_bg_color',      '',   '',   '.df_counter_char',      'background-color'],
            ['digit_border_radius', '',   'px', '.df_counter_digit',     'border-radius'],
            ['digit_border_radius', '',   'px', '.df_counter_odo_digit', 'border-radius'],
            ['digit_border_radius', '',   'px', '.df_counter_char',      'border-radius'],
        ];

        $common_styles = [];
        foreach ($style_fields as [$field, $default, $suffix, $selector, $property]) {
            $attr = self::extract_style_attr($attrs, $field, $default, $suffix);
            if (!empty($attr)) {
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} {$selector}",
                    'attr'     => $attr,
                    'property' => $property,
                ]);
            }
        }

        // Alignment (responsive across breakpoints)
        $align_attr = self::extract_style_attr($attrs, 'alignment', 'center', '');
        if (!empty($align_attr)) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_counter_wrapper",
                'attr'     => $align_attr,
                'property' => 'justify-content',
            ]);
        }

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => array_merge(
                    [
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
                        $elements->style(['attrName' => 'number_font']),
                        $elements->style(['attrName' => 'prefix_font']),
                        $elements->style(['attrName' => 'suffix_font']),
                    ],
                    $common_styles,
                    [
                        CssStyle::style(
                            [
                                'selector'  => $order_class,
                                'attr'      => $attrs['css'] ?? null,
                                'cssFields' => [
                                    'counter_wrapper' => [
                                        'selectorSuffix' => ' .df_counter_wrapper',
                                    ],
                                    'counter_digits' => [
                                        'selectorSuffix' => ' .df_counter_digits',
                                    ],
                                    'counter_digit' => [
                                        'selectorSuffix' => ' .df_counter_digit',
                                    ],
                                    'counter_prefix' => [
                                        'selectorSuffix' => ' .df_counter_prefix',
                                    ],
                                    'counter_suffix' => [
                                        'selectorSuffix' => ' .df_counter_suffix',
                                    ],
                                ],
                            ]
                        ),
                    ],
                ),
            ]
        );
    }
}
