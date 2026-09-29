<?php
/**
 * ImageReveal Module class.
 *
 * @package DIVIFLASH5\Modules\ImageReveal;
 */

namespace DIFL\Server\Modules\ImageReveal;

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
 * Class ImageReveal
 *
 * @package DIVIFLASH5\Modules\ImageReveal
 */
class ImageReveal implements DependencyInterface {

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;

    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/imagereveal' )->customCssFields;
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
        $instance->styles = $instance->renderUnifiedStyles( $elements, $attrs, $orderClass, 'difl/imagereveal' );

        $styles = [];

        $isHexColor = function ( $hex ) {
            $reg_exp = '/^#[0-9A-F]{6}$/i';

            return preg_match( $reg_exp, $hex );
        };

        $hex2rgba = function ( $hex, $alpha = 1 ) use ( $isHexColor ) {
            if ( ! $isHexColor( $hex ) ) {  // Call as variable function with $
                return $hex;
            }

            preg_match_all( '/\w\w/', $hex, $matches );
            $rgb = array_map( function ( $x ) {
                return hexdec( $x );
            }, $matches[0] );

            list( $r, $g, $b ) = $rgb;

            return "rgba({$r},{$g},{$b},{$alpha})";
        };


        //SUPPORTIVE VARIABLES---->


// Overlay Container Class
        $__class__overlay_container     = $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_overlay';
        $__class__hover_overlay_content = $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_hover_overlay .difl__image_reveal_hover_overlay_content';

// Overlay field declaration
        $__field__overlay_enable    = $instance->props['field_overlay_enable'] ?? 'off';
        $__field__overlay_color     = $instance->props['field_overlay_color'] ?? '#ffffff';
        $__field__overlay_opacity   = $instance->props['field_overlay_opacity'] ?? 0.15;
        $__field__reveal_directions = $instance->props['field_reveal_directions'] ?? 'reveal_ltr';

// Hover Overlay Field Declaration
        $__field__hover_overlay_enable  = $instance->props['field_hover_overlay_enable'] ?? 'off';
        $__field__hover_overlay_color   = $instance->props['field_hover_overlay_color'] ?? '#ffffff';
        $__field__hover_overlay_opacity = $instance->props['field_hover_overlay_opacity'] ?? '0.3';

// Caption Field Declaration
        $__field__caption_enable = $instance->props['field_caption_enable'] ?? 'off';

// Reveal Effect Field Declaration
        $__field__reveal_effects      = $instance->props['field_reveal_effects'] ?? 'none';
        $__field__reveal_effect_delay = $instance->props['field_reveal_effect_delay'] ?? '0';
        $__field__reveal_effect_time  = $instance->props['field_reveal_effect_animation_time'] ?? '1';

// Hover Zoom Effect Field Declaration
        $__field__hover_image_effect_enable = $instance->props['field_hover_image_effect_enable'] ?? 'off';
        $__field__hover_image_effect_style  = $instance->props['field_effect_style'] ?? 'none';
        $__field__zoom_scale                = $instance->props['field_zoom_scale'] ?? '1.5';
        $__field__zooming_time              = $instance->props['field_zooming_time'] ?? '0.25';
        $__field__Speed_curve               = $instance->props['field_Speed_curve'] ?? 'ease-in';
        $__field__zoom_rotate               = $instance->props['field_zoom_rotate'] ?? '25';
        $__field__zooming_blur_out_time     = $instance->props['field_zooming_blur_out_time'] ?? '0.25';
        $__field__zooming_blur_level        = $instance->props['field_zooming_blur_level'] ?? '2';
        $__field__zooming_grayscale         = $instance->props['field_grayscale'] ?? '100';

// $__field__reveal_color_bg_use_gradient = $instance->props['background_gradient_enabled'] ?? 'off';
        $__field__force_full_width = $instance->props['force_fullwidth'] ?? 'off';

        $__field__reveal_animation_time = $instance->props['field_reveal_animation_time'] ?? '1';
        $__field__reveal_delay          = $instance->props['field_reveal_delay'] ?? '0';

