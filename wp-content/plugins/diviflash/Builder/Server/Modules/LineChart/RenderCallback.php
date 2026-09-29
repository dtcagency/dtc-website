<?php
namespace DIFL\Server\Modules\LineChart;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use DIFL\Server\Traits\ProcessStylePreset;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIFL\Server\ChartData;

trait RenderCallback {
  use Classnames;
  use Styles;
  use ScriptData;
  use ProcessStylePreset;

  private static function parse_sortable_labels($raw) {
    if (!is_array($raw) || empty($raw)) return ['Jan', 'Feb', 'Mar'];
    $labels = [];
    foreach ($raw as $item) {
      if (is_array($item) && isset($item['value'])) {
        $labels[] = $item['value'];
      }
    }
    return !empty($labels) ? $labels : ['Jan', 'Feb', 'Mar'];
  }

  // Chart.js needs a px number for font.size — convert whatever unit the user picked.
  private static function font_size_to_px($value) {
    if (empty($value)) return 0;
    if (!preg_match('/^([\d.]+)\s*(\w+|%)?$/', trim((string) $value), $m)) return 0;
    $num  = (float) $m[1];
    $unit = strtolower($m[2] ?? '');
    switch ($unit) {
      case 'em':
      case 'rem': return (int) round($num * 16);
      case 'pt':  return (int) round($num * 1.333);
      case '%':   return (int) round($num * 16 / 100);
      default:    return (int) round($num);
    }
  }

  public static function render_callback($attrs, $content, $block, $elements) {
    wp_enqueue_script('df-chartjs');
    wp_enqueue_script('df-line-chart');

    // Apply preset to STYLE attr groups only — content (data_source,
    // chart_labels) stays per-instance. See docs/MODULE_PRESETS.md.
    $attrs = self::processStylePreset(
      $attrs,
      $block->block_type->name,
      ['module', 'chart_settings', 'axis_settings', 'grid_settings', 'line_settings', 'animation_settings',
       'title_font', 'legend_font', 'tooltip_font', 'axis_tick_font', 'axis_title_font'],
      $block->parsed_block['attrs'] ?? []
    );

    if (function_exists('difl_resolve_color_values_in_attrs')) {
      $attrs = difl_resolve_color_values_in_attrs($attrs);
    }

    $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
      function ($inner_block) {
        return $inner_block['id'];
      },
      $block->parsed_block['innerBlocks']
    ) : [];

    $parent       = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
    $parent_attrs = $parent->attrs ?? [];

    // Extract settings
    $chart    = $attrs['chart_settings']['innerContent']['desktop']['value'] ?? [];
    $axis     = $attrs['axis_settings']['innerContent']['desktop']['value'] ?? [];
    $grid     = $attrs['grid_settings']['innerContent']['desktop']['value'] ?? [];
    $line     = $attrs['line_settings']['innerContent']['desktop']['value'] ?? [];
    $anim     = $attrs['animation_settings']['innerContent']['desktop']['value'] ?? [];
    $ds_attr  = $attrs['data_source']['innerContent']['desktop']['value'] ?? [];

    // If a remote data source is configured, fetch + parse so the chart can
    // be built without dataset children. Output is { labels, datasets }.
    $data_source_mode = $ds_attr['data_source'] ?? 'manual';
    $remote_data      = null;
    if ('manual' !== $data_source_mode) {
      $url_map = [
        'google_sheets' => $ds_attr['sheets_url']      ?? '',
        'csv_url'       => $ds_attr['csv_source_url']  ?? '',
        'json_url'      => $ds_attr['json_source_url'] ?? '',
      ];
      $type = ('json_url' === $data_source_mode) ? 'json' : 'csv';
      $ttl  = isset($ds_attr['cache_ttl']) ? (int) $ds_attr['cache_ttl'] : 3600;
      $url  = $url_map[$data_source_mode] ?? '';
      $remote_data = ChartData::fetch($url, $type, $ttl);
      if (empty($remote_data)) {
        $remote_data = null;
      }
    }

