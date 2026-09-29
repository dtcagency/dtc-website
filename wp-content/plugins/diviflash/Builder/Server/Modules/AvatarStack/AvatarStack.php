<?php

namespace DIFL\Server\Modules\AvatarStack;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class AvatarStack implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

    public $slug       = 'difl_avatar_stack';
    public $vb_support = 'on';
    public $child_slug = 'difl_avatar_stack_item';
	public $icon_path;

    protected $config_file = 'AvatarStack';

    protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__( 'Avatar Stack', 'divi_flash' );
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/logo-carousel.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/avatar-stack' )->customCssFields;
	}

    public function get_common_styles( $args ) {
        $attrs       = $this->get_merged_attrs( $args );
		$attrs       = self::build_attr_for_styles( $attrs );
		$attrs       = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';
		
		$stack_hover = $this->get_hover_values($attrs, 'stack_settings');
		$field_stack_spacing__hover = !empty($stack_hover['field_stack_spacing']) ? $stack_hover['field_stack_spacing'] : null;

		// this custom code is for matching the scenario with divi4 version of this module
		$module_alignment = $attrs['module']['decoration']['sizing']['desktop']['value']['alignment'] ?? 'left';
		$module_alignment_tablet = $attrs['module']['decoration']['sizing']['tablet']['value']['alignment'] ?? $module_alignment;
		$module_alignment_phone = $attrs['module']['decoration']['sizing']['phone']['value']['alignment'] ?? $module_alignment_tablet;
        $styles = [];
     
		if( $this->props['field_stack_spacing'] !== '' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} #difl-avatar-stack-container .difl_avatar_stack_item:not(:first-child)",
				'attr'                => $attrs['stack_settings']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					$stack_item_spacing = $value['attrValue']["field_stack_spacing"] ?? '';
					return 'margin-left: '.$stack_item_spacing.';';
				}
			]);
		}

		if( $field_stack_spacing__hover !== null ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class}.difl_avatar_stack #difl-avatar-stack-container.difl_avatar_stack_container:hover .difl_avatar_stack_item",
				'attr'                => $attrs['stack_settings']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					$stack_spacing = $props['attr']['desktop']['hover']['field_stack_spacing'];
					return 'margin-right: '.$stack_spacing.' !important;margin-left:0px !important;';
				}
			]);
		}  

		// Module alignment
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class}.difl_avatar_stack .difl_avatar_stack_container",
			'attr'                => $attrs['module']['decoration']['sizing'] ?? null,
			'atRules'             => '@media only screen and (min-width: 981px)',
			'declarationFunction' => fn ( $props )  => 'justify-content: '.$module_alignment.';'
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class}.difl_avatar_stack .difl_avatar_stack_container",
			'attr'                => $attrs['module']['decoration']['sizing'] ?? null,
			'atRules'             => '@media only screen and (max-width: 980px)',
			'declarationFunction' => fn ( $props ) => 'justify-content: '.$module_alignment_tablet.';'
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class}.difl_avatar_stack .difl_avatar_stack_container",
			'attr'                => $attrs['module']['decoration']['sizing'] ?? null,
			'atRules'             => '@media only screen and (max-width: 767px)',
			'declarationFunction' => fn ( $props ) => 'justify-content: '.$module_alignment_phone.';'
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class}.difl_avatar_stack",
			'attr'                => $attrs['stack_animations']['innerContent'] ?? null,
			'declarationFunction' => function () {
                return $this->generate_stack_animation_data( $this->props ) ;
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_icon .difl_avatar_stack_icon",
			'attr'                => $attrs['icon_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_icon_size"])){
					return 'font-size: '.$value['attrValue']["field_icon_size"].';';
				}
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_icon .difl_avatar_stack_icon",
			'attr'                => $attrs['icon_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_icon_color"])){
					return 'color: '.$value['attrValue']["field_icon_color"].';';
				}
				return 'color: #7ebec5';
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_icon",
			'attr'                => $attrs['icon_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_icon_container_height"])){
					return 'background-color: '.$value["attrValue"]["field_icon_container_height"].';';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_icon",
			'attr'                => $attrs['icon_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_icon_container_height"])){
					return 'height: '.$value['attrValue']["field_icon_container_height"].';';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_icon",
			'attr'                => $attrs['icon_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_icon_container_width"])){
					return 'width: '.$value['attrValue']["field_icon_container_width"].';';
				}
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_media",
			'attr'                => $attrs['image_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_image_container_height"])){
					return 'height: '.$value['attrValue']["field_image_container_height"].';';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_media",
			'attr'                => $attrs['image_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_image_container_width"])){
					return 'width: '.$value['attrValue']["field_image_container_width"].';';
				}
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_text",
			'attr'                => $attrs['text_container_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_text_background"])){
					return 'background-color: '.$value['attrValue']["field_text_background"].';';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_text",
			'attr'                => $attrs['text_container_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["text_container_height"])){
					return 'height: '.$value['attrValue']["text_container_height"].';';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_text",
			'attr'                => $attrs['text_container_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["text_container_width"])){
					return 'width: '.$value['attrValue']["text_container_width"].';';
				}
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating_container",
			'attr'                => $attrs['rating_design']['innerContent'] ?? null,
			'declarationFunction' => fn () => 'justify-content: '.$this->props["field_rating_position"].';'
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating_container .difl_avatar_stack_rating",
			'attr'                => $attrs['rating_design']['innerContent'] ?? null,
			'declarationFunction' => fn () => 'text-align: '.$this->props["field_rating_alignment"].';'
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating_container .difl_avatar_stack_rating",
			'attr'                => $attrs['rating_design']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["field_rating_icon_size"])){
					return 'font-size: '.$value['attrValue']["field_rating_icon_size"].';';
				}
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating_container .difl_avatar_stack_rating span.rate:before",
			'attr'                => $attrs['rating_design']['innerContent'] ?? null,
			'declarationFunction' => function ($value) {
				if(isset($value['attrValue']["field_rating_color"])){
					return 'color: '.$value['attrValue']["field_rating_color"].';';
				}
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating .difl_avatar_stack_rating_container .difl_avatar_stack_rating span.blank:before",
			'attr'                => $attrs['rating_design']['innerContent'] ?? null,
			'declarationFunction' => function ($value) {
				if(isset($value['attrValue']["field_blank_color"])){
					return 'color: '.$value['attrValue']["field_blank_color"].';';
				}
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating",
			'attr'                => $attrs['rating_design']['innerContent'] ?? null,
			'declarationFunction' => function ($value) {
				if(isset($value['attrValue']["field_rating_background"])){
					return 'background-color: '.$value['attrValue']["field_rating_background"].';';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating",
			'attr'                => $attrs['rating_design']['innerContent'] ?? null,
			'declarationFunction' => function ($value) {
				if(isset($value['attrValue']["rating_container_height"])){
					return 'height: '.$value['attrValue']["rating_container_height"].';';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating",
			'attr'                => $attrs['rating_design']['innerContent'] ?? null,
			'declarationFunction' => function ($value) {
				if(isset($value['attrValue']["rating_container_width"])){
					return 'width: '.$value['attrValue']["rating_container_width"].';';
				}
			}
		]);

        $common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'][data-placement^='top'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltip_design']['innerContent'] ?? null,
			'declarationFunction' => fn () => 'border-top-color: '.$this->props["field_tooltip_arrow_color"].';'
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'][data-placement^='bottom'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltip_design']['innerContent'] ?? null,
			'declarationFunction' => fn () => 'border-bottom-color: '.$this->props["field_tooltip_arrow_color"].';'
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'][data-placement^='right'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltip_design']['innerContent'] ?? null,
			'declarationFunction' => fn () => 'border-right-color: '.$this->props["field_tooltip_arrow_color"].';'
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'][data-placement^='left'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltip_design']['innerContent'] ?? null,
			'declarationFunction' => fn () => 'border-left-color: '.$this->props["field_tooltip_arrow_color"].';'
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'] .tippy-content p",
			'attr'                => $attrs['tooltip']['innerContent'] ?? null,
			'declarationFunction' => fn () => 'padding-bottom: 0px;'
		]);

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
                'selector' => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_icon", 
                'attr' => $attrs['icon_design']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        ); 
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_avatar_stack_item:not(:has(.difl_avatar_stack_item_wrapper.has_icon, .difl_avatar_stack_item_wrapper.has_rating, .difl_avatar_stack_item_wrapper.has_text)) .difl_avatar_stack_item_wrapper", 
                'attr' => $attrs['image_design']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_text", 
                'attr' => $attrs['text_container_design']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .difl_avatar_stack_item .difl_avatar_stack_item_wrapper.has_rating", 
                'attr' => $attrs['rating_design']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
		$common_styles [] = CommonStyle::style( [ 
                'selector' => ".tippy-box[data-theme~='{$order_class}']`", 
                'attr' => $attrs['tooltip_design']['decoration']['border'] ?? null, 
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
 
    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {
        wp_enqueue_script( 'image-hotspot-popper-script' );
		wp_enqueue_script( 'image-hotspot-tippy-bundle-script' );
		wp_enqueue_script( 'df_avatar_stack' );

        $data_settings = $this->process_tooltip_data( $this->props );

        $output = sprintf(
			'<div id="difl-avatar-stack-container" class="difl_avatar_stack_container" data-tag_attr=\'%3$s\'  data-settings=\'%2$s\'>
				%1$s
			</div>',
			$content,
			wp_json_encode( $data_settings ),
			wp_json_encode( [
				"title_tag" => ! empty($this->props['text_title_level']) ? $this->props['text_title_level'] : 'h4',
				"subtitle_tag" => ! empty($this->props['text_subtitle_level']) ? $this->props['text_subtitle_level'] : 'h6',
			] )
		);

		return $output;

    }
   
    public function process_tooltip_data( $props ) {
		$data_settings = [
			'tooltip_enable'      => 'on' === $props['field_tooltip_enable'],
			'arrow'               => 'on' === $props['field_tooltip_arrow'],
			'interactive'         => 'on' === $props['field_tooltip_interactive'],
			'interactiveBorder'   => 'on' === $props['field_tooltip_interactive'] && isset( $props['field_tooltip_interactive_border'] ) ? $props['field_tooltip_interactive_border'] : 2,
			'interactiveDebounce' => 'on' === $props['field_tooltip_interactive'] && isset( $props['field_tooltip_interactive_debounce'] ) ? $props['field_tooltip_interactive_debounce'] : 0,
			'animation'           => isset( $props['field_tooltip_animation'] ) ? $props['field_tooltip_animation'] : 'fade',
			'placement'           => isset( $props['field_tooltip_placement'] ) ? $props['field_tooltip_placement'] : 'top',
			'trigger'             => isset( $props['field_tooltip_trigger'] ) ? $props['field_tooltip_trigger'] : 'focus',
			'followCursor'        => 'on' === $props['field_tooltip_follow_cursor'],
			'maxWidth'            => isset( $props['field_tooltip_custom_maxwidth'] ) ? $props['field_tooltip_custom_maxwidth'] : 350,
			'offsetEnable'        => 'on' === $props['field_tooltip_offset_enable'],
			'offsetSkidding'      => 'on' === $props['field_tooltip_offset_enable'] && isset( $props['field_tooltip_offset_skidding'] ) ? $props['field_tooltip_offset_skidding'] : 0,
			'offsetDistance'      => 'on' === $props['field_tooltip_offset_enable'] && isset( $props['field_tooltip_offset_distance'] ) ? $props['field_tooltip_offset_distance'] : 10,
			'delay' => 'on' === $props['field_tooltip_enable'] && isset($props['field_tooltip_content_delay']) ? $props['field_tooltip_content_delay'] : 300
		];

		return $data_settings;
	}

	public function generate_stack_animation_data( $props ) {
		$default = [
			'field_item_translate_x'    => '0px',
			'field_item_translate_y'    => '0px',
			'field_item_rotate_x'       => '0deg',
			'field_item_rotate_y'       => '0deg',
			'field_item_rotate_z'       => '0deg',
			'field_item_scale_x'        => '1',
			'field_item_scale_y'        => '1',
			'field_item_skew_x'         => '0deg',
			'field_item_skew_y'         => '0deg',
			'field_item_trans_duration' => '300ms',
			'field_item_trans_delay'    => '0ms',
			'field_item_trans_easing'   => 'ease-out',
			'field_stack_translate_x'   => '0px',
			'field_stack_translate_y'   => '0px',
			'field_stack_rotate_x'      => '0deg',
			'field_stack_rotate_y'      => '0deg',
			'field_stack_rotate_z'      => '0deg',
		];
		$field_root_transition = [];
		if('on' === $props['field_item_translate_enable']){
			$field_root_transition['field_item_translate_x'] = '--df-avatarStack-item-trans-x-hover';
			$field_root_transition['field_item_translate_y'] = '--df-avatarStack-item-trans-y-hover';
		}
		if('on' === $props['field_item_rotate_enable']){
			$field_root_transition['field_item_rotate_x'] = '--df-avatarStack-item-rotate-x-hover';
			$field_root_transition['field_item_rotate_y'] = '--df-avatarStack-item-rotate-y-hover';
			$field_root_transition['field_item_rotate_z'] = '--df-avatarStack-item-rotate-z-hover';
		}
		if('on' === $props['field_item_scale_enable']){
			$field_root_transition['field_item_scale_x'] = '--df-avatarStack-item-scale-x-hover';
			$field_root_transition['field_item_scale_y'] = '--df-avatarStack-item-scale-y-hover';
		}
		if('on' === $props['field_item_skew_enable']){
			$field_root_transition['field_item_skew_x'] = '--df-avatarStack-item-skew-x-hover';
			$field_root_transition['field_item_skew_y'] = '--df-avatarStack-item-skew-y-hover';
		}
		if('on' === $props['field_item_transition_enable']){
			$field_root_transition['field_item_trans_duration'] = '--df-avatarStack-item-transition-duration';
			$field_root_transition['field_item_trans_delay'] = '--df-avatarStack-item-transition-delay';
			$field_root_transition['field_item_trans_easing'] = '--df-avatarStack-item-transition-easing';
		}
		if('on' === $props['field_stack_translate_enable']){
			$field_root_transition['field_stack_translate_x'] = '--df-avatarStack-trans-x-normal';
			$field_root_transition['field_stack_translate_y'] = '--df-avatarStack-trans-y-normal';
		}
		if('on' === $props['field_stack_rotate_enable']){
			$field_root_transition['field_stack_rotate_x'] = '--df-avatarStack-rotate-x-normal';
			$field_root_transition['field_stack_rotate_y'] = '--df-avatarStack-rotate-y-normal';
			$field_root_transition['field_stack_rotate_z'] = '--df-avatarStack-rotate-z-normal';
		}

		$result = '';
		foreach ( $field_root_transition as $key => $css_var ) {
			$value = $props[ $key ] ? $props[ $key ] : $default[$key];
			if ( in_array( $key, [ 'field_item_trans_duration', 'field_item_trans_delay' ] ) ) {
				$value .= $props[ $key ] ? 'ms' : '';
			}
			$result .= "$css_var:$value;";
		}

		return $result;
	}
}
