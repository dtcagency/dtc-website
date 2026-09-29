<?php
namespace DIFL\Server\Modules\FloatImage;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;


class FloatImage implements DependencyInterface {
	public $slug       = 'difl_floatimage';
    public $child_slug = 'difl_floatimageitem';
    public $vb_support = 'on';
	public $icon_path;
    use ModuleUtil;
    use HasProps;

	protected $config_file = 'FloatImage';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	);

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/floatimage' )->customCssFields;
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
			'selector' => "{$orderClass} .df_fi_container",
			'attr'     => $attrs['fi_min_height']['innerContent'] ?? null,
			'property' => 'min-height'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .df_fii_container img",
			'attr'     => $attrs['image']['decoration']['border'] ?? null,
            'declarationFunction' => function($props) {
                return (
                    isset( $props['attrValue']['radius']['bottomLeft'] ) &&
                    isset( $props['attrValue']['radius']['bottomRight'] ) &&
                    isset( $props['attrValue']['radius']['topLeft'] ) &&
                    isset( $props['attrValue']['radius']['topRight'] )
                ) ? 'overflow: hidden !important;' : '';
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

	public function render($attrs, $content, $render_slug)
    {
        wp_enqueue_script('animejs');
        wp_enqueue_script('df-floatimage-script');

        return sprintf('<div class="df_fi_container">%1$s</div>',$content);
    }
}
