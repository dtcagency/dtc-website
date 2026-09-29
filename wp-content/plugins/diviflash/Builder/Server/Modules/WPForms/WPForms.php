<?php

namespace DIFL\Server\Modules\WPForms;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class WPForms implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'WPForms';

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/wpforms' )->customCssFields;
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

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpforms-container .wpforms-form input[type=radio]:checked:after",
			'attr'      => $attrs['checkbox_radio_color']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpforms-container .wpforms-form input[type=radio]:checked:before, 
                  {$orderClass} .wpforms-container .wpforms-form input[type=radio]:checked:after, 
                  {$orderClass} .wpforms-container .wpforms-form input[type=radio]:focus:before, 
                  {$orderClass} .wpforms-container .wpforms-form input[type=checkbox]:checked:before, 
                  {$orderClass} .wpforms-container .wpforms-form input[type=checkbox]:checked:after, 
                  {$orderClass} .wpforms-container .wpforms-form input[type=checkbox]:focus:before",
			'attr'      => $attrs['checkbox_radio_color']['innerContent'] ?? null,
			'property'  => 'border-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpforms-form .choices__inner",
			'attr'      => $attrs['dorpdown_height']['innerContent'] ?? null,
			'property'  => 'min-height',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpforms-form .wpforms-field-select:not(.wpforms-field-select-style-classic) select",
			'attr'      => $attrs['dorpdown_height']['innerContent'] ?? null,
			'property'  => 'height',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpforms-submit-container",
			'attr'      => $attrs['submit']['innerContent'] ?? null,
			'property'  => 'text-align',
			'important' => true,
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .wpforms-container .wpforms-form [type='submit']",
			'attr'     => $attrs['submit']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .wpforms-container .wpforms-form input[type='text'],{$orderClass} .wpforms-container .wpforms-form input[type='email'],{$orderClass} .wpforms-container .wpforms-form input[type='number'],{$orderClass} .wpforms-container .wpforms-form input[type='tel'],{$orderClass} .wpforms-container .wpforms-form input[type='password'],{$orderClass} .wpforms-container .wpforms-form textarea,{$orderClass} .wpforms-container .wpforms-form textarea:focus",
			'attr'     => $attrs['input']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .wpforms-form .wpforms-field-container select, {$orderClass} .wpforms-form .wpforms-field-container select:focus, {$orderClass} .wpforms-form .choices__inner",
			'attr'     => $attrs['dropdown']['decoration']['border'] ?? null,
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

	public function render_wpforms() {
		if ( $this->props['wpforms'] === 'default' ) {
			$wpforms = 'Please select an contact form!';
		} else {
			$wpforms = do_shortcode( '[wpforms id="' . $this->props['wpforms'] . '" ]' );
			ob_start();
			echo do_shortcode( '[wpforms id="' . $this->props['wpforms'] . '" ]' );
			$wpforms = ob_get_clean();
		}

		return $wpforms;
	}

	public function render( $attrs, $content, $render_slug ) {
		return sprintf( '<div class="et_pb_module_inner"><div class="df-wpf-container">%1$s</div></div>', $this->render_wpforms() );
	}

}
