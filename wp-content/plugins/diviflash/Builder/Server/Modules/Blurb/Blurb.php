<?php

namespace DIFL\Server\Modules\Blurb;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Handler\Fa_Icon_Handler;

class Blurb implements DependencyInterface {
	use ModuleUtil;
	use HasProps;
	use StyleUtil;

	protected $config_file = 'Blurb';

	public static $content;

    public static function custom_css()
    {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/advanced-blurb')->customCssFields;
    }

	public function df_render_image_icon() {
		if ( isset( $this->props['blurb_icon_enable'] ) && $this->props['blurb_icon_enable'] === 'on' ) {
			return sprintf(
				'<span class="et-pb-icon df-blurb-icon">%1$s</span>',
				isset( $this->props['blurb_icon'] ) && $this->props['blurb_icon'] !== '' ?
					Utils::process_font_icon( $this->props['blurb_icon'] ) : '4'
			);
		} else if (
            isset( $this->attrs['image']['innerContent']['desktop']['value']['src'] )
        ) {

			$src       = 'src';
			$image_alt = $this->props['alt_text'] !== '' ? $this->props['alt_text'] : df_image_alt_by_url( $this->props['src'] );
			$image_src = $this->attrs['image']['innerContent']['desktop']['value']['src'] ?? null;

			return sprintf(
				'<img class="df_ab_blurb_image_img" %3$s="%1$s" alt="%2$s" />',
				$image_src,
				$image_alt,
				$src
			);
		}
	}

	public function df_render_badge_icon() {
		if ( isset( $this->props['badge_icon_enable'] ) && $this->props['badge_icon_enable'] === 'on' ) {

			return sprintf(
				'<span class="et-pb-icon badge_icon">%1$s</span>',
				isset( $this->props['badge_icon'] ) && $this->props['badge_icon'] !== '' ?
					esc_attr( Utils::process_font_icon( $this->props['badge_icon'] ) ) : '4'
			);
		}
	}

