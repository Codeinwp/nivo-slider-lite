<?php
/**
 * The post-type functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 */

/**
 * The post-type functionality of the plugin.
 *
 * TODO WRITE DESCRIPTION
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Core_Model extends Nivo_Model_Abstract {

	/**
	 * Nivo_Core_Model constructor.
	 * @since   3.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Setting message for post type update.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   string  $messages   The message for post type update.
	 * @return mixed
	 */
	public function post_updated_messages( $messages ) {
		$defaults = $this->labels;
		global $post, $post_ID;

		$message_data = array(
			0  => '',
			1  => __( '%1$s updated.', 'nivo-slider' ),
			2  => __( 'Custom field updated.', 'nivo-slider' ),
			3  => __( 'Custom field deleted.', 'nivo-slider' ),
			4  => __( '%1$s updated.', 'nivo-slider' ),
			5  => '',
			6  => __( '%1$s published.', 'nivo-slider' ),
			7  => __( '%1$s saved.', 'nivo-slider' ),
			8  => __( '%1$s submitted.', 'nivo-slider' ),
			9  => __( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%2$s">Preview %1$s</a>', 'nivo-slider' ),
			10 => __( '%1$s draft updated.', 'nivo-slider' ),
		);

		foreach ( $message_data as $key => $value ) {
			if ( $key == 9 ) {
				$value = sprintf( $value, $defaults['singular'], date_i18n( __( 'M j, Y @ G:i', 'nivo-slider' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) );
			} else {
				if ( $key == 5 ) {
					if ( isset( $_GET['revision'] ) ) {
						$value = sprintf( __( '%1$s restored to revision from %2$s', 'nivo-slider' ), $defaults['singular'], wp_post_revision_title( (int) $_GET['revision'], false ) );
					}
				} else {
					$value = sprintf( $value, $defaults['singular'] );
				}
			}
			$message_data[ $key ] = $value;
		}
		$messages[ $this->labels['post_type'] ] = apply_filters( $this->labels['post_type'] . '_post_type_messages', $message_data );

		return $messages;
	}
}