        $reveal_in_anim   = floatval( $__field__reveal_animation_time ) / 2.0;
        $reveal_out_anim  = floatval( $__field__reveal_animation_time ) / 2.0;
        $reveal_out_delay = floatval( $__field__reveal_delay ) + floatval( $reveal_in_anim );
        $img_anim_time    = "reveal_ltr" === $__field__reveal_directions ? $__field__reveal_animation_time : '0';

        $__class__reveal_direction = [
            'reveal_ltr' => [
                'class'         => 'difl__image_reveal_lr',
                'animation_in'  => 'imageRevealLR',
                'animation_out' => 'imageRevealOutLR',
            ],
            'reveal_rtl' => [
                'class'         => 'difl__image_reveal_rl',
                'animation_in'  => 'imageRevealRL',
                'animation_out' => 'imageRevealOutRL',
            ],
            'reveal_ttb' => [
                'class'         => 'difl__image_reveal_tb',
                'animation_in'  => 'imageRevealTB',
                'animation_out' => 'imageRevealOutTB',
            ],
            'reveal_btt' => [
                'class'         => 'difl__image_reveal_bt',
                'animation_in'  => 'imageRevealBT',
                'animation_out' => 'imageRevealOutBT',
            ],
        ];

        $effect_total_delay = floatval( $__field__reveal_effect_delay ) + floatval( $__field__reveal_delay );

        $ho_anim_time                       = $instance->props['field_hover_overlay_transition_time'] ?? '0.6s';
        $ho_anim_time                       = substr( $ho_anim_time, - 1 ) !== 's' ? $ho_anim_time . 's' : $ho_anim_time;
        $ho_anim_delay                      = $instance->props['field_hover_overlay_transition_delay'] ?? '0s';
        $ho_anim_delay                      = substr( $ho_anim_delay, - 1 ) !== 's' ? $ho_anim_delay . 's' : $ho_anim_delay;
        $__field__hover_overlay_arrive_from = $instance->props['field_hover_overlay_arrive_from'] ?? 'left';

        $__class__hover_overlay_reveal_direction = [
            'left'        => [
                'class'          => 'difl__hover_overlay_lr',
                'animation_in'   => 'imageRevealLR',
                'animation_out'  => 'linear',
                'animation_mode' => 'forwards',
            ],
            'right'       => [
                'class'          => 'difl__hover_overlay_rl',
                'animation_in'   => 'imageRevealRL',
                'animation_out'  => 'linear',
                'animation_mode' => 'forwards',
            ],
            'top'         => [
                'class'          => 'difl__hover_overlay_tb',
                'animation_in'   => 'imageRevealTB',
                'animation_out'  => 'linear',
                'animation_mode' => 'forwards',
            ],
            'bottom'      => [
                'class'          => 'difl__hover_overlay_bt',
                'animation_in'   => 'imageRevealBT',
                'animation_out'  => 'linear',
                'animation_mode' => 'forwards',
            ],
            'linear'      => [
                'class'          => 'difl__hover_overlay_linear',
                'animation_in'   => 'overlayViewer',
                'animation_out'  => 'linear',
                'animation_mode' => 'both',
            ],
            'ease_in_out' => [
                'class'          => 'difl__hover_overlay_ease_in_out',
                'animation_in'   => 'overlayViewer',
                'animation_out'  => 'ease-in-out',
                'animation_mode' => 'both',
            ],
            'ease'        => [
                'class'          => 'difl__hover_overlay_ease',
                'animation_in'   => 'overlayViewer',
                'animation_out'  => 'ease',
                'animation_mode' => 'both',
            ],
            'ease_in'     => [
                'class'          => 'difl__hover_overlay_ease_in',
                'animation_in'   => 'overlayViewer',
                'animation_out'  => 'ease-in',
                'animation_mode' => 'both',
            ],
            'ease_out'    => [
                'class'          => 'difl__hover_overlay_ease_out',
                'animation_in'   => 'overlayViewer',
                'animation_out'  => 'ease-out',
                'animation_mode' => 'both',
            ],
        ];

