<?php
namespace DIFL\Server\Modules\InstagramCarousel;

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


class InstagramCarousel implements DependencyInterface {

  use ModuleUtil;
  use HasProps;
  use StyleUtil;
  // use Fa_Icon_Handler;
  protected $config_file = 'InstagramCarousel';

  public static $content;
  public $parent;
  protected $order_class;

  protected $module_credits = array(
    'module_uri' => '',
    'author'     => 'DiviFlash',
    'author_uri' => '',
  );

  public static function custom_css() {
    $block = \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/instagramcarousel' );
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

    public function get_hover_values( $attrs, $key ) {
        if ( isset($attrs[$key]['innerContent']['desktop']['hover']) ) {
            return $attrs[$key]['innerContent']['desktop']['hover'];
        }
        return [];
    }

  public function get_common_styles($args) {

    $attrs         = $args['attrs'] ?? [];
    $orderClass    = $args['orderClass'] ?? '';
    $elements      = $args['elements'] ?? null;
    $settings      = $args['settings'] ?? [];
    $styles        = [];
    $common_styles = [];

    $state      = $args['state'] ?? null;

    if ( ! $elements ) {
      return $common_styles;
    }

    $arrows_hover = $this->get_hover_values($attrs, 'arrows');
    $arrow_color__hover = !empty($arrows_hover['arrow_color']) ? $arrows_hover['arrow_color'] : null;
    $arrow_bg_color__hover = !empty($arrows_hover['arrow_background']) ? $arrows_hover['arrow_background'] : null;
    $arrow_opacity__hover = !empty($arrows_hover['arrow_opacity']) ? $arrows_hover['arrow_opacity'] : null;
    $arrow_disable_opcaity__hover = !empty($arrows_hover['arrow_opacity_disable']) ? $arrows_hover['arrow_opacity_disable'] : null;

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
    $common_styles[] = $elements->style(['attrName' => 'arrows',]);
    $common_styles[] = $elements->style(['attrName' => 'arrow_next',]);
    $common_styles[] = $elements->style(['attrName' => 'icon',]);
    $common_styles[] = $elements->style(['attrName' => 'wrapper_spacing',]);
    $common_styles[] = $elements->style(['attrName' => 'item_wrap_spacing',]);
    
    if ( ('cube' !== ($this->props['carousel_type'] ?? 'slide')) && ('on' !== ($this->props['loop'] ?? 'off')) ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .swiper-wrapper",
        'attr' => ( $attrs['carousel_settings'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "width: fit-content;",
      ]);
    }
    if ( ( $this->props['ic_full_width'] ?? '' ) !== 'on' ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item img",
        'attr' => ( $attrs['image_settings'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "max-width: {$this->props['ic_max_width']};",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item .inc_image_wrapper",
        'attr' => ( $attrs['image_settings'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['ic_img_align']};",
      ]);
    }
    if ( ( $this->props['variable_width'] ?? '' ) === 'on' ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item img , {$orderClass} .media_item iframe",
        'attr' => ( $attrs['carousel_settings'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "height: {$this->props['item_height']};",
      ]);
    }
    if ( ( $this->props['ic_equal_height'] ?? '' ) === 'on' ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item",
        'attr' => ( $attrs['image_settings'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "height:auto;",
      ]);
      if ( ! empty( $this->props['ic_vertical'] ) ) {
        $common_styles[] = CommonStyle::style([
          'selector' => "{$orderClass} .media_item",
          'attr' => ( $attrs['image_settings'] ?? [] )['innerContent'] ?? [],
          'declarationFunction' => fn($props) => "align-self: {$this->props['ic_vertical']};",
        ]);
      }
    }
    if ( ( $this->props['ic_full_width'] ?? '' ) === 'on' ) {
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .inc_image_wrapper,{$orderClass} .inc_image_wrapper im",
            'attr' => ( $attrs['image_settings'] ?? [] )['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "align-self: {$this->props['ic_vertical']};",
        ]);
    }
    $common_styles[] = CommonStyle::style([
        'selector' => ".difl_instagramcarousel{$orderClass} .swiper-container-3d .swiper-slide-shadow-left",
        'attr' => ( $attrs['advanced_carousel_settings'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "background-image: linear-gradient(to left,{$this->props['coveflow_color_dark']},{$this->props['coveflow_color_light']});",
    ]);
    $common_styles[] = CommonStyle::style([
        'selector' => ".difl_instagramcarousel{$orderClass} .swiper-container-3d .swiper-slide-shadow-right",
        'attr' => ( $attrs['advanced_carousel_settings'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "background-image: linear-gradient(to right,{$this->props['coveflow_color_dark']},{$this->props['coveflow_color_light']});",
    ]);
    if ( ( $this->props['use_icon'] ?? '' ) === 'on' ) {
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .et-pb-icon.hover_icon",
            'attr' => ( $attrs['icon'] ?? [] )['innerContent'] ?? [],
            'declarationFunction' => function($props){
              $alignment = $props['attrValue']['hover_icon_alignment'] ?? null;
              return $alignment ? "text-align: {$alignment} !important;" : '';
            }
        ]);

          $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .et-pb-icon.hover_icon",
            'attr' => ( $attrs['icon'] ?? [] )['innerContent'] ?? [],
            'declarationFunction' => function($props){
              $color = $props['attrValue']['hover_icon_color'] ?? null;
              return $color ? "color: {$color} !important;" : '';
            }
        ]);

        $common_styles[] = CommonStyle::style([
          'selector' => "{$orderClass} .et-pb-icon.hover_icon",
          'attr' => ( $attrs['icon'] ?? [] )['innerContent'] ?? [],
          'declarationFunction' => function($props){
            $size = $props['attrValue']['hover_icon_size'] ?? null;
            return $size ? "font-size: {$size} !important;" : '';
          }
        ]);


        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .media_item .content",
            'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "display: none;",
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .media_item .et-pb-icon.hover_icon",
            'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "opacity: 0; transform: " . $this->df_transform_values( $this->props['icon_anim_direction'] ?? 'bottom' )['default'] . ";",
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .media_item:hover .et-pb-icon.hover_icon",
            'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "opacity: 1; transform: " . $this->df_transform_values( $this->props['icon_anim_direction'] ?? 'bottom' )['hover'] . ";",
        ]);
        if ( ( $this->props['always_show_icon'] ?? '' ) === 'on' ) {
            $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .media_item .et-pb-icon.hover_icon",
            'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
            'declarationFunction' => fn($props) => "opacity: 1; transform: none;",
            ]);
        }  
    }

    if ( ( $this->props['arrow_prev_icon_use_icon'] ?? '' ) === 'on' ) {
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df_inc_arrows div.swiper-button-prev:after",
            'attr'     => ( $attrs['arrows'] ?? [] )['innerContent'] ?? null,
            'declarationFunction' => function ( ) {
                return self::get_font_style($this->props["arrow_prev_icon_font_icon"]);
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df_inc_arrows div.swiper-button-prev:after",
            'attr'     => ( $attrs['arrows'] ?? [] )['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $size = ( $value['attrValue'] ?? [] )['arrow_prev_icon_icon_size'] ?? null;
                return $size ? "font-size: {$size};" : '';
            }
        ]);
    }
    if ( ( $this->props['arrow_next_icon_use_icon'] ?? '' ) === 'on' ) {
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df_inc_arrows div.swiper-button-next:after",
            'attr'     => ( $attrs['arrows'] ?? [] )['innerContent'] ?? null,
            'declarationFunction' => function ( ) {
                return self::get_font_style($this->props["arrow_next_icon_font_icon"]);
            }
        ]);
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df_inc_arrows div.swiper-button-next:after",
            'attr'     => ( $attrs['arrows'] ?? [] )['innerContent'] ?? null,
            'declarationFunction' => function ( $value ) {
                $size = ( $value['attrValue'] ?? [] )['arrow_next_icon_icon_size'] ?? null;
                return $size ? "font-size: {$size};" : '';
            }
        ]);
    }

    // $common_styles[] = CommonStyle::style([
    //   'selector' => "{$orderClass} .df_inc_arrows div:after",
    //   'attr' => $attrs['arrows']['innerContent'] ?? [],
    //   'declarationFunction' => fn($props) => "color: {$this->props['arrow_color']};",
    // ]);
    // $common_styles[] = CommonStyle::style([
    //   'selector' => "{$orderClass} .df_inc_arrows div",
    //   'attr' => $attrs['arrows']['innerContent'] ?? [],
    //   'declarationFunction' => fn($props) => "background-color: {$this->props['arrow_background']};",
    // ]);


    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .swiper-pagination span",
      'attr' => ( $attrs['dots'] ?? [] )['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "background: {$this->props['dots_color']};",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .swiper-pagination span.swiper-pagination-bullet-active",
      'attr' => ( $attrs['dots'] ?? [] )['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "background: {$this->props['active_dots_color']};",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .overlay_wrapper",
      'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "justify-content: {$this->props['vertical_align']};",
    ]);
    if ( ( $this->props['image_scale'] ?? '' ) === 'on' ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item:hover img",
        'attr' => ( $attrs['hover'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "transform: scale({$this->props['image_scale_value']});",
      ]);
    }
    if ( ( $this->props['always_show_content'] ?? 'off' ) === 'on' ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item .content",
        'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "opacity: 1;",
      ]);
    } else {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item .content",
        'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "opacity: 0;",
      ]);
    }
    if ( ( $this->props['always_show_content'] ?? 'off' ) === 'off' && ( $this->props['content_hover'] ?? '' ) === 'on' ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item .content",
        'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "opacity: 0; transform: " . $this->df_transform_values( $this->props['anim_direction'] ?? 'bottom' )['default'] . ";",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item:hover .content",
        'attr' => ( $attrs['content_area'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "opacity: 1; transform: " . $this->df_transform_values( $this->props['anim_direction'] ?? 'bottom' )['hover'] . ";",
      ]);
    }
    if ( ( $this->props['item_overflow'] ?? '' ) === 'on' ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .media_item > div",
        'attr' => ( $attrs['hover'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "overflow: hidden;",
      ]);
    }
    if ( ! empty( $this->props['dots_align'] ) ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .swiper-pagination",
        'attr' => ( $attrs['dots'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['dots_align']};",
      ]);
    }
    if ( ( $this->props['arrow'] ?? '' ) === 'on' ) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_inc_arrows",
        'attr' => ( $attrs['arrows'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => $this->df_arrow_pos_styles( $this->props['arrow_position'] ?? 'middle' ) . ";justify-content: " . ( $this->props['arrow_align'] ?? 'center' ) . ";",
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_inc_arrows div",
        'attr' => ( $attrs['arrows'] ?? [] )['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "opacity: " . ( $this->props['arrow_opacity'] ?? '1' ) . ";",
      ]);
      if ( ( $this->props['loop'] ?? 'on' ) !== 'on' ) {
        $common_styles[] = CommonStyle::style([
          'selector' => "{$orderClass} .df_inc_arrows div.swiper-button-disabled",
          'attr' => ( $attrs['arrows'] ?? [] )['innerContent'] ?? [],
          'declarationFunction' => fn($props) => "opacity: " . ( $this->props['arrow_opacity_disable'] ?? '0.5' ) . ";",
        ]);
      }
    }


    // Arrow Style Settings
    if ( $state === 'value' ) {
        $arrows_attr = ( $attrs['arrows'] ?? [] )['innerContent'] ?? null;
        // arrow icon color
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df_inc_arrows div:after",
            'attr'     => $arrows_attr,
            'declarationFunction' => function ( $value ) {
                $attr_value = $value['attrValue'] ?? [];
                $color = $attr_value['arrow_color'] ?? null;
                return $color ? "color: {$color};" : '';
            }
        ]);
        // arrow background and opacity
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass} .df_inc_arrows div",
            'attr'     => $arrows_attr,
            'declarationFunction' => function ( $value ) {
                $attr_value = $value['attrValue'] ?? [];
                $bg      = $attr_value['arrow_background'] ?? null;
                $opacity = $attr_value['arrow_opacity'] ?? '1';

                $css = '';
                if ( $bg ) $css .= "background-color: {$bg};";
                $css .= "opacity: {$opacity};";
                return $css;
            }
        ]);
        // arrow disable opacity
        if ( ( $this->props['loop'] ?? 'on' ) === 'off' ) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_inc_arrows div.swiper-button-disabled",
                'attr'     => $arrows_attr,
                'declarationFunction' => function ( $value ) {
                    $attr_value = $value['attrValue'] ?? [];
                    $opacity = $attr_value['arrow_opacity_disable'] ?? '0.5';
                    return "opacity: {$opacity} !important;";
                }
            ]);
        }
    }


    $arrows_inner = ( $attrs['arrows'] ?? [] )['innerContent'] ?? null;
    if ( $arrow_bg_color__hover !== null && $arrows_inner !== null ) {
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass}:hover .df_inc_arrows div",
            'attr'     => $arrows_inner,
            'declarationFunction' => function ( $props ) {
                $hover = ( $props['attr']['desktop'] ?? [] )['hover'] ?? [];
                $bg = $hover['arrow_background'] ?? null;
                return $bg ? 'background-color: ' . $bg . ' !important;' : '';
            }
        ]);
    }
    if ( $arrow_color__hover !== null && $arrows_inner !== null ) {
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass}:hover .df_inc_arrows div:after",
            'attr'     => $arrows_inner,
            'declarationFunction' => function ( $props ) {
                $hover = ( $props['attr']['desktop'] ?? [] )['hover'] ?? [];
                $color = $hover['arrow_color'] ?? null;
                return $color ? 'color: ' . $color . ' !important;' : '';
            }
        ]);
    }
    if ( $arrow_opacity__hover !== null && $arrows_inner !== null ) {
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass}:hover .df_inc_arrows div:after",
            'attr'     => $arrows_inner,
            'declarationFunction' => function ( $props ) {
                $hover = ( $props['attr']['desktop'] ?? [] )['hover'] ?? [];
                $arrow_opacity = $hover['arrow_opacity'] ?? null;
                return $arrow_opacity !== null ? 'opacity: ' . $arrow_opacity . ' !important;' : '';
            }
        ]);
    }
    if ( $arrow_disable_opcaity__hover !== null && $arrows_inner !== null && ( $this->props['loop'] ?? 'on' ) !== 'on' ) {
        $common_styles[] = CommonStyle::style([
            'selector' => "{$orderClass}:hover .df_inc_arrows div.swiper-button-disabled",
            'attr'     => $arrows_inner,
            'declarationFunction' => function ( $props ) {
                $hover = ( $props['attr']['desktop'] ?? [] )['hover'] ?? [];
                $arrow_opacity_disable = $hover['arrow_opacity_disable'] ?? null;
                return $arrow_opacity_disable !== null ? 'opacity: ' . $arrow_opacity_disable . ' !important;' : '';
            }
        ]);
    }

    $common_styles[] = CommonStyle::style( [
      'selector'            => "{$orderClass} .swiper-button-prev:after",
      'attr'                => $attrs['arrows']['innerContent'] ?? [],
      'declarationFunction' => fn( $props ) => self::get_font_style( $this->props['arrow_prev_icon_font_icon'] ?? '1' ),
    ] );
    $common_styles[] = CommonStyle::style( [
      'selector'            => "{$orderClass} .swiper-button-next:after",
      'attr'                => $attrs['arrows']['innerContent'] ?? [],
      'declarationFunction' => fn( $props ) => self::get_font_style( $this->props['arrow_next_icon_font_icon'] ?? '1' ),
    ] );
    $common_styles[] = CommonStyle::style( [
      'selector'            => "{$orderClass} .et-pb-icon.hover_icon",
      'attr'                => $attrs['content_area']['innerContent'] ?? [],
      'declarationFunction' => fn( $props ) => self::get_font_style( $this->props['hover_icon'] ?? '1' ),
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


  public function df_render_hover_icon() {
    $hover_icon = $this->props['hover_icon'] !== '' ?
      esc_html( Utils::process_font_icon( $this->props['hover_icon'] ) ?? 'P' ) : 'P';
    return $hover_icon;
  }

  public function render($attrs, $content, $block,  $render_slug) {

    // $this->handle_fa_icon();
    wp_enqueue_style('swiper-style');
    wp_enqueue_script('swiper-script');
    wp_enqueue_script('df-instagramcarousel');
    // $this->additional_css_styles($render_slug);
    $order_class   = $this->order_class; // self::get_module_order_class( $render_slug );
    $order_number  = $block->parsed_block['orderIndex']; // str_replace('_','',str_replace($this->slug,'', $order_class));

    $instagram_client_id = (!empty($this->props['instagram_client_id'])) ? $this->props['instagram_client_id'] : '';
    $instagram_client_secret = (!empty($this->props['instagram_client_secret'])) ? $this->props['instagram_client_secret'] : '';
    $instagram_user_token = (!empty($this->props['instagram_user_token'])) ? $this->props['instagram_user_token'] : '';
    $item_limit = (!empty($this->props['item_limit'])) ? $this->props['item_limit'] : '';

    $instagram_obj = new \DF_Instagram_Process($instagram_client_id, $instagram_client_secret, $instagram_user_token);


    $cache_time = !empty($this->props['cache_time']) ? $this->props['cache_time'] : '1';

    $cache_time_type = $this->props['cache_time_type'];
    $settings_data = array(
      'item_limit' => $item_limit,
      'cache_time' => $cache_time,
      'cache_time_type' => $cache_time_type,
      'unique_module' => $order_class
    );
    $data = $instagram_obj->get_instagram_data($settings_data, $order_class);

    if (count($data) > 0) {
      if ($this->props['instagram_post_only_image'] == 'on') {

        $media_type = "IMAGE";
        $data = array_filter($data, function ($value) use ($media_type) {
          return $value->media_type == $media_type;
        }, ARRAY_FILTER_USE_BOTH);
      }
    }

    $error_msg = "";
    $instagram_data = true;
    //if (isset(($instagram_obj->get_instagram_account_id($instagram_user_token) )->status) === 400 ) {
    if (
      is_object($instagram_obj->get_instagram_account_id($instagram_user_token)) &&
      isset($instagram_obj->get_instagram_account_id($instagram_user_token)->status) &&
      $instagram_obj->get_instagram_account_id($instagram_user_token)->status === 400
    ) {

      $error_msg = "Your Instagram User access token is not valid";
      $instagram_data = false;
    } else if (intval(count($data)) < 1) {
      if ($item_limit < 0) {
        $error_msg = "Item limit should more then 0";
      } else {
        $error_msg = "No data found from instagram";
      }
      $instagram_data = false;
    } else {
      $instagram_data = true;
    }

    $module_error_msg = $this->props['instagram_user_token'] == '' ?
      '<div class="instagram-carousel-error"> Please Enter Access token </div>' :
      sprintf('<div class="instagram-carousel-error"> %1$s </div>', $error_msg);

    $item_html = '';
    $always_show_icon = ($this->props['use_icon'] === 'on' && $this->props['always_show_icon'] === 'on') ? 'always_show_icon' : '';
    $icon = $this->props['use_icon'] === 'on' ?
      sprintf('<span class="et-pb-icon hover_icon %2$s">%1$s</span>', $this->df_render_hover_icon(), $always_show_icon) : '';
    
    if ($this->props['instagram_user_token'] !== '' && $instagram_data) {
      foreach ($data as $item) {
        $custom_url = $this->props['use_url'] === 'on' ?
          sprintf('data-url="%1$s"', $item->permalink)
          : '';
        $autoplay_video = ($this->props['autoplay_video'] === 'on' && $this->props['instagram_post_only_image'] === 'off') ? 'autoplay' : '';
        $content = sprintf('%1$s', !empty($item->caption) ? '<div class="content">' . $item->caption . '</div>' : '');
        $media_type = ('VIDEO' == $item->media_type) ? 'video' : 'img';
        $overlay_wrapper = ('VIDEO' == $item->media_type) ? '' : sprintf('<div class="overlay_wrapper">%1$s %2$s</div>', $icon, $content);
        $media_html = '';
        if ($item->media_type !== 'VIDEO') {
          $media_html .=  sprintf('<div class="inc_image_wrapper">
                                            <%1$s src="%2$s" title="Image by: %3$s"/>
                                    </div>', $media_type, $item->media_url, $item->username);
        } else {
          $media_html .=  sprintf(
            '<div class="inc_image_wrapper">
                                        <video id="instagram-video" src=%1$s controls %2$s></video>
                                        
                                    </div>',
            $item->media_url,
            $autoplay_video
          );
        }

        $item_html .= sprintf('<div class="media_item swiper-slide" %1$s >
                                <div>
                                    <div class="df_inci_container">
                                        %2$s
                                        %3$s
                                    </div>
                                        
                                </div>
                            </div>', $custom_url, $media_html, $overlay_wrapper);
      }
    } else {
      $item_html .= $module_error_msg;
    }

    $class = '';
    $carousel_inner = ( $attrs['carousel_settings'] ?? [] )['innerContent'] ?? [];
    $carousel_tablet_value = ( $carousel_inner['tablet'] ?? [] )['value'] ?? [];
    $carousel_phone_value  = ( $carousel_inner['phone'] ?? [] )['value'] ?? [];
    $maxSlideDesktop = $this->props['max_slide_desktop'] ?? '4';
    $maxSlideTablet  = $this->props['max_slide_tablet'] ?? $maxSlideDesktop;
    $maxSlidePhone   = $this->props['max_slide_mobile'] ?? $maxSlideTablet;
    $itemSpacingDesktop = $this->props['item_spacing'] ?? '30px';
    $itemSpacingTablet  = $carousel_tablet_value['item_spacing'] ?? $itemSpacingDesktop;
    $itemSpacingPhone   = $carousel_phone_value['item_spacing'] ?? $itemSpacingTablet; 
    $data = [
      'effect' => $this->props['carousel_type'],
      'desktop' => $maxSlideDesktop,
      'tablet' => $maxSlideTablet, // item_tablet
      'mobile' => $maxSlidePhone, // item_mobile
      'variable_width' => $this->props['variable_width'],
      'loop' => $this->props['loop'] === 'on' ? true : false,
      'item_spacing' => $itemSpacingDesktop,
      'item_spacing_tablet' => $itemSpacingTablet,
      'item_spacing_phone' => $itemSpacingPhone,
      'arrow' => $this->props['arrow'],
      'dots' => $this->props['dots'],
      'autoplay' => $this->props['autoplay'],
      'auto_delay' => $this->props['autospeed'],
      'speed' => $this->props['speed'],
      'pause_hover' => $this->props['pause_hover'],
      'centeredSlides' => $this->props['centered_slides'],
      'order' => $order_number,
      'url_target' => $this->props['url_target']
    ];
    if ($this->props['carousel_type'] === 'coverflow') {
      $data['slideShadows'] = $this->props['coverflow_shadow'];
      $data['rotate'] = $this->props['coverflow_rotate'];
      $data['stretch'] = $this->props['coverflow_stretch'];
      $data['depth'] = $this->props['coverflow_depth'];
      $data['modifier'] = $this->props['coverflow_modifier'];
    }

    if (isset($this->props['variable_width']) && $this->props['variable_width'] === 'on') {
      $class .= ' variable-width';
    }

    // arrow position classes
    if ($this->props['arrow'] === 'on') {
      $arrow_position = '' !== $this->props['arrow_position'] ? $this->props['arrow_position'] : 'middle';
      $class .= ' arrow-' . $arrow_position;
    }

    return sprintf(
      '<div class="df_inc_container%5$s" data-settings=\'%4$s\' data-item="%6$s" data-itemtablet="%7$s" data-itemphone="%8$s">
        <div class="df_inc_inner_wrapper">
          <div class="swiper-container">
            <div class="swiper-wrapper">
              %1$s
            </div>
          </div>
          %2$s
        </div>
        %3$s
      </div>',
      $item_html,
      $this->df_ic_arrow($order_number),
      $this->df_ic_dots($order_number),
      wp_json_encode($data),
      $class,
      $maxSlideDesktop, // item_desktop
      $maxSlideTablet, // item_tablet
      $maxSlidePhone // item_mobile
    );
  }

  /**
   * Arrow Position styles
   * 
   * @param String | position
   * @return String
   */
  public function df_arrow_pos_styles($value) {
    $options = array(
      'top' => 'position: relative; 
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 0;',
      'middle' => 'position: absolute; 
                        top: 50%;
                        left: 0;
                        right: 0;
                        transform: translateY(-50%);',
      'bottom' => 'position: relative; 
                    top: auto;
                    left: auto;
                    right: auto;
                    transform: translateY(0);
                    order: 2;',
    );
    return $options[$value];
  }

  /**
   * Arrow navigation
   * 
   * @param Integer | $order_number
   * @return String | HTML
   */
  public function df_ic_arrow($order_number) {

    $prev_icon = $this->props['arrow_prev_icon_use_icon'] === 'on' && isset( $this->props['arrow_prev_icon_font_icon'] ) && ! empty( $this->props['arrow_prev_icon_font_icon'] ) ?
      esc_attr( Utils::process_font_icon( $this->props['arrow_prev_icon_font_icon'] ) ?? '4' ) : '4';
    $next_icon = $this->props['arrow_next_icon_use_icon'] === 'on' && isset( $this->props['arrow_next_icon_font_icon'] ) && ! empty( $this->props['arrow_next_icon_font_icon'] ) ?
      esc_attr( Utils::process_font_icon( $this->props['arrow_next_icon_font_icon'] ) ?? '5' ) : '5';

    return $this->props['arrow'] === 'on' ? sprintf('
            <div class="df_inc_arrows">
                <div class="swiper-button-next inc-next-%1$s" data-icon="%3$s"></div>
                <div class="swiper-button-prev inc-prev-%1$s" data-icon="%2$s"></div>
            </div>
        ', $order_number, $prev_icon, $next_icon) : '';
  }

  /**
   * Dot pagination
   * 
   * @param Integer | $order_number
   * @return String | HTML
   */
  public function df_ic_dots($order_number) {
    return $this->props['dots'] === 'on' ? sprintf('<div class="swiper-pagination inc-dots-%1$s"></div>', $order_number) : '';
  }

  /**
   * Get transform values
   * 
   * @param String $key
   * @param String | State
   */
  public function df_transform_values($key = 'bottom') {
    $transform_values = array(
      'top'           => [
        'default'   => 'translateY(-60px)',
        'hover'     => 'translateY(0px)'
      ],
      'bottom'        => [
        'default'   => 'translateY(60px)',
        'hover'     => 'translateY(0px)'
      ],
      'left'          => [
        'default'   => 'translateX(-60px)',
        'hover'     => 'translateX(0px)'
      ],
      'right'         => [
        'default'   => 'translateX(60px)',
        'hover'     => 'translateX(0px)'
      ],
      'center'        => [
        'default'   => 'scale(0)',
        'hover'     => 'scale(1)'
      ],
      'top_right'     => [
        'default'   => 'translateX(50px) translateY(-50px)',
        'hover'     => 'translateX(0px) translateY(0px)'
      ],
      'top_left'      => [
        'default'   => 'translateX(-50px) translateY(-50px)',
        'hover'     => 'translateX(0px) translateY(0px)'
      ],
      'bottom_right'  => [
        'default'   => 'translateX(50px) translateY(50px)',
        'hover'     => 'translateX(0px) translateY(0px)'
      ],
      'bottom_left'   => [
        'default'   => 'translateX(-50px) translateY(50px)',
        'hover'     => 'translateX(0px) translateY(0px)'
      ]
    );
    return $transform_values[$key];
  }

}
