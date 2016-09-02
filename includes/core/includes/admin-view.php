<?php
/**
 * Admin View Class
 *
 * @package     Plugin Core
 * @subpackage  Admin/View
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin View Core Class
 *
 * @since 2.2
 */
class Dev7_Core_Admin_View extends Dev7_Core {

	/**
	 * Instance of Dev7 Images Core Class
	 *
	 * @var object
	 * @access private
	 * @since  2.2
	 */
	private $core_images;

	/**
	 * "construct" for the [gallery] list page
	 */
	protected function core_init() {
		$this->core_images = new Dev7_Core_Images( $this->labels, $this->is_lite );

		add_action( 'manage_edit-' . $this->labels->post_type . '_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_' . $this->labels->post_type . '_posts_custom_column', array( $this, 'custom_columns' ) );
	}

	/**
	 * Adds the custom columns to the post type admin index screen
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $columns
	 *
	 * @return array $columns
	 */
	public function edit_columns( $columns ) {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title', 'dev7core' ),
			'shortcode' => __( 'Shortcode', 'dev7core' ),
			'type'      => __( 'Type', 'dev7core' ),
			'images'    => __( 'Images', 'dev7core' ),
			'author'    => __( 'Author', 'dev7core' ),
			'date'      => __( 'Date', 'dev7core' )
		);

		return apply_filters( 'dev7_edit_columns', $columns );
	}

	/**
	 * Populates the data in the custom columns
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param $column
	 */
	public function custom_columns( $column ) {
		global $post;

		if ( $post->post_type != $this->labels->post_type ) {
			return;
		}
		$options = get_post_meta( $post->ID, $this->labels->post_meta_key, true );

		do_action( $this->labels->post_type . '_custom_column_switch', $post, $column, $options );

		$type  = dev7_default_val( $options, $this->labels->source_name, $this->core_images->image_source_default() );
		$types = $this->core_images->get_image_sources();

		switch ( $column ) {
			case 'images':
				$limit = 5;
				if ( isset( $_GET['mode'] ) && $_GET['mode'] == 'excerpt' ) {
					$limit = 20;
				}
				$images = $this->core_images->get_images( $post->ID, 'thumbnail', $limit );
				if ( $images ) {
					echo '<ul class="dev7plugin-thumbs">';
					foreach ( $images as $image ) {
						echo '<li><img src="' . $image['image_src'] . '" alt="' . $image['alt_text'] . '" style="width:32px;height:32px;" /></li>';
					}
					echo '</ul>';
				} else {
					if ( ( ! isset( $types[$type] ) || $type == $this->labels->manual_name ) && ( ! isset( $options['manual_image_ids'] ) || $options['manual_image_ids'] == '' ) ) {
						$images = dev7_get_attached_images( $post->ID, 1 );
						if ( $images ) {
							echo '<a class="reattach-images" data-post="'. $post->ID .'" href="#">Reattach Images</a><div class="reattach-spinner spinner"></div>';
						} else {
							echo 'No images attached';
						}
					}
				}
				break;
			case 'shortcode':
				echo '<code>[' . $this->labels->shortcode . ' id="' . $post->ID . '"]</code>';
				if ( $post->post_name != '' ) {
					echo '<br /><code>[' . $this->labels->shortcode . ' slug="' . $post->post_name . '"]</code>';
				}
				break;
			case 'type':

				echo isset( $types[$type] ) ? $types[$type] : 'Manual';
				switch ( $type ) {
					case 'gallery':
						$gallery_post = get_the_title( dev7_default_val( $options, $this->labels->type_name . '_gallery' ) );
						echo '<br><em>' . $gallery_post . '</em>';
						break;
					case 'category':
						$category = get_category( dev7_default_val( $options, $this->labels->type_name . '_category' ) );
						echo '<br><em>' . $category->name . '</em>';
						break;
					case 'custom':
						$post_type = get_post_type_object( dev7_default_val( $options, $this->labels->type_name . '_custom' ) );
						echo '<br><em>' . ( ( isset( $post_type->labels->name ) ) ? $post_type->labels->name : ucfirst( $post_type ) ) . '</em>';
						break;
				}
				break;
		}
	}
}