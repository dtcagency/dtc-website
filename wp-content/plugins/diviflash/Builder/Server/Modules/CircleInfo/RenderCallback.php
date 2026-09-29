<?php
namespace DIFL\Server\Modules\CircleInfo;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallback {
  use Classnames;
  use Styles;
  use ScriptData;

  public static function render_callback($attrs, $content, $block, $elements) {
    wp_enqueue_script('df_circle_info');

    $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
      function ($inner_block) {
        return $inner_block['id'];
      },
      $block->parsed_block['innerBlocks']
    ) : [];

    $parent       = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
    $parent_attrs = $parent->attrs ?? [];

    // Extract settings
    $trigger_mode     = $attrs['circle_settings']['innerContent']['desktop']['value']['trigger_mode'] ?? 'click';
    $content_animation = $attrs['circle_settings']['innerContent']['desktop']['value']['content_animation'] ?? 'fade';
    $start_angle      = $attrs['circle_settings']['innerContent']['desktop']['value']['circle_start_angle'] ?? '-90';
    $item_distance    = $attrs['circle_settings']['innerContent']['desktop']['value']['item_distance'] ?? '50';
    // Collect responsive item_distance values for all breakpoints
    $item_distance_responsive = [];
    $responsive_breakpoints = ['phone', 'phoneWide', 'tablet', 'tabletWide', 'widescreen', 'ultraWide'];
    foreach ($responsive_breakpoints as $bp) {
      $val = $attrs['circle_settings']['innerContent'][$bp]['value']['item_distance'] ?? '';
      if (!empty($val)) {
        $item_distance_responsive[$bp] = $val;
      }
    }
    $enable_autoplay  = $attrs['circle_settings']['innerContent']['desktop']['value']['enable_autoplay'] ?? 'off';
    $autoplay_speed   = $attrs['circle_settings']['innerContent']['desktop']['value']['autoplay_speed'] ?? '3000';
    $autoplay_interval   = $attrs['circle_settings']['innerContent']['desktop']['value']['autoplay_interval'] ?? '0';
    $pause_on_hover   = $attrs['circle_settings']['innerContent']['desktop']['value']['autoplay_pause_on_hover'] ?? 'on';
    $autoplay_rotation = $attrs['circle_settings']['innerContent']['desktop']['value']['autoplay_rotation'] ?? 'off';
    $rotation_direction = $attrs['circle_settings']['innerContent']['desktop']['value']['rotation_direction'] ?? 'cw';
    $rotation_speed    = $attrs['circle_settings']['innerContent']['desktop']['value']['rotation_speed'] ?? '600';
    $default_active   = $attrs['circle_settings']['innerContent']['desktop']['value']['default_active_item'] ?? '1';
    $mobile_layout    = $attrs['mobile_settings']['innerContent']['desktop']['value']['mobile_layout'] ?? 'list';
    $mobile_breakpoint = $attrs['mobile_settings']['innerContent']['desktop']['value']['mobile_breakpoint'] ?? '768';
    $center_title     = 'Interactive Circle';
    $center_desc      = 'Select an item to view details';

    $data_settings = wp_json_encode([
      'trigger_mode'            => $trigger_mode,
      'enable_autoplay'         => $enable_autoplay,
      'autoplay_speed'          => $autoplay_speed,
      'autoplay_interval'          => $autoplay_interval,
      'autoplay_pause_on_hover' => $pause_on_hover,
      'autoplay_rotation'       => $autoplay_rotation,
      'rotation_direction'      => $rotation_direction,
      'rotation_speed'          => $rotation_speed,
      'content_animation'       => $content_animation,
      'circle_start_angle'      => $start_angle,
      'item_distance'           => $item_distance,
      'item_distance_responsive' => $item_distance_responsive,
      'default_active_item'     => $default_active,
      'mobile_layout'           => $mobile_layout,
      'mobile_breakpoint'       => $mobile_breakpoint,
    ]);

    $default_center = sprintf(
      '<div class="df_ci_center_content active" data-source="default">
        <div class="df_ci_content_inner">
          <div class="df_ci_title">%s</div>
          <div class="df_ci_description">%s</div>
        </div>
      </div>',
      esc_html($center_title),
      wp_kses_post($center_desc)
    );

    $mobile_class = ($mobile_layout === 'list') ? ' mobile_list' : '';
    $rotation_class = ($enable_autoplay === 'on' && $autoplay_rotation === 'on') ? ' autoplay_rotation' : '';

    if (empty($children_ids)) {
      $circle_html = '<div class="df_circle_info_wrapper"><div class="difl__empty"><div class="difl__empty__message"><span class="difl__empty__message__text">No items yet. Add a new item to get started.</span></div></div></div>';
    } else {
      $circle_html = sprintf(
        '<div class="df_circle_info_wrapper trigger_%s animation_%s%s%s" data-settings=\'%s\'>
          <div class="df_circle_info_circle">
            <div class="df_circle_info_center"></div>
            <div class="df_circle_info_items">%s</div>
          </div>
        </div>',
        esc_attr($trigger_mode),
        esc_attr($content_animation),
        $mobile_class,
        $rotation_class,
        esc_attr($data_settings),
        $content ?: ''
      );
    }

    return Module::render(
      [
        // FE only.
        'orderIndex'          => $block->parsed_block['orderIndex'],
        'storeInstance'       => $block->parsed_block['storeInstance'],

        // VB equivalent.
        'id'                  => $block->parsed_block['id'],
        'name'                => $block->block_type->name,
        'moduleCategory'      => $block->block_type->category,
        'attrs'               => $attrs,
        'elements'            => $elements,
        'classnamesFunction'  => [self::class, 'classnames'],
        'scriptDataComponent' => [self::class, 'script_data'],
        'stylesComponent'     => [self::class, 'styles'],
        'parentAttrs'         => $parent_attrs,
        'parentId'            => $parent->id ?? '',
        'parentName'          => $parent->blockName ?? '',
        'children'            => ElementComponents::component(
          [
            'attrs'         => $attrs['module']['decoration'] ?? [],
            'id'            => $block->parsed_block['id'],
            'orderIndex'    => $block->parsed_block['orderIndex'],
            'storeInstance' => $block->parsed_block['storeInstance'],
          ]
        ) . $circle_html,
        'childrenIds'         => $children_ids,
      ]
    );
  }
}
