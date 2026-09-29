<?php

namespace DIFL\Server\Modules\CircleInfoItem;

if (!defined('ABSPATH')) {
    die('Direct access forbidden.');
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use DIFL\Server\Traits\IconRenderTrait;

trait RenderCallback
{
    use Classnames;
    use Styles;
    use IconRenderTrait;

    public static function render_callback($attrs, $content, $block, $elements)
    {
        $parent       = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
        $parent_attrs = $parent->attrs ?? [];

        // Extract item content
        $title       = $attrs['item_content']['innerContent']['desktop']['value']['title'] ?? '';
        $description = $attrs['item_content']['innerContent']['desktop']['value']['description'] ?? '';

        // Extract icon settings
        $icon_type   = $attrs['item_icon_content']['innerContent']['desktop']['value']['icon_type'] ?? 'icon';
        $font_icon   = $attrs['item_icon_content']['innerContent']['desktop']['value']['font_icon'] ?? null;
        $icon_image  = $attrs['item_icon_content']['innerContent']['desktop']['value']['icon_image'] ?? '';

        $icon_text   = $attrs['item_icon_content']['innerContent']['desktop']['value']['icon_text'] ?? '';

        // Render icon or image (skip if "none")
        $icon_html = '';
        if ($icon_type === 'image') {
            $img_src = !empty($icon_image) ? $icon_image : plugins_url('Builder/assets/images/placeholder.svg', DIFL_MAIN_FILE_PATH);
            $icon_html = sprintf('<img class="df_ci_icon_img" src="%s" alt="%s" />', esc_url($img_src, array('http', 'https', 'data')), esc_attr($title ?: 'Placeholder'));
        } elseif ($icon_type === 'icon') {
            $icon_html = self::render_icon($font_icon);
        }

        // Append text if provided
        if (!empty($icon_text)) {
            $icon_html .= sprintf('<span class="df_ci_icon_text">%s</span>', esc_html($icon_text));
        }

        // Build center content as real HTML (SEO-friendly)
        $center_html = '';
        $title_tag = $attrs['item_title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h4';
        $allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p'];
        if (!in_array($title_tag, $allowed_tags, true)) {
            $title_tag = 'div';
        }

        if (!empty($title)) {
            $center_html .= sprintf('<%s class="df_ci_title">%s</%s>', $title_tag, esc_html($title), $title_tag);
        }

        if (!empty($description)) {
            $center_html .= sprintf('<div class="df_ci_description">%s</div>', wp_kses_post($description));
        }

        // Render child elements (nested modules via "Add Element")
        $elements_html = $content ?: '';
        if (!empty(trim($elements_html))) {
            $center_html .= sprintf('<div class="df_ci_elements">%s</div>', $elements_html);
        }

        $item_html = sprintf(
            '<div class="df_circle_info_item_wrapper">
                <div class="df_circle_info_icon">%s</div>
            </div>
            <div class="df_ci_center_content"><div class="df_ci_content_inner">%s</div></div>',
            $icon_html,
            $center_html
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
