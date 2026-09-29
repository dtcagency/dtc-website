<?php

namespace DIFL\Server\Modules\ContentCarousel;

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
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\FrontEnd\Assets\StaticCSS;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Handler\Fa_Icon_Handler;


class ContentCarousel implements DependencyInterface {

  public $icon_path;
  use ModuleUtil;
  use HasProps;
  use StyleUtil;

  protected $config_file = 'ContentCarousel';
  public static $content;
  protected $order_class;

  protected $module_credits = [
    'module_uri' => '',
    'author'     => 'DiviFlash',
    'author_uri' => 'diviflash.com',
  ];

  public static function custom_css() {

    $block = \WP_Block_Type_Registry::get_instance()->get_registered('difl/contentcarousel');
    return $block ? $block->customCssFields : [];
  }

  public static function module_classnames($args) {
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

    // Module.
    $classnames_instance->add(
      ElementClassnames::classnames(
        [
          'attrs' => $attrs['module']['decoration'] ?? [],
          'attrs' => array_merge(
            $attrs['module']['decoration'] ?? [],
            [
              'link' => $attrs['module']['advanced']['link'] ?? [],
            ]
          ),
        ]
      )
    );

    $has_lightbox = $attrs['use_lightbox']['innerContent']['desktop']['value'] ?? 'off';
    if ($has_lightbox === 'on') {
      $classnames_instance->add('has_lightbox');
    }

  }

  public static function module_script_data($args) {
    $elements = $args['elements'];

    // Element Script Data Options.
    $elements->script_data(
      [
        'attrName' => 'module',
      ]
    );
  }

  public function get_hover_values( $attrs, $key ) {
		if ( isset($attrs[$key]['innerContent']['desktop']['hover']) ) {
			return $attrs[$key]['innerContent']['desktop']['hover'];
		}
		return [];
	}


