<?php
/**
 * Shortcode Class
 *
 * @package     Plugin Core
 * @subpackage  Shortcode
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode Core Class
 *
 * @since 1.0
 */
class Dev7_Core_Shortcode extends Dev7_Core {

	/**
	 * Shortcode core scripts
	 *
	 * @var array
	 * @access private
	 * @since  2.2
	 */
	private $core_scripts;

	/**
	 * Shortcode scripts
	 *
	 * @var array
	 * @access private
	 * @since  2.2
	 */
	private $scripts;

	/**
	 * Shortcode core styles
	 *
	 * @var array
	 * @access private
	 * @since  2.2
	 */
	private $core_styles;

	/**
	 * Shortcode styles
	 *
	 * @var array
	 * @access private
	 * @since  2.2
	 */
	private $styles;

	/**
	 * Instance of Dev7 Images Core Class
	 *
	 * @var object
	 * @access private
	 * @since  2.2
	 */
	private $core_images;

	/**
	 * Whether or not core styles and scripts have been enqueued
	 *
	 * @var boolean
	 * @access private
	 * @since 2.4.3
	 */
	private $core_enqueued = false;

	/**
	 * "construct" for the Dev7 core Images class
	 */
	protected function core_init() {
		$this->core_images = new Dev7_Core_Images( $this->labels, $this->is_lite );

		$this->core_scripts  = apply_filters( $this->labels->post_type . '_shortcode_core_scripts', array() );
		$this->core_styles  = apply_filters( $this->labels->post_type . '_shortcode_core_styles', array() );

		$this->scripts = apply_filters( $this->labels->post_type . '_shortcode_scripts', array() );
		$this->styles  = apply_filters( $this->labels->post_type . '_shortcode_styles', array() );

		add_shortcode( $this->labels->shortcode, array( $this, 'shortcode' ) );

		// Enqueues core styles and scripts only when a shortcode is present.
		add_filter( 'the_posts', array( $this, 'shortcode_enqueue_core' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
	}

	/**
	 * Shortcode output for the plugin
	 *
	 * @since  2.2
	 *
	 * @param array $atts
	 *
	 * @access public
	 * @return string $output
	 */
	public function shortcode( $atts ) {
		extract(
			shortcode_atts(
				array(
					'id'        => 0,

					// Here for backwards compatibility, has the same function as force below.
					'template'  => false,

					// To force the enqueue of core scripts, pass [... force="true"].
					'force'     => false,
					'slug'      => ''
				), $atts
			)
		);

		if ( ! $id && ! $slug ) {
			return sprintf( __( 'Invalid %1$s', 'dev7core' ), $this->labels->singular );
		}

		if ( ! $id ) {
			$object = get_page_by_path( $slug, OBJECT, $this->labels->post_type );
			if ( $object ) {
				$id = $object->ID;
			} else {
				return sprintf( __( 'Invalid %1$s Slug', 'dev7core' ), $this->labels->singular );
			}
		}

		$output  = '';
		$options = get_post_meta( $id, $this->labels->post_meta_key, true );
		$type    = $options[$this->labels->source_name];

		$defaults    = $this->core_images->image_sources_defaults();
		$slider_type = ( ! array_key_exists( $type, $defaults ) ) ? 'external' : '';

		$images = $this->core_images->get_images( $id );

		// Force enqueue of core scripts (good for do_shortcode() in templates).
		if ( $template || $force ) {
			$this->enqueue_core_scripts( true );
			$this->enqueue_core_styles( true );
		}

		$this->enqueue_scripts( $options );
		$this->enqueue_styles( $options );

		// generate unique prefix for $id to avoid clashes with multiple same shortcode use
		$prefix = wp_generate_password( 5, false );
		$id     = $prefix . '-' . $id;

		if ( $images ) {
			$output = apply_filters( $this->labels->post_type . '_shortcode_output', $id, $output, $options, $images, $slider_type );
		}

		return $output;
	}

	/**
	 * Tests each post for a specific shortcode and enqueues core styles and scripts.
	 *
	 * Because we can't always trust `global $post` on `is_home()` or `is_archive()`
	 * we use this method with the `the_posts` filter to test each post being shown
	 * for the shortcode and enqueue the core styles. This saves us from having
	 * to enqueue the core scripts when they are un-necessary.
	 *
	 * Any list of posts that does not use the shortcode will result in the core
	 * styles not being enqueued.
	 *
	 * @since                  2.4.3
	 *
	 * @param  array  $posts   Posts sent from `the_posts` filter.
	 *
	 * @access public
	 * @return object $posts   Does not modify Posts.
	 */
	public function shortcode_enqueue_core( $posts ) {
		foreach ( $posts as $post ) {
			if ( ! $this->core_enqueued && dev7_has_shortcode_wrap( $post->post_content, $this->labels->shortcode ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_core_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_core_styles' ) );

				// Tell this instance that it's been enqueued so we don't do this for every post.
				$this->core_enqueued = true;
			}
		}
		return $posts;
	}

	/**
	 * Enqueues core shortcode scripts in <head>
	 *
	 * @param bool $force
	 *
	 * @since  2.2
	 * @access public
	 */
	public function enqueue_core_scripts( $force = false ) {
		if ( $this->core_scripts && count( $this->core_scripts ) > 0 ) {
			$required = array( 'jquery' );

			// Enqueue our required scripts.
			wp_enqueue_script( $required );

			// Enqueue all the scripts.
			foreach ( $this->core_scripts as $name => $url ) {
				wp_enqueue_script( $name, $url, $required, $this->labels->plugin_version );
				$required[] = $name;
			}
		}
	}

	/**
	 * Enqueues core shortcode styles in <head>
	 *
	 * @param bool $force
	 *
	 * @since  2.2
	 * @access public
	 */
	public function enqueue_core_styles( $force = false ) {
		if ( $this->core_styles && count( $this->core_styles ) > 0 ) {
			foreach ( $this->core_styles as $name => $url ) {
				wp_enqueue_style( $name, $url, array(), $this->labels->plugin_version );
			}
		}
	}

	/**
	 * Enqueue shortcode scripts that are dependant on plugin options in <footer>
	 *
	 * @since  2.2
	 *
	 * @param array $options
	 *
	 * @access private
	 */
	private function enqueue_scripts( $options ) {
		$scripts = apply_filters( $this->labels->post_type . '_shortcode_scripts_enqueue', $this->scripts, $options );
		if ( $scripts && count( $scripts ) > 0 ) {
			foreach ( $scripts as $name => $url ) {
				wp_enqueue_script( $name );
			}
		}
	}

	/**
	 * Enqueue shortcode styles
	 *
	 * @since  2.2
	 *
	 * @param array $options
	 *
	 * @access private
	 */
	private function enqueue_styles( $options ) {
		$styles = apply_filters( $this->labels->post_type . '_shortcode_styles_enqueue', $this->styles, $options );
		if ( $styles && count( $styles ) > 0 ) {
			foreach ( $styles as $name => $url ) {
				wp_enqueue_style( $name );
			}
		}
	}

	/**
	 * Register shortcode scripts
	 *
	 * @since  2.2
	 * @access public
	 */
	public function register_scripts() {
		if ( $this->scripts && count( $this->scripts ) > 0 ) {
			$required = array( 'jquery' );
			foreach ( $this->scripts as $name => $url ) {
				wp_register_script( $name, $url, $required, $this->labels->plugin_version );
				$required[] = $name;
			}
		}
	}

	/**
	 * Register shortcode styles
	 *
	 * @since  2.2
	 * @access public
	 */
	public function register_styles() {
		if ( $this->styles && count( $this->styles ) > 0 ) {
			foreach ( $this->styles as $name => $url ) {
				wp_register_style( $name, $url, array(), $this->labels->plugin_version );
			}
		}
	}
}
