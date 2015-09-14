<?php
/**
 * Functions
 *
 * @package     Plugin Core
 * @subpackage  Core
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default value helper function
 *
 * @since 2.2
 *
 * @param        $options
 * @param        $value
 * @param string $default
 *
 * @return string
 */
if ( ! function_exists( 'dev7_default_val' ) ) {
	function dev7_default_val( $options, $value, $default = '' ) {
		if ( ! isset( $options[$value] ) ) {
			return $default;
		} else {
			return $options[$value];
		}
	}
}

/**
 * Checks if a plugin is installed and active
 *
 * @since 2.2
 *
 * @param $plugin
 *
 * @return bool
 */
if ( ! function_exists( 'dev7_plugin_is_active' ) ) {
	function dev7_plugin_is_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
	}
}

/**
 * Checks if Media Manager Plus is installed and active
 *
 * @since 2.2
 * @return bool
 */
if ( ! function_exists( 'dev7_mmp_active' ) ) {
	function dev7_mmp_active() {
		$mmp_path = 'uber-media/uber-media.php';

		return dev7_plugin_is_active( $mmp_path );
	}
}

/**
 * Checks if a license for the plugin has been set as a constant
 *
 * @since 2.2
 * @return bool
 */
if ( ! function_exists( 'dev7_license_constant' ) ) {
	function dev7_license_constant( $post_type ) {
		return apply_filters( $post_type . '_check_license_constant', false );
	}
}

/**
 * Returns the license key for the plugin
 *
 * @since 2.2
 *
 * @param string $post_type
 * @param array  $options
 *
 * @return string $license
 */
if ( ! function_exists( 'dev7_get_license_key' ) ) {
	function dev7_get_license_key( $post_type, $options ) {
		if ( dev7_license_constant( $post_type ) ) {
			$license = apply_filters( $post_type . '_get_license_constant', '' );
		} else {
			$license = dev7_default_val( $options, 'license_key', '' );
		}

		return trim( $license );
	}
}

/**
 * Checks to see if content has the plugin shortcode
 *
 * @since 2.2
 *
 * @param string $content
 * @param array  $shortcode
 *
 * @return bool $found
 */
if ( ! function_exists( 'dev7_has_shortcode_wrap' ) ) {
	function dev7_has_shortcode_wrap( $content, $shortcode = '' ) {

		if ( function_exists( 'has_shortcode' ) ) {
			return has_shortcode( $content, $shortcode );
		}

		$found = false;

		if ( ! $shortcode ) {
			return $found;
		}
		if ( stripos( $content, '[' . $shortcode ) !== false ) {
			$found = true;
		}

		return $found;
	}
}

/**
 * Returns the images attached to a [gallery] post
 *
 * @since 2.2
 *
 * @param string $post_id
 * @param array  $limit
 *
 * @return array
 */
if ( ! function_exists( 'dev7_get_attached_images' ) ) {
	function dev7_get_attached_images( $post_id, $limit ) {
		$args = array(
			'orderby'        => 'menu_order ID',
			'order'          => 'ASC',
			'post_type'      => 'attachment',
			'post_parent'    => $post_id,
			'post_mime_type' => 'image',
			'post_status'    => null,
			'numberposts'    => $limit
		);

		return get_posts( $args );
	}
}

/**
 * Gets the [gallery] images attached as a gallery or just attached the to the post
 *
 * @since 2.2
 *
 * @param string $post
 * @param array  $limit
 *
 * @return array
 */
if ( ! function_exists( 'dev7_grab_attachment_ids_from_gallery' ) ) {
	function dev7_grab_attachment_ids_from_gallery( $post, $limit ) {
		if ( ! $post = get_post( $post ) ) {
			return array();
		}

		if ( ! dev7_has_shortcode_wrap( $post->post_content, 'gallery' ) ) {
			return dev7_get_attached_images( $post->ID, $limit );
		}

		$count       = 0;
		$attachments = array();
		if ( preg_match_all( '/' . get_shortcode_regex() . '/s', $post->post_content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $shortcode ) {
				if ( 'gallery' === $shortcode[2] ) {
					$atts = shortcode_parse_atts( $shortcode[3] );
					if ( isset( $atts['ids'] ) ) {
						$attachment_ids = explode( ',', $atts['ids'] );
					}
					foreach ( $attachment_ids as $att_id ) {
						$count ++;
						if ( $limit != - 1 && $count > $limit ) {
							break;
						}
						$attachment     = new stdClass();
						$attachment->ID = $att_id;
						$attachments[]  = $attachment;
					}
				}
			}
		}

		return $attachments;
	}
}
