<?php

namespace DIFL\Server\Modules\ImageHotspotItem;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use DIFL\Server\Traits\ModuleUtil;


class ImageHotspotItem implements DependencyInterface
{

    use ModuleUtil;
    use HasProps;
    use StyleUtil;
    // use Fa_Icon_Handler;
    protected $config_file = 'ImageHotspotItem';

    public static $content;
    protected $order_class;

    protected $module_credits = array(
        'module_uri' => '',
        'author'     => 'DiviFlash',
        'author_uri' => '',
    );

    public static function custom_css()
    {

        $block = \WP_Block_Type_Registry::get_instance()->get_registered('difl/imagehotspotitem');
        return $block ? $block->customCssFields : [];
    }

    public function get_common_styles($args)
    {

        $attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
        $orderClass    = $args['orderClass'] ?? '';
        $elements      = $args['elements'];
        $settings      = $args['settings'] ?? [];
        $styles        = [];
        $common_styles = [];

        $common_styles[] = $elements->style([
            'attrName'   => 'module',
            'styleProps' => [
                'disabledOn' => [
                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                ],
            ],
        ]);
        $common_styles[] = CssStyle::style([
            'selector'  => $args['orderClass'],
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ]);

        $common_styles[] = $elements->style(['attrName' => 'spot_design',]);
        $spotType = $this->props['spot_type'] ?? 'icon';
        // $spotType = $attrs['spot_content']['innerContent']['desktop']['value']['spot_type'] ?? 'icon';

        ($spotType === 'text') ?
            $common_styles[] = CommonStyle::style([
                'selector'            => ".difl_imagehotspot {$orderClass}",
                'attr'                => $attrs['spot_content']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "width: auto; height:auto;",
            ])
            : '';

        $spostAnimation = $this->props['spot_animation'] ?? 'off';
        $animationStyle = $this->props['spot_animation_style'] ?? 'style_1';
        // $spostAnimation = $attrs['spot_settings']['innerContent']['desktop']['value']['spot_animation'] ?? 'off';
        // $animationStyle = $attrs['spot_settings']['innerContent']['desktop']['value']['spot_animation_style'] ?? 'style_1';


        if ('on' === $spostAnimation) {

            if ($animationStyle === 'style_1' || $animationStyle === 'style_2') {

                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_imagehotspot {$orderClass}, .difl_imagehotspot {$orderClass}.pulsating:before , .difl_imagehotspot {$orderClass}.pulsating_2:before",
                    'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                    'declarationFunction' => function($props) {
                        $animation_color = $props['attrValue']['animation_color'] ?? '';
                        $animation_color = is_scalar($animation_color) ? $animation_color : '';
                        return !empty($animation_color) ? "background-color:{$animation_color} !important;" : '';
                    },
                ]);
            }

            if ($animationStyle === 'style_3') {

                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_imagehotspot {$orderClass}.pulse:before, .difl_imagehotspot {$orderClass}.pulse:after",
                    'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                    'declarationFunction' => function($props) {
                        $animation_color = $props['attrValue']['animation_color'] ?? '';
                        $animation_color = is_scalar($animation_color) ? $animation_color : '';
                        return !empty($animation_color) ? "border-color:{$animation_color} !important;" : '';
                    },
                ]);
            }

            if ($animationStyle === 'style_4' || $animationStyle === 'style_5') {

                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_imagehotspot {$orderClass}.pulse_2, .difl_imagehotspot {$orderClass}.web_pulse-1",
                    'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                    'declarationFunction' => function($props) {
                        $color = $props['attrValue']['animation_color'] ?? $this->props['animation_color'];
                        return "color: {$color} !important;";
                    },
                ]);
            }

            $hex = $this->props['animation_color'] ?? '#37b475';
            // $hex = $attrs['spot_settings']['innerContent']['desktop']['value']['animation_color'] ?? '#37b475';
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            if ($animationStyle === 'style_7') {

                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_imagehotspot {$orderClass} .wheel",
                    'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                    'declarationFunction' => function($props) {
                        $animation_color = $props['attrValue']['animation_color'] ?? '';
                        $animation_color = is_scalar($animation_color) ? $animation_color : '';
                        return !empty($animation_color) ? "border-color:{$animation_color} !important;" : '';
                    },
                ]);
                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_imagehotspot {$orderClass} .wheel:before",
                    'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                    'declarationFunction' => function($props) {
                        $animation_color = $props['attrValue']['animation_color'] ?? '';
                        $animation_color = is_scalar($animation_color) ? $animation_color : '';
                        return !empty($animation_color) ? "background-color:{$animation_color} !important;" : '';
                    },
                ]);
                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_imagehotspot {$orderClass} .wheel",
                    'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                    'declarationFunction' => fn($props) => sprintf(' box-shadow: inset 0 0 4px 2px rgba(%1$s, %2$s, %3$s , 0.6);', $r, $g, $b),
                ]);
            }

            if ($animationStyle === 'style_8') {

                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_imagehotspot {$orderClass} .sq",
                    'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                    'declarationFunction' => function($props) {
                        $animation_color = $props['attrValue']['animation_color'] ?? $this->props['animation_color'];
                        return "background-color:{$animation_color} !important;";
                    },
                ]);
                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_imagehotspot {$orderClass} .sq",
                    'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                    'declarationFunction' => fn($props) => sprintf(' box-shadow: inset 0 0 8px 6px rgba(%1$s, %2$s, %3$s , 0.4);', $r, $g, $b),
                ]);
            }
        }

        if ('on' === $spostAnimation) {
            $common_styles[] = CommonStyle::style([
                'selector'            => ".difl_imagehotspot {$orderClass}",
                'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "width: auto; height:auto;",
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector'            => ".difl_imagehotspot {$orderClass}",
            'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => (
                isset($props['attrValue']['left_position'])
                ? "left:{$props['attrValue']['left_position']} !important;"
                : ""
            ),
        ]);
        $common_styles[] = CommonStyle::style([
            'selector'            => ".difl_imagehotspot {$orderClass}",
            'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => (
                isset($props['attrValue']['top_position'])
                ? "top:{$props['attrValue']['top_position']} !important;"
                : ""
            )
        ]);

        // process_transform_props
        $left = "-" . $this->props['left_position'] ?? '-50%';
        $top  = "-" . $this->props['top_position'] ?? '-30%';
        // $left = $attrs['spot_settings']['innerContent']['desktop']['value']['left_position'] ?? '-50%';
        // $top  = $attrs['spot_settings']['innerContent']['desktop']['value']['top_position'] ?? '-30%';

        $tx = isset($left) ? "translateX({$left})" : '';
        $ty = isset($top) ? "translateY({$top})" : '';
        $transform = trim(implode(' ', array_filter([$tx, $ty])));

        if ($transform !== '') {
            $common_styles[] = CommonStyle::style([
                'selector'            => ".difl_imagehotspot {$orderClass}",
                'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "transform: {$transform} !important;",
            ]);
        }

        // $variableWidth  = $attrs['spot_settings']['innerContent']['desktop']['value']['variable_width'] ?? 'on';
        $variableWidth  = $this->props['variable_width'] ?? 'on';
        if ($variableWidth === 'on') {
            $common_styles[] = CommonStyle::style([
                'selector'            => ".difl_imagehotspot {$orderClass}",
                'attr'                => $attrs['spot_settings']['innerContent'] ?? [],
                // 'declarationFunction' => fn($props) => "width:{$props['attrValue']['spot_width']} !important; height:{$props['attrValue']['spot_width']} !important;",
                'declarationFunction' => fn($props) => (
                    isset($props['attrValue']['spot_width'])
                    ? "width:{$props['attrValue']['spot_width']} !important; height:{$props['attrValue']['spot_width']} !important;"
                    : ""
                )
            ]);
        }
        if ('icon' === $spotType) {
            $common_styles[] = CommonStyle::style([
                'selector'            => ".difl_imagehotspot {$orderClass} .et-pb-icon.df-image-hotspot-icon",
                'attr'                => $attrs['spot_design']['innerContent'] ?? [],
                'declarationFunction' => function($props) {
                    $icon_size = $props['attrValue']['icon_size'] ?? '';
                    $icon_color = $props['attrValue']['icon_color'] ?? '';
                    // Ensure we only use scalar values (convert arrays to empty string)
                    $icon_size = is_scalar($icon_size) ? $icon_size : '';
                    $icon_color = is_scalar($icon_color) ? $icon_color : '';
                    $declarations = [];
                    if (!empty($icon_size)) {
                        $declarations[] = "font-size:{$icon_size} !important;";
                    }
                    if (!empty($icon_color)) {
                        $declarations[] = "color:{$icon_color} !important;";
                    }
                    return implode(' ', $declarations);
                },
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} img.df-image-hotspot-icon",
            'attr'                => $attrs['spot_design']['innerContent'] ?? [],
            'declarationFunction' => function($props) {
                $image_width = $props['attrValue']['image_as_icon_width'] ?? '';
                // Ensure we only use scalar values (convert arrays to empty string)
                $image_width = is_scalar($image_width) ? $image_width : '';
                return !empty($image_width) ? "width:{$image_width} !important; height:{$image_width} !important;" : '';
            },
        ]);

        // process_icon_font_style
        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .et-pb-icon.df-image-hotspot-icon",
            'attr'                => $attrs['spot_content']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => self::get_font_style($props['attrValue']['font_icon'] ?? '1'),
        ]);

        // $common_styles[] = CommonStyle::style([
        //   'selector'            => "{$orderClass} .et-pb-icon.df-image-hotspot-icon",
        //   'attr'                => $attrs['spot_content']['innerContent'] ?? [],
        //   'declarationFunction' => fn($props) => Utils::process_font_icon($this->props['font_icon']),
        // ]);

        /*
    $common_styles[] = $elements->style(['attrName' => 'ia_active_overlay_background',]);
    $common_styles[] = $elements->style([
      'attrName'   => 'item_spacing',
      'styleProps' => [
        'selector' => "{$orderClass} .difl_imageaccordionitem"
      ]
    ]);

    $common_styles[] = CommonStyle::style(
      [
        'selector' => "{$orderClass} .et-pb-icon.df-image-accordion-icon",
        'attr'     => $attrs['icon_color']['innerContent'] ?? [],
        'property' => 'color'
      ]
    );

    (($attrs['image_scale_type']['innerContent']['desktop']['value'] ?? 'no-image-scale') === 'c4-image-rotate-left') ?
      $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .df_ap_person_container:hover img,{$orderClass} .df_ap_person_container:focus img",
        'attr'                => $attrs['image_scale_value']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "transform:scale({$props['attrValue']}) rotate(-15deg);",
      ])
      : '';

   */

        foreach ($common_styles as $common_style) {
            if (empty($common_style)) {
                continue;
            }

            foreach ($common_style as $style) {
                $styles[] = $style;
            }
        }

        return $styles ?? [];
    }

    public function df_render_image_icon()
    {
        if (isset($this->props['spot_type']) && $this->props['spot_type'] === 'icon' && $this->props['use_image_as_icon'] === 'off') {

            return sprintf(
                '<span class="et-pb-icon df-image-hotspot-icon">%1$s</span>',
                isset($this->props['font_icon']) && $this->props['font_icon'] !== '' ?
                    esc_attr(Utils::process_font_icon($this->props['font_icon'])) : 'P'
            );
        } else if (isset($this->props['use_image_as_icon']) && $this->props['use_image_as_icon'] === 'on') {

            $src = 'src';
            $image_alt_text =  isset($this->props['image_as_icon']) ? df_image_alt_by_url($this->props['image_as_icon']) : '';
            $image_alt = isset($this->props['image_alt_text']) && $this->props['image_alt_text'] !== '' ? $this->props['image_alt_text']  : $image_alt_text;
            $image_url = $this->props['image_as_icon'];

            return sprintf(
                '<img class="df-image-hotspot-icon" %3$s="%1$s" alt="%2$s" />',
                $this->props['image_as_icon'],
                $image_alt,
                $src
            );
        }
    }

    public function render($attrs, $content, $block, $render_slug)
    {
        $order_class   = $this->order_class;
        // $this->handle_fa_icon();
        $icon_html = $this->df_render_image_icon();
        $spot_text = isset($this->props['spot_text']) && $this->props['spot_text'] !== '' && 'empty' !== $this->props['spot_type'] && 'text' === $this->props['spot_type'] ? wp_kses_post($this->props['spot_text']) : '';

        if ($this->props['spot_type'] === 'icon') {
            $spot_html = sprintf('%1$s', $icon_html);
        } else {
            $spot_html = sprintf('%1$s', $spot_text);
        }

        // array_push($this->order_class, $spot_animation_class);
        // array_push($this->classname, $spot_animation_class);

        // Tooltip: same processing as Divi 4 tiny_mce body (richtext + shortcodes).
        $raw_tooltip = ! empty($this->props['content']) && is_string($this->props['content']) ? $this->props['content'] : '';
        $item_content = $raw_tooltip !== '' ? preg_replace("/<p[^>]*>(?:\s|&nbsp;)*<\/p>/", '', $raw_tooltip) : '';
        if ($item_content !== '') {
            if (function_exists('et_pb_fix_shortcodes')) {
                $item_content = et_pb_fix_shortcodes($item_content, false);
            }
            if (function_exists('et_maybe_enable_embed_shortcode')) {
                $item_content = et_maybe_enable_embed_shortcode($item_content, true);
            }
            $item_content = do_shortcode($item_content);
        }

        global $df_image_hotspot_data;
        $df_imh_child_class = $order_class;
        $df_image_hotspot_data[$df_imh_child_class] = $item_content;

        $spot_type_class =  isset($this->props['spot_type']) ? 'spot_type_' . $this->props['spot_type'] : '';
        $item_html = sprintf(
            '<div class="difl_marker %2$s %3$s %4$s" data-options=\'%5$s\'>
        <span class="difl_marker_wrapper difl_image_marker">%1$s</span>
      </div>',
            $spot_html,
            $spot_type_class,
            $this->props['spot_animation'] === 'on' && isset($this->props['spot_animation_style']) && $this->props['spot_animation_style'] === 'style_7' ? 'wheel' : '',
            $this->props['spot_animation'] === 'on' && isset($this->props['spot_animation_style']) && $this->props['spot_animation_style'] === 'style_8' ? 'sq' : '',
            $item_content
        );

        return $item_html;
    }

    public static function module_classnames($args)
    {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'];

        $spot_animation      = $attrs['spot_settings']['innerContent']['desktop']['value']['spot_animation'] ?? 'off';
        $spot_animation_style = $attrs['spot_settings']['innerContent']['desktop']['value']['spot_animation_style'] ?? 'style_1';

        if ($spot_animation === "on") {

            $animation_class = '';
            switch ($spot_animation_style) {
                case "style_1":
                    $animation_class = "pulsating";
                    break;
                case "style_2":
                    $animation_class = "pulsating_2";
                    break;
                case "style_3":
                    $animation_class = "pulse";
                    break;
                case "style_4":
                    $animation_class = "pulse_2";
                    break;
                case "style_5":
                    $animation_class = "web_pulse-1";
                    break;
                case "style_6":
                    $animation_class = "pulse_key";
                    break;
                default:
                    break;
            }
            if ($animation_class) {
                $classnames_instance->add($animation_class);
            }
        }
    }
}
