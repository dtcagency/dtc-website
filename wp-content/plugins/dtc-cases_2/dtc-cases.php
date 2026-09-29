<?php
/**
 * Plugin Name: DTC Cases Module
 * Plugin URI:  https://design.dtc.agency
 * Description: Portfolio case studies CPT with phase-based meta boxes and pixel-perfect frontend output.
 * Version:     1.0.0
 * Author:      DTC Agency
 * Author URI:  https://design.dtc.agency
 * Text Domain: dtc-cases
 * License:     GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

define( 'DTC_CASES_VERSION', '1.9.0' );
define( 'DTC_CASES_PATH', plugin_dir_path( __FILE__ ) );
define( 'DTC_CASES_URL', plugin_dir_url( __FILE__ ) );

require_once DTC_CASES_PATH . 'includes/cpt.php';
require_once DTC_CASES_PATH . 'includes/meta-boxes.php';
require_once DTC_CASES_PATH . 'includes/settings.php';
require_once DTC_CASES_PATH . 'includes/shortcode.php';
require_once DTC_CASES_PATH . 'includes/order.php';

/**
 * Enqueue frontend stylesheet on relevant pages.
 */
add_action( 'wp_enqueue_scripts', function () {
	global $post;

	$load = is_singular( 'dtc_case' ) || is_post_type_archive( 'dtc_case' );

	if ( ! $load && is_a( $post, 'WP_Post' ) ) {
		$load = has_shortcode( $post->post_content, 'dtc_cases_grid' );
	}

	if ( $load ) {
		wp_enqueue_style(
			'dtc-cases',
			DTC_CASES_URL . 'assets/css/dtc-cases.css',
			[],
			DTC_CASES_VERSION
		);
	}
} );

/**
 * Load the custom single template for dtc_case posts.
 */
add_filter( 'single_template', function ( $template ) {
	global $post;
	if ( $post && 'dtc_case' === $post->post_type ) {
		$custom = DTC_CASES_PATH . 'templates/single-dtc_case.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
} );

/**
 * Apply the chosen Divi Theme Builder template to a dtc_case page.
 *
 * Divi picks a template via et_theme_builder_template_setting_get_template_id.
 * When the post meta is 'divi_{ID}' we return that template ID so Divi uses
 * the selected header/body/footer layouts instead of its default rules.
 */
add_filter( 'et_theme_builder_template_setting_get_template_id', function ( $template_id ) {
	if ( ! is_singular( 'dtc_case' ) ) {
		return $template_id;
	}
	$meta = (string) get_post_meta( get_the_ID(), 'dtc_case_page_template', true );
	if ( preg_match( '/^divi_(\d+)$/', $meta, $m ) ) {
		$chosen = (int) $m[1];
		if ( $chosen ) {
			return $chosen;
		}
	}
	return $template_id;
}, 20 );

/**
 * Flush rewrite rules on activation so the 'work' slug works immediately.
 */
register_activation_hook( __FILE__, function () {
	dtc_cases_register_cpt();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
