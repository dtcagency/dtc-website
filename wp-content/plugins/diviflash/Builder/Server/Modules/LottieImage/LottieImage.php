<?php

namespace DIFL\Server\Modules\LottieImage;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class LottieImage implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'Lottie';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/lottieimage' )->customCssFields;
	}

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

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

	public function render( $attr, $content, $render_slug ) {
		// https://github.com/airbnb/lottie-web
		// https://elementor.com/widgets/lottie-widget/

		wp_enqueue_script( 'df-lottie-lib' );
		wp_enqueue_script( 'df-lottie-control' );

		$path  = '';
		$props = $this->props;

		if ( $props['lottie_file_options'] !== 'media' ) {
			$path = $props['external_file'];
		} else if ( ! empty( $props['upload'] ) ) {
			$path = $props['upload'];
		}

		$data_options = [
			'path'              => $path,
			'loop'              => $props['loop'] === 'on' ? true : false,
			'speed'             => $props['speed'],
			'direction_reverse' => $props['direction_reverse'],
			'renderer'          => $props['renderer'],
			'animation_trigger' => $props['animation_trigger'],
			'threshold'         => $props['threshold'],
			'stop_on_mouse_out' => $props['stop_on_mouse_out'],
			'scroll_effect'     => $props['scroll_effect'],
		];

		return sprintf( '<div class="df-lottie-image-container" data-options=\'%1$s\'>
                <div class="df-lottie-image"></div>
            </div>',
			wp_json_encode( $data_options ) );
	}

}