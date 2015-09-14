<?php
/**
 * Widget Class
 *
 * @package     Nivo_Slider
 * @subpackage  Widget
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Widget
 *
 * Displays the widget for the slider/gallery
 *
 * @since 2.4
 * @return void
 */
class Dev7_Nivo_Slider_Widget extends WP_Widget {
	/** Constructor */
	function __construct() {
		parent::__construct(
			false,
			__( 'Nivo Slider', 'dev7core' ),
			array( 'description' => __( 'Display a Nivo Slider', 'dev7core' ) )
		);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$id    = $instance['nivo_slider_id'];
		global $post;

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		if ( $id ) {
			echo do_shortcode( '[nivoslider  id="' . $id . '" template="1"]' );
		}

		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['nivo_slider_id'] = isset( $new_instance['nivo_slider_id'] ) ? strip_tags( $new_instance['nivo_slider_id'] ) : '';

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'edd' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php
		$args      = array(
			'post_type'      => 'nivoslider',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);
		$galleries = get_posts( $args );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'nivo_slider_id' ); ?>">Nivo Slider</label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'nivo_slider_id' ); ?>" id="<?php echo $this->get_field_id( 'nivo_slider_id' ); ?>">
				<option value=""><?php _e( 'Select Slider', 'dev7core' ); ?></option>
				<?php foreach ( $galleries as $gallery ) {
					$selected = ( isset( $instance['nivo_slider_id'] ) ) ? $instance['nivo_slider_id'] : ''; ?>
					<option <?php selected( $selected, $gallery->ID ); ?> value="<?php echo $gallery->ID; ?>"><?php echo ( $gallery->post_title ) ? $gallery->post_title : $gallery->ID; ?></option>
				<?php } ?>
			</select>
		</p>
	<?php
	}
}

/**
 * Register Widgets
 *
 * Registers the Widgets.
 *
 * @since 2.4
 * @return void
 */
function nivo_slider_register_widgets() {
	register_widget( 'dev7_nivo_slider_widget' );
}

add_action( 'widgets_init', 'nivo_slider_register_widgets' );