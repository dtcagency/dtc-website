<?php

namespace DIFL\Server\Modules\TestimonialCarousel;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class TestimonialCarousel implements DependencyInterface{

    use ModuleUtil;
    use HasProps;
 
    protected $config_file = 'TestimonialCarousel';
    public $slug       = 'difl_testimonialcarousel';
    public $vb_support = 'on';
    public $child_slug = 'difl_testimonialcarouselitem';
	public $icon_path;

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/testimonialcarousel' )->customCssFields;
	}

    public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';
        $state      = $args['state'];

        $styles = [];

        $arrows_hover = $this->get_hover_values($attrs, 'arrows_design');
		$arrow_color__hover = !empty($arrows_hover['arrow_color']) ? $arrows_hover['arrow_color'] : null;
		$arrow_bg_color__hover = !empty($arrows_hover['arrow_background']) ? $arrows_hover['arrow_background'] : null;
		$arrow_opacity__hover = !empty($arrows_hover['arrow_opacity']) ? $arrows_hover['arrow_opacity'] : null;
		$arrow_disable_opcaity__hover = !empty($arrows_hover['arrow_opacity_disable']) ? $arrows_hover['arrow_opacity_disable'] : null;

        $quote_icon_hover = $this->get_hover_values($attrs, 'quote_icon_design');
        $quote_icon_color__hover = !empty($quote_icon_hover['quote_icon_color']) ? $quote_icon_hover['quote_icon_color'] : null;
        $quote_icon_bgcolor__hover = !empty($quote_icon_hover['quote_icon_bgcolor']) ? $quote_icon_hover['quote_icon_bgcolor'] : null;

        // CoverFlow Settings Shadow
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .swiper-container-3d .swiper-slide-shadow-left",
            'attr'     => $attrs['coverflow_settings_content']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $coveflow_color_dark = $value['attrValue']['coveflow_color_dark'] ?? '';
                $coveflow_color_light = $value['attrValue']['coveflow_color_light'] ?? '';
                return "background-image: linear-gradient(to left, ". $coveflow_color_dark .",". $coveflow_color_light .");";
            },
        ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .swiper-container-3d .swiper-slide-shadow-right",
            'attr'     => $attrs['coverflow_settings_content']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ){
                $coveflow_color_dark = $value['attrValue']['coveflow_color_dark'] ?? '';
                $coveflow_color_light = $value['attrValue']['coveflow_color_light'] ?? '';
                return "background-image: linear-gradient(to right, " . $coveflow_color_dark .",". $coveflow_color_light .");";
            },
        ]);
        // Items Order [has no responsive and hover state]
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_quote_image",
            'attr'     => $attrs['items_order_content']['innerContent'] ?? null,
            'declarationFunction' => function () {
                $quote_order  = $this->props['quote_order'] ?? '9';
                return "order:". $quote_order .";";
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_company_logo",
            'attr'     => $attrs['items_order_content']['innerContent'] ?? null,
            'declarationFunction' => function () {
                $logo_order  = $this->props['logo_order'] ?? '9';
                return "order:". $logo_order .";";
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_content",
            'attr'     => $attrs['items_order_content']['innerContent'] ?? null,
            'declarationFunction' => function () {
                $text_order  = $this->props['text_order'] ?? '9';
                return "order:". $text_order .";";
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_author_box",
            'attr'     => $attrs['items_order_content']['innerContent'] ?? null,
            'declarationFunction' => function () {
                $author_order  = $this->props['author_order'] ?? '9';
                return "order:". $author_order .";";
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_ratings",
            'attr'     => $attrs['items_order_content']['innerContent'] ?? null,
            'declarationFunction' => function ( ) {
                $rating_order  = $this->props['rating_order'] ?? '9';
                return "order:". $rating_order .";";
            }
        ]);

        if( $this->props['author_image_position'] === 'top' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_author_box",
                'attr'     => $attrs['author_box_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'flex-direction: column;';
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_author_image",
                'attr'     => $attrs['author_box_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'margin-left:0;margin-right:0;';
                }
            ]);
        }
        if( $this->props['author_image_position'] === 'bottom' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_author_box",
                'attr'     => $attrs['author_box_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'flex-direction: column;';
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_author_image",
                'attr'     => $attrs['author_box_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'order: 2; margin-left:0;margin-right:0;';
                }
            ]);
        }
        if( $this->props['author_image_position'] === 'right' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_author_image",
                'attr'     => $attrs['author_box_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'order: 2;margin-left:0; margin-right:10px;';
                }
            ]);
        }
        if( $this->props['author_alignhr'] ){
            if($this->props['author_image_position'] === 'top' || $this->props['author_image_position'] === 'bottom'){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_author_box",
                    'attr'     => $attrs['author_box_settings']['innerContent'] ?? null,
                    'declarationFunction' => function ( ) {
                        return 'align-items: '.$this->props['author_alignhr'].';';
                    }
                ]);
            }else{
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_author_box",
                    'attr'     => $attrs['author_box_settings']['innerContent'] ?? null,
                    'declarationFunction' => function ( ) {
                        return 'justify-content: '.$this->props['author_alignhr'].';';
                    }
                ]);
            }
        }
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_author_info",
            'attr'     => $attrs['author_box_settings']['innerContent'] ?? null,
            'declarationFunction' => function ( ) {
                return 'text-align: '.$this->props['info_align'].';';
            }
        ]);
        if( $this->props['large_active_dot'] === 'on' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .swiper-pagination .swiper-pagination-bullet-active",
                'attr'     => $attrs['dots_design']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'width: 40px; border-radius: 20px;';
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .swiper-pagination span",
                'attr'     => $attrs['dots_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $dots_color = $value['attrValue']['dots_color'] ?? '';
                    return $dots_color ? 'background: ' . $dots_color . ';' : '';
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .swiper-pagination span.swiper-pagination-bullet-active",
                'attr'     => $attrs['dots_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $active_color = $value['attrValue']['active_dots_color'] ?? '';
                    return $active_color ? 'background: ' . $active_color . ';' : '';
                }

            ]);

        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .swiper-pagination",
            'attr'     => $attrs['dots_design']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $align = $value['attrValue']['dots_align'] ?? null;
                return $align ? "text-align: {$align};" : '';
            }
        ]);

        if( $this->props['arrow'] === 'on' ) {
           
            if( $this->props['arrow_circle'] === 'on' ) {
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_arrows > div",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( ) {
                        return 'border-radius: 50%;';
                    }
                ]);
            }

            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_arrows",
                'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $arrow_position = $value['attrValue']['arrow_position'] ?? '';
                    if( $arrow_position !== '' ){
                        return $this->df_arrow_pos_styles( $arrow_position );
                    }
                }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_arrows",
                'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $align = $value['attrValue']["arrow_align"] ?? '';
                    if( $align !== '' ){
                        return 'justify-content: ' . $align . ';';
                    }
                }
            ]);

            // Arrow Style Settings
            if( $state === 'value' ){
                // arrow icon color
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_arrows div:after",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        $color = $value['attrValue']['arrow_color'] ?? null;
                        return $color ? "color: {$color};" : '';
                    }
                ]);
                // arrow background and opacity
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_arrows div",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        $bg      = $value['attrValue']['arrow_background'] ?? null;
                        $opacity = $value['attrValue']['arrow_opacity'] ?? '1';

                        $css = '';
                        if ($bg) $css .= "background-color: {$bg};";
                        $css .= "opacity: {$opacity};";
                        return $css;
                    }
                ]);
                // arrow disable opacity
                if( $this->props["loop"] === 'off' ){
                    $common_styles[] = CommonStyle::style([
                        'selector' => "{$order_class} .df_tc_arrows div.swiper-button-disabled",
                        'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                        'declarationFunction' => function ( $value ) {
                            $opacity = $value['attrValue']['arrow_opacity_disable'] ?? '0.5';
                            return "opacity: {$opacity} !important;";
                        }
                    ]);
                }
            }else{
                
            }
            
            if( $arrow_bg_color__hover !== null ){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class}:hover .df_tc_arrows div",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) {
                        if(isset($props['attr']['desktop']['hover']['arrow_background'])){
                            return 'background-color: '.$props['attr']['desktop']['hover']['arrow_background'].' !important;';
                        }
                    }
                ]);
            }
            if( $arrow_color__hover !== null ){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class}:hover .df_tc_arrows div:after",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) {
                        $color = $props['attr']['desktop']['hover']['arrow_color'];
                        return 'color: '.$color.' !important;';
                    }
                ]);
            }
            if( $arrow_opacity__hover !== null ){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class}:hover .df_tc_arrows.df_tc_arrows div",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) {
                        if(isset($props['attr']['desktop']['hover']['arrow_opacity'])){
                            return 'opacity: '.$props['attr']['desktop']['hover']['arrow_opacity'].' !important;';
                        }
                    }
                ]);
            }
            if( $arrow_disable_opcaity__hover !== null ){
                if( $this->props["loop"] !== 'on' ){
                    $common_styles[] = CommonStyle::style([
                        'selector' => "{$order_class}:hover .df_tc_arrows div.swiper-button-disabled",
                        'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                        'declarationFunction' => function ( $props ) {
                            if(isset($props['attr']['desktop']['hover']['arrow_opacity_disable'])){
                                return 'opacity: '.$props['attr']['desktop']['hover']['arrow_opacity_disable'].' !important;';
                            }
                        }
                    ]);
                }
            }


            if( $this->props["arrow_prev_icon_use_icon"] === 'on' ){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_arrows div.swiper-button-prev:after",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( ) {
                        return self::get_font_style($this->props["arrow_prev_icon_font_icon"]);
                    }
                ]);
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_arrows div.swiper-button-prev:after",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        $size = $value['attrValue']['arrow_prev_icon_icon_size'] ?? null;
                        return $size ? "font-size: {$size};" : '';
                    }
                ]);
            }
            if( $this->props["arrow_next_icon_use_icon"] === 'on' ){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_arrows div.swiper-button-next:after",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( ) {
                        return self::get_font_style($this->props["arrow_next_icon_font_icon"]);
                    }
                ]);
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_arrows div.swiper-button-next:after",
                    'attr'     => $attrs['arrows_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        $size = $value['attrValue']['arrow_next_icon_icon_size'] ?? null;
                        return $size ? "font-size: {$size};" : '';
                    }
                ]);
            }
        }


        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class}  .df_tc_ratings span.et-pb-icon.df_rating_icon_fill",
            'attr'     => $attrs['ratings_design']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $color = $value['attrValue']['rating_color'] ?? null;
                return 'color: '.$color.';';
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_ratings",
            'attr'     => $attrs['ratings_design']['innerContent'] ?? null,
            'declarationFunction' => function ( ) {
                return 'justify-content: '.$this->props["rating_align"].';';
            }
        ]);
        if( $this->props["rating_size"] ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_ratings span.et-pb-icon",
                'attr'     => $attrs['ratings_design']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'font-size: '.$this->props["rating_size"].';';
                }
            ]);
        }
        if( $this->props["rating_space"] ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_ratings span",
                'attr'     => $attrs['ratings_design']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'margin-right: '.$this->props["rating_space"].';';
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_ratings span:last-child",
                'attr'     => $attrs['ratings_design']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'margin-right: 0;';
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_company_logo",
                'attr'     => $attrs['brand_logo_design']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'text-align: '.$this->props["brand_align"].';';
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_company_logo img",
                'attr'     => $attrs['brand_logo_design']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'max-width: '.$this->props["brand_width"].';';
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_author_image",
                'attr'     => $attrs['author_image_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $width = $value['attrValue']['author_image_width'] ?? null;
                    return $width ? "max-width: {$width};" : '';
                }
            ]);

        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_quote_image",
                'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $alignment = $value['attrValue']['qoute_align'] ?? null;
                    return 'text-align: '.$alignment.';';
                }
            ]);

        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_quote_icon",
                'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $size = $value['attrValue']['quote_icon_size'] ?? null;
                    return $size ? "font-size: {$size};" : '';
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_quote_image img",
                'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $max = $value['attrValue']['quote_image_max'] ?? null;
                    return $max ? "width: {$max}; max-width: {$max};" : '';
                }
            ]);
        $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_quote_image, {$order_class} .df_tc_quote_icon",
                'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'z-index: '.$this->props["quote_z_index"].';';
                }
            ]);

        if( $state === 'value' ) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tci_container .df_tc_quote_icon",
                'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $color = $value['attrValue']['quote_icon_color'] ?? "#007AFF";
                    return "color: {$color};";
                }

            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tci_container .df_tc_quote_icon",
                'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']['quote_icon_bgcolor'])){
                        return 'background-color: '.$value['attrValue']['quote_icon_bgcolor'].';';
                    }
                }
            ]);
        }

        if( $quote_icon_color__hover !== '' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .difl_testimonialcarouselitem:hover .df_tci_container .df_tc_quote_icon",
                'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) {
                    if(isset($props['attr']['desktop']['hover']['quote_icon_color'])){
                        return 'color: '.$props['attr']['desktop']['hover']['quote_icon_color'].' !important;';
                    }
                }
            ]);
        }
        if( $quote_icon_bgcolor__hover !== '' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .difl_testimonialcarouselitem:hover .df_tci_container .df_tc_quote_icon",
                'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) {
                    if(isset($props['attr']['desktop']['hover']['quote_icon_bgcolor'])){
                        return 'background-color: '.$props['attr']['desktop']['hover']['quote_icon_bgcolor'].' !important;';
                    }
                }
            ]);
        }

        if( $this->props["equal_height"] === 'on' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .difl_testimonialcarouselitem.swiper-slide",
                'attr'     => $attrs['carousel_settings_content']['innerContent'] ?? null,
                'declarationFunction' => function ( ) {
                    return 'align-self: auto !important;';
                }
            ]);
        }
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_quote_icon",
            'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
            'declarationFunction' => function ( ) {
                return 'opacity: '.$this->props["quote_opacity"].';';
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_quote_image",
            'attr'     => $attrs['quote_icon_design']['innerContent'] ?? null,
            'declarationFunction' => function ( ) {
                return 'opacity: '.$this->props["quote_opacity"].';';
            }
        ]);

        {/**
        * Support for border-radius
        * In Divi-4, if user change radius property, an additional css autometically added [overflow:hidden] to that selector
        * But in 5??  To resolve this issues. These are the fields which will generate overflow hidden
        */}
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_testimonialcarouselitem .et_pb_module_inner", 
                'attr' => $attrs['module']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_testimonialcarouselitem .df_tc_company_logo img", 
                'attr' => $attrs['brand_logo_design']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_testimonialcarouselitem .df_tc_author_image img", 
                'attr' => $attrs['author_image_design']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_testimonialcarouselitem .df_tc_author_box", 
                'attr' => $attrs['author_box_settings']['decoration']['border'] ?? null, 
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

    public function get_hover_values( $attrs, $key ) {
		if ( isset($attrs[$key]['innerContent']['desktop']['hover']) ) {
			return $attrs[$key]['innerContent']['desktop']['hover'];
		}
		return [];
	}

    public function df_arrow_pos_styles( $value = 'middle' ) {

        $options = array (
            'top' => 'position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 0;',
            'middle' => 'position: absolute;
                        top: 50%;
                        left: 0;
                        right: 0;
                        transform: translateY(-50%);',
            'bottom' => 'position: relative;
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 2;',
        );
        return $options[$value];
    }

    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {

        if ( $content === '' ) {
            return sprintf(
                '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Testimonial Item.</strong></h2>'
            );
        }

        wp_enqueue_script('swiper-script');
        wp_enqueue_script('df-testcarousel');

        $item_spacing = $this->props['item_spacing'];
        $item_spacing_tablet = $attrs['carousel_settings_content']['innerContent']['tablet']['value']['item_spacing'] ?? $item_spacing;
        $item_spacing_phone = $attrs['carousel_settings_content']['innerContent']['phone']['value']['item_spacing'] ?? $item_spacing;

        // Get order class and number
        $order_class = $this->order_class;
        $order_number	= str_replace('_','',str_replace($this->slug,'', $order_class));

        $class = '';

        $data = [
            'effect' => $this->props['carousel_type'],
            'desktop' => $this->props['item_desktop'],
            'tablet' => $this->props['item_tablet'],
            'mobile' => $this->props['item_mobile'],
            'loop' => $this->props['loop'] === 'on' ? true : false,
            'item_spacing' => $item_spacing,
            'item_spacing_tablet' => $item_spacing_tablet,
            'item_spacing_phone' => $item_spacing_phone,
            'arrow' => $this->props['arrow'],
            'dots' => $this->props['dots'],
            'autoplay' => $this->props['autoplay'],
            'auto_delay' => $this->props['autospeed'],
            'speed' => $this->props['item_speed'],
            'pause_hover' => $this->props['pause_hover'],
            'centeredSlides' => $this->props['centered_slides'],
            'order' => $order_number,
	        'author_tag' => !empty($this->props['name_level']) ? $this->props['name_level'] : 'h4'
        ];
        if ($this->props['carousel_type'] === 'coverflow') {
            $data['slideShadows'] = $this->props['coverflow_shadow'];
            $data['rotate'] = $this->props['coverflow_rotate'];
            $data['stretch'] = $this->props['coverflow_stretch'];
            $data['depth'] = $this->props['coverflow_depth'];
            $data['modifier'] = $this->props['coverflow_effect_multiper'];
        }

        if($this->props['arrow'] === 'on') {
            $arrow_position = $this->props['arrow_position'] ?? 'middle';
            $class .= ' arrow-' . $arrow_position;
        }

        return sprintf('<div class="df_tc_container%8$s" data-settings=\'%2$s\' data-item="%5$s" data-itemtablet="%6$s" data-itemphone="%7$s">
                <div class="df_tc_inner_wrapper">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            %1$s
                        </div>
                    </div>
                    %3$s
                </div>
                %4$s
            </div>',
            $content,
            wp_json_encode($data),
            $this->df_tc_arrow($order_number),
            $this->df_tc_dots($order_number),
            $this->props['item_desktop'],
            $this->props['item_tablet'],
            $this->props['item_mobile'],
            $class
        );
    }

    /**
     * Arrow navigation
     *
     * @param Integer | $order_number
     * @return String | HTML
     */
    public function df_tc_arrow($order_number) {
        $prev_icon = $this->props['arrow_prev_icon_use_icon'] === 'on' && isset( $this->props['arrow_prev_icon_font_icon'] ) && ! empty( $this->props['arrow_prev_icon_font_icon'] ) ?
            esc_attr( Utils::process_font_icon( $this->props['arrow_prev_icon_font_icon'] ) ?? '4' ) : '4';
        $next_icon = $this->props['arrow_next_icon_use_icon'] === 'on' && isset( $this->props['arrow_next_icon_font_icon'] ) && ! empty( $this->props['arrow_next_icon_font_icon'] ) ?
            esc_attr( Utils::process_font_icon( $this->props['arrow_next_icon_font_icon'] ) ?? '5' ) : '5';

        return $this->props['arrow'] === 'on' ? sprintf('
            <div class="df_tc_arrows">
                <div class="swiper-button-next tc-next-%1$s" data-icon="%3$s"></div>
                <div class="swiper-button-prev tc-prev-%1$s" data-icon="%2$s"></div>
            </div>
        ', $order_number, $prev_icon, $next_icon) : '';
    }

    /**
     * Dot pagination
     *
     * @param Integer | $order_number
     * @return String | HTML
     */
    public function df_tc_dots($order_number) {
        return $this->props['dots'] === 'on' ?
            sprintf('<div class="swiper-pagination tc-dots-%1$s"></div>',$order_number) : '';
    }
}
