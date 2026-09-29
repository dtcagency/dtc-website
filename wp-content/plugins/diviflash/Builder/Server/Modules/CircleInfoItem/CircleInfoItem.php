<?php
namespace DIFL\Server\Modules\CircleInfoItem;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class CircleInfoItem implements DependencyInterface {
  use RenderCallback;

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/circleinfoitem')->customCssFields;
  }

  public function load() {
    $module_json_folder_path = DIFL_MODULES_JSON_PATH . 'CircleInfoItem/';

    add_action(
      'init',
      function () use ($module_json_folder_path) {
        ModuleRegistration::register_module(
          $module_json_folder_path,
          [
            'render_callback' => [CircleInfoItem::class, 'render_callback'],
          ]
        );
      }
    );
  }
}
