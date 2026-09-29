<?php

namespace DIFL\Server\Modules\ContentCarouselItem;

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
use ET\Builder\Packages\StyleLibrary\Declarations\Declarations;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use DIFL\Server\Traits\ModuleUtil;


class ContentCarouselItem implements DependencyInterface
{

    use ModuleUtil;
    use HasProps;
    use StyleUtil;
    // use Fa_Icon_Handler;
    protected $config_file = 'ContentCarouselItem';

    public static $content;
    public $parent;
    protected $order_class;

    protected $module_credits = array(
        'module_uri' => '',
        'author'     => 'DiviFlash',
        'author_uri' => '',
    );

    public static function custom_css()
    {
        $block = \WP_Block_Type_Registry::get_instance()->get_registered('difl/contentcarouselitem');
        return $block ? $block->customCssFields : [];
    }

    public static function font_style($font)
    {
        return static::get_font_style($font);
    }

    public static function module_classnames($args)
    {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'];

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

        // Module (decoration + link for et_clickable when link is available).
        $classnames_instance->add(
            ElementClassnames::classnames(
                [
                    'attrs' => array_merge(
                        $attrs['module']['decoration'] ?? [],
                        [
                            'link' => $attrs['module']['advanced']['link'] ?? [],
                        ]
                    ),
                ]
            )
        );
        $classnames_instance->add('swiper-slide');
    }

