<?php
namespace DIFL\Server\Modules\InstagramGallery;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\API;


class InstagramGallery implements DependencyInterface {

  use ModuleUtil;
  use HasProps;
  use StyleUtil;
  // use Fa_Icon_Handler;
  protected $config_file = 'InstagramGallery';

  public static $content;
  public $parent;
  public $df_image_props = array();
  protected $order_class;

  protected $module_credits = array(
    'module_uri' => '',
    'author'     => 'DiviFlash',
    'author_uri' => '',
  );

  public static function custom_css() {
    $block = \WP_Block_Type_Registry::get_instance()->get_registered('difl/instagramgallery');
    return $block ? $block->customCssFields : [];
  }

  public static function module_classnames($args) {
    $classnames_instance = $args['classnamesInstance'];
    $attrs               = $args['attrs'];

    // Text Options.
    $classnames_instance->add(
      TextClassnames::text_options_classnames(
        $attrs['module']['advanced']['text'] ?? [],
        [
          'orientation' => false,
        ]
      ),
      true
    );

    // Module.
    $classnames_instance->add(
      ElementClassnames::classnames(
        [
          'attrs' => $attrs['module']['decoration'] ?? [],
          'attrs' => array_merge(
            $attrs['module']['decoration'] ?? [],
            [
              'link' => $attrs['module']['advanced']['link'] ?? [],
            ]
          ),
        ]
      )
    );
  }

  public static function module_script_data($args) {

    $elements = $args['elements'];
    // Element Script Data Options.
    $elements->script_data(
      [
        'attrName' => 'module',
      ]
    );
  }

    function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

