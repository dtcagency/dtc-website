<?php

namespace DIFL\Server\Modules\BarChartItem;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallback
{
    use Classnames;
    use Styles;

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $parent       = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $parent_attrs = $parent->attrs ?? [];

        // Extract bar item settings — check multiple paths for attrs
        $ds = $attrs['dataset_settings']['innerContent']['desktop']['value']
           ?? $attrs['dataset_settings']['innerContent']['value']
           ?? [];

        // Defaults for newly created child items (Divi may not populate default_value into attrs)
        $defaults = [
            'item_label'      => 'Item 1',
            'item_value'      => '25',
            'dataset_name'    => 'Dataset 1',
            'bar_color'       => '#3366cc',
            'bar_hover_color' => '#2255bb',
            'border_color'    => '#3366cc',
            'border_width'    => '1',
            'use_gradient'    => 'off',
            'gradient_top'    => '#3366cc',
            'gradient_bottom' => '#1a3366',
        ];
        $ds = array_merge($defaults, $ds);

        $bar_json = wp_json_encode([
            'label'          => $ds['item_label'],
            'value'          => floatval($ds['item_value']),
            'datasetName'    => $ds['dataset_name'],
            'barColor'       => $ds['bar_color'],
            'barHoverColor'  => $ds['bar_hover_color'],
            'borderColor'    => $ds['border_color'],
            'borderWidth'    => $ds['border_width'],
            'useGradient'    => $ds['use_gradient'],
            'gradientTop'    => $ds['gradient_top'],
            'gradientBottom' => $ds['gradient_bottom'],
        ]);

        // Render a hidden script block with bar item JSON for the parent's JS to consume
        $item_html = sprintf(
            '<script type="application/json" class="df_bar_chart_bar_item">%s</script>',
            $bar_json
        );

        return Module::render(
            [
                // FE only.
                'orderIndex'         => $block->parsed_block['orderIndex'],
                'storeInstance'      => $block->parsed_block['storeInstance'],

                // VB equivalent.
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
