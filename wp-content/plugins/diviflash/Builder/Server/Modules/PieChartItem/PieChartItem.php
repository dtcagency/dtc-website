<?php
namespace DIFL\Server\Modules\PieChartItem;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

class PieChartItem implements DependencyInterface {
  use RenderCallback;

  public function load() {
    $module_json_folder_path = DIFL_MODULES_JSON_PATH . 'PieChartItem/';

    add_action(
      'init',
      function () use ($module_json_folder_path) {
        ModuleRegistration::register_module(
          $module_json_folder_path,
          [
            'render_callback' => [PieChartItem::class, 'render_callback'],
          ]
        );
      }
    );
  }
}