    public static function module_script_data($args)
    {
        $elements = $args['elements'];

        // Element Script Data Options.
        $elements->script_data(['attrName' => 'module',]);
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

        $block  = \WP_Block_Type_Registry::get_instance()->get_registered('difl/contentcarouselitem');
        // $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $pBtnFullWidth = $parent->attrs['cc_button']["innerContent"]["desktop"]["value"]["cc_button_button_fullwidth"] ?? "off";

        $common_styles[] = $elements->style([
            'attrName'   => 'module',
            'styleProps' => [
                'disabledOn' => [
                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                ],
            ],
        ]);

        // Clip content when module border radius is set on .et_pb_module_inner.
        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .et_pb_module_inner",
            'attr'                => $attrs['module']['decoration']['border'] ?? [],
            'declarationFunction' => function ( $props ) use ( $attrs ) {
                return Declarations::overflow_for_border_radius_style_declaration(
                    $props,
                    $attrs['module']['decoration']['overflow'] ?? []
                );
            },
        ]);

        $common_styles[] = CssStyle::style([
            'selector'  => $args['orderClass'],
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ]);

        $common_styles[] = $elements->style(['attrName' => 'cc_title',]);
        $common_styles[] = $elements->style(['attrName' => 'cc_subtitle',]);
        $common_styles[] = $elements->style(['attrName' => 'cc_content',]);
        $common_styles[] = $elements->style(['attrName' => 'content_image',]);
        $common_styles[] = $elements->style(['attrName' => 'cc_button',]);
        $common_styles[] = $elements->style(['attrName' => 'button_wrapper_spacing',]);
        $common_styles[] = $elements->style(['attrName' => 'btn_icon_margin',]);
        $common_styles[] = $elements->style(['attrName' => 'image',]);
        $common_styles[] = $elements->style(['attrName' => 'icon',]);
        $common_styles[] = $elements->style(['attrName' => 'customSpacing',]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .df_cci_image_container",
            'attr'                => $attrs['image']['decoration']['border'] ?? [],
            'declarationFunction' => function($props) {
                if(isset($props['attrValue']['radius'])) {
                    return "overflow: hidden !important;";
                }
            },
        ]);

        if ($this->props['df_cci_full_width'] === 'on') {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cci_image_container img",
                'attr'                => $attrs['image']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "width: 100% !important;",
            ]);
        } else {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cci_image_container img",
                'attr'                => $attrs['image']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "max-width: {$this->props['df_cci_max_width']} !important;",
            ]);
        }

        if ($this->props['df_cci_full_width'] !== 'on') {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cci_image_container",
                'attr'                => $attrs['image']['innerContent'] ?? [],
                'declarationFunction' => function($props) {
                    $text_align = $props['attrValue']['df_cci_image_align'] ?? $this->props['df_cci_image_align'];
                    return "text-align: {$text_align};";
                },
            ]);
        }

        if ($this->props['df_cci_use_icon'] === 'on') {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .et-pb-icon",
                'attr'                => $attrs['df_cci_icon_color']['innerContent'] ?? [],
                'declarationFunction' => function($props) {
                    $icon_color = $props['attrValue'] ?? $this->props['df_cci_icon_color'] ?? '';
                    return "color: {$icon_color} !important;";
                },
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .et-pb-icon",
                'attr'                => $attrs['df_cci_icon_size']['innerContent'] ?? [],
                'declarationFunction' => function($props) {
                    $icon_size = $props['attrValue'] ?? $this->props['df_cci_icon_size'] ?? '48px';
                    return "font-size: {$icon_size} !important;";
                },
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .et-pb-icon",
                'attr'                => $attrs['df_cci_icon_bg']['innerContent'] ?? [],
                'declarationFunction' => function($props) {
                    $icon_bg = $props['attrValue'] ?? $this->props['df_cci_icon_bg'] ?? '';
                    return "background-color: {$icon_bg} !important;";
                },
            ]);
        }

        if ($this->props['df_cci_use_icon'] === 'on' && $this->props['df_cci_circle_icon'] === 'on') {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .et-pb-icon",
                'attr'                => $attrs['df_cci_circle_icon']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "border-radius: 50%;",
            ]);
        }

        if ($this->props['df_cci_use_icon'] === 'on') {
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_cci_image_container",
                'attr'                => $attrs['df_cci_icon_align']['innerContent'] ?? [],
                'declarationFunction' => function($props) {
                    $text_align = $props['attrValue'] ?? $this->props['df_cci_icon_align'] ?? 'left';
                    return "text-align: {$text_align};";
                }
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector' => ".difl_contentcarousel {$orderClass} .df_cci_button_wrapper",
            'attr'     => $attrs['cc_button']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "text-align: {$this->props['cc_button_button_align']};",
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => ".difl_contentcarousel {$orderClass} .df_cci_image_container",
            'attr'     => $attrs['image_order']['innerContent'] ?? [],
            'property' => 'order'
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => ".difl_contentcarousel {$orderClass} .df_cc_title",
            'attr'     => $attrs['title_order']['innerContent'] ?? [],
            'property' => 'order'
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => ".difl_contentcarousel {$orderClass} .df_cc_subtitle",
            'attr'     => $attrs['subtitle_order']['innerContent'] ?? [],
            'property' => 'order'
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => ".difl_contentcarousel {$orderClass} .df_cc_content",
            'attr'     => $attrs['content_order']['innerContent'] ?? [],
            'property' => 'order'
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => ".difl_contentcarousel {$orderClass} .df_cci_button_wrapper",
            'attr'     => $attrs['button_order']['innerContent'] ?? [],
            'property' => 'order'
        ]);
        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .df_cci_container .et-pb-icon",
            'attr'                => $attrs['df_cci_font_icon']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => self::get_font_style($this->props['df_cci_font_icon'] ?? '1'),
        ]);
        if ($this->props['df_cci_circle_icon'] === 'on') {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_cci_container .et-pb-icon",
                'attr' => $attrs['df_cci_circle_icon']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "border-radius:50% !important; box-shadow: none !important;",
            ]);
        }

        // Button Style
        if($this->props['btn_use_icon'] === 'on') {
            // Regular state: no default font-size
            $common_styles[] = CommonStyle::style([
                'selector' => ".difl_contentcarousel {$orderClass} .df_cci_button .df_cci_btn_icon.df_cci_btn_hover_off",
                'attr' => $attrs['cc_button']['innerContent'] ?? [],
                'declarationFunction' => function($props) {
                    $font_size  = $props['attrValue']['btn_icon_font_size'] ?? $this->props['btn_icon_font_size'];
                    $icon_color = $props['attrValue']['btn_icon_color'] ?? $this->props['btn_icon_color'];
                    if ( $this->parent !== null ) {
                        $p = $this->parent->attrs['cc_button']['innerContent']['desktop']['value'] ?? [];
                        if ( empty( $font_size ) && ! empty( $p['btn_icon_font_size'] ) ) {
                            $font_size = $p['btn_icon_font_size'];
                        }
                        if ( empty( $icon_color ) && ! empty( $p['btn_icon_color'] ) ) {
                            $icon_color = $p['btn_icon_color'];
                        }
                    }
                    $output = '';
                    if ( ! empty( $font_size ) ) {
                        $output = "font-size: {$font_size}";
                    }
                    if ( ! empty( $icon_color ) ) {
                        $output .= ( $output !== '' ? '; ' : '' ) . "color: {$icon_color}";
                    }
                    return $output !== '' ? $output . ';' : $output;
                },
            ]);

            // Hover state: default font-size 14px when empty
            $common_styles[] = CommonStyle::style([
                'selector' => ".difl_contentcarousel {$orderClass} .df_cci_button:hover .df_cci_btn_icon.df_cci_btn_hover_on",
                'attr' => $attrs['cc_button']['innerContent'] ?? [],
                'declarationFunction' => function($props) {
                    $font_size  = $props['attrValue']['btn_icon_font_size'] ?? $this->props['btn_icon_font_size'];
                    $icon_color = $props['attrValue']['btn_icon_color'] ?? $this->props['btn_icon_color'];
                    if ( $this->parent !== null ) {
                        $p = $this->parent->attrs['cc_button']['innerContent']['desktop']['value'] ?? [];
                        if ( empty( $font_size ) && ! empty( $p['btn_icon_font_size'] ) ) {
                            $font_size = $p['btn_icon_font_size'];
                        }
                        if ( empty( $icon_color ) && ! empty( $p['btn_icon_color'] ) ) {
                            $icon_color = $p['btn_icon_color'];
                        }
                    }
                    if ( empty( $font_size ) ) {
                        $font_size = '14px';
                    }
                    $output = '';
                    if ( ! empty( $font_size ) ) {
                        $output = "font-size: {$font_size}";
                    }
                    if ( ! empty( $icon_color ) ) {
                        $output .= ( $output !== '' ? '; ' : '' ) . "color: {$icon_color}";
                    }
                    return $output !== '' ? $output . ';' : $output;
                },
            ]);

            // When "Show icon on hover" is on: hide icon by default so it appears on button hover (match legacy includes/modules/ContentCarouselItem)
            if (($this->props['btn_icon_show_hover'] ?? '') === 'on') {
                $common_styles[] = CommonStyle::style([
                    'selector'            => ".difl_contentcarousel {$orderClass} .df_cci_btn_icon.df_cci_btn_hover_on",
                    'attr'                => $attrs['cc_button']['innerContent'] ?? [],
                    'declarationFunction' => fn($props) => "font-size: 0; transition: 0.3s;",
                ]);
            }

            $common_styles[] = CommonStyle::style([
                'selector'            => ".difl_contentcarousel {$orderClass} .df_cci_button_wrapper .df_cci_btn_icon",
                'attr'                => $attrs['btn_font_icon']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => self::get_font_style($this->props['btn_font_icon'] ?? '4'),
            ]);
        }


        if ($pBtnFullWidth === 'on' && $this->props['btn_use_icon'] === 'on') {

            $btnTextAlign = $this->props['cc_button_button_align'] ?? 'center';
            $textIconP    = $btnTextAlign;

            if ($btnTextAlign === 'right') {
                $textIconP = 'flex-end';
            } else if ($btnTextAlign === 'left') {
                $textIconP = 'flex-start';
            }

            $common_styles[] = CommonStyle::style([
                'selector' => ".difl_contentcarousel {$orderClass} span.df_cci_btn_text_icon_wrap",
                'attr' => $attrs['btn_use_icon']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "justify-content: {$textIconP};",
            ]);
        }

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

    public function df_render_image()
    {
        if ($this->props['df_cci_use_icon'] === 'on') {
            $defaultIconValue = array(
                "unicode" => "&#x35;",
                "type" => "divi",
                "weight" => 400,
            );
            $iconValue = $this->props['df_cci_font_icon'] ?? $defaultIconValue;

            return sprintf('<div class="df_cci_image_container df_cci_icon_only">
                    <span class="et-pb-icon">%1$s</span>
                </div>', Utils::process_font_icon($iconValue));
        } else if ($this->props['df_cci_image'] !== '') {

            $image_alt = $this->props['df_cci_alt_text'] ?? '';
            $image_url = $this->props['df_cci_image'];

            if(empty($image_url)) {
                return;
            }

            $parent_module = $this->parent;
            if (isset($parent_module->props['use_lightbox_title']) && $parent_module->props['use_lightbox_title'] === 'on') {
                $lightbox_title = $this->props['title'] !== '' ? 'data-sub-html=".df_cc_title"' : '';
            } else {
                $lightbox_title = '';
            }

            // data-src is required by lightgallery; without it loadContent gets null and isVideo(null) throws
            return sprintf(
                '<div class="df_cci_image_container" data-src="%1$s" %3$s>
                    <img class="df_cci_image" alt="%2$s" src="%1$s" />
                </div>',
                esc_attr($image_url),
                esc_attr($image_alt),
                $lightbox_title
            );
        }
    }

    public function df_render_button($parent_attrs)
    {

        $df_btn_icon_yes = $parent_attrs['cc_button']['innerContent']['desktop']['value']['btn_use_icon'] ?? 'off';
        $df_btn_icon     = $parent_attrs['btn_font_icon']['innerContent']['desktop']['value'] ?? '';
        $df_btn_place    = $parent_attrs['cc_button']['innerContent']['desktop']['value']['btn_icon_placement'] ?? 'right';
        $df_btn_show     = $parent_attrs['cc_button']['innerContent']['desktop']['value']['btn_icon_show_hover'] ?? 'off';

        $button_text = $this->props['cc_button_button_text'] ?? '';
        $button_url  = $this->props['cc_button_button_url'] ?? '#';
        $linkTarget  = ($this->props['cc_button_button_url_new_window'] ?? '') === 'on' ? '_blank' : '_self';

        $btn_place = 'df_cci_btn_place_right';
        $btn_show  = 'df_cci_btn_hover_off';
        $btn_icon  = '';

        if (($this->props['btn_use_icon'] ?? 'off') === 'on') {
            $btn_icon  = $this->props['btn_font_icon'] ? Utils::process_font_icon($this->props['btn_font_icon']) : '5';
            $btn_place = ($this->props['btn_icon_placement'] ?? '') === 'left' ? 'df_cci_btn_place_left' : 'df_cci_btn_place_right';
            $btn_show  = ($this->props['btn_icon_show_hover'] ?? '') === 'on' ? 'df_cci_btn_hover_on' : 'df_cci_btn_hover_off';
        } else if ($df_btn_icon_yes === 'on') {
            $btn_icon  = $df_btn_icon ? Utils::process_font_icon($df_btn_icon) : "5";
            $btn_place = $df_btn_place === 'left' ? 'df_cci_btn_place_left' : 'df_cci_btn_place_right';
            $btn_show  = $df_btn_show === 'on' ? 'df_cci_btn_hover_on' : 'df_cci_btn_hover_off';
        }

        if ($btn_icon) {
            $btnIconHas = sprintf(
                '<span class="df_cci_btn_text_icon_wrap %1$s">
                <span class="df_cci_btn_icon %2$s">%3$s</span>
                <span class="df_cci_btn_text">%4$s</span>
            </span>',
                esc_attr($btn_place),
                esc_attr($btn_show),
                $btn_icon,
                esc_html($button_text)
            );
        } else {
            $btnIconHas = esc_html($button_text);
        }

        if ($button_text) {
            return sprintf(
                '<div class="df_cci_button_wrapper">
                <a class="df_cci_button" href="%1$s" target="%2$s">%3$s</a>
            </div>',
                esc_attr($button_url),
                esc_attr($linkTarget),
                $btnIconHas
            );
        } else {
            return '';
        }
    }

    /**
     * Render title from element (cc_title). Supports empty: returns empty string when no title.
     *
     * @param array $attrs Module attrs.
     * @return string
     */
    private function render_title_text($attrs)
    {
        $title_value = $attrs['cc_title']['innerContent']['desktop']['value']['title'] ?? $this->props['title'] ?? '';
        $title_value = is_string($title_value) ? trim($title_value) : '';
        if ($title_value === '') {
            return '';
        }
        $title_tag = $attrs['cc_title']['innerContent']['desktop']['value']['title_tag'] ?? $this->props['title_tag'] ?? 'h4';
        return sprintf(
            '<%2$s class="df_cc_title">%1$s</%2$s>',
            esc_html($title_value),
            esc_attr($title_tag)
        );
    }

    /**
     * Render subtitle from element (cc_subtitle). Supports empty: returns empty string when no subtitle.
     *
     * @param array $attrs Module attrs.
     * @return string
     */
    private function render_subtitle_text($attrs)
    {
        $sub_value = $attrs['cc_subtitle']['innerContent']['desktop']['value']['sub_title'] ?? $this->props['sub_title'] ?? '';
        $sub_value = is_string($sub_value) ? trim($sub_value) : '';
        if ($sub_value === '') {
            return '';
        }
        $subtitle_tag = $attrs['cc_subtitle']['innerContent']['desktop']['value']['subtitle_tag'] ?? $this->props['subtitle_tag'] ?? 'h5';
        return sprintf(
            '<%2$s class="df_cc_subtitle">%1$s</%2$s>',
            esc_html($sub_value),
            esc_attr($subtitle_tag)
        );
    }

    public function render($attrs, $content, $block,  $render_slug)
    {
        // $this->additional_css_styles($render_slug);

        $order_class   = $this->order_class;
        $order_number  = $block->parsed_block['orderIndex'];
        $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $this->parent = $parent;

        $parent_attrs = $parent->attrs ?? [];
        $parentId = $parent->id ?? '';
        $parent_module = $parent;
        // $parent_module = isset(self::get_parent_modules('page')['difl_contentcarousel']) ? self::get_parent_modules('page')['difl_contentcarousel'] : new stdClass();

        $title    = $this->render_title_text($attrs);
        $sub_title = $this->render_subtitle_text($attrs);
        $content = isset($this->props['content']) && $this->props['content'] !== '' ?
            sprintf('<div class="df_cc_content">%1$s</div>', $this->props['content']) : '';

        $parent_use_lightbox_title = $parent_attrs['use_lightbox_title']['innerContent']['desktop']['value'] ?? 'off'; // use_lightbox_title
        if ($parent_use_lightbox_title === 'on') {
            $title_value = $attrs['cc_title']['innerContent']['desktop']['value']['title'] ?? $this->props['title'] ?? '';
            $lightbox_title = (is_string($title_value) && trim($title_value) !== '') ? 'data-sub-html=".df_cc_title"' : '';
        } else {
            $lightbox_title = '';
        }

        $btnText       =  $this->props['cc_button_button_text'] ?? '';
        $linkTarget = 'on' === ($this->props['cc_button_button_url_new_window'] ?? 'off') ? '_blank' : '_self';
        $btnLink    = $this->props['cc_button_button_url'] ?? '#';
        $btnMarkup  = $this->df_render_button($parent_attrs);

        $parent_module_bg_style = ''; // background_enable_mask_style
        return sprintf(
            '%8$s%9$s
          <div class="et_pb_module_inner">
            <div class="df_cci_container" %7$s>
                %10$s%11$s
                %4$s%1$s%2$s%3$s%5$s
            </div>
          </div>',
            $title,
            $sub_title,
            $content,
            $this->df_render_image(),
            $btnMarkup,
            esc_attr($this->props['df_cci_image']),
            $lightbox_title,
            isset($parent_module_bg_style) && $parent_module_bg_style === 'pattern' ? '<span class="et_pb_background_pattern"></span>' : '',
            isset($parent_module_bg_style) && $parent_module_bg_style === 'mask' ? '<span class="et_pb_background_mask"></span>' : '',
            isset($parent_module_bg_style) && $parent_module_bg_style === 'pattern' ? '<span class="et_pb_background_pattern"></span>' : '',
            isset($parent_module_bg_style) && $parent_module_bg_style === 'mask' ? '<span class="et_pb_background_mask"></span>' : ''

        );
    }
}
