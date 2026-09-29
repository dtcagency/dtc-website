<?php

namespace DIFL\Server\Modules\TableOfContents;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use ET\Builder\Framework\Breakpoint\Breakpoint;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class TableOfContents implements DependencyInterface {
	public $icon_path;

	use ModuleUtil;
	use HasProps;

	protected $config_file = 'TableOfContents';

	protected $module_credits = [
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => 'diviflash.com',
	];

	public function init() {
		$this->name       = esc_html__( 'Table Of Contents', 'divi_flash' );
		$this->slug       = 'difl_table_of_contents';
		$this->icon_path  = DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/toc.svg';
		$this->vb_support = 'on';
	}

	public function get_advanced_fields_config() {
		$advanced_fields                       = [];
		$advanced_fields['text']               = false;
		$advanced_fields['link_options']       = false;
		$advanced_fields['fonts']['title']     = [
			'label'       => esc_html__( 'Title', 'divi_flash' ),
			'css'         => [
				'main'       => '%%order_class%% .heading_container .title',
				'hover'      => '%%order_class%% .heading_container .title:hover',
				'text_align' => '%%order_class%% .heading_container .title',
				'important'  => true,
			],
			'text_align'  => [
				'default'          => 'left',
				'default_on_front' => 'left',
			],
			'tab_slug'    => 'advanced',
			'toggle_slug' => 'title_style',
		];
		$advanced_fields['fonts']['heading_1'] = [
			'label'       => esc_html__( 'Heading', 'divi_flash' ),
			'font_size'   => [
				'default' => '24px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_1 > li > .toc-li-wrapper > a',
				'hover' => '%%order_class%% .body_container .difl_heading_level_1 > li > .toc-li-wrapper > a:hover',
			],
			'toggle_slug' => 'heading',
			'tab_slug'    => 'advanced',
			'sub_toggle'  => 'h1',
		];
		$advanced_fields['fonts']['heading_2'] = [
			'label'       => esc_html__( 'Heading 2', 'divi_flash' ),
			'font_size'   => [
				'default' => '20px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_2 > li > .toc-li-wrapper > a',
				'hover' => '%%order_class%% .body_container .difl_heading_level_2 > li > .toc-li-wrapper > a:hover',
			],
			'tab_slug'    => 'advanced',
			'toggle_slug' => 'heading',
			'sub_toggle'  => 'h2',
		];
		$advanced_fields['fonts']['heading_3'] = [
			'label'       => esc_html__( 'Heading 3', 'divi_flash' ),
			'font_size'   => [
				'default' => '16px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_3 > li > .toc-li-wrapper > a',
				'hover' => '%%order_class%% .body_container .difl_heading_level_3 > li > .toc-li-wrapper > a:hover',
			],
			'toggle_slug' => 'heading',
			'tab_slug'    => 'advanced',
			'sub_toggle'  => 'h3',
		];
		$advanced_fields['fonts']['heading_4'] = [
			'label'       => esc_html__( 'Heading 4', 'divi_flash' ),
			'font_size'   => [
				'default' => '14px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_4 > li > .toc-li-wrapper > a',
				'hover' => '%%order_class%% .body_container .difl_heading_level_4 > li > .toc-li-wrapper > a:hover',
			],
			'toggle_slug' => 'heading',
			'tab_slug'    => 'advanced',
			'sub_toggle'  => 'h4',
		];
		$advanced_fields['fonts']['heading_5'] = [
			'label'       => esc_html__( 'Heading 5', 'divi_flash' ),
			'font_size'   => [
				'default' => '12px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_5 > li > .toc-li-wrapper > a',
				'hover' => '%%order_class%% .body_container .difl_heading_level_5 > li > .toc-li-wrapper > a:hover',
			],
			'toggle_slug' => 'heading',
			'tab_slug'    => 'advanced',
			'sub_toggle'  => 'h5',
		];
		$advanced_fields['fonts']['heading_6'] = [
			'label'       => esc_html__( 'Heading 6', 'divi_flash' ),
			'font_size'   => [
				'default' => '10px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_6 > li > .toc-li-wrapper > a',
				'hover' => '%%order_class%% .body_container .difl_heading_level_6 > li > .toc-li-wrapper > a:hover',
			],
			'toggle_slug' => 'heading',
			'tab_slug'    => 'advanced',
			'sub_toggle'  => 'h6',
		];

		$advanced_fields['fonts']['heading_font_1'] = [
			'label'       => esc_html__( 'Heading', 'divi_flash' ),
			'font_size'   => [
				'default' => '24px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_1 > li > .toc-li-wrapper .native-number, %%order_class%% .body_container .difl_heading_level_1 > li > .toc-li-wrapper .number-with-dot',
				'hover' => '%%order_class%% .body_container .difl_heading_level_1 > li > .toc-li-wrapper .native-number:hover, %%order_class%% .body_container .difl_heading_level_1 > li > .toc-li-wrapper .number-with-dot:hover',
			],
			'toggle_slug' => 'marker_settings',
			'tab_slug'    => 'general',
			'show_if'     => [
				'marker_type' => 'number',
			],
			'sub_toggle'  => 'h1',
		];
		$advanced_fields['fonts']['heading_font_2'] = [
			'label'       => esc_html__( 'Heading 2', 'divi_flash' ),
			'font_size'   => [
				'default' => '20px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_2 > li > .toc-li-wrapper .native-number, %%order_class%% .body_container .difl_heading_level_2 > li > .toc-li-wrapper .number-with-dot',
				'hover' => '%%order_class%% .body_container .difl_heading_level_2 > li > .toc-li-wrapper .native-number:hover, %%order_class%% .body_container .difl_heading_level_2 > li > .toc-li-wrapper .number-with-dot:hover',
			],
			'toggle_slug' => 'marker_settings',
			'tab_slug'    => 'general',
			'show_if'     => [
				'marker_type' => 'number',
			],
			'sub_toggle'  => 'h2',
		];
		$advanced_fields['fonts']['heading_font_3'] = [
			'label'       => esc_html__( 'Heading 3', 'divi_flash' ),
			'font_size'   => [
				'default' => '16px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_3 > li > .toc-li-wrapper .native-number, %%order_class%% .body_container .difl_heading_level_3 > li > .toc-li-wrapper .number-with-dot',
				'hover' => '%%order_class%% .body_container .difl_heading_level_3 > li > .toc-li-wrapper .native-number:hover, %%order_class%% .body_container .difl_heading_level_3 > li > .toc-li-wrapper .number-with-dot:hover',
			],
			'toggle_slug' => 'marker_settings',
			'tab_slug'    => 'general',
			'show_if'     => [
				'marker_type' => 'number',
			],
			'sub_toggle'  => 'h3',
		];
		$advanced_fields['fonts']['heading_font_4'] = [
			'label'       => esc_html__( 'Heading 4', 'divi_flash' ),
			'font_size'   => [
				'default' => '14px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_4 > li > .toc-li-wrapper .native-number, %%order_class%% .body_container .difl_heading_level_4 > li > .toc-li-wrapper .number-with-dot',
				'hover' => '%%order_class%% .body_container .difl_heading_level_4 > li > .toc-li-wrapper .native-number:hover, %%order_class%% .body_container .difl_heading_level_4 > li > .toc-li-wrapper .number-with-dot:hover',
			],
			'toggle_slug' => 'marker_settings',
			'tab_slug'    => 'general',
			'show_if'     => [
				'marker_type' => 'number',
			],
			'sub_toggle'  => 'h4',
		];
		$advanced_fields['fonts']['heading_font_5'] = [
			'label'       => esc_html__( 'Heading 5', 'divi_flash' ),
			'font_size'   => [
				'default' => '12px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_5 > li > .toc-li-wrapper .native-number, %%order_class%% .body_container .difl_heading_level_5 > li > .toc-li-wrapper .number-with-dot',
				'hover' => '%%order_class%% .body_container .difl_heading_level_5 > li > .toc-li-wrapper .native-number:hover, %%order_class%% .body_container .difl_heading_level_5 > li > .toc-li-wrapper .number-with-dot:hover',
			],
			'toggle_slug' => 'marker_settings',
			'tab_slug'    => 'general',
			'show_if'     => [
				'marker_type' => 'number',
			],
			'sub_toggle'  => 'h5',
		];
		$advanced_fields['fonts']['heading_font_6'] = [
			'label'       => esc_html__( 'Heading 6', 'divi_flash' ),
			'font_size'   => [
				'default' => '10px',
			],
			'font_weight' => [
				'default' => '500',
			],
			'line_height' => [
				'default' => '1.7',
			],
			'css'         => [
				'main'  => '%%order_class%% .body_container .difl_heading_level_6 > li > .toc-li-wrapper .native-number, %%order_class%% .body_container .difl_heading_level_6 > li > .toc-li-wrapper .number-with-dot',
				'hover' => '%%order_class%% .body_container .difl_heading_level_6 > li > .toc-li-wrapper .native-number:hover, %%order_class%% .body_container .difl_heading_level_6 > li > .toc-li-wrapper .number-with-dot:hover',
			],
			'toggle_slug' => 'marker_settings',
			'tab_slug'    => 'general',
			'show_if'     => [
				'marker_type' => 'number',
			],
			'sub_toggle'  => 'h6',
		];

		$advanced_fields['borders'] = [
			'default' => true,
		];

		$advanced_fields['borders'] ['title_border']   = [
			'tab_slug'    => 'advanced',
			'toggle_slug' => 'header_style',
			'css'         => [
				'main' => [
					'border_radii'        => '%%order_class%% .heading_container',
					'border_radii_hover'  => '%%order_class%% .heading_container:hover',
					'border_styles'       => '%%order_class%% .heading_container',
					'border_styles_hover' => '%%order_class%% .heading_container:hover',
				],
			],
			'important'   => 'all',
		];
		$advanced_fields['borders'] ['content_border'] = [
			'tab_slug'    => 'advanced',
			'toggle_slug' => 'content_style',
			'css'         => [
				'main' => [
					'border_radii'        => '%%order_class%% .body_container',
					'border_radii_hover'  => '%%order_class%% .body_container:hover',
					'border_styles'       => '%%order_class%% .body_container',
					'border_styles_hover' => '%%order_class%% .body_container:hover',
				],
			],
			'important'   => 'all',
		];
		$advanced_fields['borders'] ['active_style']   = [
			'tab_slug'    => 'advanced',
			'toggle_slug' => 'active_style',
			'show_if'     => [
				'highlight_active_link' => 'on',
			],
			'css'         => [
				'main' => [
					'border_radii'        => '%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul li > .toc-li-wrapper:has( > a.active)',
					'border_radii_hover'  => '%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul li > .toc-li-wrapper:has( > a.active):hover',
					'border_styles'       => '%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul li > .toc-li-wrapper:has( > a.active)',
					'border_styles_hover' => '%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul li > .toc-li-wrapper:has( > a.active):hover',
				],
			],
			'important'   => 'all',
		];

		$advanced_fields['box_shadow'] ['active_style'] = [
			'tab_slug'    => 'advanced',
			'toggle_slug' => 'active_style',
			'css'         => [
				'main'  => '%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul li .toc-li-wrapper:has( > a.active)',
				'hover' => '%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul li .toc-li-wrapper:has( > a.active):hover',
			],
			'important'   => 'all',
		];


		$headings = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
		foreach ( $headings as $heading ) {
			$sub_toggle                                            = $heading;
			$level                                                 = str_replace( 'h', '', $heading );
			$advanced_fields['borders']["heading_border_$heading"] = [
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'heading',
				'sub_toggle'  => $sub_toggle,
				'css'         => [
					'main' => [
						'border_radii'        => "%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul.difl_heading_level_$level > li > .toc-li-wrapper",
						'border_radii_hover'  => "%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul.difl_heading_level_$level > li:hover > .toc-li-wrapper",
						'border_styles'       => "%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul.difl_heading_level_$level > li > .toc-li-wrapper",
						'border_styles_hover' => "%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul.difl_heading_level_$level > li:hover > .toc-li-wrapper",
					],
				],
				'important'   => 'all',
			];

			$advanced_fields['box_shadow']["heading_shadow_$heading"] = [
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'heading',
				'sub_toggle'  => $sub_toggle,
				'css'         => [
					'main'  => "%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul.difl_heading_level_$level > li > .toc-li-wrapper",
					'hover' => "%%order_class%% .difl_toc_main_container .body_container ul.difl--toc--ul.difl_heading_level_$level:hover > li > .toc-li-wrapper",
				],
				'important'   => 'all',
			];
		}

		return $advanced_fields;
	}

