<?php

namespace DIFL\Server\Modules\SVGAnimator;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class SVGAnimator implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'SVGAnimator';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/svganimator' )->customCssFields;
	}

	public function get_settings_modal_toggles() {
		return [
			'general'  => [
				'toggles' => [
					'content'   => esc_html__( 'Element ', 'divi_flash' ),
					'animation' => esc_html__( 'Animation', 'divi_flash' ),
				],
			],
			'advanced' => [
				'toggles' => [
				],
			],
		];
	}

	public function get_advanced_fields_config() {
		$advanced_fields                = [];
		$advanced_fields['text']        = false;
		$advanced_fields['text_shadow'] = false;
		$advanced_fields['fonts']       = false;

		return $advanced_fields;
	}

	public static function add_prefix_svg_selector( $svg_html, $render_slug ) {
//		$order_class = self::get_module_order_class( $render_slug ) ?: 'difl_svg_animator_unknown';
		$order_class = $render_slug;
		$prefix      = '.' . $order_class . ' ';

		if ( preg_match_all( '/<style (.*?)>(.*?)<\/style>/s', $svg_html, $style_codes ) ) {
			foreach ( $style_codes[2] as $style_code ) {
				$prefixed_styles = '';

				preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $style_code, $rules );
				foreach ( $rules[1] as $i => $selector ) {
					$selectors       = array_map(
						fn( $sel ) => $prefix . trim( $sel ),
						explode( ',', trim( $selector ) )
					);
					$prefixed_styles .= implode( ',', $selectors ) . '{' . trim( $rules[2][ $i ] ) . "}\r\n";
				}

				$svg_html = str_replace( $style_code, $prefixed_styles, $svg_html );
			}
		}

		return $svg_html;
	}

	public static function render_svg_animator( $args, $render_slug ) {
		$svg_src              = array_key_exists( 'src', $args ) && ! empty( $args['src'] ) ? $args['src'] : '';
		$svg_color            = array_key_exists( 'svg_color', $args ) && ! empty( $args['svg_color'] ) ? $args['svg_color'] : '';
		$svg_weight           = array_key_exists( 'svg_weight', $args ) && ! empty( $args['svg_weight'] ) ? $args['svg_weight'] : '';
		$animation_type       = array_key_exists( 'animation_type', $args ) && ! empty( $args['animation_type'] ) ? $args['animation_type'] : '';
		$animation_delay      = array_key_exists( 'delay_animation', $args ) && ! empty( $args['delay_animation'] ) ? $args['delay_animation'] : '';
		$animation_duration   = array_key_exists( 'duration_animation', $args ) && ! empty( $args['duration_animation'] ) ? $args['duration_animation'] : '';
		$path_timing_function = array_key_exists( 'path_timing_func', $args ) && ! empty( $args['path_timing_func'] ) ? $args['path_timing_func'] : '';
		$anim_timing_function = array_key_exists( 'animation_timing_func', $args ) && ! empty( $args['animation_timing_func'] ) ? $args['animation_timing_func'] : '';
		$animation_start      = array_key_exists( 'animation_start', $args ) && ! empty( $args['animation_start'] ) ? $args['animation_start'] : '';
		$replay_enable        = array_key_exists( 'enable_replay', $args ) && ! empty( $args['enable_replay'] ) ? $args['enable_replay'] : '';
		$repeat_type          = array_key_exists( 'replay_type', $args ) && ! empty( $args['replay_type'] ) ? $args['replay_type'] : '';
		$is_loop              = array_key_exists( 'enable_loop', $args ) && ! empty( $args['enable_loop'] ) ? $args['enable_loop'] : '';
		$loop_infinite        = array_key_exists( 'loop_infinite', $args ) && ! empty( $args['loop_infinite'] ) ? $args['loop_infinite'] : '';
		$iteration_number     = array_key_exists( 'loop_time', $args ) && ! empty( $args['loop_time'] ) ? $args['loop_time'] : '';

//		$sa_svg_id = self::get_module_order_class( $render_slug );
		$sa_svg_id = $render_slug;
		$config    = [];
		if ( $animation_type != 'none' ) {
			$config = [
				'svg_id'             => $sa_svg_id,
				'type'               => ( $animation_type != '' ? $animation_type : 'delayed' ),
				'delay'              => $animation_delay,
				'duration'           => ( $animation_duration != '' ? $animation_duration : '200' ),
				'start'              => ( $animation_start != '' ? $animation_start : 'autostart' ),
				'pathTimingFunction' => ( $path_timing_function != '' ? $path_timing_function : 'linear' ),
				'animTimingFunction' => ( $anim_timing_function != '' ? $anim_timing_function : 'linear' ),
				'replay_enable'      => $replay_enable,
				'repeat_type'        => ( $repeat_type != '' ? $repeat_type : 'click' ),
				'loop_enabled'       => ( $is_loop != '' ? $is_loop : 'off' ),
				'loop_infinite'      => ( $loop_infinite != '' ? $loop_infinite : 'off' ),
				'iteration_number'   => ( $iteration_number != '' ? $iteration_number : '1' ),
			];
		}

		$svg_color   = ( $svg_color != '' && $svg_color != '#' ? $svg_color : '#000000' );
		$svg_content = '';

		if ( $svg_src ) {
			$svg_content = file_get_contents( $svg_src );
			if ( ! $svg_content ) {
				$response    = wp_remote_get( $svg_src );
				$svg_content = wp_remote_retrieve_body( $response );
			}
			$svg_attributes = 'fill="none" stroke-width="' . $svg_weight . '" stroke="' . $svg_color . '"';
			$validators     = [
				'<svg'            => '<svg id="difl-svg-animator-' . $sa_svg_id . '"',
				'<ellipse'        => '<ellipse ' . $svg_attributes,
				'<rect'           => '<rect ' . $svg_attributes,
				'<circle'         => '<circle ' . $svg_attributes,
				'<polygon'        => '<polygon ' . $svg_attributes,
				'<polyline'       => '<polyline ' . $svg_attributes,
				'<defs'           => '<defs ' . $svg_attributes,
				'<linearGradient' => '<linearGradient ' . $svg_attributes,
				'<path'           => '<path ' . $svg_attributes,
				'style="'         => 'style="fill:none!important;',
			];

			foreach ( $validators as $key => $value ) {
				$svg_content = str_replace( $key, $value, $svg_content );
			}
		}

		$svg_content = self::add_prefix_svg_selector( $svg_content, $render_slug );

		$svg_animator = sprintf(
			'<div class="difl-svg-animator-inner-wrapper" data-config=\'%1$s\'>
                %2$s
            </div>',
			wp_json_encode( $config ),
			$svg_content
		);

		return $svg_animator;
	}

	public function render( $attrs, $content, $render_slug ) {
		wp_enqueue_script( 'df_svg_animator' );
//		$this->render_style( $render_slug );

		$svg_animator = $this->render_svg_animator( $this->props->all(), $this->order_class );

		$output = sprintf(
			'<div class="difl-svg-animator-container" style="opacity: 0;">
                %1$s
            </div>',
			$svg_animator
		);

		return $output;
	}

	/**
	 * Get attribute with default value fallback for CommonStyle.
	 * Ensures desktop value exists when responsive values are set.
	 *
	 * @param array       $attr         The attribute innerContent array.
	 * @param string|null $default      The default value to use.
	 * 
	 * @return array The attribute with default value ensured.
	 */
	private function get_attr_with_default( $attr, $default = null ) {
		// If attr is empty, return with default if provided
		if ( empty( $attr ) ) {
			if ( $default !== null ) {
				return [
					'desktop' => [
						'value' => $default,
					],
				];
			}
			return [];
		}

		// If desktop value doesn't exist but other breakpoints do, add desktop default
		if ( ! isset( $attr['desktop']['value'] ) && $default !== null ) {
			$attr['desktop'] = [
				'value' => $default,
			];
		}

		return $attr;
	}

	public function get_common_styles( $args ) {
		$attrs         = $this->get_merged_attrs( $args );
		$attrs         = self::build_attr_for_styles( $attrs );
		$attrs         = $this->get_default_value_for_styles( $attrs );
		$orderClass    = $args['orderClass'] ?? '';
		$styles        = [];
		$common_styles = [];

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .difl-svg-animator-container svg *",
			'attr'      => $this->get_attr_with_default( $attrs['svg_color']['innerContent'] ?? [], '#000000' ),
			'property'  => "stroke",
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .difl-svg-animator-container svg *",
			'attr'      => $this->get_attr_with_default( $attrs['svg_weight']['innerContent'] ?? [], '2px' ),
			'property'  => "stroke-width",
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .difl-svg-animator-container svg",
			'attr'      => $this->get_attr_with_default( $attrs['svg_width']['innerContent'] ?? [], '100%' ),
			'property'  => "width",
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .difl-svg-animator-container svg",
			'attr'      => $this->get_attr_with_default( $attrs['svg_height']['innerContent'] ?? [], '100%' ),
			'property'  => "height",
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .difl-svg-animator-inner-wrapper",
			'attr'      => $this->get_attr_with_default( $attrs['svg_alignment']['innerContent'] ?? [], 'center' ),
			'property'  => "justify-content",
			'important' => true,
		] );

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
}