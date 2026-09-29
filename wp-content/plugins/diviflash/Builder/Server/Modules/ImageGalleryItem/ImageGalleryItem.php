<?php
namespace DIFL\Server\Modules\ImageGalleryItem;

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
use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use DIFL\Server\Traits\ModuleUtil;


class ImageGalleryItem implements DependencyInterface {

  use ModuleUtil;
  use HasProps;
  use StyleUtil;

  protected $config_file = 'ImageGalleryItem';
  public static $content;
  protected $order_class;

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/imagegalleryitem')->customCssFields;
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

   /*
    $common_styles[] = $elements->style(['attrName' => 'ia_active_overlay_background',]);
    $common_styles[] = $elements->style([
      'attrName'   => 'item_spacing',
      'styleProps' => [
        'selector' => "{$orderClass} .difl_imageaccordionitem"
      ]
    ]);
    
    $common_styles[] = CommonStyle::style(
      [
        'selector' => "{$orderClass} .et-pb-icon.df-image-accordion-icon",
        'attr'     => $attrs['icon_color']['innerContent'] ?? [],
        'property' => 'color'
      ]
    );

    (($attrs['image_scale_type']['innerContent']['desktop']['value'] ?? 'no-image-scale') === 'c4-image-rotate-left') ?
      $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .df_ap_person_container:hover img,{$orderClass} .df_ap_person_container:focus img",
        'attr'                => $attrs['image_scale_value']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "transform:scale({$props['attrValue']}) rotate(-15deg);",
      ])
      : '';

   */
    
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
    // $this->additional_css_styles($render_slug);

    $_jsonstring = [
      "gallery_title" => sanitize_text_field($this->props['gallery_title']),
      "gallery_ids" => $this->props['gallery_ids']
    ];

    return wp_json_encode($_jsonstring) . ',';
  }
}
