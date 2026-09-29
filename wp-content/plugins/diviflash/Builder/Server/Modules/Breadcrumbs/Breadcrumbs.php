<?php

namespace DIFL\Server\Modules\Breadcrumbs;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class Breadcrumbs implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'Breadcrumbs';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/breadcrumbs' )->customCssFields;
	}

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} ul.df-breadcrumbs",
			'attr'      => $attrs['alignment']['innerContent'] ?? null,
			'property'  => 'justify-content',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df-breadcrumbs-item.df-breadcrumbs-start .df-breadcrumbs-home-icon .df-home-icon, {$orderClass} .df-breadcrumbs-item.df-breadcrumbs-start:hover .df-breadcrumbs-home-icon .df-home-icon",
			'attr'      => $attrs['home_icon_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .et-pb-icon.df-home-icon",
			'attr'      => $attrs['home_icon_font_size']['innerContent'] ?? null,
			'property'  => 'font-size',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .et-pb-icon.df-home-icon",
			'attr'                => $attrs['home_font_icon']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return self::get_font_style( $this->props['home_font_icon'] ?? '' );
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df-breadcrumbs-item .et-pb-icon.df-inner-icon, {$orderClass} .df-breadcrumbs-item:hover .et-pb-icon.df-inner-icon",
			'attr'      => $attrs['inner_icon_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .et-pb-icon.df-inner-icon",
			'attr'      => $attrs['inner_icon_font_size']['innerContent'] ?? null,
			'property'  => 'font-size',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .et-pb-icon.df-inner-icon",
			'attr'      => $attrs['inner_icon_spacing']['innerContent'] ?? null,
			'property'  => 'margin-left',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .et-pb-icon.df-inner-icon",
			'attr'                => $attrs['inner_icon']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return self::get_font_style( $this->props['inner_icon'] ?? '' );
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .et-pb-icon.df-separator-icon",
			'attr'      => $attrs['separator_icon_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .df-breadcrumbs-separator .df-breadcrumbs-separator-icon",
			'attr'      => $attrs['separator_icon_font_size']['innerContent'] ?? null,
			'property'  => 'font-size',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .et-pb-icon.df-separator-icon",
			'attr'                => $attrs['separator_font_icon']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return self::get_font_style( $this->props['separator_font_icon'] ?? '' );
			},
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

	public function render( $attrs, $content, $block, $elements = null ) {
		$settings            = $this->props;
		$home_font_icon_default = [
			"unicode" => "&#xe074;",
			"type" => "divi",
			"weight" => 400,
		];
		$home_font_icon      = $settings['home_font_icon'] ?? $home_font_icon_default;
		$home_icon           = '';
		$home_icon           = $settings['use_home_icon'] === 'on' ?
			sprintf( '<span class="et-pb-icon df-home-icon">%1$s</span>',
				esc_attr( Utils::process_font_icon( $home_font_icon ) )
			) : '';
		$home_icon_placement = $settings['home_icon_placement'] !== '' ? $settings['home_icon_placement'] : 'left';

		$separator_font_icon_default = [
			"unicode" => "&#x39;",
			"type" => "divi",
			"weight" => 400,
		];
		$separator_font_icon = $settings['separator_font_icon'] ?? $separator_font_icon_default;
		$separator           = '';

		// Use raw saved attributes: empty string and null/unset both show no separator; only print when key exists with non-empty value.
		$raw_attrs           = isset( $block->parsed_block['attrs'] ) && is_array( $block->parsed_block['attrs'] ) ? $block->parsed_block['attrs'] : [];
		$separator_text_value = '';
		if ( array_key_exists( 'separator_text', $raw_attrs ) ) {
			$raw = $raw_attrs['separator_text'];
			$separator_text_value = is_string( $raw )
				? $raw
				: (string) ( $raw['innerContent']['desktop']['value'] ?? '' );
		}
		$separator_text_value = trim( $separator_text_value );

		$home_text_value = '';
		if ( array_key_exists( 'home_text', $raw_attrs ) ) {
			$raw = $raw_attrs['home_text'];
			$home_text_value = is_string( $raw )
				? $raw
				: (string) ( $raw['innerContent']['desktop']['value'] ?? '' );
		}
		$home_text_value = trim( $home_text_value );

		if ( 'on' === $settings['use_separator_icon'] ) {
			$icon      = sprintf( '<span class="et-pb-icon df-separator-icon">%1$s</span>',
				esc_attr( Utils::process_font_icon( $separator_font_icon ) )
			);
			$class     = 'df-breadcrumbs-separator-icon';
			$separator = sprintf( '<span class="%2$s"> %1$s </span>', $icon, $class );
		} elseif ( 'off' === $settings['use_separator_icon'] && $separator_text_value !== '' ) {
			$class     = 'df-breadcrumbs-separator-text';
			$separator = sprintf( '<span class="%2$s"> %1$s </span>', esc_html( $separator_text_value ), $class );
		}

		$inner_font_icon      = $settings['inner_icon'] ?? $separator_font_icon_default;
		$inner_icon      = '';
		$inner_icon      = $settings['use_icon_inner_item'] !== 'off' ?
			sprintf( '<span class="et-pb-icon df-inner-icon">%1$s</span>',
				esc_attr( Utils::process_font_icon( $inner_font_icon ) )
			) : '';
		$labels          = [
			'home'               => $home_text_value !== '' ? esc_html( $home_text_value ) : '',
			'enable_custom_page' => isset( $settings['enable_custom_page'] ) ? esc_attr( $settings['enable_custom_page'] ) : 'off',
			'page_title'         => isset( $settings['page_title'] ) && $settings['page_title'] !== '' && $settings['enable_custom_page'] === 'on' ? esc_html( $settings['page_title'] ) : '',

			'use_page_custom_url'    => isset( $settings['use_page_custom_url'] ) ? esc_attr( $settings['use_page_custom_url'] ) : 'off',
			'page_custom_url'        => isset( $settings['page_custom_url'] ) ? esc_url( $settings['page_custom_url'] ) : '',
			'page_custom_url_target' => isset( $settings['page_custom_url_target'] ) ? esc_attr( $settings['page_custom_url_target'] ) : 'same_window',
			'search'                 => isset( $settings['search_title'] ) ? esc_html( $settings['search_title'] ) . ' %s' : '%s',
			'error_404'              => isset( $settings['error_404_title'] ) ? esc_html( $settings['error_404_title'] ) : '',
		];

		$args = [
			'list_class'          => 'df-breadcrumbs',
			'item_class'          => 'df-breadcrumbs-item',
			'separator'           => $separator,
			'separator_class'     => 'df-breadcrumbs-separator',
			'home_icon'           => $home_icon,
			'home_icon_placement' => $home_icon_placement,
			'home_icon_class'     => 'df-breadcrumbs-home-icon',
			'labels'              => $labels,
			'use_icon_inner_item' => $settings['use_icon_inner_item'],
			'inner_icon'          => $inner_icon,
			'show_on_front'       => 'on' === $settings['show_on_front_page'] ? true : false,
			'show_title'          => 'on' === $settings['show_title'] ? true : false,
		];

		if ( ! class_exists( '\Df_Breadcrumb' ) ) {
			require_once( DIFL_MAIN_DIR . '/includes/classes/df-class-breadcrumbs.php' );
		}

		$breadcrumb = new \Df_Breadcrumb( $args );

		$schema_script = '';
		if ( $this->props['enable_schema'] === 'on' ) {
			$schema_script = sprintf( '<script type="application/ld+json">%1$s</script>', wp_json_encode( $breadcrumb->trail()['schema'] ) );
		}

		return sprintf( '<div class="df_breadcrumbs_container">
                             %2$s
                            <div class="df_breadcrumbs_wrapper">
                                %1$s
                            </div>

                        </div>',
			$breadcrumb->trail()['breadcrumb'],
			$schema_script
		);
	}
}