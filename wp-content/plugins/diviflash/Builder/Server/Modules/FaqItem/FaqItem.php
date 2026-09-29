<?php
namespace DIFL\Server\Modules\FaqItem;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class FaqItem implements DependencyInterface {
    // public $parent_faq;
	public $icon_path;
    use ModuleUtil;
    use HasProps;

	protected $config_file = 'FaqItem';

    protected $module_classnames = 'et_pb_module';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	);

	public function addOverflowHidden($props){
        return (
            isset( $props['attrValue']['radius']['bottomLeft'] ) &&
            isset( $props['attrValue']['radius']['bottomRight'] ) &&
            isset( $props['attrValue']['radius']['topLeft'] ) &&
            isset( $props['attrValue']['radius']['topRight'] )
        ) ? 'overflow: hidden !important;' : '';
    }

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/faqitem' )->customCssFields;
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

    public function get_common_styles( $args ) {
		$attrs      = $args['attrs'] ?? [];
        $attrs      = $this->get_merged_attrs( $args );
        $attrs      = self::build_attr_for_styles( $attrs );
        $attrs      = $this->get_default_value_for_styles($attrs);
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

		$common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_question_area",
			'attr'     => $attrs['question_image_placement']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return $this->generateStyle($this->propMatches('question_image_placement', 'row-reverse'), 'flex-direction', 'row-reverse');
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_content",
			'attr'     => $attrs['answer_image_placement']['innerContent'] ?? null,
			'property' => 'flex-flow'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_answer_image",
			'attr'     => $this->propMatches('enable_answer_image', 'on') ? $attrs['answer_image_width']['innerContent'] : null,
            'declarationFunction' => function(){
                if (in_array($this->props['answer_image_placement'], ['row', 'row-reverse'], true)) {
                    return $this->generateStyle($this->props['answer_image_width'], 'width');
                }
                return '';

            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_content",
			'attr'     => $attrs['answer_image_full_width_mobile']['innerContent'] ?? null,
            'atRules'  => '@media only screen and (max-width: 767px)',
			'declarationFunction' => function(){
                return $this->generateStyle($this->propMatches('answer_image_full_width_mobile', 'on'), 'flex-flow', 'column-reverse !important');
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_answer_image",
			'attr'     => $attrs['answer_image_full_width_mobile']['innerContent'] ?? null,
            'atRules'  => '@media only screen and (max-width: 767px)',
			'declarationFunction' => function(){
                return $this->generateStyle($this->propMatches('answer_image_full_width_mobile', 'on'), 'width', '100% !important');
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['faq_icon_bg']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .active .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['active_faq_icon_bg']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} div.faq_question_wrapper div.faq_icon div.close_icon span.et-pb-icon, {$orderClass} div.faq_question_wrapper div.faq_icon div.open_icon span.et-pb-icon",
			'attr'     => $attrs['faq_close_icon_color']['innerContent'] ?? null,
			'property' => 'color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} div.faq_question_wrapper div.faq_icon div.open_icon span.et-pb-icon",
			'attr'     => $attrs['faq_open_icon_color']['innerContent'] ?? null,
			'property' => 'color',
            'important' => true
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_content",
			'attr'     => $this->propMatches('enable_answer_image', 'on') ? $attrs['answer_image_alignment']['innerContent'] : null,
			'declarationFunction' => function(){
                $map = [
                    'left' => 'start',
                    'center' => 'center',
                    'right' => 'end'
                ];
                $alignment_key = $this->props['answer_image_alignment'] ?? 'left';
                $alignment_value = $map[$alignment_key] ?? 'start';

                return $this->generateStyle($this->props['answer_image_alignment'], 'align-items', $alignment_value);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .df_faq_item .faq_question_image",
			'attr'     => $attrs['que_img_bg']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .df_faq_item.active .faq_question_image",
			'attr'     => $attrs['active_que_img_bg']['innerContent'] ?? null,
			'property' => 'background-color'
		] );


        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_answer_image, {$orderClass} .faq_answer_image img",
			'attr'     => $this->propMatches('enable_answer_image', 'on') ? $attrs['answer_image_width_top_bottom']['innerContent'] : null,
            'declarationFunction' => function($props){
                if (in_array($this->props['answer_image_placement'], ['row', 'row-reverse'], true)) {
                    return '';
                }
                return $this->generateStyle($props['attrValue'], 'width');
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_button a",
			'attr'     => $attrs['button_text_color']['innerContent'] ?? null,
			'property' => 'color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_button a",
			'attr'     => $attrs['button_full_width']['innerContent'] ?? null,
			'declarationFunction' => function(){
                return $this->generateStyle($this->propMatches('button_full_width', 'on'), 'display', 'block');
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_button",
			'attr'     => $attrs['button_alignment']['innerContent'] ?? null,
			'property' => 'text-align'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_button a .faq_button_icon",
			'attr'     => $attrs['button_icon_color']['innerContent'] ?? null,
			'property' => 'color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_button a .faq_button_icon",
			'attr'     => $attrs['button_icon_size']['innerContent'] ?? null,
			'property' => 'font-size'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_button a .faq_button_icon",
			'attr'     => $attrs['button_icon_space']['innerContent'] ?? null,
			'declarationFunction' => function(){
                if( $this->propMatches('button_icon_placement', 'right') ) {
                    return $this->generateStyle($this->props['button_icon_space'], 'margin-left');
                }else{
                    return $this->generateStyle($this->props['button_icon_space'], 'margin-right');
                }
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .faq_answer .faq_button .faq_button_icon",
			'attr'     => $this->propMatches('use_button_icon', 'on') ? $attrs['answer']['innerContent'] : null,
			'declarationFunction' => function () {
                return self::get_font_style( $this->props['button_font_icon'] );
            }
		] );

        // show icon on hover
        if ('off' !== $this->props['show_icon_hover']) {
            $icon_size = "" !== $this->props['button_icon_size'] ? $this->props['button_icon_size'] : "18px";
            $icon_space = "" !== $this->props['button_icon_space'] ? $this->props['button_icon_space'] : "0px";
            $icon_wrapper_width = intval(substr($icon_size, 0, -2)) + intval(substr($icon_space, 0, -2));

            if ('left' !== $this->props['button_icon_placement']) {
                $common_styles [] = CommonStyle::style( [
                    'selector' => "{$orderClass} .faq_answer_wrapper .faq_button.icon_show_hover .faq_button_icon",
                    'attr'     => $this->propMatches('use_button_icon', 'on') ? $attrs['answer']['innerContent'] : null,
                    'declarationFunction' => function () use ($icon_wrapper_width) {
                        return "margin: 0px; margin-right: -" . $icon_wrapper_width . "px !important; opacity: 0 !important;";
                    }
                ] );

                $common_styles [] = CommonStyle::style( [
                    'selector' => "{$orderClass} .faq_answer_wrapper .faq_button.icon_show_hover:hover .faq_button_icon",
                    'attr'     => $this->propMatches('use_button_icon', 'on') ? $attrs['answer']['innerContent'] : null,
                    'declarationFunction' => function () {
                        return "margin: 0px; margin-right: 0px !important; opacity: 1 !important;";
                    }
                ] );
            } else {
                $common_styles [] = CommonStyle::style( [
                    'selector' => "{$orderClass} .faq_answer_wrapper .faq_button.icon_show_hover .faq_button_icon",
                    'attr'     => $this->propMatches('use_button_icon', 'on') ? $attrs['answer']['innerContent'] : null,
                    'declarationFunction' => function () use ($icon_wrapper_width) {
                        return "margin-left: -" . $icon_wrapper_width . "px !important; opacity: 0 !important;";
                    }
                ] );

                $common_styles [] = CommonStyle::style( [
                    'selector' => "{$orderClass} .faq_answer_wrapper .faq_button.icon_show_hover:hover .faq_button_icon",
                    'attr'     => $this->propMatches('use_button_icon', 'on') ? $attrs['answer']['innerContent'] : null,
                    'declarationFunction' => function () {
                        return "margin: 0px; margin-left: 0px !important; opacity: 1 !important;";
                    }
                ] );
            }
        }

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass}",
			'attr'     => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .df_faq_item .faq_question_wrapper",
			'attr'     => $attrs['question']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .df_faq_item.active div.faq_question_wrapper",
			'attr'     => $attrs['question_open']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_answer_area",
			'attr'     => $attrs['answer']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_answer_image",
			'attr'     => $attrs['faq_ans_image']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_button a",
			'attr'     => $attrs['faq_ans_button']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['faq_icon_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .active .faq_question_wrapper .faq_icon",
			'attr'     => $attrs['faq_open_icon_settings']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .faq_question_wrapper .faq_question_image",
			'attr'     => $attrs['faq_que_image_image']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_faq {$orderClass} .df_faq_item.active .faq_question_wrapper .faq_question_image",
			'attr'     => $attrs['faq_que_image_opened_image']['decoration']['border'] ?? null,
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

    public function df_render_question()
    {
        $close_q_img = !empty($this->props['close_question_image']) ? $this->props['close_question_image'] : "";
        $close_q_img_alt = !empty($this->props['close_que_img_alt_txt']) ? $this->props['close_que_img_alt_txt'] : "";
        $open_q_img = !empty($this->props['open_question_image']) ? $this->props['open_question_image'] : $close_q_img;
        $open_q_img_alt = !empty($this->props['open_que_img_alt_txt']) ? $this->props['open_que_img_alt_txt'] : $close_q_img_alt;
        $que_img_html = 'on' === $this->props['enable_question_image'] && (!empty($this->props['close_question_image']) || !empty($this->props['open_question_image'])) ?
            sprintf(
                '<div class="faq_question_image">
                <div class="close_image">%1$s</div>
                <div class="open_image">%2$s</div>
                </div>',
                $this->df_render_faq_image($close_q_img, $close_q_img_alt),
                $this->df_render_faq_image($open_q_img, $open_q_img_alt)
            ) : '';

        $que_title_tag = esc_attr($this->props['question_title_tag']);

        $que_html = "" !== $this->props['question'] ?
            sprintf(
                '<div class="faq_question"><%1$s class="faq_question_title">%2$s</%1$s></div>',
                '' !== $que_title_tag ? $que_title_tag : "h3",
                $this->props['question'] ? $this->props['question'] : ""
            ) : '';

        $icon_html =  sprintf(
            '<div class="faq_icon">%1$s</div>',
            $this->df_render_faq_toggle_icon()
        );

        $output = sprintf(
            '<div class="faq_question_wrapper">
                <div class="faq_question_area">
                    %1$s
                    %2$s
                </div>
                %3$s
            </div>
          ',
            $que_img_html,
            $que_html,
            $icon_html
        );

        return $output;
    }

    public function df_render_answer()
    {
        $ans_btn = $this->props['enable_answer_button'] ? $this->df_render_button() : "";
        $ans_html = $this->props['content'] !== '' ? sprintf('<div class="faq_answer"><div>%1$s</div> %2$s</div>',  $this->props['content'], $ans_btn) : '';
        $ans_img = !empty($this->props['answer_image']) ? $this->props['answer_image'] : '';
        $ans_img_html  = 'on' === $this->props['enable_answer_image'] ?
            sprintf(
                '<div class="faq_answer_image">%1$s</div>',
                $this->df_render_faq_image($ans_img, $this->props['answer_image_alt_text'])
            ) : "";

        $output = sprintf(
            '<div class="faq_answer_wrapper">
                <div class="faq_answer_area">
                   <div class="faq_content">
                        %1$s
                        %2$s
                   </div>
                </div>
            </div>',
            $ans_html,
            $ans_img_html
        );

        return $output;
    }

    public function df_render_faq_image($img_prop, $img_alt)
    {
        if (isset($img_prop) && '' !== $img_prop) {
            $image_alt = '' !== $img_alt ? $img_alt  : df_image_alt_by_url($img_prop);
            return sprintf(
                '<img src="%1$s" alt="%2$s" />',
                $img_prop,
                $image_alt
            );
        }
    }

    public function df_render_faq_toggle_icon()
    {
        $default_close_icon = [
            'unicode' => '&#x4c;',
            'type'    => 'divi',
            'weight'  => '400'
        ];

        $default_open_icon = [
            'unicode' => '&#x4b;',
            'type'    => 'divi',
            'weight'  => '400'
        ];

        $close_icon = !empty($this->parent_props['close_faq_icon']) ? $this->parent_props['close_faq_icon'] : $default_close_icon;
        $open_icon = !empty($this->parent_props['open_faq_icon']) ? $this->parent_props['open_faq_icon'] : $default_open_icon;

        $close_faq_icon = esc_attr( Utils::process_font_icon( $close_icon ) );
        $open_faq_icon = esc_attr( Utils::process_font_icon( $open_icon ) );

        if (!empty($close_faq_icon)) {
            return sprintf(
                '<div class="close_icon"><span class="et-pb-icon">%1$s</span></div>
                <div class="open_icon"><span class="et-pb-icon">%2$s</span></div>',
                $close_faq_icon,
                $open_faq_icon
            );
        }
    }

    public function df_render_button()
    {
        $text = isset($this->props['button_text']) ? $this->props['button_text'] : '';
        $url = isset($this->props['button_url']) ? $this->props['button_url'] : '';
        $target = $this->props['button_url_new_window'] === 'on' ? 'target="_blank"' : '';

        $has_button_font_icon = isset($this->props['button_font_icon']) && $this->props['button_font_icon'] !== '';
        $button_icon_pos = $this->props['button_icon_placement'];
        $icon_show_hover = "on" === $this->props['show_icon_hover'] ? "icon_show_hover" : "";

        // Button icon
        $button_icon = $this->props['use_button_icon'] !== 'off' ? sprintf(
            '<span class="et-pb-icon faq_button_icon">%1$s</span>',
            $has_button_font_icon ? esc_attr(Utils::process_font_icon($this->props['button_font_icon'])) : '5'
        ) : '';
        if ('on' === $this->props['enable_answer_button']) {
            return sprintf(
                '<div class="faq_button %6$s %7$s">
                    <a href="%1$s" %3$s data-icon="5">%5$s <span>%2$s</span> %4$s</a>
                </div>',
                esc_attr($url),
                esc_html(trim($text)),
                $target,
                $button_icon_pos === 'right' ? $button_icon : '',
                $button_icon_pos === 'left' ? $button_icon : '',
                $icon_show_hover,
                "left" !== $button_icon_pos ? "right" : "left"
            );
        } else {
            return '';
        }
    }

    // set checkbox value to devices
    public function df_multicheck_value($checkbox_values)
    {
        if (!is_array($checkbox_values) || empty($checkbox_values)) {
            return '';
        }

        return array_reduce($checkbox_values, function ($result, $value) {
            $result[$value] = 'on';
            return $result;
        }, []);
    }

    // text to flex conversion
    public function df_set_flex_position($options)
    {
        $defaults = [
            'render_slug' => '',
            'slug'        => '',
            'selector'    => '',
            'type'        => '',
            'css'         => '',
        ];

        $options = wp_parse_args($options, $defaults);
        $get_values = ["center", "left", "right"];
        $set_values = ["center", "start", "end"];
        $values = [];

        foreach ($get_values as $key => $value) {
            $values[$value] = $set_values[$key];
        }

        if (array_key_exists($options['slug'], $this->props) && !empty($this->props[$options['slug']])) {
            $desktop = $this->props[$options['slug']];
            self::set_style($options['render_slug'], array(
                'selector'    => $options['selector'],
                'declaration' => sprintf('display: flex;%1$s:%2$s;%3$s;', $options['type'], $values[$desktop], $options['css'])
            ));
        }

        if (array_key_exists($options['slug'], $this->props) && !empty($this->props[$options['slug'] . "_tablet"])) {
            $tablet = $this->props[$options['slug'] . "_tablet"];
            self::set_style($options['render_slug'], array(
                'selector'    => $options['selector'],
                'declaration' => sprintf('display: flex;%1$s:%2$s;%3$s;', $options['type'], $values[$tablet], $options['css']),
                'media_query' => self::get_media_query('max_width_980')
            ));
        }

        if (array_key_exists($options['slug'], $this->props) && !empty($this->props[$options['slug'] . "_phone"])) {
            $phone = $this->props[$options['slug'] . "_phone"];
            self::set_style($options['render_slug'], array(
                'selector'    => $options['selector'],
                'declaration' => sprintf('display: flex;%1$s:%2$s;%3$s;', $options['type'], $values[$phone], $options['css']),
                'media_query' => self::get_media_query('max_width_767')
            ));
        }
    }

	public function render($attrs, $content, $render_slug)
    {
        // Scripts
        wp_enqueue_script('animejs');
        wp_enqueue_script('df_faq');

        // Schema Data
        global $df_faq_schema_data;
        $df_faq_class = $this->order_class;
        $df_faq_schema_data[$df_faq_class]['question'] = wp_strip_all_tags($this->props['question']);
        $df_faq_schema_data[$df_faq_class]['content'] = $this->props['content'];

        // JS data
        $data_settings = [
            'hide_faq_item' => $this->df_multicheck_value($this->props['hide_faq_item']),
        ];

        $output = sprintf(
            '<div class="et_pb_module_inner"><div class="df_faq_item" data-settings=\'%3$s\'>

                    %1$s
                    %2$s
                </div>
            </div>',
            $this->df_render_question(),
            $this->df_render_answer(),
            // $this->df_render_answer($render_slug),
            wp_json_encode($data_settings)
        );

        return $output;
    }
}
