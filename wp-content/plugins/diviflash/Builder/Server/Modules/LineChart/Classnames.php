<?php
namespace DIFL\Server\Modules\LineChart;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;

trait Classnames {

  public static function classnames($args) {
    $classnames_instance = $args['classnamesInstance'];
    $attrs               = $args['attrs'];

    $text_options_classnames = TextClassnames::text_options_classnames($attrs['module']['advanced']['text'] ?? []);

    if ($text_options_classnames) {
      $classnames_instance->add($text_options_classnames, true);
    }
    $classnames_instance->add(
      ElementClassnames::classnames(
        [
          'attrs' => array_merge(
            $attrs['module']['decoration'] ?? [],
            [
              'link' => $attrs['module']['advanced']['link'] ?? [],
            ]
          ),
        ]
      )
    );
  }
}