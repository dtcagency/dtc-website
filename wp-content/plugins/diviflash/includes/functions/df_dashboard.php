<?php
defined('ABSPATH') || die();

/**
 * Process styles for diviflash
 * from dashboard settings
 *
 * - menu hover line styles
 *
 * @return void
 */
function df_process_menu_line_styles_fb()
{
	$menu_line = get_option('df_menu_bottom_line');
	$hide_menu_border_bottom = get_option('df_menu_hide_bottom_border');

	$styles = '';

	// generate menu line styles
	// check if the option is on?
	if ($menu_line == '1') {
		$line_color = get_option('df_menu_bottom_line_color');
		$line_height = get_option('df_menu_bottom_line_height');
		$line_width = get_option('df_menu_line_width', 'full');
		$line_distance = get_option('df_menu_bottom_line_distance');
		$line_distance_fixed = get_option('df_menu_bottom_line_distance_fixed');
		$line_animation = get_option('df_menu_line_animation');

		$width = array(
			'full' => array(
				'width' => '100%',
				'left' => '0',
				'right' => '0'
			),
			'half_left' => array(
				'width' => '50%',
				'left' => '0',
				'right' => 'auto'
			),
			'half_right' => array(
				'width' => '50%',
				'right' => '0',
				'left' => 'auto'
			)
		);
		if ( ! isset( $width[ $line_width ] ) || ! is_array( $width[ $line_width ] ) ) {
			$line_width = 'full';
		}
		$line_dims = $width[ $line_width ];
		// menu line styles
		$styles .= sprintf('
				#top-menu-nav .nav li a {
					position: relative;
				}
				#top-menu-nav ul.nav > li > a:before {
					content: "";
					display: block;
					width: %1$s;
					left: %2$s;
					right: %3$s;
					height: %4$spx;
					background-color: %5$s;
					position: absolute;
					bottom: %6$spx;
					transition: all .3s ease;
					transform: scaleX(0);
					transform-origin: %7$s;
				}
				.et-fixed-header #top-menu-nav .nav li a:before {
					bottom: %8$spx;
				}
				#top-menu-nav .nav li a:hover:before,
				#top-menu-nav .nav li.current-menu-item a:before {
					transform: scaleX(1);
				}
			',
			esc_attr( $line_dims['width'] ),
			esc_attr( $line_dims['left'] ),
			esc_attr( $line_dims['right'] ),
			esc_attr($line_height),
			esc_attr($line_color),
			esc_attr($line_distance),
			esc_attr($line_animation),
			esc_attr($line_distance_fixed)
		);
	}
	;

	// menu button styles
	if ($hide_menu_border_bottom == '1') {
		$styles .= '#main-header {box-shadow: none !important;}';
	}

	// menu item distance between
	$styles .= sprintf('
			#top-menu-nav .nav li:not(:last-child) {
				padding-right: %1$spx;
			}
		',
		esc_attr(get_option('df_menu_item_distance'))
	);

	wp_add_inline_style('df-lib-styles', $styles);
}
add_action('wp_enqueue_scripts', 'df_process_menu_line_styles_fb', 99);

/**
 * Adding mime type support
 * from the dashboard settings
 *
 * @return object
 */
add_filter('mime_types', 'df_adding_mime_types_support', 99);

function df_adding_mime_types_support( $mimes ) {
    $df_general_svg_support = get_option('df_general_svg_support');
    $df_general_json_support = get_option('df_general_json_support');
    $df_general_spline_support = get_option('df_general_spline_support');

	if ($df_general_svg_support == 1) {
		if (!defined('ALLOW_UNFILTERED_UPLOADS')) {
			define('ALLOW_UNFILTERED_UPLOADS', true);
		}
		$mimes['svg'] = 'image/svg+xml';
	}
	if ($df_general_json_support == 1) {
		if (!defined('ALLOW_UNFILTERED_UPLOADS')) {
			define('ALLOW_UNFILTERED_UPLOADS', true);
		}
        $mimes['json']  = 'text/plain';
    }

    if ( $df_general_spline_support == 1 ) {
        if ( ! defined('ALLOW_UNFILTERED_UPLOADS' ) ) {
            define( 'ALLOW_UNFILTERED_UPLOADS', true );
        }
        $mimes['splinecode'] = 'application/octet-stream';
    }

    return $mimes;
}

