<?php
/**
 * ImageCarouselItem Module class.
 *
 * @package DIVIFLASH5\Modules\ImageCarouselItem;
 */

namespace DIFL\Server\Modules\ImageCarouselItem;

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct access forbidden.' );
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;


/**
 * Class ImageCarouselItem
 *
 * @package DIVIFLASH5\Modules\ImageCarouselItem
 */
class ImageCarouselItem implements DependencyInterface {

    use GetPropsTrait;
    use UnifiedStyleRenderer;

    protected $styles;
    protected $parent_attrs;

    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/imagecarouselitem' )->customCssFields;
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
        $classnames_instance->add( 'swiper-slide' );
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
        $instance->styles = $instance->renderUnifiedStyles( $elements, $attrs, $order_class, 'difl/imagecarouselitem' );
        $styles           = [];

        if ( $instance->props['content_hover'] === 'on' ) {
            $styles[] = CommonStyle::style( [
                'selector'            => "{$order_class} .content",
                'attr'                => $attrs['content_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) use ( $instance ) {
                    $anim_direction = $props['attrValue']['anim_direction'] ?? $instance->props['anim_direction'];

                    return "opacity: 0; transform:" . $instance->df_transform_values( $anim_direction, 'default' ) . ";";
                },
            ] );
            $styles[] = CommonStyle::style( [
                'selector'            => "{$order_class}:hover .content",
                'attr'                => $attrs['content_obj_settings']['innerContent'] ?? null,
                'declarationFunction' => function ( $props ) use ( $instance ) {
                    $anim_direction = $props['attrValue']['anim_direction'] ?? $instance->props['anim_direction'];

                    return "opacity: 1; transform:" . $instance->df_transform_values( $anim_direction, 'hover' ) . ";";
                },
            ] );
        };
        if (
            !empty($attrs['module']['decoration']['border']['desktop']['value']['radius']) ||
            !empty($attrs['module']['decoration']['border']['tablet']['value']['radius']) ||
            !empty($attrs['module']['decoration']['border']['phone']['value']['radius'])
        ) {
            $styles[] = CommonStyle::style([
                'selector' => "{$order_class}",
                'attr' => [
                    'desktop' => [
                        'value' => 'default',
                    ],
                ],
                'declarationFunction' => fn($props) => "overflow: hidden !important;",
            ]);
        }
        $styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ) );
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
                ],
            ]
        );
    }

    public function render( $parent_attrs ) {
        $src            = 'src';
        $image_alt_text = $this->props['alt_text'] !== '' ? $this->props['alt_text'] : df_image_alt_by_url( $this->props['image'] );
        $image          = $this->props['image'] !== '' ?
            sprintf( '<div class="ic_image_wrapper"><img class="df-ic-image" %3$s="%1$s" alt="%2$s" /></div>', esc_attr( $this->props['image'] ), esc_attr( $image_alt_text ), $src ) : '';


        $caption         = ( !is_null( $this->props['caption'] ) && $this->props['caption'] !== '' ) ? sprintf( '<%2$s class="ic_caption">%1$s</%2$s>', esc_attr( $this->props['caption'] ), esc_attr( $this->props['caption_tag'] ) ) : '';
        $content         = ( !is_null( $this->props['caption'] )  && $this->props['caption'] !== '' ) || $this->df_render_button( 'ic_button' ) !== '' ?
            sprintf( '<div class="content">%1$s %2$s</div>', $caption, $this->df_render_button( 'ic_button' ) ) : '';
        $overlay_wrapper = sprintf( '<div class="overlay_wrapper">%1$s</div>', $content );
        $lightbox_caption = (
            !empty($this->props['caption']) &&
            !empty($parent_attrs['settings_obj_settings']) &&
            isset($parent_attrs['settings_obj_settings']['innerContent']['desktop']['value']['use_lightbox_caption']) &&
            $parent_attrs['settings_obj_settings']['innerContent']['desktop']['value']['use_lightbox_caption'] === 'on'
        ) ?
            'data-sub-html=".ic_caption"' : '';

        return sprintf( '<div> <div class="df_ici_container" data-src="%3$s" %4$s> %1$s%2$s </div></div>',
            $image,
            $overlay_wrapper,
            esc_attr( $this->props['image'] ),
            $lightbox_caption,
        );
    }

    public static function render_callback( $attrs, $content, $block, $elements ) {
        $parent = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );

        $instance = new static();
        $instance->initializeProps( $elements, $attrs );
        $instance->parent_attrs = $parent->attrs ?? [];

        wp_enqueue_script( 'df-image-carousel-item' );

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
                'classnamesFunction'  => [ ImageCarouselItem::class, 'module_classnames' ],
                'moduleCategory'      => $block->block_type->category,
                'stylesComponent'     => [ ImageCarouselItem::class, 'module_styles' ],
                'scriptDataComponent' => [ ImageCarouselItem::class, 'module_script_data' ],
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
        $module_json_folder_path = DIFL_MODULES_JSON_PATH . '/image-carousel-item';

        add_action(
            'init',
            function () use ( $module_json_folder_path ) {
                ModuleRegistration::register_module(
                    $module_json_folder_path,
                    [
                        'render_callback' => [ ImageCarouselItem::class, 'render_callback' ],
                    ]
                );
            }
        );
    }

    public function df_render_button( $key ) {
        $text   = isset( $this->props[ $key . '_button_text' ] ) ? $this->props[ $key . '_button_text' ] : '';
        $url    = isset( $this->props[ $key . '_button_url' ] ) ? $this->props[ $key . '_button_url' ] : '';
        $target = $this->props[ $key . '_button_url_new_window' ] === 'on' ?
            'target="_blank"' : '';
        if ( ( isset( $text ) && $text !== '' ) || ( isset( $url ) && $url !== '' ) ) {
            return sprintf( '<div class="df_ic_button_wrapper">
                <a class="df_ic_button" href="%1$s" %3$s>%2$s</a>
            </div>', esc_attr( $url ), esc_html( $text ), $target );
        } else {
            return '';
        }
    }


    public function df_transform_values( $key = 'top', $state = 'default' ) {
        $transform_values = array(
            'top'          => [
                'default' => 'translateY(-60px)',
                'hover'   => 'translateY(0px)'
            ],
            'bottom'       => [
                'default' => 'translateY(60px)',
                'hover'   => 'translateY(0px)'
            ],
            'left'         => [
                'default' => 'translateX(-60px)',
                'hover'   => 'translateX(0px)'
            ],
            'right'        => [
                'default' => 'translateX(60px)',
                'hover'   => 'translateX(0px)'
            ],
            'center'       => [
                'default' => 'scale(0)',
                'hover'   => 'scale(1)'
            ],
            'top_right'    => [
                'default' => 'translateX(50px) translateY(-50px)',
                'hover'   => 'translateX(0px) translateY(0px)'
            ],
            'top_left'     => [
                'default' => 'translateX(-50px) translateY(-50px)',
                'hover'   => 'translateX(0px) translateY(0px)'
            ],
            'bottom_right' => [
                'default' => 'translateX(50px) translateY(50px)',
                'hover'   => 'translateX(0px) translateY(0px)'
            ],
            'bottom_left'  => [
                'default' => 'translateX(-50px) translateY(50px)',
                'hover'   => 'translateX(0px) translateY(0px)'
            ]
        );

        return $transform_values[ $key ][ $state ];
    }

}
