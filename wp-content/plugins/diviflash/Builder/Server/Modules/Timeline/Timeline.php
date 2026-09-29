<?php
/**
 * Timeline Module class.
 *
 * @package DIVIFLASH5\Modules\Timeline;
 */

namespace DIFL\Server\Modules\Timeline;

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
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;


/**
 * Class Timeline
 *
 * @package DIVIFLASH5\Modules\Timeline
 */
class Timeline implements DependencyInterface {

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use \DIFL\Server\Traits\StyleUtil;

    protected $styles;

    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/timeline' )->customCssFields;
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
        $elements   = $args['elements'];
        $settings   = $args['settings'] ?? [];
        $orderClass = $args['orderClass'];

        $instance->initializeProps( $elements, $attrs );
        $instance->styles = $instance->renderUnifiedStyles( $elements, $attrs, $orderClass, 'difl/timeline' );

        $get_numeric_value = static function ( $value, $default = 0 ) {
            if ( is_numeric( $value ) ) {
                return (float) $value;
            }

            if ( is_string( $value ) && preg_match( '/-?\d+(\.\d+)?/', $value, $match ) ) {
                return (float) $match[0];
            }

            return (float) $default;
        };

        // ---- Arrow Type ----
        $arrowType        = $instance->props['arrow_type'];
        $arrowSelector    = "";
        $arrowSelectorRev = "";

        switch ( $arrowType ) {
            case "arrow_caret":
                $arrowSelector    = "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_caret";
                $arrowSelectorRev = "{$orderClass} .df_timeline_item.reverse .df_timeline_content_area .timeline_arrow_caret";
                break;

            case "arrow_icon":
                $arrowSelector    = "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_icon";
                $arrowSelectorRev = "{$orderClass} .df_timeline_item.reverse .df_timeline_content_area .timeline_arrow_icon";
                break;

            case "arrow_line":
                $arrowSelector    = "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_line";
                $arrowSelectorRev = "{$orderClass} .df_timeline_item.reverse .df_timeline_content_area .timeline_arrow_line";
                break;
        }

        // ---- Date Arrow Type ----
        $dateArrowType        = $instance->props['date_arrow_type'];
        $dateArrowSelector    = "";
        $dateArrowSelectorRev = "";

        switch ( $dateArrowType ) {
            case "arrow_caret":
                $dateArrowSelector    = "{$orderClass} .df_timeline_item .df_timeline_date_content  .timeline_arrow_caret";
                $dateArrowSelectorRev = "{$orderClass} .df_timeline_item.reverse .df_timeline_date_content .timeline_arrow_caret";
                break;

            case "arrow_icon":
                $dateArrowSelector    = "{$orderClass} .df_timeline_item .df_timeline_date_content .timeline_arrow_icon";
                $dateArrowSelectorRev = "{$orderClass} .df_timeline_item.reverse .df_timeline_date_content .timeline_arrow_icon";
                break;

            case "arrow_line":
                $dateArrowSelector    = "{$orderClass} .df_timeline_item .df_timeline_date_content .timeline_arrow_line";
                $dateArrowSelectorRev = "{$orderClass} .df_timeline_item.reverse .df_timeline_date_content .timeline_arrow_line";
                break;
        }

        // ---- Closure: Mobile Date Arrow Position ----
        $mobileDateArrowPosition = function () use ( $attrs, $instance, $get_numeric_value ) {

            $get_mobile_margin_value = function ( $parentKey, $childKey ) use ( $attrs ) {
                if ( ! empty( $attrs[ $parentKey ]['decoration'][ $childKey ]['phone']['value']['margin'] ) ) {
                    return $attrs[ $parentKey ]['decoration'][ $childKey ]['phone']['value']['margin'];
                }
                if ( ! empty( $attrs[ $parentKey ]['decoration'][ $childKey ]['tablet']['value']['margin'] ) ) {
                    return $attrs[ $parentKey ]['decoration'][ $childKey ]['tablet']['value']['margin'];
                }
                if ( ! empty( $attrs[ $parentKey ]['decoration'][ $childKey ]['desktop']['value']['margin'] ) ) {
                    return $attrs[ $parentKey ]['decoration'][ $childKey ]['desktop']['value']['margin'];
                }

                return null;
            };

            $get_mobile_innerContent_value = function ( $parentKey, $childKey ) use ( $attrs ) {
                if ( ! empty( $attrs[ $parentKey ]['innerContent']['phone']['value'][ $childKey ] ) ) {
                    return $attrs[ $parentKey ]['innerContent']['phone']['value'][ $childKey ];
                }
                if ( ! empty( $attrs[ $parentKey ]['innerContent']['tablet']['value'][ $childKey ] ) ) {
                    return $attrs[ $parentKey ]['innerContent']['tablet']['value'][ $childKey ];
                }
                if ( ! empty( $attrs[ $parentKey ]['innerContent']['desktop']['value'][ $childKey ] ) ) {
                    return $attrs[ $parentKey ]['innerContent']['desktop']['value'][ $childKey ];
                }

                return null;
            };

            $date_wrapper_margin = $get_mobile_margin_value( 'date_wrapper_obj_settings', 'spacing' );

            $dateMargin = $date_wrapper_margin ?: [
                "top"            => "0px",
                "bottom"         => "0px",
                "left"           => "80px",
                "right"          => "80px",
                "syncHorizontal" => "off",
                "syncVertical"   => "on",
            ];

            $defaultMargin = ( 'on' === $instance->props['enable_date_in_blurb'] ) ? "0px" : "80px";

            $date_horizontal_gap = $defaultMargin;
            if ( 'left' === $instance->props['layout_type_mobile'] ) {
                $date_horizontal_gap = ! empty( $dateMargin['bottom'] ) ? $dateMargin['bottom'] : $defaultMargin;
            } else {
                $date_horizontal_gap = ! empty( $dateMargin['top'] ) ? $dateMargin['top'] : $defaultMargin;
            }
            if ( $date_horizontal_gap !== '' ) {
                $date_horizontal_gap = $defaultMargin;
            }

            $date_arrow_size     = $get_numeric_value(
                $get_mobile_innerContent_value( 'date_arrow_obj_settings', 'date_arrow_size' ) ?? ( $instance->props['date_arrow_size'] ?? 0 ),
                $instance->props['date_arrow_size'] ?? 0
            );
            $date_arrow_size_rev = ( "on" === $instance->props['reverse_date_arrow'] )
                ? $date_arrow_size
                : $date_arrow_size * 2;

            return [
                'date_horizontal_gap'          => $get_numeric_value( $date_horizontal_gap, $defaultMargin ),
                'date_arrow_size_rev'          => $date_arrow_size_rev,
                'date_arrow_horizontal_mobile' => $get_mobile_innerContent_value( 'date_arrow_obj_settings', 'date_arrow_horizontal_position' ) ?? $instance->props['date_arrow_horizontal_position'],
                'date_arrow_size'              => $date_arrow_size,
            ];
        };

