<?php
namespace DIFL\Server\Modules\InlineContents;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class InlineContents implements DependencyInterface {

    public $slug       = 'difl_inline_contents';
    public $child_slug = 'difl_inline_contents_item';
    public $vb_support = 'on';
	public $icon_path;
    use ModuleUtil;
    use HasProps;

	protected $config_file = 'InlineContents';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	);

    public static function custom_css() {
        return \WP_Block_Type_Registry::get_instance()->get_registered('difl/inline-contents')->customCssFields;
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
			'selector' => "{$orderClass} .difl_inline_contents_container",
			'attr'     => $attrs['column_gap']['innerContent'] ?? null,
			'property' => 'column-gap'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container",
			'attr'     => $attrs['row_gap']['innerContent'] ?? null,
			'property' => 'row-gap'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container",
			'attr'     => $attrs['content_alignment']['innerContent'] ?? null,
			'property' => 'justify-content'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container",
			'attr'     => $attrs['items_position']['innerContent'] ?? null,
			'property' => 'align-items'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item:has( .difl_inline_content_text )",
			'attr'     => $attrs['text_bg_color']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item .difl_inline_content_icon",
			'attr'     => $attrs['icon_color']['innerContent'] ?? null,
			'property' => 'color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item:has( .difl_inline_content_icon )",
			'attr'     => $attrs['icon_bg_color']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item .difl_inline_content_icon",
			'attr'     => $attrs['icon_size']['innerContent'] ?? null,
			'property' => 'font-size'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item .difl_inline_content_image",
			'attr'     => $attrs['media_size']['innerContent'] ?? null,
			'property' => 'width'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item:has( .difl_inline_content_image )",
			'attr'     => $attrs['media_bg_color']['innerContent'] ?? null,
			'property' => 'background-color'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item:has( div > .difl_inline_content_text )",
			'attr'     => $attrs['content_text']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item:has( div > .difl_inline_content_icon )",
			'attr'     => $attrs['content_icon']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .difl_inline_contents_container .difl_inline_contents_item:has( div > .difl_inline_content_image )",
			'attr'     => $attrs['content_image']['decoration']['border'] ?? null,
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

    public function render($attrs, $content, $block, $render_slug) {

        $main_wrapper_tag = ! empty( $this->props['main_wrapper_tag'] ) ? $this->props['main_wrapper_tag'] : 'div';
        
        // Whitelist of allowed HTML tags for security
        $allowed_tags = ['div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span'];
        
        // Validate and sanitize the tag name
        $main_wrapper_tag = strtolower( trim( $main_wrapper_tag ) );
        if ( ! in_array( $main_wrapper_tag, $allowed_tags, true ) ) {
            $main_wrapper_tag = 'div';
        }

        return sprintf(
            '<div class="et_pb_module_inner"><%1$s class="difl_inline_contents_container" id="difl-inline-contents-container">%2$s</%1$s></div>',
            esc_html( $main_wrapper_tag ),
            $content ? $content : ''
        );
    }

}
