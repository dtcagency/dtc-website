<?php

namespace DIFL\Server\Modules\AdvancedTabItem;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class AdvancedTabItem implements DependencyInterface {
	use ModuleUtil;
	use HasProps;
    use \DIFL\Server\Traits\StyleUtil;

	protected $config_file = 'AdvancedTabItem';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/advancedtabitem' )->customCssFields;
	}

	public function get_settings_modal_toggles() {
		return [
			'general'  => [
				'toggles' => [
					'content'             => esc_html__( 'Content', 'divi_flash' ),
					'at_icon'             => esc_html__( 'Nav Icon Settings', 'divi_flash' ),
					'image'               => esc_html__( 'Image', 'divi_flash' ),
					'navigation_settings' => esc_html__( 'Navigation Settings', 'divi_flash' ),
					'at_button'           => esc_html__( 'Button', 'divi_flash' ),
					'text_area'           => esc_html__( 'Text Area Background', 'divi_flash' ),
				],
			],
			'advanced' => [
				'toggles' => [
					'nav_text'  => [
						'title'             => esc_html__( 'Nav Item Text', 'divi_flash' ),
						'tabbed_subtoggles' => true,
						'sub_toggles'       => [
							'title'    => [
								'name' => 'Title',
							],
							'subtitle' => [
								'name' => 'Description',
							],
						],
					],
					'image'     => esc_html__( 'Image Styles', 'divi_flash' ),
					'text'      => [
						'title'             => esc_html__( 'Text', 'divi_flash' ),
						'tabbed_subtoggles' => true,
						'bb_icons_support'  => true,
						'sub_toggles'       => [
							'p'     => [
								'name' => 'P',
								'icon' => 'text-left',
							],
							'a'     => [
								'name' => 'A',
								'icon' => 'text-link',
							],
							'ul'    => [
								'name' => 'UL',
								'icon' => 'list',
							],
							'ol'    => [
								'name' => 'OL',
								'icon' => 'numbered-list',
							],
							'quote' => [
								'name' => 'QUOTE',
								'icon' => 'text-quote',
							],
						],
					],
					'header'    => [
						'title'             => esc_html__( 'Heading Text', 'et_builder' ),
						'tabbed_subtoggles' => true,
						'sub_toggles'       => [
							'h1' => [
								'name' => 'H1',
								'icon' => 'text-h1',
							],
							'h2' => [
								'name' => 'H2',
								'icon' => 'text-h2',
							],
							'h3' => [
								'name' => 'H3',
								'icon' => 'text-h3',
							],
							'h4' => [
								'name' => 'H4',
								'icon' => 'text-h4',
							],
							'h5' => [
								'name' => 'H5',
								'icon' => 'text-h5',
							],
							'h6' => [
								'name' => 'H6',
								'icon' => 'text-h6',
							],
						],
					],
					'at_button' => esc_html__( 'Button', 'divi_flash' ),
					'text_area' => esc_html__( 'Content Area Styles', 'divi_flash' ),
				],
			],
		];
	}

	public function get_advanced_fields_config() {
		$advanced_fields = [];

		// $advanced_fields['text'] = false;
		$advanced_fields['fonts']      = [
			'title'    => [
				'label'       => et_builder_i18n( 'Title' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_title",
				],
				'line_height' => [
					'default' => floatval( et_get_option( 'body_font_height', '1.7' ) ) . 'em',
				],
				'font_size'   => [
					'default' => '16px',
				],
				'toggle_slug' => 'nav_text',
				'sub_toggle'  => 'title',
			],
			'subtitle' => [
				'label'       => et_builder_i18n( 'Sub Title' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_subtitle",
				],
				'line_height' => [
					'default' => floatval( et_get_option( 'body_font_height', '1.7' ) ) . 'em',
				],
				'font_size'   => [
					'default' => absint( et_get_option( 'body_font_size', '14' ) ) . 'px',
				],
				'toggle_slug' => 'nav_text',
				'sub_toggle'  => 'subtitle',
			],
			'text'     => [
				'label'       => et_builder_i18n( 'Text' ),
				'css'         => [
					'main'        => ".difl_advancedtab {$this->main_css_element} .df_at_content",
					'line_height' => ".difl_advancedtab {$this->main_css_element} .df_at_content",
					'color'       => ".difl_advancedtab {$this->main_css_element} .df_at_content",
				],
				'line_height' => [
					'default' => floatval( et_get_option( 'body_font_height', '1.7' ) ) . 'em',
				],
				'font_size'   => [
					'default' => absint( et_get_option( 'body_font_size', '14' ) ) . 'px',
				],
				'toggle_slug' => 'text',
				'sub_toggle'  => 'p',
				// 'hide_text_align' => true,
			],
			'link'     => [
				'label'           => et_builder_i18n( 'Link' ),
				'css'             => [
					'main'  => ".difl_advancedtab {$this->main_css_element} .df_at_content a",
					'color' => ".difl_advancedtab {$this->main_css_element} .df_at_content a",
				],
				'line_height'     => [
					'default' => '1em',
				],
				'font_size'       => [
					'default' => absint( et_get_option( 'body_font_size', '14' ) ) . 'px',
				],
				'hide_text_align' => true,
				'toggle_slug'     => 'text',
				'sub_toggle'      => 'a',
			],
			'ul'       => [
				'label'       => esc_html__( 'Unordered List', 'et_builder' ),
				'css'         => [
					'main'        => ".difl_advancedtab {$this->main_css_element} .df_at_content ul li",
					'color'       => ".difl_advancedtab {$this->main_css_element} .df_at_content ul li",
					'line_height' => ".difl_advancedtab {$this->main_css_element} .df_at_content ul li",
					'item_indent' => ".difl_advancedtab {$this->main_css_element} .df_at_content ul",
				],
				'line_height' => [
					'default' => '1em',
				],
				'font_size'   => [
					'default' => '14px',
				],
				'toggle_slug' => 'text',
				'sub_toggle'  => 'ul',
			],
			'ol'       => [
				'label'       => esc_html__( 'Ordered List', 'et_builder' ),
				'css'         => [
					'main'        => ".difl_advancedtab {$this->main_css_element} .df_at_content ol li",
					'color'       => ".difl_advancedtab {$this->main_css_element} .df_at_content ol li",
					'line_height' => ".difl_advancedtab {$this->main_css_element} .df_at_content ol li",
					'item_indent' => ".difl_advancedtab {$this->main_css_element} .df_at_content ol",
				],
				'line_height' => [
					'default' => '1em',
				],
				'font_size'   => [
					'default' => '14px',
				],
				'toggle_slug' => 'text',
				'sub_toggle'  => 'ol',
			],
			'quote'    => [
				'label'       => esc_html__( 'Blockquote', 'et_builder' ),
				'css'         => [
					'main'  => ".difl_advancedtab {$this->main_css_element} .df_at_content blockquote",
					'color' => ".difl_advancedtab {$this->main_css_element} .df_at_content blockquote",
				],
				'line_height' => [
					'default' => '1em',
				],
				'font_size'   => [
					'default' => '14px',
				],
				'toggle_slug' => 'text',
				'sub_toggle'  => 'quote',
			],
			'header'   => [
				'label'       => esc_html__( 'Heading', 'et_builder' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_content h1",
				],
				'font_size'   => [
					'default' => absint( et_get_option( 'body_header_size', '30' ) ) . 'px',
				],
				'toggle_slug' => 'header',
				'sub_toggle'  => 'h1',
			],
			'header_2' => [
				'label'       => esc_html__( 'Heading 2', 'et_builder' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_content h2",
				],
				'font_size'   => [
					'default' => '26px',
				],
				'line_height' => [
					'default' => '1em',
				],
				'toggle_slug' => 'header',
				'sub_toggle'  => 'h2',
			],
			'header_3' => [
				'label'       => esc_html__( 'Heading 3', 'et_builder' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_content h3",
				],
				'font_size'   => [
					'default' => '22px',
				],
				'line_height' => [
					'default' => '1em',
				],
				'toggle_slug' => 'header',
				'sub_toggle'  => 'h3',
			],
			'header_4' => [
				'label'       => esc_html__( 'Heading 4', 'et_builder' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_content h4",
				],
				'font_size'   => [
					'default' => '18px',
				],
				'line_height' => [
					'default' => '1em',
				],
				'toggle_slug' => 'header',
				'sub_toggle'  => 'h4',
			],
			'header_5' => [
				'label'       => esc_html__( 'Heading 5', 'et_builder' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_content h5",
				],
				'font_size'   => [
					'default' => '16px',
				],
				'line_height' => [
					'default' => '1em',
				],
				'toggle_slug' => 'header',
				'sub_toggle'  => 'h5',
			],
			'header_6' => [
				'label'       => esc_html__( 'Heading 6', 'et_builder' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_content h6",
				],
				'font_size'   => [
					'default' => '14px',
				],
				'line_height' => [
					'default' => '1em',
				],
				'toggle_slug' => 'header',
				'sub_toggle'  => 'h6',
			],
			'button'   => [
				'label'       => et_builder_i18n( 'Button' ),
				'css'         => [
					'main' => ".difl_advancedtab {$this->main_css_element} .df_at_button",
				],
				'line_height' => [
					'default' => floatval( et_get_option( 'body_font_height', '1.7' ) ) . 'em',
				],
				'font_size'   => [
					'default' => absint( et_get_option( 'body_font_size', '14' ) ) . 'px',
				],
				'toggle_slug' => 'at_button',
			],
		];
		$advanced_fields['borders']    = [
			'default'         => [
				'css' => [
					'main' => [
						'border_radii'        => ".df_at_all_tabs {$this->main_css_element}",
						'border_radii_hover'  => ".df_at_all_tabs {$this->main_css_element}:hover",
						'border_styles'       => ".df_at_all_tabs {$this->main_css_element}",
						'border_styles_hover' => ".df_at_all_tabs {$this->main_css_element}:hover",
					],
				],
			],
			'button'          => [
				'css'         => [
					'main' => [
						'border_radii'        => ".difl_advancedtab {$this->main_css_element} .df_at_button",
						'border_radii_hover'  => ".difl_advancedtab {$this->main_css_element} .df_at_button:hover",
						'border_styles'       => ".difl_advancedtab {$this->main_css_element} .df_at_button",
						'border_styles_hover' => ".difl_advancedtab {$this->main_css_element} .df_at_button:hover",
					],
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'at_button',
			],
			'image'           => [
				'css'         => [
					'main' => [
						'border_radii'        => ".difl_advancedtab {$this->main_css_element} .df_at_image",
						'border_radii_hover'  => ".difl_advancedtab {$this->main_css_element} .df_at_image:hover",
						'border_styles'       => ".difl_advancedtab {$this->main_css_element} .df_at_image",
						'border_styles_hover' => ".difl_advancedtab {$this->main_css_element} .df_at_image:hover",
					],
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'image',
			],
			'textarea_border' => [
				'css'         => [
					'main'      => [
						'border_radii'        => ".difl_advancedtab {$this->main_css_element} .df_at_content_wrapper",
						'border_radii_hover'  => ".difl_advancedtab {$this->main_css_element} .df_at_content_wrapper:hover",
						'border_styles'       => ".difl_advancedtab {$this->main_css_element} .df_at_content_wrapper",
						'border_styles_hover' => ".difl_advancedtab {$this->main_css_element} .df_at_content_wrapper:hover",
					],
					'important' => 'all',
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'text_area',
			],
		];
		$advanced_fields['box_shadow'] = [
			'default'         => [
				'css' => [
					'main'      => ".difl_advancedtab {$this->main_css_element}.et_pb_module",
					'hover'     => ".difl_advancedtab {$this->main_css_element}.et_pb_module:hover",
					'important' => 'all',
				],
			],
			'button'          => [
				'css'         => [
					'main'  => ".difl_advancedtab {$this->main_css_element} .df_at_button",
					'hover' => ".difl_advancedtab {$this->main_css_element} .df_at_button:hover",
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'at_button',
			],
			'image'           => [
				'css'         => [
					'main'  => ".difl_advancedtab {$this->main_css_element} .df_at_image",
					'hover' => ".difl_advancedtab {$this->main_css_element} .df_at_image:hover",
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'image',
			],
			'textarea_shadow' => [
				'css'         => [
					'main'      => ".difl_advancedtab {$this->main_css_element} .df_at_content_wrapper",
					'hover'     => ".difl_advancedtab {$this->main_css_element} .df_at_content_wrapper:hover",
					'important' => 'all',
				],
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'text_area',
			],
		];
		$advanced_fields['filters']    = [
			'child_filters_target' => [
				'tab_slug'    => 'advanced',
				'toggle_slug' => 'image',
				'css'         => [
					'main' => '%%order_class%% img',
				],
			],
		];

		$advanced_fields['image']          = [
			'css' => [
				'main' => [
					'%%order_class%% img',
				],
			],
		];
		$advanced_fields['transform']      = false;
		$advanced_fields['background']     = [
			'css' => [
				'main' => "{$this->main_css_element}.et_pb_module",
			],
		];
		$advanced_fields['max_width']      = [
			'css' => [
				'main' => "{$this->main_css_element}.et_pb_module",
			],
		];
		$advanced_fields['margin_padding'] = [
			'css' => [
				'main'      => "{$this->main_css_element}.et_pb_module",
				'important' => 'all',
			],
		];
		$advanced_fields['link_options']   = false;

		return $advanced_fields;
	}

	public function get_fields() {
		$general               = [
			'admin_label'        => [
				'label'            => esc_html__( 'Admin Label', 'divi_flash' ),
				'type'             => 'text',
				'option_category'  => 'basic_option',
				'toggle_slug'      => 'admin_label',
				'default_on_front' => 'Tab Item',
			],
			'title'              => [
				'label'           => esc_html__( 'Tab Label', 'divi_flash' ),
				'description'     => esc_html__( 'Dynamic Content Shown Only Frontend', 'divi_flash' ),
				'type'            => 'text',
				'toggle_slug'     => 'content',
				'dynamic_content' => 'text',
			],
			'subtitle'           => [
				'label'           => esc_html__( 'Tab Description', 'divi_flash' ),
				'description'     => esc_html__( 'Dynamic Content Shown Only Frontend', 'divi_flash' ),
				'type'            => 'textarea',
				'toggle_slug'     => 'content',
				'dynamic_content' => 'text',
			],
			'content_type'       => [
				'default'         => 'content',
				'label'           => esc_html__( 'Content Type', 'divi_flash' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => [
					'content' => esc_html__( 'Content', 'divi_flash' ),
					'library' => esc_html__( 'Library Item', 'divi_flash' ),
				],
				'toggle_slug'     => 'content',

			],
			'content'            => [
				'label'           => esc_html__( 'Body', 'divi_flash' ),
				'type'            => 'tiny_mce',
				'toggle_slug'     => 'content',
				'dynamic_content' => 'text',
				'show_if_not'     => [ 'content_type' => 'library' ],
			],
			'library_item'       => [
				'default'          => 'none',
				'label'            => esc_html__( 'Library Layout', 'divi_flash' ),
				'type'             => 'select',
				'option_category'  => 'configuration',
				'options'          => df_load_library(),
				'toggle_slug'      => 'content',
				'show_if'          => [ 'content_type' => 'library' ],
				'computed_affects' => [
					'__libraryShortcode',
				],
			],
			'__libraryShortcode' => [
				'type'                => 'computed',
				'computed_callback'   => [ 'DIFL_AdvancedTabItem', 'df_divi_library_shortcode' ],
				'computed_depends_on' => [
					'library_item',
				],
			],
		];
		$icon                  = [
			'use_icon'   => [
				'label'           => esc_html__( 'Use Icon', 'divi_flash' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => [
					'off' => esc_html__( 'No', 'divi_flash' ),
					'on'  => esc_html__( 'Yes', 'divi_flash' ),
				],
				'toggle_slug'     => 'at_icon',
				'tab_slug'        => 'general',
				'affects'         => [
					'font_icon',
					'icon_color',
					'icon_size',
				],
			],
			'font_icon'  => [
				'label'           => esc_html__( 'Icon', 'divi_flash' ),
				'type'            => 'select_icon',
				'option_category' => 'basic_option',
				'class'           => [ 'et-pb-font-icon' ],
				'toggle_slug'     => 'at_icon',
				'tab_slug'        => 'general',
				'depends_show_if' => 'on',
			],
			'icon_color' => [
				'label'           => esc_html__( 'Icon Color', 'divi_flash' ),
				'type'            => 'color-alpha',
				'description'     => esc_html__( 'Here you can define a custom color for your icon.', 'divi_flash' ),
				'depends_show_if' => 'on',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'at_icon',
				'hover'           => 'tabs',
			],
			'icon_size'  => [
				'label'           => esc_html__( 'Icon Size', 'divi_flash' ),
				'type'            => 'range',
				'option_category' => 'font_option',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'at_icon',
				'default_unit'    => 'px',
				'range_settings'  => [
					'min'  => '1',
					'max'  => '120',
					'step' => '1',
				],
				'mobile_options'  => true,
				'depends_show_if' => 'on',
				'responsive'      => true,
			],
			'tab_image'  => [
				'label'              => esc_html__( 'Tab Image', 'dgat-advanced-tabs' ),
				'description'        => esc_html__( 'Dynamic Content Shown Only Frontend', 'divi_flash' ),
				'type'               => 'upload',
				'option_category'    => 'basic_option',
				'upload_button_text' => esc_attr__( 'Upload an image', 'dgat-advanced-tabs' ),
				'choose_text'        => esc_attr__( 'Choose an Image', 'dgat-advanced-tabs' ),
				'update_text'        => esc_attr__( 'Set As Image', 'dgat-advanced-tabs' ),
				'toggle_slug'        => 'at_icon',
				'dynamic_content'    => 'image',
				'show_if_not'        => [ 'use_icon' => 'on' ],
			],
		];
		$image                 = [
			'image'         => [
				'label'              => esc_html__( 'Image', 'divi_flash' ),
				'type'               => 'upload',
				'option_category'    => 'basic_option',
				'upload_button_text' => esc_attr__( 'Upload an image', 'divi_flash' ),
				'choose_text'        => esc_attr__( 'Choose an Image', 'divi_flash' ),
				'update_text'        => esc_attr__( 'Set As Image', 'divi_flash' ),
				'toggle_slug'        => 'image',
				'dynamic_content'    => 'image',
				// 'show_if_not'           => array('use_library_item' => 'on')
			],
			'alt'           => [
				'label'       => esc_html__( 'Alt Text', 'divi_flash' ),
				'type'        => 'text',
				'toggle_slug' => 'image',
				// 'show_if_not'           => array('use_library_item' => 'on')
			],
			'iamge_place'   => [
				'label'               => esc_html__( 'Image Placement', 'divi_flash' ),
				'type'                => 'composite',
				'tab_slug'            => 'general',
				'toggle_slug'         => 'image',
				'composite_type'      => 'default',
				// 'show_if_not'       => array('use_library_item' => 'on'),
				'composite_structure' => [
					'desktop' => [
						'icon'     => 'desktop',
						'controls' => [
							'img_placement'       => [
								'label'       => esc_html__( 'Image Placement Desktop', 'divi_flash' ),
								'type'        => 'select',
								'default'     => 'top',
								'options'     => [
									'flex_top'    => esc_html__( 'Default', 'divi_flash' ),
									'flex_bottom' => esc_html__( 'Bottom', 'divi_flash' ),
									'flex_left'   => esc_html__( 'Left', 'divi_flash' ),
									'flex_right'  => esc_html__( 'Right', 'divi_flash' ),
								],
								'toggle_slug' => 'image',
								'tab_slug'    => 'general',
							],
							'img_container_width' => [
								'label'            => esc_html__( 'Container Width Desktop', 'divi_flash' ),
								'type'             => 'range',
								'toggle_slug'      => 'image',
								'tab_slug'         => 'general',
								'default'          => '50%',
								'default_unit'     => '%',
								'default_on_front' => '50%',
								'range_settings'   => [
									'min'  => '1',
									'max'  => '100',
									'step' => '1',
								],
								'show_if'          => [
									'img_placement' => [ 'flex_left', 'flex_right' ],
								],
							],
						],
					],
					'tablet'  => [
						'icon'     => 'tablet',
						'controls' => [
							'img_placement_tablet'       => [
								'label'       => esc_html__( 'Image Placement Tablet', 'divi_flash' ),
								'type'        => 'select',
								'default'     => 'top',
								'options'     => [
									'flex_top'    => esc_html__( 'Default', 'divi_flash' ),
									'flex_bottom' => esc_html__( 'Bottom', 'divi_flash' ),
									'flex_left'   => esc_html__( 'Left', 'divi_flash' ),
									'flex_right'  => esc_html__( 'Right', 'divi_flash' ),
								],
								'toggle_slug' => 'image',
								'tab_slug'    => 'general',
							],
							'img_container_width_tablet' => [
								'label'            => esc_html__( 'Container Width Tablet', 'divi_flash' ),
								'type'             => 'range',
								'toggle_slug'      => 'image',
								'tab_slug'         => 'general',
								'default'          => '50%',
								'default_unit'     => '%',
								'default_on_front' => '50%',
								'range_settings'   => [
									'min'  => '1',
									'max'  => '100',
									'step' => '1',
								],
								'show_if'          => [
									'img_placement_tablet' => [ 'flex_left', 'flex_right' ],
								],
							],
						],
					],
					'phone'   => [
						'icon'     => 'phone',
						'controls' => [
							'img_placement_phone'       => [
								'label'       => esc_html__( 'Image Placement Mobile', 'divi_flash' ),
								'type'        => 'select',
								'default'     => 'top',
								'options'     => [
									'flex_top'    => esc_html__( 'Default', 'divi_flash' ),
									'flex_bottom' => esc_html__( 'Bottom', 'divi_flash' ),
									'flex_left'   => esc_html__( 'Left', 'divi_flash' ),
									'flex_right'  => esc_html__( 'Right', 'divi_flash' ),
								],
								'toggle_slug' => 'image',
								'tab_slug'    => 'general',
							],
							'img_container_width_phone' => [
								'label'            => esc_html__( 'Container Width Mobile', 'divi_flash' ),
								'type'             => 'range',
								'toggle_slug'      => 'image',
								'tab_slug'         => 'general',
								'default'          => '50%',
								'default_unit'     => '%',
								'default_on_front' => '50%',
								'range_settings'   => [
									'min'  => '1',
									'max'  => '100',
									'step' => '1',
								],
								'show_if'          => [
									'img_placement_phone' => [ 'flex_left', 'flex_right' ],
								],
							],
						],
					],
				],
			],
			'image_z_index' => [
				'label'          => esc_html__( 'Image Z-index', 'divi_flash' ),
				'type'           => 'range',
				'toggle_slug'    => 'image',
				'tab_slug'       => 'advanced',
				'default'        => '0',
				'range_settings' => [
					'min'  => '0',
					'max'  => '15',
					'step' => '1',
				],
				'validate_unit'  => false,
			],
		];
		$navigation_settings   = [
			'default_active'  => [
				'label'           => esc_html__( 'Set as Active', 'divi_flash' ),
				'description'     => esc_html__( 'Set the default active tab for User.', 'divi_flash' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'options'         => [
					'off' => esc_html__( 'No', 'divi_flash' ),
					'on'  => esc_html__( 'Yes', 'divi_flash' ),
				],
				'default'         => 'off',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'navigation_settings',
			],
			'navigation_link' => [
				'label'           => esc_html__( 'Navigation Link', 'divi_flash' ),
				'description'     => esc_html__( 'Use this link to landing this web page and open specific tab. Just click, it will copy your link in clipboard.', 'divi_flash' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'navigation_settings',
			],
		];
		$at_button             = [
			'at_button_button_text'           => [
				'label'           => esc_html__( 'Button Text', 'divi_flash' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Input your desired button text, or leave blank for no button.', 'divi_flash' ),
				'toggle_slug'     => 'at_button',
				'tab_slug'        => 'general',
				'dynamic_content' => 'text',
				// 'show_if_not'     => array('use_library_item' => 'on')
			],
			'at_button_button_url'            => [
				'label'           => esc_html__( 'Button URL', 'divi_flash' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Input URL for your button.', 'divi_flash' ),
				'toggle_slug'     => 'at_button',
				'tab_slug'        => 'general',
				'dynamic_content' => 'url',
				// 'show_if_not'     => array('use_library_item' => 'on')
			],
			'at_button_button_url_new_window' => [
				'default'          => 'off',
				'default_on_front' => true,
				'label'            => esc_html__( 'Url Opens', 'divi_flash' ),
				'type'             => 'select',
				'option_category'  => 'configuration',
				'options'          => [
					'off' => esc_html__( 'In The Same Window', 'divi_flash' ),
					'on'  => esc_html__( 'In The New Tab', 'divi_flash' ),
				],
				'toggle_slug'      => 'at_button',
				'tab_slug'         => 'general',
				// 'show_if_not'     => array('use_library_item' => 'on')
			],
		];
		$button                = [
			'button_align' => [
				'label'       => esc_html__( 'Alignment', 'divi_flash' ),
				'type'        => 'text_align',
				'options'     => et_builder_get_text_orientation_options( [ 'justified' ] ),
				'toggle_slug' => 'at_button',
				'tab_slug'    => 'advanced',
			],
		];
		$image_sizing          = $this->df_add_max_width( [
			'key'         => 'image_size',
			'toggle_slug' => 'image',
			'alignment'   => true,
			'tab_slug'    => 'advanced',
		] );
		$button_background     = $this->df_add_bg_field( [
			'label'       => 'Background',
			'key'         => 'button',
			'toggle_slug' => 'at_button',
			'tab_slug'    => 'advanced',
		] );
		$button_spacing        = $this->add_margin_padding( [
			'title'       => 'Button',
			'key'         => 'button',
			'toggle_slug' => 'at_button',
			'tab_slug'    => 'advanced',
		] );
		$image_wrapper_spacing = $this->add_margin_padding( [
			'title'       => 'Image Wrapper',
			'key'         => 'image_wrapper',
			'toggle_slug' => 'margin_padding',
		] );
		$item_content_spacing  = $this->add_margin_padding( [
			'title'       => 'Content Wrapper',
			'key'         => 'content',
			'toggle_slug' => 'margin_padding',
		] );
		$text_area_background  = $this->df_add_bg_field( [
			'label'       => 'Nav Item Background',
			'key'         => 'text_area',
			'toggle_slug' => 'text_area',
			'tab_slug'    => 'general',
		] );

		return array_merge(
			$general,
			$icon,
			$image,
			$image_sizing,
			$navigation_settings,
			$at_button,
			$button,
			$text_area_background,
			$button_background,
			$button_spacing,
			$image_wrapper_spacing,
			$item_content_spacing
		);
	}

	public function get_transition_fields_css_props() {
		// $fields = parent::get_transition_fields_css_props();
		$button = '%%order_class%% .df_at_button';
		$image  = '%%order_class%% .df_at_image';

		$fields['icon_color']          = [ 'color' => '%%order_class%%.df_at_nav .et-pb-icon' ];
		$fields['image_size_maxwidth'] = [ 'max-width' => $image ];

		$fields = $this->df_background_transition( [
			'fields'   => $fields,
			'key'      => 'button',
			'selector' => '%%order_class%% .df_at_button',
		] );
		$fields = $this->df_background_transition( [
			'fields'   => $fields,
			'key'      => 'text_area',
			'selector' => '%%order_class%% .df_at_content_wrapper',
		] );

		// spacing
		$fields['image_wrapper_margin']  = [ 'margin' => '%%order_class%% .df_at_image_wrapper' ];
		$fields['image_wrapper_padding'] = [ 'padding' => '%%order_class%% .df_at_image_wrapper' ];

		$fields['content_margin']  = [ 'margin' => '%%order_class%% .df_at_content_wrapper' ];
		$fields['content_padding'] = [ 'padding' => '%%order_class%% .df_at_content_wrapper' ];

		$fields['button_margin']  = [ 'margin' => $button ];
		$fields['button_padding'] = [ 'padding' => $button ];

		// border
		$fields = $this->df_fix_border_transition(
			$fields,
			'button',
			$button
		);
		$fields = $this->df_fix_border_transition(
			$fields,
			'image',
			$image
		);

		return $fields;
	}

	public static function df_divi_library_shortcode( $args = [] ) {
		if ( empty( $args['library_item'] ) || 'none' === $args['library_item'] ) {
			return '';
		}

		return do_shortcode( do_blocks( get_post( $args['library_item'] )->post_content ) );
	}

    public static function font_style($font) {
        return static::get_font_style($font);
    }

//	public function maybe_inherit_values() {
//		$order_class                    = ET_Builder_Element::get_module_order_class( 'difl_advancedtabitem' );
//		$page_link                      = esc_html__( get_page_link(), 'divi_flash' );
//		$this->props['navigation_link'] = sprintf( '%1$s#%2$s',
//			$page_link,
//			esc_html__( $order_class, 'divi_flash' )
//		);
//	}

	/**
	 * Render button HTML markup
	 *
	 * @param String $key
	 *
	 * @return String HTML markup of the button
	 */
	public function df_render_button( $key ) {
		$text   = isset( $this->props[ $key . '_button_text' ] ) ? $this->props[ $key . '_button_text' ] : '';
		$url    = isset( $this->props[ $key . '_button_url' ] ) ? $this->props[ $key . '_button_url' ] : '';
		$target = $this->props[ $key . '_button_url_new_window' ] === 'on' ?
			'target="_blank"' : '';
		if ( $text !== '' || $url !== '' ) {
			return sprintf( '<div class="df_at_button_wrapper">
                <a class="df_at_button" href="%1$s" %3$s>%2$s</a>
            </div>',
				esc_attr( $url ), esc_html( $text ), $target );
		} else {
			return '';
		}
	}

	/**
	 * Format Body (tiny_mce) for frontend: same autop + shortcode order as Divi content elements / VB.
	 *
	 * @param string $raw Raw content attribute.
	 * @return string
	 */
	private function df_process_tab_body_content( $raw ) {
		if ( '' === $raw || null === $raw ) {
			return '';
		}
		$html = wp_kses_post( $raw );
		$html = preg_replace( '/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/i', '<p>&nbsp;</p>', $html );
		$html = wpautop( $html );
		$html = shortcode_unautop( $html );
		return do_shortcode( $html );
	}

	public function df_render_content( $content_container ) {
		if ( $this->props['content_type'] == 'library' ) {
			$library = self::df_divi_library_shortcode( [
				'library_item' => $this->props['library_item'],
			] );

			return sprintf( '<div class="df_at_content_wrapper">%1$s</div>', $library );
		} else {
			return $content_container;
		}
	}

    /**
     * Get image placement attribute with independent defaults for each breakpoint.
     * Tablet and phone do NOT inherit from desktop - they default to 'column' independently.
     *
     * @param array|null $innerContent The innerContent array with responsive values.
     * @return array The innerContent with independent default values for each breakpoint.
     */
    protected static function get_image_place_with_independent_defaults( $innerContent ) {
        $default_value = 'column';

        return [
            'desktop' => [
                'value' => $innerContent['desktop']['value'] ?? $default_value,
            ],
            'tablet' => [
                'value' => $innerContent['tablet']['value'] ?? $default_value,
            ],
            'phone' => [
                'value' => $innerContent['phone']['value'] ?? $default_value,
            ],
        ];
    }

    /**
     * Add responsive fallback values to an attribute's innerContent.
     * Ensures tablet inherits from desktop (not phone) when no tablet value is set.
     *
     * @param array|null $innerContent The innerContent array with responsive values.
     * @return array|null The innerContent with proper fallback values added.
     */
    protected static function add_responsive_fallback( $innerContent ) {
        if ( empty( $innerContent ) || ! is_array( $innerContent ) ) {
            return $innerContent;
        }

        // Fallback order: tablet falls back to desktop, phone falls back to tablet then desktop
        $fallback_order = [
            'tablet'     => [ 'tablet', 'desktop' ],
            'tabletWide' => [ 'tabletWide', 'desktop' ],
            'phone'      => [ 'phone', 'tablet', 'desktop' ],
            'phoneWide'  => [ 'phoneWide', 'phone', 'tablet', 'desktop' ],
        ];

        foreach ( $fallback_order as $device => $fallbacks ) {
            // Skip if device already has a value
            if ( isset( $innerContent[ $device ]['value'] ) ) {
                continue;
            }

            // Find the first fallback that has a value
            foreach ( $fallbacks as $fallback_device ) {
                if ( isset( $innerContent[ $fallback_device ]['value'] ) ) {
                    $innerContent[ $device ]['value'] = $innerContent[ $fallback_device ]['value'];
                    break;
                }
            }
        }

        return $innerContent;
    }

    public function get_common_styles( $args ) {
        $attrs      = $this->get_merged_attrs( $args );
        $attrs      = self::build_attr_for_styles( $attrs );
        $attrs      = self::get_default_value_for_styles( $attrs );
        $orderClass = $args['orderClass'] ?? '';

        $styles = [];

        // Image placement with independent responsive defaults (not inherited from desktop)
        $image_place_attr = self::get_image_place_with_independent_defaults( $attrs['iamge_place']['innerContent'] ?? null );
        $common_styles[] = CommonStyle::style( [
            'selector'  => "{$orderClass} .df_ati_container",
            'attr'      => $image_place_attr,
            'property'  => 'flex-direction',
			'important' => true,
        ] );
        $common_styles[] = CommonStyle::style( [
            'selector'  => "{$orderClass}.df_at_nav .et-pb-icon, {$orderClass}.df_at_nav:hover .et-pb-icon",
            'attr'      => $attrs['icon_color']['innerContent'] ?? null,
            'property'  => 'color',
			'important' => true,
        ] );
        $common_styles[] = CommonStyle::style( [
            'selector'  => "{$orderClass}.df_at_nav .et-pb-icon",
            'attr'      => $attrs['icon_size']['innerContent'] ?? null,
            'property'  => 'font-size',
        ] );

		$common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .at_icon_wrap .et-pb-icon",
            'attr'                => $attrs['font_icon']['innerContent'] ?? null,
            'declarationFunction' => function( $props ) {
				return self::font_style( $props['attrValue'] );
			}
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .df_ati_container .df_at_button_wrapper",
            'attr'      => $attrs['button']['innerContent'] ?? null,
            'property'  => 'text-align',
            'important' => true,
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .df_ati_container .df_at_image_wrapper",
            'attr'      => $attrs['image_z_index']['innerContent'] ?? null,
            'property'  => 'z-index',
            'important' => true,
        ]);

        // Image container width - applies width for row/row-reverse, resets to auto for column/column-reverse
        $img_width_raw = $attrs['img_container_width']['innerContent'] ?? [];
        $image_place_raw = $attrs['iamge_place']['innerContent'] ?? [];
        $default_width = [ 'value' => '50%' ];
        
        // Helper to get placement value for a device
        $get_placement = function( $device ) use ( $image_place_raw ) {
            if ( isset( $image_place_raw[ $device ]['value'] ) ) {
                $value = $image_place_raw[ $device ]['value'];
                return ( $value === '' ) ? 'column' : $value;
            }
            return 'column';
        };
        
        // Helper to check if placement is row-based
        $is_row_layout = function( $placement ) {
            return in_array( $placement, [ 'row', 'row-reverse' ], true );
        };
        
        // Get placement for each breakpoint
        $desktop_placement = $get_placement( 'desktop' );
        $tablet_placement = $get_placement( 'tablet' );
        $phone_placement = $get_placement( 'phone' );
        
        // Get width values with fallback
        $desktop_width = $img_width_raw['desktop'] ?? $default_width;
        $tablet_width = $img_width_raw['tablet'] ?? $desktop_width;
        $phone_width = $img_width_raw['phone'] ?? $tablet_width;
        
        // Build attr for row layouts (where we apply width)
        $row_width_attr = [];
        if ( $is_row_layout( $desktop_placement ) ) {
            $row_width_attr['desktop'] = $desktop_width;
        }
        if ( $is_row_layout( $tablet_placement ) ) {
            $row_width_attr['tablet'] = $tablet_width;
        }
        if ( $is_row_layout( $phone_placement ) ) {
            $row_width_attr['phone'] = $phone_width;
        }
        
        // Build attr for column layouts (where we reset to auto)
        $column_auto_attr = [];
        if ( ! $is_row_layout( $desktop_placement ) ) {
            $column_auto_attr['desktop'] = [ 'value' => 'auto' ];
        }
        if ( ! $is_row_layout( $tablet_placement ) ) {
            $column_auto_attr['tablet'] = [ 'value' => 'auto' ];
        }
        if ( ! $is_row_layout( $phone_placement ) ) {
            $column_auto_attr['phone'] = [ 'value' => 'auto' ];
        }
        
        // Apply width for row/row-reverse layouts
        if ( ! empty( $row_width_attr ) ) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_ati_container .df_at_image_wrapper",
                'attr'     => $row_width_attr,
                'property' => 'width',
            ]);
            $common_styles[] = CommonStyle::style([
                'selector'            => "{$orderClass} .df_ati_container .df_at_content_wrapper",
                'attr'                => $row_width_attr,
                'declarationFunction' => function( $props ) {
                    $width = $props['attrValue'] ?? '50%';
                    return "width: calc(100% - {$width});";
                }
            ]);
        }
        
        // Reset to auto for column/column-reverse layouts
        if ( ! empty( $column_auto_attr ) ) {
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_ati_container .df_at_image_wrapper",
                'attr'     => $column_auto_attr,
                'property' => 'width',
            ]);
            $common_styles[] = CommonStyle::style([
                'selector' => "{$orderClass} .df_ati_container .df_at_content_wrapper",
                'attr'     => $column_auto_attr,
                'property' => 'width',
            ]);
        }

        $common_styles[] = CommonStyle::style([
            'selector'  => "{$orderClass} .df_ati_container .df_at_image",
            'attr'      => $attrs['image_size_maxwidth']['innerContent'] ?? null,
            'property'  => 'max-width',
            'important' => true,
        ]);

        $common_styles[] = CommonStyle::style([
            'selector'            => "{$orderClass} .df_ati_container .df_at_image",
            'attr'                => self::add_responsive_fallback( $attrs['image_size_alignment']['innerContent'] ?? null ),
            'declarationFunction' => function( $props ) {
                $value = $props['attrValue'] ?? '';
                if ( 'center' === $value ) {
                    return 'margin-left: auto; margin-right: auto;';
                }
                if ( 'right' === $value ) {
                    return 'margin-left: auto; margin-right: 0;';
                }
                return 'margin-left: 0; margin-right: auto;';
            }
        ]);

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
		// $this->handle_fa_icon();
		global $df_at_data;
		$df_ati_class = $this->order_class;

		$df_at_data[ $df_ati_class ]['default_active'] = 'on' === $this->props['default_active'] ? $this->props['default_active'] : 'off';
		$df_at_data[ $df_ati_class ]['title']          = esc_html( $this->props['title'] );
		$df_at_data[ $df_ati_class ]['subtitle']       = wp_kses_post( $this->props['description'] );
		$df_at_data[ $df_ati_class ]['use_icon']       = esc_html( $this->props['use_icon'] );
		$df_at_data[ $df_ati_class ]['font_icon']      = esc_attr( Utils::process_font_icon( $this->props['font_icon'] ) );
		$df_at_data[ $df_ati_class ]['tab_image']      = esc_url( $this->props['tab_image'] );

		// filter for images
//		if ( array_key_exists( 'image', $this->advanced_fields ) && array_key_exists( 'css', $this->advanced_fields['image'] ) ) {
//			$this->add_classname( $this->generate_css_filters(
//				$render_slug,
//				'child_',
//				self::$data_utils->array_get( $this->advanced_fields['image']['css'], 'main', '%%order_class%%' )
//			) );
//		}
		$image_alt_text = $this->props['alt'] !== '' ? $this->props['alt'] : df_image_alt_by_url( $this->props['image'] );
		$image          = '' !== $this->props['image'] && $this->props['content_type'] !== 'library' ?
			sprintf( '<div class="df_at_image_wrapper"><img class="df_at_image" src="%1$s" alt="%2$s" /></div>',
				esc_attr( $this->props['image'] ),
				esc_attr( $image_alt_text )
			) : '';

		$content = isset( $this->props['content'] ) && $this->props['content'] !== '' ?
			sprintf( '<div class="df_at_content">%1$s</div>', $this->df_process_tab_body_content( $this->props['content'] ) ) : '';

		$content_container = $content !== '' || $this->df_render_button( 'at_button' ) !== '' ?
			sprintf( '<div class="df_at_content_wrapper">%1$s %2$s</div>',
				$content, $this->df_render_button( 'at_button' )
			) : '';

		return sprintf( '<div class="df_ati_container">%2$s%1$s</div>', $this->df_render_content( $content_container ), $image );
	}
}
