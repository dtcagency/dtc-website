<?php

namespace DIFL\Server\Modules\TextHighlighter;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class TextHighlighter implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

    public $slug       = 'difl_text_highlighter';
    public $vb_support = 'on';
	public $icon_path;

    protected $config_file = 'TextHighlighter';

    protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__( 'Text Highlighter', 'divi_flash' );
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/titlt-box.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/texthighlighter' )->customCssFields;
	}

    public function get_hover_values( $attrs, $key ) {
		if ( isset($attrs[$key]['innerContent']['desktop']['hover']) ) {
			return $attrs[$key]['innerContent']['desktop']['hover'];
		}
		return [];
	}


    public function get_common_styles( $args ) {
        $attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';
        $state = $args['state'] ?? [];

        $divider_hover = $this->get_hover_values($attrs, 'divider_settings');
		$divider_width__hover = !empty($divider_hover['divider_width']) ? $divider_hover['divider_width'] : null;

        $styles = [];

        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-text-highlight svg path",
            'attr'     => $attrs['highlighter']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $highlighter_color = $value['attrValue']['highlighter_color'] ?? '#6A33D7';
                return "stroke:". $highlighter_color. ";";
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-text-highlight svg path",
            'attr'     => $attrs['highlighter']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $stroke_width = $value['attrValue']['highlighter_stroke_width'] ?? '';
                return "stroke-width:". $stroke_width. ";";
            },
        ]);

        // $common_styles[] = CommonStyle::style([
        //     'selector' => "{$order_class} .df-text-highlight svg",
        //     'attr'     => $attrs['infix'] ?? null,
        //     'declarationFunction' => function ( $value ) {
        //         // return "transform: translateX(calc(-100% + 10px + 0px)) scale(". $this->props['highlighter_size']. ");";
        //     }
        // ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-text-highlight svg",
            'attr'     => $attrs['highlighter']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $h_size = $value['attrValue']['highlighter_size'] ?? '1';
                return "transform: translateX(calc(-100% + 10px + 0px)) scale(".$h_size.");";
            }
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-text-highlight svg",
            'attr'     => $attrs['highlighter']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $opacity = $value['attrValue']['highlighter_opacity'] ?? '1';
                return "opacity: ". $opacity .";"; 
            }
        ]);

        // While .active, hide SVG until JS removes .active after anim_delay (avoids stroke visible during delay).
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-texthighlighter-container.active .df-text-highlight svg",
            'attr'     => $attrs['highlighter']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return 'opacity: 0 !important;';
            },
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-texthighlighter-container.active .df-text-highlight .df-svg-wrapper svg",
            'attr'     => $attrs['highlighter']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return 'opacity: 0 !important;';
            },
        ]);

        if( $this->props['highlighter_position'] === 'above' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-text-highlight svg",
                'attr'     => $attrs['highlighter']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "z-index: 1 !important;";
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-texthighlighter-container .df-heading > span",
                'attr'     => $attrs['highlighter']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "z-index: 0 !important;";
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-text-highlight svg",
            'attr'     => $attrs['highlighter']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $margin_left = $value['attrValue']['highlighter_horizontal_position'] ?? '0px';
                return "margin-left: ". $margin_left .";";
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-text-highlight svg",
            'attr'     => $attrs['highlighter']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $top = $value['attrValue']['highlighter_vertical_position'] ?? '0px';
                return "top: ". $top .";";
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-heading-divider .df-divider-line::before",
            'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return "border-top-style:". $this->props['divider_style'] .";";
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df-heading-divider .df-divider-line::before",
            'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
            'declarationFunction' => function () {
                return "border-top-color:". $this->props['divider_color'] .";";
            }
        ]);

        if($state === 'value'){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider .df-divider-line",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']['divider_height'])){
                        $_height = $value['attrValue']['divider_height'];
                        $_height_value = (int) filter_var($_height, FILTER_SANITIZE_NUMBER_INT); 
                        $_position = $_height_value / 2;
                        return "top:calc(50% - {$_position}px);";
                    }
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider .df-divider-line::before",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']['divider_height'])){   
                        $border_thickness = $value['attrValue']['divider_height'];
                        return "border-top-width: ". $border_thickness .";";
                    }
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider .df-divider-line",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']['divider_height'])){   
                        $_height = $value['attrValue']['divider_height'];
                        return "height: ". $_height .";";
                    }
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if( isset($value['attrValue']['divider_width']) ) {
                        $divider_width = $value['attrValue']['divider_width'];
                        return "max-width: ".$divider_width.";";
                    }
                }
            ]);
        }

        if( $divider_width__hover !== null ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class}:hover .df-heading-divider",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) {
                    $divider_width = $props['attr']['desktop']['hover']['divider_width'];
                    return 'max-width: '.$divider_width.';';
                }
            ]);
        }

        if( $this->props['divider_alignment'] === 'center' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "margin: 0 auto;";
                }
            ]);
        }
        if( $this->props['divider_alignment'] === 'right' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "margin: 0 0 0 auto;";
                }
            ]);
        }
        if( $this->props['use_divider_icon'] !== 'on' && $this->props['use_divider_image'] !== 'on' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider::before",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "position: relative;";
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
            'selector'            => "{$order_class} .df-heading-divider .et-pb-icon",
            'attr'                => $attrs['divider_settings']['innerContent'] ?? null,
            'declarationFunction' => function () {
                $icon_style = $this->props['divider_icon'] ?? null;
                return self::get_font_style($icon_style);
            },
        ]);
        if( $this->props['use_divider_icon_circle'] === 'on' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider .et-pb-icon",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "border-radius: 50%;";
                }
            ]);
        }
        if( $this->props['use_divider_image_circle'] === 'on' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider img",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "border-radius: 50%;";
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider .df-divider-line:before, {$order_class} .df-heading-divider .df-divider-line",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $border_radius = $value['attrValue']['divider_border_radius'] ?? '0';
                    return "border-radius: ".$border_radius.";";
                }
            ]);
        
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider .et-pb-icon",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if( isset($value['attrValue']['dvr_icon_font_size']) ){
                        return "font-size: ".$value['attrValue']['dvr_icon_font_size'].";";
                    }
                }
            ]);

        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider .et-pb-icon",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if( isset($value['attrValue']['divider_icon_color']) ){ 
                        return "color: ".$value['attrValue']['divider_icon_color'].";";
                    }
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider .et-pb-icon",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if( isset($value['attrValue']['divider_icon_bgcolor']) ){
                        return "background-color: ".$value['attrValue']['divider_icon_bgcolor'].";";
                    }
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider img.divider-image",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $image_bgColor = $value['attrValue']['divider_image_bgcolor'] ?? 'transparent';
                    return "background-color: ".$image_bgColor.";";
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "text-align: ".$this->props['divider_icon_alignment'].";";
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider img",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $divider_image_width = $value['attrValue']['divider_image_width'] ?? '100px';
                    return "max-width: ".$divider_image_width.";";
                }
            ]);
        if($this->props['divider_image_alignment'] && $this->props['use_divider_image'] === 'on'){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading-divider",
                'attr'     => $attrs['divider_settings']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return "text-align: ".$this->props['divider_image_alignment'].";";
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .prefix",
                'attr'     => $attrs['prefix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $p_displayType = $value['attrValue']['title_prefix_block'] ?? 'inline-block';
                    return "display: ". $p_displayType .";";
                }
            ]);
        if($this->props['title_prefix_block'] === 'block'){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .prefix",
                'attr'     => $attrs['prefix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $width = $value['attrValue']['df_prefix_width'] ?? '100%';
                    return "max-width: ".$width.";";
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .prefix",
                'attr'     => $attrs['prefix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']['df_prefix_alignment'])){
                        switch($value['attrValue']['df_prefix_alignment']){
                            case 'left':
                                return "margin-right: auto; margin-left: 0;";
                            case 'center':
                                return "margin-left: auto; margin-right: auto;";
                            case 'right':
                                return "margin-left: auto; margin-right: 0;";
                        }
                    }
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .infix",
                'attr'     => $attrs['infix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $i_displayType = $value['attrValue']['title_infix_block'] ?? 'inline-block';
                    return "display: ".$i_displayType.";";
                }
            ]);
        if($this->props['title_infix_block'] === 'block'){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .infix",
                'attr'     => $attrs['infix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $width = $value['attrValue']['df_infix_width'] ?? '100%';
                    return "max-width: ".$width.";";
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .infix",
                'attr'     => $attrs['infix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']['df_infix_alignment'])){
                        switch($value['attrValue']['df_infix_alignment']){
                            case 'left':
                                return "margin-right: auto; margin-left: 0;";
                            case 'center':
                                return "margin-left: auto; margin-right: auto;";
                            case 'right':
                                return "margin-left: auto; margin-right: 0;";
                        }
                    }
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .suffix",
                'attr'     => $attrs['suffix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $s_displayType = $value['attrValue']['title_suffix_block'] ?? 'inline-block';
                    return "display: ".$s_displayType.";";
                }
            ]);
        
        if($this->props['title_suffix_block'] === 'block'){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .suffix",
                'attr'     => $attrs['suffix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $width = $value['attrValue']['df_suffix_width'] ?? '100%';
                    return "max-width: ".$width.";";
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df-heading .suffix",
                'attr'     => $attrs['suffix_text']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']['df_suffix_alignment'])){
                        switch($value['attrValue']['df_suffix_alignment']){
                            case 'left':
                                return "margin-right: auto; margin-left: 0;";
                            case 'center':
                                return "margin-left: auto; margin-right: auto;";
                            case 'right':
                                return "margin-left: auto; margin-right: 0;";
                        }
                    }
                }
            ]);
        }
        
        if($this->props['df_prefix_enable_clip'] === 'on'){
            if($this->props['df_prefix_enable_bg_clip'] === 'on'){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .prefix",
                    'attr'     => $attrs['prefix_text']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return "-webkit-background-clip: text;";
                    }
                ]);
            }
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .prefix",
                    'attr'     => $attrs['prefix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        if(isset($value['attrValue']['df_prefix_fill_color'])){
                            return "-webkit-text-fill-color:".$value['attrValue']['df_prefix_fill_color'].";";
                        }
                        return "-webkit-text-fill-color:rgba(255,255,255,0);";
                    }
                ]);
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .prefix",
                    'attr'     => $attrs['prefix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        if(isset($value['attrValue']['df_prefix_stroke_color'])){
                            return "-webkit-text-stroke-color:".$value['attrValue']['df_prefix_stroke_color'].";";
                        }
                        // return "-webkit-text-stroke-color:rgba(255,255,255,0);";
                    }
                ]);
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .prefix",
                    'attr'     => $attrs['prefix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        if(isset($value['attrValue']['df_prefix_stroke_width'])){
                            return "-webkit-text-stroke-width:".$value['attrValue']['df_prefix_stroke_width'].";";
                        }
                        return "-webkit-text-stroke-width:1px;";
                    }
                ]);
        }

        if($this->props['df_infix_enable_clip'] === 'on'){
            if($this->props['df_infix_enable_bg_clip'] === 'on'){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .infix",
                    'attr'     => $attrs['infix_text']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return "-webkit-background-clip: text;";
                    }
                ]);
            }
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .infix",
                    'attr'     => $attrs['infix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        if(isset($value['attrValue']['df_infix_fill_color'])){
                            return "-webkit-text-fill-color:".$value['attrValue']['df_infix_fill_color'].";";
                        }
                        return "-webkit-text-fill-color:rgba(255,255,255,0);";
                    }
                ]);
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .infix",
                    'attr'     => $attrs['infix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        return "-webkit-text-stroke-color:".$this->props['df_infix_stroke_color'].";";
                    }
                ]);
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .infix",
                    'attr'     => $attrs['infix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        if(isset($value['attrValue']['df_infix_stroke_width'])){
                            return "-webkit-text-stroke-width:".$value['attrValue']['df_infix_stroke_width'].";";
                        }
                        return "-webkit-text-stroke-width:1px;";
                    }
                ]);
        }

        if($this->props['df_suffix_enable_clip'] === 'on'){
            if($this->props['df_suffix_enable_bg_clip'] === 'on'){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .suffix",
                    'attr'     => $attrs['suffix_text']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        return "-webkit-background-clip: text;";
                    }
                ]);
            }  
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .suffix",
                    'attr'     => $attrs['suffix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        if(isset($value['attrValue']['df_suffix_fill_color'])){
                            return "-webkit-text-fill-color:".$value['attrValue']['df_suffix_fill_color'].";";
                        }
                        return "-webkit-text-fill-color:rgba(255,255,255,0);";
                    }
                ]);
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .suffix",
                    'attr'     => $attrs['suffix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        $stroke_color = $value['attrValue']['df_suffix_stroke_color'] ?? '';
                        return "-webkit-text-stroke-color:".$stroke_color.";";
                    }
                ]);
            $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df-heading .suffix",
                    'attr'     => $attrs['suffix_text']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        if(isset($value['attrValue']['df_suffix_stroke_width'])){
                            return "-webkit-text-stroke-width:".$value['attrValue']['df_suffix_stroke_width'].";";
                        }
                        return "-webkit-text-stroke-width:1px;";
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
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} span.prefix", 
                'attr' => $attrs['prefix_border']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} span.infix", 
                'attr' => $attrs['infix_border']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} span.suffix", 
                'attr' => $attrs['suffix_border']['decoration']['border'] ?? null, 
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
  
    public function render_divider( $position, $value ) {

		$divider_icon = '';

		if ( 'on' === $this->props['use_divider_icon'] ) {
			$divider_icon = ! empty( $this->props['divider_icon'] ) ?
				sprintf(
					'<span class="et-pb-icon">%1$s</span>',
					html_entity_decode( Utils::process_font_icon( $this->props['divider_icon'] ) )
				) :
					'<span class="et-pb-icon">1</span>';
		}
		if ( 'on' === $this->props['use_divider_image'] ) {
			$image_alt    = $this->props['divider_image_alt_text'] ?? '';
			$divider_icon = ! empty( $this->props['divider_image'] ) ?
				sprintf(
					'<img alt="%2$s" src="%1$s" class="divider-image"/>',
					$this->props['divider_image'],
					$image_alt
				) : '';
		}
        
		if ( $value === $position ) {
			return 'on' === $this->props['use_divider'] ?
				sprintf( '<div class="df-heading-divider"><div class="df-divider-line"></div>%1$s</div>', $divider_icon ) : '';
		}
	}

    public function highlighter_svg() {
		$jsonString = file_get_contents( DIFL_ADMIN_DIR_PATH . "assets/svg/textHighlighter.json" );
		$svgArray   = json_decode( $jsonString, true );

		if ( empty( $this->props['highlighter_type'] ) ) {
			return $svgArray['TextHighlighterSVG']['underline'];
		}
		return $svgArray['TextHighlighterSVG'][ $this->props['highlighter_type'] ];
	}

    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {

        // $this->handle_fa_icon();
		wp_enqueue_script( 'df-vivus-svg' );
		wp_enqueue_script( 'df-text-highlighter' );

		$heading_classes  = '';
		$dual_text_title  = '';

		$divider_position = $this->props['divider_position'] ?? 'bottom';

		// $title_level  = $this->props['title_level'];
		$title_level  = $this->props['title'] ?? '';
		$title_prefix = ! empty( $this->props['title_prefix'] ) ?
			sprintf( '<span class="prefix">%1$s</span>', $this->props['title_prefix'] ) : '';

		$title_infix = ! empty( $this->props['title_infix'] ) ?
			sprintf(
				'<span class="infix df-text-highlight">%1$s %2$s</span>',
				$this->props['title_infix'],
				$this->highlighter_svg() // $highlighter_svg
			) : '';

		$title_suffix = ! empty( $this->props['title_suffix'] ) ?
			sprintf( '<span class="suffix">%1$s</span>', $this->props['title_suffix'] ) : '';

		$title = sprintf( '%1$s %2$s %3$s', $title_prefix, $title_infix, $title_suffix );

		// $this->additional_css_styles( $render_slug );

		if ( $this->props['use_dual_text'] === 'on' ) {
			$dual_title = sprintf( '%1$s %2$s %3$s', $this->props['title_prefix'], $this->props['title_infix'], $this->props['title_suffix'] );

			if ( $this->props['use_dual_text_custom'] !== 'on' ) {
				$dual_text_title = sprintf(
					'<div class="df-heading-dual_text" data-title="%1$s"></div>',
					wp_strip_all_tags( trim( $dual_title ) )
				);
			} else {
				$dual_text_title = sprintf(
					'<div class="df-heading-dual_text" data-title="%1$s"></div>',
					wp_strip_all_tags( $this->props['custom_text_input'] )
				);
			}

			$heading_classes .= ' has-dual-text';
		}

		$data = [
			'animation'          => $this->props['enable_animation'],
			'animationStart'     => $this->props['anim_start'],
			'viewportPosition'   => $this->props['anim_start_viewport'],
			'type'               => $this->props['highlighter_type'],
			'animTimingFunction' => $this->props['anim_easing'],
			'duration'           => (float) $this->props['anim_duration'],
			'delay'              => (float) $this->props['anim_delay'],
			'loop'               => $this->props['enable_loop'],
			'iteration'          => (int) $this->props['anim_iteration'],
			'iterationGap'       => (int) $this->props['anim_iteration_gap'],
		];

		$data_svg = [
			'isGradient'              => $this->props['enable_gradient_color'],
			'colorStart'              => $this->props['gradient_color_start'],
			'colorEnd'                => $this->props['gradient_color_end'],
			'gradientType'            => $this->props['gradient_type'],
			'gradientDirection'       => $this->props['gradient_direction'],
			'gradientDirectionRadial' => $this->props['gradient_direction_radial'],
			'startPosition'           => $this->props['gradient_start_position'],
			'endPosition'             => $this->props['gradient_end_position'],
		];

        // echo '<pre>';
        // print_r($data_svg);

        return sprintf(
			'<div class="df-texthighlighter-container %6$s active" data-svg=\'%8$s\' data-settings=\'%7$s\'>
                %5$s
                %3$s
                <%2$s class="df-heading">%1$s</%2$s>
                %4$s
            </div>',
			$title,
			esc_html($title_level ),
			$this->render_divider( 'top', $divider_position ),
			$this->render_divider( 'bottom', $divider_position ),
			$dual_text_title,
			$heading_classes,
			wp_json_encode( $data ),
			wp_json_encode( $data_svg )
		);
    }
}