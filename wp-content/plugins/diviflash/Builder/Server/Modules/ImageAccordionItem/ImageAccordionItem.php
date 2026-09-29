<?php

namespace DIFL\Server\Modules\ImageAccordionItem;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Handler\Fa_Icon_Handler;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;


class ImageAccordionItem implements DependencyInterface {
  use ModuleUtil;
  use HasProps;
  use StyleUtil;
  // use Fa_Icon_Handler;
  protected $config_file = 'ImageAccordionItem';

  public static $content;
  protected $module_credits = array(
    'module_uri' => '',
    'author'     => 'DiviFlash',
    'author_uri' => '',
  );

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/imageaccordionitem')->customCssFields;
  }

  public function df_render_image_icon($attrs) {
	  if ( $this->props['use_icon'] !== 'on' ) {
		  return '';
	  }

    if (isset($this->props['use_icon']) && $this->props['use_icon'] === 'on' && $this->props['use_image_as_icon'] !== 'on') {

      $font_icon   = $this->props['font_icon'] ?? '';
			$processIcon = Utils::process_font_icon( $this->props['font_icon'] ) ?? '1';

      return sprintf(
        '<span class="et-pb-icon df-image-accordion-icon">%1$s</span>',
        isset($font_icon) && $font_icon !== '' ? esc_attr($processIcon) : '1'
      );

    } else if (isset($this->props['use_image_as_icon']) && $this->props['use_image_as_icon'] === 'on') {

      $src = 'src';
      $image_alt = $this->props['image_alt_text'] !== '' ? $this->props['image_alt_text']  : df_image_alt_by_url($this->props['image_as_icon']);
      $image_url = $this->props['image_as_icon'];

      return sprintf(
        '<img class="df-image-accordion-icon" %3$s="%1$s" alt="%2$s" />',
        $this->props['image_as_icon'],
        $image_alt,
        $src
      );

    }
  }

  public function get_common_styles($args) {
    $attrs         = $args['attrs'] ?? [];
    $orderClass    = $args['orderClass'] ?? '';
    $elements      = $args['elements'];
    $settings      = $args['settings'] ?? [];
    $styles        = [];
    $common_styles = [];

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
    $common_styles[] = $elements->style(['attrName' => 'mainContent',]);
    $common_styles[] = $elements->style(['attrName' => 'df_button',]);
    $common_styles[] = $elements->style(['attrName' => 'icon',]);
    $common_styles[] = $elements->style(['attrName' => 'title',]);
    $common_styles[] = $elements->style(['attrName' => 'sub_title',]);
    $common_styles[] = $elements->style(['attrName' => 'ia_image',]);

    (($this->props['use_icon'] ?? 'off') === 'on') ?
      $common_styles[] = CommonStyle::style([
        'selector' => ".difl_imageaccordion {$orderClass} .et-pb-icon.df-image-accordion-icon",
        'attr'     => $attrs['icon']['innerContent'] ?? [],
        // 'declarationFunction' => fn($props) => "font-size: {$props['attrValue']['icon_size']};"
        'declarationFunction' => function($props) {
          if(isset($props['attrValue']['icon_size'])){
            return 'font-size: ' . $props['attrValue']['icon_size'] . ';';
          }
          return '';
        }
      ])
      : '';
    (($this->props['use_icon'] ?? 'off') === 'on') ?
      $common_styles[] = CommonStyle::style([
        'selector' => ".difl_imageaccordion {$orderClass} .et-pb-icon.df-image-accordion-icon",
        'attr'     => $attrs['icon']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "color: {$this->props['icon_color']};"
      ])
      : '';

    (($this->props['use_icon'] ?? 'off') === 'on') ?
      $common_styles[] = CommonStyle::style([
        'selector' => ".difl_imageaccordion {$orderClass} .et-pb-icon.df-image-accordion-icon",
        'attr'     => $attrs['icon']['innerContent'] ?? [],
        'declarationFunction' => function () {
                return self::get_font_style( $this->props['font_icon'] );
            }
      ])
      : '';
    $common_styles[] = CommonStyle::style([
      'selector' => ".difl_imageaccordion {$orderClass} img.df-image-accordion-icon",
      'attr'     => $attrs['icon']['innerContent'] ?? [],
      // 'declarationFunction' => fn($props) => "width: {$props['attrValue']['image_as_icon_width']};"
      'declarationFunction' => function($props) {
        if(isset($props['attrValue']['image_as_icon_width'])){
          return 'width: ' . $props['attrValue']['image_as_icon_width'] . ';';
        }
        return '';

      }
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => ".difl_imageaccordion {$orderClass} .content",
      'attr'     => $attrs['mainContent']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "text-align: {$this->props['content_alignment']};"
    ]);
    (($this->props['vertical_align'] ?? '')) ?
      $common_styles[] = CommonStyle::style([
        'selector' => ".difl_imageaccordion {$orderClass} .overlay_wrapper",
        'attr'     => $attrs['mainContent']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "justify-content: {$this->props['vertical_align']};"
      ])
      : '';

      $common_styles[] = CommonStyle::style([
        'selector' => ".difl_imageaccordion {$orderClass} .content",
        'attr'     => $attrs['df_button']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['df_button']};"
      ]);

    {/**
        * Support for border-radius
        * In Divi-4, if user change radius property, an additional css autometically added [overflow:hidden] to that selector
        * But in 5??  To resolve this issues. These are the fields which will generate overflow hidden
    */}
    $common_styles [] = CommonStyle::style( [
            'selector' => "{$orderClass}",
            'attr' => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function ( $props ) {
                if($props['attrValue']['radius']){
                    return 'overflow: hidden !important';
                }
            }
        ]
    );
    $common_styles [] = CommonStyle::style( [
            'selector' => "{$orderClass} .df_ia_button",
            'attr' => $attrs['button']['decoration']['border'] ?? null,
            'declarationFunction' => function ( $props ) {
                if($props['attrValue']['radius']){
                    return 'overflow: hidden !important';
                }
            }
        ]
    );
    $common_styles [] = CommonStyle::style( [
            'selector' => "{$orderClass} .df-image-accordion-icon",
            'attr' => $attrs['icon']['decoration']['border'] ?? null,
            'declarationFunction' => function ( $props ) {
                if($props['attrValue']['radius']){
                    return 'overflow: hidden !important';
                }
            }
        ]
    );

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

