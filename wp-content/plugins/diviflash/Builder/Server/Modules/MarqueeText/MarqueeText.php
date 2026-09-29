<?php

namespace DIFL\Server\Modules\MarqueeText;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class MarqueeText implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'MarqueeText';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/marqueetext' )->customCssFields;
	}

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

		$common_styles[] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_marqueetext_wrapper .df_marquee_text .df_marquee_text_icon, {$orderClass} .df_marqueetext_wrapper .df_marquee_text:hover .df_marquee_text_icon",
			'attr'     => $attrs['text_icon_color']['innerContent'] ?? null,
			'property' => 'color',
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_marqueetext_wrapper .df_marquee_text .df_marquee_text_icon, {$orderClass} .df_marqueetext_wrapper .df_marquee_text:hover .df_marquee_text_icon",
			'attr'      => $attrs['text_icon_size']['innerContent'] ?? null,
			'property'  => 'font-size',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_marquee_list",
			'attr'      => $attrs['ticker_gap']['innerContent'] ?? null,
			'property'  => 'gap',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df_marqueetext_wrapper .df_marquee_text .df_marquee_media .df_marquee_text_img",
			'attr'      => $attrs['text_image_width']['innerContent'] ?? null,
			'property'  => 'width',
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

	public function render( $attrs, $content, $render_slug ) {
		wp_enqueue_script( 'df-marquee-script' );
		wp_enqueue_script( 'df_marquee_text' );

		$data = [
			'ticker_hover'     => 'on' === $this->props['ticker_hover'],
			'ticker_speed'     => $this->props['ticker_speed'],
			'ticker_direction' => $this->props['ticker_direction'],
			'ticker_loop'      => 'on' === $this->props['ticker_loop'],
			'ticker_gap'       => $this->props['ticker_gap'],
		];

		return ! empty( $content ) ? sprintf(
			'<div class="df_marqueetext_wrapper" data-settings=\'%2$s\'>
            <div class="df_marquee_animation">
            	<div class="df_marquee_list">
                  %1$s
               </div>
            </div>
         </div>',
			et_core_sanitized_previously( $content ),
			wp_json_encode( $data )
		) : "<h2 class='df_marquee_notice'>Please <strong>Add New Item.</strong></h2>";
	}
}
