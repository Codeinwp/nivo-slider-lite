<?php
/**
 * Assets Class
 *
 * @package     Plugin Core
 * @subpackage  Assets
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets Core Class
 *
 * @since 2.2
 */
class Dev7_Core_Assets extends Dev7_Core {

	/**
	 * "construct" for the Dev7 core Assets class
	 */
	protected function core_init() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Registers the TinyMCE plugins
	 *
	 * @since  2.2
	 * @access public
	 */
	public function init() {
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) && get_user_option( 'rich_editing' ) == 'true' ) {
			add_filter( 'mce_external_plugins', array( $this, 'mce_add_plugin' ) );
			add_filter( 'mce_buttons_2', array( $this, 'mce_register_button' ) );
		}
	}

	/**
	 * Registers the TinyMCE script
	 *
	 * @since  2.2
	 * @access public
	 */
	public function mce_add_plugin( $plugin_array ) {
		$plugin_array[$this->labels->post_type] = plugins_url( 'assets/js/tinymce.js', $this->labels->plugin_file );

		return $plugin_array;
	}

	/**
	 * Registers the TinyMCE button
	 *
	 * @since  2.2
	 * @access public
	 */
	public function mce_register_button( $buttons ) {
		array_push( $buttons, "|", $this->labels->post_type );

		return $buttons;
	}

	/**
	 * Prints the [galleries] as javascript to be used by the TinyMCE plugin
	 *
	 * @since  2.2
	 * @access public
	 */
	public function admin_print_scripts() {
		// Galleries list for TinyMCE dropdown
		$galleries = get_posts( array( 'post_type' => $this->labels->post_type, 'posts_per_page' => - 1 ) );
		$list      = array();
		foreach ( $galleries as $gallery ) {
			$list[] = array(
				'id'   => $gallery->ID,
				'name' => $gallery->post_title
			);
		}
		$settings = array(
			'galleries' => json_encode( $list )
		);
		$html     = "<script type='text/javascript'>\n";
		$html .= 'var ' . $this->labels->post_type . ' = ' . json_encode( $settings ) . '';
		$html .= "\n</script>";
		echo $html;
	}

	/**
	 * Registers and enqueues the styles for the plugin
	 *
	 * @since  2.2
	 * @access public
	 */
	public function admin_styles() {
		global $post;
		if ( ( isset( $post->post_type ) && $post->post_type == $this->labels->post_type )
			 || ( isset( $_GET['page'] ) && $_GET['page'] == $this->labels->post_type . '-settings' )
		) {

			wp_register_style( $this->labels->post_type . '-admin', plugins_url( 'includes/core/assets/css/admin.css', $this->labels->plugin_file ), array(), $this->labels->plugin_version );
			wp_enqueue_style( $this->labels->post_type . '-admin' );
		}
	}

	/**
	 * Registers and enqueues the scripts for the plugin
	 *
	 * @since  2.2
	 * @access public
	 */
	public function admin_scripts() {
		global $post;
		if ( ( isset( $post->post_type ) && $post->post_type == $this->labels->post_type )
			 || ( isset( $_GET['page'] ) && $_GET['page'] == $this->labels->post_type . '-settings' )
		) {

			wp_register_script( $this->labels->post_type . '-core-admin', plugins_url( 'includes/core/assets/js/admin.js', $this->labels->plugin_file ), array( 'jquery' ), $this->labels->plugin_version );
			wp_enqueue_script( $this->labels->post_type . '-core-admin' );

			wp_register_script(
				$this->labels->post_type . '-image-admin', plugins_url( 'includes/core/assets/js/image-admin.js', $this->labels->plugin_file ), array(
					'jquery',
					$this->labels->post_type . '-core-admin'
				), $this->labels->plugin_version
			);
			wp_enqueue_script( $this->labels->post_type . '-image-admin' );

			$settings = array(
				'post_id' => ( isset( $post->ID ) ) ? $post->ID : '',
				'labels'  => $this->labels,
				'nonce'   => wp_create_nonce( $this->labels->post_type ),
			);
			$settings = apply_filters( $this->labels->post_type . '_script_settings', $settings );
			wp_localize_script( $this->labels->post_type . '-core-admin', 'dev7plugin', $settings );

			do_action( $this->labels->post_type . '_admin_scripts' );
		}
	}
}