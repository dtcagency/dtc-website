<?php

namespace DIFL\Server\Modules\AdvancedTab;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class AdvancedTab implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'AdvancedTab';

	protected $default_active = '';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/advancedtab' )->customCssFields;
	}

	/**
	 * Render tab navigation
	 *
	 */
	public function df_at_render_nav( $attrs ) {
		global $df_at_data;
		$nav_placement = $this->props['nav_placement'] ?? 'flex_top';
		$placement     = [
			'flex_top'    => 'top',
			'flex_bottom' => 'bottom',
			'flex_left'   => 'left',
			'flex_right'  => 'right',
		];
		$arrows        = [
			'flex_top'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 255 127.5" width="30px" height="auto"><g><polygon points="0 0 127.5 127.5 255 0 0 0"/></g></svg>',
			'flex_bottom' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 255 127.5" width="30px" height="auto"><g><polygon points="255 127.5 127.5 0 0 127.5 255 127.5"/></g></svg>',
			'flex_left'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.5 255" height="30px" width="auto"><g><polygon points="0 255 127.5 127.5 0 0 0 255"/></g></svg>',
			'flex_right'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.5 255" height="30px" width="auto"><g><polygon points="127.5 0 0 127.5 127.5 255 127.5 0"/></g></svg>',
		];
		$arrow         = ( $this->props['use_active_arrow'] === 'on' && isset( $arrows[ $nav_placement ] ) )
			? $arrows[ $nav_placement ]
			: '';
		$arrow_class   = 'arrow_' . ( $placement[ $nav_placement ] ?? $nav_placement );

		$icon_desktop = $this->props['icon_placement'] ?? 'flex_top';
		$icon_tablet  = $this->props['icon_placement_tablet'] ?? $icon_desktop;
		$icon_phone   = $this->props['icon_placement_phone'] ?? $icon_tablet;

		$desktop = $placement[ $icon_desktop ] ?? $icon_desktop;
		$tablet  = $placement[ $icon_tablet ] ?? $icon_tablet;
		$phone   = $placement[ $icon_phone ] ?? $icon_phone;

		$lr = 'lr_' . $desktop;
		$md = 'md_' . $tablet;
		$sm = 'sm_' . $phone;

		$icon_placement_class = sprintf( '%1$s %2$s %3$s', $lr, $md, $sm );
		$tabs                 = '';
		$title_level          = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? 'h4';
		foreach ( $df_at_data as $class => $data ) {
			if ( 'on' === $data['default_active'] ) {
				$this->default_active = $class;
			}
			$title    = $data['title'] !== '' ?
				sprintf( '<%2$s class="df_at_title">%1$s</%2$s>', esc_html( $data['title'] ), $title_level ) : '';
			$subtitle = $data['subtitle'] !== '' ?
				sprintf( '<div class="df_at_subtitle">%1$s</div>', $data['subtitle'] ) : '';

			$tab_image_alt_text = '';

			if ( 'on' !== $data['use_icon'] && '' !== $data['tab_image'] ) {
				$tab_image_id       = attachment_url_to_postid( $data['tab_image'] );
				$tab_image_alt_text = get_post_meta( $tab_image_id, '_wp_attachment_image_alt', true );
			}

			$image = $data['use_icon'] !== 'on' && $data['tab_image'] !== '' ?
				sprintf( '<span class="at_image_wrap"><img src="%1$s" alt="%2$s"/></span>', $data['tab_image'], esc_attr( $tab_image_alt_text ) ) : '';

			$icon = isset( $data['use_icon'] ) && $data['use_icon'] === 'on' ?
				sprintf( '<span class="at_icon_wrap "><span class="et-pb-icon df-tab-nav-icon">%1$s</span></span>',
					isset( $data['font_icon'] ) && $data['font_icon'] !== '' ?
						$data['font_icon'] : '5'
				) : $image;

			$tabs .= sprintf( '<div data-hash="%1$s" class="%1$s df_at_nav %5$s %7$s">
                %4$s <span class="at_nav_content">%2$s %3$s</span> %6$s
            </div>', esc_attr( $class ), $title, $subtitle, $icon, $icon_placement_class, $arrow, $arrow_class );
		}
		$df_at_data = [];

		return $tabs;
	}

    /**
     * Get nav_placement attribute with independent defaults for each breakpoint.
     * Tablet and phone do NOT inherit from desktop - they default to 'flex_top' independently.
     *
     * @param array|null $innerContent The innerContent array with responsive values.
     * @return array The innerContent with independent default values for each breakpoint.
     */
    protected static function get_nav_placement_with_independent_defaults( $innerContent ) {
        $default_value = 'flex_top';

        return [
            'desktop' => [
                'value' => $innerContent['desktop']['value'] ?? $default_value,
            ],
            'tablet' => [
                'value' => $innerContent['tablet']['value'] ?? $default_value,
            ],
            'phone' => [
                'value' => $innerContent['phone']['value'] ?? $default_value,
            ],
        ];
    }

    /**
     * Get nav_placement value for a specific breakpoint with independent defaults.
     *
     * @param array|null $innerContent The innerContent array with responsive values.
     * @param string $breakpoint The breakpoint to get the value for.
     * @return string The nav_placement value for the specified breakpoint.
     */
    protected static function get_nav_placement_value( $innerContent, $breakpoint ) {
        $default_value = 'flex_top';
        return $innerContent[$breakpoint]['value'] ?? $default_value;
    }

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';

        // Get nav_placement attr with independent defaults
        $nav_placement_attr = self::get_nav_placement_with_independent_defaults( $attrs['nav_placement']['innerContent'] ?? [] );

		$vertical_align = [
			'flex_start'  => '',
			'flex_center' => 'margin-top: auto; margin-bottom: auto;',
			'flex_end'    => 'margin-top:auto; margin-bottom:0;',
		];

		$translate_values = [
			'flex_left'   => 'top: 50%; transform: translateX(0px) translateY(-50%);',
			'flex_right'  => 'top: 50%; transform: translateX(-100%) translateY(-50%);',
			'flex_bottom' => 'left: 50%; transform: translateY(-100%) translateX(-50%);',
			'flex_top'    => 'left: 50%; transform: translateX(-50%);',
		];

		$layout_map = [
			'center'        => 'center',
			'top_left'      => 'top left',
			'top_center'    => 'top center',
			'center_top'    => 'center top',
			'top'           => 'top',
			'top_right'     => 'top right',
			'right'         => 'right',
			'center_right'  => 'center right',
			'bottom_right'  => 'bottom right',
			'bottom'        => 'bottom',
			'bottom_center' => 'bottom center',
			'bottom_left'   => 'bottom left',
			'left'          => 'left',
			'center_left'   => 'center left',
			'no_repeat'     => 'no-repeat',
			'repeat'        => 'repeat',
			'repeat_x'      => 'repeat-x',
			'repeat_y'      => 'repeat-y',
			'space'         => 'space',
			'round'         => 'round',
			'cover'         => 'cover',
			'fit'           => 'contain',
			'actual_size'   => 'initial',
			'flex_left'     => 'row',
			'flex_top'      => 'column',
			'flex_right'    => 'row-reverse',
			'flex_bottom'   => 'column-reverse',
			'flex_start'    => 'flex-start',
			'flex_end'      => 'flex-end',
			'flex_center'   => 'center',
		];

		$content_vertical_align = [
			'flex_start'  => 'flex-start',
			'flex_center' => 'center',
			'flex_end'    => 'flex-end',
		];

		$nav_placement = isset( $all_props['nav_placement'] ) ? $all_props['nav_placement'] : 'flex_top';

		$styles = [];

		if ( 'on' === $this->props['use_active_arrow'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df_at_nav",
				'attr'                => $attrs['use_active_arrow']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'overflow: visible !important;';
				},
			] );
		}

		// Icon align - conditional based on icon_placement
		$icon_placement = $this->props['icon_placement'] ?? 'column';
		if ( in_array( $icon_placement, [ 'row', 'row-reverse' ] ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df_at_nav",
				'attr'                => $attrs['icon_align']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					$icon_align = $props['attrValue'] ?? 'left';
					return "align-items: center; justify-content: {$icon_align};";
				},
			] );
		} else {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .at_icon_wrap, {$orderClass} .at_image_wrap",
				'attr'                => $attrs['icon_align']['innerContent'] ?? null,
				'declarationFunction' => function ( $props ) {
					$icon_align = $props['attrValue'] ?? 'left';
					return "width: 100% !important; text-align: {$icon_align};";
				},
			] );
		}

		// Additional style for row-reverse icon placement
		if ( 'row-reverse' === $icon_placement ) {
			$icon_align_value = $this->props['icon_align'] ?? 'left';
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df_at_nav",
				'attr'                => $attrs['icon_placement']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $icon_align_value ) {
					return "justify-content: {$icon_align_value} !important;";
				},
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_at_nav svg, {$orderClass} .df_at_nav:hover svg",
			'attr'      => $attrs['active_arrow_color']['innerContent'] ?? null,
			'property'  => 'fill',
			'important' => true,
		] );

		// Arrow size - responsive based on nav_placement (independent defaults)
		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_at_nav svg",
			'attr'                => $attrs['active_arrow_size']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) use ( $attrs ) {
				$breakpoint = $props['breakpoint'] ?? 'desktop';
				$placement = self::get_nav_placement_value( $attrs['nav_placement']['innerContent'] ?? [], $breakpoint );
				$arrowSize = $props['attrValue'] ?? '10px';
				if ( in_array( $placement, [ 'flex_left', 'flex_right' ] ) ) {
					return "height: {$arrowSize}; width: auto;";
				}
				return "width: {$arrowSize}; height: auto;";
			},
		] );

		// Arrow align center - responsive (independent nav_placement defaults)
		$common_styles[] = CommonStyle::style( [
			'selector'    => "{$orderClass} .df_at_nav svg",
			'attr'        => $attrs['arrow_align']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) use ( $attrs, $translate_values ) {
				$arrowAlign = $props['attrValue'] ?? 'start';
				if ( $arrowAlign !== 'center' ) return '';
				$breakpoint = $props['breakpoint'] ?? 'desktop';
				$placement = self::get_nav_placement_value( $attrs['nav_placement']['innerContent'] ?? [], $breakpoint );
				return $translate_values[ $placement ] ?? '';
			},
		] );

		// Arrow align end - responsive based on nav_placement (independent defaults)
		$common_styles[] = CommonStyle::style( [
			'selector'    => "{$orderClass} .df_at_nav svg",
			'attr'        => $attrs['arrow_align']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) use ( $attrs ) {
				$arrowAlign = $props['attrValue'] ?? 'start';
				if ( $arrowAlign !== 'end' ) return '';
				$breakpoint = $props['breakpoint'] ?? 'desktop';
				$placement = self::get_nav_placement_value( $attrs['nav_placement']['innerContent'] ?? [], $breakpoint );
				if ( in_array( $placement, [ 'flex_left', 'flex_right' ] ) ) {
					return 'top: auto; bottom: 0px;';
				}
				return 'left: auto; right: 0;';
			},
		] );

		// Nav align auto - responsive based on nav_placement (independent defaults)
		$common_styles[] = CommonStyle::style( [
			'selector'    => "{$orderClass} .df_at_nav_container",
			'attr'        => $attrs['nav_align']['innerContent'] ?? null,
			'declarationFunction' => function ( $props ) use ( $attrs ) {
				$navAlign = $props['attrValue'] ?? 'start';
				if ( $navAlign !== 'auto' ) return '';
				$breakpoint = $props['breakpoint'] ?? 'desktop';
				$placement = self::get_nav_placement_value( $attrs['nav_placement']['innerContent'] ?? [], $breakpoint );
				if ( in_array( $placement, [ 'flex_left', 'flex_right' ] ) ) {
					return 'height: 100%; width: auto;';
				}
				return '';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_at_nav .et-pb-icon, {$orderClass} .df_at_nav:hover .et-pb-icon",
			'attr'      => $attrs['icon_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_at_nav.df_at_nav_active .et-pb-icon, {$orderClass} .df_at_nav.df_at_nav_active:hover .et-pb-icon",
			'attr'      => $attrs['icon_color_active']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_at_nav .et-pb-icon",
			'attr'      => $attrs['icon_size']['innerContent'] ?? null,
			'property'  => 'font-size',
		] );

		if ( '0px' !== $this->props['icon_size_active'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_at_nav.df_at_nav_active .et-pb-icon",
				'attr'      => $attrs['icon_size_active']['innerContent'] ?? null,
				'property'  => 'font-size',
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_at_nav .at_image_wrap img",
			'attr'      => $attrs['image_size']['innerContent'] ?? null,
			'property'  => 'max-width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_at_nav.df_at_nav_active .at_image_wrap img",
			'attr'      => $attrs['image_size_active']['innerContent'] ?? null,
			'property'  => 'max-width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_at_nav",
			'attr'      => $attrs['icon_placement']['innerContent'] ?? null,
			'property'  => 'flex-direction',
			'important' => true,
		] );

		if ( 'on' === $this->props['use_nav_width'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_at_nav",
				'attr'      => $attrs['nav_min_width']['innerContent'] ?? null,
				'property'  => 'min-width',
				'important' => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_at_nav",
				'attr'      => $attrs['nav_max_width']['innerContent'] ?? null,
				'property'  => 'max-width',
				'important' => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_at_nav_container .df_at_nav",
				'attr'      => $attrs['nav_height']['innerContent'] ?? null,
				'property'  => 'height',
				'important' => true,
			] );
		}

		if ( 'start' !== $this->props['nav_align'] ) {

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_at_nav_wrap",
				'attr'      => $attrs['nav_align']['innerContent'] ?? null,
				'property'  => 'align-self',
				'important' => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_at_nav_wrap",
				'attr'      => $attrs['nav_align']['innerContent'] ?? null,
				'property'  => 'justify-content',
				'important' => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_at_nav_container",
				'attr'      => $attrs['nav_align']['innerContent'] ?? null,
				'property'  => 'justify-content',
				'important' => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .df_at_nav_container",
				'attr'      => $attrs['nav_align']['innerContent'] ?? null,
				'property'  => 'align-items',
				'important' => true,
			] );
		}

		// Nav placement with independent responsive defaults (not inherited from desktop)
		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_at_container",
			'attr'                => $nav_placement_attr,
			'declarationFunction' => function ( $props ) use ( $layout_map ) {
				$placement = $props['attrValue'] ?? 'flex_top';
				$value = $layout_map[ $placement ] ?? 'column';
				return "flex-direction: $value;";
			},
		] );

		// Styles based on nav_placement - responsive (with independent defaults)
		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_at_nav_container",
			'attr'                => $nav_placement_attr,
			'declarationFunction' => function ( $props ) {
				$placement = $props['attrValue'] ?? 'flex_top';
				if ( in_array( $placement, [ 'flex_left', 'flex_right' ] ) ) {
					return 'flex-direction: column;';
				}
				// For top/bottom, ensure nav items are in a row
				return 'flex-direction: row;';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_at_nav_wrap",
			'attr'                => $nav_placement_attr,
			'declarationFunction' => function ( $props ) use ( $attrs ) {
				$placement = $props['attrValue'] ?? 'flex_top';
				$breakpoint = $props['breakpoint'] ?? 'desktop';
				if ( in_array( $placement, [ 'flex_left', 'flex_right' ] ) ) {
					$nav_width = $attrs['nav_container_width']['innerContent'][$breakpoint]['value']
						?? $attrs['nav_container_width']['innerContent']['desktop']['value']
						?? '20%';
					return "width: {$nav_width};";
				}
				// For top/bottom, reset to full width
				return 'width: 100%;';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_at_all_tabs_wrap",
			'attr'                => $nav_placement_attr,
			'declarationFunction' => function ( $props ) use ( $attrs ) {
				$placement = $props['attrValue'] ?? 'flex_top';
				$breakpoint = $props['breakpoint'] ?? 'desktop';
				if ( in_array( $placement, [ 'flex_left', 'flex_right' ] ) ) {
					$nav_width = $attrs['nav_container_width']['innerContent'][$breakpoint]['value']
						?? $attrs['nav_container_width']['innerContent']['desktop']['value']
						?? '20%';
					return "width: calc(100% - {$nav_width});";
				}
				// For top/bottom, reset to full width
				return 'width: 100%;';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .et_pb_module",
			'attr'                => $nav_placement_attr,
			'declarationFunction' => function ( $props ) {
				$placement = $props['attrValue'] ?? 'flex_top';
				if ( in_array( $placement, [ 'flex_left', 'flex_right' ] ) ) {
					return 'height: 100%;';
				}
				// For top/bottom, reset height to auto
				return 'height: auto;';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .df_ati_container",
			'attr'                => $nav_placement_attr,
			'declarationFunction' => function ( $props ) use ( $vertical_align, $layout_map, $attrs ) {
				$placement = $props['attrValue'] ?? 'flex_top';
				$breakpoint = $props['breakpoint'] ?? 'desktop';
				$content_align = $attrs['content_vertical_align']['innerContent'][$breakpoint]['value']
					?? $attrs['content_vertical_align']['innerContent']['desktop']['value']
					?? 'flex_start';
				if ( in_array( $placement, [ 'flex_left', 'flex_right' ] ) ) {
					return $vertical_align[ $content_align ] ?? '';
				}
				return "align-items: " . ( $layout_map[ $content_align ] ?? 'flex-start' ) . ";";
			},
		] );

		if ( 'on' === $this->props['use_nav_width'] ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df_at_nav_container",
				'attr'                => $attrs['use_nav_width']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'flex-wrap: wrap!important;';
				},
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_at_button_wrapper",
			'attr'      => $attrs['button_align']['innerContent'] ?? null,
			'property'  => 'text-align',
			'important' => true,
		] );

		// Only apply overflow hidden if nav container border width is greater than 0px
		$has_border_width = false;
		$border = $attrs['nav_border_shadow']['decoration']['border'] ?? [];
		$breakpoints = [ 'desktop', 'tablet', 'phone' ];
		$sides = [ 'all', 'top', 'right', 'bottom', 'left' ];
		foreach ( $breakpoints as $bp ) {
			$border_styles = $border[ $bp ]['value']['styles'] ?? [];
			foreach ( $sides as $side ) {
				$width = $border_styles[ $side ]['width'] ?? '';
				if ( ! empty( $width ) && floatval( $width ) > 0 ) {
					$has_border_width = true;
					break 2;
				}
			}
		}
		if ( $has_border_width ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .df_at_nav_wrap",
				'attr'                => $attrs['nav_border_shadow']['decoration']['border'] ?? null,
				'declarationFunction' => function () {
					return 'overflow: hidden !important;';
				},
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

	public function render( $attrs, $content, $render_slug ) {
		if ( $content === '' ) {
			return sprintf(
				'<h2 style="background:#eee; padding: 10px 20px;">Please <strong>Add New Tab Item.</strong></h2>'
			);
		}
		$df_at_contents = et_core_sanitized_previously( $content );
		wp_enqueue_script( 'animejs' );
		wp_enqueue_script( 'sticky-script' );
		wp_enqueue_script( 'df-tabs' );

		$df_tab_nav       = $this->df_at_render_nav( $attrs );
		$sticky_nav_class = $this->props['use_sticky_nav'] === 'on' ? ' df_has_sticky_nav' : '';
		$order_class      = $this->order_class;

		$sticky_distance        = isset( $this->props['sticky_nav_distance'] ) ?
			$this->props['sticky_nav_distance'] : '55px';
		$sticky_distance_tablet = isset( $this->props['sticky_nav_distance_tablet'] ) && $this->props['sticky_nav_distance_tablet'] !== '' ?
			$this->props['sticky_nav_distance_tablet'] : $sticky_distance;
		$sticky_distance_phone  = isset( $this->props['sticky_nav_distance_phone'] ) ?
			$this->props['sticky_nav_distance_phone'] : $sticky_distance_tablet;

		$data                 = [
			'tab_event_type'         => $this->props['tab_event_type'],
			'use_sticky_nav'         => $this->props['use_sticky_nav'],
			'use_scroll_to_content'  => $this->props['use_scroll_to_content'],
			'sticky_distance'        => $sticky_distance,
			'sticky_distance_tablet' => $sticky_distance_tablet,
			'sticky_distance_phone'  => $sticky_distance_phone,
			'tab_animation'          => $this->props['tab_animation'],
			'extra_space'            => is_admin_bar_showing() ? true : false,
			'turn_off_sticky'        => $this->props['turn_off_sticky'],
			'module_class'           => $order_class,
			'animation_duration'     => $this->props['df_animation_duration'],
			'default_active'         => $this->default_active,
		];
		$this->default_active = '';

//		$this->additional_css_styles( $render_slug );

		return sprintf( '<div class="df_at_container%4$s" data-settings=\'%3$s\'>
                <div class="df_at_nav_wrap"><div class="df_at_nav_container">%2$s</div></div>
                <div class="df_at_all_tabs_wrap"><div class="df_at_all_tabs et_flex_module">%1$s</div></div>
            </div>',
			$df_at_contents,
			$df_tab_nav,
			wp_json_encode( $data ),
			$sticky_nav_class
		);
	}

	/**
	 * Icon wrapper width
	 *
	 * @param $render_slug
	 */
	public function icon_wrapper_width( $render_slug ) {
		$icon_wrapper_width = [
			'flex_top'    => '100%',
			'flex_bottom' => '100%',
			'flex_left'   => 'auto',
			'flex_right'  => 'auto',
		];
		$desktop            = $this->props['icon_placement'] !== '' ?
			$this->props['icon_placement'] : 'flex_top';
		$tablet             = $this->props['icon_placement_tablet'] !== '' ?
			$this->props['icon_placement_tablet'] : $desktop;
		$phone              = $this->props['icon_placement_phone'] !== '' ?
			$this->props['icon_placement_phone'] : $tablet;

		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .at_icon_wrap , %%order_class%% .at_image_wrap',
			'declaration' => sprintf( 'width:%1$s;', $icon_wrapper_width[ $desktop ] ),
		] );
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .at_icon_wrap , %%order_class%% .at_image_wrap',
			'declaration' => sprintf( 'width:%1$s;', $icon_wrapper_width[ $tablet ] ),
			'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
		] );
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .at_icon_wrap , %%order_class%% .at_image_wrap',
			'declaration' => sprintf( 'width:%1$s;', $icon_wrapper_width[ $phone ] ),
			'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
		] );
	}

	public function icon_alignment_styles( $render_slug ) {
		$align = isset( $this->props['icon_align'] ) && $this->props['icon_align'] !== '' ?
			$this->props['icon_align'] : 'left';

		$flex_style         = [
			'left'   => 'flext-start',
			'center' => 'center',
			'right'  => 'flex-end',
		];
		$flex_style_reverse = [
			'left'   => 'flex-end',
			'center' => 'center',
			'right'  => 'flext-start',
		];
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .at_icon_wrap ,%%order_class%% .at_image_wrap ',
			'declaration' => sprintf( 'text-align: %1$s;', $align ),
		] );

		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .lr_left .at_nav_content, %%order_class%% .lr_right .at_nav_content',
			'declaration' => 'width: auto;',
			'media_query' => ET_Builder_Element::get_media_query( 'min_width_981' ),
		] );
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .df_at_nav.lr_left',
			'declaration' => sprintf( 'justify-content: %1$s;', $flex_style[ $align ] ),
			'media_query' => ET_Builder_Element::get_media_query( 'min_width_981' ),
		] );
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => ' %%order_class%% .df_at_nav.lr_right',
			'declaration' => sprintf( 'justify-content: %1$s;', $flex_style_reverse[ $align ] ),
			'media_query' => ET_Builder_Element::get_media_query( 'min_width_981' ),
		] );

		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .md_left .at_nav_content, %%order_class%% .md_right .at_nav_content',
			'declaration' => 'width: auto;',
			'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
		] );
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .df_at_nav.md_left',
			'declaration' => sprintf( 'justify-content: %1$s;', $flex_style[ $align ] ),
			'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
		] );
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => ' %%order_class%% .df_at_nav.md_right',
			'declaration' => sprintf( 'justify-content: %1$s;', $flex_style_reverse[ $align ] ),
			'media_query' => ET_Builder_Element::get_media_query( 'max_width_980' ),
		] );

		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .sm_left .at_nav_content, %%order_class%% .sm_right .at_nav_content',
			'declaration' => 'width: auto;',
			'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
		] );
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .df_at_nav.sm_left',
			'declaration' => sprintf( 'justify-content: %1$s;', $flex_style[ $align ] ),
			'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
		] );
		ET_Builder_Element::set_style( $render_slug, [
			'selector'    => '%%order_class%% .df_at_nav.sm_right',
			'declaration' => sprintf( 'justify-content: %1$s;', $flex_style_reverse[ $align ] ),
			'media_query' => ET_Builder_Element::get_media_query( 'max_width_767' ),
		] );
	}

}

