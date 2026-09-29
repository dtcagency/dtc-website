<?php
namespace DIFL\Server\Modules\BusinessHours;

if (! defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

trait Styles
{

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/businesshours')->customCssFields;
  }

  public static function styles($args) {
    $attrs        = $args['attrs'] ?? [];
    $parent_attrs = $args['parentAttrs'] ?? [];
    $order_class  = $args['orderClass'];
    $elements     = $args['elements'];
    $settings     = $args['settings'] ?? [];
    $icon_selector = "{$order_class} .et-pb-icon";

    Style::add(
      [
        'id'            => $args['id'],
        'name'          => $args['name'],
        'orderIndex'    => $args['orderIndex'],
        'storeInstance' => $args['storeInstance'],
        'styles'        => [
          // Module.
            $elements->style(
                [
                    'attrName'   => 'module',
                    'styleProps' => [
                        'disabledOn' => [
                            'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                        ],
                        'background' => [
                            'featureSelectors' => [
                                'mask'    => [
                                    'desktop' => [
                                        'value' => "{$order_class} .et_pb_background_mask",
                                    ],
                                ],
                                'pattern' => [
                                    'desktop' => [
                                        'value' => "{$order_class} .et_pb_background_pattern",
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ),

          CssStyle::style(
            [
              'selector'  => $args['orderClass'],
              'attr'      => $attrs['css'] ?? [],
              'cssFields' => self::custom_css(),
            ]
          ),

          CommonStyle::style(
            [
              'selector' => "{$order_class} .df_bh_day",
              'attr'     => $attrs['day_width']['innerContent'] ?? '',
              'property' => 'max-width',
            ]
          ),
          CommonStyle::style(
            [
              'selector' => "{$order_class} .df_bh_time",
              'attr'     => $attrs['day_width']['innerContent'] ?? [],
              'declarationFunction' => [self::class, 'df_day_width_calc'],
            ]
          ),

          // border, font, boxShadow, background, Spacing style.
          $elements->style(['attrName' => 'day',]),
          $elements->style(['attrName' => 'time',]),
          $elements->style(['attrName' => 'title',]),
          $elements->style(['attrName' => 'item',]),
          $elements->style(['attrName' => 'start_time',]),
          $elements->style(['attrName' => 'end_time',]),
          $elements->style(['attrName' => 'time_separetor',]),

          CommonStyle::style(
            [
              'selector' => "{$order_class} .df_bh_start_time",
              'attr'     => $attrs['start_time_background_color']['innerContent'] ?? '',
              'property' => 'background-color',
            ]
          ),
          CommonStyle::style(
            [
              'selector' => "{$order_class} .df_bh_end_time",
              'attr'     => $attrs['end_time_background_color']['innerContent'] ?? '',
              'property' => 'background-color',
            ]
          ),
          CommonStyle::style(
            [
              'selector' => "{$order_class} .df_bh_time_separetor",
              'attr'     => $attrs['time_separetor_background_color']['innerContent'] ?? '',
              'property' => 'background-color',
            ]
          ),

          $elements->style(['attrName' => 'main_wrapper_spacing',]),
          $elements->style(['attrName' => 'item_wrapper_spacing',]),
          $elements->style(['attrName' => 'title_wrapper_spacing',]),
          $elements->style(['attrName' => 'day_time_separetor_design',]),
          // $elements->style(['attrName' => 'day_time_separetor_margin',]),

          CommonStyle::style(
            [
              'selector' => "{$order_class} .df_bh_day_time_separator hr",
              'attr'     => $attrs['day_time_separator_color']['innerContent'] ?? '',
              'property' => 'border-color',
            ]
          ),
          CommonStyle::style(
            [
              'selector' => "{$order_class} .df_bh_day_time_separator hr",
              'attr'     => $attrs['day_time_separator_hight']['innerContent'] ?? '',
              'property' => 'border-bottom-width',
            ]
          ),
          CommonStyle::style(
            [
              'selector' => ".difl_businesshours{$order_class} .df_bh_day_time_separator hr",
              'attr'     => $attrs['day_time_separator_style']['innerContent'] ?? '',
              'property' => 'border-style',
            ]
          ),

          ...\difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ),
        ],
      ]
    );
  }

  public static function df_day_width_calc(array $args): string {
    $time_width_cal = $args['attrValue'] ? 'max-width: calc(100% - '.$args['attrValue'].');' : '';
    return $time_width_cal;
  }

}
