<?php

namespace DIFL\D5\Helper;

use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;

class Styles {

	/**
	 * Generates custom CSS styles based on provided properties and attributes.
	 *
	 * @param array $props {
	 *     An associative array of properties for generating custom styles.
	 *
	 *     @type string $selector               The CSS selector where the styles will be applied.
	 *     @type string $attr                   The attribute name for fetching style values dynamically.
	 *     @type string $property               The CSS property to be set.
	 *     @type string $value                  The value for the specified CSS property (optional).
	 *     @type array  $staticPropertyAndValue Static CSS property-value pairs (optional).
	 *     @type bool   $important              Whether to append `!important` to the style declaration (optional).
	 * }
	 *
	 * @return array|string CSS declarations as a string or an array based on the provided properties.
	 */
	// phpcs:ignore -- PHP 8.0+ union return type; PHPCS/standard may target PHP 7.4 parser.
	public static function custom_style( array $props ): array|string {
		// Extract props
		$selector               = $props['selector'] ?? '';
		$attr                   = $props['attr'] ?? '';
		$property               = $props['property'] ?? '';
		$value                  = $props['value'] ?? '';
		$staticPropertyAndValue = $props['staticPropertyAndValue'] ?? [];
		$important              = $props['important'] ?? false;

		// Custom declaration function
		$customDeclarationFunction = function ( $declaretionProps ) use ( $property, $value, $staticPropertyAndValue, $important ) {
			$style_declarations = new StyleDeclarations(
				[
					'returnType' => 'string',
					'important'  => $important,
				]
			);

			// Add static property-value pairs
			foreach ( $staticPropertyAndValue as $key => $val ) {
				$style_declarations->add( $key, $val );
			}

			// Add dynamic property-value pair
			if ( ! empty( $property ) ) {
				$styleValue = ! empty( $value ) ? $value : ( $declaretionProps['attrValue'] ?? '' );
				$style_declarations->add( $property, $styleValue );
			}

			return $style_declarations->value();
		};

		return CommonStyle::style(
			[
				'selector'            => $selector,
				'attr'                => $attr,
				'declarationFunction' => $customDeclarationFunction,
				'important'           => $important
			]
		);

	}

	/**
	 * Generates tooltip styles for different arrow directions and additional customizations.
	 *
	 * @param array $args {
	 *     An associative array containing tooltip attributes, elements, and settings.
	 *
	 *     @type array  $attrs    CSS attributes for decoration settings, including arrow color.
	 *     @type object $elements An object with methods for generating tooltip-related style declarations.
	 *     @type array  $settings Additional customization settings (optional).
	 *     @type string $orderClass The class name used to target a specific tooltip theme.
	 * }
	 *
	 * @return array|string An array of CSS declarations for tooltip styles, including arrow colors and padding.
	 */
	public static function tooltip_style( array $args ): array {
		$attrs    = $args['attrs'] ?? [];
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];
		$main_class = $args['orderClass'];

		$result = [
			// Design Tooltip.
			$elements->style( [
				'attrName' => 'design_tooltip',
			] ),

			// Arrow Color
			CommonStyle::style( [
				'selector' => ".tippy-box[data-theme~='" . $args['orderClass'] . "'][data-placement^='top'] > .tippy-arrow::before",
				'attr'     => $attrs['design_tooltip']['decoration']['field_tooltip_arrow_color'] ?? [],
				'property' => 'border-top-color',
			] ),
			CommonStyle::style( [
				'selector' => ".tippy-box[data-theme~='" . $args['orderClass'] . "'][data-placement^='bottom'] > .tippy-arrow::before",
				'attr'     => $attrs['design_tooltip']['decoration']['field_tooltip_arrow_color'] ?? [],
				'property' => 'border-bottom-color',
			] ),
			CommonStyle::style( [
				'selector' => ".tippy-box[data-theme~='" . $args['orderClass'] . "'][data-placement^='right'] > .tippy-arrow::before",
				'attr'     => $attrs['design_tooltip']['decoration']['field_tooltip_arrow_color'] ?? [],
				'property' => 'border-right-color',
			] ),
			CommonStyle::style( [
				'selector' => ".tippy-box[data-theme~='" . $args['orderClass'] . "'][data-placement^='left'] > .tippy-arrow::before",
				'attr'     => $attrs['design_tooltip']['decoration']['field_tooltip_arrow_color'] ?? [],
				'property' => 'border-left-color',
			] ),
			self::custom_style( [
				'attr'     => $attrs['bg_hover_background_color']['decoration'] ?? [],
				'selector' => ".tippy-box[data-theme~='" . $args['orderClass'] . "'] .tippy-content p",
				'property' => "padding-bottom",
				'value'    => "0px"
			] ),

			// Tooltip Text
			$elements->style( [ 'attrName' => 'design_tooltip_text', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_text_body', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_text_a', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_text_ul', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_text_ol', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_text_quota', ] ),

			// Tooltip Sub Text
			$elements->style( [
				'attrName' => 'design_tooltip_header',
				] ),
			$elements->style( [ 'attrName' => 'design_tooltip_header_h1', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_header_h2', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_header_h3', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_header_h4', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_header_h5', ] ),
			$elements->style( [ 'attrName' => 'design_tooltip_header_h6', ] ),
		];

		return $result;

	}

}
