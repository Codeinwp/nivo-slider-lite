<?php
/**
 * Dev7studios Plugin Core
 *
 * @package     Plugin Core
 * @subpackage  Core
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Core Class
 *
 * @since 2.2
 */
abstract class Dev7_Core_Plugin {

	/**
	 * All of the specific plugin data
	 *
	 * @var object
	 * @access private
	 * @since  2.2
	 */
	private $labels;

	/**
	 * Is this plugin a "lite" plugin
	 *
	 * @var bool
	 * @access private
	 * @since  2.4.8
	 */
	private $is_lite;

	/**
	 * Plugin options
	 *
	 * @var array
	 * @access private
	 * @since  2.2
	 */
	private $options;

	/**
	 * Instance of Dev7 Admin Settings Core Class
	 *
	 * @var object
	 * @access private
	 * @since  2.2
	 */
	private $settings_class;

	/**
	 * Main construct
	 *
	 * @since 2.2
	 *
	 * @param array $labels Specific plugin label data
	 */
	public function __construct( $labels, $is_lite = false ) {
		$this->labels  = (object) $labels;
		$this->is_lite = $is_lite;
		$this->options = get_option( $this->labels->options_key );
		$this->setup_constants();
		$this->includes();
		$this->updater();
		$this->loader();
	}

	/**
	 * Setup core constants
	 *
	 * @access private
	 * @since  2.2
	 */
	private function setup_constants() {
		if ( ! defined( 'DEV7_SITE_URL' ) ) {
			define( 'DEV7_SITE_URL', 'https://themeisle.com' );
		}
		if ( ! defined( 'DEV7_STORE_URL' ) ) {
			define( 'DEV7_STORE_URL', 'http://themeisle.com' );
		}
		// Core Folder Path
		if ( ! defined( 'DEV7_CORE_DIR' ) ) {
			define( 'DEV7_CORE_DIR', dirname( __FILE__ ) );
		}
		// Core Folder URL
		if ( ! defined( 'DEV7_CORE_DIR' ) ) {
			define( 'DEV7_CORE_DIR', dirname( __FILE__ ) );
		}
	}

	/**
	 * Include required core files
	 *
	 * @access private
	 * @since  2.2
	 */
	private function includes() {
		if ( ! $this->is_lite ) {
			require_once DEV7_CORE_DIR . '/includes/edd_sl_plugin_updater.php';
		}
		require_once DEV7_CORE_DIR . '/includes/core.php';
		require_once DEV7_CORE_DIR . '/includes/functions.php';
		require_once DEV7_CORE_DIR . '/includes/assets.php';
		require_once DEV7_CORE_DIR . '/includes/post-type.php';
		require_once DEV7_CORE_DIR . '/includes/admin-settings.php';
		require_once DEV7_CORE_DIR . '/includes/admin-view.php';
		require_once DEV7_CORE_DIR . '/includes/admin-edit.php';
		require_once DEV7_CORE_DIR . '/includes/admin-ajax.php';
		require_once DEV7_CORE_DIR . '/includes/admin-images.php';
		require_once DEV7_CORE_DIR . '/includes/shortcode.php';
		require_once DEV7_CORE_DIR . '/includes/images.php';
	}

	/**
	 * Instantiate the EDD Plugin Updater
	 *
	 * @access private
	 * @uses   EDD_SL_Plugin_Updater()
	 * @since  2.2
	 */
	private function updater() {
		if ( ! $this->is_lite ) {
			$license_key = dev7_get_license_key( $this->labels->post_type, $this->options );
			new Dev7_EDD_SL_Plugin_Updater_16( DEV7_STORE_URL, $this->labels->plugin_file, array(
				'version'   => $this->labels->plugin_version,
				'license'   => $license_key,
				'item_name' => $this->labels->dev7_item_name,
				'author'    => 'Dev7studios',
			) );
		}
	}

	/**
	 * Loads all the Dev7 Core classes
	 *
	 * @access private
	 * @since  2.2
	 */
	private function loader() {
		new Dev7_Core_Assets( $this->labels, $this->is_lite );
		new Dev7_Core_Post_Type( $this->labels, $this->is_lite );
		$this->settings_class = new Dev7_Core_Admin_Settings( $this->labels, $this->is_lite );
		new Dev7_Core_Admin_View( $this->labels, $this->is_lite );
		new Dev7_Core_Admin_Edit( $this->labels, $this->is_lite );
		new Dev7_Core_Admin_AJAX( $this->labels, $this->is_lite );
		new Dev7_Core_Admin_Images( $this->labels, $this->is_lite );
		new Dev7_Core_Shortcode( $this->labels, $this->is_lite );
	}

	/**
	 * Makes public the settings helper from the Dev7 Settings class
	 *
	 * @access public
	 * @uses   Dev7_Core_Admin_Settings::settings_helper()
	 *
	 * @param array $args Settings arguments to be rendered as HTML
	 *
	 * @since  2.2
	 */
	public function settings_helper( $args ) {
		$this->settings_class->settings_helper( $args );
	}
}


