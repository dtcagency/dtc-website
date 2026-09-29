<?php

namespace DIFL\Server\Modules\PieChartItem;

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

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $parent       = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $parent_attrs = $parent->attrs ?? [];

        // Merge child preset's slice_settings (color/hover/offset/gradient)
        // into $attrs so a saved slice style preset applies on the front-
        // end. slice_label and slice_value live in the same group, so the
        // trait's user-attrs-win ordering keeps the receiving item's label
        // and value from being overwritten by the preset's values.
        $attrs = self::processStylePreset(
            $attrs,
            $block->block_type->name,
            ['module', 'slice_settings'],
            $block->parsed_block['attrs'] ?? []
        );

        $ds = $attrs['slice_settings']['innerContent']['desktop']['value']
           ?? $attrs['slice_settings']['innerContent']['value']
           ?? [];

        // Color fields stay empty so the parent's FE JS can apply
        // DF_PIE_PALETTE per sibling index when the user hasn't set a
        // slice color. A concrete hex here forces every unset slice to
        // render the same blue on the front-end.
        $defaults = [
            'slice_label'       => 'Slice 1',
            'slice_value'       => '25',
            'slice_color'       => '',
            'slice_hover_color' => '',
            'slice_offset'      => '0',
            'use_gradient'      => 'off',
            'gradient_from'     => '',
            'gradient_to'       => '',
        ];
        $ds = array_merge($defaults, $ds);

        $item_json = wp_json_encode([
            'sliceLabel'      => $ds['slice_label'],
            'sliceValue'      => $ds['slice_value'],
            'sliceColor'      => $ds['slice_color'],
            'sliceHoverColor' => $ds['slice_hover_color'],
            'sliceOffset'     => $ds['slice_offset'],
            'useGradient'     => $ds['use_gradient'],
            'gradientFrom'    => $ds['gradient_from'],
            'gradientTo'      => $ds['gradient_to'],
        ]);

        $item_html = sprintf(
            '<script type="application/json" class="df_pie_chart_item">%s</script>',
            $item_json
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
