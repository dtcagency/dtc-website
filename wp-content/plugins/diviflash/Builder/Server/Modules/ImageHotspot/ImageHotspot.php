<?php

namespace DIFL\Server\Modules\ImageHotspot;

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
use ET\Builder\Packages\Module\Options\Background\BackgroundComponents;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\FrontEnd\Assets\StaticCSS;
use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Handler\Fa_Icon_Handler;


class ImageHotspot implements DependencyInterface
{
    public $icon_path;
    use ModuleUtil;
    use HasProps;
    use StyleUtil;

    protected $config_file = 'ImageHotspot';
    public static $content;
    protected $order_class;

    protected $module_credits = [
        'module_uri' => '',
        'author'     => 'DiviFlash',
        'author_uri' => 'diviflash.com',
    ];

    public static function custom_css()
    {

        $block = \WP_Block_Type_Registry::get_instance()->get_registered('difl/imagehotspot');
        return $block ? $block->customCssFields : [];
    }

    public function df_render_image($props_key)
    {
        // Get image URL - check both direct prop and extracted 'src' prop (for Divi 5 image objects)
        $image_url = '';
        if (isset($this->props[$props_key]) && $this->props[$props_key] !== '') {
            $image_url = $this->props[$props_key];
        } elseif (isset($this->props['src']) && $this->props['src'] !== '') {
            $image_url = $this->props['src'];
        }

        if ($image_url !== '') {
            $image_alt_text =  'image alt text '; // isset($this->props[$props_key]) ? df_image_alt_by_url($image_url) : '';
            $alt_text_value = $this->props[$props_key . '_alt_text'] ?? '';
            // Ensure alt text is a string (not an object/array)
            if (is_array($alt_text_value) || is_object($alt_text_value)) {
                $alt_text_value = '';
            }
            $photo_alt_text = (!empty($alt_text_value) && is_string($alt_text_value)) ? $alt_text_value : $image_alt_text;
            
            // Use df_sanitize_src() to handle both regular URLs and base64 data URIs
            // esc_url() filters out base64 data URIs, so we need special handling
            $sanitized_image_url = function_exists('df_sanitize_src') ? df_sanitize_src($image_url) : esc_url($image_url);
            
            // Fallback if df_sanitize_src returns empty (shouldn't happen, but safety check)
            if (empty($sanitized_image_url) && !empty($image_url)) {
                // If it's a base64 data URI, use esc_attr() as fallback
                if (preg_match('/^data:image\//', $image_url)) {
                    $sanitized_image_url = esc_attr($image_url);
                } else {
                    $sanitized_image_url = esc_url($image_url);
                }
            }

            $src = 'src';
            $image_html = sprintf(
                '<img class="hotspot_image" %3$s="%1$s" alt="%4$s" />',
                $sanitized_image_url,
                $props_key,
                $src,
                esc_attr($photo_alt_text)
            );

            return $image_html;
        }
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

        $common_styles[] = $elements->style(['attrName' => 'tooltip',]);
        $common_styles[] = $elements->style(['attrName' => 'tooltip_text_h1',]);
        $common_styles[] = $elements->style(['attrName' => 'spot',]);
        $common_styles[] = $elements->style(['attrName' => 'spots_image_border',]);
        $common_styles[] = $elements->style(['attrName' => 'hotsopt_image',]);
        $common_styles[] = $elements->style(['attrName' => 'wrapper_spacing',]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .difl_imagehotspot_container",
            'attr'     => $attrs['hotsopt_image_alignment']['innerContent'] ?? [],
            'property' => 'text-align',
        ]);
        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .et-pb-icon.df-image-hotspot-icon",
            'attr'                => $attrs['spot']['innerContent'] ?? [],
            'declarationFunction' => function($props) {
                $attrValue = $props['attrValue'] ?? [];
                $icon_color = $attrValue['spots_icon_color'] ?? '';
                $icon_size = $attrValue['spots_icon_size'] ?? '';
                // Ensure we only use scalar values (convert arrays to empty string)
                $icon_color = is_scalar($icon_color) ? $icon_color : '';
                $icon_size = is_scalar($icon_size) ? $icon_size : '';
                return "color: {$icon_color};font-size: {$icon_size}!important;";
            },
        ]);

        /*
    $common_styles[] = CommonStyle::style([
      'selector' => ".tippy-box[data-theme~='{$orderClass}'][data-placement^='top'] > .tippy-arrow::before, .tippy-box[data-theme~='{$orderClass}'][data-placement^='bottom'] > .tippy-arrow::before, .tippy-box[data-theme~='{$orderClass}'][data-placement^='right'] > .tippy-arrow::before, .tippy-box[data-theme~='{$orderClass}'][data-placement^='left'] > .tippy-arrow::before,
      .tippy-box[data-theme~='{$orderClass}'][data-placement^='top']:hover > .tippy-arrow::before, .tippy-box[data-theme~='{$orderClass}'][data-placement^='bottom']:hover > .tippy-arrow::before, .tippy-box[data-theme~='{$orderClass}'][data-placement^='right']:hover > .tippy-arrow::before, .tippy-box[data-theme~='{$orderClass}'][data-placement^='left']:hover > .tippy-arrow::before",
      'attr' => $attrs['tooltip']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "
        border-top-color: {$props['attrValue']['tooltips_arrow_color']};
        border-bottom-color: {$props['attrValue']['tooltips_arrow_color']};
        border-right-color: {$props['attrValue']['tooltips_arrow_color']};
        border-left-color: {$props['attrValue']['tooltips_arrow_color']};
      ",
    ]);*/

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .tippy-box[data-placement^='top'] > .tippy-arrow::before, {$orderClass} .tippy-box[data-placement^='bottom'] > .tippy-arrow::before, {$orderClass} .tippy-box[data-placement^='right'] > .tippy-arrow::before, {$orderClass} .tippy-box[data-placement^='left'] > .tippy-arrow::before,
      {$orderClass} .tippy-box[data-placement^='top']:hover > .tippy-arrow::before, {$orderClass} .tippy-box[data-placement^='bottom']:hover > .tippy-arrow::before, {$orderClass} .tippy-box[data-placement^='right']:hover > .tippy-arrow::before, {$orderClass} .tippy-box[data-placement^='left']:hover > .tippy-arrow::before",
            'attr' => $attrs['tooltip']['innerContent'] ?? [],
            'declarationFunction' => function($props) {
                $attrValue = $props['attrValue'] ?? [];
                $arrow_color = $attrValue['tooltips_arrow_color'] ?? '';
                // Ensure we only use scalar values (convert arrays to empty string)
                $arrow_color = is_scalar($arrow_color) ? $arrow_color : '';
                return "color: {$arrow_color};";
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .tippy-box .tippy-content p",
            'attr'                => $attrs['tooltip']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "padding-bottom: 0px;",
        ]);

        // Support for border-radius - add overflow hidden when border radius is set
        $common_styles[] = CommonStyle::style([
            'selector'            => $orderClass,
            'attr'                => $attrs['module']['decoration']['border'] ?? [],
            'declarationFunction' => function($props) {
                if (isset($props['attrValue']['radius'])) {
                    return 'overflow: hidden !important;';
                }
                return '';
            },
        ]);

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

    public function render($attrs, $content, $block, $render_slug)
    {

        $tooltip_status = $this->props['tooltip_enable'] === 'on' ? true : false;
        if ($tooltip_status === true) {
            wp_enqueue_script('image-hotspot-popper-script');
            wp_enqueue_script('image-hotspot-tippy-bundle-script');
            wp_enqueue_script('df_image_hotspot');
        }
        global $df_image_hotspot_data;

        $order_class = $this->order_class ?? '';
        if (! class_exists('DF_Localize_Vars')) {
            require_once DIFL_MAIN_DIR . '/includes/classes/df-class-localize-vars.php';
        }
        // 'class' is required: DF_Localize_Vars uses it as the key per module instance (see df-class-localize-vars.php).
        \DF_Localize_Vars::enqueue(
            'df_image_hotspot',
            array(
                'class'               => $order_class,
                'toogle_content_data' => $df_image_hotspot_data,
            )
        );

        $df_image_hotspot_data = array();
        // $this->additional_css_styles($render_slug);
        $hotspot_image = $this->df_render_image('hotsopt_image');
        $data_options = array(
            'tooltip_enable'     =>  $this->props['tooltip_enable'] === 'on' ? true : false,
            'arrow'              =>  $this->props['tooltip_arrow'] === 'on' ? true : false,
            'interactive'        => $this->props['tooltip_interactive'] === 'on' ? true : false,
            'interactiveBorder'  => $this->props['tooltip_interactive'] === 'on' && isset($this->props['tooltip_interactive_border']) ? $this->props['tooltip_interactive_border'] : 2,
            'interactiveDebounce' => $this->props['tooltip_interactive'] === 'on' && isset($this->props['tooltip_interactive_debounce']) ? $this->props['tooltip_interactive_debounce'] : 0,
            'animation'          => isset($this->props['tooltip_animation']) ? $this->props['tooltip_animation'] : 'fade',
            'placement'          => isset($this->props['tooltip_placement']) ? $this->props['tooltip_placement'] : 'top',
            'trigger'            => isset($this->props['tooltip_trigger']) ? $this->props['tooltip_trigger'] : 'focus',
            'followCursor'       => $this->props['tooltip_follow_cursor'] === 'on' ? true : false,
            'maxWidth'           => isset($this->props['tooltip_custom_maxwidth']) ? $this->props['tooltip_custom_maxwidth'] : 350,
            'offsetEnable'       => $this->props['tooltip_offset_enable'] === 'on' ? true : false,
            'offsetSkidding'     => $this->props['tooltip_offset_enable'] === 'on' && isset($this->props['tooltip_offset_skidding']) ? $this->props['tooltip_offset_skidding'] : 0,
            'offsetDistance'     => $this->props['tooltip_offset_enable'] === 'on' && isset($this->props['tooltip_offset_distance']) ? $this->props['tooltip_offset_distance'] : 10
        );

        // Get background attributes for parallax rendering
        $background_attr = $attrs['module']['decoration']['background'] ?? [];
        $background_component = BackgroundComponents::component([
            'attr'          => $background_attr,
            'id'            => $block->parsed_block['id'] ?? '',
            'settings'      => [
                'parallax' => true,
                'video'    => false,
                'pattern'  => true,
                'mask'     => true,
            ],
            'storeInstance' => $block->parsed_block['storeInstance'] ?? null,
        ]);

        $html_code = sprintf(
            '%4$s<div class="difl_imagehotspot_container" data-options=\'%3$s\'>
                    <div class="difl_imagehotspot_wrapper">
                        <div class="difl_image_wrapper">
                            %2$s
                        </div>
                        %1$s
                    </div>
                </div>',
            et_core_sanitized_previously($content),
            $hotspot_image,
            wp_json_encode($data_options),
            $background_component
        );
        return $html_code;
    }
}
