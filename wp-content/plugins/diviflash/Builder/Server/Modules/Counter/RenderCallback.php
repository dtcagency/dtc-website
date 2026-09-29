<?php
namespace DIFL\Server\Modules\Counter;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use DIFL\Server\Traits\ProcessStylePreset;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallback {
  use Classnames;
  use Styles;
  use ScriptData;
  use ProcessStylePreset;

  public static function render_callback($attrs, $content, $block, $elements) {
    wp_enqueue_script('df-counter');

    // Include counter_settings in the preset merge so "Save as Preset"
    // captures the target/start/prefix/suffix and applies them on render.
    // The trait's merge layers user_raw_attrs LAST, so a panel-edited
    // target on the receiving instance still wins over the preset's
    // value — the original concern about presets clobbering per-instance
    // numbers is handled by that ordering. See docs/MODULE_PRESETS.md.
    $attrs = self::processStylePreset(
      $attrs,
      $block->block_type->name,
      ['module', 'counter_settings', 'animation_settings', 'style_settings', 'number_font'],
      $block->parsed_block['attrs'] ?? []
    );

    $parent       = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
    $parent_attrs = $parent->attrs ?? [];

    // Extract settings from attribute groups
    $counter   = $attrs['counter_settings']['innerContent']['desktop']['value'] ?? [];
    $animation = $attrs['animation_settings']['innerContent']['desktop']['value'] ?? [];
    // Build JSON config — only values needed by JS for DOM/logic (not styling)
    $counter_config = wp_json_encode([
      'endValue'           => $counter['end_value'] ?? '1234',
      'startValue'         => $counter['start_value'] ?? '0',
      'thousandSeparator'  => $counter['thousand_separator'] ?? 'comma',
      'decimalPlaces'      => $counter['decimal_places'] ?? '0',
      'decimalSeparator'   => $counter['decimal_separator'] ?? 'dot',
      'prefix'             => $counter['prefix'] ?? '',
      'suffix'             => $counter['suffix'] ?? '',
      'animationStyle'     => $animation['animation_style'] ?? 'rolling',
      'duration'           => $animation['duration'] ?? '2000',
      'scrollTrigger'      => $animation['scroll_trigger'] ?? 'on',
      'scrollThreshold'    => $animation['scroll_threshold'] ?? '0.2',
      'staggerDelay'       => $animation['stagger_delay'] ?? '50',
      'easing'             => $animation['easing'] ?? 'ease-out',
      'reTrigger'          => $animation['re_trigger'] ?? 'off',
    ]);

    $counter_html = sprintf(
      '<div class="df_counter_wrapper" data-counter-config=\'%s\'>
        <div class="df_counter_content"></div>
      </div>',
      esc_attr($counter_config)
    );

    return Module::render(
      [
        'orderIndex'          => $block->parsed_block['orderIndex'],
        'storeInstance'       => $block->parsed_block['storeInstance'],
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
        ) . $counter_html,
      ]
    );
  }
}
