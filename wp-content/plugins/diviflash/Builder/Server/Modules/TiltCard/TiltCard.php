<?php

namespace DIFL\Server\Modules\TiltCard;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class TiltCard implements DependencyInterface{

    use ModuleUtil;
    use HasProps;
    use \DIFL\Server\Traits\StyleUtil;

    public $slug       = 'difl_tiltcard';
    public $vb_support = 'on';
	public $icon_path;

    protected $config_file = 'TiltCard';

    protected $module_credits = array (
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__('Tilt Card', 'divi_flash');
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/titlt-box.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/tiltcard' )->customCssFields;
	}

    public static function font_style($font) {
        return static::get_font_style($font);
    }

    public function get_common_styles( $args ) {
        $attrs         = $this->get_merged_attrs( $args );
		$attrs         = self::build_attr_for_styles( $attrs );
		$attrs         = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';

        $styles = [];

        // Icon color
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_image_container .et-pb-icon",
            'attr'     => $attrs['image_and_icon_content']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $color = $value['attrValue']['image_icon_color'] ?? '#2ea3f2';
                return 'color: '.$color.';';
            }
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$order_class} .df_tc_image_container .et-pb-icon",
            'attr'                => $attrs['image_and_icon_content']['innerContent'] ?? null,
            'declarationFunction' => function( $props ) {
                if(isset($props['attrValue']['image_font_icon'])){
                    return self::font_style( $props['attrValue']['image_font_icon'] );
                }
                return '';
            }
        ]);

        // Icon background-color
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_image_container .et-pb-icon",
            'attr'     => $attrs['image_and_icon_content']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $bgcolor = $value['attrValue']['image_icon_bg'] ?? null;
                if($bgcolor) return 'background-color: '.$bgcolor.';';
            },
        ]);

        // Icon font-size
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_image_container .et-pb-icon",
            'attr'     => $attrs['image_and_icon_content']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $size = $value['attrValue']['image_icon_size'] ?? '';
                if($size) return 'font-size: '.$size.';';
            },
        ]);

        // Icon circle toggle
        if($this->props['image_circle_icon'] === 'on'){
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .df_tc_image_container .et-pb-icon",
                'attr'                => $attrs['image_and_icon_content']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return 'border-radius: 50%;';
                },
            ]);
        }

        // icon align
        if( $this->props['image_use_icon'] === 'on' ) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_image_container",
                'attr'     => $attrs['image_and_icon_content']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    $align = $this->props['image_icon_align'] ?? null;
                    if($align) return 'text-align: '.$align.';';
                }
            ]);
        }


        if( $this->props['image_full_width'] === 'on' ){
            // Image full width
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_image_container img",
                'attr'     => $attrs['image_and_icon']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return 'width: 100%';
                }
            ]);
        }else{
            // Image max width
            $common_styles[] = CommonStyle::style([
                'selector' => "{$order_class} .df_tc_image_container img",
                'attr'     => $attrs['image_and_icon']['innerContent'] ?? [],
                'declarationFunction' => function ( $value ) {
                    $maxWidth = $value['attrValue']['image_max_width'] ?? '100%';
                    if($maxWidth) return 'max-width: '.$maxWidth.';';
                }
            ]);
            // Image alignment
            if( $this->props['image_use_icon'] === 'off' ){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$order_class} .df_tc_image_container",
                    'attr'     => $attrs['image_and_icon']['innerContent'] ?? null,
                    'declarationFunction' => function () {
                        $align = $this->props['image_image_align'] ?? 'left';
                        if($align) return 'text-align: '.$align.';';
                    }
                ]);
            }
        }

        // Button alignment
        $common_styles[] = CommonStyle::style([
            'selector' => "{$order_class} .df_tc_button_wrapper",
            'attr'     => $attrs['button']['innerContent'] ?? null,
            'declarationFunction' => function () {
                $align = $this->props['tc_btn_button_align'] ?? null;
                if($align) return 'text-align: '.$align.';';
            }
        ]);

        if ( $this->props['tc_content_float'] === 'on' ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$order_class} > div:first-child",
                'attr'                => $attrs['content_float']['innerContent'] ?? null, // Any attr to track updates
                'declarationFunction' => function () {
                    $translate = $this->props['tc_translate'] ?? '50px';
                    $scale     = $this->props['tc_scale'] ?? '0.9';
                    return "transform: translateZ({$translate}) scale({$scale});";
                }
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$order_class}, {$order_class}:hover",
                'attr'                => $attrs['content_float']['innerContent'] ?? null,
                'declarationFunction' => function () {
                    return 'overflow: visible;';
                },
            ] );
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
                'selector' => "{$order_class} .difl_testimonialcarouselitem .df_tc_company_logo img",
                'attr' => $attrs['brand_logo_design']['decoration']['border'] ?? null,
                'declarationFunction' => function ( $props ) {
                    if($props['attrValue']['radius']){
                        return 'overflow: hidden !important';
                    }
                }
            ]
        );
        $common_styles [] = CommonStyle::style( [
                'selector' => "{$order_class} .difl_testimonialcarouselitem .df_tc_author_image img",
                'attr' => $attrs['author_image_design']['decoration']['border'] ?? null,
                'declarationFunction' => function ( $props ) {
                    if($props['attrValue']['radius']){
                        return 'overflow: hidden !important';
                    }
                }
            ]
        );
        $common_styles [] = CommonStyle::style( [
                'selector' => "{$order_class} .difl_testimonialcarouselitem .df_tc_author_box",
                'attr' => $attrs['author_box_settings']['decoration']['border'] ?? null,
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

    public function render_df_tilt_image_or_icon() {
        $output = '';
        if ( $this->props['image_use_icon'] === 'on' ) {
            $icon = $this->props['image_font_icon'] ? Utils::process_font_icon($this->props['image_font_icon']) : '5';
            $output .= '<div class="df_tc_image_container"><span class="et-pb-icon">' . esc_html($icon) . '</span></div>';
        } else {
            if ($this->props['imageSrc']) {
                $output .= '<div class="df_tc_image_container"><img src="' . $this->props['imageSrc'] . '" alt="'.esc_html($this->props['alt']).'" /></div>';
            }
        }

        return $output;
    }

    public function render_df_tilt_button() {
        $btn_text  = $this->props['tc_btn_button_text'];
        $btn_url   = $this->props['tc_btn_button_url'] ?? '#';
        $btn_blank = $this->props['tc_btn_button_url_new_window'] === 'on' ? ' target="_blank"' : '';

        if ( ! $btn_text ) return '';

        $output  = '<div class="df_tc_button_wrapper">';
        $output .= '<a class="df_tc_button" href="' . esc_url($btn_url) . '"' . $btn_blank . '>' . esc_html($btn_text) . '</a>';
        $output .= '</div>';

        return $output;
    }

    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {

        wp_enqueue_script('df-tilt-lib');  // enqueue library (vanilla-tilt)
        wp_enqueue_script('df-tilt-script');  // enqueue custom scripts to execute [data-options]

        // Extract attribute values with fallbacks
	    $_title        = $this->props['title'] ?? '';
	    $_content      = $this->props['content'] ?? '';

        $title = $_title !== '' ? sprintf('<h4 class="title">%1$s</h4>', $_title) : '';
        $content = $_content !== '' ? sprintf('<div class="content">%1$s</div>', $_content) : '';

        // Tilt options
        $options = [
            'reverse'       => $this->props['tc_reverse'] === 'on' ? true : false,
            'max'           => $this->props['tc_max'] ? intval( $this->props['tc_max'] ) : 35,
            'perspective'   => $this->props['tc_perspective'] ? intval( $this->props['tc_perspective'] ) : 1000 ,
            'glare'         => $this->props['tc_glare'] === 'on' ? true : false,
            'scale'         => $this->props['tc_card_scale'] ? floatval( $this->props['tc_card_scale'] ) : 1,
            'speed'         => $this->props['tc_speed']  ? intval( $this->props['tc_speed'] ) : 300,
            'glare_opacity' => $this->props['tc_glare_opacity'] ? floatval( $this->props['tc_glare_opacity'] ) : 1,
            'tc_full_page'  => $this->props['tc_full_page'] === 'on' ? true : false,
        ];

        $content_wrapper = $title !== '' || $content !== '' ?
            sprintf('<div class="df_tc_content_container">%1$s%2$s</div>', $title, $content) : '';

        return sprintf(
            '<div class="df_tc_container" data-options="%2$s">
                %3$s
                %1$s
                %4$s
            </div>',
            $content_wrapper,
            esc_attr( wp_json_encode( $options ) ),
            $this->render_df_tilt_image_or_icon(),
            $this->render_df_tilt_button()
        );
    }
}
