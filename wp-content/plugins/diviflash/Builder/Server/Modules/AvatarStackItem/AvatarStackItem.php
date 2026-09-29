<?php

namespace DIFL\Server\Modules\AvatarStackItem;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class AvatarStackItem implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

    public $slug       = 'difl_avatar_stack_item';
    public $vb_support = 'on';
	public $icon_path;

    protected $config_file = 'AvatarStackItem';
 
    protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__( 'Stack Item Settings', 'divi_flash' );
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/logo-carousel.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/avatar-stack-item' )->customCssFields;
	}

    public function get_common_styles( $args ) {
        $attrs       = $this->get_merged_attrs( $args );
		$attrs       = self::build_attr_for_styles( $attrs );
		$attrs       = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';

        $styles = [];

        $field_content_type = $this->props['field_content_type'] ? $this->props['field_content_type'] : "icon";

        if( 'icon' === $field_content_type ){
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_icon .difl_avatar_stack_icon",
                'attr'                => $attrs['content']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    return self::get_font_style($value['attrValue']["field_font_icon"]);
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_icon .difl_avatar_stack_icon",
                'attr'                => $attrs['icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']["field_icon_size"])){
                        return 'font-size: '.$value['attrValue']["field_icon_size"].' !important;';
                    }
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_icon .difl_avatar_stack_icon",
                'attr'                => $attrs['icon_design']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']["field_icon_color"])){
                        return 'color: '.$value['attrValue']["field_icon_color"].' !important;';
                    }
                }
            ]);
        }
        if( 'rating' === $field_content_type ){
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_text_container",
                'attr'                => $attrs['content']['innerContent'] ?? null,
                'declarationFunction' => fn () => 'justify-content: '.$this->props["field_rating_position"].' !important;'
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating",
                'attr'                => $attrs['rating_label']['innerContent'] ?? null,
                'declarationFunction' => fn () => 'text-align: '.$this->props["field_rating_alignment"].' !important;'
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating",
                'attr'                => $attrs['rating_label']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']["field_rating_icon_size"])){
                        return 'font-size: '.$value['attrValue']["field_rating_icon_size"].' !important;';
                    }
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating_container .difl_avatar_stack_rating span.rate:before",
                'attr'                => $attrs['rating_label']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']["field_rating_color"])){
                        return 'color: '.$value['attrValue']["field_rating_color"].' !important;';
                    }
                }
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating_container .difl_avatar_stack_rating span.blank:before",
                'attr'                => $attrs['rating_label']['innerContent'] ?? null,
                'declarationFunction' => function ( $value ) {
                    if(isset($value['attrValue']["field_blank_color"])){
                        return 'color: '.$value['attrValue']["field_blank_color"].' !important;';
                    }
                }
            ]);
        }
        if( 'text' === $field_content_type ){
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$order_class} .difl_avatar_stack_item_wrapper.has_text .difl_avatar_stack_text_container",
                'attr'                => $attrs['content']['innerContent'] ?? null,
                'declarationFunction' => fn () => 'justify-content: '.$this->props["field_text_position"].' !important;'
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
 
    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {

		$output       = '';
		$output_class = 'has_icon';
		if ( 'image' === $this->props['field_content_type'] ) {
			$output       = $this->process_media( $this->props );
			$output_class = 'has_media';
		} else if ( 'rating' === $this->props['field_content_type'] ) {
			$output       = $this->process_rating( $this->props );
			$output_class = 'has_rating';
		} else if ( 'text' === $this->props['field_content_type'] ) {
			$output       = $this->process_text( $this->props );
			$output_class = 'has_text';
		} else {
			$output       = $this->process_icon( $this->props, $render_slug );
			$output_class = 'has_icon';
		}

		/*-------- Tooltip --------*/
		// Tooltip
		$tooltip_content      = $this->props['field_tooltip_content'];
		$tooltip_content_data = "";
		if ( ! empty( $tooltip_content ) ) {
			$tooltip_content_data = sprintf( '<noscript>%1$s</noscript>', $tooltip_content );
		}

		return sprintf( 
            '<div class="difl_avatar_stack_item_wrapper %2$s">
				%1$s%3$s
			</div>',
			$output,
			$output_class,
            $tooltip_content_data
		);
    }
 
    public function process_icon( $props ) {
		if ( empty( $props['field_font_icon'] ) ) {
			return "";
		}
        $font_icon = ! empty( $props['field_font_icon'] ) 
            ? $props['field_font_icon'] 
            : [
                'unicode' => '&#xf406;',
                'type'    => 'fa',
                'weight'  => '900',
            ];

        $processedIcon = Utils::process_font_icon( $font_icon );

        return sprintf( '<span class="et-pb-icon difl_avatar_stack_icon">%1$s</span>', esc_attr( $processedIcon ) );
	}

	public function process_media( $props ) {
		if ( empty( $props['field_image_src'] ) ) {
			return "";
		}

		return sprintf( '<img src="%1$s" alt="" class="difl_avatar_stack_media"/>', esc_html( $props['field_image_src'] ) );
	}

	public function process_rating( $props ) {
		$rating = '';
		for ( $i = 1; $i <= 5; $i ++ ) {
			if ( $i <= $props['field_rating_number'] ) {
				$rating .= '<span class="rate"></span>';
			} else {
				$rating .= '<span class="blank"></span>';
			}
		}

		$rating_label = '';
		if ( ! empty( $props['field_rating_label'] ) ) {
			$rating_label = sprintf( '<span class="difl_avatar_stack_rating_label">%1$s</span>', esc_html( $props['field_rating_label'] ) );
		}

		return sprintf( '<div class="difl_avatar_stack_rating_container">
				<div class="difl_avatar_stack_rating">
					%1$s
				</div>
				%2$s
			</div>', $rating, $rating_label );
	}

	public function process_text( $props ) {

		$title = '';
		if ( ! empty( $props['field_title_text'] ) ) {
			$title = sprintf( '<h4 class="difl_avatar_stack_text_title">%1$s</h4>', esc_html( $props['field_title_text'] ) );
		}

		$subtitle = '';
		if ( ! empty( $props['field_subtitle_text'] ) ) {
			$subtitle = sprintf( '<h6 class="difl_avatar_stack_text_subtitle">%1$s</h6>', esc_html( $props['field_subtitle_text'] ) );
		}

		return sprintf( '<div class="difl_avatar_stack_text_container"> %1$s %2$s </div>', $title, $subtitle );
	}
}
