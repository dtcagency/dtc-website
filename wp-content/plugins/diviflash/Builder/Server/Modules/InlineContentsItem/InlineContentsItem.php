<?php
namespace DIFL\Server\Modules\InlineContentsItem;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class InlineContentsItem implements DependencyInterface {

    public $slug       = 'difl_inline_contents_item';
    public $vb_support = 'on';
	public $icon_path;
    use ModuleUtil;
    use HasProps;

	protected $config_file = 'InlineContentsItem';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	);

    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/inline-contents-item')->customCssFields;
    }

    function propMatches( $key, $value ) {
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
			'selector' => ".difl_inline_contents {$orderClass}.difl_inline_contents_item .difl_inline_content_icon",
			'attr'     => $attrs['icon_color']['innerContent'] ?? null,
			'property' => 'color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_inline_contents {$orderClass}.difl_inline_contents_item .difl_inline_content_icon",
			'attr'     => $attrs['icon_size']['innerContent'] ?? null,
			// 'property' => 'font-size'
            'declarationFunction' => function($props) {
                return 'font-size: ' . $props['attrValue'] . ';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_inline_contents {$orderClass}.difl_inline_contents_item .difl_inline_content_image",
			'attr'     => $attrs['media_size']['innerContent'] ?? null,
			// 'property' => 'width'
            'declarationFunction' => function($props) {
                return 'width: ' . $props['attrValue'] . ';';
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_content_icon",
			'attr'     => $this->propMatches('content_type', 'Icon') ? $attrs['content_icon']['innerContent'] : null,
			'declarationFunction' => function () {
                return self::get_font_style( $this->props['content_icon'] );
            }
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

    public function process_icon( $props, $module_class ) {
        if ( 'Icon' !== $props['content_type'] ) {
            return '';
        }

        $font_icon = ! empty( $props['content_icon'] ) ? $props['content_icon'] : '&#xe08a;||divi||400';

        return sprintf(
            '<span class="%2$s et-pb-icon difl_inline_content_icon">%1$s</span>',
            esc_attr( utils::process_font_icon( $font_icon ) ),
            esc_attr( $module_class )
        );
    }

    public function process_image( $props, $module_class ) {
        if ( 'Image' !== $props['content_type'] ) {
            return '';
        }
        if ( empty( $props['content_image'] ) ) {
            return '';
        }

        return sprintf(
            '<img src="%1$s" alt="" class="difl_inline_content_image"/>',
            esc_url( $props['content_image'] )
        );
    }

    public function process_text( $props, $module_class ) {
        if ( in_array( $props['content_type'], [ 'Icon', 'Image', 'Line_Break' ], true ) ) {
            return '';
        }

        return sprintf(
            '<span class="%2$s difl_inline_content_text">%1$s</span>',
            esc_html( $props['content_text'] ),
            esc_attr( $module_class )
        );
    }

    public function process_line_break( $props ) {
        if ( 'Line_Break' !== $props['content_type'] ) {
            return '';
        }

        return '<span class="df_break_line"></span>';
    }

    public function render( $attrs, $content, $block, $render_slug ) {
        $module_class = ! empty( $this->props['content_new_line'] ) ? 'line_break' : '';

        return sprintf(
            '%1$s %2$s %3$s %4$s',
            $this->process_text( $this->props, $module_class ),
            $this->process_icon( $this->props, $module_class ),
            $this->process_image( $this->props, $module_class ),
            $this->process_line_break( $this->props )
        );
    }

}