    // Extract font decoration attrs (responsive: desktop, tablet, phone)
    $font_keys = ['title_font', 'legend_font', 'tooltip_font', 'axis_tick_font', 'axis_title_font'];
    $breakpoints = ['desktop', 'tablet', 'phone'];
    $fonts = [];
    foreach ($font_keys as $fk) {
      $font_data = $attrs[$fk]['decoration']['font']['font'] ?? [];
      $font_entry = [];
      foreach ($breakpoints as $bp) {
        $fv = $font_data[$bp]['value'] ?? [];
        if (!empty($fv)) {
          $font = [];
          if (!empty($fv['family'])) $font['family'] = $fv['family'];
          if (!empty($fv['size'])) {
            $px = self::font_size_to_px($fv['size']);
            if ($px > 0) $font['size'] = $px;
          }
          if (!empty($fv['weight'])) $font['weight'] = $fv['weight'];
          if (!empty($fv['style'])) $font['style'] = $fv['style'];
          if (!empty($fv['color'])) $font['color'] = $fv['color'];
          if (!empty($font)) $font_entry[$bp] = $font;
        }
      }
      if (!empty($font_entry)) $fonts[$fk] = $font_entry;
    }

    // Override chart labels with remote labels when data source is active.
    $effective_labels = $remote_data
      ? $remote_data['labels']
      : self::parse_sortable_labels($attrs['chart_labels']['innerContent']['desktop']['value'] ?? []);

    $chart_config = wp_json_encode([
      'chartTitle'         => $chart['chart_title'] ?? '',
      'titlePosition'      => $chart['title_position'] ?? 'top',
      'chartLabels'        => $effective_labels,
      'remoteDatasets'     => $remote_data['datasets'] ?? null,
      'showLegend'         => $chart['show_legend'] ?? 'on',
      'legendPosition'     => $chart['legend_position'] ?? 'top',
      'legendAlign'        => $chart['legend_align'] ?? 'center',
      'showTooltip'        => $chart['show_tooltip'] ?? 'on',
      'valuePrefix'        => $chart['value_prefix'] ?? '',
      'valueSuffix'        => $chart['value_suffix'] ?? '',
      'showXAxis'          => $axis['show_x_axis'] ?? 'on',
      'xAxisTitle'         => $axis['x_axis_title'] ?? '',
      'showXGrid'          => $axis['show_x_grid'] ?? 'on',
      'showYAxis'          => $axis['show_y_axis'] ?? 'on',
      'yAxisTitle'         => $axis['y_axis_title'] ?? '',
      'showYGrid'          => $axis['show_y_grid'] ?? 'on',
      'yBeginAtZero'       => $axis['y_begin_at_zero'] ?? 'on',
      'yMin'               => $axis['y_min'] ?? '',
      'yMax'               => $axis['y_max'] ?? '',
      'yStepSize'          => $axis['y_step_size'] ?? '',
      'gridColor'          => $grid['grid_color'] ?? 'rgba(0,0,0,0.1)',
      'gridWidth'          => $grid['grid_width'] ?? '1',
      'chartHeight'        => $line['chart_height'] ?? '400',
      'enableAnimation'    => $anim['enable_animation'] ?? 'on',
      'animationDuration'  => $anim['animation_duration'] ?? '1000',
      'animationEasing'    => $anim['animation_easing'] ?? 'easeOutQuart',
      'animateOnScroll'    => $anim['animate_on_scroll'] ?? 'on',
      'fonts'              => !empty($fonts) ? $fonts : null,
    ]);

    $chart_html = sprintf(
      '<div class="df_line_chart_wrapper" data-chart-config=\'%s\'>
        <canvas class="df_line_chart_canvas"></canvas>
        %s
      </div>',
      esc_attr($chart_config),
      $content ?: ''
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
        ) . $chart_html,
        'childrenIds'         => $children_ids,
      ]
    );
  }
}