public function render($attrs, $content, $render_slug) {
    // $this->handle_fa_icon();
    // $this->additional_css_styles($render_slug);
    $icon_html = $this->df_render_image_icon($attrs);
    $title_level  =  esc_attr($this->props['title_tag']);
    $sub_title_level  =  esc_attr($this->props['sub_title_tag']);

    $title_html = $this->props['title'] !== '' ?
      sprintf('<%1$s  class="df_ia_title anime_wrap">%2$s</%1$s >', $title_level, $this->props['title']) : '';
    $sub_title_html = $this->props['sub_title'] !== '' ?
      sprintf('<%1$s  class="df_ia_sub_title anime_wrap">%2$s</%1$s >', $sub_title_level, $this->props['sub_title']) : '';

    $pattern = "/<p[^>]*><\\/p[^>]*>/";
    $description = preg_replace($pattern, '', $this->props['description']);
    $description = $this->props['description'] !== '' ?
      sprintf('<div class="df_ia_description anime_wrap">%1$s</div>', $description) : '';
    $content = sprintf('<div class="content">%5$s %1$s %2$s %3$s %4$s </div>', $title_html, $sub_title_html, $description, $this->df_render_button('ia_button'), $icon_html);


    $overlay_wrapper = sprintf('<div class="overlay_wrapper">%1$s</div>', $content);

    $image = sprintf('<div class="accordion-item">
                        <div class="overlay">
                            %1$s
                        </div>
                    </div>', $overlay_wrapper);

    return sprintf('<div class="et_pb_module_inner"><div class="df_iai_container">%1$s</div></div>', $image);
  }

  /**
   * Render button HTML markup
   *
   * @param String $key
   * @return String HTML markup of the button
   */
  public function df_render_button($key) {
    $text = isset($this->props[$key . '_button_text']) ? $this->props[$key . '_button_text'] : '';
    $url = isset($this->props[$key . '_button_url']) ? $this->props[$key . '_button_url'] : '';
    $target = $this->props[$key . '_button_url_new_window'] === 'on'  ?
      'target="_blank"' : '';
    if ($text !== '' || $url !== '') {
      return sprintf('<div class="df_ia_button_wrapper anime_wrap">
                <a class="df_ia_button" href="%1$s" %3$s>%2$s</a>
            </div>', esc_attr($url), esc_html($text), $target);
    } else {
      return '';
    }
  }
}
