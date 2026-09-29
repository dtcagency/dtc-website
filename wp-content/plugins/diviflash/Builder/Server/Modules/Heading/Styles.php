<?php
namespace DIFL\Server\Modules\Heading;

if (! defined('ABSPATH')) {
  die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;



trait Styles {

  public static function custom_css() {
    return \WP_Block_Type_Registry::get_instance()->get_registered('difl/df-adh-heading')->customCssFields;
  }

  public static function styles($args) {

    $attrs        = $args['attrs'] ?? [];
    $parent_attrs = $args['parentAttrs'] ?? [];
    $order_class  = $args['orderClass'];
    $elements     = $args['elements'];
    $settings     = $args['settings'] ?? [];
    $icon_selector = "{$order_class} .et-pb-icon";

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
          $elements->style(['attrName' => 'divider_spacing',]),
          $elements->style(['attrName' => 'divider_container_spacing',]),
          $elements->style(['attrName' => 'main_spacing',]),
          $elements->style(['attrName' => 't_dual',]),
          $elements->style(['attrName' => 'title',]),
          $elements->style(['attrName' => 'prefix',]),
          $elements->style(['attrName' => 'infix',]),
          $elements->style(['attrName' => 'suffix',]),
          $elements->style(['attrName' => 'divider_icon',]),

          isset($attrs['divider_style']['innerContent']['desktop']['value']) ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .df-divider-line::before",
							'attr'     => $attrs['divider_style']['innerContent'] ?? '',
							'property' => 'border-top-style',
						]
					) : [],
          isset($attrs['divider_color']['innerContent']['desktop']['value']) ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .df-divider-line::before",
							'attr'     => $attrs['divider_color']['innerContent'] ?? '',
							'property' => 'border-top-color',
						]
					) : [],

          CommonStyle::style(
            [
              'selector' => "{$order_class} .df-heading-divider .df-divider-line",
              'attr'     => $attrs['divider_height']['innerContent'] ?? [],
              'declarationFunction' => [self::class, 'df_divider_height_calc'],
            ]
          ),

          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .df-divider-line::before",
							'attr'     => $attrs['divider_height']['innerContent'] ?? '',
							'property' => 'border-top-width',
						]
          ),

          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .df-divider-line",
							'attr'     => $attrs['divider_height']['innerContent'] ?? '',
							'property' => 'height',
						]
          ),

          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider",
							'attr'     => $attrs['divider_width']['innerContent'] ?? '',
							'property' => 'max-width',
						]
          ),

          ($attrs['divider_alignment']['innerContent']['desktop']['value'] ?? '') ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider",
							'attr'     => $attrs['divider_alignment']['innerContent'] ?? '',
              'declarationFunction' => [self::class, 'df_divider_alignment'],
						]
					) : [],

          (($attrs['use_divider_icon']['innerContent']['desktop']['value'] ?? 'off') !== 'on' && ($attrs['use_divider_image']['innerContent']['desktop']['value'] ?? 'off') !== 'on') ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider::before",
							'attr'     => $attrs['use_divider_icon']['innerContent'] ?? '',
              'declarationFunction' => [self::class, 'df_divider_icon_img_not'],
						]
					) : [],

					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .et-pb-icon",
							'attr'     => $attrs['use_divider_icon_circle']['innerContent'] ?? '',
              'declarationFunction' => [self::class, 'df_circle_icon'],
						]
          ),
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider img",
							'attr'     => $attrs['use_divider_image_circle']['innerContent'] ?? '',
              'declarationFunction' => [self::class, 'df_circle_icon'],
						]
          ),

          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .df-divider-line:before",
							'attr'     => $attrs['divider_border_radius']['innerContent'] ?? '',
							'property' => 'border-radius',
						]
          ),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .df-divider-line",
							'attr'     => $attrs['divider_border_radius']['innerContent'] ?? '',
							'property' => 'border-radius',
						]
          ),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .et-pb-icon, {$order_class}:hover .df-heading-divider .et-pb-icon",
							'attr'     => $attrs['dvr_icon_font_size']['innerContent'] ?? '',
							'property' => 'font-size',
						]
          ),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider .et-pb-icon, {$order_class}:hover .df-heading-divider .et-pb-icon",
							'attr'     => $attrs['divider_icon_bgcolor']['innerContent'] ?? '',
							'property' => 'background-color',
						]
          ),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider img.divider-image",
							'attr'     => $attrs['divider_image_bgcolor']['innerContent'] ?? '',
							'property' => 'background-color',
						]
          ),

          (($attrs['divider_icon_alignment']['innerContent']['desktop']['value'] ?? '') && ($attrs['use_divider_icon']['innerContent']['desktop']['value'] ?? 'off') === 'on') ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider",
							'attr'     => $attrs['divider_icon_alignment']['innerContent'] ?? '',
							'property' => 'text-align',
						]
					) : [],
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider img",
							'attr'     => $attrs['divider_image_width']['innerContent'] ?? '',
							'property' => 'max-width',
						]
          ),

          (($attrs['divider_image_alignment']['innerContent']['desktop']['value'] ?? '') && ($attrs['use_divider_image']['innerContent']['desktop']['value'] ?? 'off') === 'on') ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading-divider",
							'attr'     => $attrs['divider_image_alignment']['innerContent'] ?? '',
							'property' => 'text-align',
						]
					) : [],

          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .prefix",
							'attr'     => $attrs['title_prefix_block']['innerContent'] ?? '',
							'property' => 'display',
						]
          ),
          ($attrs['title_prefix_block']['innerContent']['desktop']['value'] ?? 'inline-block') === 'block' ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .prefix",
							'attr'     => $attrs['prefix_alignment']['innerContent'] ?? '',
              'declarationFunction' => [self::class, 'alignmentPrefixInfixSuffix'],
						]
					) : [],

          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .infix",
							'attr'     => $attrs['title_infix_block']['innerContent'] ?? '',
							'property' => 'display',
						]
          ),
          ($attrs['title_infix_block']['innerContent']['desktop']['value'] ?? 'inline-block') === 'block' ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .infix",
							'attr'     => $attrs['infix_alignment']['innerContent'] ?? '',
              'declarationFunction' => [self::class, 'alignmentPrefixInfixSuffix'],
						]
					) : [],
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .suffix",
							'attr'     => $attrs['title_suffix_block']['innerContent'] ?? '',
							'property' => 'display',
						]
          ),
          ($attrs['title_suffix_block']['innerContent']['desktop']['value'] ?? 'inline-block') === 'block' ? 
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .suffix",
							'attr'     => $attrs['suffix_alignment']['innerContent'] ?? '',
              'declarationFunction' => [self::class, 'alignmentPrefixInfixSuffix'],
						]
					) : [],
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .prefix",
							'attr'     => $attrs['prefix_maxwidth']['innerContent'] ?? '',
							'property' => 'max-width',
						]
          ),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .infix",
							'attr'     => $attrs['infix_maxwidth']['innerContent'] ?? '',
							'property' => 'max-width',
						]
          ),
          CommonStyle::style(
						[
							'selector' => "{$order_class} .df-heading .suffix",
							'attr'     => $attrs['suffix_maxwidth']['innerContent'] ?? '',
							'property' => 'max-width',
						]
          ),

          (($attrs['df_prefix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ? 
          CommonStyle::style(
            [
              'selector' => "{$order_class} .df-heading .prefix",
              'attr'     => $attrs['df_prefix_enable_bg_clip']['innerContent'] ?? '',
              'declarationFunction' => [self::class, 'df_enable_clip_bg'],
            ]
          ) : [],

          (($attrs['df_prefix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ? 
          CommonStyle::style(
            [
              'selector' => "{$order_class} .df-heading .prefix",
              'attr'     => $attrs['df_prefix_fill_color']['innerContent'] ??'',
              'property' => '-webkit-text-fill-color',
            ]
          ) : [],

          (($attrs['df_prefix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ? 
          CommonStyle::style(
            [
              'selector' => "{$order_class} .df-heading .prefix",
                'attr'     => $attrs['df_prefix_stroke_color']['innerContent'] ?? '',
                'property' => '-webkit-text-stroke-color',
            ]
          ) : [],
          (($attrs['df_prefix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ? 
          CommonStyle::style(
            [
              'selector' => "{$order_class} .df-heading .prefix",
              'attr'     => $attrs['df_prefix_stroke_width']['innerContent'] ??'',
              'property' => '-webkit-text-stroke-width',
            ]
          ) : '',

          (($attrs['df_infix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ?
            CommonStyle::style(
              [
                'selector' => "{$order_class} .df-heading .infix",
                'attr'     => $attrs['df_infix_enable_bg_clip']['innerContent'] ?? '',
                'declarationFunction' => [self::class, 'df_enable_clip_bg'],
              ]
            )
            : '',
          (($attrs['df_infix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ?
              CommonStyle::style(
                [
                  'selector' => "{$order_class} .df-heading .infix",
                  'attr'     => $attrs['df_infix_fill_color']['innerContent'] ?? '',
                  'property' => '-webkit-text-fill-color',
                ]
              )
            : '',
          (($attrs['df_infix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ?
              
              CommonStyle::style(
                [
                  'selector' => "{$order_class} .df-heading .infix",
                  'attr'     => $attrs['df_infix_stroke_color']['innerContent'] ?? '',
                  'property' => '-webkit-text-stroke-color',
                ]
              )
            : '',
          (($attrs['df_infix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ?
            CommonStyle::style(
              [
                'selector' => "{$order_class} .df-heading .infix",
                'attr'     => $attrs['df_infix_stroke_width']['innerContent'] ?? '',
                'property' => '-webkit-text-stroke-width',
              ]
            ) : '',

          (($attrs['df_suffix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ?
            CommonStyle::style(
              [
                'selector' => "{$order_class} .df-heading .suffix",
                'attr'     => $attrs['df_suffix_enable_bg_clip']['innerContent'] ?? '',
                'declarationFunction' => [self::class, 'df_enable_clip_bg'],
              ]
            ) : '',

          (($attrs['df_suffix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ?
            CommonStyle::style(
              [
                'selector' => "{$order_class} .df-heading .suffix",
                'attr'     => $attrs['df_suffix_fill_color']['innerContent'] ?? '',
                'property' => '-webkit-text-fill-color',
              ]
            )
              : '',
          (($attrs['df_suffix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ?
            
            CommonStyle::style(
              [
                'selector' => "{$order_class} .df-heading .suffix",
                'attr'     => $attrs['df_suffix_stroke_color']['innerContent'] ?? '',
                'property' => '-webkit-text-stroke-color',
              ]
            )
            : '',
            
          (($attrs['df_suffix_enable_clip']['innerContent']['desktop']['value'] ?? 'off') === 'on') ?
            CommonStyle::style(
              [
                'selector' => "{$order_class} .df-heading .suffix",
                'attr'     => $attrs['df_suffix_stroke_width']['innerContent'] ?? '',
                'property' => '-webkit-text-stroke-width',
              ]
            )
            : '',
          ...\difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ),
        ],
      ]
    );
  }

  public static function df_divider_height_calc(array $args): string {
    $arg   = $args['attrValue'] ?: '5px';
    $value = intval($arg) / 2;
    $unit  = str_replace((string)intval($arg), '', $arg);
    $calcValue = $value . $unit;

    $dividerThickness = $calcValue ? "top:calc(50% - {$calcValue});" :'';

    return $dividerThickness;
  }

  public static function alignmentPrefixInfixSuffix(array $args): string {
    
    if($args['attrValue'] === 'right'){
      $dividerAli = 'margin-left: auto;margin-right: 0;';
    } elseif ($args['attrValue'] === 'center'){
      $dividerAli = 'margin-left: auto;margin-right: auto;';
    } else {
      $dividerAli = 'margin-left: 0;margin-right: auto;';
    }
    return $dividerAli;

  }

  public static function df_divider_alignment(array $args): string {
    
    if($args['attrValue'] === 'right'){
      $dividerAli = 'margin: 0 0 0 auto;';
    } elseif ($args['attrValue'] === 'center'){
      $dividerAli = 'margin: 0 auto;';
    } else {
      $dividerAli = 'margin: 0;';
    }

    return $dividerAli;
  }

  public static function df_divider_icon_img_not(array $args): string {
    return 'position: relative;';
  }
  public static function df_circle_icon(array $args): string {
    $iconRadius = ($args['attrValue'] === 'on') ? 'border-radius: 50%;' : '';
    return $iconRadius;
  }

  public static function df_enable_clip_bg(array $args): string {
    $bgClip = ($args['attrValue'] === 'on') ? '-webkit-background-clip: text;' : '';
    return $bgClip;
  }

}