        $__field__hover_overlay_content_placement   = $instance->props['field_hover_overlay_content_placement'] ?? 'center';
        $__field__hover_overlay_content_alignment   = $instance->props['field_hover_overlay_content_alignment'] ?? 'center';
        $__field__hover_overlay_content_arrive_from = $instance->props['field_hover_overlay_content_arrive_from'] ?? 'left';
        $__class__hover_overlay_content_arrive_from = [
            'left'   => [
                'transform' => "translateX(-2rem)"
            ],
            'right'  => [
                'transform' => "translateX(2rem)"
            ],
            'top'    => [
                'transform' => "translateY(-2rem)"
            ],
            'bottom' => [
                'transform' => "translateY(2rem)"
            ]
        ];


        //SUPPORTIVE VARIABLES----*


// Overlay enable condition
        if ( "on" === $__field__overlay_enable ) {
            $styles[] = CommonStyle::style( [
                'selector'            => $__class__overlay_container,
                'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) use ( $hex2rgba, $__field__overlay_color, $__field__overlay_opacity ) {
                    $bgColor = $props['attrValue']['field_overlay_color']??$__field__overlay_color;
                    return 'background:' . $hex2rgba( $bgColor, $__field__overlay_opacity ) . ';';
                },
            ] );
        }

        $styles[] = CommonStyle::style( [
            'selector'            => $orderClass . ' .' . $__class__reveal_direction[ $__field__reveal_directions ]['class'] . ' img',
            'attr'                => $attrs['reveal_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ( $props ) use ( $img_anim_time, $reveal_out_delay ) {
                return "animation:fadeInImg {$img_anim_time}s {$reveal_out_delay}s forwards;
                -webkit-animation:fadeInImg {$img_anim_time}s {$reveal_out_delay}s forwards;";
            },
        ] );

        $styles[] = CommonStyle::style( [
            'selector'            => $orderClass . ' .' . $__class__reveal_direction[ $__field__reveal_directions ]['class'] . ' .difl__image_reveal_overlay',
            'attr'                => $attrs['reveal_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ( $props ) use ( $__field__reveal_animation_time, $reveal_out_delay ) {
                return "animation:fadeInImg {$__field__reveal_animation_time}s {$reveal_out_delay}s linear forwards;
                -webkit-animation:fadeInImg {$__field__reveal_animation_time}s {$reveal_out_delay}s linear forwards;";
            },
        ] );

        $styles[] = CommonStyle::style( [
            'selector'            => $orderClass . ' .' . $__class__reveal_direction[ $__field__reveal_directions ]['class'] . ' .difl__image_reveal',
            'attr'                => $attrs['reveal_obj_settings']['innerContent'] ?? null,
            'selectorFunction'    => function ( $props ) use ( $orderClass, $__class__reveal_direction, $__field__reveal_directions ) {
                if ( "hover" === $props['state'] ) {
                    return $orderClass . ':hover .' . $__class__reveal_direction[ $__field__reveal_directions ]['class'] . ' .difl__image_reveal';
                }

                return $props['selector'];
            },
            'declarationFunction' => function ( $props ) use ( $__class__reveal_direction, $__field__reveal_directions, $reveal_in_anim, $__field__reveal_delay, $reveal_out_anim, $reveal_out_delay ) {
                return "animation:{$__class__reveal_direction[$__field__reveal_directions]['animation_in']} {$reveal_in_anim}s {$__field__reveal_delay}s, {$__class__reveal_direction[$__field__reveal_directions]['animation_out']} {$reveal_out_anim}s {$reveal_out_delay}s;
                -webkit-animation:{$__class__reveal_direction[$__field__reveal_directions]['animation_in']} {$reveal_in_anim}s {$__field__reveal_delay}s, {$__class__reveal_direction[$__field__reveal_directions]['animation_out']} {$reveal_out_anim}s {$reveal_out_delay}s;
                animation-fill-mode:forwards;
                -webkit-animation-fill-mode:forwards;";
            },
        ] );

// Reveal Effect Style handler
        if ( "none" !== $__field__reveal_effects ) {
            $styles[] = CommonStyle::style( [
                'selector'            => $orderClass . ' .difl__image_reveal_wrapper .difl__animate',
                'attr'                => $attrs['reveal_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) use ( $__field__reveal_effect_time, $effect_total_delay ) {
                    return "animation-duration:{$__field__reveal_effect_time}s;
                    -webkit-animation-duration:{$__field__reveal_effect_time}s;
                    animation-delay:{$effect_total_delay}s;
                    -webkit-animation-delay:{$effect_total_delay}s;";
                },
            ] );
        }

