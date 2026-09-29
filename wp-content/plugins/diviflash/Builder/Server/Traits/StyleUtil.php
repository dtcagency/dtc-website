<?php

namespace DIFL\Server\Traits;

use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\IconLibrary\IconFont\Utils as IconFontUtils;
use ET\Builder\Packages\StyleLibrary\Utils\StyleDeclarations;
use ET\Builder\Packages\Module\Layout\Components\Style\Utils\Utils;
use ReflectionObject;

trait StyleUtil {
	use ProcessAttrWithPreset;
	public function module_styles( $args ) {
		$elements = $args['elements'];
		$attrs    = $this->get_merged_attrs( $args );
		$attrs    = $this->processAttrWithPreset($attrs, $args['elements']->name);
		// PHP 8.2+: Divi's ModuleElements expects attrs for style(); dynamic property deprecated.
		@$elements->attrs = $attrs;
		$styles   = [];

		foreach ( $attrs as $attr_name => $attr_value ) {
			if ( ! is_array( $attr_value ) ) {
				continue;
			}

			if ( ! array_key_exists( 'decoration', $attr_value ) ) {
				continue;
			}

			$style_args = [
				'attrName' => $attr_name,
			];

			$style = $elements->style( $style_args );

			if ( empty( $style ) ) {
				continue;
			}

			foreach ( $style as $value ) {
				$styles[] = $value;
			}
		}

		if ( method_exists( $this, 'get_common_styles' ) ) {
			$styles = array_merge( $styles, $this->get_common_styles( $args ) );
		}

		$order_class      = $args['orderClass'] ?? '';
		$attr_definitions = ( isset( $elements->module_metadata->attributes ) && is_array( $elements->module_metadata->attributes ) )
			? $elements->module_metadata->attributes
			: [];
		\difl_merge_custom_decoration_background_into_flat_styles( $styles, $order_class, $attrs, $attr_definitions );

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					$styles,
				],
			] );
	}

	protected function get_merged_attrs( $args ) {
		$object     = $args['elements'];
		$reflection = new ReflectionObject( $object );
		$property   = $reflection->getProperty( '_merged_attrs' );
		$property->setAccessible( true );

		return $property->getValue( $object );
	}

	protected static function build_attr_for_styles( $attrs ) {
		if ( empty( $attrs ) || ! is_array( $attrs ) ) {
			return $attrs;
		}

		$devices = Breakpoint::get_style_breakpoint_settings();

		$new_attrs = [];

		foreach ( $attrs as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			if ( ! array_key_exists( 'innerContent', $value ) ) {
				continue;
			}

			if ( ! $value['innerContent'] ) {
				continue;
			}
			if ( empty( $value['innerContent']['desktop'] ) ) {
				continue;
			}
			if ( empty( $value['innerContent']['desktop']['value'] ) || ! is_array( $value['innerContent']['desktop']['value'] ) ) {
				continue;
			}
			if ( count( $value['innerContent']['desktop']['value'] ) === 1 && array_keys( $value['innerContent']['desktop']['value'] )[0] === $key ) {
				continue;
			}

			$is_checkboxes = count( array_filter( array_keys( $value['innerContent']['desktop']['value'] ), 'is_numeric' ) ) === count( $value['innerContent']['desktop']['value'] );

			if ( $is_checkboxes ) {
				continue;
			}

			foreach ( $devices as $device => $breakpoint ) {
				if ( ! array_key_exists( $device, $value['innerContent'] ) ) {
					continue;
				}

				foreach ( $value['innerContent'][ $device ]['value'] as $sub_key => $sub_value ) {
					$new_attrs[ $sub_key ]['innerContent'][ $device ]['value'] = $sub_value;
				}

				if ( 'desktop' === $device && array_key_exists( 'hover', $value['innerContent'][ $device ] ) ) {
					foreach ( $value['innerContent'][ $device ]['hover'] as $sub_key => $sub_value ) {
						$new_attrs[ $sub_key ]['innerContent'][ $device ]['hover'] = $sub_value;
					}
				}

				if ( 'desktop' === $device && array_key_exists( 'sticky', $value['innerContent'][ $device ] ) ) {
					foreach ( $value['innerContent'][ $device ]['sticky'] as $sub_key => $sub_value ) {
						$new_attrs[ $sub_key ]['innerContent'][ $device ]['sticky'] = $sub_value;
					}
				}
			}

		}

		return array_merge( $attrs, $new_attrs );
	}

	protected function get_default_value_for_styles( $attrs ) {
		if ( empty( $attrs ) ) {
			return $attrs;
		}

		foreach ( $attrs as $key => $value ) {
			if ( is_null( $value ) || is_string( $value ) ) {
				continue;
			}

			if ( ! is_array( $value ) ) {
				continue;
			}

			if ( ! array_key_exists( 'innerContent', $value ) ) {
				continue;
			}

			$desktop = $value['innerContent']['desktop'] ?? [];
			// Do not treat 0 / '0' as empty (same as HasProps::set_props).
			if ( ! array_key_exists( 'value', $desktop ) ) {
				$attrs[ $key ]['innerContent']['desktop']['value'] = $this->props[ $key ];
				continue;
			}
			$content = $desktop['value'];
			if ( $content === null || $content === '' ) {
				$attrs[ $key ]['innerContent']['desktop']['value'] = $this->props[ $key ];
			}
		}

		if ( isset( $this->attributes ) ) {
			foreach ( $this->attributes as $key => $value ) {
				if ( ! isset( $value['default_value'] ) ) {
					continue;
				}

				if ( isset( $value['default_value']['innerContent']['desktop']['value'] ) ) {
					$desktopValue = $value['default_value']['innerContent']['desktop']['value'];

					if ( ! is_array( $desktopValue ) ) {
						if ( ! isset( $attrs[ $key ]['innerContent']['desktop']['value'] ) ) {
							$attrs[ $key ]['innerContent']['desktop']['value'] = $desktopValue;
						}
					} else {
						$is_checkboxes = count( array_filter( array_keys( $desktopValue ), 'is_numeric' ) ) === count( $desktopValue );
						if ( $is_checkboxes ) {
							$attrs[ $key ]['innerContent']['desktop']['value'] = $desktopValue;
							continue;
						}

						foreach ( $desktopValue as $inner_key => $inner_value ) {
							if ( ! isset( $attrs[ $inner_key ]['innerContent']['desktop']['value'] ) ) {
								$attrs[ $inner_key ]['innerContent']['desktop']['value'] = $inner_value;
							}
						}
					}
				}
			}
		}

		return $attrs;
	}

	/**
	 * Build font style declarations for an icon (font-family, font-weight, content).
	 * Mirrors Divi BlurbModule::icon_style_declaration() so icon font is loaded.
	 *
	 * @param array|string $icon Icon attribute: array with type, unicode, weight (D5) or legacy string e.g. "||fa||\uf00c".
	 * @return string CSS declaration string.
	 */
	protected static function get_font_style( $icon ) {
		$style_declarations = new StyleDeclarations(
			[
				'returnType' => 'string',
				'important'  => [
					'font-family' => true,
				],
			]
		);

		// Normalize legacy string (e.g. "||fa||\uf00c") so font-family is always set.
		if ( is_string( $icon ) ) {
			$font_family = ( strpos( $icon, '||fa||' ) !== false ) ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add( 'font-family', $font_family );
			return $style_declarations->value();
		}

		if ( ! is_array( $icon ) ) {
			return $style_declarations->value();
		}

		if ( isset( $icon['type'] ) ) {
			$font_family = 'fa' === $icon['type'] ? 'FontAwesome' : 'ETmodules';
			$style_declarations->add( 'font-family', $font_family );
		}

		if ( ! empty( $icon['weight'] ) ) {
			$style_declarations->add( 'font-weight', "{$icon['weight']}!important" );
		}

		if ( ! empty( $icon['unicode'] ) ) {
			$font_icon = IconFontUtils::escape_font_icon( IconFontUtils::process_font_icon( $icon ) );
			if ( $font_icon ) {
				$style_declarations->add( 'content', "'" . $font_icon . "'" );
			}
		}

		return $style_declarations->value();
	}
}
