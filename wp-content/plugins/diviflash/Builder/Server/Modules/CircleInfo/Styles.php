<?php

namespace DIFL\Server\Modules\CircleInfo;

if (!defined('ABSPATH')) {
	die('Direct access forbidden.');
}

use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

trait Styles
{

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered('difl/circleinfo')->customCssFields;
	}

	public static function styles($args)
	{
		$attrs        = $args['attrs'] ?? [];
		$parent_attrs = $args['parentAttrs'] ?? [];
		$order_class  = $args['orderClass'];
		$elements     = $args['elements'];
		$settings     = $args['settings'] ?? [];

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					// Module
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'disabledOn' => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
								'background' => [
									'featureSelectors' => [
										'mask' => [
											'desktop' => [
												'value' => "{$order_class} .et_pb_background_mask",
											],
										],
										'pattern' => [
											'desktop' => [
												'value' => "{$order_class} .et_pb_background_pattern",
											],
										],
									],
								],
							],
						]
					),

					CssStyle::style(
						[
							'selector'  => $args['orderClass'],
							'attr'      => $attrs['css'] ?? [],
							'cssFields' => self::custom_css(),
						]
					),

					// Circle size
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df_circle_info_circle",
							'attr'     => $attrs['circle_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'circle_size_style'],
						]
					),

					// Center area size
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df_circle_info_center",
							'attr'     => $attrs['circle_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'center_area_size_style'],
						]
					),

					// Icon container size and bg
					CommonStyle::style(
						[
							'selector'  => "{$order_class} .df_circle_info_icon",
							'selectors' => [
								'desktop' => [
									'value' => "{$order_class} .df_circle_info_icon",
									'hover' => "{$order_class} .df_circle_info_icon:hover",
								],
							],
							'attr'     => $attrs['item_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'icon_container_style'],
						]
					),

					// Icon font size and color
					CommonStyle::style(
						[
							'selector'  => "{$order_class} .df_circle_info_icon .et-pb-icon",
							'selectors' => [
								'desktop' => [
									'value' => "{$order_class} .df_circle_info_icon .et-pb-icon",
									'hover' => "{$order_class} .df_circle_info_icon:hover .et-pb-icon",
								],
							],
							'attr'     => $attrs['item_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'icon_style'],
						]
					),

					// Icon image size and fit
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df_circle_info_icon img",
							'attr'     => $attrs['item_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'icon_image_style'],
						]
					),

					// Icon active background
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl_circleinfoitem.active .df_circle_info_icon",
							'attr'     => $attrs['item_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'icon_active_bg_style'],
						]
					),

					// Icon active color
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl_circleinfoitem.active .df_circle_info_icon .et-pb-icon",
							'attr'     => $attrs['item_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'icon_active_color_style'],
						]
					),

					// Icon text active color
					CommonStyle::style(
						[
							'selector' => "{$order_class} .difl_circleinfoitem.active .df_circle_info_icon .df_ci_icon_text",
							'attr'     => $attrs['item_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'icon_text_active_color_style'],
						]
					),

					// Social icon container
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df_ci_social_icon",
							'attr'     => $attrs['social_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'social_icon_container_style'],
						]
					),

					// Social icon font
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df_ci_social_icon i:before",
							'attr'     => $attrs['social_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'social_icon_font_style'],
						]
					),

					// Social links gap
					CommonStyle::style(
						[
							'selector' => "{$order_class} .df_ci_social_links",
							'attr'     => $attrs['social_icon_settings']['innerContent'] ?? [],
							'declarationFunction' => [self::class, 'social_links_gap_style'],
						]
					),

					// Decoration styles
					$elements->style(['attrName' => 'center_title']),
					$elements->style(['attrName' => 'center_description']),
					$elements->style(['attrName' => 'circle_border_design']),
					$elements->style(['attrName' => 'icon_area_design']),
					$elements->style(['attrName' => 'icon_text_font']),
					$elements->style(['attrName' => 'center_area_design']),
					$elements->style(['attrName' => 'center_button']),
				],
			]
		);
	}

	public static function circle_size_style(array $args): string
	{
		$size = $args['attrValue']['circle_size'] ?? '';
		if ($size) {
			return "width: {$size};";
		}
		return '';
	}

	public static function center_area_size_style(array $args): string
	{
		$size = $args['attrValue']['center_area_size'] ?? '';
		if ($size) {
			return "width: {$size};";
		}
		return '';
	}

	public static function icon_container_style(array $args): string
	{
		$style = '';
		$size = $args['attrValue']['icon_container_size'] ?? '';
		$bg = $args['attrValue']['icon_bg_color'] ?? '';

		if ($size) $style .= "width: {$size}; height: {$size};";
		if ($bg) $style .= "background-color: {$bg};";

		return $style;
	}

	public static function icon_style(array $args): string
	{
		$style = '';
		$size = $args['attrValue']['icon_size'] ?? '';
		$color = $args['attrValue']['icon_color'] ?? '';

		if ($size) $style .= "font-size: {$size};";
		if ($color) $style .= "color: {$color};";

		return $style;
	}

	public static function icon_image_style(array $args): string
	{
		$style = '';
		$width = $args['attrValue']['image_width'] ?? '';
		$fit = $args['attrValue']['image_fit'] ?? 'contain';

		if ($width) $style .= "width: {$width}; height: {$width};";
		$style .= "object-fit: {$fit};";

		return $style;
	}

	public static function icon_active_bg_style(array $args): string
	{
		$style = '';
		$bg = $args['attrValue']['icon_active_bg_color'] ?? '';
		$border = $args['attrValue']['icon_active_border_color'] ?? '';
		if ($bg) $style .= "background-color: {$bg};";
		if ($border) $style .= "border-color: {$border};";
		return $style;
	}

	public static function icon_active_color_style(array $args): string
	{
		$color = $args['attrValue']['icon_active_color'] ?? '';
		return $color ? "color: {$color};" : '';
	}

	public static function icon_text_active_color_style(array $args): string
	{
		$color = $args['attrValue']['icon_text_active_color'] ?? '';
		return $color ? "color: {$color};" : '';
	}

	public static function social_icon_container_style(array $args): string
	{
		$style = '';
		$icon_style = $args['attrValue']['social_icon_style'] ?? 'circle';
		$size = $args['attrValue']['social_icon_size'] ?? '';
		$bg = $args['attrValue']['social_icon_bg_color'] ?? '#333333';

		$is_outline = in_array($icon_style, ['outline_circle', 'outline_square', 'outline_rounded'], true);
		$is_flat = $icon_style === 'flat';

		// Background
		if ($is_outline || $is_flat) {
			$style .= 'background-color: transparent;';
		} elseif ($bg) {
			$style .= "background-color: {$bg};";
		}

		// Border for outline
		if ($is_outline && $bg) {
			$style .= "border: 2px solid {$bg};";
		}

		// Border radius from preset
		if (in_array($icon_style, ['circle', 'outline_circle'], true)) {
			$style .= 'border-radius: 50%;';
		} elseif (in_array($icon_style, ['square', 'outline_square'], true)) {
			$style .= 'border-radius: 0;';
		} elseif (in_array($icon_style, ['rounded', 'outline_rounded'], true)) {
			$style .= 'border-radius: 8px;';
		}

		// Size
		if ($size) {
			$container = "calc({$size} * 2.25)";
			$style .= "width: {$container}; height: {$container};";
		}

		if ($is_flat) {
			$style .= 'width: auto; height: auto;';
		}

		return $style;
	}

	public static function social_icon_font_style(array $args): string
	{
		$style = '';
		$icon_style = $args['attrValue']['social_icon_style'] ?? 'circle';
		$size = $args['attrValue']['social_icon_size'] ?? '';
		$color = $args['attrValue']['social_icon_color'] ?? '';
		$bg = $args['attrValue']['social_icon_bg_color'] ?? '#333333';

		$is_outline = in_array($icon_style, ['outline_circle', 'outline_square', 'outline_rounded'], true);

		if ($size) $style .= "font-size: {$size};";

		if ($is_outline && $bg) {
			$style .= "color: {$bg};";
		} elseif ($color) {
			$style .= "color: {$color};";
		}

		return $style;
	}

	public static function social_links_gap_style(array $args): string
	{
		$gap = $args['attrValue']['social_icon_spacing'] ?? '';
		return $gap ? "gap: {$gap};" : '';
	}
}
