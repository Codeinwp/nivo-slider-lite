<?php
/**
 * The admin-ajax functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 */

/**
 * The admin-ajax functionality of the plugin.
 *
 * TODO WRITE DESCRIPTION
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Core_Admin_Ajax extends Nivo_Core_Abstract implements Nivo_Library_Interface {

	/**
	 * Instance of Nivo Images Core Class
	 *
	 * @var     object
	 * @access  private
	 * @since   2.2.*
	 */
	private $core_images;

	/**
	 * Plugin options
	 *
	 * @var     array
	 * @access  private
	 * @since   2.2.*
	 */
	private $options;

	/**
	 * Use this instead of overriding __construct
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function after_core_construct() {
		$this->options     = get_option( $this->labels['options_key'] );
		$this->core_images = new Nivo_Core_Images( self::get_plugin_settings() );
	}

	/**
	 * Sends to the media modal the link for an image
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return string
	 */
	public function get_meta_link() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( $this->labels['post_type'] ) ) ) {
			return 0;
		}
		if ( ! isset( $_POST['ids'] ) ) {
			return 0;
		}
		$attachments = explode( ',', $_POST['ids'] );
		$links = array();
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$link = get_post_meta( $attachment, '_wp_attachment_image_link', true );
				if ( $link != '' ) {
					$links[ $attachment ] = $link;
				}
			}
		}
		$response = array(
			'metalink' => $links,
		);
		echo json_encode( $response );
		die;
	}

	/**
	 * Saves the link for an image when entered in the media modal
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return string
	 */
	public function set_meta_link() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( $this->labels['post_type'] ) ) ) {
			return 0;
		}
		if ( ! isset( $_POST['id'] ) || ! isset( $_POST['metalink'] ) ) {
			return 0;
		}
		$link     = get_post_meta( $_POST['id'], '_wp_attachment_image_link', true );
		$new_link = stripslashes( $_POST['metalink'] );
		if ( $link != $new_link ) {
			$new_link = wp_strip_all_tags( $new_link, true );
			$result   = update_post_meta( $_POST['id'], '_wp_attachment_image_link', addslashes( $new_link ) );
		}
		$response = array();
		echo json_encode( $response );
		die;
	}

	/**
	 * Retrieves images associated with the [gallery]
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return string
	 */
	public function load_images() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], $this->labels['post_type'] ) || ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$response['images']  = array();
		$number_images = ( isset( $_POST['number_images'] ) && $_POST['number_images'] != '' ) ? $_POST['number_images'] : - 1;
		$method    = ( ( isset( $_POST['method'] ) && $_POST['method'] != '' ) ) ? $_POST['method'] : null;
		$param     = ( ( isset( $_POST['param'] ) && $_POST['param'] != '' ) ) ? $_POST['param'] : null;
		$gallery   = ( ( isset( $_POST['gallery'] ) && $_POST['gallery'] != '' ) ) ? $_POST['gallery'] : null;
		$category  = ( ( isset( $_POST['category'] ) && $_POST['category'] != '' ) ) ? $_POST['category'] : null;
		$custom    = ( ( isset( $_POST['custom'] ) && $_POST['custom'] != '' ) ) ? $_POST['custom'] : null;
		$image_ids = ( ( isset( $_POST['image_ids'] ) && $_POST['image_ids'] != '' ) ) ? $_POST['image_ids'] : null;
		$response['images'] = $this->core_images->get_images(
			$_POST['id'],
			'thumbnail',
			$number_images,
			$_POST['source'],
			$method,
			$param,
			$gallery,
			$category,
			$custom,
			$image_ids
		);
		echo json_encode( $response );
		die;
	}

	/**
	 * Ajax endpoint for url tracking.
	 */
	public function track_url() {
		check_admin_referer( 'nivo-track', 'nonce' );
		$status = $_GET['status'];
		if ( $status == 'yes' ) {
			update_option( 'nivo_logger_flag', 'yes' );
		} else {
			update_option( 'nivo_logger_flag', 'no' );
		}
		wp_send_json_success(
			array(
				'status' => $status,
			)
		);
	}

	/**
	 * Reattaches images to a manual gallery when the manual image ids are not set
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return string
	 */
	public function reattach_images() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( $this->labels['post_type'] ) ) ) {
			return 0;
		}
		if ( ! isset( $_POST['post_id'] ) ) {
			return 0;
		}
		$ajax_response['error']   = false;
		$ajax_response['message'] = '';
		$options = get_post_meta( $_POST['post_id'], $this->labels['post_meta_key'], true );
		;
		$images = $this->nivo_get_attached_images( $_POST['post_id'], - 1 );
		if ( ! $images || count( $images ) == 0 ) {
			$ajax_response['error']   = true;
			$ajax_response['message'] = 'No images to attach';
		} else {
			$ids                         = wp_list_pluck( $images, 'ID' );
			$manual_image_ids            = implode( ',', $ids );
			$options['manual_image_ids'] = $manual_image_ids;
			$ajax_response['redirect']   = admin_url( 'edit.php?post_type=' . $this->labels['post_type'] );
			update_post_meta( $_POST['post_id'], $this->labels['post_meta_key'], $options );
		}
		echo json_encode( $ajax_response );
		die;
	}
}
