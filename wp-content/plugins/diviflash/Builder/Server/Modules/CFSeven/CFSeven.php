<?php

namespace DIFL\Server\Modules\CFSeven;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class CFSeven implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'CFSeven';

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/cfseven' )->customCssFields;
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
			'selector'  => "{$orderClass} .wpcf7-form-control.wpcf7-submit",
			'attr'      => $attrs['input_submit_width']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} p:has(.wpcf7-form-control.wpcf7-submit)",
			'attr'      => $attrs['submit_button_align']['innerContent'] ?? null,
			'property'  => 'text-align',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpcf7-form-control.wpcf7-text",
			'attr'      => $attrs['input_text_width']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpcf7-form-control.wpcf7-email",
			'attr'      => $attrs['input_email_width']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpcf7-form-control.wpcf7-submit",
			'attr'      => $attrs['input_submit_width']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpcf7-form-control.wpcf7-textarea",
			'attr'      => $attrs['input_textarea_width']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .wpcf7-form-control.wpcf7-select",
			'attr'      => $attrs['input_select_width']['innerContent'] ?? null,
			'property'  => 'width',
			'important' => true,
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass}",
			'attr'     => $attrs['module']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} input[type='submit']",
			'attr'     => $attrs['submit']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} input[type='text'],{$orderClass} input[type='email'], {$orderClass} input[type='number'],{$orderClass} input[type='tel'],{$orderClass} input[type='password'],{$orderClass} input[type='url'],{$orderClass} textarea",
			'attr'     => $attrs['input']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .wpcf7-select",
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

	public function get_cf7() {
		if ( $this->props['cf7_forms'] === 'default' ) {
			$contact_forms = 'Please select an contact form!';
		} else {
			$contact_forms = do_shortcode( '[contact-form-7 id="' . $this->props['cf7_forms'] . '" ]' );
		}

		return $contact_forms;
	}

	public function render( $attrs, $content, $render_slug ) {

		return sprintf( '<div class="et_pb_module_inner"><div class="df-cf7-container">%1$s</div></div>', $this->get_cf7() );
	}

}