  public function get_common_styles($args) {
    $attrs      = $this->get_merged_attrs( $args );
    $attrs      = self::build_attr_for_styles( $attrs );
    $attrs      = self::get_default_value_for_styles( $attrs );
    $orderClass    = $args['orderClass'] ?? '';
    $elements      = $args['elements'];
    $settings      = $args['settings'] ?? [];
    $styles        = [];
    $common_styles = [];

    $state      = $args['state'];

    $common_styles[] = $elements->style([
        'attrName'   => 'module',
        'styleProps' => [
            'disabledOn' => [
                'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
            ],
        ],
    ]);

    $common_styles[] = CssStyle::style([
        'selector'  => $args['orderClass'],
        'attr'      => $attrs['css'] ?? [],
        'cssFields' => self::custom_css(),
    ]);

    $common_styles[] = $elements->style(['attrName' => 'cc_title',]);
    $common_styles[] = $elements->style(['attrName' => 'cc_subtitle',]);
    $common_styles[] = $elements->style(['attrName' => 'cc_content',]);
    $common_styles[] = $elements->style(['attrName' => 'content_heading_1',]);
    $common_styles[] = $elements->style(['attrName' => 'cc_button',]);
    $common_styles[] = $elements->style(['attrName' => 'button_wrapper_spacing',]);
    $common_styles[] = $elements->style(['attrName' => 'btn_icon_margin',]);
    $common_styles[] = $elements->style(['attrName' => 'arrows',]);
    $common_styles[] = $elements->style(['attrName' => 'arrow_next_spacing',]);
    $common_styles[] = $elements->style(['attrName' => 'wrapper_padding',]);
    $common_styles[] = $elements->style(['attrName' => 'item_wrapper_spacing',]);
    $common_styles[] = $elements->style(['attrName' => 'image_wrapper_spacing',]);
    $common_styles[] = $elements->style(['attrName' => 'image_margin',]);

    $arrows_hover = $this->get_hover_values($attrs, 'arrows');
		$arrow_color__hover = !empty($arrows_hover['arrow_color']) ? $arrows_hover['arrow_color'] : null;
		$arrow_bg_color__hover = !empty($arrows_hover['arrow_background']) ? $arrows_hover['arrow_background'] : null;
		$arrow_opacity__hover = !empty($arrows_hover['arrow_opacity']) ? $arrows_hover['arrow_opacity'] : null;
		$arrow_disable_opcaity__hover = !empty($arrows_hover['arrow_opacity_disable']) ? $arrows_hover['arrow_opacity_disable'] : null;

    if ($this->props['arrow'] === 'on') {

        // if hover state is true, then add the hover styles

        if ($state === 'value') {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_cc_arrows div:after",
                'attr' => $attrs['arrows']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "color: " . ( $props['attrValue']['arrow_color'] ?? '' ) . ";",
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_cc_arrows div",
                'attr' => $attrs['arrows']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "background-color: " . ( $props['attrValue']['arrow_background'] ?? '' ) . ";",
            ]);

            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_cc_arrows div",
                'attr' => $attrs['arrows']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "opacity: " . ( $props['attrValue']['arrow_opacity'] ?? '1' ) . ";",
            ]);

            if ($this->props['loop'] !== 'on') {
                $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_cc_arrows div.swiper-button-disabled",
                'attr' => $attrs['arrows']['innerContent'] ?? [],
                'declarationFunction' => fn($props) => "opacity: " . ( $props['attrValue']['arrow_opacity_disable']  ?? '0.5' ) . "!important;",
                ]);
            }
        } else{

        }

        if( $arrow_color__hover !== null ){
            $common_styles[] = CommonStyle::style([
                'selector' => " {$orderClass}:hover .df_cc_arrows div:before, {$orderClass}:hover .df_cc_arrows div:after",
                'attr'     => $attrs['arrows']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) {
                    if(isset($props['attr']['desktop']['hover']['arrow_color'])){
                        return 'color: '.$props['attr']['desktop']['hover']['arrow_color'].' !important;';
                    }
                }
            ]);
        }

        if( $arrow_bg_color__hover !== null ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}:hover .df_cc_arrows div",
                'attr'     => $attrs['arrows']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) {
                    if(isset($props['attr']['desktop']['hover']['arrow_background'])){
                        return 'background-color: '.$props['attr']['desktop']['hover']['arrow_background'].' !important;';
                    }
                }
            ]);
        }

        if( $arrow_opacity__hover !== null ){
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass}:hover .df_cc_arrows div:not(.swiper-button-disabled)",
                'attr'     => $attrs['arrows']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) {
                    if(isset($props['attr']['desktop']['hover']['arrow_opacity'])){
                        return 'opacity: '.$props['attr']['desktop']['hover']['arrow_opacity'].' !important;';
                    }
                }
            ]);
        }

        if( $arrow_disable_opcaity__hover !== null ){
            if( $this->props['loop'] !== 'on' ){
                $common_styles[] = CommonStyle::style([
                    'selector' => "{$orderClass}:hover .df_cc_arrows div.swiper-button-disabled",
                    'attr'     => $attrs['arrows']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) {
                        if(isset($props['attr']['desktop']['hover']['arrow_opacity_disable'])){
                            return 'opacity: '.$props['attr']['desktop']['hover']['arrow_opacity_disable'].' !important;';
                        }
                    }
                ]);
            }
        }

        // non hover state styles

        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df_cc_arrows",
            'attr' => $attrs['arrows']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => $this->df_arrow_pos_styles($this->props['arrow_position']) . "justify-content: {$this->props['arrow_align']};",
        ]);

        if ($this->props['arrow_circle'] === 'on') {
            $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df_cc_arrows > div",
            'attr' => $attrs['arrows']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "border-radius: 50%;",
            ]);
        }

        if ( ( $this->props['arrow_opacity'] ?? '1' ) !== '0') {
            $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .arrow-middle .df_cc_arrows *",
            'attr' => $attrs['arrows']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "pointer-events: all !important;",
            ]);
        }
    }

    $common_styles[] = CommonStyle::style( [
        'selector' => "{$orderClass} .difl_contentcarouselitem .et_pb_module_inner",
        'attr' => $attrs['module']['decoration']['border'] ?? [],
        'declarationFunction' => function ( $props ) {
            $radius = $props['attrValue']['radius'] ?? null;
            if ( ! empty( $radius ) ) {
                return 'overflow: hidden !important';
            }
            return '';
        }
    ]);

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .df_cci_button_wrapper",
      'attr'     => $attrs['cc_button']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "text-align: {$this->props['cc_button_button_align']};",
    ]);

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .swiper-wrapper",
      'attr' => $attrs['carousel_settings']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "width: fit-content;",
    ]);

    $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_cci_image_container",
        'attr' => $attrs['item_order']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "order: {$this->props['image_order']};",
    ]);

    $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_cc_title",
        'attr' => $attrs['item_order']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "order: {$this->props['title_order']};",
    ]);

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .df_cc_subtitle",
      'attr' => $attrs['item_order']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "order: {$this->props['subtitle_order']};",
    ]);

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .df_cc_content",
      'attr' => $attrs['item_order']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "order: {$this->props['content_order']};",
    ]);

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .df_cci_button_wrapper",
      'attr' => $attrs['item_order']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "order: {$this->props['button_order']};",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => ".difl_contentcarousel{$orderClass} .swiper-container-3d .swiper-slide-shadow-left",
      'attr' => $attrs['advanced_settings']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "background-image: linear-gradient(to left,{$this->props['coveflow_color_dark']}, {$this->props['coveflow_color_light']});",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => ".difl_contentcarousel{$orderClass} .swiper-container-3d .swiper-slide-shadow-right",
      'attr' => $attrs['advanced_settings']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "background-image: linear-gradient(to right,{$this->props['coveflow_color_dark']}, {$this->props['coveflow_color_light']});",
    ]);

    if (($this->props['large_active_dot'] ?? '') === 'on') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .swiper-pagination .swiper-pagination-bullet-active",
        'attr' => $attrs['dots']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "width: 40px; border-radius: 20px;",
      ]);
    }

    if ($this->props['dots_align']) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .swiper-pagination",
        'attr' => $attrs['dots']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['dots_align']};",
      ]);
    }

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .swiper-pagination",
      'attr' => $attrs['dots']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "top: {$this->props['dot_vertical_position']};",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .swiper-pagination span",
      'attr' => $attrs['dots']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "background: {$this->props['dots_color']} !important;",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet-active",
      'attr' => $attrs['dots']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "background: {$this->props['active_dots_color']} !important;",
    ]);

    if ($this->props['equal_height'] === 'on') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .difl_contentcarouselitem",
        'attr' => $attrs['carousel_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props) {

            if(isset($props['attrValue']['equal_height']) && $props['attrValue']['equal_height'] === 'on') {
                return "align-self: auto;";
            }
        },
      ]);
    }

    // Button Style
    $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .difl_contentcarouselitem .df_cci_button .df_cci_btn_icon.df_cci_btn_hover_off,{$orderClass} .difl_contentcarouselitem .df_cci_button:hover .df_cci_btn_icon.df_cci_btn_hover_on",
        'attr' => $attrs['cc_button']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "font-size: {$this->props['btn_icon_font_size']}; color: {$this->props['btn_icon_color']};",
    ]);

    if ($this->props['cc_button_button_fullwidth'] === 'on') {

      $btnTextAlign = $this->props['cc_button_button_align'] ?? 'center';
      $textIconP    = $btnTextAlign;

      if($btnTextAlign==='right'){
          $textIconP = 'flex-end';
      } else if ($btnTextAlign === 'left'){
          $textIconP = 'flex-start';
      }

      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} span.df_cci_btn_text_icon_wrap",
        'attr' => $attrs['cc_button']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "justify-content: {$textIconP};",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_cci_button",
        'attr' => $attrs['cc_button']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "width: 100%;",
      ]);

    }

    if ($this->props['btn_use_icon'] === 'on') {
      $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .df_cci_button_wrapper .df_cci_btn_icon",
        'attr'                => $attrs['btn_font_icon']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => self::get_font_style($this->props['btn_font_icon'] ?? '4'),
      ]);
    }

    $common_styles[] = CommonStyle::style([
      'selector'            => "{$orderClass} .swiper-button-prev:after",
      'attr'                => $attrs['arrow_prev_icon_font_icon']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => self::get_font_style($this->props['arrow_prev_icon_font_icon'] ?? '4'),
    ]);

    if(isset($this->props['arrow_prev_icon_use_icon']) && $this->props['arrow_prev_icon_use_icon'] === 'on') {
        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .df_cc_arrows .swiper-button-prev:after",
            'attr'                => $attrs['arrows']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "font-size: " . ( $props['attrValue']['arrow_prev_icon_icon_size'] ?? '' ) . " !important;",
            'important' => true
        ]);
    }

    $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .swiper-button-next:after",
        'attr'                => $attrs['arrow_next_icon_font_icon']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => self::get_font_style($this->props['arrow_next_icon_font_icon'] ?? '4'),
    ]);

    if(isset($this->props['arrow_next_icon_use_icon']) && $this->props['arrow_next_icon_use_icon'] === 'on') {
        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .df_cc_arrows .swiper-button-next:after",
            'attr'                => $attrs['arrows']['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "font-size: " . ( $props['attrValue']['arrow_next_icon_icon_size'] ?? '' ) . " !important;",
            'important' => true
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

  public function render($attrs, $content, $block, $render_slug) {
    if ($content === '') {
      return '<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Item</strong> to continue</h2>';
    }

    if ($this->props['use_lightbox'] === 'on') {
      wp_enqueue_script('lightgallery-script');
      // $this->add_classname('has_lightbox');
    }

    wp_enqueue_script('swiper-script');
    wp_enqueue_script('df-contentcarousel');
    // $this->additional_css_styles($render_slug);

    $order_class = $this->order_class; //self::get_module_order_class($render_slug);
    $order_number = $block->parsed_block['orderIndex']; // str_replace('_', '', str_replace($this->slug, '', $order_class));
    $class = '';
    $auto_play = !empty($this->props['autoplay']) ? $this->props['autoplay'] : 'off';
    $pause_hover = !empty($this->props['pause_hover']) ? $this->props['pause_hover'] : 'off';
    $auto_delay = !empty($this->props['autospeed']) ? $this->props['autospeed'] : '2000';
    // $item_spacing = !empty($this->props['item_spacing']) ? $this->props['item_spacing'] : '30px';

    $maxSlideDesktop= $this->props['item_desktop'] ?? '3';
		$maxSlideTablet = $this->props['item_tablet'] ?? $this->props['item_mid_device'] ?? '2';
		$maxSlidePhone  = $this->props['item_mobile'] ?? '1';

    // $maxSlideDesktop= $this->props['max_slide'] ?? '3';
		// $maxSlideTablet = $attrs['carousel_settings']['innerContent']['tablet']['value']['max_slide'] ?? $maxSlideDesktop;
		// $maxSlidePhone  = $attrs['carousel_settings']['innerContent']['phone']['value']['max_slide'] ?? $maxSlideTablet;
    $itemSpacingDesktop= $this->props['item_spacing'] ?? '30px';
		$itemSpacingTablet = $attrs['carousel_settings']['innerContent']['tablet']['value']['item_spacing'] ?? $itemSpacingDesktop;
		$itemSpacingPhone  = $attrs['carousel_settings']['innerContent']['phone']['value']['item_spacing'] ?? $itemSpacingTablet;

    $data = [
      'effect' => $this->props['carousel_type'],
      'desktop' => $maxSlideDesktop,
      'tablet' => $maxSlideTablet,
      'mobile' => $maxSlidePhone,
      'loop' => $this->props['loop'] === 'on' ? true : false,
      'item_spacing' => $itemSpacingDesktop,
      'item_spacing_tablet' => $itemSpacingTablet,
      'item_spacing_phone' => $itemSpacingPhone,
      'arrow' => $this->props['arrow'],
      'dots' => $this->props['dots'],
      'autoplay' => $auto_play,
      'autoplay_tablet' => !empty($this->props['autoplay_tablet']) ? $this->props['autoplay_tablet'] : $auto_play,
      'autoplay_phone' => !empty($this->props['autoplay_phone']) ? $this->props['autoplay_phone'] : $auto_play,
      'auto_delay' => $auto_delay,
      'auto_delay_tablet' => !empty($this->props['autospeed_tablet']) ? $this->props['autospeed_tablet'] : $this->props['autospeed'],
      'auto_delay_phone' => !empty($this->props['autospeed_phone']) ? $this->props['autospeed_phone'] : ($this->props['autospeed_tablet'] ? $this->props['autospeed_tablet'] : $this->props['autospeed']),
      'speed' => $this->props['speed'],
      'pause_hover' => $pause_hover,
      'pause_hover_tablet' => !empty($this->props['pause_hover_tablet']) ? $this->props['pause_hover_tablet'] : $pause_hover,
      'pause_hover_phone' => !empty($this->props['pause_hover_phone']) ? $this->props['pause_hover_phone'] : $pause_hover,
      'centeredSlides' => $this->props['centered_slides'],
      'order' => $order_number,
      'use_lightbox' => $this->props['use_lightbox'],
      'use_lightbox_title' => $this->props['use_lightbox_title']
    ];
    if ($this->props['carousel_type'] === 'coverflow') {
      $data['slideShadows'] = $this->props['coverflow_shadow'];
      $data['rotate'] = $this->props['coverflow_rotate'];
      $data['stretch'] = $this->props['coverflow_stretch'];
      $data['depth'] = $this->props['coverflow_depth'];
      $data['modifier'] = $this->props['coverflow_modifier'];
    }

    // arrow position classes
    if ($this->props['arrow'] === 'on') {
      $arrow_position = '' !== $this->props['arrow_position'] ? $this->props['arrow_position'] : 'middle';
      $class .= ' arrow-' . $arrow_position;
    }

    return sprintf(
      '<div class="df_cc_container%8$s" data-settings=\'%2$s\' data-item="%5$s" data-itemtablet="%6$s" data-itemphone="%7$s">
                <div class="df_cc_inner_wrapper">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            %1$s
                        </div>
                    </div>
                    %3$s
                </div>
                %4$s
            </div>',
      et_core_sanitized_previously($content),
      wp_json_encode($data),
      $this->df_cc_arrow($order_number),
      $this->df_cc_dots($order_number),
      $maxSlideDesktop,
      $maxSlideTablet,
      $maxSlidePhone,
      $class
    );
  }

  public function df_cc_arrow($order_number) {
    $prev_icon = $this->props['arrow_prev_icon_use_icon'] === 'on' && isset($this->props['arrow_prev_icon_font_icon']) && !empty($this->props['arrow_prev_icon_font_icon']) ?
      Utils::process_font_icon($this->props['arrow_prev_icon_font_icon']) : '4';
    $next_icon = $this->props['arrow_next_icon_use_icon'] === 'on' && isset($this->props['arrow_next_icon_font_icon']) && !empty($this->props['arrow_next_icon_font_icon']) ?
      Utils::process_font_icon($this->props['arrow_next_icon_font_icon']) : '5';

    return $this->props['arrow'] === 'on' ? sprintf('
            <div class="df_cc_arrows">
                <div class="swiper-button-next cc-next-%1$s" data-icon="%3$s"></div>
                <div class="swiper-button-prev cc-prev-%1$s" data-icon="%2$s"></div>
            </div>
        ', $order_number, $prev_icon, $next_icon) : '';
  }

  public function df_cc_dots($order_number) {
    return $this->props['dots'] === 'on' ? sprintf('<div class="swiper-pagination cc-dots-%1$s"></div>', $order_number) : '';
  }


  public function df_arrow_pos_styles($value) {

    $options = array(
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

}
