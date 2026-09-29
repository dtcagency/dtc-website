<?php

namespace DIFL\Server\Modules\MarqueeTextItem;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class MarqueeTextItem implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'MarqueeTextItem';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/marqueetextitem' )->customCssFields;
	}

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

		$common_styles[] = CommonStyle::style( [
			'selector'            => "div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text .df_marquee_text_icon, div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text:hover .df_marquee_text_icon",
			'attr'                => $attrs['text_icon']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return self::get_font_style( $this->props['text_icon'] );
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text span.df_marquee_text_icon, div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text:hover span.df_marquee_text_icon",
			'attr'      => $attrs['text_icon_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text .df_marquee_text_icon, div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text:hover .df_marquee_text_icon",
			'attr'                => $attrs['text_icon_size']['innerContent'] ?? null,
			'declarationFunction' => function ( $value ) {
				$value = $value['attrValue'];

				return intval( $value ) === 0 ? '' : 'font-size: ' . $value . '!important;';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text div.df_marquee_media img.df_marquee_text_img",
			'attr'      => $attrs['text_image_width']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );


		if ( 'on' === $this->props['text_clip_enable_clip'] ) {

			$common_styles[] = CommonStyle::style( [
				'selector'  => "div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text>*:not(div.df_marquee_media)",
				'attr'      => $attrs['text_clip_fill_color']['innerContent'] ?? null,
				'property'  => '-webkit-text-fill-color',
				'important' => false,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text>*:not(div.df_marquee_media)",
				'attr'      => $attrs['text_clip_stroke_color']['innerContent'] ?? null,
				'property'  => '-webkit-text-stroke-color',
				'important' => false,
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text>*:not(div.df_marquee_media)",
				'attr'      => $attrs['text_clip_stroke_width']['innerContent'] ?? null,
				'property'  => '-webkit-text-stroke-width',
				'important' => false,
			] );

			if ( $this->props['text_clip_enable_bg_clip'] === 'on' ) {
				$common_styles[] = CommonStyle::style( [
					'selector'            => "div.df_marqueetext_wrapper {$orderClass} div.df_marquee_text>*:not(div.df_marquee_media)",
					'attr'                => $attrs['text_clip_enable_bg_clip']['innerContent'] ?? null,
					'declarationFunction' => function () {
						return '-webkit-background-clip: text;';
					},
				] );
			}
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
		$text_tag  = esc_attr( $this->props['text_tag'] ? $this->props['text_tag'] : 'p' );
		$text      = ! empty( $this->props['text'] ) ? $this->props['text'] : '';
		$text_html = '' !== $text ? sprintf( '<%1$s>%2$s</%1$s>', $text_tag, $text ) : '';

		$text_image = ! empty( $this->props['text_img'] ) ? sprintf(
			'<div class="df_marquee_media">
				<img src="%1$s" alt="%2$s" class="df_marquee_text_img"/>
			</div>',
			esc_attr( $this->props['text_img'] ),
			esc_attr( $this->props['text_img_alt_txt'] )
		) : '';

		$text_icon_html = ! empty( $this->props['text_icon'] ) ?
			sprintf(
				'<div class="df_marquee_media">
					<span class="et-pb-icon df_marquee_text_icon">%1$s</span>
				</div>',
				Utils::process_font_icon( $this->props['text_icon'] )
			) : '';

		$icon_image          = 'on' === $this->props['enable_text_icon'] ? $text_icon_html : $text_image;
		$is_enable_text_clip = 'on' === $this->props['text_clip_enable_clip'] ? 'enable_text_clip' : '';

		return ! empty( $text ) || ! empty( $text_image ) || ! empty( $text_icon ) ?
			sprintf(
				'<div class="df_marquee_text %4$s">%2$s%1$s%3$s</div>',
				$text_html,
				'on' !== $this->props['set_media_after_text'] ? $icon_image : '',
				'on' === $this->props['set_media_after_text'] ? $icon_image : '',
				$is_enable_text_clip
			) : '';
	}
}