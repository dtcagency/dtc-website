<?php

namespace DIFL\Server\Traits;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Packages\IconLibrary\IconFont\Utils;

/**
 * Reusable trait for rendering Font Awesome and Divi icons.
 *
 * Usage in module:
 * use DIFL\Server\Traits\IconRenderTrait;
 *
 * class YourModule {
 *     use IconRenderTrait;
 *
 *     // In render_callback:
 *     $icon_html = self::render_icon($font_icon, [
 *         'class' => 'your-icon-class',
 *         'default_icon' => '&#x4e;'
 *     ]);
 * }
 */
trait IconRenderTrait {

    /**
     * Render an icon (Font Awesome or Divi ETmodules)
     *
     * @param mixed $font_icon The icon data (array, object, or string)
     * @param array $options   Rendering options
     *                         - class: Additional CSS classes (default: '')
     *                         - default_icon: Default icon unicode (default: '&#x4e;')
     *                         - wrapper_class: Wrapper span class (default: 'et-pb-icon')
     *                         - tag: HTML tag to use (default: 'span')
     * @return string HTML for the icon
     */
    public static function render_icon($font_icon, array $options = []): string {
        $defaults = [
            'class'         => '',
            'default_icon'  => '&#x4e;',
            'wrapper_class' => 'et-pb-icon',
            'tag'           => 'span',
        ];

        $options = array_merge($defaults, $options);
        $icon_data = self::get_icon_data($font_icon, $options['default_icon']);

        // Build CSS classes
        $classes = [$options['wrapper_class']];
        if ($icon_data['is_fa']) {
            $classes[] = 'fa-icon';
        }
        if (!empty($options['class'])) {
            $classes[] = $options['class'];
        }
        $class_string = implode(' ', $classes);

        // Build inline style for FA icons
        $icon_style = $icon_data['is_fa'] ? sprintf(' style="font-weight: %d;"', $icon_data['weight']) : '';

        $tag = sanitize_key($options['tag']);

        return sprintf(
            '<%1$s class="%2$s"%3$s>%4$s</%1$s>',
            $tag,
            esc_attr($class_string),
            $icon_style,
            $icon_data['content']
        );
    }

    /**
     * Get icon data for use in rendering or data attributes
     *
     * @param mixed  $font_icon    The icon data
     * @param string $default_icon Default icon unicode if none provided
     * @return array Normalized icon data with content, is_fa, weight, font_family
     */
    public static function get_icon_data($font_icon, string $default_icon = '&#x4e;'): array {
        $is_fa_icon = false;
        $icon_weight = 400;
        $icon_content = '';

        if (!empty($font_icon)) {
            if (is_array($font_icon) || is_object($font_icon)) {
                $icon_content = Utils::process_font_icon($font_icon);
                $is_fa_icon = Utils::is_fa_icon($font_icon);

                $font_icon_arr = is_object($font_icon) ? (array)$font_icon : $font_icon;
                $icon_weight = isset($font_icon_arr['weight'])
                    ? (int)$font_icon_arr['weight']
                    : ($is_fa_icon ? 900 : 400);
            } else {
                $icon_content = esc_attr($font_icon);
            }
        } else {
            $icon_content = $default_icon;
        }

        return [
            'content'     => $icon_content,
            'is_fa'       => $is_fa_icon,
            'weight'      => $icon_weight,
            'font_family' => $is_fa_icon ? 'FontAwesome' : 'ETmodules',
        ];
    }
}
