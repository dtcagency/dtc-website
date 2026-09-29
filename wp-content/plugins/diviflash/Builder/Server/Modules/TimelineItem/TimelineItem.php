<?php
/**
 * TimelineItem Module class.
 *
 * @package DIVIFLASH5\Modules\TimelineItem;
 */

namespace DIFL\Server\Modules\TimelineItem;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils as IconFontUtils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;


/**
 * Class TimelineItem
 *
 * @package DIVIFLASH5\Modules\TimelineItem
 */
class TimelineItem implements DependencyInterface {

    use GetPropsTrait;
    use UnifiedStyleRenderer;
    use \DIFL\Server\Traits\StyleUtil;

    protected $parent_attrs;
    protected $styles;

    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/timelineitem' )->customCssFields;
    }

    public static function font_style($font) {
        return static::get_font_style($font);
    }

    private function df_user_device() {
        // library: Mobile Detect
        $user_agent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        if ( class_exists( '\Detection\DIFL_MobileDetect' ) ) {
            $detect = new \Detection\DIFL_MobileDetect;
        }

        $get_devices = [];
        $detect->setUserAgent( $user_agent );

        if ( $detect->isTablet() ) {
            array_push( $get_devices, "tablet" );
        }

        if ( $detect->isMobile() && ! in_array( "tablet", $get_devices ) ) {
            array_push( $get_devices, "mobile" );
        }

        if ( ! empty( $get_devices ) ) {
            return $get_devices[0];
        }

        return false;
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
        $instance->styles = $instance->renderUnifiedStyles( $elements, $attrs, $orderClass, 'difl/timelineitem' );
        $styles           = [];

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

        // Arrow icon
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

        // Arrow line
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

        $styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .et-pb-icon.df_timeline_content_icon",
            'attr'                => $attrs['content_icon_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['content_icon'] ?? $instance->props['content_icon'] ) ,
        ] );
        $styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} span.et-pb-icon.df_timeline_marker_icon",
            'attr'                => $attrs['marker_obj_settings']['innerContent'] ?? null,
            'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['marker_icon'] ?? $instance->props['marker_icon'] ) ,
        ] );

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

    public function render( $parent_attrs ) {
        // Content arrow
        $hasContentElement = ! empty( $this->df_tmln_render_content( $parent_attrs ) );
        $arrow_html        = "on" === ( $parent_attrs['arrow_obj_settings']['innerContent']['desktop']['value']['enable_arrow'] ?? "on" ) && $hasContentElement ?
            $this->df_tmln_render_arrow(
                ( $parent_attrs['arrow_obj_settings']['innerContent']['desktop']['value']['arrow_type'] ?? 'arrow_caret' ),
                ( $parent_attrs['arrow_obj_settings']['innerContent']['desktop']['value']['arrow_icon'] ?? "&#x24;" ),
            ) : "";

        $data_settings = [
            'child_arrow_vertical_position'      => ! empty( $this->props['child_arrow_vertical_position'] ) ? $this->props['child_arrow_vertical_position'] : "",
            'child_date_arrow_vertical_position' => isset( $this->props['child_date_arrow_vertical_position'] ) ? $this->props['child_date_arrow_vertical_position'] : "",

        ];

        $output = sprintf(
            '<div class="df_timeline_item" data-settings=\'%6$s\'>
                %1$s
                %2$s
                <div class="df_timeline_content_area %5$s">
                    <div class="df_timeline_content">
                        %3$s
                    </div>
                    %4$s
                </div>
                <div class="df_line_inner"></div>
            </div>',
            $this->df_tmln_render_date( $parent_attrs ), // 1
            $this->df_tmln_render_marker( $parent_attrs ), // 2
            $this->df_tmln_render_content( $parent_attrs ), // 3
            $arrow_html, // 4
            ! $hasContentElement ? 'df_hide_section' : '', // 5
            wp_json_encode( $data_settings ) // 6
        );

        return $output;
    }

    public function df_tmln_render_date( $parent_attrs ) {
        $disable_date = "off" !== ( $parent_attrs['date_obj_settings']['innerContent']['desktop']['value']['disable_date'] ?? 'off' );
        $layout_type  = ( $parent_attrs['layout_obj_settings']['innerContent']['desktop']['value']['layout_type'] ?? "middle" );
        $title_tag    = esc_attr( $this->props['date_title_tag'] ? $this->props['date_title_tag'] : "h3" );
        $subtitle_tag = esc_attr( $this->props['date_sub_title_tag'] ? $this->props['date_sub_title_tag'] : "h4" );

        $title_html = "" !== $this->props['date_title'] ?
            sprintf(
                '<%1$s class="df_timeline_date_title">%2$s</%1$s>',
                $title_tag,
                $this->props['date_title'] ? $this->props['date_title'] : ""
            ) : '';

        $subtitle_html = "" !== $this->props['date_sub_title'] ?
            sprintf(
                '<%1$s class="df_timeline_date_subtitle">%2$s</%1$s>',
                $subtitle_tag,
                $this->props['date_sub_title'] ? $this->props['date_sub_title'] : ""
            ) : '';
        $arrow_html    = "on" === ( $parent_attrs['date_arrow_obj_settings']['innerContent']['desktop']['value']['enable_date_arrow'] ?? 'on' ) ?
            $this->df_tmln_render_arrow(
                ( $parent_attrs['date_arrow_obj_settings']['innerContent']['desktop']['value']['date_arrow_type'] ?? 'arrow_caret' ),
                ( $parent_attrs['date_arrow_obj_settings']['innerContent']['desktop']['value']['date_arrow_icon'] ?? "&#x24;" )
            ) : "";

        return sprintf(
            '<div class="df_timeline_date_area %5$s %6$s">
                <div class="df_timeline_date_content %4$s">
                    %1$s
                    %2$s
                    %3$s
                </div>
             </div>',
            $arrow_html,
            $title_html,
            $subtitle_html,
            empty( $this->props['date_title'] ) && empty( $this->props['date_sub_title'] ) ? 'df_hide_section' : '',
            $disable_date && 'middle' === $layout_type ? "df_hide_section" : "",
            $disable_date && 'middle' !== $layout_type ? "df_disable_section" : ""
        );
    }


    public function df_tmln_render_marker( $parent_attrs ) {
        $marker_type        = $this->props['marker_type'] ? $this->props['marker_type'] : "icon";
        $marker_icon        = sprintf(
            '<span class="et-pb-icon df_timeline_marker_icon">%1$s</span>',
            ! empty( $this->props['marker_icon'] ) ? esc_attr( Utils::process_font_icon( $this->props['marker_icon'] ) ) : [
                "type"    => "divi",
                "unicode" => "&#x31;",
                "weight"  => "400"
            ]
        );
        $isMarkerMobile     = 'mobile' === $this->df_user_device() && "on" === $this->props['enable_marker_icon_mobile'];
        $marker_icon_mobile = sprintf(
            '<span class="et-pb-icon df_timeline_marker_icon %2$s">%1$s</span>',
            ! empty( $this->props['mobile_marker_icon'] ) ? esc_attr( Utils::process_font_icon( $this->props['mobile_marker_icon'] ) ) : "&#xe00a;",
            $isMarkerMobile ? "marker_mobile" : ""
        );
        $marker_img         = $this->props['marker_img'] ? $this->df_render_tmln_image( $this->props['marker_img'], $this->props['marker_img_alt_txt'] ) : "";
        $marker_txt         = $this->props['marker_txt'] ? $this->props['marker_txt'] : "Mar 1, 2023";
        $marker_html        = "";

        switch ( $marker_type ) {
            case 'icon':
                $marker_html = $isMarkerMobile ? $marker_icon_mobile : $marker_icon;
                break;
            case 'image':
                $marker_html = $marker_img;
                break;
            case 'text':
                $marker_html = $marker_txt;
                break;
            case 'blank':
                $marker_html = "";
                break;
        }

        return sprintf(
            '<div class="df_timeline_marker">%1$s</div>',
            $marker_html
        );
    }


    public function df_tmln_render_content( $parent_attrs ) {
        $title_tag         = esc_attr( $this->props['title_tag'] ? $this->props['title_tag'] : "h3" );
        $subtitle_tag      = esc_attr( $this->props['sub_title_tag'] ? $this->props['sub_title_tag'] : "h4" );
        $title_html        = ! empty( $this->props['title'] ) ?
            sprintf(
                '<%1$s class="df_timeline_title">%2$s</%1$s>',
                $title_tag,
                $this->props['title']
            ) : '';
        $subtitle_html     = ! empty( $this->props['sub_title'] ) ?
            sprintf(
                '<%1$s class="df_timeline_subtitle">%2$s</%1$s>',
                $subtitle_tag,
                $this->props['sub_title']
            ) : '';
        $content_btn       = $this->props['enable_content_button'] ? $this->df_tmln_render_button() : "";
        $content           = ! empty( $this->props['content'] ) ? $this->props['content'] : '';
        $content_html      = ! empty( $content ) ? sprintf( '<div class="df_timeline_desc">%1$s</div>', $content ) : "";
        $content_icon_html = ! empty( $this->props['content_icon'] ) ?
            "<span class='et-pb-icon df_timeline_content_icon'>" . esc_attr( Utils::process_font_icon( $this->props['content_icon'] ) ) . "</span>" :
            "<span class='et-pb-icon df_timeline_content_icon'>&#xe00a;</span>";
        $content_img_html  = ! empty( $this->props['content_image'] ) ? $this->df_render_tmln_image( $this->props['content_image'], $this->props['content_image_alt_text'] ) : "";

        $media_html = 'on' === $this->props['enable_content_media'] ?
            sprintf(
                '<div class="df_timeline_media">%1$s</div>',
                'image' === $this->props['content_media_type'] ? $content_img_html : $content_icon_html
            )
            : "";

        return ! empty( $title_html ) || ! empty( $subtitle_html ) || ! empty( $content ) || ! empty( $content_btn ) || ! empty( $media_html ) || $content_btn ?
            sprintf(
                '%1$s %2$s %3$s %4$s %5$s',
                $title_html,
                $subtitle_html,
                $media_html,
                $content_html,
                $content_btn
            ) : "";
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


    public function df_tmln_render_button() {
        $text   = isset( $this->props['button_text'] ) ? $this->props['button_text'] : '';
        $url    = isset( $this->props['button_url'] ) ? $this->props['button_url'] : '';
        $target = $this->props['button_url_new_window'] === 'on' ? 'target="_blank"' : '';

        $button_font_icon = $this->props['button_font_icon'];
        $button_icon_pos  = $this->props['button_icon_placement'];

        // Button icon
        $button_icon = $this->props['use_button_icon'] !== 'off' ? sprintf(
            '<span class="et-pb-icon df_timeline_btn_icon">%1$s</span>',
            $button_font_icon !== '' ? esc_attr( Utils::process_font_icon( $button_font_icon ) ) : '5'
        ) : '';
        if ( 'on' === $this->props['enable_content_button'] ) {
            return sprintf(
                '<div class="df_timeline_button %6$s">
                    <a href="%1$s" %3$s data-icon="5">%5$s <span>%2$s</span> %4$s</a>
                </div>',
                esc_attr( $url ),
                esc_html( trim( $text ) ),
                $target,
                $button_icon_pos === 'right' ? $button_icon : '',
                $button_icon_pos === 'left' ? $button_icon : '',
                "left" !== $button_icon_pos ? "right" : "left"
            );
        } else {
            return '';
        }
    }


    public function df_tmln_render_arrow( $type, $icon ) {
        $arrow_type      = ! empty( $type ) ? $type : "arrow_caret";
        $iconObj         = [
            'unicode' => $icon,
            'type'    => 'divi',
            'weight'  => '400'
        ];
        $arrow_icon_html = ! empty( $icon ) ?
            "<span class='et-pb-icon timeline_arrow_icon 1'>" . Utils::process_font_icon( $iconObj ) . "</span>" :
            "<span class='et-pb-icon timeline_arrow_icon 2'>".Utils::process_font_icon( ['unicode' => '&#x45;', 'type' => 'divi', 'weight' => '400'],true,true ) ."</span>";
        $arrow_icon      = "";

        switch ( $arrow_type ) {
            case 'arrow_caret':
                $arrow_icon = '<div class="timeline_arrow_caret"></div>';
                break;
            case 'arrow_icon':
                $arrow_icon = $arrow_icon_html;
                break;
            case 'arrow_line':
                $arrow_icon = '<div class="timeline_arrow_line"></div>';
                break;
        }

        return sprintf( '<div class="timeline_arrow">%1$s</div>', $arrow_icon );
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {

        $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

        $instance = new static();
        $instance->initializeProps( $elements, $attrs );
        $instance->parent_attrs = $parent->attrs ?? [];

        wp_enqueue_script( 'df-timeline-item' );


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
                'classnamesFunction'  => [ TimelineItem::class, 'module_classnames' ],
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => [ TimelineItem::class, 'module_styles' ],
                'scriptDataComponent' => [ TimelineItem::class, 'module_script_data' ],
                'parentAttrs'         => $parent->attrs ?? [],
                'parentId'            => $parent->id ?? '',
                'parentName'          => $parent->blockName ?? '',
                'children'            => $elements->style_components(
                        [
                            'attrName' => 'module',
                        ]
                    ) . $instance->render( $instance->parent_attrs ),
            ]
        );
    }

    public function load() {
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/timeline-item';

        add_action(
            'init',
            function () use ( $module_json_folder_path ) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ TimelineItem::class, 'render_callback' ],
                    ]
                );
            }
        );
    }


}