	public function render_button( $attrs = null ) {
        // Same as title: read from attrs when available so empty = no markup
        $button_from_attrs = ( $attrs && isset( $attrs['button']['innerContent']['desktop']['value'] ) )
            ? $attrs['button']['innerContent']['desktop']['value']
            : null;
        $button_text = ( $button_from_attrs !== null && isset( $button_from_attrs['button_text'] ) )
            ? (string) $button_from_attrs['button_text']
            : ( $this->props['button_text'] ?? '' );
        $button_url = ( $button_from_attrs !== null && isset( $button_from_attrs['button_url'] ) )
            ? $button_from_attrs['button_url']
            : ( $this->props['button_url'] ?? '#' );

        $target_page = ( $this->props['button_url_new_window'] ?? 'off' ) === 'on'
            ? '_blank'
            : '_self';

        $button_icon_position = $this->props['button_icon_position'] ?? '';

        if ( '' === trim( $button_text ) ) {
            return '';
        }

        $button_icon = '';
        if ( ( $this->props['button_icon_enable'] ?? 'off' ) === 'on' ) {
            $icon = '5';

            if ( ! empty( $this->props['button_icon'] ) ) {
                $icon = Utils::process_font_icon( $this->props['button_icon'] );
            }

            $button_icon = '<span class="et-pb-icon df-blurb-button-icon">' . esc_html( $icon ) . '</span>';
        }

        if ( $button_icon_position === 'left' ) {
            $button_content = $button_icon . esc_html( $button_text );
        } else {
            $button_content = esc_html( $button_text ) . $button_icon;
        }

        return sprintf(
            '<div class="df_ab_blurb_button_wrapper">
                <a class="df_ab_blurb_button" href="%1$s" target="%2$s" rel="noopener noreferrer">
                    %3$s
                </a>
            </div>',
            esc_url( $button_url ),
            esc_attr( $target_page ),
            $button_content
        );
    }


	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );

        $orderClass    = $args['orderClass'] ?? '';
        $elements      = $args['elements'];
        $styles        = [];
		$common_styles = [];

        $common_styles[] = CssStyle::style([
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ]);

        if("on" === $this->props['button_full_width'] ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_ab_blurb_button",
                'attr'                => $attrs['button_width_alignment']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    return "display: block !important;";
                },
                'important' => true
            ] );
        }

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .df_ab_blurb_button_wrapper",
            'attr'                => $attrs['button_width_alignment']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                $alignment = $props['attrValue']['button_alignment'] ?? $this->props['button_alignment'];

                return "text-align: {$alignment} !important;";
            },
            'important' => true
        ] );

        if("on" === $this->props['button_icon_enable'] ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df-blurb-button-icon",
                'attr'                => $attrs['button_icon_object']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $button_icon = $props['attrValue']['button_icon'] ?? $this->props['button_icon'];

                    return self::get_font_style( $button_icon );
                },
            ] );
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df-blurb-button-icon",
                'attr'                => $attrs['button_icon_object']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $font_size = $props['attrValue']['button_icon_size'] ?? $this->props['button_icon_size'];

                    return "font-size: {$font_size} !important;";
                },
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df-blurb-button-icon, {$orderClass}:hover .df-blurb-button-icon",
                'attr'                => $attrs['button_icon_object']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $color = $props['attrValue']['button_icon_color'] ?? $this->props['button_icon_color'] ?? '';

                    if (empty($color)) {
                        return '';
                    }

                    return "color: {$color} !important;";
                },
            ] );
        }

        $common_styles[] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_ab_blurb_image_img",
			'attr'     => $attrs['icon_image_image']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
				$bgColor = $props['attrValue'] ?? null;
				if (empty($bgColor)) {
					return '';
				}
				return "background-color: {$bgColor};";
			},
		] );

		// $common_styles[] = CommonStyle::style( [
		// 	'selector'            => "{$orderClass}",
		// 	'attr'                => $attrs['module']['decoration']['border'] ?? [],
		// 	'declarationFunction' => function ($props) {
		// 		$has_radius = $props['attrValue']['radius'] ?? false;
		// 		return $has_radius ? 'overflow: hidden !important;' : '';
		// 	},
        //     'important' => true
		// ] );

		$common_styles[] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_ab_blurb_content_container",
			'attr'     => $attrs['content_area_alignment']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
				$alignment = $props['attrValue'] ?? null;
				if (empty($alignment)) {
					return '';
				}
				return "text-align: {$alignment};";
			},
		] );

        $common_styles[] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_ab_blurb_content_container",
			'attr'     => $attrs['content_area_alignment']['innerContent'] ?? null,
			'declarationFunction' => function ($props) {
				$overflow = $props['attrValue'] ?? null;
				if (empty($overflow)) {
					return '';
				}
				return "overflow: {$overflow};";
			},
		] );

        // $common_styles[] = CommonStyle::style([
        //     'selector' => "{$orderClass} .df_ab_blurb_content_container",
        //     'attr'     => $attrs['content_area']['decoration']['border'] ?? [],
        //     'declarationFunction' => function( $props ) {
        //         $has_radius = $props['attrValue']['radius'] ?? false;

        //         return $has_radius
        //             ? 'overflow: hidden !important;'
        //             : 'overflow: auto;';
        //     },
        // ]);

        $common_styles[] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_ab_blurb_image",
			'attr'     => $attrs['image_icon_alignment']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
				$alignment = $props['attrValue'] ?? null;
				if (empty($alignment)) {
					return '';
				}
				return "text-align: {$alignment};";
			},
		] );
        $common_styles[] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_ab_blurb_container",
			'attr'     => $attrs['image_icon_item_align']['innerContent'] ?? [],
			'property' => 'align-items',
			'default'  => 'center',
		] );


        if(isset($this->props['image_icon_container_position']) && $this->props['image_icon_container_position'] === 'outside') {
            if(isset($this->props['image_placement']) && in_array($this->props['image_placement'], ['flex_left', 'flex_right'])) {
                $common_styles[] = CommonStyle::style( [
                    'selector'  => "{$orderClass} .df_ab_blurb_image",
                    'attr'  => $attrs['image_container_width']['innerContent'] ?? [],
                    'declarationFunction' => function ($props) {
                        $width = $props['attrValue'];

                        if(!empty($width)) {
                            return "width: {$width} !important;";
                        }

                    },
                    'important' => true
                ] );

                $common_styles[] = CommonStyle::style( [
                    'selector'  => "{$orderClass} .df_ab_blurb_content_container",
                    'attr'  => $attrs['image_container_width']['innerContent'] ?? [],
                    'declarationFunction' => function ($props) {
                        $width = $props['attrValue'];

                        if(!empty($width)) {
                            return "width: calc(100% - {$width}) !important;";
                        }

                    },
                    'important' => true
                ] );
            }
        }


        if("on" === $this->props['badge_enable'] && "on" === $this->props['badge_icon_enable'] ) {
            $common_styles[] = CommonStyle::style( [
                'selector'  => "{$orderClass} .df_ab_blurb_badge .et-pb-icon",
                'attr'  => $attrs['badge']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    if(isset($props['attrValue']['badge_icon_color'])) {
                        $color = $props['attrValue']['badge_icon_color'];
                        return "color: {$color} !important;";
                    }

                },
                'important' => true
            ] );
            $common_styles[] = CommonStyle::style( [
                'selector'  => "{$orderClass} .df_ab_blurb_badge .et-pb-icon",
                'attr'  => $attrs['badge']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    if(isset($props['attrValue']['badge_icon_size'])) {
                        $font_size = $props['attrValue']['badge_icon_size'];
                        return "font-size: {$font_size} !important;";
                    }

                },
                'important' => true
            ] );
        }

        if("on" === $this->props['order_enable'] ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_ab_blurb_image",
                'attr'                => $attrs['item_order']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $image_order = $props['attrValue']['image_order'] ?? $this->props['image_order'];
                    return "order: {$image_order};";
                },
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_ab_blurb_title",
                'attr'                => $attrs['item_order']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $title_order = $props['attrValue']['title_order'] ?? $this->props['title_order'];
                    return "order: {$title_order};";
                },
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_ab_blurb_sub_title",
                'attr'                => $attrs['item_order']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $sub_title_order = $props['attrValue']['sub_title_order'] ?? $this->props['sub_title_order'];
                    return "order: {$sub_title_order};";
                },
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_ab_blurb_description",
                'attr'                => $attrs['item_order']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $content_order = $props['attrValue']['content_order'] ?? $this->props['content_order'];
                    return "order: {$content_order};";
                },
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_ab_blurb_button_wrapper",
                'attr'                => $attrs['item_order']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $button_order = $props['attrValue']['button_order'] ?? $this->props['button_order'];
                    return "order: {$button_order};";
                },
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'            => "{$orderClass} .df_ab_blurb_badge_wrapper",
                'attr'                => $attrs['item_order']['innerContent'] ?? [],
                'declarationFunction' => function ($props) {
                    $badge_order = $props['attrValue']['badge_order'] ?? $this->props['badge_order'];
                    return "order: {$badge_order};";
                },
            ] );
        }


		$common_styles[] = CommonStyle::style( [
            "selector" => "{$orderClass} .df-blurb-icon",
            'selectors' => [
                'desktop' => [
                    'value' => "{$orderClass} .df-blurb-icon",
                    'hover' => "{$orderClass} .df_ab_blurb_container:hover .df-blurb-icon"
                ]
            ],
			'attr'                => $attrs['icon_image_icon']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
                $color = $props['attrValue']['blurb_icon_color'] ?? $this->props['blurb_icon_color'];
				return "color: {$color};";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-blurb-icon, {$orderClass} .df_ab_blurb_container:hover .df-blurb-icon",
			'attr'                => $attrs['icon_image_icon']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
                $bg_color = $props['attrValue']['blurb_icon_background_color'] ?? $this->props['blurb_icon_background_color'];
				return "background-color: {$bg_color};";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-blurb-icon",
			'attr'                => $attrs['icon_image_icon']['innerContent'] ?? [],
			'property'            => 'font-size',
			'declarationFunction' => function ( $props ) {
				$icon_size = $props['attrValue']['icon_size'] ?? $this->props['icon_size'];
				// Use attrValue (same as Divi BlurbModule) so icon object with type/unicode/weight is passed and font-family is generated.
				$icon = $props['attrValue']['blurb_icon'] ?? $this->props['blurb_icon'];
				return "font-size: {$icon_size};" . self::get_font_style( $icon );
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .badge_icon",
			'attr'                => $attrs['badge']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				$icon = $props['attrValue']['badge_icon'] ?? $this->props['badge_icon'];
				return self::get_font_style( $icon );
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_ab_blurb_image",
			'attr'                => $attrs['zindex']['innerContent'] ?? [],
			'declarationFunction' => function () {
				return "z-index: {$this->props['blurb_img_zindex']};";
			},
		] );

		if ( ! empty( $attrs['badge']['innerContent']['desktop']['value']['badge_enable'] ) && $attrs['badge']['innerContent']['desktop']['value']['badge_enable'] === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df_ab_blurb_badge_wrapper",
				'attr'                => $attrs['zindex']['innerContent'] ?? [],
				'declarationFunction' => function () {
					return "z-index: {$this->props['badge_zindex']};";
				},
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_ab_blurb_button_wrapper",
			'attr'                => $attrs['zindex']['innerContent'] ?? [],
			'declarationFunction' => function () {
				return "z-index: {$this->props['button_zindex']};";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_ab_blurb_title",
			'attr'                => $attrs['zindex']['innerContent'] ?? [],
			'declarationFunction' => function () {
				return "z-index: {$this->props['title_zindex']};";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_ab_blurb_sub_title",
			'attr'                => $attrs['zindex']['innerContent'] ?? [],
			'declarationFunction' => function () {
				return "z-index: {$this->props['sub_title_zindex']};";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_ab_blurb_description",
			'attr'                => $attrs['zindex']['innerContent'] ?? [],
			'declarationFunction' => function () {
				return "z-index: {$this->props['content_zindex']};";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_ab_blurb_badge_wrapper",
			'attr'                => $attrs['badge']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
                $alignment = $props['attrValue']['badge_alignment'] ?? $this->props['badge_alignment'];
				return "text-align: {$alignment};";
			},
            'important' => true
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .badge_text_1",
			'attr'                => $attrs['badge']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
				return "display: block !important;";
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .badge_text_2",
			'attr'                => $attrs['badge']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
				return "display: block !important;";
			},
		] );

        $common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_ab_blurb_container",
			'attr'      => $attrs['content_width']['innerContent'] ?? [],
			'declarationFunction' => function ($props) {
				$maxWidth = $props['attrValue'] ?? null;
				if (empty($maxWidth)) {
					return '';
				}
				return "max-width: {$maxWidth} !important;";
			},
		] );

        $common_styles[] = CommonStyle::style( [
            'selector'  => "{$orderClass} .df_ab_blurb_container .df_ab_blurb_image_img",
            'attr'      => $attrs['image_width']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
				$maxWidth = $props['attrValue'] ?? null;
				if (empty($maxWidth)) {
					return '';
				}
				return "max-width: {$maxWidth} !important;";
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

	public function render( $attrs, $content, $block, $elements ) {
        $title_tag = 'h3';
        if (isset($attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'])) {
            $title_tag = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'];
        }

        $sub_title_tag = 'h4';
        if (isset($attrs['sub_title']['decoration']['font']['font']['desktop']['value']['headingLevel'])) {
            $sub_title_tag = $attrs['sub_title']['decoration']['font']['font']['desktop']['value']['headingLevel'];
        }

		$title_level             = $this->props['title_level'];
		$sub_title_level         = $this->props['sub_title_level'];
        $title_url_data = $attrs['title_url']['innerContent']['desktop']['value'] ?? [];
		$title_url               = $title_url_data['url'] ?? '';
        $title_url_target   = $title_url_data['target'] ?? 'off';

		// Check if title exists in attrs structure
		$title_from_attrs = isset( $attrs['title']['innerContent']['desktop']['value'] )
			? $attrs['title']['innerContent']['desktop']['value']
			: null;

		$title_value = $title_from_attrs !== null ? $title_from_attrs : '';
		$title_safe  = wp_kses_post( $title_value );
        $title_value_trimmed = trim( $title_value );
        $is_title_empty = empty( $title_value_trimmed );

		$title_url_target        = $title_url_target === 'on' ?
			'target="_blank"' : '';
		$title_element_with_link = ! $is_title_empty ?
			sprintf( '<a href="%1$s" class="df_ab_title_link" %3$s>%2$s</a>',
				esc_url( $title_url ),
				$title_safe,
				$title_url_target
			) : '';

		$title_html = $is_title_empty !== '' ?
			sprintf( '<%1$s class="df_ab_blurb_title">%2$s</%1$s>',
				et_pb_process_header_level( $title_level, $title_tag ),
				! empty( $title_url ) ? $title_element_with_link : $title_safe ) : '';

		// Check if sub_title exists in attrs structure
		$sub_title_from_attrs = isset( $attrs['sub_title']['innerContent']['desktop']['value'] )
			? $attrs['sub_title']['innerContent']['desktop']['value']
			: null;

		$sub_title_value = $sub_title_from_attrs !== null ? $sub_title_from_attrs : '';
		$sub_title_value_trimmed = trim( $sub_title_value );
		$is_sub_title_empty = empty( $sub_title_value_trimmed );

		$sub_title_html = ! $is_sub_title_empty ?
			sprintf( '<%1$s class="df_ab_blurb_sub_title">%2$s</%1$s>',
				et_pb_process_header_level( $sub_title_level, $sub_title_tag ),
				wp_kses_post( $sub_title_value ) ) : '';
		$content        = $this->props['content'] !== '' ?
			sprintf( '<div class="df_ab_blurb_description">%1$s</div>', $this->props['content'] ) : '';

		$badge_text_1_html = ( $this->props['badge_enable'] === 'on' && $this->props['badge_text_1'] !== '' ) ?
			sprintf( '<span class="badge_text_1">%1$s</span>', $this->props['badge_text_1'] ) : '';

		$badge_text_2_html = ( $this->props['badge_enable'] === 'on' && $this->props['badge_text_2'] !== '' ) ?
			sprintf( '<span class="badge_text_2">%1$s</span>', $this->props['badge_text_2'] ) : '';

		$badge_text_html = ( $this->props['badge'] !== '' && $this->props['badge_icon_enable'] !== 'on' ) ?
			sprintf( '<span class="badge_text_wrapper">
										%1$s
										%2$s
								</span>
								', $badge_text_1_html, $badge_text_2_html ) : '';

		$badge_html = ( $this->props['badge_enable'] === 'on' ) ?
			sprintf( '<div class="df_ab_blurb_badge_wrapper">
								<div class="df_ab_blurb_badge">
										%2$s
										%1$s
								</div>
						</div>', $badge_text_html, $this->df_render_badge_icon() ) : '';

//		$this->additional_css_styles( $render_slug );

		// filter for images


//		if ( array_key_exists( 'image', $this->advanced_fields ) && array_key_exists( 'css', $this->advanced_fields['image'] ) ) {
//			$this->add_classname( $this->generate_css_filters(
//				$render_slug,
//				'child_',
//				self::$data_utils->array_get( $this->advanced_fields['image']['css'], 'main', '%%order_class%%' )
//			) );
//		}

		$placement_class      = ( $this->props['image_placement'] !== '' && $this->props['blurb_icon_enable'] === 'off' ) ? 'placement_image_' . $this->props['image_placement'] : 'placement_icon_' . $this->props['image_placement'];
		$icon_available_class = ( $this->props['blurb_icon_enable'] === 'on' ) ? 'icon' : 'image';

		$image_html = sprintf( '<div class="df_ab_blurb_image %3$s %2$s">%1$s</div>', $this->df_render_image_icon(), $placement_class, $icon_available_class );

		if ( 'outside' !== $this->props['image_icon_container_position'] ) {
			$html_code = '<div class="et_pb_module_inner"><div class="df_ab_blurb_container"> <div class="df_ab_blurb_content_container">%2$s %3$s %4$s %1$s %5$s %6$s</div></div></div>';
		} else {
			$html_code = '<div class="et_pb_module_inner"><div class="df_ab_blurb_container"> %2$s<div class="df_ab_blurb_content_container"> %3$s %4$s %1$s %5$s %6$s</div></div></div>';
		}

		// Button: same as title — only render when text is non-empty (read from attrs)
		$button_html = $this->render_button( $attrs );

		return sprintf( $html_code, $content, $image_html, $title_html, $sub_title_html, $button_html, $badge_html );
	}
}