// Hover Overlay Color, Opacity, Transition Delay, Transition Time
        if ( "on" === $__field__hover_overlay_enable ) {
            $styles[] = CommonStyle::style( [
                'selector'            => $__class__hover_overlay_content,
                'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) use ( $hex2rgba, $__field__hover_overlay_color, $__field__hover_overlay_opacity ) {
                    $bgColor = $props['attrValue']['field_hover_overlay_color'] ?? $__field__hover_overlay_color;

                    return 'background-color:' . $hex2rgba( $bgColor, $__field__hover_overlay_opacity ) . ';';
                },
            ] );
        }

// Hover Overlay Animation Control
        $styles[] = CommonStyle::style( [
            'selector'            => $orderClass . ' .difl__image_reveal_wrapper .' . $__class__hover_overlay_reveal_direction[ $__field__hover_overlay_arrive_from ]['class'] . ':hover .difl__image_reveal_hover_overlay_content',
            'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
            'selectorFunction'    => function ( $props ) use ( $orderClass, $__class__hover_overlay_reveal_direction, $__field__hover_overlay_arrive_from ) {
                if ( "hover" === $props['state'] ) {
                    return $orderClass . ' .difl__image_reveal_wrapper .' . $__class__hover_overlay_reveal_direction[ $__field__hover_overlay_arrive_from ]['class'] . ':hover .difl__image_reveal_hover_overlay_content';
                }

                return $props['selector'];
            },
            'declarationFunction' => function ( $props ) use ( $__class__hover_overlay_reveal_direction, $__field__hover_overlay_arrive_from, $ho_anim_time, $ho_anim_delay ) {
                return "-webkit-animation:{$__class__hover_overlay_reveal_direction[$__field__hover_overlay_arrive_from]['animation_in']} {$ho_anim_time} {$__class__hover_overlay_reveal_direction[$__field__hover_overlay_arrive_from]['animation_out']} {$ho_anim_delay};
                animation:{$__class__hover_overlay_reveal_direction[$__field__hover_overlay_arrive_from]['animation_in']} {$ho_anim_time} {$__class__hover_overlay_reveal_direction[$__field__hover_overlay_arrive_from]['animation_out']} {$ho_anim_delay};
                -webkit-animation-fill-mode:{$__class__hover_overlay_reveal_direction[$__field__hover_overlay_arrive_from]['animation_mode']};
                animation-fill-mode:{$__class__hover_overlay_reveal_direction[$__field__hover_overlay_arrive_from]['animation_mode']};";
            },
        ] );

        $styles[] = CommonStyle::style( [
            'selector'            => $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_hover_overlay_content',
            'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ( $props ) {
                return 'animation-duration:0.5s;';
            },
        ] );

// Hover Overlay Content Transition
        $styles[] = CommonStyle::style( [
            'selector'            => $orderClass . ' .difl__image_reveal_hover_overlay_content .arrival',
            'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => function ( $props ) use ( $ho_anim_delay, $__class__hover_overlay_content_arrive_from, $__field__hover_overlay_content_arrive_from ) {
                return "transition:all 1s ease-in-out {$ho_anim_delay};
                transform:{$__class__hover_overlay_content_arrive_from[$__field__hover_overlay_content_arrive_from]['transform']};";
            },
        ] );

