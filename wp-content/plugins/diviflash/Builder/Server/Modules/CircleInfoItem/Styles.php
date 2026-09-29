<?php
namespace DIFL\Server\Modules\CircleInfoItem;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait Styles {

  public static function styles($args) {
    $attrs        = $args['attrs'] ?? [];
    $parent_attrs = $args['parentAttrs'] ?? [];
    $order_class  = $args['orderClass'];
    $elements     = $args['elements'];
    $settings     = $args['settings'] ?? [];

    $override = $attrs['item_icon_override_settings']['innerContent']['desktop']['value']['override_icon_style'] ?? 'off';

    $override_styles = [];

    if ($override === 'on') {
      $override_styles[] = CommonStyle::style(
        [
          'selector' => ".difl_circleinfo {$order_class} .df_circle_info_icon .et-pb-icon",
          'attr'     => $attrs['item_icon_override_settings']['innerContent'] ?? [],
          'declarationFunction' => [self::class, 'custom_icon_color_style'],
        ]
      );

      $override_styles[] = CommonStyle::style(
        [
          'selector' => ".difl_circleinfo {$order_class} .df_circle_info_icon",
          'attr'     => $attrs['item_icon_override_settings']['innerContent'] ?? [],
          'declarationFunction' => [self::class, 'custom_icon_bg_style'],
        ]
      );

      $override_styles[] = CommonStyle::style(
        [
          'selector' => ".difl_circleinfo {$order_class}.active .df_circle_info_icon .et-pb-icon",
          'attr'     => $attrs['item_icon_override_settings']['innerContent'] ?? [],
          'declarationFunction' => [self::class, 'custom_active_icon_color_style'],
        ]
      );

      $override_styles[] = CommonStyle::style(
        [
          'selector' => ".difl_circleinfo {$order_class}.active .df_circle_info_icon",
          'attr'     => $attrs['item_icon_override_settings']['innerContent'] ?? [],
          'declarationFunction' => [self::class, 'custom_active_icon_bg_style'],
        ]
      );
    }

    Style::add(
      [
        'id'            => $args['id'],
        'name'          => $args['name'],
        'orderIndex'    => $args['orderIndex'],
        'storeInstance' => $args['storeInstance'],
        'styles'        => array_merge(
          [
            $elements->style(
              [
                'attrName'   => 'module',
                'styleProps' => [
                  'disabledOn' => [
                    'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                  ],
                ],
              ]
            ),
            $elements->style(['attrName' => 'item_background']),
            $elements->style(['attrName' => 'item_title']),
            $elements->style(['attrName' => 'item_description']),
          ],
          $override_styles
        ),
      ]
    );
  }

  public static function custom_icon_color_style(array $args): string {
    $color = $args['attrValue']['custom_icon_color'] ?? '';
    return $color ? "color: {$color};" : '';
  }

  public static function custom_icon_bg_style(array $args): string {
    $bg = $args['attrValue']['custom_icon_bg_color'] ?? '';
    return $bg ? "background-color: {$bg};" : '';
  }

  public static function custom_active_icon_color_style(array $args): string {
    $color = $args['attrValue']['custom_active_icon_color'] ?? '';
    return $color ? "color: {$color};" : '';
  }

  public static function custom_active_icon_bg_style(array $args): string {
    $bg = $args['attrValue']['custom_active_icon_bg_color'] ?? '';
    return $bg ? "background-color: {$bg};" : '';
  }
}
