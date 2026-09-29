<?php

namespace DIFL\Server\Modules\Faq;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;


class Faq implements DependencyInterface {
	public $slug       = 'difl_faq';
    public $child_slug = 'difl_faqitem';
    public $vb_support = 'on';
	public $icon_path;
    use ModuleUtil;
    use HasProps;

	protected $config_file = 'Faq';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	);

	// public function init() {
    //     $this->name = esc_html__( 'Animated Heading', 'divi_flash' );
    //     $this->main_css_element = "%%order_class%%";
    //     $this->icon_path        =  DIFL_ADMIN_DIR_PATH . 'dashboard/static/module-icons/animated-heading.svg';
	// }

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/faq' )->customCssFields;
	}

    public function generateStyle($value, $key, $_default = '') {
        if ($value) {
            return $key . ': ' . ($_default !== '' ? $_default : $value) . ';';
        } else {
            return '';
        }
    }

    public function propMatches( $key, $value ) {
        return isset( $this->props[ $key ] ) && $this->props[ $key ] === $value;
    }

    public function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

    public function get_common_styles( $args ) {
		$attrs      = $args['attrs'] ?? [];
        $attrs      = $this->get_merged_attrs( $args );
        $attrs      = self::build_attr_for_styles( $attrs );
        $attrs      = $this->get_default_value_for_styles($attrs);
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_item .faq_question_wrapper",
			'attr'     => $attrs['faq_layout']['innerContent'] ?? null,
			'declarationFunction' => function(){
                $cursorValue = isset($this->props['faq_layout']) && $this->props['faq_layout'] === 'plain' ? 'inherit' : 'pointer';
                return $this->generateStyle($this->props['faq_layout'] ?? null, 'cursor', $cursorValue);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_item .faq_question_wrapper",
			'attr'     => $attrs['faq_que_swap']['innerContent'] ?? null,
			'property' => 'flex-direction'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_item .faq_question_wrapper",
			'attr'     => $attrs['faq_que_alignment']['innerContent'] ?? null,
			'property' => 'justify-content'
		] );

        // $common_styles [] = CommonStyle::style( [
		// 	'selector' => "{$orderClass} .df_faq_wrapper",
		// 	'attr'     => $this->propMatches('faq_layout_grid', 'on') ? $attrs['faq_grid_settings']['innerContent'] : null,
		// 	'declarationFunction' => function(){
        //         return "display: grid;";
        //     }
		// ] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_wrapper.grid_layout",
            'attr'     => $attrs['faq_grid_settings']['innerContent'] ?? null,
			// 'declarationFunction' => function($props){
            //     $col = $props['attrValue']['faq_item_per_column'];
            //     if( $col ) {
            //         return "grid-template-columns: repeat(" .$col . ", minmax(0, 1fr));";
            //     }
            // }
			'declarationFunction' => function(){
				return "grid-auto-flow: column !important; grid-auto-columns: minmax(0, 1fr);";
			}
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass}.difl_faq .df_faq_wrapper, {$orderClass} .df_faq_wrapper .column",
			'attr'     => $attrs['faq_item_gap']['innerContent'] ?? null,
			'property' => 'gap',
			'important' => true,
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_wrapper",
			'attr'     => $attrs['output_html']['innerContent'] ?? null,
			'declarationFunction' => function(){
                $isTrue = $this->propMatches('enable_schema', 'on') && $this->propMatches('output_html', 'off');
                return $this->generateStyle($isTrue, 'display', 'none !important');
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_faqitem",
			'attr'     => $attrs['faq_item_horizontal_alignment']['innerContent'] ?? null,
			'property' => 'justify-content'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_faqitem .et_pb_module_inner",
			'attr'     => $attrs['faq_item_width']['innerContent'] ?? null,
			'declarationFunction' => function(){
                if( $this->propMatches('faq_item_equal_width', 'on') ) {
                    return 'width: 100%';
                }else{
                    return $this->generateStyle($this->props['faq_item_width'], 'width');
                }

            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['disable_faq_icon']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return $this->generateStyle($this->propMatches('disable_faq_icon', 'on'), 'display', 'none');
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['faq_icon_bg']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .active .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['active_faq_icon_bg']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon .close_icon span.et-pb-icon, {$orderClass} .faq_question_wrapper .faq_icon .open_icon span.et-pb-icon",
			'attr'     => $attrs['faq_close_icon_color']['innerContent'] ?? null,
			'property' => 'color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon .close_icon span.et-pb-icon, {$orderClass} .faq_question_wrapper .faq_icon .open_icon span.et-pb-icon",
			'attr'     => $attrs['faq_close_icon_size']['innerContent'] ?? null,
			'property' => 'font-size'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon .open_icon span.et-pb-icon",
			'attr'     => $attrs['faq_open_icon_color']['innerContent'] ?? null,
			'property' => 'color',
			'important' => true,
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon .open_icon span.et-pb-icon",
			'attr'     => $attrs['faq_open_icon_size']['innerContent'] ?? null,
			'property' => 'font-size'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon .close_icon span.et-pb-icon, {$orderClass} .faq_question_wrapper .faq_icon .open_icon span.et-pb-icon",
			'attr'     => $attrs['close_faq_icon']['innerContent'] ?? null,
			'declarationFunction' => function () {
                return self::get_font_style( $this->props['close_faq_icon'] );
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon .open_icon span.et-pb-icon",
			'attr'     => $attrs['open_faq_icon']['innerContent'] ?? null,
			'declarationFunction' => function () {
                return self::get_font_style( $this->props['open_faq_icon'] );
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_answer .faq_button a",
			'attr'     => $attrs['button_text_color']['innerContent'] ?? null,
			'property' => 'color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_answer .faq_button .faq_button_icon",
			'attr'     => $attrs['button_icon_color']['innerContent'] ?? null,
			'property' => 'color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_answer .faq_button .faq_button_icon",
			'attr'     => $attrs['button_icon_size']['innerContent'] ?? null,
			'property' => 'font-size'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_answer .faq_button",
			'attr'     => $attrs['button_alignment']['innerContent'] ?? null,
			'property' => 'text-align'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_item .faq_question_image",
			'attr'     => $attrs['que_img_bg']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_image .close_image img",
			'attr'     => $attrs['close_que_img_size']['innerContent'] ?? null,
			'property' => 'max-width'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_item.active .faq_question_image",
			'attr'     => $attrs['active_que_img_bg']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_image .open_image img",
			'attr'     => $attrs['open_que_img_size']['innerContent'] ?? null,
			'property' => 'max-width'
		] );

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass}",
			'attr'     => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper",
			'attr'     => $attrs['faq_que_wrapper']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['faq_icon_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .active .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['faq_open_icon_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_faqitem .df_faq_item",
			'attr'     => $attrs['faq_item_design_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_item.active .faq_question_wrapper",
			'attr'     => $attrs['faq_que_active_wrapper']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_answer_area",
			'attr'     => $attrs['faq_ans_wrapper']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_button a",
			'attr'     => $attrs['faq_ans_button']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_question_wrapper .faq_question_image",
			'attr'     => $attrs['faq_que_image_image']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_faq_item.active .faq_question_wrapper .faq_question_image",
			'attr'     => $attrs['faq_que_image_opened_image']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_answer_image",
			'attr'     => $attrs['faq_ans_image']['decoration']['border'] ?? null,
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

    // faq schema
    public function df_render_schema()
    {
        global $df_faq_schema_data;
	    $script = '';

		if('on' === $this->props['enable_schema'] && !empty($df_faq_schema_data) && is_array($df_faq_schema_data)){
			$schema = [
				"@context" => "https://schema.org",
				"@type" => "FAQPage",
				"mainEntity" => [],
			];
			foreach ($df_faq_schema_data as $data) {
				$schema['mainEntity'][] = [
					"@type" => "Question",
					"name" => $data['question'],
					"acceptedAnswer" => [
						"@type" => "Answer",
						"text" => $data['content']
					],
				];
			}
			$script = sprintf('<script type="application/ld+json">%1$s</script>', wp_json_encode($schema));
		}
	    $df_faq_schema_data = [];
        return $script;
    }

	public function render($attrs, $content, $render_slug)
    {
        // $this->handle_fa_icon();
        // Scripts
        wp_enqueue_script('animejs');
        wp_enqueue_script('df_faq');

        // Get all style
        // $this->additional_css_styles($render_slug);

        $active_item = 'on' === $this->props['activate_on_first_time'] ? $this->props['active_item_order_number'] : 0;
        // $order_class = self::get_module_order_class($render_slug);
        $data_settings = [
            'faq_layout'               => $this->props['faq_layout'],
            'activate_on_first_time'   => $this->props['activate_on_first_time'],
            'active_item_order_number' => $active_item,
            'faq_animation'            => $this->props['faq_animation'],
            'faq_anime_duration'       => $this->props['faq_anime_duration'],
            'icon_animation'           => $this->props['icon_animation'],
            'que_img_animation'        => $this->props['que_img_animation'],
            'content_animation_type'   => $this->props['content_animation_type'],
            'content_anime_duration'   => $this->props['content_anime_duration'],
            'order_class'              => $this->order_class,
            'faq_layout_grid'          => isset($this->props['faq_layout_grid']) ? $this->props['faq_layout_grid'] : 'off',
            'faq_item_per_column'      => isset($attrs['faq_grid_settings']['innerContent']['desktop']['value']['faq_item_per_column']) ? $attrs['faq_grid_settings']['innerContent']['desktop']['value']['faq_item_per_column'] : 2,
            'faq_item_per_column_tablet'      => isset($attrs['faq_grid_settings']['innerContent']['tablet']['value']['faq_item_per_column']) ? $attrs['faq_grid_settings']['innerContent']['tablet']['value']['faq_item_per_column'] : $attrs['faq_grid_settings']['innerContent']['desktop']['value']['faq_item_per_column'],
            'faq_item_per_column_phone'      => isset($attrs['faq_grid_settings']['innerContent']['phone']['value']['faq_item_per_column']) ? $attrs['faq_grid_settings']['innerContent']['phone']['value']['faq_item_per_column'] : $attrs['faq_grid_settings']['innerContent']['tablet']['value']['faq_item_per_column'],
            // 'faq_item_per_column_tablet'=> isset($this->props['faq_item_per_column_tablet']) ? $this->props['faq_item_per_column_tablet'] : $this->props['faq_item_per_column'],
            // 'faq_item_per_column_phone'=> isset($this->props['faq_item_per_column_phone']) ? $this->props['faq_item_per_column_phone'] : $this->props['faq_item_per_column']
        ];

        // Display frontend
        $grid_system_class = isset($this->props['faq_layout_grid']) &&  $this->props['faq_layout_grid'] === 'on' ? 'grid_layout': '';
        $output = sprintf(
            '<div class="df_faq_wrapper %4$s" data-settings=\'%3$s\'>%1$s</div>
            %2$s',
            $content ? $content  : "",
	        $this->df_render_schema(),
            wp_json_encode($data_settings),
            $grid_system_class
        );

        return $output;
    }
}
