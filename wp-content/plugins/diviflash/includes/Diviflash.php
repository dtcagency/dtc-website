<?php

class DIFL_Diviflash extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'difl-diviflash';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'diviflash';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = DIFL_VERSION;

	/**
	 * DIFL_Diviflash constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'diviflash', $args = array() ) {		
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
	}

	/**
	 * Skip Divi 4 extension assets in the Divi 5 Visual Builder.
	 *
	 * Parent {@see DiviExtension::wp_hook_enqueue_scripts()} loads `styles/style.min.css`,
	 * `backend-style.min.css` (when FB), and D4 `frontend-bundle` / `builder-bundle` JS.
	 * D5 VB uses `Builder/Visual-Builder/build/` instead; loading D4 CSS/JS there causes conflicts and bloat.
	 *
	 * @since 5.0.0
	 */
	public function wp_hook_enqueue_scripts() {
		if (
			function_exists( 'et_builder_d5_enabled' )
			&& et_builder_d5_enabled()
			&& function_exists( 'et_core_is_fb_enabled' )
			&& et_core_is_fb_enabled()
		) {
			return;
		}

		parent::wp_hook_enqueue_scripts();
	}
}

new DIFL_Diviflash;
