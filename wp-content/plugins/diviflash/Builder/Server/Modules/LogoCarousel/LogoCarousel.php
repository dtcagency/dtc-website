<?php

namespace DIFL\Server\Modules\LogoCarousel;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class LogoCarousel implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

    public $slug       = 'difl_logocarousel';
    public $vb_support = 'on';
    public $child_slug = 'difl_logocarouselitem';
	public $icon_path;

    protected $config_file = 'LogoCarousel';

    protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__( 'Logo Carousel', 'divi_flash' );
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/logo-carousel.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/logocarousel' )->customCssFields;
	}

    public function get_common_styles( $args ) {
        $attrs      = $this->get_merged_attrs( $args );
		$attrs       = self::build_attr_for_styles( $attrs );
		$attrs       = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';
        $state      = $args['state'];

        $arrows_hover = $this->get_hover_values($attrs, 'arrow_design');
		$arrow_color__hover = !empty($arrows_hover['arrow_icon_color']) ? $arrows_hover['arrow_icon_color'] : null;
		$arrow_bg_color__hover = !empty($arrows_hover['arrow_bg_color']) ? $arrows_hover['arrow_bg_color'] : null;
		$arrow_opacity__hover = !empty($arrows_hover['arrow_opacity_disable']) ? $arrows_hover['arrow_opacity_disable'] : null;

        $styles = [];

        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_lci_container img",
            'attr'     => $attrs['image_settings']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                    $maxwidth = $value['attrValue']['lc_max_width'] ?? '';
                    if($maxwidth){
                        return 'max-width: '.$maxwidth.';';
                    }
                }
        ]);

        if ( $this->props['lc_vertical'] !== '' && $this->props['equal_height'] !== 'on' ){

            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_lc_container",
                'attr'     => $attrs['image_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) {
                    return "align-items: ".$this->props['lc_vertical'].";";
                }
            ]);
        }

        if ( $this->props['equal_height'] === 'on' ) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_lc_container .difl_logocarouselitem",
                'attr'     => $attrs['image_settings']['innerContent'] ?? [],
                'declarationFunction' => function () {
                    return 'height: auto; align-items: center;';
                },
            ]);
        }

       
        if( $this->props['arrow'] === 'on' ){
            if( $state === 'value' ){
                $common_styles [] = CommonStyle::style( [
                    'selector'            => "{$order_class} .bx-controls-direction .bx-prev, {$order_class} .bx-controls-direction .bx-next",
                    'attr'                => $attrs['arrow_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                        $icon_color = $value['attrValue']['arrow_icon_color'] ?? '';
                        return 'color:'.$icon_color.';';
                    }
                ] );

                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .bx-controls-direction .bx-prev, {$order_class} .bx-controls-direction .bx-next",
                    'attr'     => $attrs['arrow_design']['innerContent'] ?? null,
                    'declarationFunction' => function ( $value ) {
                    $icon_bg_color = $value['attrValue']['arrow_bg_color'] ?? '';
                        return 'background-color:'.$icon_bg_color.';';
                    }
                ]);

                if ( $this->props['loop'] !== 'on' ) {
                    $common_styles[] = CommonStyle::style([
                        'selector' => "{$order_class} .bx-controls-direction a.disabled",
                        'attr'     => $attrs['arrow_design']['innerContent'] ?? [],
                        'declarationFunction' => function ( $value ) {
                            $opacity = $value['attrValue']['arrow_opacity_disable'] ?? '';
                            return 'opacity: '.$opacity.';';
                        }
                    ]);
                }
            }

            if( $arrow_color__hover !== null ){
                    $common_styles [] = CommonStyle::style( [
                        'selector'            => "{$order_class}:hover .bx-controls-direction .bx-prev, {$order_class}:hover .bx-controls-direction .bx-next",
                        'attr'                => $attrs['arrow_design']['innerContent'] ?? null,
                        'declarationFunction' => function ( $props ) {
                            $color = $props['attr']['desktop']['hover']['arrow_icon_color'];
                            return 'color:'.$color.' !important;';
                        }
                    ] );
                }
                if( $arrow_bg_color__hover !== null ){
                    $common_styles [] = CommonStyle::style( [
                        'selector'            => "{$order_class}:hover .bx-controls-direction .bx-prev, {$order_class}:hover .bx-controls-direction .bx-next",
                        'attr'                => $attrs['arrow_design']['innerContent'] ?? null,
                        'declarationFunction' => function ( $props ) {
                            $bgcolor = $props['attr']['desktop']['hover']['arrow_bg_color'];
                            return 'background:'.$bgcolor.' !important;';
                        }
                    ] );
                }
                if( $arrow_opacity__hover !== null ){
                    if ( $this->props['loop'] !== 'on' ) {
                        $common_styles[] = CommonStyle::style([
                            'selector' => "{$order_class}:hover .bx-controls-direction a.disabled",
                            'attr'     => $attrs['arrow_design']['innerContent'] ?? [],
                            'declarationFunction' => function ( $props ) {
                                $opacity = $props['attr']['desktop']['hover']['arrow_opacity_disable'];
                                return 'opacity: '.$opacity.' !important;';
                            }
                        ]);
                    }
                }
                
        }
        
        if( $this->props['dots'] === 'on' ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .bx-pager .bx-pager-item a",
                'attr'     => $attrs['dots_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    $dots_color = $value['attrValue']['dots_color'] ?? '';
                        return 'background:'.$dots_color.';';
                    }
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .bx-pager .bx-pager-item a.active",
                'attr'     => $attrs['dots_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                        $bg_color = $value['attrValue']['active_dot_color'] ?? '';
                        return 'background: '.$bg_color.';';
                    }
            ]);
        }

        {/**
        * Support for border-radius
        * In Divi-4, if user change radius property, an additional css autometically added [overflow:hidden] to that selector
        * But in 5??  To resolve this issues. These are the fields which will generate overflow hidden
        */}
        $common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_logocarouselitem", 
                'attr' => $attrs['module']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if($props['attrValue']['radius']){
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
 
    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {

        if ( trim( $content ) === '' ) {
            return '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Logo Item.</strong></h2>';
        }
        // wp_enqueue_style('bxslider-style');
        wp_enqueue_script('bxslider-script');
        wp_enqueue_script('df-logocarousel');

        // Get order class and number
        $order_class = $this->order_class;
        $order_number	= str_replace('_','',str_replace($this->slug,'', $order_class));
        
        // Prepare options
        $options = [
            'desktop'        => (int) ($this->props['item_desktop'] ?? '4'),
            'tablet'         => (int) ($this->props['item_tablet'] ?? '3'),
            'mobile'         => (int) ($this->props['item_mobile'] ?? '1'), 
            'width'          => (int) ($this->props['item_width'] ?? '300'), 
            'loop'           => $this->props['loop'] === 'on', 
            'spacingbetween' => (int) ($this->props['item_spacing'] ?? '30'),
            'arrows'         => ( $this->props['arrow'] ?? '' ) === 'on',
            'dots'           => ( $this->props['dots'] ?? '' ) === 'on',
            'autoplay'       => ( $this->props['autoplay'] ?? '' ) === 'on',
            'auto_delay'     => (int) ($this->props['autospeed'] ?? '2000'),
            'speed'          => (int) ($this->props['speed'] ?? '500'),
            'pause_hover'    => ( $this->props['pause_hover'] ?? '' ) === 'on',
            'ticker'         => $this->props['ticker'] ?? 'off',
            'ticker_hover'   => ( $this->props['ticker_hover'] ?? '' ) === 'on',
            'order'          => $order_number,
            'ticker_speed'   => (int) ($this->props['ticker_speed'] ?? '4000'),
        ];
 
        return sprintf(
            '<div class="df_lc_outer">
                <div class="df_lc_container" data-settings="%2$s">
                    %1$s
                </div>
            </div>',
            $content,
            esc_attr( wp_json_encode( $options ) )
        );
    }
}
