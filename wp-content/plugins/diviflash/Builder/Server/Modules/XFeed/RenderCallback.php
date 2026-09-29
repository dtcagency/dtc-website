<?php
namespace DIFL\Server\Modules\XFeed;

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
    wp_enqueue_script('df_x_feed');

    $x = $attrs['x_settings']['innerContent']['desktop']['value'] ?? [];

    $plugin_type = $x['plugin_type'] ?? 'tweet';
    $language    = $x['language'] ?? 'en';
    $align       = $x['x_align'] ?? 'center';

    $plugin_html = self::build_plugin_html($plugin_type, $x, $language);

    $wrapper_html = sprintf(
      '<div class="df_x_feed_wrapper df_x_align_%s">
        <div class="df_x_widget">%s</div>
      </div>',
      esc_attr($align),
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

  private static function build_plugin_html($type, $x, $language) {
    switch ($type) {
      case 'tweet':
        $tweet_url = self::normalize_x_url($x['tweet_url'] ?? '');
        $tweet_id  = self::extract_tweet_id($tweet_url);
        if (empty($tweet_id)) return '';
        $theme       = $x['tweet_theme'] ?? 'light';
        $cards       = ($x['tweet_cards'] ?? 'visible') === 'hidden' ? ' data-cards="hidden"' : '';
        $width_attr  = !empty($x['tweet_width']) ? ' data-width="' . esc_attr($x['tweet_width']) . '"' : '';

        return sprintf(
          '<blockquote class="twitter-tweet" data-theme="%s" data-lang="%s"%s%s><a href="%s"></a></blockquote>',
          esc_attr($theme),
          esc_attr($language),
          $cards,
          $width_attr,
          esc_url($tweet_url)
        );

      case 'follow':
        $username = self::strip_at($x['follow_username'] ?? '');
        if (empty($username)) return '';
        $show_name = ($x['follow_show_username'] ?? 'on') === 'off' ? ' data-show-screen-name="false"' : '';
        $size      = ($x['follow_size'] ?? 'small') === 'large' ? ' data-size="large"' : '';

        return sprintf(
          '<a href="https://twitter.com/%s" class="twitter-follow-button" data-lang="%s"%s%s>Follow @%s</a>',
          esc_attr($username),
          esc_attr($language),
          $show_name,
          $size,
          esc_html($username)
        );

      case 'share':
        $url = $x['share_url'] ?? '';
        if (empty($url)) return '';
        $size      = ($x['share_size'] ?? 'small') === 'large' ? ' data-size="large"' : '';
        $text_attr = !empty($x['share_text']) ? ' data-text="' . esc_attr($x['share_text']) . '"' : '';
        $via       = self::strip_at($x['share_via'] ?? '');
        $via_attr  = !empty($via) ? ' data-via="' . esc_attr($via) . '"' : '';
        $raw_tags  = $x['share_hashtags'] ?? '';
        $hashtags  = self::parse_hashtags($raw_tags);
        $tags_attr = !empty($hashtags) ? ' data-hashtags="' . esc_attr(implode(',', $hashtags)) . '"' : '';

        return sprintf(
          '<a href="https://twitter.com/share" class="twitter-share-button" data-url="%s" data-lang="%s"%s%s%s%s>Tweet</a>',
          esc_url($url),
          esc_attr($language),
          $size,
          $text_attr,
          $via_attr,
          $tags_attr
        );

      default:
        return '';
    }
  }

  private static function normalize_x_url($url) {
    if (empty($url)) return '';
    return preg_replace('#https?://(www\.)?x\.com#i', 'https://twitter.com', $url);
  }

  private static function strip_at($username) {
    return ltrim(trim($username), '@');
  }

  private static function extract_tweet_id($url) {
    if (preg_match('/status\/(\d+)/', $url, $matches)) {
      return $matches[1];
    }
    return '';
  }

  private static function parse_hashtags($str) {
    if (empty($str)) return [];
    return array_values(array_filter(array_map('trim', explode(',', $str))));
  }
}
