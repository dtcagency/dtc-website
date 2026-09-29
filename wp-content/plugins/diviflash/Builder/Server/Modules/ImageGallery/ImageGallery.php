<?php
namespace DIFL\Server\Modules\ImageGallery;

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
use DIFL\admin\taxonomies\DiviFlash_Media_Category\DiviFlash_Media_Category;

use \DIFL\Server\API;


class ImageGallery implements DependencyInterface {

  use ModuleUtil;
  use HasProps;
  use StyleUtil;

  protected $config_file = 'ImageGallery';
  public static $content;
  protected $order_class;

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/imagegallery')->customCssFields;
  }

    function propMatches( $key, $value ) {
        return isset( $this->props[ $key ] ) && $this->props[ $key ] === $value;
    }

    function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

    function getOverlayStyle($attr){
        $styles = '';
        if ($this->propMatches('overlay', 'on')) {
            $overlay_direction = $attr['overlay_direction'] ?? '180deg';
            $overlay_primary   = $attr['overlay_primary'] ?? '#00b4db';
            $overlay_secondary = $attr['overlay_secondary'] ?? '#0083b0';
            $overlay_spacing = $attr['overlay_spacing'] ?? '';

            $styles  .= "background-image: linear-gradient(" . $overlay_direction . ", " . $overlay_primary . " 0, " . $overlay_secondary . " 100%);";
            $styles .= $overlay_spacing ? "margin: " . $overlay_spacing . ";" : "";
        }
        return $styles;
    }

  public function get_common_styles($args) {

    $attrs         = $args['attrs'] ?? [];
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

    $common_styles[] = $elements->style(['attrName' => 'caption_font_styles',]);
    $common_styles[] = $elements->style(['attrName' => 'description_font_styles',]);
    $common_styles[] = $elements->style(['attrName' => 'image',]);
    $common_styles[] = $elements->style(['attrName' => 'filter_buttons',]);
    $common_styles[] = $elements->style(['attrName' => 'active_filter_btn',]);
    $common_styles[] = $elements->style(['attrName' => 'load_more_button',]);
    $common_styles[] = $elements->style(['attrName' => 'pagination_button_styles',]);
    $common_styles[] = $elements->style(['attrName' => 'active_pagination',]);
    $common_styles[] = $elements->style(['attrName' => 'filter_button_container_spacing',]);

    // Converted styles from JSX to PHP
    if (!empty($this->props['image_to_display'])) {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_ig_gallery .grid-sizer, {$orderClass} .df_ig_gallery .grid-item",
        'attr' => $attrs['gallery_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props) {
            $items = $props['attrValue']['image_to_display'] ?? null;
            if( $items ) {
                return "width: " . (100 / $items) . "%;";
            }
            return '';
        }
      ]);
    }
    if($this->props['item_gutter'] !== '') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .grid .grid-item",
        'attr' => $attrs['gallery_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props) {
            if( isset($props['attrValue']['item_gutter']) ) {
                return "padding-left: {$props['attrValue']['item_gutter']}; padding-bottom: {$props['attrValue']['item_gutter']};";
            }
        }
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .grid ",
        'attr' => $attrs['gallery_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props) {
            if( isset($props['attrValue']['item_gutter']) ) {
                return "margin-left: {$props['attrValue']['item_gutter']};";
            }
        }
      ]);
    }
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .ig-load-more-btn .spinner svg",
      'attr' => $attrs['load_more_button']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "fill: {$this->props['spinner_color']};",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .df-ig-load-more-icon",
      'attr' => $attrs['load_more_button']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "font-size: {$this->props['more_btn_icon_size']};",
    ]);
    if ($this->props['image_scale'] === 'c4-image-rotate-left') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-image-rotate-left:hover img,{$orderClass} :focus.c4-image-rotate-left img",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "transform: scale({$this->props['image_scale_hover']}) rotate(-15deg);",
      ]);
    }
    if ($this->props['image_scale'] === 'c4-image-rotate-right') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-image-rotate-right:hover img,{$orderClass} :focus.c4-image-rotate-right img",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "transform: scale({$this->props['image_scale_hover']}) rotate(15deg);",
      ]);
    }
    if ($this->props['fliter_align'] !== '') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_filter_buttons",
        'attr' => $attrs['filter_buttons']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['fliter_align']};",
      ]);
    }
    if ($this->props['more_btn_align'] !== '') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df_ig_button_container",
        'attr' => $attrs['load_more_button']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "text-align: {$this->props['more_btn_align']};",
      ]);
    }
    if ($this->props['overlay'] !== 'on') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-izmir",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "--image-opacity: 1;",
      ]);
    }
    if ($this->props['overlay'] === 'on') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-izmir .df-overlay",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props){
            return $this->getOverlayStyle($props['attrValue'] ?? []);
        }
      ]);
    }
    if ($this->props['border_anim'] === 'on') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-izmir",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props){
            if( isset($props['attrValue']['anm_border_color']) ) {
              return "--border-color: {$props['attrValue']['anm_border_color']};";
            }
            return "--border-color: #fff;";
        }
      ]);
    }
    if ($this->props['border_anim'] === 'on') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-izmir",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props) {
            if( isset($props['attrValue']['anm_border_width']) ) {
               return "--border-width: {$props['attrValue']['anm_border_width']};";
            }
            return "--border-width: 3px;";
        }
      ]);
    }
    if ($this->props['border_anim'] === 'on') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .c4-izmir",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props) {
            if( isset($props['attrValue']['anm_border_margin']) ) {
                return "--border-margin: {$props['attrValue']['anm_border_margin']};";
            }
        }
      ]);
    }
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .c4-izmir",
      'attr' => $attrs['hover_settings']['innerContent'] ?? [],
      'declarationFunction' => function($props) {
            if( isset($props['attrValue']['anm_content_padding']) ) {
                return "--padding: {$props['attrValue']['anm_content_padding']};";
            }
        }
    ]);
    if ($this->props['field_use_icon'] === 'on') {
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df-overlay .et-pb-icon",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => function() {
            return "color: {$this->props['field_icon_color']};";
        }
      ]);

      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df-overlay .et-pb-icon",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => function($props) {
            $size = $props['attrValue']['field_icon_size'] ?? null;
            if( $size ) {
                return "font-size: {$size};";
            }
            return '';
        }
      ]);
      $common_styles[] = CommonStyle::style([
        'selector' => "{$orderClass} .df-overlay",
        'attr' => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn($props) => "display: flex; align-items:{$this->props['field_icon_placement']}; justify-content:{$this->props['field_icon_alignment']};",
      ]);
    }
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .pagination",
      'attr' => $attrs['pagination_button_styles']['innerContent'] ?? [],
      'declarationFunction' => function($props) {
        if($props['attrValue']['pagination_align']) {
            return "justify-content: {$props['attrValue']['pagination_align']};";
        }
      }
    ]);

    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .pagination .older:before,{$orderClass} .pagination .prev:before",
      'attr' => $attrs['pagination_button_styles']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "content: '{$this->ig_arrow_icon($this->props['next_prev_icon'], 'prev')}';",
    ]);
    $common_styles[] = CommonStyle::style([
      'selector' => "{$orderClass} .pagination .newer:after,{$orderClass} .pagination .next:after",
      'attr' => $attrs['pagination_button_styles']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => "content: '{$this->ig_arrow_icon($this->props['next_prev_icon'], 'next')}';",
    ]);

    $common_styles[] = CommonStyle::style([
      'selector'            => "{$orderClass} .df-ig-load-more-icon",
      'attr'                => $attrs['load_more_button']['innerContent'] ?? [],
      'declarationFunction' => fn($props) => self::get_font_style($this->props['more_btn_font_icon'] ?? '1'),
    ]);
    if ( $this->props['field_use_icon'] === 'on' ) {

      $common_styles[] = CommonStyle::style([
        'selector'            => "{$orderClass} .df-overlay .et-pb-icon",
        'attr'                => $attrs['hover_settings']['innerContent'] ?? [],
        'declarationFunction' => fn( $props ) => self::get_font_style( $this->props['field_font_icon'] ?? '1' ),
      ]);

    }

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .df_filter_buttons",
        'attr'     => $attrs['module']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .df_ig_image .item-content",
        'attr'     => $attrs['image']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .df_filter_buttons button",
        'attr'     => $attrs['filter_buttons']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .df_filter_buttons button.is-checked",
        'attr'     => $attrs['active_filter_btn']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .ig-load-more-btn",
        'attr'     => $attrs['load_more_button']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .pagination .page-numbers:not(.dots)",
        'attr'     => $attrs['pagination_button_styles']['decoration']['border'] ?? null,
        'declarationFunction' => function( $props ) {
            return $this->addOverflowHidden($props);
        },
    ] );

    $common_styles [] = CommonStyle::style( [
        'selector' => "{$orderClass} .pagination .page-numbers.current",
        'attr'     => $attrs['active_pagination']['decoration']['border'] ?? null,
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

  public function render($attrs, $content, $block, $render_slug) {

    $order_class   = $this->order_class; // self::get_module_order_class($render_slug);
    $order_number  = $block->parsed_block['orderIndex'];

    // $this->handle_fa_icon();
    // $this->additional_css_styles($render_slug);
    wp_enqueue_script('imageload');
    wp_enqueue_script('lightgallery-script');
    wp_enqueue_script('df-imagegallery-lib');
    // wp_enqueue_script('df-image-gallery');
    wp_enqueue_script('df-imagegallery');

    // $content = $this->content;
    $load_more = $this->props['load_more'];
    $filter_nav = $this->props['filter_nav'];
    $more_btn_use_icon = $this->props['more_btn_use_icon'];
    $show_pagination = $this->props['show_pagination'];

    $_string = '[' . substr(trim(wp_strip_all_tags($content)), 0, -1) . ']';
    $_objects = json_decode($_string);
    $filter_nav_markup = '';
    $filter_nav_buttons = '';
    $filter_nav_status = true;
    $image_ids = '';
    $images_array = [];
    if ('on' === $this->props['df_gallery_enable_category']) {
      if (!isset($this->props['category_ids'])) return;
      $tex_ids = $this->props['category_ids']; // category_ids
      // $tex_id = $this->props['category_id'];
      // echo '<pre>';
      // var_dump($tex_id);
      // echo '</pre>';
      // $tex_ids = explode(',', substr($tex_ids, 5, -2));

      foreach ($tex_ids as $index => $value) {
        // $term_id = (int)str_replace('"', '', $value);
        $term_id = $value['id'];
        // $term_id = $value;
        $term_name = get_term_by('id', $term_id, DiviFlash_Media_Category::TAXONOMY);
        $title = $term_name ? $term_name->name : "";
        $category = strtolower(preg_replace('/[^A-Za-z0-9-]/', "-", $title));
        $args = [
          'post_type' => 'attachment',
          'post_status' => 'inherit',
          'posts_per_page' => -1,
          'tax_query' => [
            [
              'taxonomy' => DiviFlash_Media_Category::TAXONOMY,
              'field'    => 'term_id',
              'terms'    => $term_id,
            ],
          ],
        ];
        $attachments = get_posts($args);

        if (!empty($attachments)) {

          $filter_nav_buttons .= sprintf('<button class="button %3$s" data-filter=".%1$s" onclick="igHandleClick(event)"> %2$s</button>', esc_attr($category), $title, ("on" === $this->props['disable_filter_nav_all_button'] && $filter_nav_status) ? "is-checked" : "");
          if ("on" === $this->props['disable_filter_nav_all_button'] && $filter_nav_status) {
            $filter_nav_status = false;
          }
          foreach ($attachments as $attachment) {
            $images_array[$attachment->ID][] = $category;
          }
        }
      }
    } else {
      foreach ($_objects as $_object) {
        $title = isset($_object->gallery_title) && $_object->gallery_title !== '' ?
          $_object->gallery_title : '';
        $category = isset($_object->gallery_title) && $_object->gallery_title !== '' ?
          strtolower(preg_replace('/[^A-Za-z0-9-]/', "-", $_object->gallery_title)) : '';

        $ids = !empty($_object->gallery_ids) ? explode(',', $_object->gallery_ids) : [];

        $filter_nav_buttons .= sprintf('<button class="button %3$s" data-filter=".%1$s" onclick="igHandleClick(event)">
                %2$s</button>', esc_attr($category), $title, ("on" === $this->props['disable_filter_nav_all_button'] && $filter_nav_status) ? "is-checked" : "");
        if ("on" === $this->props['disable_filter_nav_all_button'] && $filter_nav_status) {
          $filter_nav_status = false;
        }
        foreach ($ids as $id) {
          $images_array[$id][] = $category;
        }
      }
    }

    // image order
    if ('on' === $this->props['use_image_order']) {
      if ('' === $this->props['image_order'] || 'random' === $this->props['image_order']) {
        $images_array = df_shuffle_assoc($images_array);
      } else if ('asc' === $this->props['image_order']) {
        ksort($images_array);
      } else if ('desc' === $this->props['image_order']) {
        krsort($images_array);
      }
    }

    if (!empty($images_array)) {
      $image_ids = implode(", ", array_keys($images_array));
    }

    $options = API::df_ig_options(
      array('images_array' => $images_array),
      $this->props
    );

    $images = API::df_ig_render_images($options);

    // var_dump($images);
    // filter nav HLML
    if ('on' === $filter_nav && !empty($filter_nav_buttons)) {
      $first_btn = ('on' !== $this->props['disable_filter_nav_all_button']) ? sprintf('<button class="button is-checked" data-filter="*" onclick="igHandleClick(event)">%1$s</button>', "" !== $this->props['filter_nav_all_button'] ? esc_html($this->props['filter_nav_all_button']) : "All") : "";
      $filter_nav_markup = sprintf('<div class="df_filter_buttons">%2$s%1$s</div>', $filter_nav_buttons, $first_btn);
    }
    $load_more_icon = $more_btn_use_icon === 'on' ? sprintf(
      '<span class="df-ig-load-more-icon">%1$s</span>',
      isset( $this->props['more_btn_font_icon'] ) && $this->props['more_btn_font_icon'] !== '' ? esc_html( Utils::process_font_icon( $this->props['more_btn_font_icon'] ) ?? '4' ) : '4'
    ) : '';

    $load_more_icon_class = 'on' === $more_btn_use_icon ? ' has_icon' : '';

    $load_more_button = $load_more === 'on' ?
      sprintf(
        '<div class="df_ig_button_container">
                    <button class="ig-load-more-btn%4$s" data-loaded="%2$s">%1$s %3$s
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
                    </span></button>
                </div>',
        sanitize_text_field($this->props['load_more_text']),
        sanitize_text_field($this->props['init_count']),
        $load_more_icon,
        esc_attr($load_more_icon_class)
      ) : '';

    $older = $this->props['older_text'] ? $this->props['older_text'] : 'Older Entries';
    $next = $this->props['newer_text'] ? $this->props['newer_text'] : 'Next Entries';
    if ('on' === $this->props['use_icon_only_at_pagination']) {
      $older = '';
      $next = '';
    }
    $pagination_data = '';
    if ('on' === $show_pagination && count(array_filter(explode(',', $image_ids))) > (int)$this->props['pagination_img_count']) {
      ob_start();
    ?>
      <div class="df-ig-pagination pagination clearfix <?php echo 'on' === $this->props['use_icon_only_at_pagination'] ? 'only_icon' : ''; ?>">
        <a class="prev page-numbers" data-current="1" data-count="<?php echo esc_html($this->props['pagination_img_count']); ?>" href="#" style="display: none;"><?php echo esc_html($older); ?></a>
        <?php
        if ('on' === $this->props['use_number_pagination']) {
          for ($i = 0; $i < ceil(count($images_array) / (int)$this->props['pagination_img_count']); $i++) {
        ?>
            <a class="page-numbers <?php echo 0 === $i ? 'current' : ''; ?>" data-page="<?php echo esc_html(($i + 1)); ?>" data-count="<?php echo esc_html($this->props['pagination_img_count']); ?>" href="#"><?php echo esc_html(($i + 1)); ?></a>
        <?php
          }
        }
        ?>
        <a class="next page-numbers" data-current="1" data-count="<?php echo esc_html($this->props['pagination_img_count']); ?>" href="#"><?php echo esc_html($next); ?></a>
      </div>
      <?php
      $pagination_data = ob_get_clean();
    }

    $data = API::df_ig_options(
      array('image_ids' => $image_ids),
      $this->props
    );

    // filter for images
    // if (array_key_exists('image', $this->advanced_fields) && array_key_exists('css', $this->advanced_fields['image'])) {
    //   $this->add_classname($this->generate_css_filters(
    //     $render_slug,
    //     'child_',
    //     self::$data_utils->array_get($this->advanced_fields['image']['css'], 'main', '%%order_class%%')
    //   ));
    // }

    return sprintf(
      '<div class="df_ig_container%5$s" data-settings=\'%4$s\'>
                %2$s
                <div class="df_ig_gallery grid" style="opacity:0;">
                    %1$s
                </div>
                %3$s
                %6$s
            </div>',
      $images,
      $filter_nav_markup,
      strlen($images) > 0 ? $load_more_button : "",
      wp_json_encode($data),
      'on' === $this->props['use_lightbox'] ? ' ig_has_lightbox' : '',
      $pagination_data
    );
  }

  public function ig_arrow_icon($set = 'set_1', $type = 'next') {
    $icons = array(
      'set_1'  => array(
        'next' => '5',
        'prev' => '4'
      ),
      'set_2' => array(
        'next' => '$',
        'prev' => '#'
      ),
      'set_3' => array(
        'next' => '9',
        'prev' => '8'
      ),
      'set_4' => array(
        'next' => 'E',
        'prev' => 'D'
      )
    );
    return $icons[$set][$type];
  }

}
