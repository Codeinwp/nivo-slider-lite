<?php
/**
 * Admin AJAX Class
 *
 * @package     Plugin Core
 * @subpackage  Admin/AJAX
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX Core Class
 *
 * @since 2.2
 */
class Dev7_Core_Admin_AJAX extends Dev7_Core {

	/**
	 * Instance of Dev7 Images Core Class
	 *
	 * @var object
	 * @access private
	 * @since  2.2
	 */
	private $core_images;

	/**
	 * Plugin options
	 *
	 * @var array
	 * @access private
	 * @since  2.2
	 */
	private $options;

	/**
	 * "construct" setting up ajax action hooks
	 */
	protected function core_init() {
		$this->options     = get_option( $this->labels->options_key );
		$this->core_images = new Dev7_Core_Images( $this->labels, $this->is_lite );

		add_action( 'wp_ajax_' . $this->labels->post_type . '_set_meta_link', array( $this, 'set_meta_link' ) );
		add_action( 'wp_ajax_' . $this->labels->post_type . '_get_meta_link', array( $this, 'get_meta_link' ) );
		add_action( 'wp_ajax_' . $this->labels->post_type . '_activate_license', array( $this, 'activate_license' ) );
		add_action( 'wp_ajax_' . $this->labels->post_type . '_deactivate_license', array( $this, 'deactivate_license' ) );
		add_action( 'wp_ajax_' . $this->labels->post_type . '_load_images', array( $this, 'load_images' ) );
		add_action( 'wp_ajax_' . $this->labels->post_type . '_reattach_images', array( $this, 'reattach_images' ) );
	}

	/**
	 * Sends to the media modal the link for an image
	 *
	 * @since  2.2
	 * @access public
	 * @return string
	 */
	public function get_meta_link() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( $this->labels->post_type ) ) ) {
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
					$links[$attachment] = $link;
				}
			}
		}
		$response = array( 'metalink' => $links );
		echo json_encode( $response );
		die;
	}

	/**
	 * Saves the link for an image when entered in the media modal
	 *
	 * @since  2.2
	 * @access public
	 * @return string
	 */
	public function set_meta_link() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( $this->labels->post_type ) ) ) {
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
	 * Activates the plugin license with the Dev7studios EDD store
	 *
	 * @since  2.2
	 * @access public
	 * @return string
	 */
	public function activate_license() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( $this->labels->post_type ) ) ) {
			return 0;
		}

		if ( ! isset( $_POST['license_key'] ) ) {
			return 0;
		}

		$ajax_response['error']   = false;
		$ajax_response['message'] = '';

		$options                = $this->options;
		$license                = ( dev7_license_constant( $this->labels->post_type ) ) ? dev7_get_license_key( $this->labels->post_type, $options ) : trim( $_POST['license_key'] );
		$options['license_key'] = $license;

		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->labels->dev7_item_name )
		);
		$response   = wp_remote_get(
			esc_url_raw( add_query_arg( $api_params, DEV7_STORE_URL ) ), array(
				'timeout'   => 15,
				'sslverify' => false
			)
		);
		if ( is_wp_error( $response ) ) {
			$ajax_response['error']   = true;
			$ajax_response['message'] = $response->get_error_message();
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( isset( $license_data->license ) ) {
				$options['license_status']       = $license_data->license;
				$ajax_response['license_status'] = $license_data->license;
				$ajax_response['redirect']       = admin_url( 'edit.php?post_type=' . $this->labels->post_type . '&page=' . $this->labels->post_type . '-settings' );
			}
		}
		update_option( $this->labels->options_key, $options );
		echo json_encode( $ajax_response );
		die;
	}

	/**
	 * Deactivates the plugin license with the Dev7studios EDD store
	 *
	 * @since  2.2
	 * @access public
	 * @return string
	 */
	public function deactivate_license() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( $this->labels->post_type ) ) ) {
			return 0;
		}

		$ajax_response['error']   = false;
		$ajax_response['message'] = '';

		$options = $this->options;
		$license = trim( dev7_default_val( $options, 'license_key', '' ) );

		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( $this->labels->dev7_item_name )
		);
		$response   = wp_remote_get(
			esc_url_raw( add_query_arg( $api_params, DEV7_STORE_URL ) ), array(
				'timeout'   => 15,
				'sslverify' => false
			)
		);
		if ( is_wp_error( $response ) ) {
			$ajax_response['error']   = true;
			$ajax_response['message'] = $response->get_error_message();
		} else {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( isset( $license_data->license ) ) {
				unset( $options['license_key'] );
				unset( $options['license_status'] );
				update_option( $this->labels->options_key, $options );
				$ajax_response['license_status'] = 'deactivated';
				$ajax_response['redirect']       = admin_url( 'edit.php?post_type=' . $this->labels->post_type . '&page=' . $this->labels->post_type . '-settings' );
			}
		}
		echo json_encode( $ajax_response );
		die;
	}

	/**
	 * Retrieves images associated with the [gallery]
	 *
	 * @since  2.2
	 * @access public
	 * @return string
	 */
	function load_images() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], $this->labels->post_type ) || ! isset( $_POST['source'] ) ) {
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
	 * Reattaches images to a manual gallery when the manual image ids are not set
	 *
	 * @since  2.2
	 * @access public
	 * @return string
	 */
	public function reattach_images() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], plugin_basename( $this->labels->post_type ) ) ) {
			return 0;
		}

		if ( ! isset( $_POST['post_id'] ) ) {
			return 0;
		}

		$ajax_response['error']   = false;
		$ajax_response['message'] = '';

		$options =  get_post_meta( $_POST['post_id'], $this->labels->post_meta_key, true );;

		$images = dev7_get_attached_images( $_POST['post_id'], -1 );

		if ( ! $images || count( $images ) == 0 ) {
			$ajax_response['error']   = true;
			$ajax_response['message'] = 'No images to attach';
		} else {
			$ids = wp_list_pluck( $images, 'ID' );
			$manual_image_ids = implode( ',', $ids );
			$options['manual_image_ids']     = $manual_image_ids;
			$ajax_response['redirect']       = admin_url( 'edit.php?post_type=' . $this->labels->post_type );
			update_post_meta( $_POST['post_id'], $this->labels->post_meta_key, $options );
		}

		echo json_encode( $ajax_response );
		die;
	}
}