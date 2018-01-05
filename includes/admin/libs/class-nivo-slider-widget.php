<?php
/**
 * The widget functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 */

/**
 * The widget functionality of the plugin.
 *
 * TODO WRITE DESCRIPTION
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Slider_Widget extends WP_Widget {

	/**
	 * Nivo_Slider_Widget constructor.
	 * @since   3.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct(
			false,
			__( 'Nivo Slider', 'nivo-slider' ),
			array(
				'description' => __( 'Display a Nivo Slider', 'nivo-slider' ),
			)
		);
	}

	/**
	 * Register widget utility method.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function register_widget() {
		register_widget( 'nivo_slider_widget' );
	}

	/**
	 * Method to render widget.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   array   $args       The widget arguments.
	 * @param   object  $instance   The widget instance.
	 * @return mixed
	 */
	public function widget( $args, $instance ) {
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

	/**
	 * Utility method to update widget.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   object  $new_instance   The widget instance.
	 * @param   object  $old_instance   The widget instance.
	 * @return mixed
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['nivo_slider_id'] = isset( $new_instance['nivo_slider_id'] ) ? strip_tags( $new_instance['nivo_slider_id'] ) : '';

		return $instance;
	}

	/**
	 * Method to render widget form.
	 *
	 * @since   3.0.0
	 * @acces   public
	 * @param   object  $instance   The widget instance.
	 * @return mixed
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'nivo-slider' ); ?></label>
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
				<option value=""><?php _e( 'Select Slider', 'nivo-slider' ); ?></option>
				<?php
				foreach ( $galleries as $gallery ) {
					$selected = ( isset( $instance['nivo_slider_id'] ) ) ? $instance['nivo_slider_id'] : '';
					?>
					<option <?php selected( $selected, $gallery->ID ); ?> value="<?php echo $gallery->ID; ?>"><?php echo ( $gallery->post_title ) ? $gallery->post_title : $gallery->ID; ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
	}
}
