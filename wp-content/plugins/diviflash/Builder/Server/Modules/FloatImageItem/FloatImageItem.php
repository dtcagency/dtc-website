<?php
namespace DIFL\Server\Modules\FloatImageItem;

use \DIFL\Server\Traits\ModuleUtil;
use \DIFL\Server\Traits\HasProps;
use \DIFL\Server\Traits\StyleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\IconLibrary\IconFont\Utils;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class FloatImageItem implements DependencyInterface {
    public $slug       = 'difl_floatimageitem';
    public $vb_support = 'on';
	public $icon_path;
    use ModuleUtil;
    use HasProps;

	protected $config_file = 'FloatImageItem';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => 'DiviFlash',
		'author_uri' => '',
	);

    public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/floatimageitem' )->customCssFields;
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

        $common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_floatimage {$orderClass} .df_fii_container img",
			'attr'     => $attrs['fii_max_width']['innerContent'] ?? null,
			'property' => 'max-width'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_floatimage {$orderClass} .df_fii_container img",
			'attr'     => $attrs['fii_max_height']['innerContent'] ?? null,
			'property' => 'max-height'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_floatimage {$orderClass}",
			'attr'     => $attrs['horizontal_position']['innerContent'] ?? null,
			'property' => 'left'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_floatimage {$orderClass}",
			'attr'     => $attrs['vertical_position']['innerContent'] ?? null,
			'property' => 'top'
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_floatimage {$orderClass}",
			'attr'     => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            }
		] );

        $common_styles [] = CommonStyle::style( [
			'selector' => ".difl_floatimage {$orderClass} .df_fii_container img",
			'attr'     => $attrs['image']['decoration']['border'] ?? null,
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

    public function render($attr, $content, $render_slug ) {

        $image_alt = $this->props['alt_text'] !== '' ? $this->props['alt_text']  : df_image_alt_by_url($this->props['image']);
        $image = $this->props['image'] !== '' ?
                sprintf('<img src="%1$s" alt="%2$s" />',
                esc_attr($this->props['image']),
                esc_attr($image_alt)
            ): '';

        $data = array (
            'animation_type'            => $this->props['animation_type'],
            'duration'                  => $this->props['duration'],
            'delay'                     => $this->props['delay'],
            'animation_function'        => $this->props['animation_function'],
            'vertical_anime_distance'   => $this->props['vertical_anime_distance'],
            'horizontal_anime_distance' => $this->props['horizontal_anime_distance']
        );

        return sprintf('
            <div class="df_fii_container" data-animation=\'%2$s\'>
                %1$s
            </div>',
            $image,
            wp_json_encode($data)
        );
    }
}
