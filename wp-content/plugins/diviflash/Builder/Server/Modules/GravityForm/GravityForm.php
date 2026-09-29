<?php

namespace DIFL\Server\Modules\GravityForm;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Layout\Components\StyleCommon\CommonStyle;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class GravityForm implements DependencyInterface {
	use ModuleUtil;
	use HasProps;

	protected $config_file = 'GravityForm';

	public static function custom_css()
	{
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/gravity-form' )->customCssFields;
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

		$inputSelector         = "{$orderClass} .gfield:not(.gfield--type-time) input[type='text'], {$orderClass} input[type='password'], {$orderClass} input[type='url'], {$orderClass} input[type='phone'], {$orderClass} input[type='number']";
		$inputSelectorFocus    = "{$orderClass} .gfield:not(.gfield--type-time) input[type='text']:focus, {$orderClass} input[type='password']:focus, {$orderClass} input[type='url']:focus, {$orderClass} input[type='phone']:focus, {$orderClass} input[type='number']:focus";
		$selectSelector        = "{$orderClass} .gfield:not(.gfield--type-time) select";
		$selectSelectorFocus   = "{$orderClass} .gfield:not(.gfield--type-time) select:focus";
		$textareaSelector      = "{$orderClass} textarea";
		$textareaSelectorFocus = "{$orderClass} textarea:focus";
		$checkboxSelector      = "{$orderClass} .ginput_container:not(.ginput_container_consent) input[type='checkbox']";
		$radioSelector         = "{$orderClass} input[type='radio']";
		$buttonSelector        = "{$orderClass} .button";
		$confirmSelector       = "{$orderClass} .gform_confirmation_message";
		$sectionSelector       = "{$orderClass} .gsection";
		$progressSelector      = "{$orderClass} .gf_progressbar_percentage";
		$progressWrapper       = "{$orderClass} .gf_progressbar_wrapper";
		$gformHeading          = "{$orderClass} .gform_heading";
		$consent               = "{$orderClass} .gfield--type-consent";
		$error_box_global      = "{$orderClass} .gform_validation_errors";
		$error_box             = "{$orderClass} .validation_message";
		$progress_steps        = "{$orderClass} .gf_page_steps";
		$time_selector         = "{$orderClass} .gfield.gfield--type-time input[type='text'], {$orderClass} .gfield.gfield--type-time select";
		$time_input            = "{$orderClass} .gfield.gfield--type-time input[type='text']";
		$time_select           = "{$orderClass} .gfield.gfield--type-time select";
		$alignmentValues       = [
			'left'   => 'flex-start',
			'right'  => 'flex-end',
			'center' => 'center',
		];

		$styles = [];

		$common_styles[] = CssStyle::style( [
            'selector'  => $orderClass,
            'attr'      => $attrs['css'] ?? [],
            'cssFields' => self::custom_css(),
        ] );


		$common_styles[] = CommonStyle::style( [
			'selector'  => $inputSelector,
			'attr'      => $attrs['input_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} input::placeholder",
			'attr'      => $attrs['input_placeholder_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );


		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .gfield:not(.gfield--type-time) select",
			'attr'      => $attrs['select_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );


		$common_styles[] = CommonStyle::style( [
			'selector'  => $textareaSelector,
			'attr'      => $attrs['textarea_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} textarea::placeholder",
			'attr'      => $attrs['textarea_placeholder_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );


		// $common_styles[] = CommonStyle::style( [
		// 	'selector'            => $orderClass,
		// 	'attr'                => $attrs['gravity_forms']['innerContent'] ?? null,
		// 	'declarationFunction' => function () {
		// 		return "
        //     --checkbox-size: {$this->props['checkbox_size']};
        //     --checkbox-border-color: {$this->props['checkbox_bordercolor']};
        //     --checkbox-tick-color: {$this->props['checkbox_tickcolor']};
        //     --checkbox-border-width: {$this->props['checkbox_bordersize']};
        //     --checkbox-background: {$this->props['checkbox_background']};
        //     --checkbox-gap: {$this->props['checkbox_gap']};
        //     --radio-size: {$this->props['radio_size']};
        //     --radio-dot-color: {$this->props['radio_dotcolor']};
        //     --radio-border-color: {$this->props['radio_bordercolor']};
        //     --radio-border-width: {$this->props['radio_bordersize']};
        //     --radio-background: {$this->props['radio_background']};
        //     --radio-gap: {$this->props['radio_gap']};
        //     --radio-border-radius: " . ( $this->props['radio_box'] === 'on' ? '0' : '50%' ) . ";
        //     --consent-checkbox-size: {$this->props['consent_checkbox_size']};
        //     --consent-checkbox-border-color: {$this->props['consent_checkbox_bordercolor']};
        //     --consent-checkbox-tick-color: {$this->props['consent_checkbox_tickcolor']};
        //     --consent-checkbox-border-width: {$this->props['consent_checkbox_bordersize']};
        //     --consent-checkbox-background: {$this->props['consent_checkbox_background']};
        //     --consent-description-background: {$this->props['consent_description_background']};
        //     --consent-description-bordercolor: {$this->props['consent_description_bordercolor']};";
		// 	},
		// ] );

		$common_styles[] = CommonStyle::style( [
			'selector'            => $orderClass,
			'attr'                => $attrs['gravity_forms']['innerContent'] ?? null,
			'declarationFunction' => function () {
				return "
				--checkbox-size: {$this->props['checkbox_size']};
				--checkbox-border-color: " . ( $this->props['checkbox_bordercolor'] ?? '#333' ) . ";
				--checkbox-tick-color: " . ( $this->props['checkbox_tickcolor'] ?? '#333' ) . ";
				--checkbox-border-width: {$this->props['checkbox_bordersize']};
				--checkbox-background: {$this->props['checkbox_background']};
				--checkbox-gap: {$this->props['checkbox_gap']};

				--radio-size: {$this->props['radio_size']};
				--radio-dot-color: " . ( $this->props['radio_dotcolor'] ?? '#333' ) . ";
				--radio-border-color: " . ( $this->props['radio_bordercolor'] ?? '#333' ) . ";
				--radio-border-width: {$this->props['radio_bordersize']};
				--radio-background: {$this->props['radio_background']};
				--radio-gap: {$this->props['radio_gap']};
				--radio-border-radius: " . ( $this->props['radio_box'] === 'on' ? '0' : '50%' ) . ";

				--consent-checkbox-size: {$this->props['consent_checkbox_size']};
				--consent-checkbox-border-color: " . ( $this->props['consent_checkbox_bordercolor'] ?? '#333' ) . ";
				--consent-checkbox-tick-color: " . ( $this->props['consent_checkbox_tickcolor'] ?? '#333' ) . ";
				--consent-checkbox-border-width: {$this->props['consent_checkbox_bordersize']};
				--consent-checkbox-background: {$this->props['consent_checkbox_background']};
				--consent-description-background: {$this->props['consent_description_background']};
				--consent-description-bordercolor: " . ( $this->props['consent_description_bordercolor'] ?? '#ddd' ) . ";
				";
			},
		] );



		$common_styles[] = CommonStyle::style( [
			'selector'  => $inputSelectorFocus,
			'attr'      => $attrs['input_focus_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $inputSelectorFocus,
			'attr'      => $attrs['input_focus_border_color']['innerContent'] ?? null,
			'property'  => 'border-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $inputSelectorFocus,
			'attr'      => $attrs['input_focus_text_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $selectSelectorFocus,
			'attr'      => $attrs['select_focus_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $selectSelectorFocus,
			'attr'      => $attrs['select_focus_border_color']['innerContent'] ?? null,
			'property'  => 'border-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $selectSelectorFocus,
			'attr'      => $attrs['select_focus_text_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $textareaSelectorFocus,
			'attr'      => $attrs['textarea_focus_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $textareaSelectorFocus,
			'attr'      => $attrs['textarea_focus_border_color']['innerContent'] ?? null,
			'property'  => 'border-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $textareaSelectorFocus,
			'attr'      => $attrs['textarea_focus_text_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );


		if ( 'on' !== ( $this->props['button_full_width'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .gform_footer, {$orderClass} .gform_page_footer",
				'attr'                => $attrs['button_align']['innerContent'] ?? null,
				'declarationFunction' => function ( $value ) use ( $alignmentValues ) {
					return "justify-content: {$alignmentValues[$value['attrValue']]};";
				},
			] );
		}

		if ( 'on' === ( $this->props['button_full_width'] ?? '' ) ) {
			$common_styles[] = CommonStyle::style( [
				'selector'            => "{$orderClass} .button",
				'attr'                => $attrs['button_full_width']['innerContent'] ?? null,
				'declarationFunction' => function () {
					return 'width: 100% !important;';
				},
			] );
		}


		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$buttonSelector} svg path",
			'attr'      => $attrs['btn_icon_color']['innerContent'] ?? null,
			'property'  => 'fill',
			'important' => true,
		] );


		$common_styles[] = CommonStyle::style( [
			'selector'  => $progressSelector,
			'attr'      => $attrs['progress_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );


		$common_styles[] = CommonStyle::style( [
			'selector'  => $progress_steps,
			'attr'      => $attrs['steps_align']['innerContent'] ?? null,
			'property'  => 'justify-content',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step .gf_step_number",
			'attr'      => $attrs['step_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step .gf_step_number",
			'attr'      => $attrs['step_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step.gf_step_completed .gf_step_number",
			'attr'      => $attrs['step_bordercolor']['innerContent'] ?? null,
			'property'  => 'border-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step_active .gf_step_number",
			'attr'      => $attrs['active_step_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step_active .gf_step_number",
			'attr'      => $attrs['active_step_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step_active .gf_step_number",
			'attr'      => $attrs['active_step_bordercolor']['innerContent'] ?? null,
			'property'  => 'border-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step.gf_step_completed .gf_step_number:before",
			'attr'      => $attrs['complete_step_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step.gf_step_completed .gf_step_number:before",
			'attr'      => $attrs['complete_step_background']['innerContent'] ?? null,
			'property'  => 'border-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step.gf_step_completed .gf_step_number:after",
			'attr'      => $attrs['complete_step_color']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$progress_steps} .gf_step.gf_step_completed .gf_step_number",
			'attr'      => $attrs['complete_step_bordercolor']['innerContent'] ?? null,
			'property'  => 'border-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $progress_steps,
			'attr'      => $attrs['step_bottom_bordercolor']['innerContent'] ?? null,
			'property'  => 'border-bottom-color',
			'important' => true,
		] );


		$common_styles[] = CommonStyle::style( [
			'selector'  => $error_box_global,
			'attr'      => $attrs['error_box_global_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => $error_box,
			'attr'      => $attrs['error_box_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );


		$common_styles[] = CommonStyle::style( [
			'selector'  => $time_selector,
			'attr'      => $attrs['time_background']['innerContent'] ?? null,
			'property'  => 'background-color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$time_input}::placeholder",
			'attr'      => $attrs['time_input_placeholder']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles[] = CommonStyle::style( [
			'selector'  => "{$orderClass} .hour_minute_colon",
			'attr'      => $attrs['time_colon']['innerContent'] ?? null,
			'property'  => 'color',
			'important' => true,
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .gform_heading",
			'attr'     => $attrs['heading']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .gfield:not(.gfield--type-time) input[type='text']:not(.chosen-search-input), {$orderClass} input[type='password'], {$orderClass} input[type='url'], {$orderClass} input[type='phone'], {$orderClass} input[type='number'], {$orderClass} input[type='tel'], {$orderClass} input[type='email']",
			'attr'     => $attrs['input']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .gfield:not(.gfield--type-time) select, {$orderClass} .chosen-container-single .chosen-single, {$orderClass} .chosen-container-multi .chosen-choices",
			'attr'     => $attrs['select']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} textarea",
			'attr'     => $attrs['textarea']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .button",
			'attr'     => $attrs['button']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .gsection",
			'attr'     => $attrs['section']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .gform_confirmation_message",
			'attr'     => $attrs['confirm']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .gform_validation_errors",
			'attr'     => $attrs['error_box_global']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .gfield.gfield--type-time input[type='text'],{$orderClass} .gfield.gfield--type-time select",
			'attr'     => $attrs['time']['decoration']['border'] ?? null,
            'declarationFunction' => function( $props ) {
                return $this->addOverflowHidden($props);
            },
		] );

		$common_styles [] = CommonStyle::style( [
			'selector' => "{$orderClass} .validation_message",
			'attr'     => $attrs['error_box']['decoration']['border'] ?? null,
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

	public function df_gravity_form_render() {
		if ( ! isset( $this->props['gravity_forms'] ) || $this->props['gravity_forms'] == 'none' ) {
			return '<h2>Please select a form</h2>';
		}

		$id          = $this->props['gravity_forms'];
		$title       = $this->props['show_title'] === 'on' ? 'true' : 'false';
		$description = $this->props['show_description'] === 'on' ? 'true' : 'false';
		$ajax        = $this->props['use_ajax'] === 'on' ? 'true' : 'false';
		$tabindex    = $this->props['tabindex'];

		$shortcode = "[gravityform id='{$id}' title={$title} description={$description} ajax={$ajax} tabindex={$tabindex} ]";
		ob_start();
		echo do_shortcode( $shortcode );
		$gform = ob_get_clean();

		return $gform;
	}

	public function render( $attrs, $content, $render_slug ) {

		return sprintf( '<div class="et_pb_module_inner"><div class="df-gravity-form-container">
                %1$s
            </div></div>',
			$this->df_gravity_form_render()
		);
	}

}
