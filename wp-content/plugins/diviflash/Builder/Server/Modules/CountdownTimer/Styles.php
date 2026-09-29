<?php

namespace DIFL\Server\Modules\CountdownTimer;

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
        // Apply the preset to STYLE attr groups only so the display_style
        // branches below (flip / circle / progress CSS-variable emissions)
        // and the extract_style_attr() reads see preset values. Content
        // attr groups (timer_settings, label_settings, expiration_settings)
        // are deliberately excluded — presets carry styles, not content.
        // The Styles trait doesn't receive $block, so panel edits to fields
        // pre-filled by Divi default_value won't override preset values
        // for CSS purposes. That's acceptable: render_callback handles the
        // user-override priority correctly for the actual HTML output, and
        // Divi's preset CSS path (render_styles_preset_module) covers the
        // "user override" CSS when needed. See docs/MODULE_PRESETS.md.
        $attrs        = self::processStylePreset(
            $attrs,
            $args['name'] ?? '',
            ['module', 'style_settings', 'unit', 'number_font', 'label_font', 'separator_font']
        );
        // Resolve $variable(...)$ global color references that arrived via
        // the preset — Divi's auto-resolve filter runs before our merge.
        if (function_exists('difl_resolve_color_values_in_attrs')) {
            $attrs = difl_resolve_color_values_in_attrs($attrs);
        }
        $elements     = $args['elements'];
        $settings     = $args['settings'] ?? [];
        $order_class  = $args['orderClass'];
        // All unit-level "card" styling for the Default display style targets
        // the .df_countdown_number element directly — the number IS the card
        // in default mode. Scoped so saved styles don't leak onto the number
        // elements inside Flip / Circle / Progress.
        $num_card     = "{$order_class} .df_countdown_style_default .df_countdown_number";

        $unit_styles = [
            ['unit_gap',           '20',      'px', "{$order_class} .df_countdown_display", 'gap'],
            ['unit_min_width',     '100',     'px', $num_card, 'min-width'],
            ['unit_padding',       '20',      'px', $num_card, 'padding'],
            ['unit_border_radius', '8',       'px', $num_card, 'border-radius'],
            ['unit_border_width',  '',        'px', $num_card, 'border-width'],
            ['unit_border_color',  '',        '',   $num_card, 'border-color'],
            ['separator_size',     '36',      'px', "{$order_class} .df_countdown_separator", 'font-size'],
        ];

        $common_styles = [];
        foreach ($unit_styles as [$field, $default, $suffix, $selector, $property]) {
            $attr = self::extract_style_attr($attrs, $field, $default, $suffix);
            if (!empty($attr)) {
                $common_styles[] = CommonStyle::style([
                    'selector' => $selector,
                    'attr'     => $attr,
                    'property' => $property,
                ]);
            }
        }

        // ── Dynamic styles (previously inline JS) ──────────────────────────
        // Routed through extract_style_attr so each property carries values
        // and hover states across all responsive breakpoints.
        $style_vals = $attrs['style_settings']['innerContent']['desktop']['value'] ?? [];

        // $important=true appends " !important" to every emitted value/hover
        // so per-unit colors win over the typography font group's color
        // (Number/Label Font emit their color with !important too).
        $emit = function ($selector, $field, $default, $suffix, $property, $important = false) use ($attrs, &$common_styles) {
            $attr = self::extract_style_attr($attrs, $field, $default, $suffix);
            if (empty($attr)) {
                return;
            }
            if ($important) {
                foreach ($attr as $bp => $bp_entry) {
                    if (!empty($bp_entry['value'])) {
                        $attr[$bp]['value'] = $bp_entry['value'] . ' !important';
                    }
                    if (!empty($bp_entry['hover'])) {
                        $attr[$bp]['hover'] = $bp_entry['hover'] . ' !important';
                    }
                }
            }
            $common_styles[] = CommonStyle::style([
                'selector' => $selector,
                'attr'     => $attr,
                'property' => $property,
            ]);
        };

        // Alignment
        $layout     = $style_vals['timer_layout'] ?? 'inline';
        $align_prop = $layout === 'stacked' ? 'align-items' : 'justify-content';
        $emit("{$order_class} .df_countdown_display", 'unit_alignment', 'center', '', $align_prop);

        // Individual per-unit colors. Each style has its own dedicated color
        // fields so styles never share values (mirrors the JSX side):
        //  • Boxed     → <unit>_bg_color + <unit>_number_color + <unit>_label_color
        //  • Flip      → <unit>_flip_bg_color + <unit>_flip_text_color + <unit>_label_color
        //  • Circle    → <unit>_ring_color + <unit>_circle_fill_color (FE JS via SVG attrs) + <unit>_number_color + <unit>_label_color
        //  • Progress  → <unit>_progress_fill_color + <unit>_number_color + <unit>_label_color
        // Per-unit defaults mirror module.json default_value so toggling the
        // switch on emits the Material palette immediately even before the
        // user picks a color (Divi doesn't pre-fill these defaults into the
        // runtime attrs we read from).
        if (($style_vals['enable_individual_colors'] ?? 'off') === 'on') {
            // Per-unit defaults are split by style because the surface each
            // text element sits on differs:
            //  • Boxed / Flip — number sits on a coloured chip → white reads.
            //  • Circle / Progress — number sits on the page bg (typically
            //    white) → white blends in. Use the unit's accent colour
            //    instead so the digits stay visible by default.
            //  • Label sits below the chip on the page bg in every style →
            //    use the accent colour, never white-on-white.
            $per_unit_defaults = [
                'days'    => ['accent' => '#4285F4', 'on_chip' => '#ffffff'],
                'hours'   => ['accent' => '#34A853', 'on_chip' => '#ffffff'],
                'minutes' => ['accent' => '#EA4335', 'on_chip' => '#ffffff'],
                'seconds' => ['accent' => '#FBBC05', 'on_chip' => '#ffffff'],
            ];
            foreach (['days', 'hours', 'minutes', 'seconds'] as $u) {
                $d = $per_unit_defaults[$u];

                // Label sits on the page bg in every style.
                $emit("{$order_class} .df_countdown_wrapper .df_countdown_{$u} .df_countdown_label", "{$u}_label_color", $d['accent'], '', 'color', true);

                // Number color — split by style so each gets the contrast-
                // safe default for its own surface. User-set values are read
                // from a single `<unit>_number_color` attr, so picking one
                // colour still applies it across all three styles.
                $boxed_num_sel    = "{$order_class} .df_countdown_style_default .df_countdown_{$u} .df_countdown_number";
                $circle_num_sel   = "{$order_class} .df_countdown_style_circle  .df_countdown_{$u} .df_countdown_number";
                $progress_num_sel = "{$order_class} .df_countdown_style_progress .df_countdown_{$u} .df_countdown_number";
                $emit($boxed_num_sel,    "{$u}_number_color", $d['on_chip'], '', 'color', true);
                $emit($circle_num_sel,   "{$u}_number_color", $d['accent'],  '', 'color', true);
                $emit($progress_num_sel, "{$u}_number_color", $d['accent'],  '', 'color', true);

                // Boxed: per-unit number bg.
                $emit("{$order_class} .df_countdown_style_default .df_countdown_{$u} .df_countdown_number", "{$u}_bg_color", $d['accent'], '', 'background-color', true);

                // Flip: per-unit card bg + per-unit card text color (CSS vars on the card).
                $flip_card = "{$order_class} .df_countdown_style_flip .df_countdown_{$u} .df_flip_card";
                $emit($flip_card, "{$u}_flip_bg_color",   $d['accent'], '', '--df-flip-bg', true);
                $emit($flip_card, "{$u}_flip_text_color", $d['text'],   '', '--df-flip-text', true);

                // Split Flip: per-unit digit-card bg + digit color. Selector
                // depth matches the global split-flip selector below so the
                // per-unit value cleanly overrides the global one.
                $split_flip_card = "{$order_class} .df_countdown_style_split_flip .df_countdown_{$u} .df_split_flip_card";
                $emit($split_flip_card, "{$u}_split_flip_bg_color",   $d['accent'], '', '--df-split-flip-bg',   true);
                $emit($split_flip_card, "{$u}_split_flip_text_color", $d['text'],   '', '--df-split-flip-text', true);

                // Progress: per-unit bar fill + per-unit track behind it.
                $emit("{$order_class} .df_countdown_style_progress .df_countdown_{$u} .df_progress_fill",  "{$u}_progress_fill_color",  $d['accent'],  '', 'background-color', true);
                $emit("{$order_class} .df_countdown_style_progress .df_countdown_{$u} .df_progress_track", "{$u}_progress_track_color", '#e0e0e0',     '', 'background-color', true);
            }
        }

        // Flip card CSS custom properties
        if (($style_vals['display_style'] ?? 'flip') === 'flip') {
            $flip_sel = "{$order_class} .df_flip_card";
            $emit($flip_sel, 'flip_bg_color',      '#6E31E8', '',   '--df-flip-bg');
            $emit($flip_sel, 'flip_divider_color', '#222222', '',   '--df-flip-divider');
            $emit($flip_sel, 'flip_border_radius', '6',       'px', '--df-flip-radius');
            // Match the SCSS nesting (.df_countdown_style_flip .df_flip_card —
            // 3 classes deep) so the emit's specificity matches and cascade
            // order lets us override the static width/height in styles.scss.
            $flip_sel_specific = "{$order_class} .df_countdown_style_flip .df_flip_card";
            $emit($flip_sel_specific, 'flip_card_width',  '80',  'px', 'width');
            $emit($flip_sel_specific, 'flip_card_height', '100', 'px', 'height');
        }

        // Split Flip card CSS custom properties — same shape as Flip but
        // distinct vars / selectors so the two styles don't bleed together.
        if (($style_vals['display_style'] ?? 'flip') === 'split_flip') {
            $sf_sel = "{$order_class} .df_split_flip_card";
            $emit($sf_sel, 'split_flip_bg_color',      '#4285F4',         '',   '--df-split-flip-bg');
            $emit($sf_sel, 'split_flip_divider_color', 'rgba(0,0,0,0.3)', '',   '--df-split-flip-divider');
            $emit($sf_sel, 'split_flip_border_radius', '6',               'px', '--df-split-flip-radius');
            // Match the SCSS nesting depth so width/height override
            // the static width:60px / height:90px in styles.scss.
            $sf_sel_specific = "{$order_class} .df_countdown_style_split_flip .df_split_flip_card";
            $emit($sf_sel_specific, 'split_flip_card_width',  '60', 'px', 'width');
            $emit($sf_sel_specific, 'split_flip_card_height', '90', 'px', 'height');
            // Gap between digit cards within a unit.
            $sf_unit_sel = "{$order_class} .df_countdown_style_split_flip .df_split_flip_unit";
            $emit($sf_unit_sel, 'split_flip_digit_gap', '4', 'px', '--df-split-flip-digit-gap');
        }

        // Progress bar CSS custom properties
        if (($style_vals['display_style'] ?? 'flip') === 'progress') {
            $prog_sel = "{$order_class} .df_countdown_wrapper";
            $emit($prog_sel, 'progress_bar_height',  '12',      'px', '--df-progress-height');
            $emit($prog_sel, 'progress_track_color', '#e0e0e0', '',   '--df-progress-track');
            $emit($prog_sel, 'progress_fill_color',  '#4285F4', '',   '--df-progress-fill');
            $emit($prog_sel, 'progress_bar_radius',  '6',       'px', '--df-progress-radius');
        }

        // Circle container size
        if (($style_vals['display_style'] ?? 'flip') === 'circle') {
            $c_sel = "{$order_class} .df_circle_container";
            $emit($c_sel, 'circle_size', '120', 'px', 'width');
            $emit($c_sel, 'circle_size', '120', 'px', 'height');
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
                        $elements->style(['attrName' => 'label_font']),
                        $elements->style(['attrName' => 'separator_font']),
                        $elements->style(['attrName' => 'expired']),
                        $elements->style(['attrName' => 'unit']),
                    ],
                    $common_styles,
                    [
                    CssStyle::style(
                        [
                            'selector'  => $order_class,
                            'attr'      => $attrs['css'] ?? null,
                            'cssFields' => [
                                'timer_wrapper' => [
                                    'selectorSuffix' => ' .df_countdown_wrapper',
                                ],
                                'unit_box' => [
                                    'selectorSuffix' => ' .df_countdown_unit',
                                ],
                                'unit_number' => [
                                    'selectorSuffix' => ' .df_countdown_number',
                                ],
                                'unit_label' => [
                                    'selectorSuffix' => ' .df_countdown_label',
                                ],
                                'separator' => [
                                    'selectorSuffix' => ' .df_countdown_separator',
                                ],
                                'flip_card' => [
                                    'selectorSuffix' => ' .df_flip_card',
                                ],
                                'split_flip_card' => [
                                    'selectorSuffix' => ' .df_split_flip_card',
                                ],
                                'circle_ring' => [
                                    'selectorSuffix' => ' .df_circle_ring',
                                ],
                                'progress_track' => [
                                    'selectorSuffix' => ' .df_progress_track',
                                ],
                                'progress_fill' => [
                                    'selectorSuffix' => ' .df_progress_fill',
                                ],
                                'expired_message' => [
                                    'selectorSuffix' => ' .df_countdown_message',
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
