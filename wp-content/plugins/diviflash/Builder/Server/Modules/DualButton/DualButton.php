<?php
/**
 * DualButton Module class.
 *
 * @package DIVIFLASH5\Modules\DualButton;
 */

namespace DIFL\Server\Modules\DualButton;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;


/**
 * Class DualButton
 *
 * @package DIVIFLASH5\Modules\DualButton
 */
class DualButton implements DependencyInterface {

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use \DIFL\Server\Traits\StyleUtil;

    protected $styles;

    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/dual-button' )->customCssFields;
    }

    public static function font_style($font) {
        return static::get_font_style($font);
    }

    public static function module_classnames( $args ) {
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

    public static function module_script_data( $args ) {
        $elements = $args['elements'];

        // Element Script Data Options.
        $elements->script_data(
            [
                'attrName' => 'module',
            ]
        );
    }

    protected function getDefaultKeys( $args ) {
        $attrs = $args['attrs'];

        $metaData = $args['elements']->module_metadata->attributes;
        foreach ( $metaData as $key => $value ) {
            if ( isset( $attrs[ $key ] ) && ! is_array( $attrs[ $key ] ) ) {
                continue;
            }
            if ( empty( $attrs[ $key ] ) ) {
                $attrs[ $key ] = [
                    'innerContent' => [
                        'desktop' => [
                            'value' => []
                        ]
                    ],
                ];
            }
        }

        return $attrs;
    }

    public static function module_styles( $args ) {
        $instance = new static();
        $attrs    = $instance->getDefaultKeys( $args );;
        $elements    = $args['elements'];
        $settings    = $args['settings'] ?? [];
        $order_class = $args['orderClass'];

        $instance->initializeProps( $elements, $attrs );
        $instance->styles = $instance->renderUnifiedStyles( $elements, $attrs, $order_class, 'difl/dual-button' );
        $fn_alignment     = [
            'left'      => 'flex-start',
            'center'    => 'center',
            'right'     => 'flex-end',
            'justified' => 'space-between',
        ];
        $styles           = [];
        $styles[] = CommonStyle::style( [
            'selector' => $args['orderClass'] . ' .df_button_container',
            'attr'     => $attrs['alignment_obj_settings']['innerContent'],
            'declarationFunction' => function ( $props ) use ( $fn_alignment, $instance ) {
                $button_style = customGetResponsiveValue( $props, 'button_style', $instance->props['button_style']);
                $alignment    = customGetResponsiveValue( $props, 'alignment' );
                $button_style =  $button_style !=''?$button_style:'row';

                if ( $button_style === "column" ) {
                    return sprintf(
                        "align-items: %s;",
                        isset( $fn_alignment[ $alignment ] ) ? $fn_alignment[ $alignment ] : $alignment
                    );
                }
                if ( $button_style === "row" ) {
                    return sprintf(
                        "justify-content: %s;",
                        isset( $fn_alignment[ $alignment ] ) ? $fn_alignment[ $alignment ] : $alignment
                    );
                }
            },
        ] );

        if ( ( $instance->props['use_icon'] ) === 'on' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => $args['orderClass'] . ' .et-pb-icon.df-btn-separator-icon',
                'attr'                => $attrs['separator_obj_settings']['innerContent'] ?? $attrs['separator_obj_settings']['settings']['innerContent'] ?? null,
                'declarationFunction' => function () use ( $instance ) {
                    return self::font_style( $instance->props['font_icon'] ?? '' );
                },
            ] );
        }

        if ( ( $instance->props['use_left_button_icon'] ?? '' ) !== 'off' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => $args['orderClass'] . ' .et-pb-icon.df-left-btn-icon',
                'attr'                => $attrs['left_obj_settings']['innerContent'] ?? $attrs['left_obj_settings']['settings']['innerContent'] ?? null,
                'declarationFunction' => function () use ( $instance ) {
                    return self::font_style( $instance->props['btn_left_font_icon'] ?? '' );
                },
            ] );
        }

        if ( ( $instance->props['use_right_button_icon'] ?? '' ) !== 'off' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => $args['orderClass'] . ' .et-pb-icon.df-right-btn-icon',
                'attr'                => $attrs['right_obj_settings']['innerContent'] ?? $attrs['right_obj_settings']['settings']['innerContent'] ?? null,
                'declarationFunction' => function () use ( $instance ) {
                    return self::font_style( $instance->props['btn_right_font_icon'] ?? '' );
                },
            ] );
        }

        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $args['orderClass'] ?? '', $attrs, $elements->module_metadata->attributes ?? [] ) );

        Style::add(
            [
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => [
                    // Element: Module.
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

                    ...$instance->styles,
                    ...$styles,
//
                    //  CommonStyle::style(
                    //      [
                    //          'selector'            => "{$order_class} .df_text_reveal_main_container",
                    //          'attr'                => $attrs['settings__reveal_color']['decoration'] ?? [],
                    //          'property' => '--secondary-reveal-color'
                    //      ]
                    //  ),
                ],
            ]
        );
    }

    public function render() {
        $font_icon                  = $this->props['font_icon'];
        $use_icon                   = $this->props['use_icon'];
        $use_icon_font_size         = $this->props['use_icon_font_size'];
        $icon_font_size             = $this->props['icon_font_size'];
        $icon_font_size_tablet      = $this->props['icon_font_size_tablet'];
        $icon_font_size_phone       = $this->props['icon_font_size_phone'];
        $icon_font_size_last_edited = $this->props['icon_font_size_last_edited'];
        $btn_left_font_icon         = $this->props['btn_left_font_icon'];
        $btn_right_font_icon        = $this->props['btn_right_font_icon'];
        $left_btn_icon_pos          = $this->props['btn_left_icon_placement'];
        $right_btn_icon_pos         = $this->props['btn_right_icon_placement'];
        // left icon
        $left_button_icon  = $this->props['use_left_button_icon'] !== 'off' ? sprintf( '<span class="et-pb-icon df-left-btn-icon">%1$s</span>',
            $btn_left_font_icon !== '' ? esc_html( Utils::process_font_icon( $btn_left_font_icon ) ?? '5' ) : '5'
        ) : '';
        $right_button_icon = $this->props['use_right_button_icon'] !== 'off' ? sprintf( '<span class="et-pb-icon df-right-btn-icon">%1$s</span>',
            $btn_right_font_icon !== '' ? esc_html( Utils::process_font_icon( $btn_right_font_icon ) ?? '5' ) : '5'
        ) : '';

        $left_button  = ! empty( $this->props['left_button'] ) ?
            sprintf( '<div class="df_button_left_wrapper">
                <a href="%2$s" class="df_button_left%5$s" %6$s>%4$s%1$s%3$s</a>
            </div>', $this->props['left_button'],
                $this->props['left_button_url'],
                $left_btn_icon_pos !== 'left' ? $left_button_icon : '',
                $left_btn_icon_pos === 'left' ? $left_button_icon : '',
                $left_btn_icon_pos === 'left' ? ' icon-left' : '',
                $this->props['left_button_target'] === 'on' ? 'target="_blank"' : ''
            ) : null;
        $right_button = ! empty( $this->props['right_button'] ) ?
            sprintf( '<div class="df_button_right_wrapper">
                <a href="%2$s" class="df_button_right%5$s" %6$s>%4$s%1$s%3$s</a>
            </div>', $this->props['right_button'],
                $this->props['right_button_url'],
                $right_btn_icon_pos !== 'left' ? $right_button_icon : '',
                $right_btn_icon_pos === 'left' ? $right_button_icon : '',
                $right_btn_icon_pos === 'left' ? ' icon-left' : '',
                $this->props['right_button_target'] === 'on' ? 'target="_blank"' : ''
            ) : null;

        $separator_content = '';
        if ( 'on' === $use_icon ) {

            $separator_content = ( '' !== $font_icon && $this->props['use_icon'] === 'on' ) ? sprintf(
                '<span class="et-pb-icon df-btn-separator-icon">%1$s</span>',
                esc_html( Utils::process_font_icon( $font_icon ) ?? '' )
            ) : '';
        } else {
            $separator_content .= $this->props['separator_text'];
        }
        $separator = $this->props['button_separator'] === 'on' ?
            sprintf( '<div class="button-separator">%1$s</div>',
                $separator_content ) : null;


        return sprintf( '<div class="df_button_container">%1$s %3$s %2$s</div>',
            $left_button, $right_button, $separator );
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {

        $instance = new static();
        $instance->initializeProps( $elements, $attrs );
        wp_enqueue_script( 'df-dual-button' );

        return Module::render(
            [
                // FE only.
                'orderIndex'          => $block->parsed_block['orderIndex'],
                'storeInstance'       => $block->parsed_block['storeInstance'],

                // VB equivalent.
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'],
                'moduleClassName'     => '',
                'name'                => $block->block_type->name,
                'classnamesFunction'  => [ DualButton::class, 'module_classnames' ],
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => [ DualButton::class, 'module_styles' ],
                'scriptDataComponent' => [ DualButton::class, 'module_script_data' ],
                'parentAttrs'         => $parent->attrs ?? [],
                'parentId'            => $parent->id ?? '',
                'parentName'          => $parent->blockName ?? '',
                'children'            => $elements->style_components(
                        [
                            'attrName' => 'module',
                        ]
                    ) . $instance->render(),
            ]
        );
    }

    public function load() {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/dual-button';

        add_action(
            'init',
            function () use ( $module_json_folder_path ) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ DualButton::class, 'render_callback' ],
                    ]
                );
            }
        );
    }

    function df_render_pattern_or_mask_html( $props, $type ) {
        $html = array(
            'pattern' => '<span class="et_pb_background_pattern"></span>',
            'mask'    => '<span class="et_pb_background_mask"></span>'
        );

        return $props == 'on' ? $html[ $type ] : '';
    }


    function df_block_align( $align ) {
        if ( $align === 'center' ) {
            return 'margin-left: auto; margin-right: auto;';
        } else if ( $align === 'right' ) {
            return 'margin-left: auto; margin-right: 0;';
        } else if ( $align === 'left' ) {
            return 'margin-right: auto; margin-left: 0;';
        }
    }


    function df_get_div_value( $arg ) {
        $value = intval( $arg ) / 2;
        $unit  = str_replace( intval( $arg ), "", $arg );

        return $value . $unit;
    }

}
