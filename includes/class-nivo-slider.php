<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0.0
 * @package    nivo-slider
 * @subpackage nivo-slider/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Slider {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      Nivo_Slider_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    3.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    3.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'nivo-slider';
		$this->version = '2.1.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		//$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Nivo_Slider_Loader. Orchestrates the hooks of the plugin.
	 * - Nivo_Slider_i18n. Defines internationalization functionality.
	 * - Nivo_Slider_Admin. Defines all hooks for the admin area.
	 * - Nivo_Slider_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		$this->loader = new Nivo_Slider_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Nivo_Slider_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Nivo_Slider_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Nivo_Slider_Admin( $this->get_plugin_name(), $this->get_version() );
		$post_type = $plugin_admin::get_plugin_settings()->get_label( 'post_type' );
		$plugin_shortcode_label = $plugin_admin::get_plugin_settings()->get_label( 'shortcode' );
		// Actions Nivo_Slider_Admin
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_admin, 'init_tinymce' );
		$this->loader->add_action( 'admin_print_scripts', $plugin_admin, 'admin_print_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu', 999 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'remove_admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		// Filters Nivo_Slider_Admin
		$this->loader->add_filter( $post_type . '_script_settings', $plugin_admin, 'script_settings' );
		$this->loader->add_filter( $post_type . '_post_type_menu_icon', $plugin_admin, 'menu_icon' );
		$this->loader->add_filter( $post_type . '_post_type_labels', $plugin_admin, 'plugin_labels' );
		$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'plugin_action_links', 10, 2 );
		$this->loader->add_filter( 'nivo_field_upsell', $plugin_admin, 'add_upsell', 10, 2 );
		$this->loader->add_filter( 'nivo_slider_lite_logger_flag', $plugin_admin, 'check_logger', 10, 2 );
		$this->loader->add_filter( 'get_nivo_settings', $plugin_admin, 'get_nivo_settings' );
		$this->loader->add_filter( 'nivo_default_val', $plugin_admin, 'nivo_default_val', 10, 3 );

		$plugin_admin_edit = new Nivo_Core_Admin_Edit();
		// Actions Nivo_Core_Admin_Edit
		$this->loader->add_action( 'admin_init', $plugin_admin_edit, 'admin_init' );
		$this->loader->add_action( 'manage_edit-' . $post_type . '_columns', $plugin_admin_edit, 'edit_columns' );
		$this->loader->add_action( 'manage_' . $post_type . '_posts_custom_column', $plugin_admin_edit, 'custom_columns' );
		// Filters Nivo_Core_Admin_Edit
		$this->loader->add_filter( $post_type . '_admin_edit_settings', $plugin_admin_edit, 'admin_edit_settings' );
		$this->loader->add_filter( 'get_image_source_details', $plugin_admin_edit, 'get_image_source_details', 10, 2 );

		$plugin_admin_ajax = new Nivo_Core_Admin_Ajax();
		// Actions Nivo_Core_Admin_Ajax
		$this->loader->add_action( 'wp_ajax_' . $post_type . '_set_meta_link', $plugin_admin_ajax, 'set_meta_link' );
		$this->loader->add_action( 'wp_ajax_' . $post_type . '_get_meta_link', $plugin_admin_ajax, 'get_meta_link' );
		$this->loader->add_action( 'wp_ajax_' . $post_type . '_load_images', $plugin_admin_ajax, 'load_images' );
		$this->loader->add_action( 'wp_ajax_' . $post_type . '_reattach_images', $plugin_admin_ajax, 'reattach_images' );
		$this->loader->add_action( 'wp_ajax_track_url', $plugin_admin_ajax, 'track_url' );

		$plugin_admin_images = new Nivo_Core_Admin_Images();
		// Actions Nivo_Core_Admin_Images
		$this->loader->add_action( 'after_setup_theme', $plugin_admin_images, 'theme_support_check', 999 );
		$this->loader->add_action( 'admin_footer', $plugin_admin_images, 'print_media_templates' );
		// Filters Nivo_Core_Admin_Images
		$this->loader->add_filter( 'media_view_strings', $plugin_admin_images, 'custom_media_string', 11, 2 );
		$this->loader->add_filter( 'uber_media_pre_insert', $plugin_admin_images, 'mmp_pre_insert' );

		$plugin_model = new Nivo_Core_Model();
		// Actions Nivo_Core_Model
		$this->loader->add_action( 'init', $plugin_model, 'register' );
		$this->loader->add_action( 'save_post', $plugin_model, 'save_post' );
		// Filters Nivo_Core_Model
		$this->loader->add_filter( 'post_updated_messages', $plugin_model, 'post_updated_messages' );
		$this->loader->add_filter( $post_type . '_post_meta_save', $plugin_model, 'save_post_meta' );

		$plugin_shortcode = new Nivo_Core_Shortcode();
		add_shortcode( $plugin_shortcode_label, array( $plugin_shortcode, 'shortcode' ) );
		// Actions Nivo_Core_Shortcode
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_shortcode, 'register_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_shortcode, 'register_styles' );
		// Filters Nivo_Core_Shortcode
		$this->loader->add_filter( 'the_posts', $plugin_shortcode, 'shortcode_enqueue_core' );
		$this->loader->add_filter( 'nivo_slider', $plugin_shortcode, 'shortcode_enqueue_core' );
		$this->loader->add_filter( $post_type . '_shortcode_styles_enqueue', $plugin_shortcode, 'shortcode_styles_enqueue', 10, 2 );
		$this->loader->add_filter( $post_type . '_shortcode_output', $plugin_shortcode, 'shortcode_output', 10, 5 );

		$plugin_widget = new Nivo_Slider_Widget();
		// Actions Nivo_Core_Shortcode
		$this->loader->add_action( 'widgets_init', $plugin_widget, 'register_widget' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Nivo_Slider_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    3.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     3.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     3.0.0
	 * @return    Nivo_Slider_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     3.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
