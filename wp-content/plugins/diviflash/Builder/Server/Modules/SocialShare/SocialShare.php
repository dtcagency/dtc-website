<?php

namespace DIFL\Server\Modules\SocialShare;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class SocialShare implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

    public $slug       = 'difl_social_share';
    public $vb_support = 'on';
    public $child_slug = 'difl_social_share_item';
	public $icon_path;

    protected $config_file = 'SocialShare';

    protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__( 'Social Share', 'divi_flash' );
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/logo-carousel.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/social-share' )->customCssFields;
	}

	public function get_hover_values( $attrs, $key ) {
		if ( isset($attrs[$key]['innerContent']['desktop']['hover']) ) {
			return $attrs[$key]['innerContent']['desktop']['hover'];
		}
		return [];
	}

	private function get_column_template_css( $key ){
		$column_template_ref = [
			'auto'  => 'gap: 10px; display: inline-flex; flex-wrap: wrap;',
			'one'   => 'display: grid; grid-template-columns: repeat(1, 1fr);',
			'two'   => 'display: grid; grid-template-columns: repeat(2, 1fr);',
			'three' => 'display: grid; grid-template-columns: repeat(3, 1fr);',
			'four'  => 'display: grid; grid-template-columns: repeat(4, 1fr);',
			'five'  => 'display: grid; grid-template-columns: repeat(5, 1fr);',
			'six'   => 'display: grid; grid-template-columns: repeat(6, 1fr);',
		];

		return $column_template_ref[$key];

	}

    public function get_common_styles( $args ) {
        $attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';
        $state      = $args['state'];

        $styles = [];

        $icon_image_hover = $this->get_hover_values($attrs, 'iconImage');
		$icon_color__hover = !empty($icon_image_hover['icon_color']) ? $icon_image_hover['icon_color'] : null;

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-container.difl_social_share_container",
			'attr'                => $attrs['general']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$column_view = $value['attrValue']['column_view'] ?? 'auto';
				if($column_view){
					return $this->get_column_template_css($column_view);
				}
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-container.difl_social_share_container",
			'attr'                => $attrs['settingsDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'column-gap: '.$this->props["columns_gap"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-container.difl_social_share_container",
			'attr'                => $attrs['settingsDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'row-gap: '.$this->props["rows_gap"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-container.difl_social_share_container .difl_social_share_item_wrapper",
			'attr'                => $attrs['settingsDesign']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if( isset($value['attrValue']["button_height"]) ){
					return 'height: '.$value['attrValue']["button_height"].';';
				}
			}
		]);
		if( in_array( $this->props['icon_position'], ['column', 'column-reverse'], true ) && $this->props['button_height'] !== '' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} #difl-social-share-container .difl_social_share_item_wrapper",
				'attr'                => $attrs['iconImage']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'justify-content: center;';
				}
			]);
		}

		if( $state === 'value' ){
            $common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} #difl-social-share-container a.difl_social_share_item_wrapper .difl_social_share_icon i:before",
				'attr'                => $attrs['iconImage']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					if(isset($value['attrValue']["icon_color"])){
						return 'color: '.$value['attrValue']["icon_color"].';';
					}
				}
			]);
		}

		if( $icon_color__hover !== null ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} #difl-social-share-container a.difl_social_share_item_wrapper:hover .difl_social_share_icon i:before",
				'attr'                => $attrs['iconImage']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					$color = $props['attr']['desktop']['hover']['icon_color'];
					return 'color: '.$color.';';
				}
			]);
		}

		if( 'on' === $this->props['use_icon_font_size'] ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} #difl-social-share-container .difl_social_share_item_wrapper",
				'attr'                => $attrs['iconImage']['innerContent'] ?? null,
				'declarationFunction' => function ($value) {
					if(isset($value['attrValue']["icon_font_size"])){
						return '--df-ss-icon-font-size: '.$value['attrValue']["icon_font_size"].';';
					}
				}
			]);
		}
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class}",
			'attr'                => $attrs['alignment']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if( isset($value['attrValue']["content_alignment"]) ){
					return 'text-align: '.$value['attrValue']["content_alignment"].';';
				}
			}
		]);
		if( $this->props['column_view'] === 'auto' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_social_share_container",
				'attr'                => $attrs['alignment']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					if( isset($value['attrValue']["column_auto_child_item_alignment"]) ){
						return 'justify-content: '.$value['attrValue']["column_auto_child_item_alignment"].';';
					}
				}
			]);
		}

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_social_share_item_wrapper",
			'attr'                => $attrs['alignment']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if( isset($value['attrValue']["child_content_alignment"]) ){
					return 'justify-content: '.$value['attrValue']["child_content_alignment"].' !important;';
				}
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-item-wrapper .difl_social_share_icon",
			'attr'                => $attrs['iconImage']['decoration']['spacing'] ?? null,
			'declarationFunction' => function ($props) {
				return 'width: auto; height: auto;';
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-container .difl_social_share_item_wrapper",
			'attr'                => $attrs['iconImage']['innerContent'] ?? null,
			'declarationFunction' => function ($props) {
				if(isset($props['attrValue']['icon_position'])){
					return 'flex-direction: '.$props['attrValue']['icon_position'].';';
				}
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-container .difl_social_share_item_wrapper .difl_social_share_icon",
			'attr'                => $attrs['iconImage']['innerContent'] ?? null,
			'declarationFunction' => function ($props) {
				if(isset($props['attrValue']['icon_alignment'])){
					return 'align-self: '.$props['attrValue']['icon_alignment'].';';
				}
			}
		]);
		
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_social_share_item .difl_social_share_item_wrapper .difl_social_share_icon",
			'attr'                => $attrs['iconImage']['decoration']['spacing'] ?? null,
			'declarationFunction' => function ($props) {
				if( isset($props['attrValue']['padding']) ){
					return 'width: auto; height: auto;';
				}
			}
		]);

		// <CommonStyle
		// 		selector={ `${ orderClass } #difl-social-share-item-wrapper.difl_temp_1.difl_temp_2.difl_social_share_item_wrapper .difl_social_share_icon` }
		// 		attr={ attrs?.iconImage?.decoration?.spacing ?? {} }
		// 		declarationFunction={ (props) =>{
		// 			return  `width: auto; height: auto;`;
		// 		}}
		// 	/>

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-header-container.difl_social_share_header_container",
			'attr'                => $attrs['headerDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'flex-direction: '.$this->props["header_icon_position"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-header-container.difl_social_share_header_container .difl_social_share_header_icon",
			'attr'                => $attrs['headerDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'align-self: '.$this->props["header_icon_alignment"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-header #difl-social-share-header-container.difl_social_share_header_container .difl_social_share_header_icon",
			'attr'                => $attrs['headerDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'color: '.$this->props["header_icon_color"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-header-container .difl_social_share_header_icon",
			'attr'                => $attrs['headerDesign']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if(isset($value['attrValue']["header_icon_font_size"])){
					return '--df-header-icon-size: '.$value['attrValue']["header_icon_font_size"].';';
				}
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} #difl-social-share-header-container.difl_social_share_header_container",
			'attr'                => $attrs['headerContainer']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'gap: '.$this->props["header_content_gap"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_social_share_header",
			'attr'                => $attrs['headerContainer']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'justify-content: '.$this->props["header_alignment"].';';
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'][data-placement^='top'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltipDesign']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if( isset($value['attrValue']["field_tooltip_arrow_color"]) ){
					return 'border-top-color: '.$value['attrValue']["field_tooltip_arrow_color"].';';
				}
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'][data-placement^='bottom'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltipDesign']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if( isset($value['attrValue']["field_tooltip_arrow_color"]) ){
					return 'border-bottom-color: '.$value['attrValue']["field_tooltip_arrow_color"].';';
				}
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'][data-placement^='right'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltipDesign']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if( isset($value['attrValue']["field_tooltip_arrow_color"]) ){
					return 'border-right-color: '.$value['attrValue']["field_tooltip_arrow_color"].';';
				}
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'][data-placement^='left'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltipDesign']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				if( isset($value['attrValue']["field_tooltip_arrow_color"]) ){
					return 'border-left-color: '.$value['attrValue']["field_tooltip_arrow_color"].';';
				}
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}']",
			'attr'                => $attrs['tooltipSettings']['innerContent'] ?? null,
			'declarationFunction' => function () {
				$max = isset( $this->props['field_tooltip_custom_maxwidth'] ) && $this->props['field_tooltip_custom_maxwidth'] !== ''
					? (int) $this->props['field_tooltip_custom_maxwidth']
					: 350;
				return 'max-width: ' . $max . 'px !important;';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => ".tippy-box[data-theme~='{$order_class}'] .tippy-content p",
			'attr'                => $attrs['tooltipSettings']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'padding-bottom: 0px;';
			}
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
                'selector' => "{$order_class} #difl-social-share-container .difl_social_share_item_wrapper .difl_social_share_icon", 
                'attr' => $attrs['iconImage']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} #difl-social-share-container .difl_social_share_item_wrapper .difl_social_share_content_container .difl_social_share_content", 
                'attr' => $attrs['labelContainer']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} #difl-social-share-header #difl-social-share-header-container.difl_social_share_header_container", 
                'attr' => $attrs['headerContainer']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} .tippy-box[data-theme~='{$order_class}']", 
                'attr' => $attrs['tooltipDesign']['decoration']['border'] ?? null, 
                'declarationFunction' => function ( $props ) { 
                    if(isset($props['attrValue']['radius'])){
                        return 'overflow: hidden !important';
                    }
                } 
            ] 
        );
		$common_styles [] = CommonStyle::style( [ 
                'selector' => "{$order_class} #difl-social-share-container .difl_social_share_item_wrapper", 
                'attr' => $attrs['shareButton']['decoration']['border'] ?? null, 
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
        
        wp_enqueue_script('df_social_share');
        if ( 'on' === $this->props['field_tooltip_enable'] ) {
			wp_enqueue_script( 'image-hotspot-popper-script' );
			wp_enqueue_script( 'image-hotspot-tippy-bundle-script' );
		}
        
        $header_output = '';
		if ( 'on' === $this->props['enable_header'] ) {
			$header_title     = ! empty( $this->props['header_title'] ) ? $this->props['header_title'] : '';
			$header_sub_title = ! empty( $this->props['header_sub_title'] ) ? $this->props['header_sub_title'] : '';
			$header_content   = "";
			if ( ! empty( $header_title ) || ! empty( $header_title ) ) {
				$header_content = sprintf(
					'<div class="difl_social_share_header_content">%1$s%2$s</div>',
					! empty( $header_title ) ? sprintf( '<span class="difl_social_share_header_title">%1$s</span>', esc_html($header_title) ) : '',
					! empty( $header_sub_title ) ? sprintf( '<span class="difl_social_share_header_sub_title">%1$s</span>', esc_html($header_sub_title) ) : ''
				);
			}
			$header_icon = '';
			if ( ! empty( $this->props['header_icon'] ) ) {
				$header_icon = sprintf( '<span class="et-pb-icon difl_social_share_header_icon">%1$s</span>', esc_attr( Utils::process_font_icon($this->props['header_icon'] ) ) );
			}

			$header_output = sprintf( '<div id="difl-social-share-header" class="difl_social_share_header"><div id="difl-social-share-header-container" class="difl_social_share_header_container">%1$s%2$s</div></div>', $header_icon, $header_content );
		}
 
		// Tooltip
		$data_settings = $this->process_tooltip_data( $this->props );

		add_filter( 'et_global_assets_list', [ $this, 'difl_load_required_divi_assets' ], 10, 3 );

		return sprintf( '%2$s<div class="et_pb_module_inner"><div id="difl-social-share-container" class="difl_social_share_container" data-settings=\'%3$s\'>%1$s</div></div>', $content, $header_output, wp_json_encode( $data_settings ) );
    }

    public function process_tooltip_data( $props ) {
		$data_settings = [
			'tooltip_enable'      => 'on' === $props['field_tooltip_enable'],
			'arrow'               => 'on' === $props['field_tooltip_arrow'],
			'disable_on_mobile'   => 'on' === $props['field_tooltip_disable_on_mobile'],
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
}
