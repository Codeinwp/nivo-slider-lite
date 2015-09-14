<?php
/**
 * Plugin Name: Nivo Slider
 * Plugin URI: http://dev7studios.com/plugins/nivo-slider/
 * Description: The official WordPress plugin for the Nivo Slider
 * Version: 2.4.7
 * Author: Dev7studios
 * Author URI: http://dev7studios.com
 * Text Domain: nivo-slider
 * Domain Path: languages
 *
 *
 * @package Nivo Slider
 * @author  Dev7studios
 * @version 2.4.7
 **/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WordPress_Nivo_Slider Class
 *
 * @since 2.2
 */
class WordPress_Nivo_Slider {

	/**
	 * Plugin Version
	 *
	 * @var string
	 * @access private
	 * @since  2.2
	 */
	private $version = '2.4.7';

	/**
	 * Main construct
	 *
	 * @since 2.2
	 */
	public function __construct() {
		$this->setup_constants();
		$this->loader();
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since  2.2
	 */
	private function setup_constants() {
		// Plugin Folder Path
		if ( ! defined( 'NIVO_SLIDER_PLUGIN_DIR' ) ) {
			define( 'NIVO_SLIDER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'NIVO_SLIDER_PLUGIN_URL' ) ) {
			define( 'NIVO_SLIDER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'NIVO_SLIDER_PLUGIN_FILE' ) ) {
			define( 'NIVO_SLIDER_PLUGIN_FILE', __FILE__ );
		}

		// Plugin Basename
		if ( ! defined( 'NIVO_SLIDER_PLUGIN_BASENAME' ) ) {
			define( 'NIVO_SLIDER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}
	}

	/**
	 * Load core plugin files
	 *
	 * @access private
	 * @since  2.2
	 */
	private function loader() {
		require_once NIVO_SLIDER_PLUGIN_DIR . 'includes/plugin.php';
		require_once NIVO_SLIDER_PLUGIN_DIR . 'includes/widget.php';
		new Dev7_Nivo_Slider( $this->version );
	}
}

// Let's go!
$WordPress_Nivo_Slider = new WordPress_Nivo_Slider();