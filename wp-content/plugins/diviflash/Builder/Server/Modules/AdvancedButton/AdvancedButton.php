<?php

namespace DIFL\Server\Modules\AdvancedButton;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class AdvancedButton implements DependencyInterface{

    use ModuleUtil;
    use HasProps;

    public $slug       = 'difl_advanced_button';
    public $vb_support = 'on';
	public $icon_path;

    protected $config_file = 'AdvancedButton';

    protected $module_credits = array (
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__('Advanced Button', 'divi_flash');
        $this->main_css_element = "%%order_class%%";
        $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/advanced-button.svg';
    }

    public static function custom_css(){
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/advancedbutton' )->customCssFields;
	}

    public function get_common_styles( $args ) {
        $attrs       = $this->get_merged_attrs( $args );
		$attrs       = self::build_attr_for_styles( $attrs );
		$attrs       = $this->get_default_value_for_styles( $attrs );
        $order_class  = $args['orderClass'] ?? '';

		$settingsHoverValues = $this->get_hover_values($attrs, 'settings');

		$media_placement__hover = !empty($settingsHoverValues['media_placement']) ? $settingsHoverValues['media_placement'] : '';
		$button_hover = $this->get_hover_values($attrs, 'contents');
		$button_icon__hover = !empty($button_hover['button_icon']) ? $button_hover['button_icon'] : null;

        $styles = [];

		$common_styles[] = CommonStyle::style([
            'selector'            => "{$order_class}",
            'attr'                => $attrs['alignment']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
				$btn_align = $value['attrValue']['button_alignment'] ?? 'left';
				return 'text-align: '.$btn_align.';';
            },
        ]);
		$common_styles[] = CommonStyle::style([
            'selector'            => "{$order_class} .difl_adv_btn_wrapper",
            'attr'                => $attrs['alignment']['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
				$btn_content_align = $value['attrValue']['button_content_alignment'] ?? 'flex-start';
				return 'justify-content: '.$btn_content_align.';';
            },
        ]);