/**
 * Create New Admin Column
 * for shortcode to use
 *
 * @return Object
 */
add_filter('manage_et_pb_layout_posts_columns', 'df_shortcode_create_shortcode_column', 5);

function df_shortcode_create_shortcode_column($columns)
{

	$df_general_library_shortcode = get_option('df_general_library_shortcode');

	if ($df_general_library_shortcode == 1) {
		$columns['df_shortcode_id'] = __('Shortcode', 'divi_flash');
	}

    if($df_general_library_shortcode == 1) {
        $columns['df_shortcode_id'] = __( 'Shortcode', 'divi_flash');
    }

    return $columns;
}
/**
 * Display Shortcode
 * from library post type
 *
 * @return Void
 */
add_action('manage_et_pb_layout_posts_custom_column', 'df_shortcode_content', 5, 2);
function df_shortcode_content($column, $id)
{
	if ('df_shortcode_id' == $column) {
		?>
		<div class="df-shortcode-wrapper">
			<p class="df-shortcode-copy"><?php echo '[df_layout_shortcode id="' . esc_attr($id) . '"]'; ?></p>
			<p class="df-cpy-tooltip">Click to copy</p>
		</div>
		<?php
	}
}
/**
 * Function to show the module
 * from library item

 * 
 * On Divi 5, uses the native divi/global-layout block through et_builder_render_layout()
 * (same path as Theme Builder / block content) instead of legacy [et_pb_section global_module].
 *
 * @return String
 */
function df_module_shortcode_callback($atts)
{
	$atts      = shortcode_atts(array('id' => ''), $atts);
	$layout_id = absint($atts['id']);

	if (!$layout_id) {
		return '';
	}

	$use_d5_path = function_exists('et_builder_d5_enabled')
		&& et_builder_d5_enabled()
		&& function_exists('et_builder_render_layout');

	if ($use_d5_path) {
		$layout_post = get_post($layout_id);
		$type_ok     = $layout_post && (!defined('ET_BUILDER_LAYOUT_POST_TYPE') || $layout_post->post_type === ET_BUILDER_LAYOUT_POST_TYPE);
		$status_ok   = $layout_post && in_array($layout_post->post_status, array('publish', 'private'), true)
			&& ('publish' === $layout_post->post_status || current_user_can('read_post', $layout_id));

		if ($type_ok && $status_ok) {
			// BlockParser expects globalModule as a JSON string (see divi/global-layout regex in BlockParser.php).
			$attrs_json = wp_json_encode(
				array('globalModule' => (string) $layout_id),
				JSON_UNESCAPED_SLASHES
			);
			$block = '<!-- wp:divi/global-layout ' . $attrs_json . ' /-->';

			return et_builder_render_layout($block);
		}
	}

	return do_shortcode('[et_pb_section global_module="' . esc_attr((string) $layout_id) . '"][/et_pb_section]');
}
add_shortcode('df_layout_shortcode', 'df_module_shortcode_callback');

/**
 * Hide WordPress version
 *
 * @return Void
 */
add_action('init', function () {
	$hide_wp_version = get_option('df_general_hide_wordpress_version');
	if ($hide_wp_version == 1) {
		remove_action('wp_head', 'wp_generator');
		add_filter('the_generator', '__return_empty_string');
		add_filter('style_loader_src', function ($src) {
			return remove_query_arg('ver', $src);
		}, 9999);
		add_filter('script_loader_src', function ($src) {
			return remove_query_arg('ver', $src);
		}, 9999);
	}
});
