<?php

namespace DIFL\Server\Modules\HoverBox;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class HoverBox implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

    public $slug       = 'difl_hoverbox';
    public $vb_support = 'on';
	public $icon_path;

    protected $config_file = 'HoverBox';

    protected $module_credits = array (
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__('Hover Box', 'divi_flash');
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/hover-box.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/hoverbox' )->customCssFields;
	}

    public function get_common_styles( $args ) {
        $attrs       = $this->get_merged_attrs( $args );
		$attrs       = self::build_attr_for_styles( $attrs );
		$attrs       = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';

        $styles = [];

        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_hb_button_wrapper",
            'attr'     => $attrs['button_design']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return "text-align:". $this->props['buttonAlign']. ";";
            }
        ]);

        if( $this->props['background_scale'] === 'on' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class}:hover .df_hb_background",
                'attr'     => $attrs['hover_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "transform: scale(1.06);";
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class}",
                'attr'     => $attrs['hover_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "overflow: hidden !important;";
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_hb_inner.df_hb_inner",
            'attr'     => $attrs['vertical_align']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return "justify-content:". $this->props['verticalAlign']. ";";
            }
        ]);
        if( $this->props['anim_direction'] ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_hb_def_content.df_hb_def_content",
                'attr'     => $attrs['hover_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "transform: ". $this->df_transform_values($this->props['anim_direction'], 'default'). ";";
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_hb_def_content_hover.df_hb_def_content_hover",
                'attr'     => $attrs['hover_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "transform: ". $this->df_transform_values($this->props['anim_direction'], 'hover'). ";";
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class}:hover .df_hb_def_content.df_hb_def_content",
                'attr'     => $attrs['hover_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "transform: ". $this->df_transform_values($this->props['anim_direction'], 'hover'). ";";
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class}:hover .df_hb_def_content_hover.df_hb_def_content_hover",
                'attr'     => $attrs['hover_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "transform: ". $this->df_transform_values($this->props['anim_direction'], 'default'). ";";
                }
            ]);
        }

        {/**
        * Support for border-radius
        * In Divi-4, if user change radius property, an additional css autometically added [overflow:hidden] to that selector
        * But in 5??  To resolve this issues. These are the fields which will generate overflow hidden
        */}
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class}", 
                'attr' => $attrs['module']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if($props['attrValue']['radius']){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .title", 
                'attr' => $attrs['title']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .subtitle", 
                'attr' => $attrs['sub_title']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .content", 
                'attr' => $attrs['content_design']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .df_hb_button", 
                'attr' => $attrs['button_design']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
 
        $common_styles[] = CssStyle::style( [
            'selector'  => $order_class,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );
        
        foreach ( $common_styles as $common_style ) {
            if ( empty( $common_style ) ) {
                continue;
            }
            foreach ( $common_style as $style ) {
                $styles[] = $style;
            }
        }

        return $styles ?? [];
    }
 
     /**
     * Get transform values
     * 
     * @param String $key
     * @param String | State
     */
    public function df_transform_values($key = 'bottom', $state = 'default') {
        $transform_values = array (
            'top'           => [
                'default'   => 'translateY(0px)',
                'hover'     => 'translateY(-60px)'
            ],
            'bottom'        => [
                'default'   => 'translateY(0px)',
                'hover'     => 'translateY(60px)'
            ],
            'left'          => [
                'default'   => 'translateX(0px)',
                'hover'     => 'translateX(-60px)'
            ],
            'right'         => [
                'default'   => 'translateX(0px)',
                'hover'     => 'translateX(60px)'
            ],
            'center'        => [
                'default'   => 'scale(1)',
                'hover'     => 'scale(0)'
            ],
            'top_right'     => [
                'default'   => 'translateX(0px) translateY(0px)',
                'hover'     => 'translateX(50px) translateY(-50px)'
            ],
            'top_left'      => [
                'default'   => 'translateX(0px) translateY(0px)',
                'hover'     => 'translateX(-50px) translateY(-50px)'
            ],
            'bottom_right'  => [
                'default'   => 'translateX(0px) translateY(0px)',
                'hover'     => 'translateX(50px) translateY(50px)'
            ],
            'bottom_left'   => [
                'default'   => 'translateX(0px) translateY(0px)',
                'hover'     => 'translateX(-50px) translateY(50px)'
            ]
        );
        return $transform_values[$key][$state];
    }

    /**
     * Render button HTML markup
     * 
     * @param String $key
     * @return String HTML markup of the button
     */
    public function df_render_button($key) {
        $text = isset($this->props[$key . '_button_text']) ? $this->props[$key . '_button_text'] : '';
        $url = isset($this->props[$key . '_button_url']) ? $this->props[$key . '_button_url'] : '';
        $target = $this->props['hb_btn_button_url_new_window'];

        if($text !== '' || $url !== '') {
            return sprintf('<div class="df_hb_button_wrapper">
                <a class="df_hb_button" href="%1$s" target="%3$s">%2$s</a>
            </div>', esc_attr($url), esc_html($text), $target);
        } else { return ''; }
    }

    /**
     * Render HTML markup
     * 
     * @param Null
     * @param String | HTML markup
     */
    public function df_hb_content() {
        $content_default = '';
        $content_hover = '';
        $title = $this->props['title'] !== '' ?
            sprintf('<%2$s class="title">%1$s</%2$s>', $this->props['title'], $this->props['titleTag']) : '';
        $sub_title = $this->props['subtitle'] !== '' ?
            sprintf('<%2$s class="subtitle">%1$s</%2$s>', $this->props['subtitle'], $this->props['sub_titleTag']) : '';
        $content = $this->props['content'] !== '' ?
            sprintf('<div class="content">%1$s</div>', $this->props['content']) : '';

        if ($this->props['title_on_hover'] === 'on') {
            $content_hover .= $title;
        } else {
            $content_default .= $title;
        }

        if ($this->props['subtitle_on_hover'] === 'on') {
            $content_hover .= $sub_title;
        } else {
            $content_default .= $sub_title;
        }

        if ($this->props['content_on_hover'] === 'on') {
            $content_hover .= $content;
        } else {
            $content_default .= $content;
        }

        if ($this->props['button_on_hover'] === 'on') {
            $content_hover .= $this->df_render_button('hb_btn');
        } else {
            $content_default .= $this->df_render_button('hb_btn');
        }

        $content_default_container = $content_default !== '' ?
            sprintf('<div class="df_hb_def_content">%1$s</div>', $content_default) : '';
        $content_hover_container = $content_hover !== '' ?
            sprintf('<div class="df_hb_def_content_hover">%1$s</div>', $content_hover) : '';

        return $content_default_container . $content_hover_container;
    }
  
    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {
        return sprintf('
            <div class="difl_hoverbox_editor_wrap">
                <div class="df_hb_container">
                    <div class="df_hb_background"></div>
                    <div class="df_hb_inner">
                        %1$s
                    </div>
                </div>
            </div>',
            $this->df_hb_content()
        );
    }
}