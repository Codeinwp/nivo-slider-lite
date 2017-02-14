<?php
/**
 * Plugin Name: Slider by Nivo - Responsive Image Slider
 * Plugin URI: https://themeisle.com/plugins/nivo-slider-lite
 * Description: Nivo Slider is The Most Popular And Easiest to Use WordPress Slider Plugin.
 * Version: 1.0.6
 * Author: ThemeIsle
 * Author URI: https://themeisle.com/
 * Text Domain: nivo-slider
 * Domain Path: languages
 **/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WordPress_Nivo_Slider_Lite Class
 */
class WordPress_Nivo_Slider_Lite {

	/**
	 * Plugin Version
	 *
	 * @var string
	 * @access private
	 */
	private $version = '1.0.5';

	public function __construct() {
		$this->setup_constants();
		$this->loader();

		add_action( 'plugins_loaded', array( $this, 'compat_check' ) );
	}

	public function compat_check() {
		if ( class_exists( 'WordPress_Nivo_Slider' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( 'nivo-slider/nivo-slider.php' ) ) {
				return;
			}
		}

		new Dev7_Nivo_Slider_Lite( $this->version );
	}

	/**
	 * Setup plugin constants
	 */
	private function setup_constants() {
		// Plugin Folder Path
		if ( ! defined( 'NIVO_SLIDER_LITE_PLUGIN_DIR' ) ) {
			define( 'NIVO_SLIDER_LITE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'NIVO_SLIDER_LITE_PLUGIN_URL' ) ) {
			define( 'NIVO_SLIDER_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'NIVO_SLIDER_LITE_PLUGIN_FILE' ) ) {
			define( 'NIVO_SLIDER_LITE_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Basename
		if ( ! defined( 'NIVO_SLIDER_LITE_PLUGIN_BASENAME' ) ) {
			define( 'NIVO_SLIDER_LITE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}
	}

	/**
	 * Load core plugin files
	 */
	private function loader() {
		require_once NIVO_SLIDER_LITE_PLUGIN_DIR . 'includes/plugin.php';
	}
}

// Let's go!
$WordPress_Nivo_Slider_Lite = new WordPress_Nivo_Slider_Lite();
