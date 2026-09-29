<?php

namespace DIFL\Server\Modules\ACFGallery;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use WP_Query;

class ACFGallery implements DependencyInterface {
    public $slug       = 'difl_acfgallery';
    public $vb_support = 'on';
	public $icon_path;
    use ModuleUtil;
    use HasProps;

    protected $config_file = 'AcfGallery';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	);

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/acfgallery' )->customCssFields;
	}

    function propMatches( $key, $value ) {
        return isset( $this->props[ $key ] ) && $this->props[ $key ] === $value;
    }

    function getOverlayStyle($attr){
        $styles = '';
        if ($this->propMatches('overlay', 'on')) {
            $overlay_direction = $attr['overlay_direction'] ?? '180deg';
            $overlay_primary   = $attr['overlay_primary'] ?? '#00b4db';
            $overlay_secondary = $attr['overlay_secondary'] ?? '#0083b0';

            $styles  .= "background-image: linear-gradient(" . $overlay_direction . ", " . $overlay_primary . " 0, " . $overlay_secondary . " 100%);";
        }
        return $styles;
    }

    function getBorderAnimStyle($attr){
        $styles = '';

        if ($this->propMatches('border_anim', 'on')) {
            $styles .= '--border-color: ' . ($attr['anm_border_color'] ?? '#ffffff') . ';';
            $styles .= '--border-width: ' . ($attr['anm_border_width'] ?? '3px') . ';';
            $styles .= '--border-margin: ' . ($attr['anm_border_margin'] ?? '15px') . ';';
        }

        return $styles;
    }

    function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

    function jsg_arrow_icon($set = 'set_1', $type='prev') {
        $icons = [
            'set_1' => [
                'next' => '5',
                'prev' => '4',
            ],
            'set_2' => [
                'next' => '$',
                'prev' => '#',
            ],
            'set_3' => [
                'next' => '9',
                'prev' => '8',
            ],
            'set_4' => [
                'next' => 'E',
                'prev' => 'D',
            ],
        ];

        return $icons[$set][$type] ?? '';
    }

    public function get_common_styles( $args ) {
		$attrs      = $args['attrs'] ?? [];
        $attrs      = $this->get_merged_attrs( $args );
        $attrs      = self::build_attr_for_styles( $attrs );
        $attrs      = $this->get_default_value_for_styles($attrs);
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_acf_gallery_container .grid-item",
			'attr'     => $attrs['image_to_display']['innerContent'] ?? null,
			'declarationFunction' => function($props){
                $image_width = 0 !== (int)$props['attrValue'] ? 100/(int)$props['attrValue'] . '%' : '100%';
                return 'width: ' . $image_width . ';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .grid .grid-item",
			'attr'     => $attrs['item_gutter']['innerContent'] ?? null,
			'declarationFunction' => function($props){
                $item_gutter = $props['attrValue'];
                if( $item_gutter ) {
                    return "padding-left: " . $item_gutter . "; padding-bottom: " . $item_gutter . ";";
                }
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .c4-izmir .df-overlay",
			'attr'     => $attrs['overlay']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return $this->getOverlayStyle(
					array(
						'overlay_direction' => $this->props['overlay_direction'] ?? '180deg',
						'overlay_primary'   => $this->props['overlay_primary'] ?? '#00b4db',
						'overlay_secondary' => $this->props['overlay_secondary'] ?? '#0083b0',
					)
				);
            }
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-overlay .et-pb-icon",
			'attr'     => $this->propMatches('field_use_icon', 'on') ? $attrs['field_icon_settings']['innerContent'] : null,
			'declarationFunction' => function () {
                return self::get_font_style( $this->props['field_font_icon'] );
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-overlay .et-pb-icon",
			'attr'     => $this->propMatches('field_use_icon', 'on') ? $attrs['field_icon_settings']['innerContent'] : null,
			'declarationFunction' => function(){
				return 'color: '. $this->props['field_icon_color'] .';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-overlay .et-pb-icon",
			'attr'     => $this->propMatches('field_use_icon', 'on') ? $attrs['field_icon_settings']['innerContent'] : null,
			'declarationFunction' => function(){
				return 'font-size: '. $this->props['field_icon_size'] .';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_acf_gallery_button_container",
			'attr'     => $attrs['more_btn_align']['innerContent'] ?? null,
			'property' => 'text-align'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-overlay",
			'attr'     => $this->propMatches('field_use_icon', 'on') ? $attrs['field_icon_settings']['innerContent'] : null,
			'declarationFunction' => function(){
                return 'display: flex;align-items: '. $this->props['field_icon_placement'] .'; justify-content: '. $this->props['field_icon_alignment'] .';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .c4-izmir",
			'attr'     => $attrs['border_anim']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return $this->getBorderAnimStyle(
					array(
						'anm_border_color'  => $this->props['anm_border_color'] ?? '#ffffff',
						'anm_border_width'  => $this->props['anm_border_width'] ?? '3px',
						'anm_border_margin' => $this->props['anm_border_margin'] ?? '15px',
					)
				);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .c4-izmir",
			'attr'     => $attrs['content_space']['innerContent'] ?? null,
			'property' => '--padding'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .c4-image-rotate-left:hover img",
			'attr'     => $this->propMatches('image_scale', 'c4-image-rotate-left') ? $attrs['image_scale_hover']['innerContent'] : null,
			'declarationFunction' => function(){
                return 'transform: scale('. $this->props['image_scale_hover'] .') rotate(-15deg);';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .c4-image-rotate-right:hover img",
			'attr'     => $this->propMatches('image_scale', 'c4-image-rotate-right') ? $attrs['image_scale_hover']['innerContent'] : null,
			'declarationFunction' => function(){
                return 'transform: scale('. $this->props['image_scale_hover'] .') rotate(15deg);';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination",
			'attr'     => $attrs['pagination_align']['innerContent'] ?? null,
			'property' => 'justify-content'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .prev:before",
			'attr'     => $attrs['next_prev_icon']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return 'content: \'' . $this->jsg_arrow_icon($this->props['next_prev_icon'] ?? '', 'prev') . '\';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .next:after",
			'attr'     => $attrs['next_prev_icon']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return 'content: \'' . $this->jsg_arrow_icon($this->props['next_prev_icon'] ?? '', 'next') . '\';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-acf-gallery-load-more-icon",
			'attr'     => $this->propMatches('more_btn_use_icon', 'on') ? $attrs['more_btn_icon_size']['innerContent'] : null,
			'property' => 'font-size'
        ]);

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-acf-gallery-load-more-icon",
			'attr'     => $this->propMatches('more_btn_use_icon', 'on') ? $attrs['more_btn_font_icon']['innerContent'] : null,
			'declarationFunction' => function () {
                return self::get_font_style( $this->props['more_btn_font_icon'] );
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-acf-gallery-load-more-btn .spinner svg",
			'attr'     => $attrs['spinner_color']['innerContent'] ?? null,
			'property' => 'fill'
        ]);

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-acf-gallery-load-more-btn",
			'attr'     => $attrs['more_btn']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .item-content",
			'attr'     => $attrs['image']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .pagination .page-numbers:not(.dots)",
			'attr'     => $attrs['pagination']['decoration']['border'] ?? null,
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

		foreach ( $common_styles as $common_style ) {
			if ( empty( $common_style ) ) {
				continue;
			}

			foreach ( $common_style as $style ) {
				$styles[] = $style;
			}
		}

		return $styles ?? [];
	}

    private static function df_acf_gallery_options( $images, $object ): array {
        $options = array(
			'show_caption'                => $object['show_caption'],
			'show_description'            => $object['show_description'],
			'image_size'                  => $object['image_size'],
			'use_orientation'             => $object['use_orientation'],
			'image_orientation'           => $object['image_orientation'],
			'layout_mode'                 => $object['layout_mode'],
			'load_more'                   => $object['load_more'],
			'init_count'                  => $object['init_count'],
			'image_count'                 => $object['image_count'],
			'use_lightbox'                => isset( $object['use_lightbox'] ) ? $object['use_lightbox'] : 'off',
			'use_lightbox_content'        => isset( $object['use_lightbox_content'] ) ? $object['use_lightbox_content'] : 'off',
			'use_lightbox_download'       => isset( $object['use_lightbox_download'] ) ? $object['use_lightbox_download'] : 'off',
			'caption_tag'                 => $object['caption_tag'],
			'description_tag'             => $object['description_tag'],
			'image_scale'                 => $object['image_scale'],
			'enable_content_position'     => $object['enable_content_position'],
			'content_position_outside'    => $object['content_position_outside'],
			'content_position'            => $object['content_position'],
			'content_reveal_caption'      => $object['content_reveal_caption'],
			'content_reveal_description'  => $object['content_reveal_description'],
			'border_anim'                 => $object['border_anim'],
			'border_anm_style'            => $object['border_anm_style'],
			'always_show_title'           => $object['always_show_title'],
			'always_show_description'     => $object['always_show_description'],
			'use_url'                     => isset( $object['use_url'] ) ? $object['use_url'] : 'off',
			'url_target'                  => isset( $object['url_target'] ) ? $object['url_target'] : 'same_window',
			'overlay'                     => $object['overlay'],
			'field_use_icon'              => $object['field_use_icon'],
			'field_font_icon'             => isset( $object['field_font_icon'] ) ? $object['field_font_icon'] : array(
                'unicode' => '&#x35;',
                'type'    => 'divi',
                'weight'  => '400',
            ),
			'content_reveal_icon'         => $object['content_reveal_icon'],
			'show_pagination'             => $object['show_pagination'],
			'pagination_img_count'        => $object['pagination_img_count'],
			'use_number_pagination'       => $object['use_number_pagination'],
			'older_text'                  => isset( $object['older_text'] ) ? $object['older_text'] : 'Older Entries',
			'newer_text'                  => isset( $object['newer_text'] ) ? $object['newer_text'] : 'Next Entries',
			'use_icon_only_at_pagination' => $object['use_icon_only_at_pagination'],
		);

		return array_merge( $images, $options );
	}

    private static function df_acf_get_processed_font_content( $icon_data, $content_reveal_icon ) {
        $text = Utils::process_font_icon( $icon_data );
        return sprintf( '<div class="df_acf_gallery_icon_wrap %2$s"><span class="et-pb-icon">%1$s</span></div>',
            esc_attr( $text ),
            esc_attr( $content_reveal_icon )
        );
    }

	private static function df_acf_gallery_render_images( $options = [], $load_more_type = false ): string {
        $default = array(
			'images_array'                => [],
			'show_caption'                => 'off',
			'show_description'            => 'off',
			'image_size'                  => 'medium',
			'use_orientation'             => 'off',
			'image_orientation'           => 'landscape',
			'load_more'                   => 'off',
			'init_count'                  => 6,
			'image_count'                 => 3,
			'use_lightbox'                => 'off',
			'use_lightbox_content'        => 'off',
			'caption_tag'                 => '',
			'description_tag'             => '',
			'image_scale'                 => '',
			'enable_content_position'     => 'off',
			'content_position_outside'    => '',
			'content_position'            => '',
			'content_reveal_caption'      => '',
			'content_reveal_description'  => '',
			'border_anim'                 => 'off',
			'border_anm_style'            => '',
			'always_show_title'           => '',
			'always_show_description'     => '',
			'use_url'                     => 'off',
			'url_target'                  => 'same_window',
			'overlay'                     => 'off',
			'field_use_icon'              => 'off',
			'field_font_icon'             => [],
			'content_reveal_icon'         => '',
			'show_pagination'             => 'off',
			'pagination_img_count'        => 6,
			'use_number_pagination'       => 'off',
			'older_text'                  => 'Older Entries',
			'newer_text'                  => 'Next Entries',
			'use_icon_only_at_pagination' => 'off'
		);

		$default_image = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTA4MCIgaGVpZ2h0PSI1NDAiIHZpZXdCb3g9IjAgMCAxMDgwIDU0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCI+CiAgICAgICAgPHBhdGggZmlsbD0iI0VCRUJFQiIgZD0iTTAgMGgxMDgwdjU0MEgweiIvPgogICAgICAgIDxwYXRoIGQ9Ik00NDUuNjQ5IDU0MGgtOTguOTk1TDE0NC42NDkgMzM3Ljk5NSAwIDQ4Mi42NDR2LTk4Ljk5NWwxMTYuMzY1LTExNi4zNjVjMTUuNjItMTUuNjIgNDAuOTQ3LTE1LjYyIDU2LjU2OCAwTDQ0NS42NSA1NDB6IiBmaWxsLW9wYWNpdHk9Ii4xIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgICAgICA8Y2lyY2xlIGZpbGwtb3BhY2l0eT0iLjA1IiBmaWxsPSIjMDAwIiBjeD0iMzMxIiBjeT0iMTQ4IiByPSI3MCIvPgogICAgICAgIDxwYXRoIGQ9Ik0xMDgwIDM3OXYxMTMuMTM3TDcyOC4xNjIgMTQwLjMgMzI4LjQ2MiA1NDBIMjE1LjMyNEw2OTkuODc4IDU1LjQ0NmMxNS42Mi0xNS42MiA0MC45NDgtMTUuNjIgNTYuNTY4IDBMMTA4MCAzNzl6IiBmaWxsLW9wYWNpdHk9Ii4yIiBmaWxsPSIjMDAwIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz4KICAgIDwvZz4KPC9zdmc+Cg==';

		$options = wp_parse_args( $options, $default );
		extract( $options ); // phpcs:ignore WordPress.PHP.DontExtract
		$images  = '';
		$classes = '';
		$_i      = 0;

		$always_title = 'on' === $always_show_title || 'on' === $enable_content_position ? 'always-show-title ' : $content_reveal_caption;
		$always_title = 'on' === $always_show_title && 'on' !== $enable_content_position ? $always_title . 'c4-fade-up' : $always_title;

		$always_description = 'on' === $always_show_description || 'on' === $enable_content_position ? 'always-show-description ' : $content_reveal_description;
		$always_description = 'on' === $always_show_description && 'on' !== $enable_content_position ? $always_description . 'c4-fade-up' : $always_description;

		$border_anim_class = 'on' === $border_anim ? $border_anm_style : '';

        $icon_data = is_array($field_font_icon) ? $field_font_icon : json_decode( $field_font_icon, true );

		foreach ( $images_array as $image ) {
			if(is_array($image)){
				$id = $image['id'];
			}else if(filter_var($image, FILTER_VALIDATE_URL)){
				$id = attachment_url_to_postid( $image );
			}else{
				$id = $image;
			}

			if ( 'on' === $load_more ) {
				if ( $_i >= $init_count && $load_more_type === false ) {
					break;
				}
			}
			if ( 'on' === $show_pagination && 'on' !== $load_more ) {
				if ( $_i >= $pagination_img_count ) {
					break;
				}
			}
			$_i ++;
			if ( 'on' === $use_orientation ) {
				$width  = 400;
				$height = ( 'landscape' === $image_orientation ) ? 284 : 516;

				$width  = (int) apply_filters( 'et_pb_gallery_image_width', $width );
				$height = (int) apply_filters( 'et_pb_gallery_image_height', $height );
				$media  = wp_get_attachment_image_src( $id, array( $width, $height ) );

			}else {
				$media = wp_get_attachment_image_src( $id, $image_size );
			}

			$media_lightbox     = wp_get_attachment_image_src($id, 'original');
			$lightbox_image_url = '' !== $media_lightbox && !is_bool($media_lightbox) ? $media_lightbox[0] : $default_image;
			$content_box        = '';
			$details            = '' !== $id ? get_post( $id ) : "";
			$caption            = '';
			$description        = '';

			if ( ( 'on' === $use_lightbox_content || 'on' === $show_caption ) && $details ) {
				$caption = '' !== $details->post_excerpt ?
                sprintf( '<div class="%3$s %4$s"><%2$s class="df_acf_gallery_caption">%1$s</%2$s></div>',
                    wp_kses_post($details->post_excerpt, 'divi_flash'),
                    esc_attr( $caption_tag ),
                    esc_attr( $always_title ),
                    esc_attr( 'on' !== $show_caption ? 'df_acf_gallery_hide_title' : '' )
				) : '';
			}

			if ( ( 'on' === $use_lightbox_content || 'on' === $show_description ) && $details ) {
				$description = '' !== $details->post_content ?
				sprintf( '<div class="%3$s %4$s"><%2$s class="df_acf_gallery_description">%1$s</%2$s></div>',
                    wp_kses_post($details->post_content, 'divi_flash'),
                    esc_attr( $description_tag ),
                    esc_attr( $always_description ),
                    esc_attr( 'on' !== $show_description ? 'df_acf_gallery_hide_description' : '' )
                ) : '';
			}

			$content_box = sprintf( '%1$s%2$s', $caption, $description );

			$data_lightbox_html = 'on' === $use_lightbox_content && '' !== $content_box ?
				'data-sub-html=".df_acf_gallery_content"' : '';

			if ( $load_more_type === false ) {
				$classes = "";
			}

			$custom_url = $use_url === 'on' ?
				sprintf( 'data-url="%1$s"', esc_attr( get_post_meta( $id, 'df_ig_url', true ) ) )
				: '';

			$empty_class = $content_box === '' ? ' empty_content' : '';

			if ( empty( $media[0] ) ) {
				continue;
			}
			$image_url = ! empty( $media[0] ) ? $media[0] : $default_image;

			$conent_positioning = sprintf( '<%5$s class="df_acf_gallery_content %2$s %3$s %4$s">
                            %1$s
                        </%5$s>',
				$content_box,
				'on' === $enable_content_position ? esc_attr( $content_position_outside ) : esc_attr( $content_position ),
				esc_attr( $empty_class ),
				'on' === $enable_content_position ? esc_attr( 'outside' ) : '',
				'on' === $enable_content_position ? 'div' : 'figcaption'
			);

			$content_outside_positioning = '<figcaption class="df_acf_gallery_content" ></figcaption>';
			if('on' === $enable_content_position && 'on' === $use_lightbox_content && $details){
				$lightbox_caption = '' !== $details->post_excerpt ?
					sprintf('<div class="df_acf_gallery_is_lightbox"><%2$s class="df_acf_gallery_caption">%1$s</%2$s></div>',
						wp_kses_post($details->post_excerpt, 'divi_flash'),
						esc_attr($caption_tag)
					):"";
				$lightbox_description = '' !== $details->post_content ?
					sprintf('<div class="df_acf_gallery_is_lightbox"><%2$s class="df_acf_gallery_description">%1$s</%2$s></div>',
						wp_kses_post($details->post_content, 'divi_flash'),
						esc_attr($description_tag)
					) : '';
				$content_outside_positioning = sprintf('<figcaption class="df_acf_gallery_content" >%1$s%2$s</figcaption>', $lightbox_caption, $lightbox_description);
			}

			// Lightbox Caption : https://sachinchoolur.github.io/lightgallery.js/demos/captions.html
			$image  = sprintf( '<div class="df_acf_gallery_image grid-item %1$s" data-src="%2$s" %3$s>
                <div class="item-content %4$s" %10$s>
                    %11$s
                    <figure class="%5$s c4-izmir %6$s">
                        %7$s
                        <img class="acf_gallery_image" src="%8$s" alt="%9$s" class=""/>
                        %12$s
                    </figure>
                    %13$s
                </div>
            </div>',
				esc_attr( $classes ),
				esc_attr( $lightbox_image_url ),
				$data_lightbox_html,
				esc_attr( $image_scale ),
				esc_attr( $border_anim_class ),
				esc_attr( ' has_overlay' ),
				'on' === $overlay ? sprintf( '<span class="df-overlay">%1$s</span>', 'on' === $field_use_icon ? self::df_acf_get_processed_font_content( $icon_data, $content_reveal_icon ) : '' ) : '',
				esc_attr( $image_url ),
				esc_attr(get_post_meta($id , '_wp_attachment_image_alt', true)),
				$custom_url,
				'on' === $enable_content_position && in_array( $content_position_outside, [
					'c4-layout-top-left',
					'c4-layout-top-center',
					'c4-layout-top-right'
				] ) ? $conent_positioning : "",
				'on' !== $enable_content_position ? $conent_positioning : $content_outside_positioning,
				'on' === $enable_content_position && in_array( $content_position_outside, [
					'c4-layout-bottom-left',
					'c4-layout-bottom-center',
					'c4-layout-bottom-right'
				] ) ? $conent_positioning : ""
			);
			$images .= $image;
		}

		return $images;
	}

    public static function get_acf_gallery_data( $data ) {

        $acf_gallery_fields = ! empty( $data['acf_gallery_fields'] )
            ? sanitize_key( $data['acf_gallery_fields'] )
            : '';

        $post_id     = ! empty( $data['post_id'] ) ? absint( $data['post_id'] ) : 0;
        $image_array = [];
        $error       = '';

        // Bail early if field key is missing
        if ( empty( $acf_gallery_fields ) ) {
            return [
                'gallery'     => '',
                'image_array' => [],
                'post_id'     => $post_id,
                'error'       => '<h2 class="no-post" style="background-color:#eee; padding:10px 20px; text-align:center">' . esc_html__( 'ACF gallery field not defined.', 'divi_flash' ) . '</h2>',
            ];
        }

        // If post_id not provided → use current post
        if ( 0 === $post_id ) {
            $post_id = get_queried_object_id();
        }

        // Final fallback: get any post that has this ACF field
        if ( ! $post_id ) {

            $query = new \WP_Query( [
                'posts_per_page' => 1,
                'post_type'      => 'post',
                'fields'         => 'ids',
                'meta_query'     => [
                    [
                        'key'     => $acf_gallery_fields,
                        'compare' => 'EXISTS',
                    ],
                ],
            ] );

            if ( ! empty( $query->posts[0] ) ) {
                $post_id = (int) $query->posts[0];
            }
        }

        // Get gallery data
        if ( $post_id ) {
            $gallery_data = get_field( $acf_gallery_fields, $post_id );

            if ( is_array( $gallery_data ) && ! empty( $gallery_data ) ) {
                $image_array = $gallery_data;
            } else {
                $error = '<h2 class="no-post"style="background-color:#eee; padding: 10px 20px; text-align:center">' . esc_html__( "No data found.", 'divi_flash' ) . '</h2>';
            }
        } else {
            $error = '<h2 class="no-post"style="background-color:#eee; padding: 10px 20px; text-align:center">' . esc_html__( "Post not found.", 'divi_flash' ) . '</h2>';
        }

        $options = self::df_acf_gallery_options(
            [ 'images_array' => $image_array ],
            $data
        );

        $gallery = self::df_acf_gallery_render_images( $options );

        return [
            'gallery'     => $gallery,
            'image_array' => $image_array,
            'post_id'     => $post_id,
            'error'       => $error,
        ];
    }

    public function render( $attrs, $content, $render_slug ) {
		if(! class_exists( 'acf_pro' )){
			return "";
		}

		// $this->additional_css_styles( $render_slug );
		wp_enqueue_script( 'imageload' );
		wp_enqueue_script( 'lightgallery-script' );
		wp_enqueue_script( 'df-imagegallery-lib' );
		wp_enqueue_script( 'df-acf-gallery' );

		$load_more         = $this->props['load_more'];
		$more_btn_use_icon = $this->props['more_btn_use_icon'];
		$show_pagination   = $this->props['show_pagination'];

        $image_data = $this->get_acf_gallery_data($this->props);
        if ( ! $image_data['gallery'] ) { return ""; }

		$load_more_icon = $more_btn_use_icon === 'on' ? sprintf( '<span class="df-acf-gallery-load-more-icon">%1$s</span>',
			isset($this->props['more_btn_font_icon']) && $this->props['more_btn_font_icon'] !== '' ? esc_attr( Utils::process_font_icon( $this->props['more_btn_font_icon'] ) ) : '4'
		) : '';

		$load_more_icon_class = 'on' === $more_btn_use_icon ? ' has_icon' : '';

		$load_more_button = $load_more === 'on' ? sprintf(
            '<div class="df_acf_gallery_button_container">
                <button class="df-acf-gallery-load-more-btn%4$s" data-loaded="%2$s">%1$s %3$s
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
            sanitize_text_field( $this->props['load_more_text'] ),
            sanitize_text_field( $this->props['init_count'] ),
            $load_more_icon,
            esc_attr( $load_more_icon_class )
		) : '';

		$older = $this->props['older_text'] ? $this->props['older_text'] : 'Older Entries';
		$next  = $this->props['newer_text'] ? $this->props['newer_text'] : 'Next Entries';
		if ( 'on' === $this->props['use_icon_only_at_pagination'] ) {
			$older = '';
			$next  = '';
		}
		$pagination_data = '';

		if ( 'on' === $show_pagination && count( $image_data['image_array'] ) > (int) $this->props['pagination_img_count'] ) {
			ob_start();
			?>
            <div class="df-acf-gallery-pagination pagination clearfix <?php echo 'on' === $this->props['use_icon_only_at_pagination'] ? 'only_icon' : ''; ?>">
                <a class="prev page-numbers" data-current="1"
                   data-count="<?php echo esc_html( $this->props['pagination_img_count'] ); ?>" href="#"
                   style="display: none;"><?php echo esc_html( $older ); ?></a>
				<?php
				if ( 'on' === $this->props['use_number_pagination'] ) {
					for ( $i = 0; $i < ceil( count( $image_data['image_array'] ) / (int) $this->props['pagination_img_count'] ); $i ++ ) {
						?>
                        <a class="page-numbers <?php echo 0 === $i ? 'current' : ''; ?>"
                           data-page="<?php echo esc_html( ( $i + 1 ) ); ?>"
                           data-count="<?php echo esc_html( $this->props['pagination_img_count'] ); ?>"
                           href="#"><?php echo esc_html( ( $i + 1 ) ); ?></a>
						<?php
					}
				}
				?>
                <a class="next page-numbers" data-current="1"
                   data-count="<?php echo esc_html( $this->props['pagination_img_count'] ); ?>"
                   href="#"><?php echo esc_html( $next ); ?></a>
            </div>
			<?php
			$pagination_data = ob_get_clean();
		}

		$data = df_acf_gallery_options(
			array( 'images_array' => $image_data['image_array'] ),
			$this->props
		);

        $load_more_buttn_controll = count( $image_data['image_array'] ) > $this->props['image_to_display'] ? $load_more_button : "";

		return sprintf( '<div class="df_acf_gallery_container%4$s" data-settings=\'%3$s\'>
                <div class="df_acf_gallery grid">
                    %1$s
                </div>
                %2$s
                %5$s
            </div>',
			$image_data['gallery'],
			$load_more_buttn_controll,
			esc_attr( wp_json_encode( $data ) ),
			'on' === $this->props['use_lightbox'] ? ' ig_has_lightbox' : '',
			$pagination_data
		);
	}
}
