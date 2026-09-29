<?php
/**
 * ImageAccordion Module class.
 *
 * @package DIFL\Server\Modules\ImageAccordion;
 */
namespace DIFL\Server\Modules\ImageAccordion;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Assets\StaticCSS;
use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Framework\Breakpoint\Breakpoint;

// use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use DIFL\Handler\Fa_Icon_Handler;

class ImageAccordion implements DependencyInterface {

  use ModuleUtil;
  use HasProps;
  use StyleUtil;

  protected $config_file = 'ImageAccordion';

  public static $content;
  protected $order_class;

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/imageaccordion')->customCssFields;
  }

  public function before_render() {
    $this->props['event_type__hover'] = '1px||||false|false';
    $this->props['event_type__hover_enabled'] = 'on|hover';
  }

  public function get_common_styles($args) {
    $attrs         = $args['attrs'] ?? [];
    $orderClass    = $args['orderClass'] ?? '';
    $elements      = $args['elements'];
		$settings      = $args['settings'] ?? [];
    $styles        = [];
    $common_styles = [];

		$state      = Breakpoint::get_style_breakpoint_settings();
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
    // Element: Content.
    $common_styles[] = $elements->style(['attrName' => 'item',]);
    $common_styles[] = $elements->style(['attrName' => 'content',]);
    $common_styles[] = $elements->style(['attrName' => 'icon',]);
    $common_styles[] = $elements->style(['attrName' => 'title',]);
    $common_styles[] = $elements->style(['attrName' => 'sub_title',]);
    $common_styles[] = $elements->style(['attrName' => 'description',]);
    $common_styles[] = $elements->style(['attrName' => 'df_button',]);
    $common_styles[] = $elements->style(['attrName' => 'ia_overlay_background',]);
    $common_styles[] = $elements->style(['attrName' => 'ia_active_overlay_background',]);
    $common_styles[] = $elements->style([
      'attrName'   => 'item_spacing',
      'styleProps' => [
        'selector' => "{$orderClass} .difl_imageaccordionitem"
      ]
    ]);
    $common_styles[] = $elements->style([
      'attrName'   => 'first_item_spacing',
      'styleProps' => [
        'selector' => "{$orderClass} .difl_imageaccordionitem:first-child"
      ]
    ]);
    $common_styles[] = $elements->style([
      'attrName'   => 'module_wrapper',
      'styleProps' => [
        'selector' => "{$orderClass} .df_ap_person_wrapper"
      ]
    ]);

    /*
    if ($this->props['image_force_to_fullwidth'] === 'on' && $this->props['enable_alternative_photo'] === 'off') {
      $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .c4-izmir",
        'attr'                => $attrs['image_force_to_fullwidth']['innerContent'] ?? [],
        'declarationFunction' => function () {
          return "display:block;";
        },
      ]);
    }
    ($this->props['image_scale_type'] === 'c4-image-rotate-left') ?
      $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .df_ap_person_container:hover img,{$orderClass} .df_ap_person_container:focus img",
        'attr'                => $attrs['image_scale_value']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "transform:scale({$this->props['image_scale_value']}) rotate(-15deg);",
      ])
      : '';
    */

    ($this->props['vertical_at_mobile'] !== 'off' && $this->props['accordion_type'] === 'horizontal') &&
      $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .item-wrapper.vertical_at_mobile",
        'attr'                => $attrs['accordionSetting']['innerContent'] ?? [],
        'atRules'             => '@media only screen and ( max-width: 767px )',
        'declarationFunction' => fn($props) => "flex-direction: column;"
      ]);
    
      // $common_styles[] = CommonStyle::style([
			// 	'selector'            => "{$order_class} .difl_advanced_button_container .difl_adv_btn_wrapper .difl_adv_btn_icon",
			// 	'attr'                => $attrs['contents']['innerContent'] ?? null,
			// 	'declarationFunction' => function ( $value ) {
			// 		$icon = $value['attrValue']['button_icon'] ?? null;
			// 		return self::get_font_style( $icon );
			// 	},
			// ]);

    $common_styles[] = CommonStyle::style([
      'selector'            => "{$orderClass} .item-wrapper",
      'attr'                => $attrs['accordionSetting']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "opacity: 1;
                visibility: visible;
                transform: scale(1);
                transform-origin: 100% 50%;
                display: flex;",
    ]);

    $common_styles[] = CommonStyle::style(
      [
        'selector'            => "{$orderClass} .item-wrapper",
        'attr'                => $attrs['accordionSetting']['innerContent'] ?? [],
        // 'declarationFunction' => fn($props) => "height: {$props['attrValue']['accordion_container_height']} !important;"
        'declarationFunction' => function($props) {
          if(isset($props['attrValue']['accordion_container_height'])){
            return 'height: ' . $props['attrValue']['accordion_container_height'] . ' !important;';
          }
          return '';
        }
      ]
    );
    ($this->props['event_type'] === 'hover') ?
      $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .item-wrapper.vertical_at_mobile",
        'attr'                => $attrs['accordionSetting']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "flex: 10;",
      ])
      : '';

    $common_styles[] = CommonStyle::style(
      [
        'selector' => "{$orderClass} .content",
        'attr'     => $attrs['content']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['content_alignment']};"
      ]
    );

    $common_styles[] = CommonStyle::style([
      'selector'            => "{$orderClass} .et-pb-icon.df-image-accordion-icon",
      'attr'                => $attrs['icon']['innerContent'] ?? [],
      'declarationFunction' => function ($props) {
            if(isset($props['attrValue']['icon_size'])){
              return "font-size: {$props['attrValue']['icon_size']};";
            }
            return '';
        }
    ]);
    $common_styles[] = CommonStyle::style(
      [
        'selector' => "{$orderClass} .et-pb-icon.df-image-accordion-icon",
        'attr'     => $attrs['icon']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "color: {$this->props['icon_color']};"
      ]
    );

    $common_styles[] = CommonStyle::style([
      'selector'            => "{$orderClass} img.df-image-accordion-icon",
      'attr'                => $attrs['icon']['innerContent'] ?? [],
      'declarationFunction' => function ($props) {
            if(isset($props['attrValue']['image_as_icon_width'])){
              return "width: {$props['attrValue']['image_as_icon_width']};";
            }
            return '';
        }
    ]);

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .overlay_wrapper",
      'attr'     => $attrs['content']['innerContent'] ?? [],
      'declarationFunction' => fn() => "justify-content: {$this->props['vertical_align']};"
    ]);

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

  public function render($attrs, $content, $block, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '') {

    wp_enqueue_script('animejs');
    wp_enqueue_script('df-imageaccordion');

    $order_class   = $this->order_class; // self::get_module_order_class($render_slug);
    $order_number  = $block->parsed_block['orderIndex']; //str_replace('_', '', str_replace($this->slug, '', $order_class));
    
    $active_on_desktop = $this->props['active_item_order_number'];
    $active_on_tablet = $attrs['accordionSetting']['innerContent']['tablet']['value']['active_item_order_number'] ?? $active_on_desktop;
    $active_on_phone = $attrs['accordionSetting']['innerContent']['phone']['value']['active_item_order_number'] ?? $active_on_desktop;

    
    $active_item   = $this->props['active_on_first_time'] !== 'off' ? $active_on_desktop : '';
    $active_item_tablet = $this->props['active_on_first_time'] !== 'off' ? $active_on_tablet : '';
    $active_item_phone = $this->props['active_on_first_time'] !== 'off' ? $active_on_phone : '';

    $mobile_accodion_class = $this->props['vertical_at_mobile'] !== 'off' ? ' vertical_at_mobile' : '';
    
    $data = [
      'accordion_type' => $this->props['accordion_type'],
      'event_type' => $this->props['event_type'],
      'active_on_first_time' => $this->props['active_on_first_time'],
      'active_item' => $active_item,
      'active_item_tablet' => $active_item_tablet,
      'active_item_phone' => $active_item_phone,
      'outer_click_close_item' => $this->props['outer_click_close_item'],
      'order' => $order_number
    ];
    $class = '';
    if (isset($this->props['accordion_type'])) {
      $class .= ' ' . $this->props['accordion_type'];
    }
    if ($mobile_accodion_class !== '') {
      $class .= $mobile_accodion_class;
    }
    if (isset($this->props['event_type'])) {
      $class .= ' ' . $this->props['event_type'];
    }

    $animation_data = array(
      'enable_animation'          => $this->props['enable_animation'],
      'content_animation'         => $this->props['content_animation'],
      'duration'                  => $this->props['duration'],
      'delay'                     => $this->props['delay'],
      'animation_function'        => $this->props['animation_function'],
      'module_class'              => $order_class,
      'enable_stagger'            => $this->props['enable_stagger'],
      'stagger'                   => $this->props['enable_stagger'] === 'on' ? $this->props['stagger'] : 100
    );

    return sprintf(
      '<div class="df_ia_container" data-settings=\'%2$s\' data-animation=\'%4$s\'>
            <div class="df_ia_inner_wrapper">
                <div class="item-container">
                    <div class="item-wrapper %3$s">
                        %1$s
                    </div>
                </div>
            </div>
        </div>',
      et_core_sanitized_previously($content),
      wp_json_encode($data),
      $class,
      wp_json_encode($animation_data)
    );
  }
}