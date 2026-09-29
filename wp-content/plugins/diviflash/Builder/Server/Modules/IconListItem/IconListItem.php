<?php

namespace DIFL\Server\Modules\IconListItem;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\ModuleUtils\ModuleUtils;
use ET_Builder_Module_Helper_MultiViewOptions;

class IconListItem implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'IconListItem';

	protected $module_classnames = 'et_pb_module';

	private $tooltip_css_element = '';

	/**
	 * Get CSS fields transition.
	 *
	 * Add form field options group and background image on the fields list.
	 *
	 *
	 * @since 1.0.0
	 */

	/**
	 * Wrap module's rendered output with proper module wrapper. Ensuring module has consistent
	 * wrapper output which compatible with module attribute and background insertion.
	 *
	 *
	 * @param string $output      Module's rendered output.
	 * @param string $render_slug Slug of module that is used for rendering output.
	 *
	 * @return string
	 */
	public function _render_module_wrapper( $output = '', $render_slug = '' ) {
		$wrapper_settings    = $this->get_wrapper_settings( $render_slug );
		$slug                = $render_slug;
		$outer_wrapper_attrs = $wrapper_settings['attrs'];
		$inner_wrapper_attrs = $wrapper_settings['inner_attrs'];

		/**
		 * Filters the HTML attributes for the module's outer wrapper. The dynamic portion of the
		 * filter name, '$slug', corresponds to the module's slug.
		 *
		 *
		 * @param string[]           $outer_wrapper_attrs
		 * @param ET_Builder_Element $module_instance
		 */
		$outer_wrapper_attrs = apply_filters(
			"et_builder_module_{$slug}_outer_wrapper_attrs",
			$outer_wrapper_attrs,
			$this
		);

		/**
		 * Filters the HTML attributes for the module's inner wrapper. The dynamic portion of the
		 * filter name, '$slug', corresponds to the module's slug.
		 *
		 * @param string[]           $inner_wrapper_attrs
		 * @param ET_Builder_Element $module_instance
		 */
		$inner_wrapper_attrs = apply_filters(
			"et_builder_module_{$slug}_inner_wrapper_attrs",
			$inner_wrapper_attrs,
			$this
		);

		return sprintf(
			'<li%1$s>
				%2$s
				%3$s
				%6$s
				%7$s
				%8$s
				%9$s
				<div%4$s>
					%5$s
				</div>
			</li>',
			et_html_attrs( $outer_wrapper_attrs ),
			$wrapper_settings['parallax_background'],
			$wrapper_settings['video_background'],
			et_html_attrs( $inner_wrapper_attrs ),
			$output,
			et_()->array_get( $wrapper_settings, 'video_background_tablet' ),
			et_()->array_get( $wrapper_settings, 'video_background_phone' ),
			et_core_esc_previously( $wrapper_settings['pattern_background'] ), // #8
			et_core_esc_previously( $wrapper_settings['mask_background'] ) // #9
		);
	}

	/**
	 * Renders the module output.
	 *
	 * @param array  $attrs       List of attributes.
	 * @param string $content     Content being processed.
	 * @param string $render_slug Slug of module that is used for rendering output.
	 *
	 * @return string
	 */
	public function render( $attrs, $content, $render_slug ) {
//		$multi_view = \et_pb_multi_view_options( $this );
//		$multi_view = new ET_Builder_Module_Helper_MultiViewOptions($this);

		return sprintf(
			'<div class="et_pb_module_inner"><span class="item-elements et_pb_with_background">
                    <span class="item-elements-group"><span class="difl_icon_item_container">%2$s %1$s</span>%3$s</span>
                    <span class="item-tooltip-data" style="display:none">%4$s</span>
                </span></div>',

			et_core_esc_previously( $this->render_icon_item_body() ),
			et_core_esc_previously( $this->render_item_icon( $attrs ) ),
			et_core_esc_previously( $this->render_icon_item_body_outer_wrapper() ),
			et_core_esc_previously( $this->render_icon_item_tooltip_text() )
		);
	}

	/**
	 * Render item body with title and content
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return null|string
	 */
	private function render_icon_item_body() {
		$_is_outside = $this->props['list_item_content_outside_wrapper'];

		if ( $this->props['list_item_icon_only'] === 'off' ) {
			/*$this->df_process_bg(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_title_background',
					'selector'    => "$this->main_css_element .item-elements span.difl_icon_item_container .difl_icon_item_header",
					'hover'       => "$this->main_css_element .item-elements:hover span.difl_icon_item_container .difl_icon_item_header",
				]
			);
			$this->df_process_bg(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_content_background',
					'selector'    => "$this->main_css_element div .item-elements .difl_icon_item_body",
					'hover'       => "$this->main_css_element div .item-elements:hover .difl_icon_item_body",
				]
			);
			$this->df_process_bg(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_wrapper_background',
					'selector'    => "$this->main_css_element .item-elements",
					'hover'       => "$this->main_css_element .item-elements:hover",
					'important'   => true,
				]
			);

			$this->generate_styles(
				[
					'base_attr_name' => 'list_item_text_orientation',
					'selector'       => "$this->main_css_element .item-elements *:not(.difl_icon_item_icon_wrapper)",
					'css_property'   => 'text-align',
					'render_slug'    => $this->slug,
					'type'           => 'align',
					'important'      => true,
				]
			);

			$this->set_margin_padding_styles(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_title_margin',
					'type'        => 'margin',
					'selector'    => "$this->main_css_element .item-elements span.difl_icon_item_container .difl_icon_item_header",
					'hover'       => "$this->main_css_element .item-elements:hover span.difl_icon_item_container .difl_icon_item_header",
				]
			);
			$this->set_margin_padding_styles(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_title_padding',
					'type'        => 'padding',
					'selector'    => "$this->main_css_element .item-elements span.difl_icon_item_container .difl_icon_item_header",
					'hover'       => "$this->main_css_element .item-elements:hover span.difl_icon_item_container .difl_icon_item_header",
				]
			);
			$this->set_margin_padding_styles(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_content_margin',
					'type'        => 'margin',
					'selector'    => "$this->main_css_element .item-elements .item-elements-group .difl_icon_item_body",
					'hover'       => "$this->main_css_element .item-elements:hover .item-elements-group .difl_icon_item_body",
				]
			);
			$this->set_margin_padding_styles(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_content_padding',
					'type'        => 'padding',
					'selector'    => "$this->main_css_element .item-elements .item-elements-group .difl_icon_item_body",
					'hover'       => "$this->main_css_element .item-elements:hover .item-elements-group .difl_icon_item_body",
				]
			);
			$this->set_margin_padding_styles(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_wrapper_margin',
					'type'        => 'margin',
					'selector'    => "$this->main_css_element div .item-elements",
					'hover'       => "$this->main_css_element div .item-elements:hover",
				]
			);
			$this->set_margin_padding_styles(
				[
					'render_slug' => $this->slug,
					'slug'        => 'list_item_wrapper_padding',
					'type'        => 'padding',
					'selector'    => "$this->main_css_element div .item-elements",
					'hover'       => "$this->main_css_element div .item-elements:hover",
				]
			);*/

			$outside_wrapper = $_is_outside === 'off' ? $this->render_icon_item_body_text() : null;

			return sprintf(
				'<span class="difl_icon_item_content_wrapper">%1$s%2$s</span>',
				et_core_esc_previously( $this->render_icon_item_title_text() ),
				et_core_esc_previously( $outside_wrapper )
			);
		}

		return null;
	}

	/**
	 * Render item body with title and content
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return string
	 */
	private function render_icon_item_body_text() {
		$body_classes = $this->df_iconlist_add_background_class(
			[
				'background_field' => 'list_item_content_background',
				'classes'          => [
					'difl_icon_item_body',
				],
			]
		);

		$implode_classes = implode( ' ', $body_classes );

//		$item_body_text = $multi_view->render_element(
//			[
//				'tag'     => 'span',
//				'content' => '{{content}}',
//				'attrs'   => [
//					'class' => $implode_classes,
//				],
//			]
//		);
		$item_body_text = $this->elements->render( [
			'attrName'   => 'content',
			'attributes' => [
				'class' => $implode_classes,
			],
		] );

		if ( '' !== $item_body_text ) {
			return $item_body_text;
		}

		return null;
	}

	/**
	 * Render item body with title and content
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return null|string
	 */
	private function render_icon_item_title_text() {
		$icon_title_tag        = isset( $this->props['list_item_title_tag'] ) ? $this->props['list_item_title_tag'] : 'h4';
		$icon_title_url        = isset( $this->props['list_item_title_url'] ) ? $this->props['list_item_title_url'] : '';
		$icon_title_url_target = isset( $this->props['list_item_title_url_new_window'] ) ? $this->props['list_item_title_url_new_window'] : '';

		$title_tag   = '' !== $icon_title_url ? 'a' : 'span';
		$title_attrs = [];

		if ( 'a' === $title_tag ) {
			$title_attrs['href'] = $icon_title_url;

			if ( 'on' === $icon_title_url_target ) {
				$title_attrs['target'] = '_blank';
			}
		}

		$title_text = $this->elements->render( [
			'attrName'   => 'list_item_title',
			'tagName'    => $title_tag,
			'attributes' => $title_attrs,
		] );

		if ( '' !== $title_text ) {
			$body_classes = $this->df_iconlist_add_background_class(
				[
					'background_field' => 'list_item_title_background',
					'classes'          => [
						'difl_icon_item_header',
					],
				]
			);

			$implode_classes = implode( ' ', $body_classes );

			return sprintf(
				'<%1$s class="%4$s">%2$s%3$s</%1$s>',
				et_core_esc_previously( $icon_title_tag ),
				et_core_esc_previously( $title_text ),
				et_core_esc_previously( $this->render_item_title_font_icon() ),
				et_core_esc_previously( $implode_classes )
			);
		}

		return null;
	}

	/**
	 * Render item title icon
	 *
	 * @return null|string
	 */
	private function render_item_title_font_icon() {
		if ( $this->props['list_item_title_icon_enable'] === 'on'
		     && $this->props['list_item_icon_only'] === 'off' ) {
			$icon_classes = [ 'et-pb-icon', 'difl_list_title_icon' ];

			if ( $this->props['list_item_title_icon_on_hover'] !== 'on' ) {
				$icon_classes[] = 'always_show';
			}


			$icon_prop = 'list_item_title_icon';
            $list_item_title_icon = $this->props['list_item_title_icon'] ??
					[
						'unicode' => '&#x49;',
						'type'    => 'divi',
						'weight'  => 400,
                    ];

			return sprintf(
				'<span class="%2$s">%1$s</span>',
				Utils::process_font_icon( $list_item_title_icon ),
				implode( ' ', $icon_classes )
			);
		}

		return null;
	}

	/**
	 * Resolve image src (and alt) for a breakpoint from attrs with fallback (phone → tablet → desktop).
	 *
	 * @param array  $attrs Render attrs.
	 * @param string $bp    Breakpoint: desktop, tablet, phone.
	 * @return array [ 'src' => string, 'alt' => string ]
	 */
	private function get_image_for_breakpoint( $attrs, $bp ) {
		$inner = $attrs['list_item_image']['innerContent'] ?? [];
		$alt_inner = $attrs['alt']['innerContent'] ?? [];
		$default_src = $this->props['list_item_image'] ?? '';
		$default_alt = $this->props['alt'] ?? '';
		$get_src = function ( $bp_key ) use ( $inner, $default_src ) {
			$v = $inner[ $bp_key ]['value'] ?? null;
			if ( $v === null && $bp_key === 'tablet' ) {
				$v = $inner['desktop']['value'] ?? null;
			}
			if ( $v === null && $bp_key === 'phone' ) {
				$v = $inner['tablet']['value'] ?? $inner['desktop']['value'] ?? null;
			}
			if ( is_array( $v ) ) {
				return $v['src'] ?? $default_src;
			}
			return ( $v !== null && $v !== '' ) ? $v : $default_src;
		};
		$get_alt = function ( $bp_key ) use ( $alt_inner, $default_alt ) {
			$v = $alt_inner[ $bp_key ]['value'] ?? $alt_inner['desktop']['value'] ?? $default_alt;
			return is_string( $v ) ? $v : $default_alt;
		};
		return [
			'src' => $get_src( $bp ),
			'alt' => $get_alt( $bp ),
		];
	}

	/**
	 * Resolve icon value for a breakpoint from attrs with fallback (phone → tablet → desktop).
	 *
	 * @param array  $attrs Attrs array (e.g. from render).
	 * @param string $bp    Breakpoint: desktop, tablet, phone.
	 * @return array Icon value (unicode, type, weight).
	 */
	private function get_icon_for_breakpoint( $attrs, $bp ) {
		$inner = $attrs['list_item_icon']['innerContent'] ?? [];
		$default = [ 'unicode' => '&#x4e;', 'type' => 'divi', 'weight' => 400 ];
		if ( $bp === 'desktop' ) {
			$v = $inner['desktop']['value'] ?? $default;
			return is_array( $v ) ? $v : $default;
		}
		if ( $bp === 'tablet' ) {
			$v = $inner['tablet']['value'] ?? $inner['desktop']['value'] ?? $default;
			return is_array( $v ) ? $v : $default;
		}
		// phone
		$v = $inner['phone']['value'] ?? $inner['tablet']['value'] ?? $inner['desktop']['value'] ?? $default;
		return is_array( $v ) ? $v : $default;
	}

	/**
	 * Render font icon(s). With responsive attrs, outputs one span per breakpoint (desktop/tablet/phone) using Utils::process_font_icon; visibility is controlled by CSS in get_common_styles.
	 *
	 * @param array $attrs Render attrs (must have list_item_icon.innerContent for responsive).
	 * @return null|string
	 */
	private function render_item_font_icon( $attrs = [] ) {
		if ( $this->props['list_item_icon_type'] !== 'icon' ) {
			return null;
		}
		$inner = $attrs['list_item_icon']['innerContent'] ?? [];
		if ( empty( $inner ) ) {
			return sprintf( '<span class="et-pb-icon difl_list_icon">%1$s</span> ', Utils::process_font_icon( $this->props['list_item_icon'] ?? [ 'unicode' => '&#x4e;', 'type' => 'divi', 'weight' => 400 ] ) );
		}
		$desktop_icon = Utils::process_font_icon( $this->get_icon_for_breakpoint( $attrs, 'desktop' ) );
		$tablet_icon  = Utils::process_font_icon( $this->get_icon_for_breakpoint( $attrs, 'tablet' ) );
		$phone_icon   = Utils::process_font_icon( $this->get_icon_for_breakpoint( $attrs, 'phone' ) );
		return sprintf(
			'<span class="et-pb-icon difl_list_icon difl_icon_bp_desktop">%1$s</span><span class="et-pb-icon difl_list_icon difl_icon_bp_tablet">%2$s</span><span class="et-pb-icon difl_list_icon difl_icon_bp_phone">%3$s</span> ',
			$desktop_icon,
			$tablet_icon,
			$phone_icon
		);
	}

	/**
	 * Render item image
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return null|string
	 */
	private function render_item_icon_image( $attrs = [] ) {
		if ( $this->props['list_item_icon_type'] !== 'image' ) {
			return null;
		}
		$inner = $attrs['list_item_image']['innerContent'] ?? [];
		if ( empty( $inner ) ) {
			$alt_text      = $this->props['alt'];
			$image_classes = [ 'et_pb_image_wrap' ];
			$idImg         = attachment_url_to_postid( $this->props['list_item_image'] );
			if ( ! $alt_text && $idImg > 0 ) {
				$alt_text = get_post_meta( $idImg, '_wp_attachment_image_alt', true );
			}
			$img_title = $idImg > 0 ? get_the_title( $idImg ) : $alt_text;
			$image_attachment_class = $this->get_image_attachment_class( $this->props, 'list_item_image' );
			if ( ! empty( $image_attachment_class ) ) {
				$image_classes[] = esc_attr( $image_attachment_class );
			}
			return $this->elements->render( [
				'attrName'   => 'list_item_image',
				'attributes' => [
					'class' => implode( ' ', $image_classes ),
					'alt'   => $alt_text,
					'title' => $img_title,
				],
			] );
		}
		$base_classes = [ 'et_pb_image_wrap' ];
		$image_attachment_class = $this->get_image_attachment_class( $this->props, 'list_item_image' );
		if ( ! empty( $image_attachment_class ) ) {
			$base_classes[] = esc_attr( $image_attachment_class );
		}
		$out = [];
		foreach ( [ 'desktop', 'tablet', 'phone' ] as $bp ) {
			$img   = $this->get_image_for_breakpoint( $attrs, $bp );
			if ( empty( $img['src'] ) ) {
				continue;
			}
			$idImg = attachment_url_to_postid( $img['src'] );
			$title = $idImg > 0 ? get_the_title( $idImg ) : $img['alt'];
			if ( empty( $img['alt'] ) && $idImg > 0 ) {
				$img['alt'] = get_post_meta( $idImg, '_wp_attachment_image_alt', true );
			}
			$cls = array_merge( $base_classes, [ 'difl_icon_bp_' . $bp ] );
			$out[] = sprintf(
				'<img class="%1$s" src="%2$s" alt="%3$s" title="%4$s" />',
				esc_attr( implode( ' ', $cls ) ),
				esc_url( $img['src'] ),
				esc_attr( $img['alt'] ),
				esc_attr( $title )
			);
		}
		return implode( '', $out );
	}

	/**
	 * Render item image
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return null|string
	 */
	private function render_item_icon_text() {
		if ( $this->props['list_item_icon_type'] === 'text' && ! empty( $this->props['list_item_icon_text'] ) ) {
			$icon_text_classes = [ 'difl_list_icon_text' ];

			return $this->elements->render( [
				'attrName'   => 'list_item_icon_text',
				'tagName'    => 'span',
				'attributes' => [
					'class' => implode( ' ', $icon_text_classes ),
				],
			] );
		}

		return null;
	}

	/**
	 * Render item lottie image
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return null|string
	 */
	private function render_item_icon_lottie() {
		if ( $this->props['list_item_icon_type'] === 'lottie'
		     && (
			     ! empty( $this->props['list_item_icon_lottie_src_upload'] )
			     || ! empty( $this->props['list_item_icon_lottie_src_remote'] )
		     )
		) {
			$lottie_image_classes = [ 'difl_lottie_player', 'lottie-player-container' ];

			wp_enqueue_script( 'df-lottie-lib' );

			// Generate configuration for lottie animation
			$lottie_type = isset( $this->props['list_item_icon_lottie_src_type'] ) ? $this->props['list_item_icon_lottie_src_type'] : '';
//			$lottie_src_prop = $lottie_type === 'local' ? '{{list_item_icon_lottie_src_upload}}' : '{{list_item_icon_lottie_src_remote}}';
			$lottie_src_prop = $lottie_type === 'local' ? $this->props['list_item_icon_lottie_src_upload'] : $this->props['list_item_icon_lottie_src_remote'];

			// General Settings
			$output_renderer = isset( $this->props['list_item_icon_lottie_renderer'] ) ? $this->props['list_item_icon_lottie_renderer'] : 'svg';
			$play_speed      = isset( $this->props['list_item_icon_lottie_speed'] ) ? $this->props['list_item_icon_lottie_speed'] : '1';
			$play_direction  = isset( $this->props['list_item_icon_lottie_direction'] ) ? $this->props['list_item_icon_lottie_direction'] : 'off';

			// Conditional Settings
			$play_loop           = isset( $this->props['list_item_icon_lottie_loop'] ) && $this->props['list_item_icon_lottie_loop'] === 'on';
			$play_trigger_method = isset( $this->props['list_item_icon_lottie_trigger_method'] ) ? $this->props['list_item_icon_lottie_trigger_method'] : 'viewport';

			// Interactivity Settings
			$play_with_delay = isset( $this->props['list_item_icon_lottie_delay'] ) && $play_trigger_method === 'viewport' ? $this->props['list_item_icon_lottie_delay'] : '0';
			$anim_mouse_out  = isset( $this->props['list_item_icon_lottie_mouseout_action'] ) ? $this->props['list_item_icon_lottie_mouseout_action'] : 'off';
			$scroll_effect   = isset( $this->props['list_item_icon_lottie_scroll_effect'] ) ? $this->props['list_item_icon_lottie_scroll_effect'] : 'off';

			// migration
			if ( in_array( $play_trigger_method, [ 'play-once', 'freeze-click' ] ) ) {
				$play_trigger_method = 'viewport';
			}

			$lottie_image_options = [
				// General Options
				'renderer'          => $output_renderer,
				'speed'             => $play_speed,
				'direction_reverse' => $play_direction,
				'loop'              => $play_loop,

				// Interactive Options
				'delay'             => (int) $play_with_delay,
				'mode'              => 'normal',
				'interaction'       => $play_trigger_method,
				'scroll_effect'     => $scroll_effect,
			];

			// Set mouse out event for hover interactivity with lottie image.
			$old_values = [ '', 'no_action', 'off' ];
			if ( $play_trigger_method === 'hover' && ! in_array( $anim_mouse_out, $old_values, true ) ) {
				$lottie_image_options['mouse_out_event'] = $anim_mouse_out;
			}


			return $this->elements->render( [
				'tagName'    => 'span',
				'attributes' => [
					'class'        => implode( ' ', $lottie_image_classes ),
					'data-src'     => $lottie_src_prop,
					'data-options' => wp_json_encode( $lottie_image_options ),
				],
			] );
		}

		return null;
	}

	/**
	 * Render item icon which on are active
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return string
	 */
	private function render_item_icon( $attrs = [] ) {
		if ( $this->props['list_item_icon_type'] !== 'none'
		     && (
			     null !== $this->render_item_font_icon( $attrs )
			     || null !== $this->render_item_icon_image()
			     || null !== $this->render_item_icon_text()
			     || null !== $this->render_item_icon_lottie()
		     )
		) {
			$icon_wrapper_class = [ 'difl_icon_item_icon_wrapper' ];
			$lottie_renderer    = isset( $this->props['list_item_icon_lottie_renderer'] ) ? $this->props['list_item_icon_lottie_renderer'] : 'svg';

			if ( $this->props['list_item_icon_only'] === 'on' ) {
				$icon_wrapper_class[] = 'icon_only';
			}
			//always_show

			if ( $this->props['list_item_icon_on_hover'] === 'on' && $this->props['list_item_icon_only'] === 'off' ) {
				$icon_wrapper_class[] = 'show_on_hover';
			}

			/*// Start the effect : Show icon on hover
			if ( $this->props['list_item_icon_on_hover'] === 'on' && $this->props['list_item_icon_only'] === 'off' ) {
				$icon_wrapper_class[] = 'show_on_hover';

				// set icon placement for button image with default, hover and responsive
				$this->df_iconlist_show_icon_on_hover_styles(
					[
						'field'         => 'list_item_icon_placement',
						'trigger'       => 'list_item_icon_type',
						'dependsOn'     => [
							'icon'   => 'list_item_icon_size',
							'image'  => 'list_item_image_width',
							'lottie' => 'list_item_icon_lottie_width',
						],
						'selector'      => "$this->main_css_element .item-elements span.difl_icon_item_container span.difl_icon_item_icon_wrapper.show_on_hover",
						'hover'         => "$this->main_css_element .item-elements:hover span.difl_icon_item_container span.difl_icon_item_icon_wrapper.show_on_hover",
						'css_property'  => 'margin',
						'type'          => 'margin',
						'allowedUnits'  => [
							'%',
							'em',
							'rem',
							'px',
							'cm',
							'mm',
							'in',
							'pt',
							'pc',
							'ex',
							'vh',
							'vw',
						],
						'mappingValues' => [
							'row'            => '0 #px 0 -#px',
							'row-reverse'    => '0 -#px 0 #px',
							'column'         => '-#px 0 #px 0',
							'column-reverse' => '#px 0 -#px 0',
						],
						'defaults'      => [
							'icon'   => '40px',
							'image'  => '40px',
							'lottie' => '40px',
							'field'  => 'row',
						],
					]
				);
			}
			// End the effect : Show icon on hover


			if ( $this->props['list_item_icon_placement'] !== 'inherit' ) {
				$this->generate_styles(
					[
						'base_attr_name' => 'list_item_icon_placement',
						'selector'       => "$this->main_css_element span.difl_icon_item_container",
						'css_property'   => 'flex-direction',
						'render_slug'    => $this->slug,
						'type'           => 'align',
						'important'      => true,
					]
				);
			}

			//	$this->props['list_item_icon_placement'] !== 'column' && $this->props['list_item_icon_vertical_placement'] !== ''
			$align_selector = "$this->main_css_element span.difl_icon_item_container span.difl_icon_item_icon_wrapper";
			if ( $this->props['list_item_icon_placement'] !== 'column'
			     && $this->props['list_item_icon_vertical_placement'] !== 'inherit'
			     && ! ! $this->props['list_item_icon_vertical_placement'] ) {
				self::set_style( $this->slug, [
					'selector'    => $align_selector,
					'declaration' => 'display:flex;',
				] );
				// Icon placement with default, responsive, hover
				$this->generate_styles(
					[
						'base_attr_name' => 'list_item_icon_vertical_placement',
						'selector'       => $align_selector,
						'css_property'   => 'align-items',
						'render_slug'    => $this->slug,
						'type'           => 'align',
					]
				);
			} else if ( $this->props['list_item_icon_only'] === 'on' ) {
				// Icon placement with default, responsive, hover
				$this->generate_styles(
					[
						'base_attr_name' => 'list_item_icon_alignment_alt',
						'selector'       => "$this->main_css_element span.difl_icon_item_container",
						'css_property'   => 'justify-content',
						'render_slug'    => $this->slug,
						'type'           => 'align',
					]
				);
			} else {
				if ( $this->props['list_item_icon_placement'] === 'column' ) {
					self::set_style( $this->slug, [
						'selector'    => $align_selector,
						'declaration' => 'display: block;',
					] );
				}
			}

		*/

			return sprintf(
				'<span class="%1$s"><span class="icon-element">%2$s%3$s%4$s%5$s</span></span>',
				implode( ' ', $icon_wrapper_class ),
				et_core_esc_previously( $this->render_item_font_icon( $attrs ) ),
				et_core_esc_previously( $this->render_item_icon_image( $attrs ) ),
				et_core_esc_previously( $this->render_item_icon_text() ),
				et_core_esc_previously( $this->render_item_icon_lottie() )
			);
		}

		return null;
	}

	/**
	 * Render item body if content out wrapper is enabled
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return null|string
	 */
	private function render_icon_item_body_outer_wrapper() {
		if ( $this->props['list_item_icon_only'] === 'off'
		     && $this->props['list_item_content_outside_wrapper'] === 'on' ) {
			return sprintf(
				'<span class="difl_icon_item_outer_wrapper">%1$s</span>',
				et_core_esc_previously( $this->render_icon_item_body_text() )
			);
		}

		return null;
	}

	/**
	 * Render item body with title and content
	 *
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return string
	 */
	private function render_icon_item_tooltip_text() {
		if ( $this->props['list_item_use_tooltip'] === 'on' && $this->props['list_item_tooltip_content'] !== '' ) {
			$data_options = [
				'tooltip_enable'      => $this->props['list_item_use_tooltip'] === 'on',
				'arrow'               => $this->props['tooltip_arrow'] === 'on',
				'interactive'         => $this->props['tooltip_interactive'] === 'on',
				'interactiveBorder'   => $this->props['tooltip_interactive'] === 'on' && isset( $this->props['tooltip_interactive_border'] ) ? $this->props['tooltip_interactive_border'] : 2,
				'interactiveDebounce' => $this->props['tooltip_interactive'] === 'on' && isset( $this->props['tooltip_interactive_debounce'] ) ? $this->props['tooltip_interactive_debounce'] : 0,
				'animation'           => isset( $this->props['tooltip_animation'] ) ? $this->props['tooltip_animation'] : 'fade',
				'placement'           => isset( $this->props['tooltip_placement'] ) ? $this->props['tooltip_placement'] : 'top',
				'trigger'             => isset( $this->props['tooltip_trigger'] ) ? $this->props['tooltip_trigger'] : 'focus',
				'followCursor'        => $this->props['tooltip_follow_cursor'] === 'on',
				'maxWidth'            => isset( $this->props['tooltip_custom_maxwidth'] ) ? $this->props['tooltip_custom_maxwidth'] : 350,
				'offsetEnable'        => $this->props['tooltip_offset_enable'] === 'on',
				'offsetSkidding'      => $this->props['tooltip_offset_enable'] === 'on' && isset( $this->props['tooltip_offset_skidding'] ) ? $this->props['tooltip_offset_skidding'] : 0,
				'offsetDistance'      => $this->props['tooltip_offset_enable'] === 'on' && isset( $this->props['tooltip_offset_distance'] ) ? $this->props['tooltip_offset_distance'] : 10,
			];

			return sprintf(
				'<span class="difl_icon_item_tooltip_content" data-options=\'%1$s\'>%2$s</span>',
				wp_json_encode( $data_options ),
				$this->props['list_item_tooltip_content']
			);
		}

		return null;
	}

	/**
	 * Add class name for background field
	 *
	 * @param array $options
	 *
	 * @return null|array
	 */
	protected function df_iconlist_add_background_class( $options = [] ) {
		if ( isset( $options['background_field'], $options['classes'] ) ) {
			if ( array_key_exists( $options['background_field'], $this->attributes ) ) {
				$options['classes'][] = 'et_pb_with_background';
			}

			return $options['classes'];
		}

		return null;
	}

	/**
	 * Collect parent module object, by default standard class object
	 *
	 * @param string $render_slug The slug of module.
	 *
	 * @return stdClass|object
	 * @since 1.0.0
	 */
	private function df_iconlist_get_parent_module( $render_slug = '' ) {
		$default        = new stdClass();
		$render_slug    = ! empty( $render_slug ) ? $render_slug : $this->slug;
		$parent_modules = self::get_parent_modules();

		foreach ( $parent_modules as $parent_module ) {
			foreach ( $parent_module as $moduleObject ) {
				if ( $moduleObject->child_slug === $render_slug ) {
					$default = $moduleObject;
					break;
				}
			}
		}

		return $default;
	}

	/**
	 * Collect icon prop width event if responsive mode
	 *
	 * @param object $parent_module The parent module
	 * @param array  $options       Options of current width.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function difl_iconlist_get_icon_prop_width( $parent_module, $options = [] ) {
		$defaults      = [ 'icon' => '', 'image' => '', 'lottie' => '' ];
		$results       = [ 'desktop' => '', 'tablet' => '', 'phone' => '' ];
		$devices       = array_keys( $results );
		$allowed_props = array_keys( $defaults );

		// Initiate default values for current options
		$default_options = [
			'trigger'   => '',
			'dependsOn' => $defaults,
			'defaults'  => $defaults,
		];
		$options         = wp_parse_args( $options, $default_options );

		$icon_trigger_prop = $options['trigger'];
		$icon_depend_prop  = $options['dependsOn'];

		if ( ( $this->props[ $icon_trigger_prop ] === 'off' )
		     || in_array( $this->props[ $icon_trigger_prop ], $allowed_props, true ) ) {
			foreach ( $devices as $current_device ) {
				$field_suffix = 'desktop' !== $current_device ? "_$current_device" : '';

				foreach ( $allowed_props as $allowed_prop ) {
					$modified_prop = $icon_depend_prop[ $allowed_prop ] . $field_suffix;

					if ( $this->props[ $icon_trigger_prop ] === $allowed_prop ) {
						if ( isset( $this->props[ $modified_prop ] ) && ! empty( $this->props[ $modified_prop ] ) ) {
							$results[ $current_device ] = $this->props[ $modified_prop ];
						} else if ( ! empty( $parent_module->props[ $modified_prop ] ) ) {
							$results[ $current_device ] = $parent_module->props[ $modified_prop ];
						} else if ( isset( $options['defaults'][ $allowed_prop ] ) ) {
							$results[ $current_device ] = $options['defaults'][ $allowed_prop ];
						} else {
							$results[ $current_device ] = '';
						}
					}
				}
			}
		}

		return $results;
	}


	/* Custom functions for icon list module */

	/**
	 * Collect the value of any props for Icon on hover effect
	 *
	 * @param object $parent_module The parent module
	 * @param array  $options       Options of current width.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function df_iconlist_generate_css( $parent_module, $options = [] ) {
		// Initiate default values for current options
		$default_options = [
			'qualified_name'     => '',
			'mappingValues'      => [],
			'allowed_units'      => [],
			'default_width'      => '',
			'default_unit_value' => '',
			'manual'             => false,
			'manual_value'       => '',
		];
		$options         = wp_parse_args( $options, $default_options );

		// Collect placement value
		if ( $options['manual'] ) {
			$default_value = $options['manual_value'];
		} else if ( $this->props[ $options['qualified_name'] ] === 'inherit' ) {
			if ( $this->type === 'child' && ! empty( $parent_module->props[ $options['qualified_name'] ] ) ) {
				$default_value = $parent_module->props[ $options['qualified_name'] ];
			} else {
				$default_value = isset( $options['defaults']['field'] ) ? $options['defaults']['field'] : '';
			}
		} else {
			$default_value = $this->props[ $options['qualified_name'] ];
		}

		// Generate actual value
		$field_value          = $this->df_iconlist_collect_prop_mapping_value( $options, $default_value );
		$clean_default_value  = str_replace( $options['allowed_units'], '', $options['default_width'] );
		$increased_value_data = (int) $clean_default_value + (int) $options['default_unit_value'];

		// Return actual value
		return str_replace( '#', $increased_value_data, $field_value );
	}

	/**
	 * Collect any props value from mapping values
	 *
	 * @param array $options The options array data
	 * @param       $current_value
	 *
	 * @return mixed
	 */
	protected function df_iconlist_collect_prop_mapping_value( $options, $current_value ) {
		if ( isset( $options['mappingValues'] ) && $options['mappingValues'] !== [] ) {
			if ( is_callable( $options['mappingValues'] ) ) {
				return $options['mappingValues']( $current_value );
			}

			return isset( $options['mappingValues'][ $current_value ] ) ? $options['mappingValues'][ $current_value ] : '';
		}

		return $current_value;
	}

	/**
	 * Filter multi view value.
	 *
	 * @param mixed                                     $raw_value
	 * @param array                                     $args
	 * @param ET_Builder_Module_Helper_MultiViewOptions $multi_view Multiview object instance.
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 * @see   ET_Builder_Module_Helper_MultiViewOptions::filter_value
	 */
	public function multi_view_filter_value( $raw_value, $args, $multi_view ) {
		$name = isset( $args['name'] ) ? $args['name'] : '';
		$mode = isset( $args['mode'] ) ? $args['mode'] : '';


		$icon_fields = [
			'list_item_icon',
			'list_item_title_icon',
		];
		if ( $raw_value && in_array( $name, $icon_fields, true ) ) {
			return et_pb_get_extended_font_icon_value( $raw_value, true );
		}

		$fields_need_escape = [
			'list_item_title',
		];

		if ( $raw_value && in_array( $name, $fields_need_escape, true ) ) {
			return $this->_esc_attr( $multi_view->get_name_by_mode( $name, $mode ), 'none', $raw_value );
		}

		if ( $raw_value && in_array( $name, [ 'content', 'list_item_tooltip_content' ], true ) ) {
			$raw_value = preg_replace( '/^[\w]?<\/p>/smi', '', $raw_value );
			$raw_value = preg_replace( '/<p>$/smi', '', $raw_value );
		}

		return $raw_value;
	}

	/**
	 * Whether resolved placement has 'column' on any breakpoint.
	 * Only when column do we output flex-direction (and related) code.
	 *
	 * @param array $placement_resolved [ desktop => [ value => ... ], ... ] from resolve_attr_inner_with_inheritance.
	 * @return bool
	 */
	private function placement_resolved_has_column( array $placement_resolved ) {
		foreach ( $placement_resolved as $data ) {
			if ( ( $data['value'] ?? '' ) === 'column' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Resolve responsive innerContent with Divi breakpoint inheritance (tablet follows desktop, phone follows tablet).
	 * Uses ModuleUtils::use_attr_value so we don't duplicate inheritance logic.
	 *
	 * @param array  $inner         InnerContent [ desktop => [ value => ... ], ... ] (may have missing breakpoints).
	 * @param string $default_value Default when no value is set on any breakpoint.
	 * @return array [ desktop => [ value => ... ], tablet => [ value => ... ], phone => [ value => ... ] ]
	 */
	private function resolve_attr_inner_with_inheritance( array $inner, $default_value = 'inherit' ) {
		$breakpoints = array_keys( Breakpoint::get_style_breakpoint_settings() );
		$resolved    = [];

        foreach ( $breakpoints as $bp ) {
			$resolved[ $bp ] = [
				'value' => ModuleUtils::use_attr_value( [
					'attr'         => $inner,
					'breakpoint'   => $bp,
					'state'        => 'value',
					'defaultValue' => $default_value,
				] ),
			];
		}
		return $resolved;
	}

	public function get_common_styles( $args ) {
		$attrs           = $this->get_merged_attrs( $args );
		$attrs           = self::build_attr_for_styles( $attrs );
		$attrs           = $this->get_default_value_for_styles( $attrs );
		$attrs           = self::build_attr_for_styles( $attrs );
		$orderClass      = $args['orderClass'] ?? '';
		$parentOrderClass = $args['parentOrderClass'] ?? '';

		$styles = [];

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .item-elements .item-elements-group *:not(.difl_icon_item_icon_wrapper)",
            'attr' => $attrs['child_wrapper_element']['decoration']['text']['text'] ?? null,
            'declarationFunction' => function ($props) {
                $orientation = isset($props['attrValue']['orientation']) ? $props['attrValue']['orientation'] : null;
                if ($orientation) {
                    return "text-align: {$orientation} !important;";
                }
                return null;
            }
        ]);

        // When tooltip is enabled, apply overflow visible to parent (IconList) so tooltips are not clipped.
        if ( ( $this->props['list_item_use_tooltip'] ?? '' ) === 'on' && $parentOrderClass !== '' ) {
            $common_styles[] = CommonStyle::style( [
                'selector'            => $parentOrderClass,
                'attr'                => $attrs['list_item_use_tooltip']['innerContent'] ?? [],
                'declarationFunction' => function () {
                    return 'overflow-x: visible !important; overflow-y: visible !important;';
                },
            ] );
        }

		if ( ! empty( $this->props['list_item_icon_bg_color'] ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .icon-element",
				'attr'      => $attrs['list_item_icon_bg_color']['innerContent'] ?? null,
				'property'  => 'background-color',
				'important' => true,
			] );
		}

		// When icon border radius is set, clip image/icon to rounded shape.
		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .icon-element",
			'attr'                => $attrs['child_image_icon']['decoration']['border'] ?? null,
			'declarationFunction' => function ( $props ) {
				if ( ! empty( $props['attrValue']['radius'] ) ) {
					return 'overflow: hidden;';
				}
				return '';
			},
		] );

		if ( ! empty( $this->props['list_item_icon_text_gap'] ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements span.difl_icon_item_container",
				'attr'      => $attrs['list_item_icon_text_gap']['innerContent'] ?? null,
				'property'  => 'gap',
				'important' => true,
			] );
		}

		// Icon alignment (text-align) on icon wrapper – applied for every item that has an icon.
		if ( ( $this->props['list_item_icon_type'] ?? '' ) !== 'none' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper",
				'attr'      => $attrs['list_item_icon_alignment']['innerContent'] ?? null,
				'property'  => 'text-align',
				'important' => true,
			] );
		}

		if ( 'icon' === ( $this->props['list_item_icon_type'] ?? '' ) ) {

            if(isset($this->props['list_item_icon_size']) && $this->props['list_item_icon_size'] !== '0px') {
                $common_styles[] = CommonStyle::style( [
                    'selector'  => "{$orderClass} .item-elements span.difl_icon_item_icon_wrapper span.et-pb-icon",
                    'attr'      => $attrs['list_item_icon_size']['innerContent'] ?? null,
                    'property'  => 'font-size',
                    'important' => true,
                ] );
            }

			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} span.difl_icon_item_icon_wrapper span.et-pb-icon",
				'attr'                => $attrs['list_item_icon']['innerContent'] ?? null,
				'declarationFunction' => function ( $style_props ) {
					$icon = $style_props['attrValue'] ?? $this->props['list_item_icon'] ?? '';
					return $this->get_font_style( $icon );
				},
			] );

			// Responsive icon visibility: when we have innerContent, we output 3 spans per breakpoint. Each rule must be scoped to its viewport via atRules so tablet/phone spans are not hidden everywhere.
			if ( ! empty( $attrs['list_item_icon']['innerContent'] ) ) {
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} span.difl_icon_item_icon_wrapper span.difl_icon_bp_tablet, {$orderClass} span.difl_icon_item_icon_wrapper span.difl_icon_bp_phone",
					'attr'                => [ 'desktop' => [ 'value' => '' ] ],
					'declarationFunction' => function () {
						return 'display: none !important;';
					},
					'atRules'             => '@media only screen and (min-width: 981px)',
				] );
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} span.difl_icon_item_icon_wrapper span.difl_icon_bp_desktop, {$orderClass} span.difl_icon_item_icon_wrapper span.difl_icon_bp_phone",
					'attr'                => [ 'tablet' => [ 'value' => '' ] ],
					'declarationFunction' => function () {
						return 'display: none !important;';
					},
					'atRules'             => '@media only screen and (min-width: 768px) and (max-width: 980px)',
				] );
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} span.difl_icon_item_icon_wrapper span.difl_icon_bp_desktop, {$orderClass} span.difl_icon_item_icon_wrapper span.difl_icon_bp_tablet",
					'attr'                => [ 'phone' => [ 'value' => '' ] ],
					'declarationFunction' => function () {
						return 'display: none !important;';
					},
					'atRules'             => '@media only screen and (max-width: 767px)',
				] );
			}

            $common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .item-elements span.difl_icon_item_container .difl_icon_item_header span.et-pb-icon",
				'attr'                => $attrs['list_item_title_icon']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return $this->get_font_style( $this->props['list_item_title_icon'] ?? '' );
				},
			] );

			if ( ! empty( $this->props['list_item_icon_color'] ) ) {
				$common_styles[] = CommonStyle::style( [
					'selector'  => "{$orderClass} span.difl_icon_item_icon_wrapper span.et-pb-icon",
					'attr'      => $attrs['list_item_icon_color']['innerContent'] ?? null,
					'property'  => 'color',
					'important' => true,
				] );
			}
		}

		if ( ! empty( $this->props['list_item_icon_type'] ) && $this->props['list_item_icon_type'] === 'image' ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => ".difl_iconlist_container {$orderClass} .item-elements span.difl_icon_item_icon_wrapper img",
				'attr'      => $attrs['list_item_image_width']['innerContent'] ?? null,
				'declarationFunction' => function( $props ) {
					$value = $props['attrValue'] ?? '';
					// Return empty string if value is 0px, 0, or empty
					if ( empty( $value ) || $value === '0px' || $value === '0' ) {
						return '';
					}
					return "width: {$value} !important;";
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => ".difl_iconlist_container {$orderClass} span.difl_icon_item_icon_wrapper img",
				'attr'      => $attrs['list_item_image_height']['innerContent'] ?? null,
				'declarationFunction' => function( $props ) {
					$value = $props['attrValue'] ?? '';
					// Return empty string if value is 0px, 0, or empty
					if ( empty( $value ) || $value === '0px' || $value === '0' ) {
						return '';
					}
					return "height: {$value} !important;";
				},
			] );

			// Responsive image visibility: when list_item_image has innerContent we output 3 img tags per breakpoint; scope visibility with atRules.
			if ( ! empty( $attrs['list_item_image']['innerContent'] ) ) {
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} span.difl_icon_item_icon_wrapper img.difl_icon_bp_tablet, {$orderClass} span.difl_icon_item_icon_wrapper img.difl_icon_bp_phone",
					'attr'                => [ 'desktop' => [ 'value' => '' ] ],
					'declarationFunction' => function () {
						return 'display: none !important;';
					},
					'atRules'             => '@media only screen and (min-width: 981px)',
				] );
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} span.difl_icon_item_icon_wrapper img.difl_icon_bp_desktop, {$orderClass} span.difl_icon_item_icon_wrapper img.difl_icon_bp_phone",
					'attr'                => [ 'tablet' => [ 'value' => '' ] ],
					'declarationFunction' => function () {
						return 'display: none !important;';
					},
					'atRules'             => '@media only screen and (min-width: 768px) and (max-width: 980px)',
				] );
				$common_styles[] = CommonStyle::style( [
					'selector'            => "{$orderClass} span.difl_icon_item_icon_wrapper img.difl_icon_bp_desktop, {$orderClass} span.difl_icon_item_icon_wrapper img.difl_icon_bp_tablet",
					'attr'                => [ 'phone' => [ 'value' => '' ] ],
					'declarationFunction' => function () {
						return 'display: none !important;';
					},
					'atRules'             => '@media only screen and (max-width: 767px)',
				] );
			}
		}

		if ( 'lottie' === ( $this->props['list_item_icon_type'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .difl_lottie_player svg path",
				'attr'      => $attrs['list_item_icon_lottie_color']['innerContent'] ?? null,
				'property'  => 'fill',
				'important' => true,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .difl_lottie_player",
				'attr'      => $attrs['list_item_icon_lottie_background_color']['innerContent'] ?? null,
				'property'  => 'background-color',
				'important' => true,
			] );

            if(isset($this->props['list_item_icon_lottie_width']) && $this->props['list_item_icon_lottie_width'] !== '0px') {
                $common_styles[] = CommonStyle::style( [
                    'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .difl_lottie_player",
                    'attr'      => $attrs['list_item_icon_lottie_width']['innerContent'] ?? null,
                    'property'  => 'width',
                    'important' => true,
                ] );
            }

            if(isset($this->props['list_item_icon_lottie_height']) && $this->props['list_item_icon_lottie_height'] !== '0px') {
                $common_styles[] = CommonStyle::style( [
                    'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .difl_lottie_player",
                    'attr'      => $attrs['list_item_icon_lottie_height']['innerContent'] ?? null,
                    'property'  => 'height',
                    'important' => true,
                ] );
            }

		}
        // Icon placement: only render flex-direction (and icon wrapper styles) when resolved value is "column".
        $placement_inner    = $attrs['list_item_icon_placement']['innerContent'] ?? [];
        $vertical_inner     = $attrs['list_item_icon_vertical_placement']['innerContent'] ?? [];
        $placement_resolved = $this->resolve_attr_inner_with_inheritance( $placement_inner, 'inherit' );
        $vertical_resolved  = $this->resolve_attr_inner_with_inheritance( $vertical_inner, 'inherit' );
        $icon_wrapper_selector = "{$orderClass} .item-elements span.difl_icon_item_container .difl_icon_item_icon_wrapper";
        $render_placement = $this->placement_resolved_has_column( $placement_resolved );

        if ( $render_placement ) {
            $common_styles[] = CommonStyle::style( [
                'selector'  => "{$orderClass} .item-elements span.difl_icon_item_container",
                'attr'      => $placement_resolved,
                'property'  => 'flex-direction'
            ] );
            // Icon wrapper: column → display:block; else when vertical set → display:flex + align-items (D4).
            $common_styles[] = CommonStyle::style( [
                'selector'            => $icon_wrapper_selector,
                'attr'                => $vertical_resolved,
                'declarationFunction' => function ( $props ) use ( $placement_resolved ) {
                    $bp = $props['breakpoint'] ?? 'desktop';
                    $p  = $placement_resolved[ $bp ]['value'] ?? 'row';
                    $v  = $props['attrValue'] ?? '';
                    if ( $p === 'column' ) {
                        return 'display: block;';
                    }
                    if ( $v !== '' && $v !== 'inherit' ) {
                        return 'display: flex;';
                    }
                    return '';
                },
            ] );
            $common_styles[] = CommonStyle::style( [
                'selector'            => $icon_wrapper_selector,
                'attr'                => $vertical_resolved,
                'declarationFunction' => function ( $props ) use ( $placement_resolved ) {
                    $bp  = $props['breakpoint'] ?? 'desktop';
                    $p   = $placement_resolved[ $bp ]['value'] ?? 'row';
                    $val = $props['attrValue'] ?? '';
                    if ( $p === 'column' || $val === '' || $val === 'inherit' ) {
                        return '';
                    }
                    return 'align-items: ' . $val . ' !important;';
                },
                'important' => true,
            ] );
        }

        if ( $this->props['list_item_icon_on_hover'] === 'on' ) {
            $common_styles[] = CommonStyle::style( [
                'selector'  => "{$orderClass} .item-elements span.difl_icon_item_container span.difl_icon_item_icon_wrapper.show_on_hover",
                'attr'      => $attrs['list_item_icon_on_hover']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    return 'margin: 0 44px 0 -44px !important; opacity: 0 !important;';
                },
            ] );

            $common_styles[] = CommonStyle::style( [
                'selector'  => "{$orderClass} .item-elements:hover span.difl_icon_item_container span.difl_icon_item_icon_wrapper.show_on_hover",
                'attr'      => $attrs['list_item_icon_on_hover']['innerContent'] ?? null,
                'declarationFunction' => function ($props) {
                    return 'margin: 0 !important; opacity: 1 !important;';
                },
            ] );
        }
        $common_styles[] = CommonStyle::style( [
            'selector'  => "{$orderClass} span.difl_icon_item_container .difl_icon_item_header span.et-pb-icon",
            'attr'      => $attrs['list_item_title_icon_size']['innerContent'] ?? null,
            'property'  => 'font-size',
            'important' => true,
        ] );
		if ( 'on' === ( $this->props['list_item_icon_only'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} span.difl_icon_item_container .difl_icon_item_header span.et-pb-icon",
				'attr'      => $attrs['list_item_title_icon_color']['innerContent'] ?? null,
				'property'  => 'color',
				'important' => true,
			] );

			if ( 'html' !== ( $this->props['list_item_icon_lottie_renderer'] ?? '' ) ) {
				$common_styles[] = CommonStyle::style( [
					'selector'  => "{$orderClass} .item-elements span.difl_icon_item_container .difl_icon_item_icon_wrapper",
					'attr'      => $attrs['list_item_icon_alignment']['innerContent'] ?? null,
					'property'  => 'text-align',
					'important' => true,
				] );
			}

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} span.item-elements",
				'attr'      => $attrs['list_item_content']['decoration']['sizing']['maxWidth'] ?? null,
				'property'  => 'max-width',
				'important' => true,
			] );
		} else {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements span.difl_icon_item_container",
				'attr'      => $attrs['list_item_icon_alignment_alt']['innerContent'] ?? null,
				'property'  => 'justify-content',
				'important' => true,
			] );
		}

		$arrow_color       = $this->props['tooltip_arrow_color'] ?? '';
		$tooltip_theme_sel = ( '' !== $orderClass && '.' !== substr( $orderClass, 0, 1 ) ) ? '.' . $orderClass : $orderClass;
		if ( '' !== $arrow_color ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .tippy-box[data-theme~='{$tooltip_theme_sel}'][data-placement^='top'] > .tippy-arrow::before",
				'attr'                => $attrs['tooltip_arrow_color']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $arrow_color ) {
					return 'border-top-color: ' . esc_attr( $arrow_color ) . ' !important;';
				},
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .tippy-box[data-theme~='{$tooltip_theme_sel}'][data-placement^='bottom'] > .tippy-arrow::before",
				'attr'                => $attrs['tooltip_arrow_color']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $arrow_color ) {
					return 'border-bottom-color: ' . esc_attr( $arrow_color ) . ' !important;';
				},
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .tippy-box[data-theme~='{$tooltip_theme_sel}'][data-placement^='left'] > .tippy-arrow::before",
				'attr'                => $attrs['tooltip_arrow_color']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $arrow_color ) {
					return 'border-left-color: ' . esc_attr( $arrow_color ) . ' !important;';
				},
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .tippy-box[data-theme~='{$tooltip_theme_sel}'][data-placement^='right'] > .tippy-arrow::before",
				'attr'                => $attrs['tooltip_arrow_color']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $arrow_color ) {
					return 'border-right-color: ' . esc_attr( $arrow_color ) . ' !important;';
				},
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements",
			'attr'      => $attrs['list_item_content_max_width']['innerContent'] ?? null,
			'property'  => 'max-width',
			'important' => true,
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

	public function get_image_attachment_class( $attrs, $source_key, $attachment_id = 0 ) {
		$attachment_class = '';

		// 1.a. Find attachment ID by URL. Skip if the source key is empty.
		if ( ! empty( $source_key ) ) {
			$attachment_src = $this->props[ $source_key ];
			$attachment_id  = et_get_attachment_id_by_url( $attachment_src );
		}

		// 1.b. Generate attachment ID class.
		if ( $attachment_id > 0 ) {
			$attachment_class = "wp-image-{$attachment_id}";
		}

		return $attachment_class;
	}
}
