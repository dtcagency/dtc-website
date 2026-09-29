<?php
namespace DIFL\Server\Modules\FacebookFeed;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait Styles {

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/facebookfeed')->customCssFields;
  }

  public static function styles($args) {
    $attrs        = $args['attrs'] ?? [];
    $order_class  = $args['orderClass'];
    $elements     = $args['elements'];
    $settings     = $args['settings'] ?? [];

    Style::add(
      [
        'id'            => $args['id'],
        'name'          => $args['name'],
        'orderIndex'    => $args['orderIndex'],
        'storeInstance' => $args['storeInstance'],
        'styles'        => [
          $elements->style(
            [
              'attrName'   => 'module',
              'styleProps' => [
                'disabledOn' => [
                  'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                ],
                'background' => [
                  'featureSelectors' => [
                    'mask' => [
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
        ],
      ]
    );
  }
}
