<?php

namespace DIFL\Server\Modules\BarChartDataset;

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

        // Merge child preset's dataset_settings (color/gradient/border) into
        // $attrs so a saved "Red Bar Style" preset actually applies on the
        // front-end. Divi's wrapper only pre-merges renderAttrs; innerContent
        // values like bar_color live in preset.attrs and need an explicit
        // merge. dataset_values stays outside the whitelist so the preset
        // never overwrites the per-instance numeric data.
        $attrs = self::processStylePreset(
            $attrs,
            $block->block_type->name,
            ['module', 'dataset_settings'],
            $block->parsed_block['attrs'] ?? []
        );

        // Extract bar item settings — check multiple paths for attrs
        $ds = $attrs['dataset_settings']['innerContent']['desktop']['value']
           ?? $attrs['dataset_settings']['innerContent']['value']
           ?? [];

        // Defaults for newly created child items. Color fields stay empty
        // so the parent's FE JS can apply DF_BAR_PALETTE per sibling index
        // when the user hasn't set a color — stamping a concrete hex here
        // forces every unset dataset to render the same blue on the front.
        $defaults = [
            'dataset_name'    => 'Dataset 1',
            'bar_color'       => '',
            'bar_hover_color' => '',
            'border_color'    => '',
            'border_width'    => '1',
            'use_gradient'    => 'off',
            'gradient_top'    => '',
            'gradient_bottom' => '',
        ];
        $ds = array_merge($defaults, $ds);

        $dataset_json = wp_json_encode([
            'datasetName'    => $ds['dataset_name'],
            'values'         => self::parse_sortable_values(
                $attrs['dataset_values']['innerContent']['desktop']['value'] ?? []
            ),
            'barColor'       => $ds['bar_color'],
            'barHoverColor'  => $ds['bar_hover_color'],
            'borderColor'    => $ds['border_color'],
            'borderWidth'    => $ds['border_width'],
            'useGradient'    => $ds['use_gradient'],
            'gradientTop'    => $ds['gradient_top'],
            'gradientBottom' => $ds['gradient_bottom'],
        ]);

        // Render a hidden script block with dataset JSON for the parent's JS to consume
        $item_html = sprintf(
            '<script type="application/json" class="df_bar_chart_dataset">%s</script>',
            $dataset_json
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
