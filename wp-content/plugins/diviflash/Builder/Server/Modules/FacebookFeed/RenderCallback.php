<?php
namespace DIFL\Server\Modules\FacebookFeed;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallback {
  use Classnames;
  use Styles;
  use ScriptData;

  public static function render_callback($attrs, $content, $block, $elements) {
    wp_enqueue_script('df_facebook_feed');

    // Extract settings
    $fb = $attrs['fb_settings']['innerContent']['desktop']['value'] ?? [];

    $plugin_type = $fb['plugin_type'] ?? 'page';
    $language    = $fb['language'] ?? 'en_US';
    $align       = $fb['fb_align'] ?? 'center';

    $data_settings = wp_json_encode([
      'plugin_type' => $plugin_type,
      'language'    => $language,
    ]);

    $plugin_html = self::build_plugin_html($plugin_type, $fb);

    $wrapper_html = sprintf(
      '<div class="df_fb_feed_wrapper df_fb_align_%s" data-settings=\'%s\'>
        <div class="df_fb_plugin">%s</div>
      </div>',
      esc_attr($align),
      esc_attr($data_settings),
      $plugin_html
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
        'children'            => ElementComponents::component(
          [
            'attrs'         => $attrs['module']['decoration'] ?? [],
            'id'            => $block->parsed_block['id'],
            'orderIndex'    => $block->parsed_block['orderIndex'],
            'storeInstance' => $block->parsed_block['storeInstance'],
          ]
        ) . $wrapper_html,
      ]
    );
  }

  private static function build_plugin_html($type, $fb) {
    switch ($type) {
      case 'page':
        if (empty($fb['page_url'])) return '';
        $raw_tabs = $fb['page_tabs'] ?? 'timeline';
        $tabs = is_array($raw_tabs) ? implode(',', $raw_tabs) : str_replace('|', ',', $raw_tabs);
        return sprintf(
          '<div class="fb-page" data-href="%s" data-tabs="%s" data-width="%s" data-height="%s" data-small-header="%s"%s></div>',
          esc_url($fb['page_url']),
          esc_attr($tabs),
          esc_attr($fb['page_width'] ?? '340'),
          esc_attr($fb['page_height'] ?? '500'),
          self::to_fb_bool($fb['page_small_header'] ?? 'off'),
          self::lazy_attr($fb['page_lazy_load'] ?? 'off')
        );

      case 'share':
        if (empty($fb['share_url'])) return '';
        return sprintf(
          '<div class="fb-share-button" data-href="%s" data-layout="%s" data-size="%s"></div>',
          esc_url($fb['share_url']),
          esc_attr($fb['share_layout'] ?? 'button_count'),
          esc_attr($fb['share_size'] ?? 'small')
        );

      case 'post':
        if (empty($fb['post_url'])) return '';
        $width_attr = !empty($fb['post_width']) ? ' data-width="' . esc_attr($fb['post_width']) . '"' : '';
        return sprintf(
          '<div class="fb-post" data-href="%s"%s data-show-text="%s"%s></div>',
          esc_url($fb['post_url']),
          $width_attr,
          self::to_fb_bool($fb['post_show_text'] ?? 'on'),
          self::lazy_attr($fb['post_lazy_load'] ?? 'off')
        );

      case 'video':
        if (empty($fb['video_url'])) return '';
        $width_attr = !empty($fb['video_width']) ? ' data-width="' . esc_attr($fb['video_width']) . '"' : '';
        return sprintf(
          '<div class="fb-video" data-href="%s"%s data-show-text="%s" data-show-captions="%s" data-allowfullscreen="%s"></div>',
          esc_url($fb['video_url']),
          $width_attr,
          self::to_fb_bool($fb['video_show_text'] ?? 'off'),
          self::to_fb_bool($fb['video_show_captions'] ?? 'off'),
          self::to_fb_bool($fb['video_allowfullscreen'] ?? 'on')
        );

      default:
        return '';
    }
  }

  private static function to_fb_bool($value) {
    return $value === 'on' ? 'true' : 'false';
  }

  private static function lazy_attr($value) {
    return $value === 'on' ? ' data-lazy="true"' : '';
  }
}
