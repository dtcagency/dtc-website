<?php

namespace DIFL\Server\Modules\Spline;

use DIFL\Server\Traits\HasProps;
use DIFL\Server\Traits\ModuleUtil;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\Module\Options\Css\CssStyle;

class Spline implements DependencyInterface {
    use ModuleUtil;
    use HasProps;

    protected $config_file = 'Spline';

	public static function custom_css() {
		return \WP_Block_Type_Registry::get_instance()->get_registered( 'difl/spline' )->customCssFields;
	}

	public function get_common_styles( $args ) {
		$attrs      = $this->get_merged_attrs( $args );
		$attrs      = self::build_attr_for_styles( $attrs );
		$attrs      = self::get_default_value_for_styles( $attrs );
		$orderClass = $args['orderClass'] ?? '';

		$styles = [];

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

    /**
     * Get Spline scene URL based on source type
     *
     * @return string
     */
    protected function get_scene_url() {
        if ( empty( $this->props['spline_source_type'] ) ) {
            return '';
        }

        if ( $this->props['spline_source_type'] === 'external' && ! empty( $this->props['spline_url'] ) ) {
            return $this->props['spline_url'];
        }

        if ( $this->props['spline_source_type'] === 'from_media' && ! empty( $this->props['spline_upload'] ) ) {
            return $this->props['spline_upload'];
        }

        // Fallback empty string if nothing matches
        return '';
    }

    public function render( $attr, $content, $render_slug ) {
        // Enqueue scripts
        wp_enqueue_script( 'df-spline-viewer-lib' );
        wp_enqueue_script( 'df-spline-viewer-control' );

        $props = $this->props;
        $is_loading_anim_enabled = ! empty( $props['loading_anim'] ) && 'on' === $props['loading_anim'];

        // Setting options
        $options = [
            'url'               => ! empty( $this->get_scene_url() ) ? esc_url( $this->get_scene_url() ) : '',
            'width'             => ! empty( $props['width'] ) ? esc_attr( $props['width'] ) : '100%',
            'height'            => ! empty( $props['height'] ) ? intval( $props['height'] ) : 500,
            'hint'              => ! empty( $props['hint'] ) && 'on' === $props['hint'],
            'unloadable'        => ! empty( $props['unloadable'] ) && 'on' === $props['unloadable'],
            'loading'           => ! empty( $props['loading'] ) ? esc_attr( $props['loading'] ) : 'auto',
            'loading-anim'      => $is_loading_anim_enabled,
            'loading-anim-type' => $is_loading_anim_enabled && ! empty( $props['loading_anim_type'] ) ? esc_attr( $props['loading_anim_type'] ) : '',
            'events-target'     => ! empty( $props['events_target'] ) ? esc_attr( $props['events_target'] ) : 'local',
            'background'        => ! empty( $props['background'] ) ? esc_attr( $props['background'] ) : '',
        ];

        // Output wrapper
        return sprintf(
            '<div class="et_pb_module_inner"><div class="df-spline-viewer" data-options=\'%1$s\'></div>%2$s</div>',
            wp_json_encode( $options ),
            $content
        );
    }
}