        // ---- Closure: Mobile Arrow Position ----
        $mobileArrowPosition = function () use ( $attrs, $instance, $get_numeric_value ) {

            $get_mobile_margin_value = function ( $parentKey, $childKey ) use ( $attrs ) {
                if ( ! empty( $attrs[ $parentKey ]['decoration'][ $childKey ]['phone']['value']['margin'] ) ) {
                    return $attrs[ $parentKey ]['decoration'][ $childKey ]['phone']['value']['margin'];
                }
                if ( ! empty( $attrs[ $parentKey ]['decoration'][ $childKey ]['tablet']['value']['margin'] ) ) {
                    return $attrs[ $parentKey ]['decoration'][ $childKey ]['tablet']['value']['margin'];
                }
                if ( ! empty( $attrs[ $parentKey ]['decoration'][ $childKey ]['desktop']['value']['margin'] ) ) {
                    return $attrs[ $parentKey ]['decoration'][ $childKey ]['desktop']['value']['margin'];
                }

                return null;
            };

            $get_mobile_innerContent_value = function ( $parentKey, $childKey ) use ( $attrs ) {
                if ( ! empty( $attrs[ $parentKey ]['innerContent']['phone']['value'][ $childKey ] ) ) {
                    return $attrs[ $parentKey ]['innerContent']['phone']['value'][ $childKey ];
                }
                if ( ! empty( $attrs[ $parentKey ]['innerContent']['tablet']['value'][ $childKey ] ) ) {
                    return $attrs[ $parentKey ]['innerContent']['tablet']['value'][ $childKey ];
                }
                if ( ! empty( $attrs[ $parentKey ]['innerContent']['desktop']['value'][ $childKey ] ) ) {
                    return $attrs[ $parentKey ]['innerContent']['desktop']['value'][ $childKey ];
                }

                return null;
            };

            $item_wrapper_margin = $get_mobile_margin_value( 'item_wrapper_obj_settings', 'spacing' );

            $itemMargin = $item_wrapper_margin ?: [
                "top"            => "0px",
                "bottom"         => "0px",
                "left"           => "80px",
                "right"          => "80px",
                "syncHorizontal" => "off",
                "syncVertical"   => "on",
            ];

            $defaultMargin = ( 'on' === $instance->props['enable_date_in_blurb'] ) ? "80px" : "80px";

            $item_horizontal_gap = $defaultMargin;
            if ( 'left' === $instance->props['layout_type_mobile'] ) {
                $item_horizontal_gap = ! empty( $itemMargin['left'] ) ? $itemMargin['left'] : $defaultMargin;
            } else {
                $item_horizontal_gap = ! empty( $itemMargin['right'] ) ? $itemMargin['right'] : $defaultMargin;
            }
            if ( empty( $item_horizontal_gap ) ) {
                $item_horizontal_gap = $defaultMargin;
            }

            $arrow_size     = $get_numeric_value(
                $get_mobile_innerContent_value( 'arrow_obj_settings', 'arrow_size' ) ?? ( $instance->props['arrow_size'] ?? 0 ),
                $instance->props['arrow_size'] ?? 0
            );
            $arrow_size_rev = ( "on" === $instance->props['reverse_arrow'] )
                ? $arrow_size
                : $arrow_size * 2;

            return [
                'item_horizontal_gap'     => $get_numeric_value( $item_horizontal_gap, $defaultMargin ),
                'arrow_size_rev'          => $arrow_size_rev,
                'arrow_horizontal_mobile' => $get_mobile_innerContent_value( 'arrow_obj_settings', 'arrow_horizontal_position' ) ?? $instance->props['arrow_horizontal_position'],
                'arrow_size'              => $arrow_size,
            ];
        };