// Hover Zoom Effect
        if ( 'on' === $__field__hover_image_effect_enable && 'off' === $__field__hover_overlay_enable ) {

            if ( "zoom_in" === $__field__hover_image_effect_style ) {
                $styles[] = CommonStyle::style( [
                    'selector'            => $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_content:hover img',
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) use ( $__field__zooming_time, $__field__Speed_curve, $__field__zoom_scale ) {
                        return "transform-origin:center center;
                        transition:transform {$__field__zooming_time}s, visibility " . ( floatval( $__field__zooming_time ) / 2 ) . "s {$__field__Speed_curve};
                        -ms-transform:scale({$__field__zoom_scale});
                        -webkit-transform:scale({$__field__zoom_scale});
                        transform:scale({$__field__zoom_scale});";
                    },
                ] );
            }

            if ( "zoom_n_rotate" === $__field__hover_image_effect_style ) {
                $styles[] = CommonStyle::style( [
                    'selector'            => $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_content:hover img',
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) use ( $__field__zooming_time, $__field__Speed_curve, $__field__zoom_scale, $__field__zoom_rotate ) {
                        return "transform-origin:center center;
                        transition:transform {$__field__zooming_time}s, visibility " . ( floatval( $__field__zooming_time ) / 2 ) . "s {$__field__Speed_curve};
                        -ms-transform:scale({$__field__zoom_scale}) rotate({$__field__zoom_rotate}deg);
                        -webkit-transform:scale({$__field__zoom_scale}) rotate({$__field__zoom_rotate}deg);
                        transform:scale({$__field__zoom_scale}) rotate({$__field__zoom_rotate}deg);";
                    },
                ] );
            }

            if ( "blur_out_with_zooming_in" === $__field__hover_image_effect_style ) {
                $styles[] = CommonStyle::style( [
                    'selector'            => $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_content img',
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) use ( $__field__zoom_scale, $__field__zooming_time, $__field__zooming_blur_out_time, $__field__Speed_curve, $__field__zooming_blur_level ) {
                        return "transform:scale({$__field__zoom_scale});
                        transition:transform {$__field__zooming_time}s, filter {$__field__zooming_blur_out_time}s {$__field__Speed_curve};
                        filter:blur({$__field__zooming_blur_level}px);";
                    },
                ] );

                $styles[] = CommonStyle::style( [
                    'selector'            => $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_content:hover img',
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) {
                        return "transform:scale(1);
                        filter:blur(0);";
                    },
                ] );
            }

            if ( "colorize_with_zooming_in" === $__field__hover_image_effect_style ) {
                $styles[] = CommonStyle::style( [
                    'selector'            => $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_content img',
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) use ( $__field__zooming_time, $__field__Speed_curve, $__field__zooming_grayscale ) {
                        return "transition:transform {$__field__zooming_time}s, filter {$__field__zooming_time}s {$__field__Speed_curve};
                        filter:grayscale({$__field__zooming_grayscale}%);";
                    },
                ] );

                $styles[] = CommonStyle::style( [
                    'selector'            => $orderClass . ' .difl__image_reveal_wrapper .difl__image_reveal_content:hover img',
                    'attr'                => $attrs['overlay_obj_settings']['innerContent'] ?? null,
                    'declarationFunction' => function ( $props ) use ( $__field__zoom_scale ) {
                        return "transform:scale({$__field__zoom_scale});
                        filter:grayscale(0);";
                    },
                ] );
            }
        }

