<?php

namespace DIFL\Server\Modules\LineChartDataset;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use DIFL\Server\Traits\ProcessStylePreset;
use ET\Builder\Packages\Module\Module;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallback
{
    use Classnames;
    use Styles;
    use ProcessStylePreset;

    private static function parse_sortable_values($raw) {
        if (!is_array($raw) || empty($raw)) return [25, 50, 75];
        $values = [];
        foreach ($raw as $item) {
            if (is_array($item) && isset($item['value'])) {
                $values[] = floatval($item['value']);
            }
        }
        return !empty($values) ? $values : [25, 50, 75];
    }

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $parent       = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $parent_attrs = $parent->attrs ?? [];

        // Merge child preset's dataset_settings (color/width/tension/fill,
        // point styling, dash pattern) into $attrs so a saved style preset
        // applies on the front-end. dataset_values stays outside so the
        // preset never overwrites the per-instance numeric data.
        $attrs = self::processStylePreset(
            $attrs,
            $block->block_type->name,
            ['module', 'dataset_settings'],
            $block->parsed_block['attrs'] ?? []
        );

        $ds = $attrs['dataset_settings']['innerContent']['desktop']['value']
           ?? $attrs['dataset_settings']['innerContent']['value']
           ?? [];

        $defaults = [
            'dataset_name'        => 'Dataset 1',
            // Stay empty so the parent's FE JS can apply DF_LINE_PALETTE
            // per sibling index. A concrete hex here makes every unset
            // dataset render the same blue on the front-end.
            'line_color'          => '',
            'line_width'          => '2',
            'tension'             => '0.1',
            'stepped'             => 'false',
            'use_dashed'          => 'off',
            'dash_length'         => '5',
            'dash_gap'            => '5',
            'point_style'         => 'circle',
            'point_radius'        => '3',
            'point_hover_radius'  => '5',
            'point_color'         => '',
            'fill_mode'           => 'false',
            'fill_color'          => 'rgba(51,102,204,0.2)',
            'use_fill_gradient'   => 'off',
            'fill_gradient_top'   => 'rgba(51,102,204,0.4)',
            'fill_gradient_bottom' => 'rgba(51,102,204,0.05)',
        ];
        $ds = array_merge($defaults, $ds);

        $dataset_json = wp_json_encode([
            'datasetName'       => $ds['dataset_name'],
            'values'            => self::parse_sortable_values(
                $attrs['dataset_values']['innerContent']['desktop']['value'] ?? []
            ),
            'lineColor'         => $ds['line_color'],
            'lineWidth'         => $ds['line_width'],
            'tension'           => $ds['tension'],
            'stepped'           => $ds['stepped'],
            'useDashed'         => $ds['use_dashed'],
            'dashLength'        => $ds['dash_length'],
            'dashGap'           => $ds['dash_gap'],
            'pointStyle'        => $ds['point_style'],
            'pointRadius'       => $ds['point_radius'],
            'pointHoverRadius'  => $ds['point_hover_radius'],
            'pointColor'        => $ds['point_color'],
            'fillMode'          => $ds['fill_mode'],
            'fillColor'         => $ds['fill_color'],
            'useFillGradient'   => $ds['use_fill_gradient'],
            'fillGradientTop'   => $ds['fill_gradient_top'],
            'fillGradientBottom' => $ds['fill_gradient_bottom'],
        ]);

        $item_html = sprintf(
            '<script type="application/json" class="df_line_chart_dataset">%s</script>',
            $dataset_json
        );

        return Module::render(
            [
                'orderIndex'         => $block->parsed_block['orderIndex'],
                'storeInstance'      => $block->parsed_block['storeInstance'],
                'id'                 => $block->parsed_block['id'],
                'name'               => $block->block_type->name,
                'moduleCategory'     => $block->block_type->category,
                'attrs'              => $attrs,
                'elements'           => $elements,
                'classnamesFunction' => [self::class, 'classnames'],
                'stylesComponent'    => [self::class, 'styles'],
                'parentAttrs'        => $parent_attrs,
                'parentId'           => $parent->id ?? '',
                'parentName'         => $parent->blockName ?? '',
                'children'           => ElementComponents::component(
                    [
                        'attrs'         => $attrs['module']['decoration'] ?? [],
                        'id'            => $block->parsed_block['id'],
                        'orderIndex'    => $block->parsed_block['orderIndex'],
                        'storeInstance' => $block->parsed_block['storeInstance'],
                    ]
                ) . $item_html,
            ]
        );
    }
}