        $styles = [];


        $styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .df_timeline_item .df_line_inner",
            'attr'                => $attrs['line_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn( $props ) => "width: " . ( $props['attrValue']['line_width'] ?? ( $instance->props['line_width'] ?? '' ) ),
        ] );

        // Fixing font awesome icon
        $styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .df_timeline_top .df_timeline_top_icon",
            'attr'                => $attrs['line_top_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['line_top_icon']?? $instance->props['line_top_icon'] ),
        ] );
        $styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .df_timeline_bottom .df_timeline_bottom_icon",
            'attr'                => $attrs['line_bottom_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['line_bottom_icon']?? $instance->props['line_bottom_icon'] ),
        ] );

        // Layout middle
        if ( ( $instance->props['layout_type'] ?? '' ) === 'middle' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_content_area,{$orderClass} .df_timeline_date_area",
                'attr'                => $attrs['module_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) use ( $instance ) {
                    $width = $props['attrValue']['module_area_width'] ?? $instance->props['module_area_width'];
                    if ( preg_match( '/^(\d+)(px|%)$/', $width, $match ) ) {
                        $module_width = intval( $match[1] ) / 2;
                        $unit         = $match[2];

                        return "flex-basis: {$module_width}{$unit}";
                    }

                    return "";
                },
            ] );

            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_date_area .df_timeline_date_content",
                'attr'                => $attrs['date_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "width: " . ( $props['attrValue']['date_area_width'] ?? $instance->props['date_area_width'] ).";",
            ] );
        }
        else {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_date_area",
                'attr'                => $attrs['date_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "flex-basis: " . ( $props['attrValue']['date_area_width'] ?? $instance->props['date_area_width'] ),
            ] );

            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_date_area .df_timeline_date_content",
                'attr'                => $attrs['date_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "width: 100%;",
            ] );
        }
        $styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .df_timeline_date_area .df_timeline_date_content",
            'attr'                => $attrs['date_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ( $props ) use ( $instance ) {
                $width = $props['attr']['phone']['value']['date_area_width']
                    ?? $props['attr']['tablet']['value']['date_area_width']
                    ?? $props['attr']['desktop']['value']['date_area_width']
                    ?? $instance->props['date_area_width']
                    ?? "90%";

                return "width: $width !important;";
            },
            'atRules'             => '@media only screen and (max-width: 767px)',

        ] );

        // Phone only - enable date in blurb
        if ( ( $instance->props['enable_date_in_blurb'] ?? '' ) === 'on' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_container .df_timeline_date_area",
                'attr'                => $attrs['date_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "justify-content: " . ( $props['attrValue']['date_horizontal_alignment'] ?? $instance->props['date_horizontal_alignment'] ) . " !important",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
        }

        // Phone only - disable date
        if ( ( $instance->props['disable_date'] ?? '' ) === 'on' || ( $instance->props['disable_date_mobile'] ?? '' ) === 'on' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_date_area",
                'attr'                => $attrs['date_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "display:none !important",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
        }

        // Arrow caret
        if ( ( $instance->props['arrow_type'] ?? '' ) === 'arrow_caret' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_caret",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) use ( $instance ) {
                    $arrow_size = $props['attrValue']['arrow_size'] ?? $instance->props['arrow_size'];

                    return "
            border-width:{$arrow_size} !important;
            border-left-width: 0px !important;
        ";
                },
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_caret",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "border-right-color:" . ( $props['attrValue']['arrow_color'] ?? $instance->props['arrow_color'] ) . ";",
            ] );
        }

        // Arrow icon
        if ( ( $instance->props['arrow_type'] ?? '' ) === 'arrow_icon' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_icon",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "font-size:" . ( $props['attrValue']['arrow_size'] ?? $instance->props['arrow_size'] ) . " !important;",
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_icon",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "color:" . ( $props['attrValue']['arrow_color'] ?? $instance->props['arrow_color'] ) . ";",
            ] );
        }

        // Arrow line
        if ( ( $instance->props['arrow_type'] ?? '' ) === 'arrow_line' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_line",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "border-style:" . ( $props['attrValue']['arrow_line_type'] ?? $instance->props['arrow_line_type'] ) . " !important;",
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_line",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "width:" . ( $props['attrValue']['arrow_size'] ?? $instance->props['arrow_size'] ) . " !important;",
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_content_area .timeline_arrow_line",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "border-color:" . ( $props['attrValue']['arrow_color'] ?? $instance->props['arrow_color'] ) . "!important;",
            ] );
        }


        // Base arrow positioning
        $styles[] = CommonStyle::style( [
            'selector'            => $arrowSelector,
            'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn( $props ) => "margin-right:" . ( $props['attrValue']['arrow_horizontal_position'] ?? $instance->props['arrow_horizontal_position'] ) . " !important;",
        ] );

        $styles[] = CommonStyle::style( [
            'selector'            => $arrowSelectorRev,
            'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn( $props ) => "margin-left:" . ( $props['attrValue']['arrow_horizontal_position'] ?? $instance->props['arrow_horizontal_position'] ) . " !important;",
        ] );

        // Arrow Icon + Reverse
        if ( ( $instance->props['arrow_type'] ?? '' ) === 'arrow_icon' && ( $instance->props['reverse_arrow'] ?? '' ) === 'on' ) {

            if ( intval( $instance->props['arrow_horizontal_position'] ?? 0 ) > 0 ) {
                $styles[] = CommonStyle::style( [
                    'selector'            => $arrowSelectorRev,
                    'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn( $props ) => "margin-left:" . ( $props['attrValue']['arrow_horizontal_position'] ?? $instance->props['arrow_horizontal_position'] ) . " !important;",
                ] );
            }

            if ( intval( $instance->props['arrow_horizontal_position'] ?? 0 ) === 0 ) {
                $styles[] = CommonStyle::style( [
                    'selector'            => $arrowSelectorRev,
                    'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => fn( $props ) => "margin-left:" . ( $props['attrValue']['arrow_size'] ?? $instance->props['arrow_size'] ) . " !important;",
                ] );
            }

            $styles[] = CommonStyle::style( [
                'selector'            => $arrowSelector,
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(0deg) !important;",
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => $arrowSelectorRev,
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(180deg) !important;",
            ] );
        }

        // Arrow Caret + Reverse
        if ( ( $instance->props['arrow_type'] ?? '' ) === 'arrow_caret' && ( $instance->props['reverse_arrow'] ?? '' ) === 'on' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => $arrowSelector,
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(180deg) !important;",
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => $arrowSelectorRev,
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(0deg) !important;",
            ] );
        }

        // PHONE ONLY RULES
        // left layout mobile + reverse off
        if ( ( $instance->props['layout_type_mobile'] ?? '' ) === 'left' && ( $instance->props['reverse_arrow'] ?? '' ) === 'off' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_content_area .timeline_arrow_icon",
                'attr'                => $attrs['layout_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(180deg) !important;",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
        }

        // left layout mobile + reverse on
        if ( ( $instance->props['layout_type_mobile'] ?? '' ) === 'left' && ( $instance->props['reverse_arrow'] ?? '' ) === 'on' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_content_area .timeline_arrow_icon",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(0deg) !important;",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_content_area .timeline_arrow_caret",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(180deg) !important;",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
        }

        // right layout mobile + reverse off
        if ( ( $instance->props['layout_type_mobile'] ?? '' ) === 'right' && ( $instance->props['reverse_arrow'] ?? '' ) === 'off' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_content_area .timeline_arrow_icon",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(0deg) !important;",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
        }

        // right layout mobile + reverse on
        if ( ( $instance->props['layout_type_mobile'] ?? '' ) === 'right' && ( $instance->props['reverse_arrow'] ?? '' ) === 'on' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_content_area .timeline_arrow_icon",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(180deg) !important;",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_content_area .timeline_arrow_caret",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "transform: rotate(0deg) !important;",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
        }

        // disable arrow mobile
        if ( ( $instance->props['disable_arrow_mobile'] ?? '' ) === 'on' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_content_area .timeline_arrow",
                'attr'                => $attrs['arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn() => "display: none !important;",
                'atRules'             => '@media only screen and (max-width: 767px)',
            ] );
        }

        // DATE ARROW STYLER
        if ( ( $instance->props['date_arrow_type'] ?? '' ) === 'arrow_caret' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_date_content .timeline_arrow_caret",
                'attr'                => $attrs['date_arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "
            border-width:" . ( $props['attrValue']['date_arrow_size'] ?? $instance->props['date_arrow_size'] ) . " !important;
            border-left-width: 0px !important;",
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_date_content .timeline_arrow_caret",
                'attr'                => $attrs['date_arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "border-right-color:" . ( $props['attrValue']['date_arrow_color'] ?? $instance->props['date_arrow_color'] ) . " !important;",
            ] );
        }
        if ( ( $instance->props['date_arrow_type'] ?? '' ) === 'arrow_icon' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_date_content .timeline_arrow_icon",
                'attr'                => $attrs['date_arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "font-size:" . ( $props['attrValue']['date_arrow_size'] ?? $instance->props['date_arrow_size'] ) . " !important;",
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_date_content .timeline_arrow_icon",
                'attr'                => $attrs['date_arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "color:" . ( $props['attrValue']['date_arrow_color'] ?? $instance->props['date_arrow_color'] ) . " !important;",
            ] );
        }
        if ( ( $instance->props['date_arrow_type'] ?? '' ) === 'arrow_line' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_date_content .timeline_arrow_line",
                'attr'                => $attrs['date_arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "width:" . ( $props['attrValue']['date_arrow_size'] ?? $instance->props['date_arrow_size'] ) . " !important;",
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_timeline_item .df_timeline_date_content .timeline_arrow_line",
                'attr'                => $attrs['date_arrow_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => fn( $props ) => "border-color:" . ( $props['attrValue']['date_arrow_color'] ?? $instance->props['date_arrow_color'] ) . " !important;",
            ] );
        }


        $styles[] = CommonStyle::style( [
            "selector"            => $dateArrowSelector,
            "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
            "declarationFunction" => function ( $props ) use ( $instance ) {
                $date_arrow_horizontal_position = $props['attrValue']['date_arrow_horizontal_position'] ?? $instance->props['date_arrow_horizontal_position'];

                return "margin-left:{$date_arrow_horizontal_position}";
            }
        ] );

        $styles[] = CommonStyle::style( [
            "selector"            => $dateArrowSelector,
            "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
            "declarationFunction" => function ( $props ) use ( $instance ) {
                $date_arrow_horizontal_position = $props['attrValue']['date_arrow_horizontal_position'] ?? $instance->props['date_arrow_horizontal_position'];

                return "margin-right:{$date_arrow_horizontal_position};";
            }
        ] );

        if ( 'on' === $instance->props['reverse_date_arrow'] ) {
            $styles[] = CommonStyle::style( [
                "selector"            => $dateArrowSelectorRev,
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "declarationFunction" => function ( $props ) {
                    return "transform: rotate(180deg) !important;";
                }
            ] );

            $styles[] = CommonStyle::style( [
                "selector"            => $dateArrowSelector,
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "declarationFunction" => function ( $props ) {
                    return "transform: rotate(0deg) !important;";
                }
            ] );
        }


        // ========== PHONE VIEW ==========
        $styles[] = CommonStyle::style( [
            "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_date_content .timeline_arrow_icon",
            "attr"                => $instance->props['layout_obj_settings']['innerContent'],
            "atRules"             => '@media only screen and (max-width: 767px)',
            "declarationFunction" => function ( $props ) use ( $instance ) {
                if ( "left" === $instance->props['layout_type_mobile'] && "off" === $instance->props['reverse_date_arrow'] ) {
                    return "transform: rotate(180deg) !important;";
                }
            }
        ] );

        if ( "left" !== $instance->props['layout_type_mobile'] ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_marker",
                "attr"                => $instance->props['marker_obj_settings']['innerContent'],
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => function ( $props ) {
                    $markerWidth = isset( $props->attrValue['marker_width'] ) ? $props->attrValue['marker_width'] : "50px";

                    return "left: calc(100% - {$markerWidth}) !important;";
                }
            ] );
        }

        if ( "left" === $instance->props['layout_type_mobile'] && "on" === $instance->props['reverse_date_arrow'] ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_date_content .timeline_arrow_icon",
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => fn() => "transform: rotate(0deg) !important;"
            ] );
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_date_content .timeline_arrow_caret",
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => fn() => "transform: rotate(180deg) !important;"
            ] );
        }

        if ( "right" === $instance->props['layout_type_mobile'] && "off" === $instance->props['reverse_date_arrow'] ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_date_content .timeline_arrow_icon",
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => fn() => "transform: rotate(0deg) !important;"
            ] );
        }

        if ( "right" === $instance->props['layout_type_mobile'] && "on" === $instance->props['reverse_date_arrow'] ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_date_content .timeline_arrow_icon",
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => fn() => "transform: rotate(180deg) !important;"
            ] );
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_date_content .timeline_arrow_caret",
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => fn() => "transform: rotate(0deg) !important;"
            ] );
        }

        // disable arrow on phone
        if ( "on" === $instance->props['disable_date_arrow_mobile'] ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_date_content .timeline_arrow",
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => fn() => "display: none !important;"
            ] );
        }

        // hide arrow if "date in blurb"
        if ( "on" === $instance->props['enable_date_in_blurb'] ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_content_area .df_timeline_date_content .timeline_arrow",
                "attr"                => $instance->props['date_arrow_obj_settings']['innerContent'],
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => fn() => "opacity: 0 !important;"
            ] );
        }

        // ========== DATE ARROW ==========
        if ( "on" !== $instance->props['disable_date'] && "on" !== $instance->props['disable_date_mobile'] ) {
            if ( "left" === $instance->props['layout_type_mobile'] ) {
                $styles[] = CommonStyle::style( [
                    "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_date_area .timeline_arrow>*",
                    "attr"                => $instance->props['layout_obj_settings']['innerContent'],
                    "atRules"             => '@media only screen and (max-width: 767px)',
                    "declarationFunction" => function ( $props ) use ( $mobileDateArrowPosition ) {
                        $marginLeft = ( intval( $mobileDateArrowPosition()['date_horizontal_gap'] ) - intval( $mobileDateArrowPosition()['date_arrow_size'] ) ) . "px";

                        return "margin-left:{$marginLeft}!important;left:calc(0% - " . $mobileDateArrowPosition()['date_arrow_horizontal_mobile'] . ") !important;";
                    }
                ] );

                $styles[] = CommonStyle::style( [
                    "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_date_area .timeline_arrow .timeline_arrow_icon",
                    "attr"                => $instance->props['layout_obj_settings']['innerContent'],
                    "atRules"             => '@media only screen and (max-width: 767px)',
                    "declarationFunction" => function ( $props ) use ( $instance, $mobileDateArrowPosition ) {
                        $marginLeftArrowDateIcon = $mobileDateArrowPosition()['date_horizontal_gap'];
                        if ( "on" === $instance->props['reverse_date_arrow'] ) {
                            $marginLeftArrowDateIcon = ( intval( $mobileDateArrowPosition()['date_horizontal_gap'] ) - intval( $mobileDateArrowPosition()['date_arrow_size_rev'] ) ) . "px";
                        }

                        return "margin-left:{$marginLeftArrowDateIcon}!important;left:calc(0% - " . $mobileDateArrowPosition()['date_arrow_horizontal_mobile'] . ") !important;";
                    }
                ] );

                $styles[] = CommonStyle::style( [
                    "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_date_area .timeline_arrow .timeline_arrow_line",
                    "attr"                => $instance->props['layout_obj_settings']['innerContent'],
                    "atRules"             => '@media only screen and (max-width: 767px)',
                    "declarationFunction" => function ( $props ) use ( $mobileDateArrowPosition ) {
                        $marginLeftLine = ( intval( intval( $mobileDateArrowPosition()['date_horizontal_gap'] ) ) - intval( $mobileDateArrowPosition()['date_arrow_size'] ) ) . "px";

                        return "margin-left:{$marginLeftLine}!important;left:calc(0% - " . $mobileDateArrowPosition()['date_arrow_horizontal_mobile'] . ") !important;";
                    }
                ] );
            } else {
                $styles[] = CommonStyle::style( [
                    "selector"            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_date_area .timeline_arrow>*",
                    "attr"                => $instance->props['layout_obj_settings']['innerContent'],
                    "atRules"             => '@media only screen and (max-width: 767px)',
                    "declarationFunction" => function ( $props ) use ( $instance, $mobileDateArrowPosition ) {
                        $marginLeft = intval( $mobileDateArrowPosition()['date_horizontal_gap'] ) . "px";
                        if ( "on" === $instance->props['reverse_date_arrow'] ) {
                            $marginLeft = ( intval( $mobileDateArrowPosition()['date_horizontal_gap'] ) + intval( $mobileDateArrowPosition()['date_arrow_size_rev'] ) ) . "px";
                        }

                        return "margin-left:-{$marginLeft}!important;left:calc(100% - " . $mobileDateArrowPosition()['date_arrow_horizontal_mobile'] . ") !important;";
                    }
                ] );

                $styles[] = CommonStyle::style( [
                    "selector"            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_date_area .timeline_arrow .timeline_arrow_icon",
                    "attr"                => $instance->props['layout_obj_settings']['innerContent'],
                    "atRules"             => '@media only screen and (max-width: 767px)',
                    "declarationFunction" => fn() => "margin-left:-" . $mobileDateArrowPosition()['date_horizontal_gap'] . "!important;left:calc(100% + " . $mobileDateArrowPosition()['date_arrow_horizontal_mobile'] . ") !important;"
                ] );
            }
        }


        //========== Blurb Arrow ==========
        // Left layout
        if ( $instance->props['layout_type_mobile'] === 'left' ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_content_area .timeline_arrow>*",
                "attr"                => $attrs['layout_obj_settings']['innerContent'] ?? null,
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => function ( $props ) use ( $mobileArrowPosition ) {
                    $marginLeft = ( intval( $mobileArrowPosition()['item_horizontal_gap'] ) - intval( $mobileArrowPosition()['arrow_size'] ) ) . "px";

                    return "margin-left:{$marginLeft}!important;left:calc(0% - " . $mobileArrowPosition()['arrow_horizontal_mobile'] . ") !important;";
                }
            ] );

            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_content_area .timeline_arrow .timeline_arrow_icon",
                "attr"                => $attrs['layout_obj_settings']['innerContent'] ?? null,
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => function ( $props ) use ( $instance, $mobileArrowPosition ) {
                    $marginLeftArrowIcon = $mobileArrowPosition()['item_horizontal_gap'];
                    if ( $instance->props['reverse_arrow'] === 'on' ) {
                        $marginLeftArrowIcon = ( intval( $mobileArrowPosition()['item_horizontal_gap'] ) - intval( $mobileArrowPosition()['arrow_size_rev'] ) ) . "px";
                    }

                    return "margin-left:{$marginLeftArrowIcon}!important;left:calc(0% - " . $mobileArrowPosition()['arrow_horizontal_mobile'] . ") !important;";
                }
            ] );

            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_left .df_timeline_content_area .timeline_arrow .timeline_arrow_line",
                "attr"                => $attrs['layout_obj_settings']['innerContent'] ?? null,
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => function ( $props ) use ( $mobileArrowPosition ) {
                    $marginLeftLine = ( intval( $mobileArrowPosition()['item_horizontal_gap'] ) - intval( $mobileArrowPosition()['arrow_size'] ) ) . "px";

                    return "margin-left:{$marginLeftLine}!important;left:calc(0% - " . $mobileArrowPosition()['arrow_horizontal_mobile'] . ") !important;";
                }
            ] );
        }

        // Right layout
        if ( $instance->props['layout_type_mobile'] !== 'left' ) {
            $styles[] = CommonStyle::style( [
                "selector"            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_content_area .timeline_arrow>*",
                "attr"                => $attrs['layout_obj_settings']['innerContent'] ?? null,
                "atRules"             => '@media only screen and (max-width: 767px)',
                "declarationFunction" => function ( $props ) use ( $instance, $mobileArrowPosition ) {
                    $marginLeft = intval( $mobileArrowPosition()['item_horizontal_gap'] ) . "px";
                    if ( $instance->props['reverse_arrow'] === 'on' ) {
                        $marginLeft = ( intval( $mobileArrowPosition()['item_horizontal_gap'] ) + $mobileArrowPosition()['arrow_size_rev'] ) . "px";
                    }

                    return "margin-left:-{$marginLeft}!important;left:calc(100% + " . $mobileArrowPosition()['arrow_horizontal_mobile'] . ") !important;";
                }
            ] );

            if ( $instance->props['reverse_arrow'] === 'on' && $instance->props['arrow_type'] === 'arrow_icon' ) {
                $styles[] = CommonStyle::style( [
                    "selector"            => "{$orderClass} .df_timeline_container.layout_right .df_timeline_content_area .timeline_arrow .timeline_arrow_icon",
                    "attr"                => $attrs['layout_obj_settings']['innerContent'] ?? null,
                    "atRules"             => '@media only screen and (max-width: 767px)',
                    "declarationFunction" => function ( $props ) use ( $instance, $mobileArrowPosition ) {
                        return "margin-left:-" . $mobileArrowPosition()['item_horizontal_gap'] . "!important;left:calc(100% + " . $mobileArrowPosition()['arrow_horizontal_mobile'] . ") !important;";
                    }
                ] );
            }
        }

        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $orderClass, $attrs, $elements->module_metadata->attributes ?? [] ) );

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

                    //$styles[]=CommonStyle::style(
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

    public function render( $content ) {
        // Scripts
        wp_enqueue_script( 'animejs' );
        wp_enqueue_script( 'df_timeline' );

        // Css

        $data_settings = [
            'layout_type'                  => $this->props['layout_type'] ? $this->props['layout_type'] : "middle",
            'layout_type_mobile'           => $this->props['layout_type_mobile'] ? $this->props['layout_type_mobile'] : "left",
            'enable_item_animation'        => "on" === $this->props['enable_item_animation'] ? $this->props['enable_item_animation'] : "off",
            'tmln_animation'               => $this->props['tmln_animation'] ? $this->props['tmln_animation'] : "fade_in",
            'line_width'                   => $this->props['line_width'] ? $this->props['line_width'] : "3px",
            'line_start_from'              => $this->props['line_start_from'] ? $this->props['line_start_from'] : "first_marker",
            'enable_line_top_marker'       => "on" === $this->props['enable_line_top_marker'],
            'line_top_marker_type'         => $this->props['line_top_marker_type'] ? $this->props['line_top_marker_type'] : 'icon',
            'enable_line_bottom_marker'    => "on" === $this->props['enable_line_bottom_marker'],
            'line_bottom_marker_type'      => $this->props['line_bottom_marker_type'] ? $this->props['line_bottom_marker_type'] : 'icon',
            'line_top_marker_space'        => $this->props['line_top_marker_vertical_position'] ? $this->props['line_top_marker_vertical_position'] : "0px",
            'line_bottom_marker_space'     => $this->props['line_bottom_marker_vertical_position'] ? $this->props['line_bottom_marker_vertical_position'] : "0px",
            'item_vertical_gap'            => $this->props['item_vertical_gap'] ? $this->props['item_vertical_gap'] : "50",
            'enable_scroll_line'           => "on" === $this->props['enable_scroll_line'] ? $this->props['enable_scroll_line'] : "off",
            'enable_arrow'                 => "on" === $this->props['enable_arrow'] ? $this->props['enable_arrow'] : "on",
            'disable_arrow_mobile'         => "on" === $this->props['disable_arrow_mobile'] ? $this->props['disable_arrow_mobile'] : "off",
            'item_vertical_alignment'      => $this->props['item_vertical_alignment'] ? $this->props['item_vertical_alignment'] : "center",
            // 'item_horizontal_alignment' => $this->props['item_horizontal_alignment'] ? $this->props['item_horizontal_alignment'] : "center",
            'arrow_marker_vertical_align'  => $this->props['arrow_marker_vertical_align'] ? $this->props['arrow_marker_vertical_align'] : "center",
            'arrow_vertical_align'         => ! empty( $this->props['arrow_vertical_alignment'] ) ? $this->props['arrow_vertical_alignment'] : "middle",
            'arrow_vertical_position'      => ! empty( $this->props['arrow_vertical_position'] ) ? $this->props['arrow_vertical_position'] : "50%",
            'disable_date'                 => "on" === $this->props['disable_date'] ? $this->props['disable_date'] : "off",
            'disable_date_mobile'          => "on" === $this->props['disable_date_mobile'] ? $this->props['disable_date_mobile'] : "off",
            'enable_date_in_blurb'         => "on" === $this->props['enable_date_in_blurb'] ? $this->props['enable_date_in_blurb'] : "off",
            'date_order'                   => "on" === $this->props['order_enable'] ? $this->props['date_order'] : "-1",
            'enable_date_arrow'            => "on" === $this->props['enable_date_arrow'] ? $this->props['enable_arrow'] : "off",
            'disable_date_arrow_mobile'    => "on" === $this->props['disable_date_arrow_mobile'] ? $this->props['disable_date_arrow_mobile'] : "off",
            'date_arrow_vertical_align'    => ! empty( $this->props['date_arrow_vertical_align'] ) ? $this->props['date_arrow_vertical_align'] : "middle",
            'date_arrow_vertical_position' => ! empty( $this->props['date_arrow_vertical_position'] ) ? $this->props['date_arrow_vertical_position'] : "50%",
            'enable_marker_animation'      => "on" === $this->props['enable_marker_animation'] ? $this->props['enable_marker_animation'] : "off",
            'marker_postion_mobile'        => "on" === $this->props['marker_postion_mobile'] ? $this->props['marker_postion_mobile'] : "off",
            'marker_border_color'          => isset( $this->props['border_color_all_marker_wrapper_border'] ) ? $this->props['border_color_all_marker_wrapper_border'] : "",
            'line_top_border'              => isset( $this->props['border_color_all_line_top_border'] ) ? $this->props['border_color_all_line_top_border'] : "",
            'line_bottom_border'           => isset( $this->props['border_color_all_line_bottom_border'] ) ? $this->props['border_color_all_line_bottom_border'] : "",
        ];

        $output = sprintf(
            '<div class="df_timeline_container" data-settings=\'%1$s\'>
                %3$s
                <div class="df_timeline_items">
                    %2$s
                    <div class="df_timeline_line"></div>
                </div>
                %4$s
            </div>',
            wp_json_encode( $data_settings ),
            $content ? $content : "",
            $this->df_tmln_top_bottom_marker( "top" ),
            $this->df_tmln_top_bottom_marker( "bottom" )
        );

        return ! empty( $content ) ? $output : "<h2 class='df_timeline_notice'>Please <strong>Add New Timeline Item.</strong></h2>";
    }

    public function df_tmln_top_bottom_marker( $str = "top" ) {
        $enable_marker    = "on" === $this->props[ "enable_line_" . $str . "_marker" ];
        $marker_type      = $this->props[ "line_" . $str . "_marker_type" ] ? $this->props[ "line_" . $str . "_marker_type" ] : "icon";
        $marker_icon_html = $enable_marker && "icon" === $marker_type ? sprintf(
            '<span class="et-pb-icon %2$s">%1$s</span>',
            ! empty( $this->props[ "line_" . $str . "_icon" ] ) ? esc_attr( Utils::process_font_icon( $this->props[ "line_" . $str . "_icon" ] ) ) : "&#xe00a;",
            "top" === $str ? "df_timeline_top_icon" : "df_timeline_bottom_icon"
        ) : "";
        $marker_img_html  = $enable_marker && "image" === $marker_type && ! empty( $this->props[ "line_" . $str . "_image" ] ) ? $this->df_render_tmln_image( $this->props[ "line_" . $str . "_image" ], $this->props[ "line_" . $str . "_img_alt_txt" ] ) : "";
        $marker_txt       = $enable_marker && "text" === $marker_type && ! empty( $this->props[ "line_" . $str . "_txt" ] ) ? $this->props[ "line_" . $str . "_txt" ] : "Marker Text";

        $marker = "";
        switch ( $marker_type ) {
            case 'icon':
                $marker = $marker_icon_html;
                break;
            case 'image':
                $marker = $marker_img_html;
                break;
            case 'text':
                $marker = $marker_txt;
                break;
        }

        return $enable_marker && 'custom' === $this->props['line_start_from'] ? sprintf(
            '<div class="%2$s"><div class="df_line_marker">%1$s</div></div>',
            $marker,
            "top" === $str ? "df_timeline_top" : "df_timeline_bottom"
        ) : '';
    }

    public function df_render_tmln_image( $img_prop, $img_alt ) {
        if ( isset( $img_prop ) && '' !== $img_prop ) {
            $image_alt = '' !== $img_alt ? $img_alt : df_image_alt_by_url( $img_prop );

            return sprintf(
                '<img src="%1$s" alt="%2$s" />',
                $img_prop,
                $image_alt
            );
        }
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {

        $instance = new static();
        $instance->initializeProps( $elements, $attrs );
        wp_enqueue_script( 'df-timeline' );
        $children_ids = $block->parsed_block['innerBlocks'] ? array_map(
            function ( $inner_block ) {
                return $inner_block['id'];
            },
            $block->parsed_block['innerBlocks']
        ) : [];

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
                'classnamesFunction'  => [ Timeline::class, 'module_classnames' ],
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => [ Timeline::class, 'module_styles' ],
                'scriptDataComponent' => [ Timeline::class, 'module_script_data' ],
                'parentAttrs'         => $parent->attrs ?? [],
                'parentId'            => $parent->id ?? '',
                'parentName'          => $parent->blockName ?? '',
                'children'            => ElementComponents::component(
                        [
                            'attrs'         => $attrs['module']['decoration'] ?? [],
                            'id'            => $block->parsed_block['id'],

                            // FE only.
                            'orderIndex'    => $block->parsed_block['orderIndex'],
                            'storeInstance' => $block->parsed_block['storeInstance'],
                        ]
                    ) . $instance->render( $content ),
                'childrenIds'         => $children_ids,
            ]
        );
    }

    public function load() {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/timeline';

        add_action(
            'init',
            function () use ( $module_json_folder_path ) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ Timeline::class, 'render_callback' ],
                    ]
                );
            }
        );
    }


}
