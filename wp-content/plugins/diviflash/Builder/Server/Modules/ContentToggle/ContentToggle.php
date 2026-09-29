<?php

namespace DIFL\Server\Modules\ContentToggle;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\ModuleElements\ModuleElements;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use DIFL\Server\Traits\ModuleUtil;


class ContentToggle implements DependencyInterface {
	use ModuleUtil;
	use HasProps;
	use StyleUtil;

	protected $config_file = 'ContentToggle';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/contentswitcher' )->customCssFields;
	}

	/**
	 * Render image for the front image
	 *
	 * @param String $key
	 *
	 * @return HTML | markup for the image
	 */
	// public function df_render_icon( $key = '', $ad_class = '' ) {
	// 	if ( isset( $this->props[ $key . '_use_icon' ] ) && $this->props[ $key . '_use_icon' ] === 'on' ) {
	// 		return sprintf( '<div class="df-cs-icon-wrapper">
    //                 <span class="et-pb-icon %2$s">%1$s</span>
    //             </div>',
	// 			isset( $this->props[ $key . '_font_icon' ] ) && $this->props[ $key . '_font_icon' ] !== '' ?
	// 				esc_attr( et_pb_process_font_icon( $this->props[ $key . '_font_icon' ] ) ) : '5',
	// 			$ad_class
	// 		);
	// 	} else if ( isset( $this->props[ $key . '_image' ] ) && $this->props[ $key . '_image' ] !== '' ) {

	// 		$image_alt = $this->props[ $key . '_alt_text' ] !== '' ? $this->props[ $key . '_alt_text' ] : df_image_alt_by_url( $this->props[ $key . '_image' ] );

	// 		return sprintf( '<div class="df-cs-icon-wrapper">
    //                 <img alt="%2$s" src="%1$s" />
    //             </div>',
	// 			esc_attr( $this->props[ $key . '_image' ] ),
	// 			$image_alt
	// 		);
	// 	}
	// }

	public static function handle_library_content( $id ) {
		if ( is_array( $id ) ) {
			$id = array_values( $id )[0];
		}
		if ( empty( $id ) || 'none' === $id ) {
			return '';
		}

		$post = get_post( $id );
		if ( ! $post ) {
			return '';
		}

		return do_blocks( $post->post_content );
	}

	public static function handle_shortcode_content( $tag = '' ) {
		if ( is_array( $tag ) ) {
			$tag = array_values( $tag )[0];
		}

		$tag       = str_replace( '\"', '', $tag );
		$shortcode = html_entity_decode( $tag );

		return sprintf( '<div>%1$s</div>', do_shortcode( $shortcode ) );
	}

	protected function sanitize_content( $content ) {
		return preg_replace( '/^<\/p>(.*)<p>/s', '$1', $content );
	}

	// public function get_arrow_border_poperty( $placement ) {
	// 	switch ( $placement ) {
	// 		case 'arrow-left':
	// 			$test = 'border-right-color';
	// 			break;
	// 		case 'arrow-top':
	// 			$test = 'border-bottom-color';
	// 			break;
	// 		case 'arrow-right':
	// 			$test = 'border-left-color';
	// 			break;
	// 		default:
	// 			$test = 'border-top-color';

	// 	}

	// 	return $test;
	// }

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = $this->get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';
		$arrow_border_props = [
			'arrow-left'   => 'border-right-color',
			'arrow-top'    => 'border-bottom-color',
			'arrow-right'  => 'border-left-color',
			'arrow-bottom' => 'border-top-color',
		];

		$arrow_translate_props = [
			'arrow-top'    => 'translateX',
			'arrow-bottom' => 'translateX',
			'arrow-left'   => 'translateY',
			'arrow-right'  => 'translateY',
		];
		$styles                = [];

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} label.df-cs-switch.df-input-label",
			'attr'                => $attrs['switcher_control_size']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) {
				$size = $props['attrValue'] ?? $this->props['switcher_control_size'] ?? '18px';
				return 'font-size: ' . $size . ';';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-icon-wrapper .df-primary-label-icon",
			'attr'                => $attrs['primary_title_icon']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				$icon = $props['attrValue']['primary_title_font_icon'] ?? $this->props['primary_title_font_icon'];
				return self::get_font_style( $icon );
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selectors' => [
				'desktop' => [
					'value' => "{$orderClass} .df-cs-icon-wrapper .df-primary-label-icon",
					'hover'  => "{$orderClass} .df-cs-switch.primary:hover .df-cs-icon-wrapper .df-primary-label-icon, {$orderClass} .df-cs-button.primary:hover .df-cs-icon-wrapper .df-primary-label-icon",
				],
			],
			'attr'      => $attrs['primary_title_icon_color']['innerContent'] ?? [],
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cs-icon-wrapper .df-primary-label-icon",
			'attr'     => $attrs['primary_title_icon_size']['innerContent'] ?? [],
			'property' => 'font-size',
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-icon-wrapper .df-secondary-label-icon",
			'attr'                => $attrs['secondary_title_icon']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				$icon = $props['attrValue']['secondary_title_font_icon'] ?? $this->props['secondary_title_font_icon'];
				return self::get_font_style( $icon );
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selectors' => [
				'desktop' => [
					'value' => "{$orderClass} .df-cs-icon-wrapper .df-secondary-label-icon",
					'hover'  => "{$orderClass} .df-cs-switch.secondary:hover .df-cs-icon-wrapper .df-secondary-label-icon, {$orderClass} .df-cs-button.secondary:hover .df-cs-icon-wrapper .df-secondary-label-icon",
				],
			],
			'attr'      => $attrs['secondary_title_icon_color']['innerContent'] ?? [],
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector' => "{$orderClass} .df-cs-icon-wrapper .df-secondary-label-icon",
			'attr'     => $attrs['secondary_title_icon_size']['innerContent'] ?? [],
			'property' => 'font-size',
		] );

		if ( $this->props['enable_active_icon_color'] === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selectors' => [
					'desktop' => [
						'value' => "{$orderClass} .df-cs-switch.active .df-cs-icon-wrapper .et-pb-icon, {$orderClass} .df-cs-button.active .df-cs-icon-wrapper .et-pb-icon",
						'hover'  => "{$orderClass} .df-cs-switch.active:hover .df-cs-icon-wrapper .et-pb-icon, {$orderClass} .df-cs-button.active:hover .df-cs-icon-wrapper .et-pb-icon",
					],
				],
				'attr'      => $attrs['active_icon_color']['innerContent'] ?? [],
				'property'  => 'color',
				'important' => true,
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-switch-wrapper",
			'attr'                => $attrs['switcher_bar']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				$alignment = $props['attrValue']['switcher_alignment'] ?? $this->props['switcher_alignment'] ?? 'flex-start';
				return 'justify-content: ' . $alignment . ';';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-switch-wrapper",
			'attr'                => $attrs['switcher_bar']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				$width = $props['attrValue']['switcher_bar_width'] ?? $this->props['switcher_bar_width'] ?? '100%';
				return 'width: ' . $width . ';';
			},
		] );

		// Overflow hidden when content has border radius.
		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-content-section",
			'attr'                => $attrs['content']['decoration']['border'] ?? [],
			'declarationFunction' => function ( $props ) {
				if ( ! empty( $props['attrValue']['radius'] ) ) {
					return 'overflow: hidden !important;';
				}
				return '';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-switch-wrapper .df-cs-switch.primary, {$orderClass} .df-cs-switch-wrapper .df-cs-button:not(:last-of-type)",
			'attr'                => $attrs['switcher_bar']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				return 'margin-right: ' . ($props['attrValue']['title_spacing'] ?? $this->props['title_spacing']) . ';';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-switch-wrapper .df-cs-switch.secondary",
			'attr'                => $attrs['switcher_bar']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				return 'margin-left: ' . ($props['attrValue']['title_spacing'] ?? $this->props['title_spacing']) . ';';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-input-label .df-cs-slider:before",
			'attr'                => $attrs['normal_control_color']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				return 'background-color: ' . $props['attrValue'] . ';';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-input-label input:checked+.df-cs-slider:before",
			'attr'                => $attrs['active_control_color']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				return 'background-color: ' . $props['attrValue'] . ';';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-primary-badge",
			'attr'                => $attrs['primary_badge']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				$val = $props['attrValue']['primary_badge_bg'] ?? $this->props['primary_badge_bg'] ?? '';
				return $val !== '' ? 'background: ' . $val . ';' : '';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df-cs-secondary-badge",
			'attr'                => $attrs['secondary_badge']['innerContent'] ?? [],
			'declarationFunction' => function ( $props ) {
				$val = $props['attrValue']['secondary_badge_bg'] ?? $this->props['secondary_badge_bg'] ?? '';
				return $val !== '' ? 'background: ' . $val . ';' : '';
			},
		] );

		if ( $this->props['primary_badge_position'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-primary-badge",
				'attr'                => $attrs['primary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) {
					$val = $props['attrValue']['primary_badge_position'] ?? $this->props['primary_badge_position'] ?? '';
					return $val !== '' ? 'left: ' . $val . ';' : '';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-primary-badge",
				'attr'                => $attrs['primary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) {
					$val = $props['attrValue']['primary_badge_top_position'] ?? $this->props['primary_badge_top_position'] ?? '';
					return $val !== '' ? 'top: ' . $val . ';' : '';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-primary-badge.{$this->props['primary_badge_arrow_placement']}::after",
				'attr'                => $attrs['primary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) {
					$placement = $this->props['primary_badge_arrow_placement'];
					$pos      = $props['attrValue']['primary_badge_arrow_position'] ?? $this->props['primary_badge_arrow_position'] ?? '';
					if ( $pos === '' ) {
						return '';
					}
					$prop = ( $placement === 'arrow-bottom' || $placement === 'arrow-top' ) ? 'left' : 'bottom';
					return $prop . ': ' . $pos . ';';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-primary-badge.{$this->props['primary_badge_arrow_placement']}::after",
				'attr'                => $attrs['primary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) {
					$val = $props['attrValue']['primary_badge_arrow_size'] ?? $this->props['primary_badge_arrow_size'] ?? '';
					return $val !== '' ? 'border-width: ' . $val . ';' : '';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-primary-badge.{$this->props['primary_badge_arrow_placement']}::after",
				'attr'                => $attrs['primary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) use ( $arrow_border_props ) {
					$placement = $this->props['primary_badge_arrow_placement'];
					$color    = $props['attrValue']['primary_badge_arrow_color'] ?? $this->props['primary_badge_arrow_color'] ?? '';
					return $color !== '' ? $arrow_border_props[ $placement ] . ': ' . $color . ';' : '';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-primary-badge.{$this->props['primary_badge_arrow_placement']}::after",
				'attr'                => $attrs['primary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) use ( $arrow_translate_props ) {
					$placement     = $this->props['primary_badge_arrow_placement'];
					$arrow_pos    = $props['attrValue']['primary_badge_arrow_position'] ?? $this->props['primary_badge_arrow_position'] ?? '';
					if ( $arrow_pos === '' ) {
						return '';
					}
					$transform_val = ( $placement === 'arrow-bottom' || $placement === 'arrow-top' )
						? '-' . $arrow_pos
						: $arrow_pos;
					return 'transform: ' . $arrow_translate_props[ $placement ] . '(' . $transform_val . ') !important;';
				},
			] );
		}

		if ( $this->props['enable_secondary_badge'] === 'on' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-secondary-badge",
				'attr'                => $attrs['secondary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) {
					$val = $props['attrValue']['secondary_badge_position'] ?? $this->props['secondary_badge_position'] ?? '';
					return $val !== '' ? 'left: ' . $val . ';' : '';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-secondary-badge",
				'attr'                => $attrs['secondary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) {
					$val = $props['attrValue']['secondary_badge_top_position'] ?? $this->props['secondary_badge_top_position'] ?? '';
					return $val !== '' ? 'top: ' . $val . ';' : '';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-secondary-badge.{$this->props['secondary_badge_arrow_placement']}::after",
				'attr'                => $attrs['secondary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) {
					$placement = $this->props['secondary_badge_arrow_placement'];
					$pos      = $props['attrValue']['secondary_badge_arrow_position'] ?? $this->props['secondary_badge_arrow_position'] ?? '';
					if ( $pos === '' ) {
						return '';
					}
					$prop = ( $placement === 'arrow-bottom' || $placement === 'arrow-top' ) ? 'left' : 'bottom';
					return $prop . ': ' . $pos . ';';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-secondary-badge.{$this->props['secondary_badge_arrow_placement']}::after",
				'attr'                => $attrs['secondary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) {
					$val = $props['attrValue']['secondary_badge_arrow_size'] ?? $this->props['secondary_badge_arrow_size'] ?? '';
					return $val !== '' ? 'border-width: ' . $val . ';' : '';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-secondary-badge.{$this->props['secondary_badge_arrow_placement']}::after",
				'attr'                => $attrs['secondary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) use ( $arrow_border_props ) {
					$placement = $this->props['secondary_badge_arrow_placement'];
					$color    = $props['attrValue']['secondary_badge_arrow_color'] ?? $this->props['secondary_badge_arrow_color'] ?? '';
					return $color !== '' ? $arrow_border_props[ $placement ] . ': ' . $color . ';' : '';
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-secondary-badge.{$this->props['secondary_badge_arrow_placement']}::after",
				'attr'                => $attrs['secondary_badge']['innerContent'] ?? [],
				'declarationFunction' => function ( $props ) use ( $arrow_translate_props ) {
					$placement     = $this->props['secondary_badge_arrow_placement'];
					$arrow_pos    = $props['attrValue']['secondary_badge_arrow_position'] ?? $this->props['secondary_badge_arrow_position'] ?? '';
					if ( $arrow_pos === '' ) {
						return '';
					}
					$transform_val = ( $placement === 'arrow-bottom' || $placement === 'arrow-top' )
						? '-' . $arrow_pos
						: $arrow_pos;
					return 'transform: ' . $arrow_translate_props[ $placement ] . '(' . $transform_val . ') !important;';
				},
			] );
		}

		// Hide icons on mobile.
		if ( 'on' === $this->props['primary_icon_hide_on_mobile'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-switch-wrapper .primary .df-cs-icon-wrapper",
				'attr'                => $attrs['primary_title_icon']['innerContent'] ?? [],
				'atRules'  => '@media only screen and (max-width: 767px)',
				'declarationFunction' => function() {
					return 'display: none !important;';
				}
			] );
		}

		if ( 'on' === $this->props['secondary_icon_hide_on_mobile'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-switch-wrapper .secondary .df-cs-icon-wrapper",
				'attr'                => $attrs['secondary_title_icon']['innerContent'] ?? [],
				'atRules'  => '@media only screen and (max-width: 767px)',
				'declarationFunction' => function() {
					return 'display: none !important;';
				}
			] );
		}

		$content_animation = [ 'slide_left', 'slide_right', 'slide_up', 'slide_down'];
		if ( 'on' === $this->props['enable_animation'] && in_array( $this->props['content_animation_type'], $content_animation ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df-cs-content-container",
				'attr'                => $attrs['enable_animation']['innerContent'] ?? [],
				'declarationFunction' => function() {
					return 'overflow: hidden;';
				}
			] );
		}

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
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

	public function render( $attrs, $content, $block ) {
		wp_enqueue_script( 'animejs' );
		wp_register_script( 'df-content-switcher', DIFL_PUBLIC_DIR . 'js/contentSwitcher.js', [ 'jquery' ], time(), true );
		wp_enqueue_script( 'df-content-switcher' );

		$order_class = $this->order_class;

		$switch_control_type = isset( $this->props['switcher_type'] ) && $this->props['switcher_type'] !== '' ? $this->props['switcher_type'] : 'round';

		$primary_label        = isset( $this->props['primary_label'] ) && $this->props['primary_label'] !== '' ? $this->props['primary_label'] : ( $switch_control_type !== 'button' ? '' : 'Primary' );
		$secondary_label      = isset( $this->props['secondary_label'] ) && $this->props['secondary_label'] !== '' ? $this->props['secondary_label'] : ( $switch_control_type !== 'button' ? '' : 'Secondary' );
		$primary_label_icon   = ( isset( $this->props['primary_title_use_icon'] ) && $this->props['primary_title_use_icon'] === 'on' && ! empty( $this->props['primary_title_font_icon'] ) ) ? sprintf( '<div class="df-cs-icon-wrapper"><span class="et-pb-icon df-primary-label-icon">%1$s</span></div>', Utils::process_font_icon( $this->props['primary_title_font_icon'] ?? '' ) ) : '';
		$secondary_label_icon = ( isset( $this->props['secondary_title_use_icon'] ) && $this->props['secondary_title_use_icon'] === 'on' && ! empty( $this->props['secondary_title_font_icon'] ) ) ? sprintf( '<div class="df-cs-icon-wrapper"><span class="et-pb-icon df-secondary-label-icon">%1$s</span></div>', Utils::process_font_icon( $this->props['secondary_title_font_icon'] ?? '' ) ) : '';

		$primary_icon_position          = isset( $this->props['primary_icon_align'] ) && $this->props['primary_icon_align'] !== '' ? $this->props['primary_icon_align'] : 'left'; // left or center or right
		$secondary_icon_position        = isset( $this->props['secondary_icon_align'] ) && $this->props['secondary_icon_align'] !== '' ? $this->props['secondary_icon_align'] : 'left'; // left or center or right
		$primary_button_icon_position   = $switch_control_type === 'button' && isset( $this->props['primary_icon_align'] ) && $this->props['primary_icon_align'] !== '' ? $this->props['primary_icon_align'] : 'left'; // left or center or right
		$secondary_button_icon_position = $switch_control_type === 'button' && isset( $this->props['secondary_icon_align'] ) && $this->props['secondary_icon_align'] !== '' ? $this->props['secondary_icon_align'] : 'left'; // left or center or right
		$primary_content                = '';
		$secondary_content              = '';

		if ( $this->props['content_switcher_type'] === 'content_base' ) {
			$primary_content   = isset( $this->props['content'] ) && $this->props['content'] !== '' ?
				sprintf( '<div class="primary-content">%1$s</div>', do_shortcode( html_entity_decode( $this->sanitize_content( $this->props['content'] ) ) ) ) : '';
			$secondary_content = isset( $this->props['secondary_content'] ) && $this->props['secondary_content'] !== '' ?
				sprintf( '<div class="secondary-content">%1$s</div>', do_shortcode( html_entity_decode( $this->sanitize_content( $this->props['secondary_content'] ) ) ) ) : '';

		} else if ( $this->props['content_switcher_type'] === 'shortcode_base' ) {
			$primary_content   = ! empty( $this->props['shortcode_primary_content'] ) ? self::handle_shortcode_content( $this->props['shortcode_primary_content'] ) : '';
			$secondary_content = ! empty( $this->props['shortcode_secondary_content'] ) ? self::handle_shortcode_content( $this->props['shortcode_secondary_content'] ) : '';

		} else if ( $this->props['content_switcher_type'] === 'library_base' ) {
			$library_id_primary   = ! empty( $this->props['library_id_primary'] ) ? $this->props['library_id_primary'] : '';
			$library_id_secondary = ! empty( $this->props['library_id_secondary'] ) ? $this->props['library_id_secondary'] : '';
			$primary_content      = self::handle_library_content( $library_id_primary );
			$secondary_content    = self::handle_library_content( $library_id_secondary );
		}
		$primary_badge_text              = isset( $this->props['primary_badge_text'] ) && $this->props['primary_badge_text'] !== '' ? wp_kses_post( $this->props['primary_badge_text'] ) : 'Popular';
		$primary_badge_arrow_placement   = isset( $this->props['primary_badge_arrow_placement'] ) ? $this->props['primary_badge_arrow_placement'] : 'arrow-bottom';
		$primary_badge                   = $this->props['enable_primary_badge'] === 'on' ?
			sprintf( '<div class="df-cs-primary-badge %2$s">%1$s</div>', $primary_badge_text, $primary_badge_arrow_placement )
			: '';
		$secondary_badge_text            = isset( $this->props['secondary_badge_text'] ) && $this->props['secondary_badge_text'] !== '' ? wp_kses_post( $this->props['secondary_badge_text'] ) : 'Popular';
		$secondary_badge_arrow_placement = isset( $this->props['secondary_badge_arrow_placement'] ) ? $this->props['secondary_badge_arrow_placement'] : 'arrow-bottom';
		$secondary_badge                 = $this->props['enable_secondary_badge'] === 'on' ?
			sprintf( '<div class="df-cs-secondary-badge %2$s">%1$s</div>', $secondary_badge_text, $secondary_badge_arrow_placement )
			: '';
		$reverse_active        = isset( $this->props['reverse_active_state'] ) && $this->props['reverse_active_state'] === 'on';
		$primary_active_class  = $reverse_active ? '' : ' active';
		$secondary_active_class = $reverse_active ? ' active' : '';
		$checked_attr          = $reverse_active ? 'checked' : '';

		$switch_html                     = sprintf( '
                        <div class="df-cs-switch primary df-cs-icon-%6$s%10$s">
                            %8$s
                            %4$s
                            <span class="title">%2$s</span>
                        </div>

                        <label class="df-cs-switch df-input-label">
                            <input class="df-cs-toggle-switch" type="checkbox" %11$s>
                            <span class="df-cs-slider df-cs-%1$s"></span>
                        </label>

                        <div class="df-cs-switch secondary df-cs-icon-%7$s%12$s">
                            %9$s
                            %5$s
                            <span class="title">%3$s</span>
                        </div>
                    ',
			$switch_control_type,
			$primary_label,
			$secondary_label,
			$primary_label_icon,
			$secondary_label_icon,
			$primary_icon_position,
			$secondary_icon_position,
			$primary_badge,
			$secondary_badge,
			$primary_active_class,
			$checked_attr,
			$secondary_active_class
		);
		$switch_button                   = sprintf( '
                        <button class="df-cs-button primary df-cs-icon-%5$s%9$s">
                            %7$s              
                            %3$s        
                            <span class="title">%1$s</span>
                        </button>
                        <button class="df-cs-button secondary df-cs-icon-%6$s%10$s">
                            %8$s
                            %4$s
                            <span class="title">%2$s</span>
                        </button>',
			$primary_label,
			$secondary_label,
			$primary_label_icon,
			$secondary_label_icon,
			$primary_button_icon_position,
			$secondary_button_icon_position,
			$primary_badge,
			$secondary_badge,
			$primary_active_class,
			$secondary_active_class
		);

		$switch_content = sprintf( '
                        <div class="df-cs-content-section primary%3$s">
                            %1$s
                        </div>

                        <div class="df-cs-content-section secondary%4$s">
                            %2$s
                        </div>',
			$primary_content,
			$secondary_content,
			$primary_active_class,
			$secondary_active_class
		);

		// Content animation
		$content_animation          = isset( $this->props['content_animation_type'] ) ? $this->props['content_animation_type'] : ( isset( $this->props['content_animation'] ) ? $this->props['content_animation'] : 'fade_in' );
		$content_animation_duration = isset( $this->props['content_animation_duration'] ) ? $this->props['content_animation_duration'] : '200ms';

		$class_base_selector = [
			'content_switcher_type'      => isset( $this->props['content_switcher_type'] ) ? $this->props['content_switcher_type'] : '',
			'primary_content_selector'   => isset( $this->props['primary_content_selector'] ) ? $this->props['primary_content_selector'] : '',
			'secondary_content_selector' => isset( $this->props['secondary_content_selector'] ) ? $this->props['secondary_content_selector'] : '',
			'module_class'               => $order_class,
			'enable_animation'           => isset( $this->props['enable_animation'] ) ? $this->props['enable_animation'] : 'on',
			'content_animation'          => $content_animation,
			'content_animation_duration' => $content_animation_duration,
			'reverse_active_state'       => $reverse_active,
		];

		return sprintf( '<div class="et_pb_module_inner"><div class="df-content-switcher-wrapper df-cs-design-%2$s" data-settings=\'%4$s\'>
                            <div class="df-cs-switch-container">
                                <div class="df-cs-switch-wrapper">
                                    %3$s
                                </div>
                            </div>
                                
                            <div class="df-cs-content-container">
                                <div class="df-cs-content-wrapper">
                                    %1$s
                                </div>
                           	</div>
                        </div></div>',
			$this->props['content_switcher_type'] !== 'class_base' ? $switch_content : '',
			$switch_control_type,
			$switch_control_type === 'button' ? $switch_button : $switch_html,
			wp_json_encode( $class_base_selector )
		);
	}

}