// Force full width
        if ( "on" === $__field__force_full_width ) {
            $styles[] = CommonStyle::style( [
                'selector'            => $orderClass . ' , ' . $orderClass . ' .difl__image_wrap,' . $orderClass . ' .difl__image_wrap img',
                'attr'                => $attrs['fullwidth_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) {
                    return 'width: 100% !important; max-width: 100% !important;';
                },
            ] );
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
        wp_enqueue_script( 'lightgallery-script' );
        wp_enqueue_script( 'df-image-reveal' );


        $__field__image                         = $this->props['src'];
        $__field__alt                           = $this->props['alt'];
        $__field__title_text                    = $this->props['titleText'];
        $__field__reveal_directions             = $this->props['field_reveal_directions'];
        $__field__caption_enable                = $this->props['field_caption_enable'];
        $__field__caption_placement             = $this->props['field_caption_placement'];
        $__field__hover_overlay_transition_time = $this->props['field_hover_overlay_transition_time'];
        // Reveal Animation Time, Delay Field Declaration
        $__field__reveal_animation_delay = $this->props['field_reveal_delay'];
        $__field__reveal_animation_time  = $this->props['field_reveal_animation_time'];

        $srcset_sizes = et_get_image_srcset_sizes( $__field__image );
        $srcset       = "";
        $sizes        = "";

        if (
            isset( $srcset_sizes["srcset"], $srcset_sizes["sizes"] ) &&
            $srcset_sizes["srcset"] &&
            $srcset_sizes["sizes"]
        ) {
            $srcset = $srcset_sizes["srcset"];
            $sizes  = $srcset_sizes["sizes"];
        }

        $attachment_id = et_get_attachment_id_by_url( $__field__image );
        $image_caption = wp_get_attachment_caption( $attachment_id );
        $image_size    = wp_get_attachment_image_src( $attachment_id, 'full' );
        $image_width   = isset( $image_size[1] ) ? $image_size[1] : "";
        $image_height  = isset( $image_size[2] ) ? $image_size[2] : "";

        $empty_image = "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%200%200'%3E%3C/svg%3E";

        $delay            = isset( $__field__reveal_animation_delay ) && ! empty( $__field__reveal_animation_delay ) ? $__field__reveal_animation_delay : 0;
        $anim_time        = isset( $__field__reveal_animation_time ) && ! empty( $__field__reveal_animation_time ) ? $__field__reveal_animation_time : 1;
        $reveal_in_anim   = (float) $anim_time / 2.0;
        $reveal_out_delay = (float) $delay + $reveal_in_anim;
        $ho_anim_time     = isset( $__field__hover_overlay_transition_time ) && ! empty( $__field__hover_overlay_transition_time ) ? $__field__hover_overlay_transition_time : '0.6s';
        $ho_anim_time     = substr( $ho_anim_time, - 1 ) !== 's' ? $ho_anim_time . "s" : $ho_anim_time;
        $data_settings    = [
            'revealDirectionClass' => isset( $__field__reveal_directions ) ? $this->getRevealDirection( $__field__reveal_directions ) : "difl__image_reveal_lr",
            'revealPosition'       => isset( $this->props['field_reveal_view_port'] ) && ! empty( $this->props['field_reveal_view_port'] ) ? str_replace( "%", "", $this->props['field_reveal_view_port'] ) : "25",
            'revealEffect'         => isset( $this->props['field_reveal_effects'] ) && ! empty( $this->props['field_reveal_effects'] ) && 'none' !== $this->props['field_reveal_effects'] ? $this->props['field_reveal_effects'] : "",
            'revealDelay'          => $reveal_out_delay,
            'use_light_box'        => isset( $this->props['field_lightbox_enable'] ) ? $this->props['field_lightbox_enable'] : 'off',
            'link_url'             => $this->props['field_lightbox_enable'] === 'off' && isset( $this->props['field_link_url'] ) && $this->props['field_link_url'] !== '' ? $this->props['field_link_url'] : '',
            'link_url_target'      => isset( $this->props['field_link_target'] ) ? $this->props['field_link_target'] : 'same_window',
            'animationTime'        => $ho_anim_time,
        ];
        $link_url         = isset( $this->props['field_link_url'] ) && $this->props['field_link_url'] !== '' ? $this->props['field_link_url'] : "";
        $custom_url       = $this->props['field_lightbox_enable'] === 'off' && $link_url !== '' ?
            sprintf( 'data-url="%1$s"', $link_url )
            : '';

        return sprintf(
            '<div class="difl__image_reveal_wrapper" data-settings=\'%10$s\'>
                    <span class="difl__image_wrap link_lightbox" data-src="%14$s">
                        <div class="difl__image_reveal_content" %15$s>
                            <div class="difl__box_shadow_overlay"></div>
                            %12$s
                            <img
                                decoding="async"
                                fetchpriority="high"
                                src="%1$s"
                                alt="%3$s"
                                title="%4$s"
                                sizes="%6$s"
                                width="%7$s"
                                height="%8$s"
                            />
                            %13$s
                            <noscript><img
                                decoding="async"
                                src="%2$s"
                                srcSet="%5$s"
                            />
                            </noscript>
                        </div>
                        %9$s
                        %11$s
                        <div class="difl__image_reveal_element"></div>
                    </span>
                </div>',
            $empty_image,
            $__field__image,
            $__field__alt,
            $__field__title_text,
            $srcset,
            $sizes,
            $image_width,
            $image_height,
            ( isset( $this->props['field_overlay_enable'] ) && 'on' === $this->props['field_overlay_enable'] ) ? '<div class="difl__image_reveal_overlay"></div>' : "",
            wp_json_encode( $data_settings ),
            $this->generateHoverOverlay(),
            'on' === $__field__caption_enable && 'top' === $__field__caption_placement ? $this->generateCaption( $image_caption ) : '',
            'on' === $__field__caption_enable && 'bottom' === $__field__caption_placement ? $this->generateCaption( $image_caption ) : '',
            $__field__image,
            $custom_url
        );
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {

        $instance = new static();
        $instance->initializeProps( $elements, $attrs );
        wp_enqueue_script( 'df-image-reveal' );

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
                'classnamesFunction'  => [ ImageReveal::class, 'module_classnames' ],
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => [ ImageReveal::class, 'module_styles' ],
                'scriptDataComponent' => [ ImageReveal::class, 'module_script_data' ],
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
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/image-reveal';

        add_action(
            'init',
            function () use ( $module_json_folder_path ) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ ImageReveal::class, 'render_callback' ],
                    ]
                );
            }
        );
    }

    function hexToRgba( $hex, $alpha = 1.0 ) {
        if ( preg_match( '/^#[a-f0-9]{6}$/i', $hex ) ) {
            $hex = ltrim( $hex, '#' );
            if ( strlen( $hex ) === 3 ) {
                $hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
            }
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );

            return "rgba($r, $g, $b, $alpha)";
        } else {
            return $hex;
        }

    }


    function getRevealDirection( $direction ) {
        switch ( $direction ) {
            case 'reveal_ltr':
                return 'difl__image_reveal_lr';
            case 'reveal_rtl':
                return 'difl__image_reveal_rl';
            case 'reveal_ttb':
                return 'difl__image_reveal_tb';
            case 'reveal_btt':
                return 'difl__image_reveal_bt';
            default:
                return 'difl__image_reveal_lr';
        }
    }


    function getHoverOverlayDirection( $direction ) {
        switch ( $direction ) {
            case 'left':
                return 'difl__hover_overlay_lr';
            case 'right':
                return 'difl__hover_overlay_rl';
            case 'top':
                return 'difl__hover_overlay_tb';
            case 'bottom':
                return 'difl__hover_overlay_bt';
            case 'linear':
                return 'difl__hover_overlay_linear';
            case 'ease_in_out':
                return 'difl__hover_overlay_ease_in_out';
            case 'ease':
                return 'difl__hover_overlay_ease';
            case 'ease_in':
                return 'difl__hover_overlay_ease_in';
            case 'ease_out':
                return 'difl__hover_overlay_ease_out';
            default:
                return 'difl__hover_overlay_lr';
        }
    }


    function generateHoverOverlay() {
        $hover_overlay_content = ( isset( $this->props['field_hover_overlay_content_enable'] ) && 'on' === $this->props['field_hover_overlay_content_enable'] ) ? sprintf( '<h3 class="title arrival">%1$s</h3><div class="description arrival">%2$s</div>',
            ( isset( $this->props['field_hover_content_title_text'] ) && ! empty( $this->props['field_hover_content_title_text'] ) ) ? $this->props['field_hover_content_title_text'] : "Your Title Goes Here..",
            ( isset( $this->props['field_hover_content_desc_text'] ) && ! empty( $this->props['field_hover_content_desc_text'] ) ) ? $this->props['field_hover_content_desc_text'] : "Your Description Goes Here..."
        ) : "";
        $ho_arrive_from        = ( isset( $this->props['field_hover_overlay_arrive_from'] ) && ! empty( $this->props['field_hover_overlay_arrive_from'] ) ) ? $this->props['field_hover_overlay_arrive_from'] : "left";


        $hover_overlay = ( isset( $this->props['field_hover_overlay_enable'] ) && 'on' === $this->props['field_hover_overlay_enable'] ) ? "<div class= 'difl__image_reveal_hover_overlay {$this->getHoverOverlayDirection($ho_arrive_from)}'>
                        <div class='difl__image_reveal_hover_overlay_content'>
                            {$hover_overlay_content}
                        </div>
                    </div>" : "";


        return $hover_overlay;
    }


    function generateCaption( $image_caption ) {
        if ( 'on' === $this->props['field_caption_enable'] ) {
            $__field__caption_title = $this->props['field_caption_title'];
            $generate_caption       = sprintf(
                '<div class="difl_caption">%1$s</div>',
                $__field__caption_title
            );

            return $generate_caption;
        }
    }


}
