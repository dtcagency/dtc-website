<?php
/**
 * AdvancedMenuItem Module class.
 *
 * @package DIVIFLASH5\Modules\AdvancedMenuItem;
 */

namespace DIFL\Server\Modules\AdvancedMenuItem;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access forbidden.' );
}

use DIFL\Server\Traits\GetPropsTrait;
use DIFL\Server\Traits\UnifiedStyleRenderer;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\Module\Options\Text\TextClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;


/**
 * Class AdvancedMenuItem
 *
 * @package DIVIFLASH5\Modules\AdvancedMenuItem
 */
class AdvancedMenuItem implements DependencyInterface {


	use GetPropsTrait;
	use UnifiedStyleRenderer;
	use \DIFL\Server\Traits\StyleUtil;

	protected $styles;

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/advancedmenuitem' )->customCssFields;
	}

	public static function font_style( $font ) {
		return static::get_font_style( $font );
	}

	public static function module_classnames( $args ) {
		$classnames_instance = $args['classnamesInstance'];
		$attrs               = $args['attrs'];

		// Text Options.
		$classnames_instance->add(
			TextClassnames::text_options_classnames(
				$attrs['module']['advanced']['text'] ?? array(),
				array(
					'orientation' => false,
				)
			),
			true
		);

		// Module.
		$classnames_instance->add(
			ElementClassnames::classnames(
				array(
					'attrs' => $attrs['module']['decoration'] ?? array(),
					'attrs' => array_merge(
						$attrs['module']['decoration'] ?? array(),
						array(
							'link' => $attrs['module']['advanced']['link'] ?? array(),
						)
					),
				)
			)
		);
	}

	public static function module_script_data( $args ) {
		$elements = $args['elements'];

		// Element Script Data Options.
		$elements->script_data(
			array(
				'attrName' => 'module',
			)
		);
	}

    protected function getDefaultKeys($args)
    {
        $attrs = $args['attrs'];

        $metaData = $args['elements']->module_metadata->attributes;
        foreach ($metaData as $key => $value) {
            if ( isset( $attrs[ $key ] ) && ! is_array( $attrs[ $key ] ) ) {
                continue;
            }
            if ( ! isset( $attrs[ $key ] ) ) {
                $attrs[ $key ] = array();
            }
            if (empty($attrs[$key]['innerContent'])) {
                $attrs[$key]['innerContent'] = [
                    'desktop' => [
                        'value' => []
                    ]
                ];
            }
            if(empty($attrs[$key]['decoration'])) {
                $attrs[$key]['decoration'] = [
                    'desktop' => [
                        'value' => []
                    ]
                ];
            }
        }
        return $attrs;
    }

	public static function module_styles( $args ) {
		$instance = new static();
		$attrs    = $instance->getDefaultKeys( $args );

		$elements     = $args['elements'];
		$settings     = $args['settings'] ?? array();
		$order_class  = $args['orderClass'];
		$parent_attrs = $args['parentAttrs'] ?? array();

		$instance->initializeProps( $elements, $attrs );

		$break_point = $parent_attrs['mslide_obj_settings']['innerContent']['desktop']['value']['break_point'] ?? '980';
		$disabled    = $instance->props['df_disabled_on'] ?? array();

		$break_point_max = sprintf(
			'@media only screen and ( max-width: %1$spx )',
			intval( $break_point )
		);
		$break_point_min = sprintf(
			'@media only screen and ( min-width: %1$spx )',
			intval( $break_point ) + 1
		);

		$instance->initializeProps( $elements, $attrs );
		$instance->styles = $instance->renderUnifiedStyles( $elements, $attrs, $order_class, 'difl/advancedmenuitem' );
		$styles           = array();
		if ( in_array( 'desktop', $disabled ) ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}",
					'attr'                => $attrs['df_disabled_obj_settings']['innerContent'],
					'atRules'             => $break_point_min,
					'declarationFunction' => function ( $props ) use ( $instance ) {
						return 'display: none !important;';
					},
				)
			);
		}
		if ( in_array( 'small', $disabled ) ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}",
					'attr'                => $attrs['df_disabled_obj_settings']['innerContent'] ?? array(),
					'atRules'             => $break_point_max,
					'declarationFunction' => function ( $props ) use ( $instance ) {
						return 'display: none !important;';
					},
				)
			);
		}

		if ( 'cart' === $instance->props['type'] ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class} .df-cart-info span.cart-icon",
					'attr'                => $attrs['cart_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['cart_icon'] ?? $instance->props['cart_icon'] ),
				)
			);
		}

		if ( 'search' === $instance->props['type'] ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class} .df_am_searchsubmit.with-icon",
					'attr'                => $attrs['search_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['search_icon'] ?? $instance->props['search_icon'] ),
				)
			);
            if($instance->props['search_style'==='df-searchbox-style-5']){
                $styles[] = CommonStyle::style(
                    array(
                        'selector'            => "{$order_class}.df-am-item ",
                        'attr'                => $attrs['search_obj_settings']['innerContent'] ?? null,
                        'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['search_tr_icon'] ?? $instance->props['search_tr_icon'] ),
                    )
                );
            }
		}

		if ( 'icon_box' === $instance->props['type'] ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}.df-am-item .et-pb-icon",
					'attr'                => $attrs['icon_btn_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['icon_btn_font_icon'] ?? $instance->props['icon_btn_font_icon'] ),
				)
			);
		}

		if ( 'button' === $instance->props['type'] ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class} .df-am-button-icon",
					'attr'                => $attrs['icon_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['button_font_icon'] ?? $instance->props['button_font_icon'] ),
				)
			);
		}

		if ( 'menu' === $instance->props['type'] ) {
			// font icon style for mobile menu trigger button.
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class} .df-mobile-menu-button",
					'attr'                => $attrs['mm_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['mm_trigger_icon'] ?? $instance->props['mm_trigger_icon'] ),
				)
			);

			if ( 'on' === $instance->props['use_mslide_btn'] ) {
				// font icon style for mslide button.
				$styles[] = CommonStyle::style(
					array(
						'selector'            => "{$order_class}_mslide_btn .df-mslide-button-icon",
						'attr'                => $attrs['mslide_button_obj_settings']['innerContent'] ?? null,
						'declarationFunction' => fn( $props ) => self::font_style( $props['attrValue']['mslide_button_font_icon'] ?? $instance->props['mslide_button_font_icon'] ),
					)
				);
			}
		}

		// Line Style 4
		if ( ( $instance->props['menu_item_hover_anim'] ?? null ) === 'item-hover-4' ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}.has-item-animation.item-hover-4 .menu-item>a:before,{$order_class}.has-item-animation.item-hover-4 .menu-item>a:after",
					'attr'                => $attrs['line_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => function( $props ) use ( $instance ) {
						$value = $props['attrValue']['line_weight'] ?? $instance->props['line_weight'] ?? '';
						return $value !== '' ? "width: {$value} !important;" : '';
					},
				)
			);
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}.has-item-animation.item-hover-4 .df-menu-nav>.menu-item.df-hover>a:before",
					'attr'                => $attrs['line_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => function( $props ) {
						$value = $props['attrValue']['line_space_between_item'] ?? '';
						return $value !== '' ? "left: -{$value} !important;" : '';
					},
				)
			);
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}.has-item-animation.item-hover-4 .df-menu-nav>.menu-item.df-hover>a:after",
					'attr'                => $attrs['line_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => function( $props ) {
						$value = $props['attrValue']['line_space_between_item'] ?? '';
						return $value !== '' ? "right: -{$value} !important;" : '';
					},
				)
			);
		}

		// Line Style 5
		if ( ( $instance->props['menu_item_hover_anim'] ?? null ) === 'item-hover-5' ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}.has-item-animation.item-hover-5 .df-menu-nav>.menu-item.df-hover>a:before",
					'attr'                => $attrs['line_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => function( $props ) {
						$value = $props['attrValue']['line_space_between_item'] ?? '';
						return $value !== '' ? "left: -{$value} !important;" : '';
					},
				)
			);
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}.has-item-animation.item-hover-5 .df-menu-nav>.menu-item.df-hover>a:after",
					'attr'                => $attrs['line_obj_settings']['innerContent'] ?? null,
					'declarationFunction' => function( $props ) {
						$value = $props['attrValue']['line_space_between_item'] ?? '';
						return $value !== '' ? "right: -{$value} !important;" : '';
					},
				)
			);
		}

		$styles[] = CommonStyle::style(
			array(
				'selector'            => "{$order_class} .df-menu-nav>li.menu-item>.sub-menu",
				'attr'                => $attrs['menu_obj_settings']['innerContent'] ?? array(),
				'declarationFunction' => function ( $props ) use ( $instance ) {
					$value = $props['attrValue']['submenu_distance_desktop']
						?? $instance->props['submenu_distance_desktop']
						?? '';
					return $value !== '' ? "margin-top: {$value} !important;" : '';
				},
			)
		);
		$styles[] = CommonStyle::style(
			array(
				'selector'            => "{$order_class} .df-menu-nav>li.menu-item.df-hover:after",
				'attr'                => $attrs['menu_obj_settings']['innerContent'] ?? array(),
				'declarationFunction' => function ( $props ) use ( $instance ) {
					$value = $props['attrValue']['submenu_distance_desktop']
						?? $instance->props['submenu_distance_desktop']
						?? '';
					return $value !== ''
						? "height: {$value} !important;"
						: '';
				},
			)
		);
		$styles[] = CommonStyle::style(
			array(
				'selector'            => "{$order_class} span",
				'attr'                => $attrs['icon_btn_obj_settings']['innerContent'],
				'declarationFunction' => function ( $props ) {
					$styleString = '';

					if ( ( $props['attrValue']['icon_btn_font_icon']['weight'] ?? 0 ) > 400 ) {
						$styleString .= 'font-weight: bold !important; ';
					}

					return $styleString;
				},
			)
		);

		if ( ( $instance->props['button_show_icon_on_hover'] ?? null ) !== 'off'
		) {
			if ( ( $instance->props['button_icon_on_left'] ?? null ) !== 'on'
			) {
				// Icon on right
				$styles[] = CommonStyle::style(
					array(
						'selector'            => "{$order_class}.df-menu-button.show_icon_on_hover .df-am-button-icon",
						'attr'                => $attrs['icon_btn_obj_settings']['innerContent'] ?? array(),
						'declarationFunction' => function ( $props ) use ( $attrs ) {
							// Try to find font size from content_obj_settings, fallback to 14px
							$font_size = null;
							if ( isset( $attrs['content_obj_settings']['decoration']['bodyFont']['body']['font'][ $props['breakpoint'] ][ $props['state'] ]['size'] )
								&& $attrs['content_obj_settings']['decoration']['bodyFont']['body']['font'][ $props['breakpoint'] ][ $props['state'] ]['size'] !== ''
							) {
								$font_size = $attrs['content_obj_settings']['decoration']['bodyFont']['body']['font'][ $props['breakpoint'] ][ $props['state'] ]['size'];
							} elseif ( isset( $attrs['content_obj_settings']['decoration']['bodyFont']['body']['font']['desktop']['value']['size'] )
								&& $attrs['content_obj_settings']['decoration']['bodyFont']['body']['font']['desktop']['value']['size'] !== ''
							) {
								$font_size = $attrs['content_obj_settings']['decoration']['bodyFont']['body']['font']['desktop']['value']['size'];
							}
							$icon_size = $font_size !== '' && $font_size !== null ? $font_size : '14px';
							return "margin: 0px; margin-right: -{$icon_size} !important; opacity: 0 !important;";
						},
					)
				);
				$styles[] = CommonStyle::style(
					array(
						'selector'            => "{$order_class}.df-menu-button.show_icon_on_hover:hover .df-am-button-icon",
						'attr'                => $attrs['icon_btn_obj_settings']['innerContent'] ?? array(),
						'declarationFunction' => function ( $props ) {
							return 'margin-right:0px !important; opacity: 1 !important;';
						},
					)
				);
			} else {
				// Icon on left
				$styles[] = CommonStyle::style(
					array(
						'selector'            => "{$order_class}.df-menu-button.show_icon_on_hover .df-am-button-icon",
						'attr'                => $attrs['icon_btn_obj_settings']['innerContent'] ?? array(),
						'declarationFunction' => function ( $props ) use ( $instance ) {
							// Try to find from settings 'content_body_font_size', fallback to 14px
							$icon_size = $instance->props['content_body_font_size'] ?? '14px';
							$icon_size = ( $icon_size !== '' ? $icon_size : '14px' );
							return "margin-left: -{$icon_size} !important; opacity: 0 !important;";
						},
					)
				);
				$styles[] = CommonStyle::style(
					array(
						'selector'            => "{$order_class}.df-menu-button.show_icon_on_hover:hover .df-am-button-icon",
						'attr'                => $attrs['icon_btn_obj_settings']['innerContent'] ?? array(),
						'declarationFunction' => function ( $props ) {
							return 'margin: 0px; margin-left:0px !important; opacity: 1 !important;';
						},
					)
				);
			}
			$styles[] = CommonStyle::style(
				array(
					'selector'            => '.df-am-container .df-am-col.show_icon_on_hover',
					// No attr needed
					'declarationFunction' => function ( $props ) {
						return 'min-width: 140px;';
					},
				)
			);
		}

		if ( ( $instance->props['use_button_icon'] ?? '' ) === 'on' ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class} .df-am-button-icon",
					'attr'                => $attrs['icon_obj_settings']['innerContent'] ?? array(),
					'declarationFunction' => function ( $props ) use ( $instance ) {
						$styleString = '';
						if ( ( $instance->props['button_icon_on_left'] ?? '' ) === 'on' ) {
							$styleString = 'margin-right: 5px;';
						} else {
							$styleString = 'margin-left: 5px;';
						}
						// No need for console.log in PHP
						return $styleString;
					},
				)
			);
		}

		if ( ( $instance->props['mslide_use_button_icon'] ?? '' ) === 'on' ) {
			$styles[] = CommonStyle::style(
				array(
					'selector'            => "{$order_class}_mslide_btn .df-mslide-button-icon",
					'attr'                => $attrs['mslide_button_obj_settings']['innerContent'] ?? array(),
					'declarationFunction' => function ( $props ) use ( $instance ) {
						$styleString = '';
						if ( ( $instance->props['mslide_button_icon_on_left'] ?? '' ) === 'on' ) {
							$styleString = 'margin-right: 5px;';
						} else {
							$styleString = 'margin-left: 5px;';
						}
						return $styleString;
					},
				)
			);
		}
		$styles[] = CommonStyle::style(
			array(
				'selector'            => "{$order_class} .df_am_searchsubmit",
				'attr'                => $attrs['search_obj_settings']['innerContent'],
				'declarationFunction' => function ( $props ) use ( $instance ) {
					$styleString = '';
					$attrValue = $props['attrValue'];
					$submitFontSize = isset( $attrValue['search_icon_size'] ) && $attrValue['search_icon_size'] !== '' ? $attrValue['search_icon_size'] : ( $instance->props['search_icon_size'] ?? '' );
					$submitFontSizeInt = intval( $submitFontSize );
					if ( $submitFontSizeInt === 0 ) {
						// If not set, fallback to default
						$submitFontSizeInt = 14;
					}
					$container_size = $submitFontSizeInt + 20;
					$styleString = sprintf(
						'width: %dpx !important; min-width: %dpx !important; height: %dpx !important; min-height: %dpx !important;',
						$container_size,
						$container_size,
						$container_size,
						$container_size
					);
					return $styleString;
				},
			)
		);

		$styles = array_merge( $styles, \difl_custom_decoration_background_style_chunks( $order_class, $attrs, $elements->module_metadata->attributes ?? [] ) );

		Style::add(
			array(
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => array(
					// Element: Module.
					$elements->style(
						array(
							'attrName'   => 'module',
							'styleProps' => array(
								'disabledOn' => array(
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								),
							),
						)
					),
					CssStyle::style(
						array(
							'selector'  => $args['orderClass'],
							'attr'      => $attrs['css'] ?? array(),
							'cssFields' => self::custom_css(),
						)
					),

					...$instance->styles,
					...$styles,

				),
			)
		);
	}

	/**
	 * @throws \Exception
	 */
	public function render( $id, $parent_attrs ) {
		global $df_menu, $df_menu_mobile;

		$type_settings = array();

		$type_settings['display_conditions'] = $this->props['display_conditions'];
		$type_settings['type']               = $this->props['type'];
		$type_settings['class']              = $id;
		$type_settings['indexClass']         = $type_settings['class'];
		$type_settings['link_option_url']    = isset( $this->props['link_option_url'] ) ? $this->props['link_option_url'] : '';
		// $type_settings['class'] = isset($this->props['hide_from_small']) && $this->props['hide_from_small'] === 'on' ?
		//     $type_settings['class'] . ' hide_from_small' : $type_settings['class'];

		// cart
		$type_settings['cart_icon']      = isset( $this->props['cart_icon'] ) && '' != $this->props['cart_icon'] ?
			esc_attr( Utils::process_font_icon( $this->props['cart_icon'] ) ) : '';
		$type_settings['use_cart_count'] = isset( $this->props['use_cart_count'] ) ?
			$this->props['use_cart_count'] : 'off';
		$type_settings['use_cart_total'] = isset( $this->props['use_cart_total'] ) ?
			$this->props['use_cart_total'] : 'off';

		// search
		$type_settings['search_style']   = isset( $this->props['search_style'] ) ?
			$this->props['search_style'] : '';
		$type_settings['placeholder']    = isset( $this->props['placeholder'] ) ?
			$this->props['placeholder'] : '';
		$type_settings['search_icon']    = isset( $this->props['search_icon'] ) ?
			esc_attr( Utils::process_font_icon( $this->props['search_icon'] ) ) : 'U';
		$type_settings['search_tr_icon'] = isset( $this->props['search_tr_icon'] ) ?
			esc_attr( Utils::process_font_icon( $this->props['search_tr_icon'] ) ) : 'U';

		// button icon
		$type_settings['icon_btn_font_icon']      = $this->props['icon_btn_font_icon'] ?? '';
		$type_settings['icon_link_title']         = $this->props['icon_link_title'] ?? '';
		$type_settings['icon_box_url']            = $this->props['icon_box_url'] ?? '';
		$type_settings['icon_box_url_new_window'] = $this->props['icon_box_url_new_window'] ?? '';

		// mobile button
		if ( $this->props['use_mslide_btn'] == 'on' ) {
			$type_settings['use_mslide_btn']               = isset( $this->props['use_mslide_btn'] ) ? $this->props['use_mslide_btn'] : 'Button Text';
			$type_settings['mslide_button_text']           = isset( $this->props['mslide_button_text'] ) ? $this->props['mslide_button_text'] : '';
			$type_settings['mslide_button_url']            = $this->props['mslide_button_url'] ?? '';
			$type_settings['mslide_button_url_new_window'] = $this->props['mslide_button_url_new_window'] ?? '';
			$type_settings['mslide_use_button_icon']       = $this->props['mslide_use_button_icon'] ?? '';
			$type_settings['mslide_button_font_icon']      = isset( $this->props['mslide_button_font_icon'] ) ?
				esc_attr( Utils::process_font_icon( $this->props['mslide_button_font_icon'] ) ) : '';
			$type_settings['mslide_button_icon_on_left']   = $this->props['mslide_button_icon_on_left'] ?? '';
		}

		// logo
		$type_settings['logo_upload']         = $this->props['logo_upload'] ?? '';
		$type_settings['sticky_logo']         = $this->props['sticky_logo'] ?? '';
		$type_settings['logo_alt']            = $this->props['logo_alt'] ?? '';
		$type_settings['logo_url']            = $this->props['logo_url'] ?? '';
		$type_settings['logo_url_new_window'] = $this->props['logo_url_new_window'] ?? '';

		// button
		$type_settings['button_text']               = $this->props['button_text'] ?? 'Button Text';
		$type_settings['button_url']                = $this->props['button_url'] ?? '';
		$type_settings['button_url_new_window']     = $this->props['button_url_new_window'] ?? '';
		$type_settings['use_button_icon']           = $this->props['use_button_icon'] ?? '';
		$type_settings['button_font_icon']          = isset( $this->props['button_font_icon'] ) ?
			esc_attr( Utils::process_font_icon( $this->props['button_font_icon'] ) ) : '';
		$type_settings['button_icon_on_left']       = $this->props['button_icon_on_left'] ?? '';
		$type_settings['button_show_icon_on_hover'] = $this->props['button_show_icon_on_hover'] ?? '';
		// content
		$type_settings['content'] = $this->props['content'] !== '' ? $this->props['content'] : '';

		// navigation menu
		$type_settings['menu_id']        = $this->props['menu_id'] ?? '';
		$type_settings['desktop_menu']   = $this->props['desktop_menu'] ?? '';
		$type_settings['mobile_menu']    = $this->props['mobile_menu'] ?? '';
		$type_settings['use_item_hover'] = $this->props['use_item_animation'] ?? 'off';
		$type_settings['item_hover']     = $this->props['menu_item_hover_anim'] ?? 'item-hover-1';

		// submenu
		$type_settings['submenu_reveal_anime'] = $this->props['submenu_reveal_anime'] ?? 'animtaion-submenu-1';
		$type_settings['use_submenu_arrow']    = $this->props['use_submenu_arrow'] ?? 'off';

		// social
		$type_settings['social'] = $this->props['social'] ?? '';

		// mobile menu trigger button
		$type_settings['mm_trigger_icon'] = isset( $this->props['mm_trigger_icon'] ) ?
			esc_attr( Utils::process_font_icon( $this->props['mm_trigger_icon'] ) ) : 'a';

		// common
		if ( isset( $this->props['menu_item_position'] ) && ! empty( $this->props['menu_item_position'] ) ) {
			$df_menu[ $this->props['menu_item_position'] ][] = $type_settings;
		} else {
			$df_menu['center_left'][] = $type_settings;
		}

		if ( isset( $this->props['menu_item_position_small'] ) && ! empty( $this->props['menu_item_position_small'] ) ) {
			$df_menu_mobile[ $this->props['menu_item_position_small'] ][] = $type_settings;
		} else {
			$df_menu_mobile['center_left'][] = $type_settings;
		}

		return '';
	}


	public function process_icon_size( $render_slug ) {
		$submit_font_size = $this->props['search_icon_size'] ? $this->props['search_icon_size'] : '14px';
		$container_size   = intval( $submit_font_size ) + 20;
		$styles           = array(
			'df-searchbox-style-1',
			'df-searchbox-style-2',
			'df-searchbox-style-3',
			'df-searchbox-style-4',
		);

		if ( in_array( $this->props['search_style'], $styles ) ) {
			ET_Builder_Element::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .df_am_searchsubmit',
					'declaration' => sprintf(
						'width: %1$s; min-width: %1$s; height: %1$s; min-height: %1$s;',
						$container_size . 'px'
					),
				)
			);
		}
	}


	function df_block_align( $align ) {
		if ( $align === 'center' ) {
			return 'margin-left: auto; margin-right: auto;';
		} elseif ( $align === 'right' ) {
			return 'margin-left: auto; margin-right: 0;';
		} elseif ( $align === 'left' ) {
			return 'margin-right: auto; margin-left: 0;';
		}
	}


	function df_get_div_value( $arg ) {
		$value = intval( $arg ) / 2;
		$unit  = str_replace( intval( $arg ), '', $arg );

		return $value . $unit;
	}

	public static function render_callback( $attrs, $content, $block, $elements ) {

		$instance = new static();
		$instance->initializeProps( $elements, $attrs );
		wp_enqueue_script( 'df-advanced-menu-item' );
		$parent       = BlockParserStore::get_parent( $block->parsed_block['id'], $block->parsed_block['storeInstance'] );
		$parent_attrs = $parent->attrs ?? array();
		$child_class  = str_replace(
			array(
				'-',
				'/',
			),
			'_',
			$block->parsed_block['id']
		);
		if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_header_layout' ) {
			$child_class .= '_tb_header';
		}
		if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_footer_layout' ) {
			$child_class .= '_tb_footer';
		}
		if ( isset( $block->parsed_block['layout_type'] ) && $block->parsed_block['layout_type'] === 'et_body_layout' ) {
			$child_class .= '_tb_body';
		}
		return Module::render(
			array(
				// FE only.
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],

				// VB equivalent.
				'attrs'               => $attrs,
				'elements'            => $elements,
				'id'                  => $block->parsed_block['id'],
				'moduleClassName'     => '',
				'name'                => $block->block_type->name,
				'classnamesFunction'  => array( AdvancedMenuItem::class, 'module_classnames' ),
				'moduleCategory'      => $block->block_type->category,
				'stylesComponent'     => array( AdvancedMenuItem::class, 'module_styles' ),
				'scriptDataComponent' => array( AdvancedMenuItem::class, 'module_script_data' ),
				'parentAttrs'         => $parent->attrs ?? array(),
				'parentId'            => $parent->id ?? '',
				'parentName'          => $parent->blockName ?? '',
				'children'            => $elements->style_components(
					array(
						'attrName' => 'module',
					)
				) . $instance->render( $child_class, $parent_attrs ),
			)
		);
	}

	public function load() {
		$module_json_folder_path = DIFL_MODULES_JSON_PATH . '/advanced-menu-item';

		add_action(
			'init',
			function () use ( $module_json_folder_path ) {
				ModuleRegistration::register_module(
					$module_json_folder_path,
					array(
						'render_callback' => array( AdvancedMenuItem::class, 'render_callback' ),
					)
				);
			}
		);
	}
}