  public function get_common_styles($args) {
    $attrs      = $args['attrs'] ?? [];
    $attrs      = $this->get_merged_attrs( $args );
    $attrs      = self::build_attr_for_styles( $attrs );
    $attrs      = $this->get_default_value_for_styles($attrs);
    $orderClass    = $args['orderClass'] ?? '';
    $elements      = $args['elements'];
    $settings      = $args['settings'] ?? [];
    $styles        = [];
    $common_styles = [];

    $common_styles[] = $elements->style([
      'attrName'   => 'module',
      'styleProps' => [
        'disabledOn' => [
          'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
        ],
      ],
    ]);
    $common_styles[] = CssStyle::style([
      'selector'  => $args['orderClass'],
      'attr'      => $attrs['css'] ?? [],
      'cssFields' => self::custom_css(),
    ]);

    $common_styles[] = $elements->style(['attrName' => 'caption',]);
    $common_styles[] = $elements->style(['attrName' => 'instagram_username',]);
    $common_styles[] = $elements->style(['attrName' => 'instagram_post_date',]);
    $common_styles[] = $elements->style(['attrName' => 'instagram_settings',]);
    $common_styles[] = $elements->style(['attrName' => 'gallery_settings',]);
    $common_styles[] = $elements->style(['attrName' => 'user_info',]);
    $common_styles[] = $elements->style(['attrName' => 'load_more_btn',]);
    $common_styles[] = $elements->style(['attrName' => 'instagram_icon_margin',]);

    // Converted styles from JSX to PHP
    //   $common_styles[] = CommonStyle::style([
    //     'selector' => "{$orderClass} .grid-item",
    //     'attr' => $attrs['gallery_settings']['innerContent'] ?? [],
    //     'declarationFunction' => function($props) {
    //         $items = $props['attrValue']['image_to_display'];
    //         if( $items ) {
    //             return "width: " . (100 / $items) . "%;";
    //         }
    //     }
    //   ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .grid .grid-item",
        'attr' => $attrs['gallery_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props){
            $items = $props['attrValue']['image_to_display'];
            if( $items ) {
                return "width: " . (100 / $items) . "%;";
            }
        }
    ]);

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .ing-load-more-btn .spinner svg",
      'attr' => $attrs['load_more_btn']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "fill: {$this->props['spinner_color']};",
    ]);
    if (
      ('on' === ($this->props['show_instagram_user_info'] ?? 'off')) &&
      ('on' === ($this->props['user_info_show_at_bottom'] ?? 'off'))
    ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_ing_container .item-content",
        'attr' => $attrs['user_info_show_at_bottom']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "flex-direction: column-reverse;",
      ]);
    }
    if (
      ('on' === ($this->props['show_instagram_user_info'] ?? 'off')) &&
      ('on' === ($this->props['instagram_icon_enable'] ?? 'off'))
    ) {
      $instagram_icon_selector = "{$orderClass} .item-content .df-instagram-feed-icon .et-pb-icon";
      $instagram_icon_hover_selector = "{$orderClass} .item-content:hover .df-instagram-feed-icon .et-pb-icon";

      $common_styles[] = CommonStyle::style([
        'selector' => $instagram_icon_selector,
        'selectorFunction' => function ( $props ) use ( $instagram_icon_selector, $instagram_icon_hover_selector ) {
          if ( isset( $props['state'] ) && 'hover' === $props['state'] ) {
            return $instagram_icon_hover_selector;
          }

          return $instagram_icon_selector;
        },
        'attr' => $attrs['user_info']['innerContent'] ?? [],
        'declarationFunction' => function ( $props ) {
            $attr = $props['attrValue'] ?? [];
            $style = '';

            if ( ! empty( $attr['instagram_icon_color'] ) ) {
                $style .= "color: {$attr['instagram_icon_color']}; ";
            }

            if ( ! empty( $attr['instagram_icon_size'] ) ) {
                $style .= "font-size: {$attr['instagram_icon_size']}; ";
            }

            return trim( $style );
        },
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} a.df-instagram-user",
        'attr' => $attrs['user_info']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "flex-basis: {$this->props['instagram_user_section_width']};",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} a.df-instagram-feed-icon",
        'attr' => $attrs['user_info']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "flex-basis: calc(100% -  {$this->props['instagram_user_section_width']});",
      ]);
    }
    if (
      ('on' === ($this->props['show_instagram_user_info'] ?? 'off')) &&
      !empty($this->props['user_profile_picture_width'])
    ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df-instagram-user-profile-picture",
        'attr' => $attrs['user_info']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "width: {$this->props['user_profile_picture_width']};",
      ]);
    }
    if (
      ('on' === ($this->props['show_instagram_user_info'] ?? 'off')) &&
      !empty($this->props['instagram_icon_position'])
    ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df-instagram-user-info",
        'attr' => $attrs['user_info']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "align-items: {$this->props['instagram_icon_position']};",
      ]);
    }
    if ('on' === ($this->props['show_instagram_user_info'] ?? 'off')) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} a.df-instagram-feed-icon",
        'attr' => $attrs['instagram_icon_alignment']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['instagram_icon_alignment']};",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} span.df-instagram-user-name",
        'attr' => $attrs['instagram_username_align']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['instagram_username_align']};",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} span.df-instagram-postdate",
        'attr' => $attrs['instagram_post_date_align']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['instagram_post_date_align']};",
      ]);
    }
    if ('on' === ($this->props['use_icon'] ?? 'off')) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .et-pb-icon.hover_icon",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "color: {$this->props['hover_icon_color']};",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .et-pb-icon.hover_icon",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "font-size: {$this->props['hover_icon_size']};",
      ]);
    }
    $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .grid .grid-item",
        'attr' => $attrs['gallery_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props){
            $item_gutter = ( $props['attrValue'] ?? [] )['item_gutter'] ?? '';
            if( $item_gutter ) {
                return "padding-left: " . $item_gutter . "; padding-bottom: " . $item_gutter . ";";
            }
        }
    ]);
    $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .grid",
        'attr' => $attrs['gallery_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props){
            $item_gutter = ( $props['attrValue'] ?? [] )['item_gutter'] ?? '';
            if( $item_gutter ) {
                return "margin-left: -" . $item_gutter . ";";
            }
        }
    ]);
    if ('c4-image-rotate-left' === ($this->props['image_scale'] ?? 'no-image-scale')) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-image-rotate-left:hover img, {$orderClass} :focus.c4-image-rotate-left img",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "transform: scale({$this->props['image_scale_hover']}) rotate(-15deg);",
      ]);
    }
    if ('c4-image-rotate-right' === ($this->props['image_scale'] ?? 'no-image-scale')) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-image-rotate-right:hover img, {$orderClass} :focus.c4-image-rotate-right img",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "transform: scale({$this->props['image_scale_hover']}) rotate(15deg);",
      ]);
    }
    // Uncomment and convert if needed
    // if (!empty($all_props['fliter_align'])) {
    //   $common_styles[] = CommonStyle::style([
    //     'selector' => "{$orderClass} .df_filter_buttons",
    //     'attr' => $attrs['hover_settings']['innerContent'] ?? [],
    //     'declarationFunction' => fn($props) => "text-align: {$all_props['fliter_align']};",
    //   ]);
    // }
    if (!empty($this->props['more_btn_align'])) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_ing_button_container",
        'attr' => $attrs['more_btn_align']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['more_btn_align']};",
      ]);
    }
    if ('on' === ($this->props['overlay'] ?? 'off')) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-izmir",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "--image-opacity: 1;",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-izmir .df-overlay",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props) {
            $attr = $props['attrValue'] ?? [];

            $overlay_direction = !empty($attr['overlay_direction']) ? $attr['overlay_direction'] : '180deg';
            $overlay_primary   = !empty($attr['overlay_primary']) ? $attr['overlay_primary'] : '#00b4db';
            $overlay_secondary = !empty($attr['overlay_secondary']) ? $attr['overlay_secondary'] : '#0083b0';

            return "background-image: linear-gradient({$overlay_direction}, {$overlay_primary} 0, {$overlay_secondary} 100%);";
        }
      ]);
    }
    if ('on' === ($this->props['border_anim'] ?? 'off')) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-izmir",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "
            --border-color: {$this->props['anm_border_color']};
            --border-width: {$this->props['anm_border_width']};
            --border-margin: {$this->props['anm_border_margin']};
            ",
      ]);
    }
    $common_styles[] = CommonStyle::style([
      'selector'            => "{$orderClass} .c4-izmir",
      'attr'                => $attrs['hover_settings']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "--padding: {$this->props['anm_content_padding']};",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector'            => "{$orderClass} .et-pb-icon.hover_icon",
      'attr'                => $attrs['hover_icon']['innerContent'] ?? [],
      'declarationFunction' => function () {
            return self::get_font_style( $this->props['hover_icon'] );
        }
    ]);
    $common_styles[] = CommonStyle::style([
      'selector'            => "{$orderClass} .et-pb-icon.instagram_icon",
      'attr'                => $attrs['instagram_icon']['innerContent'] ?? [],
      'declarationFunction' => function () {
            return self::get_font_style( $this->props['instagram_icon'] );
        }
    ]);

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .df_ing_image .df-instagram-user-profile-picture img",
        'attr'     => $attrs['instagram_settings']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .df_ing_image .item-content",
        'attr'     => $attrs['gallery_settings']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .df-instagram-user-info",
        'attr'     => $attrs['user_info']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .ing-load-more-btn",
        'attr'     => $attrs['load_more_btn']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    foreach ($common_styles as $common_style) {
      if (empty($common_style)) {
        continue;
      }

      foreach ($common_style as $style) {
        $styles[] = $style;
      }
    }

    return $styles ?? [];
  }

  public function render($attrs, $content, $block,  $render_slug) {

    $order_class   = $this->order_class; // self::get_module_order_class( $render_slug );
    $order_number  = $block->parsed_block['orderIndex'];
    // $this->handle_fa_icon();
    // $this->additional_css_styles($render_slug);
    wp_enqueue_script('imageload');
    wp_enqueue_script('df-imagegallery-lib');
    wp_enqueue_script('df-instagramgallery');

    $load_more = $this->props['load_more'];

    $image_ids = '';
    // $images_array = [];

    $instagram_client_id = (!empty($this->props['instagram_client_id'])) ? $this->props['instagram_client_id'] : '';
    $instagram_client_secret = (!empty($this->props['instagram_client_secret'])) ? $this->props['instagram_client_secret'] : '';
    $instagram_user_token = (!empty($this->props['instagram_user_token'])) ? $this->props['instagram_user_token'] : '';
    $item_limit = (!empty($this->props['item_limit'])) ? $this->props['item_limit'] : '';
    $cache_time = !empty($this->props['cache_time']) ? $this->props['cache_time'] : '-1';
    $cache_time_type = $this->props['cache_time_type'];

    $settings_data = array(
      'item_limit' => $item_limit,
      'cache_time' => $cache_time,
      'cache_time_type' => $cache_time_type,
      'unique_module' => $order_class // self::get_module_order_class($render_slug)
    );

    $instagram_obj = new \DF_Instagram_Process($instagram_client_id, $instagram_client_secret, $instagram_user_token);
    $images_array = $instagram_obj->get_instagram_data($settings_data, $order_class); // $order_class = self::get_module_order_class($render_slug)


    if ($this->props['instagram_post_only_image'] == 'on') {

      $media_type   = "IMAGE";
      $images_array = array_filter( $images_array, function ($value) use ($media_type) {
          return $value->media_type == $media_type;
        }, ARRAY_FILTER_USE_BOTH );
    }
    // post icon element generate
    $this->df_image_props = $this->props;
    // $this->df_image_props['caption_tag'] = $this->props['caption_tag'] ?? "h4" ;
    $this->df_image_props['unique_module_name']  = $order_class; // self::get_module_order_class($render_slug);
    $this->df_image_props['total_item'] = intval(count($images_array));


    $options = API::df_intagram_gallery_options(
      array('images_array' => $images_array),
      $this->df_image_props
    );

    $images = API::df_ing_render_images($options);
    $error_msg = "";
    $instagram_data = true;
    if (isset(($instagram_obj->get_instagram_account_id($instagram_user_token))->status) === 400) {
      $error_msg = "Your Instagram User access token is not valid";
      $instagram_data = false;
    } else if (intval(count($images_array)) < 1) {
      if ($item_limit < 0) {
        $error_msg = "Item limit should more then 0";
      } else {
        $error_msg = "No data found from instagram";
      }
      $instagram_data = false;
    } else {
      $instagram_data = true;
    }

    $user_token_exits_msg = $this->props['instagram_user_token'] == '' ? 'Please Enter Access token' :  $error_msg;

    $load_more_button = $load_more === 'on' && count($images_array) > $this->props['init_count']  ?
      sprintf(
        '<div class="df_ing_button_container">
          <button class="ing-load-more-btn" data-loaded="%2$s">%1$s
            <span class="spinner">
              <svg width="135" height="140" viewBox="0 0 135 140" xmlns="http://www.w3.org/2000/svg" fill="#fff">
                <rect y="10" width="15" height="120" rx="6">
                    <animate attributeName="height"
                        begin="0.5s" dur="1s"
                        values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                        repeatCount="indefinite" />
                    <animate attributeName="y"
                        begin="0.5s" dur="1s"
                        values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                        repeatCount="indefinite" />
                </rect>
                <rect x="30" y="10" width="15" height="120" rx="6">
                    <animate attributeName="height"
                        begin="0.25s" dur="1s"
                        values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                        repeatCount="indefinite" />
                    <animate attributeName="y"
                        begin="0.25s" dur="1s"
                        values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                        repeatCount="indefinite" />
                </rect>
                <rect x="60" width="15" height="140" rx="6">
                    <animate attributeName="height"
                        begin="0s" dur="1s"
                        values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                        repeatCount="indefinite" />
                    <animate attributeName="y"
                        begin="0s" dur="1s"
                        values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                        repeatCount="indefinite" />
                </rect>
                <rect x="90" y="10" width="15" height="120" rx="6">
                    <animate attributeName="height"
                        begin="0.25s" dur="1s"
                        values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                        repeatCount="indefinite" />
                    <animate attributeName="y"
                        begin="0.25s" dur="1s"
                        values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                        repeatCount="indefinite" />
                </rect>
                <rect x="120" y="10" width="15" height="120" rx="6">
                    <animate attributeName="height"
                        begin="0.5s" dur="1s"
                        values="120;110;100;90;80;70;60;50;40;140;120" calcMode="linear"
                        repeatCount="indefinite" />
                    <animate attributeName="y"
                        begin="0.5s" dur="1s"
                        values="10;15;20;25;30;35;40;45;50;0;10" calcMode="linear"
                        repeatCount="indefinite" />
                </rect>
              </svg>
            </span>
          </button>
        </div>',
        sanitize_text_field($this->props['load_more_text']),
        sanitize_text_field($this->props['init_count'])
      ) : '';

    $data = API::df_intagram_gallery_options(
      array('image_ids' => ''),
      $this->df_image_props
    );
    unset($data['instagram_user_token']);
    unset($data['instagram_client_secret']);
    unset($data['instagram_client_id']);

    // filter for images
    // if (array_key_exists('image', $this->advanced_fields) && array_key_exists('css', $this->advanced_fields['image'])) {
    //   $this->add_classname($this->generate_css_filters(
    //     $render_slug,
    //     'child_',
    //     self::$data_utils->array_get($this->advanced_fields['image']['css'], 'main', '%%order_class%%')
    //   ));
    // }
    if ($this->props['instagram_user_token'] !== '' && $instagram_data) {
      return sprintf(
        '<div class="df_ing_container" data-settings=\'%4$s\'>
            %2$s
            <div class="df_ing_gallery grid" style="opacity:1;">
                %1$s

            </div>
            %3$s
        </div>',
        $images,
        '',
        $load_more_button,
        wp_json_encode($data)
      );
    } else {
      return sprintf(
        '<div class="df_ing_container error-section">
                             %1$s
                            </div>',
        $user_token_exits_msg
      );
    }
  }

}
