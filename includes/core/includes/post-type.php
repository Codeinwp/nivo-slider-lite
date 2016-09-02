<?php
/**
 * Post Type Class
 *
 * @package     Plugin Core
 * @subpackage  Post Type
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Post Type Core Class
 *
 * @since 1.0
 */
class Dev7_Core_Post_Type extends Dev7_Core {

	/**
	 * "construct" for the Dev7 core Post Type class
	 */
	protected function core_init() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Registers the post type for the plugin
	 *
	 * @since  2.2
	 * @access public
	 */
	function register_post_type() {
		$labels = array(
			'name'               => '%2$s',
			'singular_name'      => '%1$s',
			'add_new'            => __( 'Add New', 'dev7-core' ),
			'add_new_item'       => __( 'Add New %1$s', 'dev7-core' ),
			'edit_item'          => __( 'Edit %1$s', 'dev7-core' ),
			'new_item'           => __( 'New %1$s', 'dev7-core' ),
			'all_items'          => __( 'All %2$s', 'dev7-core' ),
			'view_item'          => __( 'View %1$s', 'dev7-core' ),
			'search_items'       => __( 'Search %2$s', 'dev7-core' ),
			'not_found'          => __( 'No %2$s found', 'dev7-core' ),
			'not_found_in_trash' => __( 'No %2$s found in Trash', 'dev7-core' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( '%2$s', 'dev7-core' )
		);

		foreach ( $labels as $key => $value ) {
			$labels[$key] = sprintf( $value, $this->get_label_singular(), $this->get_label_plural() );
		}

		$labels = apply_filters( $this->labels->post_type . '_post_type_labels', $labels );

		$supports = array( 'title', 'thumbnail' );
		$supports = array_merge( $supports, apply_filters( 'dev7_post_type_supports', array() ) );

		$post_type_args = array(
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'menu_position' => apply_filters( $this->labels->post_type . '_post_type_menu_position', 100 ),
			'supports'      => $supports,
			'menu_icon'     => apply_filters( $this->labels->post_type . '_post_type_menu_icon', '' )
		);
		register_post_type( $this->labels->post_type, apply_filters( $this->labels->post_type . '_post_type_args', $post_type_args ) );
	}

	/**
	 * Sets the messages for the post type
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $messages
	 *
	 * @return array $messages
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$message_data = array(
			0  => '',
			1  => __( '%1$s updated.', 'dev7-core' ),
			2  => __( 'Custom field updated.', 'dev7-core' ),
			3  => __( 'Custom field deleted.', 'dev7-core' ),
			4  => __( '%1$s updated.', 'dev7-core' ),
			5  => '',
			6  => __( '%1$s published.', 'dev7-core' ),
			7  => __( '%1$s saved.', 'dev7-core' ),
			8  => __( '%1$s submitted.', 'dev7-core' ),
			9  => __( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%2$s">Preview %1$s</a>', 'dev7-core' ),
			10 => __( '%1$s draft updated.', 'dev7-core' )
		);

		foreach ( $message_data as $key => $value ) {
			if ( $key == 9 ) {
				$value = sprintf( $value, $this->get_label_singular(), date_i18n( __( 'M j, Y @ G:i', 'dev7-core' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) );
			} else {
				if ( $key == 5 ) {
					if ( isset( $_GET['revision'] ) ) {
						$value = sprintf( __( '%1$s restored to revision from %2$s', 'dev7-core' ), $this->get_label_singular(), wp_post_revision_title( (int) $_GET['revision'], false ) );
					}
				} else {
					$value = sprintf( $value, $this->get_label_singular() );
				}
			}
			$message_data[$key] = $value;
		}
		$messages[$this->labels->post_type] = apply_filters( $this->labels->post_type . '_post_type_messages', $message_data );

		return $messages;
	}

	/**
	 * Gets the default labels for plugin post type
	 *
	 * @since  2.2
	 * @access private
	 * @return array
	 */
	private function get_default_labels() {
		$defaults = array(
			'singular' => __( 'Gallery', 'edd' ),
			'plural'   => __( 'Galleries', 'edd' )
		);

		return ( $this->labels ) ? (array) $this->labels : $defaults;
	}

	/**
	 * Get Singular Label
	 *
	 * @since  2.2
	 * @access private
	 *
	 * @param bool $lowercase
	 *
	 * @return string $defaults['singular'] Singular label
	 */
	private function get_label_singular( $lowercase = false ) {
		$defaults = $this->get_default_labels();

		return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
	}

	/**
	 * Get Plural Label
	 *
	 * @since  2.2
	 * @access private
	 *
	 * @param bool $lowercase
	 *
	 * @return string $defaults['plural'] Plural label
	 */
	private function get_label_plural( $lowercase = false ) {
		$defaults = $this->get_default_labels();

		return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
	}
}
