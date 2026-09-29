<?php
namespace DIFL\Server\Modules\Divider;

if (!defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\Packages\Module\Module;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;


class Divider implements DependencyInterface
{

	public static function module_script_data( $args ) {
		$elements = $args['elements'];

		$elements->script_data(
			[
				'attrName' => 'module',
			]
		);
	}
  // module render callback which outputs server side rendered HTML on the Front-End.
  public static function render_callback($attrs, $content, $block, $elements) {
    $parent = BlockParserStore::get_parent($block->parsed_block['id'], $block->parsed_block['storeInstance']);
    $parent_attrs = $parent->attrs ?? [];

    $use_image_as_icon = $attrs['use_image_as_icon']['innerContent']['desktop']['value'] ?? 'off';
    $image_as_icon = $attrs['image_as_icon']['innerContent']['desktop']['value'] ?? '';
    $image_alt_text = $attrs['image_alt_text']['innerContent']['desktop']['value'] ?? '';
    $separator_type = $attrs['separator_type']['innerContent']['desktop']['value'] ?? 'icon';
    $font_icon = $attrs['separator_settings']['decoration']['icon']['desktop']['value'] ?? '';
    $lineNumber = $attrs['line_number']['innerContent']['desktop']['value'] ?? 2;
    $title = $attrs['title']['innerContent']['desktop']['value'] ?? 'Title';
    $iconImgAlign = $attrs['icon_image_alignment']['innerContent']['desktop']['value'] ?? 'center';
    $use_multiple_line = $attrs['use_multiple_line']['innerContent']['desktop']['value'] ?? 'off';


    $icon = '';

    if ($separator_type === 'icon') {
      if (!$font_icon || $font_icon === '') {
        $icon = '1';
      } else {
        $icon = Utils::process_font_icon($font_icon);
      }
    }

    if ($use_image_as_icon === 'on' && $separator_type === 'icon') {
      $iconHtml = '<div class="difl-divider-icon"><img class="separator-image-icon" src="' . esc_attr($image_as_icon) . '" alt="' . esc_attr($image_alt_text) . '" /></div>';
    } else {
      $iconHtml = '<span class="et-pb-icon difl-divider-icon">' . esc_html($icon) . '</span>';
    }

    $headingTag = $attrs['title_tag']['innerContent']['desktop']['value'] ?? 'h3';
    $title_text = $attrs['title']['innerContent']['desktop']['value'] ?? 'Title';
    $divider_type = $attrs['divider_type']['innerContent']['desktop']['value'] ?? 'solid';

    // Title.
    $title = sprintf('<div class="difl-divider-icon"><%2$s class="difl-divider-icon-text">%1$s</%2$s></div>', $title_text, $headingTag);

    $contentHtml = '';
    if ($separator_type === 'text') {
      $contentHtml = $title;
    } elseif ($separator_type === 'icon') {
      $contentHtml = $iconHtml;
    } elseif ($separator_type === 'lottie') {
      $contentHtml = '<div class="difl-divider-lottie-image difl-divider-icon"></div>';
    }
    
    $contentHtml = !empty($contentHtml)
      ? sprintf('<div class="difl-divider-icon-container"><div class="difl-divider-icon-wrap">%s</div></div>', $contentHtml)
      : '';

    $line_number = $use_multiple_line === 'on' ? $lineNumber : 1;

    $hr_content = str_repeat('<hr/>', $line_number);

    $path = '';
    $data = [];
    if ($separator_type === 'lottie') {
      
      wp_enqueue_script('df-lottie-lib');
      wp_enqueue_script('df_divider');
      
      if (($attrs['lottie_file_options']['innerContent']['desktop']['value'] ?? '') !== 'media') {
        $path = $attrs['external_file']['innerContent']['desktop']['value'] ?? '';
      } elseif (!empty($attrs['upload']['innerContent']['desktop']['value'])) {
        $path = $attrs['upload']['innerContent']['desktop']['value'];
      }

      $data = array(
        'divider_type'      => $divider_type,
        'separetor_type'    => $separator_type,
        'path'              => $path,
        'loop'              => ($attrs['loop']['innerContent']['desktop']['value'] ?? 'on') === 'on' ? true : false,
        'speed'             => $attrs['speed']['innerContent']['desktop']['value'] ?? '1',
        'direction_reverse' => $attrs['direction_reverse']['innerContent']['desktop']['value'] ?? '',
        'renderer'          => $attrs['renderer']['innerContent']['desktop']['value'] ?? 'svg',
        'animation_trigger' => $attrs['animation_trigger']['innerContent']['desktop']['value'] ?? 'none',
        'threshold'         => $attrs['threshold']['innerContent']['desktop']['value'] ?? '0',
        'stop_on_mouse_out' => $attrs['stop_on_mouse_out']['innerContent']['desktop']['value'] ?? '',
      );
    }

    $difl_advanced_divider_item = sprintf(
      '<div class="difl-divider-container difl-divider-%1$s"  data-settings=\'%2$s\'>
        <div class="difl-divider-wrapper">
          <div class="difl-divider-wrapper-separator">
            <div class="difl-divider-wrapper-separator-divider">
              <div class="difl-divider-inner">
                <div class="difl-divider-content-wrapper icon-type-%3$s">
                  %4$s
                  <div class="difl-divider-left difl-divider-left-side">%5$s</div>
                  %6$s
                  <div class="difl-divider-right difl-divider-right-side">%7$s</div>
                  %8$s
                </div>
              </div>
            </div>
          </div>
          <div class="difl-clearfix"></div>
        </div>
      </div>',
      esc_attr($divider_type), // %1$s:
      wp_json_encode($data), // %2$s: 
      esc_attr($separator_type), // %3$s:
      'left' === $iconImgAlign ? $contentHtml : '', // %4$s:
      $hr_content, // %5$s:
      'center' === $iconImgAlign ? $contentHtml : '', // %6$s:
      $hr_content, // %7$s:
      'right' === $iconImgAlign ? $contentHtml : '' // %8$s:
    );

    self::register_divi_assets();

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
				'scriptDataComponent'=> [ self::class, 'module_script_data' ],
        'parentAttrs'        => $parent_attrs,
        'parentId'           => $parent->id ?? '',
        'parentName'         => $parent->blockName ?? '',
        'children'           => ElementComponents::component(
          [
            'attrs'         => $attrs['module']['decoration'] ?? [],
            'id'            => $block->parsed_block['id'],

            // FE only.
            'orderIndex'    => $block->parsed_block['orderIndex'],
            'storeInstance' => $block->parsed_block['storeInstance'],
          ]
        ) . $difl_advanced_divider_item,
      ]
    );
  }

  public static function difl_load_required_divi_assets($assets_list, $assets_args, $instance) {

    $temp_url      = get_template_directory_uri();
    $icons_all     = $temp_url . "/includes/builder/feature/dynamic-assets/assets/css/icons_all.css";
    $icons_fa_all = $temp_url . "/includes/builder/feature/dynamic-assets/assets/css/icons_fa_all.css";

    if (! isset($assets_list['et_icons_all'])) {
      $assets_list['et_icons_all'] = [
        'css' => $icons_all,
      ];
    }

    if (! isset($assets_list['et_icons_fa'])) {
      $assets_list['et_icons_fa'] = [
        'css' => $icons_fa_all,
      ];
    }

    return $assets_list;
  }

  // Register the required Divi assets dynamically.
  public static function register_divi_assets() {

    add_filter('divi_frontend_assets_dynamic_assets_global_assets_list', [self::class, 'difl_load_required_divi_assets'], 10, 3);
    add_filter('divi_frontend_assets_dynamic_assets_late_global_assets_list', [self::class, 'difl_load_required_divi_assets'], 10, 3);
  }

  public static function classnames($args) {
    $classnames_instance = $args['classnamesInstance'];
    $attrs               = $args['attrs'];

    $text_options_classnames = TextClassnames::text_options_classnames($attrs['module']['advanced']['text'] ?? []);

    if ($text_options_classnames) {
      $classnames_instance->add($text_options_classnames, true);
    }
  }

  public function load() {
    $module_json_folder_path = DIFL_MODULES_JSON_PATH . 'Divider/';

    add_action(
      'init',
      function () use ($module_json_folder_path) {
        ModuleRegistration::register_module(
          $module_json_folder_path,
          [
            'render_callback' => [self::class, 'render_callback'],
          ]
        );
      }
    );
  }

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/divider')->customCssFields;
  }

  public static function styles($args) {
    $attrs        = $args['attrs'] ?? [];
    $parent_attrs = $args['parentAttrs'] ?? [];
    $order_class  = $args['orderClass'];
    $elements     = $args['elements'];
    $settings     = $args['settings'] ?? [];
    $icon_selector= "{$order_class} .et-pb-icon";
    $dividerType  = $attrs['divider_type']['innerContent']['desktop']['value'] ?? 'solid';

    $cLeftHover  = $dividerType === 'custom' ? '.difl-divider-left-side hr:hover' : '.difl-divider-left-side:hover hr';
    $cRightHover = $dividerType === 'custom' ? '.difl-divider-right-side hr:hover' : '.difl-divider-right-side:hover hr';

    Style::add(
      [
        'id'            => $args['id'],
        'name'          => $args['name'],
        'orderIndex'    => $args['orderIndex'],
        'storeInstance' => $args['storeInstance'],
        'styles'        => [
          // Module.
          $elements->style(
            [
              'attrName'   => 'module',
              'styleProps' => [
                'disabledOn' => [
                  'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
                ],
              ],
            ]
          ),

          CssStyle::style(
            [
              'selector'  => $args['orderClass'],
              'attr'      => $attrs['css'] ?? [],
              'cssFields' => self::custom_css(),
            ]
          ),

          // Background, Spacing, Font, Border, BoxShadow, icon
          $elements->style(['attrName' => 'separator_settings',]),
          $elements->style(['attrName' => 'custom_divider_border',]),

          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider",
							'attr'     => $attrs['line_alignment']['innerContent'] ?? '',
							'property' => 'text-align',
						]
          ),
         
          (($attrs['separator_type']['innerContent']['desktop']['value'] ?? 'icon') !== 'no') ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-content-wrapper",
							'attr'     => $attrs['line_placement']['innerContent'] ?? '',
							'property' => 'align-items',
						]
					) : [],

          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-icon",
							'attr'     => $attrs['icon_background']['innerContent'] ?? '',
							'property' => 'background-color',
						]
          ),
         
          (($attrs['separator_type']['innerContent']['desktop']['value'] ?? 'icon') === 'icon') ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .et-pb-icon.difl-divider-icon",
							'attr'     => $attrs['icon_size']['innerContent'] ?? '',
							'property' => 'font-size',
						]
					) : [],
          (($attrs['separator_type']['innerContent']['desktop']['value'] ?? 'icon') === 'icon') ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .et-pb-icon.difl-divider-icon",
							'attr'     => $attrs['separator_icon_color']['innerContent'] ?? '',
							'property' => 'color',
						]
					) : [],

          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-icon img.separator-image-icon",
							'attr'     => $attrs['image_as_icon_width']['innerContent'] ?? '',
							'property' => 'width',
						]
          ),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-lottie-image.difl-divider-icon",
							'attr'     => $attrs['lottie_image_width']['innerContent'] ?? '',
							'property' => 'width',
						]
          ),

          ($dividerType !== 'curvedtop' && $dividerType !== 'gradient' && 'custom' !== $dividerType) ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-left-side hr, {$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side hr",
							'attr'     => $attrs['divider_line_width']['innerContent'] ?? '',
							'property' => 'border-top-width',
						]
					) : [],

          ( $dividerType === 'curvedtop' ) ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-left-side hr, {$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side hr",
							'attr'     => $attrs['divider_line_width']['innerContent'] ?? '',
							'property' => 'border-bottom-width',
						]
					) : [],
          ( $dividerType === 'gradient' || $dividerType === 'custom' ) ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-left-side hr, {$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side hr",
							'attr'     => $attrs['divider_line_width']['innerContent'] ?? '',
							'property' => 'height',
						]
					) : [],

          ( $dividerType === 'curvedtop' ) ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-left-side hr, {$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side hr, {$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-left-side:hover hr, {$order_class} .difl-divider-container .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side:hover hr",
							'attr'     => $attrs['divider_line_color']['innerContent'] ?? '',
							'property' => 'border-bottom-color',
						]
					) : 
          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-left-side hr, {$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side hr, {$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider {$cLeftHover}, {$order_class} .difl-divider-container .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider {$cRightHover}",
							'attr'     => $attrs['divider_line_color']['innerContent'] ?? '',
							'property' => 'border-top-color',
						]
					),

          ( $dividerType === 'curvedtop' ) ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side hr, {$order_class} .difl-divider-container .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side:hover hr",
							'attr'     => $attrs['divider_right_line_color']['innerContent'] ?? '',
							'property' => 'border-bottom-color',
						]
					) : 
          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side hr, {$order_class} .difl-divider-container .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider {$cRightHover}",
							'attr'     => $attrs['divider_right_line_color']['innerContent'] ?? '',
							'property' => 'border-top-color',
						]
					),

          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-left-side",
							'attr'     => $attrs['divider_left_line_width']['innerContent'] ?? '',
							'property' => 'width',
						]
					),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side",
							'attr'     => $attrs['divider_right_line_width']['innerContent'] ?? '',
							'property' => 'width',
						]
					),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-left-side",
							'attr'     => $attrs['divider_line_spacing']['innerContent'] ?? '',
							'property' => 'padding-right',
						]
					),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-right-side",
							'attr'     => $attrs['divider_line_spacing']['innerContent'] ?? '',
							'property' => 'padding-left',
						]
					),

          ( ($attrs['use_multiple_line']['innerContent']['desktop']['value'] ?? 'off') !== 'off' ) ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-left hr:not(:last-child), {$order_class} .difl-divider-wrapper-separator .difl-divider-right hr:not(:last-child)",
							'attr'     => $attrs['multiple_line_gap']['innerContent'] ?? '',
							'property' => 'margin-bottom',
						]
					) : [],
          
          ( $dividerType === 'gradient' || $dividerType === 'custom' ) 
          ? $elements->style([
            'attrName'   => 'custom_line_bg_with_gradient',
            'styleProps' => [
              'selector' => "{$order_class} .difl-divider-{$dividerType} .difl-divider-left-side hr, {$order_class} .difl-divider-{$dividerType} {$cLeftHover},{$order_class} .difl-divider-{$dividerType} .difl-divider-right-side hr, {$order_class} .difl-divider-{$dividerType} {$cRightHover}"
            ]
          ]) 
          : [],

          ( $dividerType === 'gradient' || $dividerType === 'custom' ) 
          ? $elements->style([
            'attrName'   => 'line_design',
            'styleProps' => [
              'selector' => "{$order_class} .difl-divider-{$dividerType} .difl-divider-right-side hr, {$order_class} .difl-divider-{$dividerType} {$cRightHover}"
            ]
          ]) 
          : [],

          CommonStyle::style(
						[
							'selector' => "{$order_class} .difl-divider-wrapper-separator .difl-divider-wrapper-separator-divider .difl-divider-inner",
							'attr'     => $attrs['divider_width']['innerContent'] ?? '',
							'property' => 'width',
						]
					),

          ...\difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ),
        ],
      ]
    );
  }
  
}
