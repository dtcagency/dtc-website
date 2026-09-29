<?php
namespace DIFL\Server\Modules\AnimatedHeading;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class AnimatedHeading implements DependencyInterface {
	public $slug       = 'dfadh_heading_anim';
    public $vb_support = 'on';
	public $icon_path;
    use ModuleUtil;
    use HasProps;

	protected $config_file = 'AnimatedHeading';

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
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/heading-anim' )->customCssFields;
	}

    public function wrapChar ($word, $key) {
        $anim_type = strpos($this->props['fancy_text_anim'], 'letter');
        $newWord = '';
        if ( $anim_type == true ) {
            $len = mb_strlen($word, 'UTF-8');
            if ( $len > 0 ) {
                for ($i = 0; $i < $len; $i++) {
                    $ch = mb_substr($word, $i, 1, 'UTF-8');
                    if ( $ch !== ' ') {
                        if ( $key === 0 ) {
                            $newWord .= sprintf('<span class="in">%1$s</span>', $ch);
                        } else {
                            $newWord .= sprintf('<span class="out">%1$s</span>', $ch);
                        }
                    } else {
                        $newWord .= ' ';
                    }
                }
            }
        } else {
            $newWord = $word;
        }
        return $newWord;
    }

    public function fancy_text() {
        // $option_search  = array( '&#91;', '&#93;' );
		// $option_replace = array( '[', ']' );
        // $f_text = $this->props['fency_text_list'];
        // $lists = str_replace( $option_search, $option_replace, $f_text );

        $lists = $this->props['fency_text_list'];
        $fancy_text = '<span class="infix words-wrapper">';
        foreach ($lists as $key => $list) {
            $word = $this->wrapChar($list['value'], $key);
            if ( $key === 0 ) {
                $fancy_text .= '<span class="is-visible">' . $word . '</span>';
            } else {
                $fancy_text .= '<span class="is-hidden">' . $word . '</span>';
            }
        }
        $fancy_text .= '</span>';
        return $fancy_text;
    }

    public function fancy_text_class() {
        $animation_type = array(
            'type-word-rotate'          => 'word type-word-rotate',
            'type-word-slide-top'       => 'word type-word-slide-top',
            'type-word-slide-left'      => 'word type-word-slide-left',
            'type-word-scale'           => 'word type-word-scale',
            'type-letter-flip'          => 'letter type-letter-flip',
            'type-letter-scale'         => 'letter type-letter-scale',
            'type-letter-wave'          => 'letter type-letter-wave',
            'type-letter-stand'         => 'letter type-letter-stand',
            'type-letter-slide'         => 'letter type-letter-slide'
        );
        return sprintf('headline-animation %1$s', $animation_type[$this->props['fancy_text_anim']]);
    }

    public function get_alignment_style($alignment) {
        if( $alignment === 'left' ) {
            return 'margin-left: 0; margin-right: auto;';
        } else if( $alignment === 'center' ) {
            return 'margin-left: auto; margin-right: auto;';
        } else if( $alignment === 'right' ) {
            return 'margin-left: auto; margin-right: 0;';
        } else {
            return '';
        }
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
		$attrs      = $args['attrs'] ?? [];
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .headline-animation .prefix",
			'attr'     => $attrs['title_prefix_block']['innerContent'] ?? null,
			'property' => 'display',
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .headline-animation .infix",
			'attr'     => $attrs['title_infix_block']['innerContent'] ?? null,
			'property' => 'display',
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .headline-animation .suffix",
			'attr'     => $attrs['title_suffix_block']['innerContent'] ?? null,
			'property' => 'display',
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .headline-animation .prefix",
			'attr'     => $attrs['prefix_maxwidth']['innerContent'] ?? null,
			'property' => 'max-width',
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .headline-animation .infix",
			'attr'     => $attrs['infix_maxwidth']['innerContent'] ?? null,
			'property' => 'max-width',
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .headline-animation .suffix",
			'attr'     => $attrs['suffix_maxwidth']['innerContent'] ?? null,
			'property' => 'max-width',
		] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .headline-animation .prefix",
            'attr'                => $attrs['prefix_alignment']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                return $this->get_alignment_style($props["attrValue"]);
            },
        ] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .headline-animation .infix",
            'attr'                => $attrs['infix_alignment']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                return $this->get_alignment_style($props["attrValue"]);
            },
        ] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => "{$orderClass} .headline-animation .suffix",
            'attr'                => $attrs['suffix_alignment']['innerContent'] ?? [],
            'declarationFunction' => function ($props) {
                return $this->get_alignment_style($props["attrValue"]);
            },
        ] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .headline-animation .infix",
			'attr'     => $attrs['fancy_text_overflow']['innerContent'] ?? null,
			'property' => 'overflow',
		] );

        $common_styles[] = CommonStyle::style( [
            'selector'            => $orderClass,
            'attr'                => $attrs['module']['decoration']['border'] ?? [],
            'declarationFunction' => function ( $props ) {
                return $this->addOverflowHidden($props);
            }
        ] );

        $common_styles [] = CommonStyle::style( [
            'selector' => "{$orderClass} span.prefix",
            'attr'     => $attrs['prefix_border']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
        ] );

        $common_styles [] = CommonStyle::style( [
            'selector' => "{$orderClass} span.infix",
            'attr'     => $attrs['infix_border']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
        ] );

        $common_styles [] = CommonStyle::style( [
            'selector' => "{$orderClass} span.suffix",
            'attr'     => $attrs['suffix_border']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
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

	public function render( $attrs, $content, $render_slug ) {
        wp_enqueue_script('animejs');
        wp_enqueue_script('headline-scripts');



        $animation_type = array(
            'type-word-rotate'          => 'word',
            'type-word-slide-top'       => 'word',
            'type-word-slide-left'      => 'word',
            'type-word-scale'           => 'word',
            'type-letter-flip'          => 'letter',
            'type-letter-scale'         => 'letter',
            'type-letter-wave'          => 'letter',
            'type-letter-stand'         => 'letter',
            'type-letter-slide'         => 'letter'
        );

        // $title_level  = $this->props['heading_level'];
        $title_level  = $attrs['title']['decoration']['font']['font']['desktop']['value']['headingLevel'] ?? null;
        $title_prefix = !empty($this->props['title_prefix']) ?
            sprintf('<span class="prefix">%1$s</span>',$this->props['title_prefix']) : '';

            $title_suffix = !empty($this->props['title_suffix']) ?
            sprintf('<span class="suffix">%1$s</span>',$this->props['title_suffix']) : '';

            $title_infix = $this->fancy_text();

        $title = sprintf('%1$s %2$s %3$s', $title_prefix, $title_infix, $title_suffix);

        // $this->additional_css_styles($render_slug);

        $data_attr = array(
            'transition_type'           => $animation_type[$this->props['fancy_text_anim']],
            'animation_type'            => $this->props['fancy_text_anim'],
            'delay'                     => intval($this->props['anim_delay']),
            'duration'                  => intval($this->props['anim_duration']),
            'easing'                    => $this->props['easing'] === 'on' ? $this->props['anim_easing'] : 'none',
            'mass'                      => intval($this->props['spring_anim_mass']),
            'stiffness'                 => intval($this->props['spring_anim_stiffness']),
            'damping'                   => intval($this->props['spring_anim_damping']),
            'velocity'                  => intval($this->props['spring_anim_velocity'])
        );

        return sprintf( '<div class="df-anim-heading-container">
                <%2$s class="%3$s" data-anmi=\'%4$s\' >%1$s</%2$s>
            </div>',
            $title,
            et_pb_process_header_level( $title_level, 'h4' ),
            $this->fancy_text_class(),
            wp_json_encode($data_attr)
        );
	}
}


// Refactor placeholder content & handle on both side only for text/richtext
// For image handle differently
// On frontend resolve props value as like as Builder does

// Handle  i.e margin_padding -- done
// Script enqueue & registration isolation
//Global handling for innercontent style
