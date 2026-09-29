<?php

namespace DIFL\Server\Modules\IconList;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;

class IconList implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'IconList';

	private $tooltip_css_element = '';

	/**
	 * Return add new item(module) text.
	 *
	 * @return string
	 */
	public function add_new_child_text() {
		return esc_html__( 'Add New List Item', 'divi_flash' );
	}

	/**
	 * Declare custom css fields for the module
	 *
	 *
	 * @return array[]
	 * @since 1.0.0
	 */
	public function get_custom_css_fields_config() {
		return [
			'list_item_icon'           => [
				'label'    => esc_html__( 'Item Icon', 'divi_flash' ),
				'selector' => '.item-elements .et-pb-icon',
			],
			'list_item_image'          => [
				'label'    => esc_html__( 'Item Image', 'divi_flash' ),
				'selector' => '.item-elements img',
			],
			'list_item_icon_text'      => [
				'label'    => esc_html__( 'Item Icon Text', 'divi_flash' ),
				'selector' => '.item-elements .difl_list_icon_text',
			],
			'list_item_icon_wrapper'   => [
				'label'    => esc_html__( 'Item Icon Wrapper', 'divi_flash' ),
				'selector' => '.item-elements .difl_icon_item_icon_wrapper',
			],
			'list_item_icon_title'     => [
				'label'    => esc_html__( 'Item Title', 'divi_flash' ),
				'selector' => '.item-elements .difl_icon_item_header',
			],
			'list_item_title_icon'     => [
				'label'    => esc_html__( 'Item Title Icon', 'divi_flash' ),
				'selector' => '.item-elements .difl_icon_item_header .difl_list_title_icon',
			],
			'list_item_icon_content'   => [
				'label'    => esc_html__( 'Item Body', 'divi_flash' ),
				'selector' => '.item-elements .difl_icon_item_body',
			],
			'list_item_item_container' => [
				'label'    => esc_html__( 'Item Container', 'divi_flash' ),
				'selector' => '.item-elements .difl_icon_item_container',
			],
			'list_item_item_tooltip'   => [
				'label'    => esc_html__( 'Item Tooltip', 'divi_flash' ),
				'selector' => ".tippy-box[data-theme*='difl_iconlistitem']",
			],
			'list_item_item_wrapper'   => [
				'label'    => esc_html__( 'Item Wrapper', 'divi_flash' ),
				'selector' => '.item-elements',
			],
		];
	}

	/**
	 * Render module output
	 *
	 * @param array  $attrs       List of unprocessed attributes
	 * @param string $content     Content being processed
	 * @param string $render_slug Slug of module that is used for rendering output
	 *
	 * @return string module's rendered output
	 * @since 1.0.0
	 */
	public function render( $attrs, $content, $render_slug ) {

		// Show a notice message in Visual Builder if the list item is empty.
//		if ( $this->content === '' ) {
//			return sprintf(
//				'<div class="difl_iconlist_notice">%s</div>',
//				esc_html__( 'Add one or more list items.', 'divi_flash' )
//			);
//		}

//		wp_register_script( 'df_iconlist', DIFL_PUBLIC_DIR . 'js/iconlist.js', array('jquery'), time(), true );

		wp_enqueue_script( 'df_iconlist' );
		wp_enqueue_script( 'image-hotspot-popper-script' );
		wp_enqueue_script( 'image-hotspot-tippy-bundle-script' );

		return sprintf(
			'<div class="et_pb_module_inner"><ul class="difl_iconlist_container" style="list-style-type: none;">%1$s</ul></div>',
			$content
		);
	}

    function get_responsive_values( $data ) {
        $result = [];

        // Get breakpoint order dynamically.
        $breakpoints = array_keys( $data['attr'] ?? [] );

        // Current breakpoint.
        $current_bp = $data['breakpoint'] ?? 'desktop';

        // Find current breakpoint index.
        $current_index = array_search( $current_bp, $breakpoints );

        if ( $current_index === false ) {
            $current_index = 0;
        }

        // Collect all keys dynamically
        $keys = [];

        if ( isset( $data['attrValue'] ) ) {
            $keys = array_merge( $keys, array_keys( $data['attrValue'] ) );
        }

        foreach ( $data['attr'] as $bp ) {
            if ( isset( $bp['value'] ) ) {
                $keys = array_merge( $keys, array_keys( $bp['value'] ) );
            }
        }

        $keys = array_unique( $keys );

        // Resolve each key with fallback.
        foreach ( $keys as $key ) {

            $value_found = false;

            for ( $i = $current_index; $i >= 0; $i-- ) {

                $bp = $breakpoints[$i];

                if ( isset( $data['attr'][$bp]['value'][$key] ) ) {

                    $result[$key] = $data['attr'][$bp]['value'][$key];
                    $value_found = true;
                    break;
                }
            }

            // fallback to attrValue.
            if ( ! $value_found && isset( $data['attrValue'][$key] ) ) {
                $result[$key] = $data['attrValue'][$key];
            }
        }

        return $result;
    }

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = $this->get_default_value_for_styles( $attrs );
		$attrs      = self::build_attr_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';
		$devices    = Breakpoint::get_style_breakpoint_settings();

		foreach ( $devices as $device => $value ) {
			if ( isset( $attrs['list_view_type']['innerContent'][ $device ]['value'] )
				&& 'list' === $attrs['list_view_type']['innerContent'][ $device ]['value'] ) {
				// Ensure innerContent structure exists for list_item_per_column
				if ( ! isset( $attrs['list_item_per_column']['innerContent'][ $device ] ) ) {
					$attrs['list_item_per_column']['innerContent'][ $device ] = [];
				}
				$attrs['list_item_per_column']['innerContent'][ $device ]['value'] = 0;
			}
		}

		$styles = [];

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} ul.difl_iconlist_container",
            'attr'                => $attrs['layout_object']['innerContent'] ?? null,
            'declarationFunction' => function ( $props ) use ( $attrs, $devices ) {
                $attrValue = $this->get_responsive_values($props);

                $column_number = 'auto';
                $list_view_type = $attrValue['list_view_type'] ?? 'list';
                $list_item_per_column = $attrValue['list_item_per_column'] ?? '';

                if ( $list_view_type === 'inline' && $list_item_per_column !== null && $list_item_per_column !== '' ) {
                    $column_number = "repeat($list_item_per_column, 1fr)";
                }

                return "grid-template-columns: $column_number !important;";
            },
        ] );

		if ( 'inline' === ( $this->props['list_view_type'] ?? '' ) ) {
            // $common_styles[] = CommonStyle::style( [
            //     'selector'  => "{$orderClass} .item-elements .difl_icon_item_container",
            //     'attr'      => $attrs['list_item_vertical_alignment']['innerContent'] ?? null,
            //     'property'  => 'align-items',
            //     'important' => true,
            // ] );

			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .difl_iconlistitem",
				'attr'      => $attrs['list_item_vertical_alignment']['innerContent'] ?? null,
				'property'  => 'align-items',
				'important' => true,
			] );

		}


		if ( 'on' === ( $this->props['list_item_equal_width'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .difl_iconlistitem .et_pb_module_inner, {$orderClass} .difl_iconlistitem>div:first-child, {$orderClass} .item-elements",
				'attr'                => $attrs['list_item_equal_width']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'width:100%;';
				},
			] );

            $common_styles[] = CommonStyle::style( [
				'selector'   => "{$orderClass} .difl_iconlistitem .et_pb_module_inner",
				'attr'       => $attrs['list_item_horizontal_alignment']['innerContent'] ?? null,
				'property' => 'justify-content',
                'important' => true,
			] );
		}

		if ( ( 'list' === ( $this->props['list_view_type'] ?? '' ) || 'inline' === ( $this->props['list_view_type'] ?? '' ) ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .difl_iconlistitem",
				'attr'      => $attrs['list_item_horizontal_alignment']['innerContent'] ?? null,
				'property'  => 'justify-content',
				'important' => true,
			] );
		}

		if ( 'on' !== ( $this->props['list_item_equal_width'] ?? '' ) && 'list' === ( $this->props['list_view_type'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .difl_iconlistitem",
				'attr'      => $attrs['list_item_horizontal_alignment_after']['innerContent'] ?? null,
				'property'  => 'justify-content',
				'important' => true,
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} ul.difl_iconlist_container",
			'attr'      => $attrs['list_item_gap']['innerContent'] ?? null,
			'property'  => 'gap',
			'important' => true,
		] );

		if ( ! empty( $this->props['list_item_icon_bg_color'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .icon-element",
				'attr'      => $attrs['list_item_icon_bg_color']['innerContent'] ?? null,
				'property'  => 'background-color',
				'important' => true,
			] );
		}

		// When icon border radius is set, clip image/icon to rounded shape.
		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass} .item-elements .icon-element",
			'attr'                => $attrs['child_image_icon']['decoration']['border'] ?? null,
			'declarationFunction' => function ( $props ) {
				if ( ! empty( $props['attrValue']['radius'] ) ) {
					return 'overflow: hidden;';
				}
				return '';
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements .et-pb-icon",
			'attr'      => $attrs['list_item_icon_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );
		$icon_color_hover = $attrs['list_item_icon_color']['innerContent']['desktop']['hover'] ?? $attrs['list_item_icon_color']['innerContent']['hover'] ?? null;
		if ( ! empty( $icon_color_hover ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements:hover .et-pb-icon",
				'attr'      => [ 'desktop' => [ 'value' => $icon_color_hover ] ],
				'property'  => 'color',
				'important' => true,
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .et-pb-icon",
			'attr'      => $attrs['list_item_icon_size']['innerContent'] ?? null,
			'property'  => 'font-size',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper img",
			'attr'      => $attrs['list_item_image_width']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper img",
			'attr'      => $attrs['list_item_image_height']['innerContent'] ?? null,
			'property'  => 'height',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements .difl_icon_item_container",
			'attr'      => $attrs['list_item_icon_text_gap']['innerContent'] ?? null,
			'property'  => 'gap',
			'important' => true,
		] );

		if ( ! empty( $this->props['list_item_icon_lottie_background_color'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .difl_lottie_player svg path",
				'attr'      => $attrs['list_item_icon_lottie_color']['innerContent'] ?? null,
				'property'  => 'fill',
				'important' => false,
			] );
		}

		if ( ! empty( $this->props['list_item_icon_lottie_background_color'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .difl_lottie_player",
				'attr'      => $attrs['list_item_icon_lottie_background_color']['innerContent'] ?? null,
				'property'  => 'background-color',
				'important' => false,
			] );
		}

		if ( ! empty( $this->props['list_item_icon_lottie_width'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .difl_lottie_player",
				'attr'      => $attrs['list_item_icon_lottie_width']['innerContent'] ?? null,
				'property'  => 'width',
				'important' => true,
			] );
		}

		if ( isset( $this->props['list_item_icon_lottie_height'] ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper .difl_lottie_player",
				'attr'      => $attrs['list_item_icon_lottie_height']['innerContent'] ?? null,
				'property'  => 'height',
				'important' => true,
			] );
		}

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements span.difl_icon_item_container",
			'attr'      => $attrs['list_item_icon_placement']['innerContent'] ?? null,
			'property'  => 'flex-direction',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => "{$orderClass}.difl_iconlist .difl_iconlistitem *",
			'attr'                => $attrs['child_wrapper_element']['decoration']['text']['text'] ?? null,
			'declarationFunction' => function ( $attrValue ) {
				$orientation = isset( $attrValue['attrValue']['orientation'] ) ? $attrValue['attrValue']['orientation'] : null;
				if ( $orientation ) {
					return "text-align: {$orientation} !important;";
				}
				return null;
			},
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper",
			'attr'      => $attrs['list_item_icon_alignment']['innerContent'] ?? null,
			'property'  => 'text-align',
			'important' => true,
		] );

		if ( 'column' !== ( $this->props['list_item_icon_placement'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper",
				'attr'      => $attrs['list_item_icon_placement']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'display:flex;';
				}
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'  => "{$orderClass} .item-elements .difl_icon_item_icon_wrapper",
				'attr'      => $attrs['list_item_icon_vertical_placement']['innerContent'] ?? null,
				'property'  => 'align-items',
				'important' => true,
			] );
		}



		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .item-elements",
			'attr'      => $attrs['list_item_content_max_width']['innerContent'] ?? null,
			'property'  => 'max-width',
			'important' => true,
		] );

		$arrow_color = $this->props['tooltip_arrow_color'] ?? '';
		if ( '' !== $arrow_color ) {
			$tooltip_arrow_selector = "{$orderClass} .tippy-box[data-theme*='difl_iconlistitem'][data-placement^='%s'] > .tippy-arrow::before";
			$common_styles[]       = CommonStyle::style( [
				'selector'            => sprintf( $tooltip_arrow_selector, 'top' ),
				'attr'                => $attrs['tooltip_arrow_color']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $arrow_color ) {
					return 'border-top-color: ' . esc_attr( $arrow_color ) . ' !important;';
				},
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'            => sprintf( $tooltip_arrow_selector, 'bottom' ),
				'attr'                => $attrs['tooltip_arrow_color']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $arrow_color ) {
					return 'border-bottom-color: ' . esc_attr( $arrow_color ) . ' !important;';
				},
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'            => sprintf( $tooltip_arrow_selector, 'left' ),
				'attr'                => $attrs['tooltip_arrow_color']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $arrow_color ) {
					return 'border-left-color: ' . esc_attr( $arrow_color ) . ' !important;';
				},
			] );
			$common_styles[] = CommonStyle::style( [
				'selector'            => sprintf( $tooltip_arrow_selector, 'right' ),
				'attr'                => $attrs['tooltip_arrow_color']['innerContent'] ?? null,
				'declarationFunction' => function () use ( $arrow_color ) {
					return 'border-right-color: ' . esc_attr( $arrow_color ) . ' !important;';
				},
			] );
		}


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
