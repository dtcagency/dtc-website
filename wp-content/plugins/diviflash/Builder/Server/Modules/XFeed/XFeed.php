<?php
namespace DIFL\Server\Modules\XFeed;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class XFeed implements DependencyInterface {
  use RenderCallback;

  public function load() {
    $module_json_folder_path = DIFL_MODULES_JSON_PATH . 'XFeed/';

    add_action(
      'init',
      function () use ($module_json_folder_path) {
        ModuleRegistration::register_module(
          $module_json_folder_path,
          [
            'render_callback' => [self::class, 'render_callback'],
          ]
        );
      }
    );
  }
}