		if($this->props['use_button_icon'] === 'on'){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_icon",
				'attr'                => $attrs['contents']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					$icon = $value['attrValue']['button_icon'] ?? null;
					return self::get_font_style( $icon );
				},
			]);
			if( $button_icon__hover !== null ){
				$common_styles[] = CommonStyle::style([
					'selector'            => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_icon_hover",
					'attr'                => $attrs['contents']['innerContent'] ?? null,
					'declarationFunction' => function ( $value ) {
						$icon_hover = $value['attrValue']['button_icon__hover'] ?? null;
						return self::get_font_style( $icon_hover );
					},
				]);
			}
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_icon, {$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_icon_hover",
				'attr'                => $attrs['media']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					$color = $value['attrValue']['icon_color'] ?? '#2ea3f2'; // fallback default
					return 'color: ' . $color . ';';
				},

			]);
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_icon, {$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_icon_hover",
				'attr'                => $attrs['media']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) {
					$size = $value['attrValue']['button_icon_size'] ?? '20px'; // fallback default
					return 'font-size: ' . $size . ';';
				}
			]);
		}

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_media_wrapper",
			'attr'                => $attrs['media']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$bg = $value['attrValue']['media_background_color'] ?? 'transparent'; // fallback default
				return 'background-color: ' . $bg . ';';
			},
		]);

		if( isset( $this->props['media_placement'] ) && 'media_right' === $this->props['media_placement'] ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_media_wrapper",
				'attr'                => $attrs['settings']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return "order: 2;";
				}
			]);
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_text_wrapper",
				'attr'                => $attrs['settings']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return "order: 1;";
				}
			]);
		}

		if( $media_placement__hover !== '' ) {
			if( $media_placement__hover === 'media_right' ){
				$common_styles[] = CommonStyle::style([
					'selector'          => "{$order_class} .difl_advanced_button_container:hover .difl_adv_btn_wrapper .difl_adv_btn_media_wrapper",
					'attr'              => $attrs['settings']['innerContent'] ?? null,
					'declarationFunction' => function () {
						return "order: 2;";
					}
				]);
				$common_styles[] = CommonStyle::style([
					'selector'            => "{$order_class} .difl_advanced_button_container:hover .difl_adv_btn_wrapper .difl_adv_btn_text_wrapper",
					'attr'                => $attrs['settings']['innerContent'] ?? null,
					'declarationFunction' => function () {
						return "order: 1;";
					}
				]);
			}else{
				$common_styles[] = CommonStyle::style([
					'selector'            => "{$order_class} .difl_advanced_button_container:hover .difl_adv_btn_wrapper .difl_adv_btn_media_wrapper",
					'attr'                => $attrs['settings']['innerContent'] ?? null,
					'declarationFunction' => function () {
						return "order: 1;";
					}
				]);
				$common_styles[] = CommonStyle::style([
					'selector'            => "{$order_class} .difl_advanced_button_container:hover .difl_adv_btn_wrapper .difl_adv_btn_text_wrapper",
					'attr'                => $attrs['settings']['innerContent'] ?? null,
					'declarationFunction' => function () {
						return "order: 2;";
					}
				]);
			}
		}

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_advanced_button_container",
			'attr'                => $attrs['hover']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return '--dfab-bg-hover-background-color: '.$this->props["bg_hover_background_color"].';';
			}
		]);

		if( $this->props["bg_hover_effect_hyper"] === 'on' && in_array($this->props['bg_hover_effects'], ['dfab_reveal', 'dfab_ripple']) ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container",
				'attr'                => $attrs['hover']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return '--dfab-bg-hover-hypen-color: '.$this->props["bg_hover_hypen_color"].';';
				}
			]);
		}

		if( $this->props["bg_hover_effects"] !== 'dfab_none' && 
			in_array($this->props['bg_hover_effects'], ['dfab_reveal', 'dfab_door_open', 'dfab_skew', 'dfab_two_shade']) && 
			$this->props["bg_hover_effect_bounce"] === 'on' ) {
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container",
				'attr'                => $attrs['hover']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return '--dfab-bg-hover-background-transition-timimg-function: cubic-bezier(.52,1.64,.37,.66) !important;';
				}
			]);
		}

		if( $this->props["bg_hover_effects"] === 'dfab_two_shade' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container",
				'attr'                => $attrs['hover']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return '--dfab-bg-hover-background-secondary-color: '.$this->props["bg_hover_background_secondary_color"].';';
				}
			]);
		}

		if( $this->props["border_hover_color"] && $this->props["border_hover_effects"] !== 'dfab_none' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container",
				'attr'                => $attrs['hover']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return '--dfab-border-hover-background-color: '.$this->props["border_hover_color"].';';
				}
			]);
		}

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_advanced_button_container",
			'attr'                => $attrs['media']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$width = $value['attrValue']['media_wrapper_width'] ?? '30px'; // fallback default
				return '--dfab-media-wrapper-width: ' . $width . ';';
			}
		]);

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .difl_advanced_button_container",
			'attr'                => $attrs['media']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$height = $value['attrValue']['media_wrapper_height'] ?? '30px'; // fallback
				return '--dfab-media-wrapper-height: ' . $height . ';';
			},

		]);

		if( $this->props["bg_hover_effects"] !== 'dfab_none' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container.difl_advanced_button_container",
				'attr'                => $attrs['hover']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return '--dfab-bg-hover-background-transtion-time: '.$this->props["bg_transition_duration"].'s;--dfab-bg-hover-background-transition-timimg-function: '.$this->props["bg_transition_timing_function"] .';--dfab-bg-hover-background-transtion-delay: '.$this->props["bg_transition_delay"].'s;';
				}
			]);
		}

		if( $this->props["border_hover_effects"] !== 'dfab_none' ) {
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container.difl_advanced_button_container",
				'attr'                => $attrs['hover']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return '--dfab-border-hover-background-transtion-time: '.$this->props["stroke_transition_duration"].'s;--dfab-border-hover-background-transition-timimg-function: '.$this->props["stroke_transition_timing_function"] .';--dfab-border-hover-background-transtion-delay: '.$this->props["stroke_transition_delay"].'s;';
				}
			]);
		}

		if( $this->props["media_hover_effects"] !== 'dfab_none' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container.difl_advanced_button_container",
				'attr'                => $attrs['hover']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return '--dfab-media-hover-transition-duration: '.$this->props["media_transition_duration"].'s;--dfab-media-hover-transition-function: '.$this->props["media_transition_timing_function"] .';--dfab-media-hover-transition-delay: '.$this->props["media_transition_delay"].'s;';
				}
			]);
		}

		if( $this->props["two_d_hover_effects"] !== 'dfab_none' ){
			$common_styles[] = CommonStyle::style([
				'selector'            => "{$order_class} .difl_advanced_button_container",
				'attr'                => $attrs['hover']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return '--dfab-two-d-animation-duration: '.$this->props["two_d_transition_duration"].'s;--dfab-two-d-animation-delay: '.$this->props["two_d_transition_delay"] .'s;';
				}
			]);
		}

		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .tippy-box[data-theme~='.difl_advanced_button_container'][data-placement^='top'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltipDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'border-top-color: '.$this->props["field_tooltip_arrow_color"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .tippy-box[data-theme~='.difl_advanced_button_container'][data-placement^='bottom'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltipDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'border-bottom-color: '.$this->props["field_tooltip_arrow_color"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .tippy-box[data-theme~='.difl_advanced_button_container'][data-placement^='right'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltipDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'border-right-color: '.$this->props["field_tooltip_arrow_color"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .tippy-box[data-theme~='.difl_advanced_button_container'][data-placement^='left'] > .tippy-arrow::before",
			'attr'                => $attrs['tooltipDesign']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'border-left-color: '.$this->props["field_tooltip_arrow_color"].';';
			}
		]);
		$common_styles[] = CommonStyle::style([
			'selector'            => "{$order_class} .tippy-box[data-theme~='.difl_advanced_button_container'] .tippy-content p",
			'attr'                => $attrs['tooltip']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return 'padding-bottom: 0px;';
			}
		]);

		// support for overflow: hidden
		$common_styles [] = CommonStyle::style( [ 
				'selector' => "{$order_class} .difl_advanced_button_container", 
				'attr' => $attrs['module']['decoration']['border'] ?? null, 
				'declarationFunction' => function ( $props ) { 
					if(isset($props['attrValue']['radius'])){
						return 'overflow: hidden !important';
					}
				} 
			] 
		); 
		$common_styles [] = CommonStyle::style( [ 
				'selector' => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_media_wrapper", 
				'attr' => $attrs['media']['decoration']['border'] ?? null, 
				'declarationFunction' => function ( $props ) { 
					if(isset($props['attrValue']['radius'])){
						return 'overflow: hidden !important';
					}
				} 
			] 
		); 
		$common_styles [] = CommonStyle::style( [ 
				'selector' => "{$order_class} .tippy-box[data-theme~='.difl_advanced_button_container']", 
				'attr' => $attrs['tooltipDesign']['decoration']['border'] ?? null, 
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

	// public function add_overflow_hidden( $props ) {
	// 	return (
	// 		isset( $props['attrValue']['radius']['bottomLeft'] ) &&
	// 		isset( $props['attrValue']['radius']['bottomRight'] ) &&
	// 		isset( $props['attrValue']['radius']['topLeft'] ) &&
	// 		isset( $props['attrValue']['radius']['topRight'] )
	// 	) ? 'overflow: hidden !important;' : '';
	// }

	public function get_hover_values($attrs, $key) {
		if ( isset($attrs[$key]['innerContent']['desktop']['hover']) ) {
			return $attrs[$key]['innerContent']['desktop']['hover'];
		}

		return [];
	}
 
    public function render( $attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {
        if ( 'on' === $this->props['field_tooltip_enable'] ) {
			$this->enqueue_scripts();
		}
		if('dfab_ripple_position_aware' === $this->props['bg_hover_effects']){
			$this->generate_ripple_css();
		}
		return $this->button_markup_process( $attrs, $this->props, $render_slug );
    } 
	public function generate_ripple_css() {
		?>
		<script type="text/javascript">
			(()=>{
				window.addEventListener( 'load', () => {
					const list_advanced_button = document.querySelectorAll('.difl_advancedbutton');

					list_advanced_button.forEach((advanced_button, index) => {

						const list_position_aware_bg = advanced_button.querySelector(".dfab_position_aware_bg");

						advanced_button.querySelectorAll(".dfab_ripple_position_aware").forEach((element) => {
							element.addEventListener("mouseenter", updatePosition);
							element.addEventListener("mouseleave", updatePosition);
						});

						function updatePosition(event) {
							const containerRect = advanced_button.getBoundingClientRect();
							const left = event.pageX - containerRect.left - window.scrollX;
							const top = event.pageY - containerRect.top - window.scrollY;

							list_position_aware_bg.style.left = `${left}px`;
							list_position_aware_bg.style.top = `${top}px`;
						}
					});
				});
			})()
		</script>
		<?php
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'image-hotspot-popper-script' );
		wp_enqueue_script( 'image-hotspot-tippy-bundle-script' ); ?>
        <script type="text/javascript">
			(()=>{
				window.addEventListener( 'load', () => {
					const list_advanced_button = document.querySelectorAll('.difl_advancedbutton');

					list_advanced_button.forEach((advanced_button, index) => {
						process_tooltip(index, advanced_button);
					});
					function process_tooltip(index, advanced_button) {
						const tooltip_container = advanced_button.querySelector('.difl_advanced_button_container');
						if (!tooltip_container) return;

						const settings = tooltip_container.dataset.settings ? JSON.parse(tooltip_container.dataset.settings) : {};
						const tooltipStatus = settings.tooltip_enable;
						const disableOnMobile = settings.disable_on_mobile && window.innerWidth < 768;

						const ele_class = Array.from(advanced_button.classList).filter(class_name =>
							class_name.indexOf('difl_advancedbutton_') !== -1
						);

						if (tooltipStatus && !disableOnMobile) {
							const options = {
								arrow: settings.arrow,
								animation: settings.animation,
								placement: settings.placement,
								trigger: settings.trigger,
								allowHTML: true,
								followCursor: false,
								interactive: settings.interactive,
								interactiveBorder: parseInt(settings.interactiveBorder),
								maxWidth: parseInt(settings.maxWidth),
								offset: [
									parseInt(settings.offsetSkidding),
									parseInt(settings.offsetDistance)
								],
								theme: `.${ele_class[0]} .difl_advanced_button_container`,
								delay: [
									parseInt(settings.delay),
									parseInt(settings.interactiveDebounce)
								],
							};

							const noscriptTag = advanced_button.querySelector('noscript');
							const tooltipContent = noscriptTag ? noscriptTag.textContent : '';

							if (tooltipContent === '') {
								tippy(tooltip_container, options).disable();
							} else {
								options.content = tooltipContent;
								tippy(tooltip_container, options);
							}
						}
					}
				}); 
			})()
        </script>
		<?php
	}

    public function button_markup_process( $attrs, $props, $render_slug ) {

		$button_hover = $this->get_hover_values($attrs, 'contents');
		$button_text__hover = !empty($button_hover['button_text']) ? $button_hover['button_text'] : null;
		$button_sub_text__hover = !empty($button_hover['button_sub_text']) ? $button_hover['button_sub_text'] : null;

		/*===== Effects =====*/
		$classes = ''; 
		// Media Placement
		$media_placement = isset( $this->props['media_placement'] ) ? $this->props['media_placement'] : 'media_left';
		if ( "" !== $media_placement ) {
			$classes .= " " . $media_placement;
		}

		// Background
		$background_hover_effect        = ! empty( $this->props['bg_hover_effects'] ) && 'dfab_none' !== $this->props['bg_hover_effects'] ? $this->props['bg_hover_effects'] : "";
		$background_hover_effect_markup = "";
		if ( "" !== $background_hover_effect ) {
			$classes                        .= " " . $background_hover_effect;
			$background_hover_effect_markup = sprintf( '<span class="difl_adv_btn_bg_anim"></span>' );
		}
		if ( "" !== $background_hover_effect && in_array( $background_hover_effect, [
				'dfab_reveal',
				'dfab_reveal_with_hypen',
				'dfab_two_shade'
			] ) ) {
			$background_hover_effect_direction = ! empty( $this->props['bg_hover_effect_directions'] ) ? $this->props['bg_hover_effect_directions'] : "dfab_left";

			$classes .= " " . $background_hover_effect_direction;
		}
		$ripple_position_aware = "";
		if ( 'dfab_ripple_position_aware' === $this->props['bg_hover_effects'] ) {
			$ripple_position_aware = "<span class='dfab_position_aware_bg'></span>";
		}
		if ( 'dfab_skew' === $this->props['bg_hover_effects'] ) {
			$bg_hover_skew_effect_directions = ! empty( $this->props['bg_hover_skew_effect_directions'] ) ? $this->props['bg_hover_skew_effect_directions'] : "dfab_top_left";

			$classes .= " " . $bg_hover_skew_effect_directions;

		}

		// Hypen
		if ( 'on' === $this->props['bg_hover_effect_hyper'] && in_array( $background_hover_effect, [
				'dfab_reveal',
				'dfab_ripple'
			] ) ) {
			$classes .= " dfab_hypen";
		}

		// Border
		$border_hover_effect        = ! empty( $this->props['border_hover_effects'] ) && 'dfab_none' !== $this->props['border_hover_effects'] ? $this->props['border_hover_effects'] : "";
		$border_hover_effect_markup = "";
		if ( "" !== $border_hover_effect ) {
			$classes                    .= " " . $border_hover_effect;
			$border_hover_effect_markup = sprintf( '<span class="difl_adv_btn_border_anim"></span><span class="difl_adv_btn_border_anim_2"></span>' );
		}

		// 2D Effects
		$two_d_hover_effects = ! empty( $this->props['two_d_hover_effects'] ) && 'dfab_none' !== $this->props['two_d_hover_effects'] ? $this->props['two_d_hover_effects'] : "";
		if ( "" !== $two_d_hover_effects ) {
			$classes .= " " . $two_d_hover_effects;
			$classes .= " dfab__animate";
		}

		// Media Effects
		$media_hover_effects = 'dfab_none' !== $this->props['media_hover_effects'] ? $this->props['media_hover_effects'] : "";
		if ( "" !== $media_hover_effects ) {
			$classes .= " " . $media_hover_effects;
		}
		if ( in_array( $this->props['media_hover_effects'], [ 'dfab_media_reveal', 'dfab_media_slide' ] ) ) {
			$media_hover_effect_directions = $this->props['media_hover_effect_directions'] ? $this->props['media_hover_effect_directions'] : "dfab_mr_left";
			$classes                       .= " " . $media_hover_effect_directions;
		}

		/*===== Effects =====*/ 
		$btn_sub_text_hover = '';
		if ( $button_sub_text__hover !== null ) {
			$btn_sub_text_hover = sprintf( '<span class="difl_adv_btn_sub_text_hover">%1$s</span>', esc_html($button_sub_text__hover) );
		}

		$sub_text_markup         = "";
		$sub_text_markup_outside = "";
		if ( 'on' === $props['sub_text_placement'] && ! empty( $props['button_sub_text'] ) ) {
			$sub_text_markup_outside = sprintf( '<span class="difl_adv_btn_sub_text">%1$s</span>%2$s', esc_html($props['button_sub_text']), $btn_sub_text_hover );
		} else {
			$sub_text_markup = sprintf( '<span class="difl_adv_btn_sub_text">%1$s</span> %2$s', esc_html($props['button_sub_text']), $btn_sub_text_hover );
		}

		$btn_text       = ! empty( $props['button_text'] ) ? $props['button_text'] : "Advanced Button";
		$btn_text_hover = '';
		if ( $button_text__hover !== null ) {
			$btn_text_hover = sprintf( '<span class="difl_adv_btn_text_hover">%1$s</span>', esc_html($button_text__hover) );
		}

		$button_link_type              = ! empty( $props['button_link_type'] ) ? $props['button_link_type'] : 'link_url';
		$button_link_markup            = '';
		$button_link_additional_markup = '';
		if ( 'link_url' === $button_link_type ) {
			$button_link_markup = ! empty( $props['button_link_url'] ) ? $props['button_link_url'] : '#';
			if ( isset( $props['button_url_new_window'] ) && 'on' === $props['button_url_new_window'] ) {
				$button_link_additional_markup = 'target="_blank"';
			}
		}
		if ( 'download' === $button_link_type ) {
			$button_link_markup            = ! empty( $props['button_link_download'] ) ? $props['button_link_download'] : '';
			$button_link_additional_markup = 'download';
		}
		if ( 'email' === $button_link_type ) {
			$button_link_markup = ! empty( $props['button_link_email'] ) ? $props['button_link_email'] : '';
			$button_link_markup = 'mailto:' . $button_link_markup;
		}
		if ( 'phone' === $button_link_type ) {
			$button_link_markup = ! empty( $props['button_link_phone'] ) ? $props['button_link_phone'] : '';
			$button_link_markup = 'tel:' . $button_link_markup;
		}
		if ( 'link_url' === $button_link_type || 'download' === $button_link_type ) {
			$url_scheme = wp_parse_url( $button_link_markup, PHP_URL_SCHEME );

			if ( "https" !== $url_scheme && "http" !== $url_scheme && "#" !== $button_link_markup && "#" !== $button_link_markup[0] && "/" !== $button_link_markup[0] ) {
				$button_link_markup = "https://" . $button_link_markup;
			}
		}

		/*-------- Tooltip --------*/
		$data_settings   = $this->process_tooltip_data( $props );
		$tooltip_content = ! empty( $this->props['field_tooltip_content'] ) ? $this->props['field_tooltip_content'] : '';

		return sprintf(
			'<a class="difl_advanced_button_container%5$s" data-settings=\'%13$s\' data-text="%7$s" href="%11$s" %12$s>
							<span class="difl_adv_btn_wrapper">
								%1$s
								<span class="difl_adv_btn_text_wrapper">
									<span class="difl_adv_btn_text">%2$s</span>
									%10$s
									%3$s
								</span>
							</span>
							%4$s
							%6$s
							%8$s
							%9$s
							<noscript class="difl_advanced_button_tooltip_content">%14$s</noscript>
						</a>',
			( 'on' === $props['use_button_icon'] ) ? $this->process_icon( $attrs, $props, $render_slug ) : $this->process_media( $attrs, $props ),
			$btn_text,
			$sub_text_markup,
			$sub_text_markup_outside,
			$classes,
			$ripple_position_aware,
			( 'dfab_text_vertical_cut' === $this->props['bg_hover_effects'] ) ? $btn_text : '',
			$background_hover_effect_markup,
			$border_hover_effect_markup,
			$btn_text_hover,
			$button_link_markup,
			$button_link_additional_markup,
			wp_json_encode( $data_settings ),
			$tooltip_content
		);
	}

	public function process_icon( $attrs, $props, $render_slug ) {
		$button_hover = $this->get_hover_values($attrs, 'contents');
		$button_icon__hover = !empty($button_hover['button_icon']) ? $button_hover['button_icon'] : null;

		$font_icon = ! empty( $props['button_icon'] ) ? Utils::process_font_icon($props['button_icon']) : '5';
		$font_icon_hover = '';

		if ( $button_icon__hover !== null ) {
			$font_icon_hover = sprintf( '<span class="et-pb-icon difl_adv_btn_media difl_adv_btn_icon_hover">%1$s</span>',  Utils::process_font_icon($button_icon__hover) );
		}

		return sprintf( '<span class="difl_adv_btn_media_wrapper"><span class="et-pb-icon difl_adv_btn_media difl_adv_btn_icon">%1$s</span>%2$s</span>', $font_icon, $font_icon_hover );
	}

	public function process_media( $attrs, $props ) {

		if ( empty( $props['button_image'] ) ) {
			return "";
		}

		$button_hover = $this->get_hover_values($attrs, 'contents');
		$button_image__hover = !empty($button_hover['button_image']) ? $button_hover['button_image'] : null;
		
		$button_image_hover = '';
		if ( $button_image__hover !== null ) {
			$button_image_hover = sprintf( '<img class="difl_adv_btn_media difl_adv_btn_img_hover" src="%1$s" alt="Advanced Button" title="" />', $button_image__hover );
		}

		return sprintf( '<span class="difl_adv_btn_media_wrapper"><img class="difl_adv_btn_media difl_adv_btn_img" src="%1$s" alt="Advanced Button" title="" />%2$s</span>', $props['button_image'], $button_image_hover );
	}

	// public function generate_transition_fields( $prefix, $label_prefix, $args = [] ) {
	// 	$fields = [];
	// 	if ( ! in_array( 'duration', $args['exclude'] ) ) {
	// 		$fields["{$prefix}_transition_duration"] = [
	// 			'label'           => esc_html__( "{$label_prefix} Transition Duration [Sec]", 'divi_flash' ),
	// 			'default'         => 0.5,
	// 			'range_settings'  => [
	// 				'min'       => 1,
	// 				'max'       => 10,
	// 				'step'      => 0.1,
	// 				'min_limit' => 0,
	// 			],
	// 			'description'     => esc_html__( '', 'divi_flash' ),
	// 			'type'            => 'range',
	// 			'option_category' => 'layout',
	// 			'tab_slug'        => $args['tab_slug'],
	// 			'toggle_slug'     => $args['toggle_slug'],
	// 			'sub_toggle'      => $args['sub_toggle'],
	// 			'allowed_units'   => false,
	// 			'show_if'         => $args['show_if'],
	// 			'show_if_not'     => $args['show_if_not']
	// 		];
	// 	}
	// 	if ( ! in_array( 'delay', $args['exclude'] ) ) {
	// 		$fields["{$prefix}_transition_delay"] = [
	// 			'label'           => esc_html__( "{$label_prefix} Transition Delay [Sec]", 'divi_flash' ),
	// 			'default'         => 0.0,
	// 			'range_settings'  => [
	// 				'min'       => 1,
	// 				'max'       => 10,
	// 				'step'      => 0.1,
	// 				'min_limit' => 0,
	// 			],
	// 			'description'     => esc_html__( '', 'divi_flash' ),
	// 			'type'            => 'range',
	// 			'option_category' => 'layout',
	// 			'tab_slug'        => $args['tab_slug'],
	// 			'toggle_slug'     => $args['toggle_slug'],
	// 			'sub_toggle'      => $args['sub_toggle'],
	// 			'allowed_units'   => false,
	// 			'show_if'         => $args['show_if'],
	// 			'show_if_not'     => $args['show_if_not'],
	// 		];
	// 	}
	// 	if ( ! in_array( 'timing_function', $args['exclude'] ) ) {
	// 		$fields["{$prefix}_transition_timing_function"] = [
	// 			'label'       => esc_html__( "{$label_prefix} Transition Timing Function ", 'divi_flash' ),
	// 			'type'        => 'select',
	// 			'default'     => 'ease-out',
	// 			'options'     => [
	// 				'ease'        => esc_html__( 'Ease', 'divi_flash' ),
	// 				'linear'      => esc_html__( 'Linear', 'divi_flash' ),
	// 				'ease-in'     => esc_html__( 'Ease In', 'divi_flash' ),
	// 				'ease-out'    => esc_html__( 'Ease Out', 'divi_flash' ),
	// 				'ease-in-out' => esc_html__( 'Ease In Out', 'divi_flash' ),
	// 			],
	// 			'tab_slug'    => $args['tab_slug'],
	// 			'toggle_slug' => $args['toggle_slug'],
	// 			'sub_toggle'  => $args['sub_toggle'],
	// 			'show_if'     => $args['show_if'],
	// 			'show_if_not' => $args['show_if_not'],
	// 		];
	// 	}

	// 	return $fields;
	// }

	public function process_tooltip_data( $props ) {
		$data_settings = [
			'tooltip_enable'      => 'on' === $props['field_tooltip_enable'],
			'disable_on_mobile'   => 'on' === $props['field_tooltip_disable_on_mobile'],
			'arrow'               => 'on' === $props['field_tooltip_arrow'],
			'interactive'         => 'on' === $props['field_tooltip_interactive'],
			'interactiveBorder'   => 'on' === $props['field_tooltip_interactive'] && isset( $props['field_tooltip_interactive_border'] ) ? $props['field_tooltip_interactive_border'] : 2,
			'interactiveDebounce' => 'on' === $props['field_tooltip_interactive'] && isset( $props['field_tooltip_interactive_debounce'] ) ? $props['field_tooltip_interactive_debounce'] : 0,
			'animation'           => isset( $props['field_tooltip_animation'] ) ? $props['field_tooltip_animation'] : 'fade',
			'placement'           => isset( $props['field_tooltip_placement'] ) ? $props['field_tooltip_placement'] : 'top',
			'trigger'             => 'mouseenter focus',
			'followCursor'        => false,
			'maxWidth'            => isset( $props['field_tooltip_custom_maxwidth'] ) ? $props['field_tooltip_custom_maxwidth'] : 350,
			'offsetEnable'        => 'on' === $props['field_tooltip_offset_enable'],
			'offsetSkidding'      => 'on' === $props['field_tooltip_offset_enable'] && isset( $props['field_tooltip_offset_skidding'] ) ? $props['field_tooltip_offset_skidding'] : 0,
			'offsetDistance'      => 'on' === $props['field_tooltip_offset_enable'] && isset( $props['field_tooltip_offset_distance'] ) ? $props['field_tooltip_offset_distance'] : 10,
			'delay'               => 'on' === $props['field_tooltip_enable'] && isset( $props['field_tooltip_content_delay'] ) ? $props['field_tooltip_content_delay'] : 300
		];

		return $data_settings;
	}
}