	// public function &__get( $name ) {
	// 	return $this->props[ $name ];
	// 	if ( array_key_exists( $name, $this->props ) ) {
	// 		$value = $this->props[ $name ];
	// 		if ( str_contains( $value, '|||' ) && str_starts_with( $name, 'active_spacing_' ) ) {
	// 			$value = et_builder_get_element_style_css( $value, 'padding' );
	// 		}

	// 		return $value;
	// 	}

	// 	throw new Exception( sprintf( 'Property %s does not exist', $name ) );
	// }

	public function render_title_icon() {
		if ( $this->props['collapsible_toc'] !== 'on' || $this->props['title_icon'] !== 'on' ) {
			return null;
		}

		$icon = fn( $type ) => sprintf(
			'<span class="et-pb-icon %s_icon">%s</span>',
			$type,
			Utils::process_font_icon( $this->props[ "{$type}_icon" ] )
		);

		return sprintf(
			'<div class="icon">%s%s</div>',
			$icon( 'expand' ),
			$icon( 'collapse' )
		);
	}

	protected function get_headings() {

		$title_tag = $this->get_prop( 'title_tag' );
		$title     = $this->get_prop( 'title' );

		// Fallbacks for safety
		$title_tag = $title_tag ?: 'h2';
		$title     = $title ?: '';

		return sprintf(
			'<div class="heading_container">
				<%1$s class="title">%2$s</%1$s>
				%3$s
			</div>',
			esc_html( $title_tag ),
			esc_html( $title ),
			$this->render_title_icon()
		);
	}

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/table-of-contents' )->customCssFields;
	}


	function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

		$common_styles[] = CssStyle::style( [
			'selector'  => $orderClass,
			'attr'      => $attrs['css'] ?? [],
			'cssFields' => self::custom_css(),
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .heading_container",
			'attr'     => $attrs['header_bg_color']['innerContent'] ?? null,
			'property' => 'background-color',
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .body_container",
			'attr'     => $attrs['content']['innerContent'] ?? null,
			'property' => 'background-color',
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .heading_container .heading",
			'attr'     => $attrs['heading']['innerContent'] ?? null,
			'property' => 'color',
		] );

		if ( 'on' === $this->props['highlight_active_link'] ) {
			$common_styles [] = CommonStyle::style( [
				'selector' => "{$orderClass} .difl_toc_main_container .body_container ul.difl--toc--ul a.active",
				'attr'     => $attrs['active_link_color']['innerContent'] ?? null,
				'property' => 'color',
			] );

			$common_styles [] = CommonStyle::style( [
				'selector' => "{$orderClass} .difl_toc_main_container .body_container ul.difl--toc--ul .toc-li-wrapper:has( > a.active) span",
				'attr'     => $attrs['active_icon_marker_color']['innerContent'] ?? null,
				'property' => 'color',
			] );

			$common_styles [] = CommonStyle::style( [
				'selector' => "{$orderClass} .difl_toc_main_container .body_container ul.difl--toc--ul li .toc-li-wrapper:has( > a.active)",
				'attr'     => $attrs['active_background_color']['innerContent'] ?? null,
				'property' => 'background-color',
			] );
		}

		if ( 'off' === $this->props['full_width_header'] ) {
			$common_styles [] = CommonStyle::style( [
				'selector' => "{$orderClass}.difl_table_of_contents .difl_toc_main_container .heading_container",
				'attr'     => $attrs['title_icon_gap']['innerContent'] ?? null,
				'property' => 'gap',
			] );
		}

		if ( 'on' === $this->props['use_content_height'] ) {
			$common_styles [] = CommonStyle::style( [
				'selector' => "{$orderClass} .difl_toc_main_container .height_enable",
				'attr'     => $attrs['content_height']['innerContent'] ?? null,
				'property' => 'height',
			] );
		}

		$common_styles [] = CommonStyle::style( [
			'selector'            => "{$orderClass} .heading_container .icon .expand_icon.et-pb-icon",
			'attr'                => $attrs['expand_icon']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return self::get_font_style( $this->props['expand_icon'] );
			},
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_toc_main_container .body_container",
			'attr'     => $attrs['content']['innerContent'] ?? null,
			'property' => 'background-color',
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .heading_container .icon .expand_icon.et-pb-icon",
			'attr'     => $attrs['expand_icon_color']['innerContent'] ?? null,
			'property' => 'color',
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .heading_container .icon .expand_icon.et-pb-icon",
			'attr'     => $attrs['expand_icon_size']['innerContent'] ?? null,
			'property' => 'font-size',
		] );

		$common_styles [] = CommonStyle::style( [
			'selector'            => "{$orderClass} .heading_container .icon .collapse_icon.et-pb-icon",
			'attr'                => $attrs['collapse_icon']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return self::get_font_style( $this->props['collapse_icon'] );
			},
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .heading_container .icon .collapse_icon.et-pb-icon",
			'attr'     => $attrs['collapse_icon_color']['innerContent'] ?? null,
			'property' => 'color',
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .heading_container .icon .collapse_icon.et-pb-icon",
			'attr'     => $attrs['collapse_icon_size']['innerContent'] ?? null,
			'property' => 'font-size',
		] );

		array_map( function ( $heading ) use ( $orderClass, $attrs, &$common_styles ) {
			$selector         = "{$orderClass}.difl_table_of_contents .body_container.icon .difl_heading_level_{$heading} > li > .toc-li-wrapper > span.et-pb-icon";
			$icon             = $this->props["marker_icon_h{$heading}"];
			$size             = $this->props["marker_icon_size_h{$heading}"];
			$color            = $this->props["marker_icon_color_h{$heading}"];
			$space_selector   = "{$orderClass}.difl_table_of_contents .body_container.icon .difl_heading_level_{$heading} > li > .toc-li-wrapper > a";
			$heading_distance = $this->props["marker_icon_space_heading_h{$heading}"];

			$common_styles[] = CommonStyle::style( [
				'selector'            => $selector,
				'attr'                => $attrs['marker_icon_heading_1']['innerContent'] ?? [],
				'declarationFunction' => function () use ( $size, $icon ) {
					return "font-size:{$size};" . self::get_font_style( $icon );
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => $selector,
				'attr'                => $attrs['marker_icon_heading_1']['innerContent'] ?? [],
				'declarationFunction' => function () use ( $color ) {
					return "color:{$color};";
				},
			] );

			$common_styles[] = CommonStyle::style( [
				'selector'            => $space_selector,
				'attr'                => $attrs['marker_icon_heading_1']['innerContent'] ?? [],
				'declarationFunction' => function () use ( $heading_distance ) {
					return "margin-left:{$heading_distance};";
				},
			] );

			return $common_styles;
		}, [ '1', '2', '3', '4', '5', '6' ] );

		array_map( function ( $heading ) use ( $orderClass, $attrs, &$common_styles ) {
			$selector         = "{$orderClass} .body_container .difl_heading_level_{$heading} > li > .toc-li-wrapper";

			$common_styles[] = CommonStyle::style( [
				'selector'            => $selector,
				'attr'                => $attrs["heading_{$heading}"]['innerContent'] ?? [],
				'declarationFunction' => function ($props) {
					return "background-color:{$props['attrValue']};";
				},
			] );

			return $common_styles;
		}, [ '1', '2', '3', '4', '5', '6' ] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .heading_container",
			'attr'     => $attrs['header_style']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_toc_main_container .body_container ul.difl--toc--ul li > .toc-li-wrapper:has( > a.active)",
			'attr'     => $attrs['active_style']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass}",
			'attr'     => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .body_container",
			'attr'     => $attrs['content']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .body_container .difl_heading_level_1 > li > .toc-li-wrapper",
			'attr'     => $attrs['heading_1']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .body_container .difl_heading_level_2 > li > .toc-li-wrapper",
			'attr'     => $attrs['heading_2']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .body_container .difl_heading_level_3 > li > .toc-li-wrapper",
			'attr'     => $attrs['heading_3']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .body_container .difl_heading_level_4 > li > .toc-li-wrapper",
			'attr'     => $attrs['heading_4']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .body_container .difl_heading_level_5 > li > .toc-li-wrapper",
			'attr'     => $attrs['heading_5']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .body_container .difl_heading_level_6 > li > .toc-li-wrapper",
			'attr'     => $attrs['heading_6']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
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

	public function render( $attrs, $content, $render_slug, $parent_address = '', $global_parent = '', $global_parent_type = '', $parent_type = '', $theme_builder_area = '' ) {
		static $is_script_enqueued = false;
		if ( ! $is_script_enqueued ) {
			add_action( 'wp_print_footer_scripts', [ $this, 'enqueue_script' ], PHP_INT_MAX );
			$is_script_enqueued = true;
		}
		wp_enqueue_script( 'jquery' );

		$settings = $this->get_settings();

		$output = '';
		$output .= $this->get_headings();

		$collapse_sticky    = 'on' === $this->props['collapsible_toc'] && 'on' === $this->props['collapsible_with_sticky'] ? 'collapse_sticky' : '';
		$full_width_header  = 'on' === $this->props['full_width_header'] && 'on' === $this->props['full_width_header'] ? 'full_width_header' : '';
		$collapse_icon_only = 'on' === $this->props['collapse_icon_only'] && 'on' === $this->props['collapse_icon_only'] ? 'collapse_icon_only' : '';

		$output .= sprintf(
			'<div class="body_container %1$s %3$s %4$s %5$s" data-settings=\'%2$s\'></div>',
			! empty( $this->props['marker_type'] ) ? esc_attr( $this->props['marker_type'] ) : 'number',
			$settings,
			'on' === $this->props['use_content_height'] ? ' height_enable' : '',
			'off' === $this->props['hierarchical_view'] ? ' non_hierarchical' : '',
			$collapse_sticky
		);

		return sprintf( '<div class="difl_toc_main_container %2$s %3$s %4$s">%1$s</div>', $output, esc_attr( $this->get_prop_default_collapse_state() ), $full_width_header, $collapse_icon_only );
	}

	protected function get_prop_default_collapse_state() {
		if ( $this->props['collapsible_toc'] !== 'on' ) {
			return 'expanded';
		}

		return $this->get_prop( 'default_collapse_state' );
	}

	protected function get_prop( string $name ) {

		if ( ! isset( $this->props[ $name ] ) ) {
			return null;
		}

		$value = $this->props[ $name ];

		if (
			is_string( $value ) &&
			str_contains( $value, '|||' ) &&
			str_starts_with( $name, 'active_spacing_' )
		) {
			$value = et_builder_get_element_style_css( $value, 'padding' );
		}

		return $value;
	}

	protected function get_settings() {
		$allowed_settings = [
			'heading_tags',
			'headings_exclude_by_class',
			'container_exclude_by_class',
			'minimum_number_of_headings',
			'scrolling_speed',
			'content_height',
			'default_collapse_state',
			'offset',
			'marker_type',
			'collapsible_with_sticky',
			'active_link_color',
			'active_link_border_on_parent',
			'active_spacing_padding',
			'active_spacing_padding_tablet',
			'active_spacing_padding_phone',
			'highlight_active_link',
		];

		$headings = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];

		foreach ( $headings as $heading ) {
			$icon     = "marker_icon_$heading";
			$size     = "marker_icon_size_$heading";
			$color    = "marker_icon_color_$heading";
			$level    = str_replace( 'h', '', $heading );
			$selector = ".difl_table_of_contents .body_container.icon .difl_heading_level_$level > li > .toc-li-wrapper > span.et-pb-icon";

			array_push( $allowed_settings, $icon );
			array_push( $allowed_settings, $size );
			array_push( $allowed_settings, $color );

		}

		$nested_items = [];

		$fields = array_filter( $this->attributes, function ( $item, $key ) use ( $allowed_settings, &$nested_items ) {
			if ( isset( $item['settings']['innerContent']['groupType'] ) &&
			     $item['settings']['innerContent']['groupType'] === 'group-items' &&
			     isset( $item['settings']['innerContent']['items'] ) ) {
				$keys = array_keys( $item['settings']['innerContent']['items'] );
				foreach ( $keys as $key ) {
					$nested_items[] = $key;
				}
			}

			return in_array( $key, $allowed_settings );
		}, 1 );

		$settings = [];

		if ( $nested_items ) {
			foreach ( $nested_items as $nested_item ) {
				if ( in_array( $nested_item, $allowed_settings ) ) {
					if ( is_array( $this->props[ $nested_item ] ) && array_key_exists( 'unicode', $this->props[ $nested_item ] ) ) {
						$settings[ $nested_item ] = esc_html( Utils::process_font_icon( $this->props[ $nested_item ] ) );
						continue;
					}
					$settings[ $nested_item ] = $this->props[ $nested_item ];
				}
			}
		}

		foreach ( $fields as $key => $tooltip_option ) {
			$settings[ $key ] = $this->get_prop( $key );
		}

		$settings['offset_tablet'] = $this->props['offset_tablet'];
		$settings['offset_phone']  = $this->props['offset_phone'];
		$settings['disabled_on']   = $this->props['disabled_on'];
		$settings['order_class']   = $this->get_prop( 'order_class' );

		return wp_json_encode( $settings );
	}

	public function enqueue_script() { ?>
        <script>
			(() => {
					let current_order_class = '.difl_table_of_contents_0';

					let active_using_click = false;

					const create_toc = ( text, level, parent = undefined ) => {
						return {
							text,
							level,
							id: undefined,
							parent,
							children: []
						};
					}

					const parse_headings = elements => {
						const toc = [];
						let current_level = 0;
						let current_parent = undefined;

						elements.forEach( ( element, index ) => {
							const level = parseInt( element.tagName.substring( 1 ) );
							const text = element.textContent.trim();

							if ( current_level < level ) {
								const entry = create_toc( text, level, current_parent );
								current_parent ? current_parent.children.push( entry ) : toc.push( entry );
								current_parent = entry;
							} else {
								let new_parent = create_toc( text, level );
								while ( current_parent && current_parent.level >= level ) {
									current_parent = current_parent.parent;
								}
								if ( current_parent ) {
									new_parent.parent = current_parent;
									current_parent.children.push( new_parent );
								} else {
									toc.push( new_parent );
								}
								current_parent = new_parent;
							}

							current_level = level;

							const id = 'difl-toc-' + text.replace( /[^\w\s-]/g, '' ).replace( /\s+/g, '-' ).toLowerCase() + '-' + index;
							element?.classList?.add( 'difl-toc-item' );
							current_parent?.classList?.add( 'difl-toc-item' );
							if ( element.id ) {
								current_parent.id = element.id;
							} else {
								element.id = id;
								current_parent.id = id;
							}
						} );

						return toc;
					}

					const generate_markup = toc => {
						const is_icon_marker = 'icon' === get_settings( 'marker_type' );
						const is_number_with_dot = 'number_with_dot' === get_settings( 'marker_type' );
						const is_native_number = 'number' === get_settings( 'marker_type' );
						let icon_span = '';
						let level_stack = [ 1 ];
						let depth = 0;

						const build_list = ( entries, level = 1 ) => {
							let html = `<ul class="difl--toc--ul difl_heading_level_${ level }">`;
							const icon_settings = get_settings( `marker_icon_h${ level }` );

							entries.forEach( ( entry, index ) => {
								let numbering = level_stack.join( '.' );
								numbering = depth === 0 ? '0' + numbering + '.' : numbering;

								const classes = `difl--toc--li difl_heading_li_level_${ entry.level }`;
								const anchor = `<a class="difl--toc--anchor ${ classes }" href="#${ entry.id }">${ entry.text }</a>`;
								if ( is_icon_marker && undefined !== icon_settings ) {
									// const icon = icon_settings.split( '||' )[0];
									icon_span = `<span class="et-pb-icon marker-icon">${ icon_settings }</span>`;
								}

								if ( is_number_with_dot ) {
									icon_span = `<span class="number-with-dot">${ numbering }</span>`;
								}

								if ( is_native_number ) {
									let numbering = level_stack[depth];
									numbering = depth === 0 ? '0' + level_stack[0] : '0' + numbering;
									icon_span = `<span class="native-number">${ numbering }</span>`;
								}

								html += `<li id="${ entry.id }-toc-li"><div class="toc-li-wrapper">${ icon_span } ${ anchor }</div>`;
								if ( entry.children.length ) {
									level_stack.push( 1 );
									depth += 1;
									html += build_list( entry.children, level + 1 );
								}
								level_stack[depth] += 1;
								html += `</li>`;
							} );
							depth -= 1;
							level_stack.pop();
							return html + `</ul>`;
						}

						return build_list( toc );
					}

					const get_settings = ( key = 'all' ) => {
						const toc_main = document.querySelector( current_order_class );
						const body_container = toc_main.querySelector( '.body_container' );
						const settings = JSON.parse( body_container.dataset.settings );

						if ( 'all' === key ) {
							return settings;
						}

						return settings[key] || '';
					}

					const get_offset = () => {
						const is_mobile = window.matchMedia( '(max-width: 767px)' ).matches;
						const is_tablet = window.matchMedia( '(min-width: 768px) and (max-width: 1024px)' ).matches;
						let offset = get_settings( 'offset' ).replace( 'px', '' );

						if ( is_tablet ) {
							offset = get_settings( 'offset_tablet' );
						}

						if ( is_mobile ) {
							offset = get_settings( 'offset_mobile' );
						}

						return offset;
					}

					const get_parent_distance = ( wrapper, parent_selector, side = 'left' ) => {
						const parent = wrapper.closest( parent_selector );

						const parent_rect = parent.getBoundingClientRect();
						const child_rect = wrapper.getBoundingClientRect();

						return parent_rect[side] - child_rect[side];
					}

					const is_closest = ( element, selectors ) => {
						if ( ! selectors || selectors.trim() === '' ) return false;
						return selectors.split( ',' ).some( ( selector ) => element.closest( `.${ selector }` ) ) !== null;
					}

					const is_matched = ( element, selectors ) => {
						if ( ! selectors || selectors.trim() === '' ) return false;
						let matched = false;
						element.classList.forEach( class_name => {
							if ( matched ) return true;
							if ( selectors.split( ',' ).some( selector => class_name === selector ) ) {
								matched = true;
							}
						} )

						return matched;
					}

					const is_hidden = element => {
						const style = window.getComputedStyle( element );
						return style.display === 'none' || style.visibility === 'hidden';
					}

					const generate_toc = () => {
						const toc_main = document.querySelector( current_order_class );
						const body_container = toc_main.querySelector( '.body_container' );
						const settings = get_settings();
						const heading_tags = settings?.heading_tags;
						const module_hide_number = settings.minimum_number_of_headings;
						const container_exclude = get_settings( 'container_exclude_by_class' );
						const heading_exclude = get_settings( 'headings_exclude_by_class' );
						let main_content = document.querySelector( '#main-content' );
						if ( ! main_content ) return;

						const allowed_tags = heading_tags;

						if ( ! allowed_tags.length ) {
							toc_main.remove();
							return;
						}

						const headings = [ ...main_content.querySelectorAll( allowed_tags.join( ',' ) ) ].filter( heading => {
							return ! (heading.closest( '.entry-title' ) || heading.closest( '#sidebar' ) || heading.closest( '#comment-wrap' ) || heading.closest( '.heading_container' ) || is_closest( heading, container_exclude ) || is_matched( heading, heading_exclude ) || is_hidden( heading ))
						} )
						
						if ( headings?.length < module_hide_number ) {
							toc_main.remove();
							return;
						}

						const parsed_toc = parse_headings( Array.from( headings ) );
						body_container.innerHTML = generate_markup( parsed_toc );
					}

					const handle_multiple_modules = () => {
						const modules = document.querySelectorAll( '.difl_table_of_contents' );
						const index_length = modules?.length ?? 1;
						[ ...modules ].forEach( ( module, main_index ) => {
							 current_order_class = Object.values( module.classList )
								.filter( cls => cls.startsWith( 'difl_table' ) )
								.map( cls => '.' + cls )
								.join( '' );
							let index = index_length + main_index;
							// current_order_class = `.difl_table_of_contents_${ index }`;
							// current_order_class = `.difl_table_of_contents_${ index }`;
							// if ( ! module.classList.contains( current_order_class.replace( '.', '' ) ) ) {
							// 	current_order_class = `.difl_table_of_contents_${ index }_tb_body`
							// }
							generate_toc();
							toggle_header();
						} )
					}

					const get_spacing_value = ( value ) => {
						let top = 0, right = 0, bottom = 0, left = 0;
						value.split( ';' ).map( val => val.split( ':' ) ).forEach( item => {
							const side = item[0].replace( 'padding-', '' ).trim();
							const side_value = '' === item[1] ? 0 : parseInt( item[1] );

							if ( side === 'top' ) {
								top = side_value;
							} else if ( side === 'right' ) {
								right = side_value;
							} else if ( side === 'bottom' ) {
								bottom = side_value;
							} else if ( side === 'left' ) {
								left = side_value;
							}
						} );

						return { top, right, bottom, left };
					}

					const toggle_header = () => {
						const module = document.querySelector( current_order_class );
						if ( ! module ) {
							return;
						}
						const container = module.querySelector( '.difl_toc_main_container' );
						const header = container.querySelector( '.heading_container' );
						const body = container.querySelector( '.body_container' );
						const expand_icon = header.querySelector( '.icon .expand_icon' );
						const collapse_icon = header.querySelector( '.icon .collapse_icon' );
						const scrolling_speed = get_settings( 'scrolling_speed' );
						const transition = `all ${ scrolling_speed } ease-in-out`;
						const height = getComputedStyle( body ).height;
						const padding = getComputedStyle( body ).padding;
						const margin = getComputedStyle( body ).margin;
						const border = getComputedStyle( body ).border;
						const sections = document.querySelectorAll( '[class*="difl-toc-item"]' );
						const items = module.querySelectorAll( '.difl_toc_main_container .body_container a.difl--toc--li' );
						const offset = get_offset();
						const highlight_active_link = 'on' === get_settings( 'highlight_active_link' );
						const is_border_on_parent = 'on' === get_settings( 'active_link_border_on_parent' );
						const active_padding = get_settings( 'active_spacing_padding' );
						const active_padding_tablet = get_settings( 'active_spacing_padding_tablet' );
						const active_padding_phone = get_settings( 'active_spacing_padding_phone' );
						const active_padding_desktop_value = get_spacing_value( active_padding );
						const active_padding_tablet_value = get_spacing_value( active_padding_tablet );
						const active_padding_phone_value = get_spacing_value( active_padding_phone );

						const get_current_device = () => {
							const breakpoints = <?php echo wp_json_encode( Breakpoint::get_style_breakpoint_settings() ); ?>;
							const sortedBreakpoints = Object.entries( breakpoints ).sort(
								( [ , a ], [ , b ] ) => a.order - b.order
							);

							for ( const [ device, config ] of sortedBreakpoints ) {
								let query = '';

								if ( 'minWidth' in config ) {
									query = `(min-width: ${ config.minWidth.value })`;
								} else if ( 'maxWidth' in config ) {
									query = `(max-width: ${ config.maxWidth.value })`;
								}

								if ( query && window.matchMedia( query ).matches ) {
									return device;
								}
							}

							const baseDevice = Object.entries( breakpoints ).find(
								( [ , config ] ) => config.baseDevice
							);
							return baseDevice ? baseDevice[0] : sortedBreakpoints[0][0];
						}

						const get_padding_value = () => {
							const current_device = get_current_device();
							if ( 'desktop' === current_device ) {
								return active_padding_desktop_value;
							}
							if ( 'tablet' === current_device ) {
								return active_padding_tablet_value;
							}
							return active_padding_phone_value;
						}

						const highlight_active = () => {
							if ( active_using_click ) {
								return;
							}

							let active_id = null;

							sections.forEach( ( section ) => {
								const rect = section.getBoundingClientRect();
								if ( active_id !== null ) {
									return;
								}
								if ( rect.top >= 0 && rect.top < window.innerHeight - offset ) {
									active_id = section.getAttribute( 'id' );
								}
							} );

							items.forEach( ( item ) => {
								const href = item.getAttribute( 'href' ).substring( 1 );
								if ( href === active_id ) {
									if ( items[0] !== item ) {
										item.scrollIntoView( {
											behavior: 'smooth',
											block: 'center',
											inline: 'nearest'
										} );
									}

									if ( is_border_on_parent && highlight_active_link ) {
										const child_selector = `${ active_id }-toc-li`;
										const li = document.getElementById( `${ child_selector }` );
										const wrapper = li.querySelector( '.toc-li-wrapper' );
										const left = get_parent_distance( wrapper, '.body_container' );
										const right = get_parent_distance( wrapper, '.body_container', 'right' ) * -1;
										const margin_left = left + parseInt( getComputedStyle( wrapper ).marginLeft.replace( 'px', '' ) );
										const margin_right = right + parseInt( getComputedStyle( wrapper ).marginRight.replace( 'px', '' ) );
										const padding_left = Math.abs( margin_left ) + parseInt( get_padding_value().left );
										const padding_right = Math.abs( margin_right ) + parseInt( get_padding_value().right );

										wrapper.style.setProperty( 'margin-left', `${ margin_left }px`, 'important' );
										wrapper.style.setProperty( 'margin-right', `${ margin_right }px`, 'important' );
										wrapper.style.setProperty( 'padding-left', `${ padding_left }px`, 'important' );
										wrapper.style.setProperty( 'padding-right', `${ padding_right }px`, 'important' );
									}
									if ( highlight_active_link ) {
										item.classList.add( 'active' );
									}
									items.forEach( ( item_remove ) => {
										if ( item_remove !== item && item_remove.classList.contains( 'active' ) ) {
											item_remove.classList.remove( 'active' );
											if ( is_border_on_parent && highlight_active_link ) {
												getComputedStyle( item_remove.closest( '.toc-li-wrapper' ) ).marginLeft;
												item_remove.closest( '.toc-li-wrapper' ).style.setProperty( 'margin-left', 'initial' );
												item_remove.closest( '.toc-li-wrapper' ).style.setProperty( 'margin-right', 'initial' );
												item_remove.closest( '.toc-li-wrapper' ).style.setProperty( 'padding-left', 'initial' );
												item_remove.closest( '.toc-li-wrapper' ).style.setProperty( 'padding-right', 'initial' );
											}
										}
									} );
								}
							} );
						};
						window.addEventListener( 'scroll', () => {
							if ( module.classList.contains( 'et_pb_sticky' ) ) {
								highlight_active();
							}

							if( ! ( get_settings( 'default_collapse_state' ) === 'expanded' ) ) {
								return;
							}

							const is_sticky_enabled = get_settings( 'collapsible_with_sticky' ) === 'on';

							if ( ! is_sticky_enabled ) {
								return;
							}

							// if ( ! module.classList.contains( 'et_pb_sticky' ) ) {
							// 	return;
							// }

							if ( body.classList.contains( 'collapse_sticky' ) && module.classList.contains( 'et_pb_sticky' ) ) {
								body.style.setProperty( 'height', '0px', 'important' );
								body.style.setProperty( 'padding', '0px', 'important' );
								container.classList.add( 'collapsed' );
								container.classList.remove( 'expanded' );
							}

							if ( ! module.classList.contains( 'et_pb_sticky' ) ) {
								body.style.setProperty( 'height', height, 'important' );
								body.style.setProperty( 'padding', padding, 'important' );
								container.classList.add( 'expanded' );
								container.classList.remove( 'collapsed' );
							}

							if ( ! container.classList.contains( 'expanded' ) ) {
								expand_icon.style.display = 'none';
								collapse_icon.style.display = 'block';
							} else {
								expand_icon.style.display = 'block';
								collapse_icon.style.display = 'none';
							}
						} );

						if ( container.classList.contains( 'collapsed' ) ) {
							body.style.setProperty( 'height', 0, 'important' );
							body.style.setProperty( 'padding', 0, 'important' );
							if ( expand_icon ) {
								expand_icon.style.display = 'none';
							}
							if ( collapse_icon ) {
								collapse_icon.style.display = 'block';
							}
						}

						body.style.transition = transition;
						header.addEventListener( 'click', ( e ) => {
							let toggle_class = container.classList.contains( 'collapsed' ) ? 'collapsed' : 'expanded';
							container.classList.toggle( toggle_class );
							if ( 'expanded' !== toggle_class ) {
								body.style.setProperty( 'height', height, 'important' );
								body.style.setProperty( 'padding', padding, 'important' );
								body.style.setProperty( 'margin', margin, 'important' );
								body.style.setProperty( 'border', border, 'important' );
								if ( expand_icon ) {
									expand_icon.style.display = 'block';
								}
								if ( collapse_icon ) {
									collapse_icon.style.display = 'none';
								}
							} else {
								container.classList.add( 'collapsed' )
								body.style.setProperty( 'height', 0, 'important' );
								body.style.setProperty( 'padding', 0, 'important' );
								body.style.setProperty( 'margin', 0, 'important' );
								body.style.setProperty( 'border', 'none', 'important' );
								if ( expand_icon ) {
									expand_icon.style.display = 'none';
								}
								if ( collapse_icon ) {
									collapse_icon.style.display = 'block';
								}
							}
						} )
					}

					const handle_active_color = () => {
						const module = document.querySelector( current_order_class );

						const body = module.querySelector( '.difl_toc_main_container .body_container' );

						if ( ! body.classList.contains( 'height_enable' ) ) {
							body.style.setProperty( 'height', getComputedStyle( body ).height, 'important' );
						}

						let offset = get_offset();

						const links = module.querySelectorAll( '.difl_toc_main_container .body_container a[class*="difl--toc--anchor"]' );

						links.forEach( link => {
							$( link ).off( 'click' );

							link.addEventListener( 'click', e => {
								active_using_click = true;
								links.forEach( link => link.classList.remove( 'active' ) );
								link.classList.add( 'active' );
								e.preventDefault();
								const id = e.target.getAttribute( 'href' ).substring( 1 );
								const element = document.getElementById( id );
								if ( element ) {
									const position = element.getBoundingClientRect().top + window.pageYOffset - offset;
									window.scrollTo( { top: position, behavior: 'smooth' } );
								}
								setTimeout( () => {
									active_using_click = false;
								}, 1000 );
							} );
						} );
					}

					window.addEventListener( 'load', handle_active_color )
					document.addEventListener( 'DOMContentLoaded', () => {
						handle_multiple_modules();
					} );
				}
			)();
        </script>
		<?php
	}

}
