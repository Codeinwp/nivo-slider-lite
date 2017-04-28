<?php
/**
 * The images functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 */

/**
 * The images functionality of the plugin.
 *
 * TODO WRITE DESCRIPTION
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Core_Images extends Nivo_Core_Abstract implements Nivo_Library_Interface {

	/**
	 * Resize images dynamically using wp built in functions
	 *
	 * @since   2.2.*
	 * @access  public
	 *
	 * @param   integer $attach_id
	 * @param   string  $img_url
	 * @param   integer $width
	 * @param   integer $height
	 * @param   boolean $crop
	 *
	 * @return array
	 */
	public static function resize_image( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {
		if ( $attach_id ) { // this is an attachment, so we have the ID
			$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
			$file_path = get_attached_file( $attach_id );
		} elseif ( $img_url ) { // this is not an attachment, let's use the image url
			$file_path = parse_url( $img_url );
			$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];
			if ( ! file_exists( $file_path ) ) {
				return new WP_Error( 'broke', __( 'File doesn\'t  exist', 'nivo-slider' ) . ':' . $file_path );
			}
			$orig_size    = getimagesize( $file_path );
			$image_src[0] = $img_url;
			$image_src[1] = $orig_size[0];
			$image_src[2] = $orig_size[1];
		}
		$file_info = pathinfo( $file_path );
		$extension = '.' . $file_info['extension'];
		// the image path without the extension
		$no_ext_path      = $file_info['dirname'] . '/' . $file_info['filename'];
		$cropped_img_path = $no_ext_path . '-' . $width . 'x' . $height . strtolower( $extension );
		// checking if the file size is larger than the target size
		// if it is smaller or the same size, stop right here and return
		if ( $image_src[1] > $width || $image_src[2] > $height ) {
			if ( file_exists( $cropped_img_path ) ) { // the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
				$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
				$vt_image        = array(
					'url'    => $cropped_img_url,
					'width'  => $width,
					'height' => $height,
				);

				return $vt_image;
			}
			if ( $crop == false ) {
				// calculate the size proportionaly
				$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
				$resized_img_path  = $no_ext_path . '-' . $proportional_size[0] . 'x' . $proportional_size[1] . $extension;
				if ( file_exists( $resized_img_path ) ) {  // checking if the file already exists
					$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );
					$vt_image        = array(
						'url'    => $resized_img_url,
						'width'  => $proportional_size[0],
						'height' => $proportional_size[1],
					);

					return $vt_image;
				}
			}
			// no cache files - let's finally resize it
			$editor = wp_get_image_editor( $file_path );
			if ( is_wp_error( $editor ) ) {
				return $editor;
			}
			$editor->set_quality( 90 );
			$resized = $editor->resize( $width, $height, $crop );
			if ( is_wp_error( $resized ) ) {
				return $resized;
			}
			$new_img_path = $editor->generate_filename( $width . 'x' . $height, null );
			$saved        = $editor->save( $new_img_path );
			if ( is_wp_error( $saved ) ) {
				return $saved;
			}
			$new_img_size = getimagesize( $new_img_path );
			$new_img      = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );
			// resized output
			$vt_image = array(
				'url'    => $new_img,
				'width'  => $new_img_size[0],
				'height' => $new_img_size[1],
			);

			return $vt_image;
		}
		// default output - without resizing
		$vt_image = array(
			'url'    => $image_src[0],
			'width'  => $image_src[1],
			'height' => $image_src[2],
		);

		return $vt_image;
	}

	/**
	 * Use this instead of overriding __construct
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function after_core_construct() {
	}

	/**
	 * Returns the images sizes for the site
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return array
	 */
	public function get_image_sizes() {
		global $_wp_additional_image_sizes;
		$image_sizes = array( 'thumbnail', 'medium', 'large', 'full' ); // Standard sizes
		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes = array_merge( $image_sizes, array_keys( $_wp_additional_image_sizes ) );
		}
		$image_sizes = apply_filters( 'intermediate_image_sizes', $image_sizes );
		$all_sizes   = array();
		foreach ( $image_sizes as $size ) {
			$all_sizes[ $size ] = ucfirst( $size );
		}

		return $all_sizes;
	}

	/**
	 * Returns the images for a [gallery]
	 *
	 * @since   2.2.*
	 * @access  public
	 *
	 * @param   $post_id
	 * @param   $size
	 * @param   $limit
	 * @param   $source
	 * @param   $method
	 * @param   $param
	 * @param   $gallery
	 * @param   $category
	 * @param   $custom
	 * @param   $image_ids
	 *
	 * @return array
	 */
	public function get_images( $post_id, $size = '', $limit = null, $source = null, $method = null, $param = null, $gallery = null, $category = null, $custom = null, $image_ids = null ) {
		$options = get_post_meta( $post_id, $this->labels['post_meta_key'], true );
		if ( ! $options ) {
			$options = array();
		}
		if ( $size == '' ) {
			$size = $this->nivo_default_val( $options, 'wp_image_size', 'full' );
		}
		$images  = array();
		$sources = $this->get_image_sources();
		if ( ! isset( $options[ $this->labels['source_name'] ] ) || ! array_key_exists( $options[ $this->labels['source_name'] ], $sources ) ) {
			$image_source = $this->image_source_default();
		} else {
			$image_source = $this->nivo_default_val( $options, $this->labels['source_name'], $this->image_source_default() );
		}
		if ( $source ) {
			$image_source = $source;
		}
		if ( $limit ) {
			$limit = $limit;
		} else {
			$limit = ( isset( $options['number_images'] ) && $options['number_images'] != '' ) ? $options['number_images'] : - 1;
		}
		if ( $image_source == $this->labels['manual_name'] ) {
			if ( $image_ids ) {
				$attach_str                  = $image_ids;
				$options['manual_image_ids'] = $attach_str;
				update_post_meta( $post_id, $this->labels['post_meta_key'], $options );
			} else {
				$attach_str = $this->nivo_default_val( $options, 'manual_image_ids', '' );
			}
			$attachments = explode( ',', $attach_str );
			$count       = 0;
			if ( $attachments ) {
				foreach ( $attachments as $attachment_id ) {
					if ( $attachment_id == '' ) {
						continue;
					}
					$count ++;
					$attachment = get_post( $attachment_id );
					if ( $limit != '-1' && $count > $limit ) {
						break;
					}
					$image     = wp_get_attachment_image_src( $attachment->ID, $size );
					$thumbnail = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );
					$url       = $this->prepare_attachment_link( get_post_meta( $attachment->ID, '_wp_attachment_image_link', true ) );
					$images[]  = array(
						'image_src'      => $image[0],
						'post_permalink' => $url,
						'post_title'     => wptexturize( $attachment->post_excerpt ),
						'alt_text'       => trim( strip_tags( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) ) ),
						'attachment_id'  => $attachment->ID,
						'thumbnail'      => $thumbnail[0],
					);
				}
			}
		} elseif ( $image_source == 'gallery' ) {
			$gallery     = ( $gallery ) ? $gallery : $options[ $this->labels['type_name'] . '_gallery' ];
			$attachments = $this->nivo_grab_attachment_ids_from_gallery( $gallery, $limit );
			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {
					$image     = wp_get_attachment_image_src( $attachment->ID, $size );
					$meta      = wp_get_attachment_metadata( $attachment->ID );
					$caption   = get_post_field( 'post_excerpt', $attachment->ID );
					$link      = get_attachment_link( $attachment->ID );
					$thumbnail = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );
					$images[]  = array(
						'image_src'      => $image[0],
						'post_permalink' => $link,
						'post_title'     => $caption,
						'alt_text'       => $caption,
						'attachment_id'  => $attachment->ID,
						'thumbnail'      => $thumbnail[0],
					);
				}
			}
		} elseif ( $image_source == 'category' ) {
			$category = ( $category ) ? $category : $options[ $this->labels['type_name'] . '_category' ];
			$args     = array(
				'post_type'   => 'post',
				'numberposts' => $limit,
				'category'    => $category,
			);
			$posts    = get_posts( $args );
			if ( $posts ) {
				foreach ( $posts as $post ) {
					if ( has_post_thumbnail( $post->ID ) ) {
						$attachment_id = get_post_thumbnail_id( $post->ID );
						$image         = wp_get_attachment_image_src( $attachment_id, $size );
						$thumbnail     = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
						$title         = get_the_title( $post->ID );
						$link          = ( get_post_meta( $attachment_id, '_wp_attachment_image_link', true ) ? $this->prepare_attachment_link( get_post_meta( $attachment_id, '_wp_attachment_image_link', true ) ) : get_permalink( $post->ID ) );
						$images[]      = array(
							'image_src'      => $image[0],
							'post_permalink' => $link,
							'post_title'     => $title,
							'alt_text'       => $title,
							'attachment_id'  => $attachment_id,
							'thumbnail'      => $thumbnail[0],
						);
					}
				}
			}
		} elseif ( $image_source == 'sticky' ) {
			$sticky = get_option( 'sticky_posts' );
			$args   = array(
				'post_type'   => 'post',
				'numberposts' => $limit,
				'post__in'    => $sticky,
			);
			$posts  = get_posts( $args );
			if ( $posts ) {
				foreach ( $posts as $post ) {
					if ( has_post_thumbnail( $post->ID ) ) {
						$attachment_id = get_post_thumbnail_id( $post->ID );
						$image         = wp_get_attachment_image_src( $attachment_id, $size );
						$thumbnail     = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
						$link          = ( get_post_meta( $attachment_id, '_wp_attachment_image_link', true ) ? $this->prepare_attachment_link( get_post_meta( $attachment_id, '_wp_attachment_image_link', true ) ) : get_permalink( $post->ID ) );
						$title         = get_the_title( $post->ID );
						$images[]      = array(
							'image_src'      => $image[0],
							'post_permalink' => $link,
							'post_title'     => $title,
							'alt_text'       => $title,
							'attachment_id'  => $attachment_id,
							'thumbnail'      => $thumbnail[0],
						);
					}
				}
			}
		} else {
			if ( $image_source == 'custom' ) {
				$custom = ( $custom ) ? $custom : $options[ $this->labels['type_name'] . '_custom' ];
				$args   = array(
					'post_type'   => $custom,
					'numberposts' => $limit,
				);
				$posts  = get_posts( $args );
				if ( $posts ) {
					foreach ( $posts as $post ) {
						if ( has_post_thumbnail( $post->ID ) ) {
							$attachment_id = get_post_thumbnail_id( $post->ID );
							$image         = wp_get_attachment_image_src( $attachment_id, $size );
							$thumbnail     = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
							$title         = get_the_title( $post->ID );
							$link          = ( get_post_meta( $attachment_id, '_wp_attachment_image_link', true ) ? $this->prepare_attachment_link( get_post_meta( $attachment_id, '_wp_attachment_image_link', true ) ) : get_permalink( $post->ID ) );
							$images[]      = array(
								'image_src'      => $image[0],
								'post_permalink' => $link,
								'post_title'     => $title,
								'alt_text'       => $title,
								'attachment_id'  => $attachment_id,
								'thumbnail'      => $thumbnail[0],
							);
						}
					}
				}
			} else {
				if ( ! $this->nivo_mmp_active() ) {
					return $images;
				}
				$mmp_options = get_option( 'ubermediasettings_settings', array() );
				$mmp_sources = $this->nivo_default_val( $mmp_options, 'ubermediasettings_sources_available', array() );
				if ( isset( $mmp_sources[ $image_source . '-settings' ] ) ) {
					$source_settings = $mmp_sources[ $image_source . '-settings' ];
					$access_token    = $source_settings['access-token'];
					$var             = 'media_manager_plus_source_' . $image_source;
					$obj             = new $var( $access_token['oauth_token'], $access_token['oauth_token_secret'] );
					$method          = ( $method ) ? $method : ( ( isset( $options[ $image_source . '_type' ] ) ) ? $options[ $image_source . '_type' ] : '' );
					if ( $method == '' ) {
						return $images;
					}
					$params = array();
					if ( $param ) {
						$params[] = $param;
					} else {
						if ( isset( $options[ $image_source . '_' . $method ] ) ) {
							$params[] = $options[ $image_source . '_' . $method ];
						}
					}
					$count = ( $limit == '-1' ) ? '20' : $limit;
					if ( $count != '' ) {
						$params['count'] = $count;
					}
					$params['page']     = 1;
					$safemode           = $this->nivo_default_val( $mmp_options, 'ubermediasettings_general_safe-mode', 1 );
					$params['safemode'] = $safemode;
					if ( $size != 'thumbnail' ) {
						$size = 'full';
					}
					$source_images = call_user_func_array( array( $obj, $method ), $params );
					if ( $source_images && isset( $source_images['images'] ) ) {
						foreach ( $source_images['images'] as $mmp_image ) {
							$images[] = array(
								'image_src'      => $mmp_image[ $size ],
								'post_permalink' => $mmp_image['link'],
								'post_title'     => $mmp_image['caption'],
								'alt_text'       => $mmp_image['caption'],
								'thumbnail'      => $mmp_image['thumbnail'],
							);

						}
					}
				}
			}
		}

		return $images;
	}

	/**
	 * Returns the images sources for the plugin available, including those from Media Manager Plus
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return array
	 */
	function get_image_sources() {
		$sources = $this->image_sources_defaults();
		$exclude = apply_filters( 'mmp_feed_exclude', array() );
		if ( $this->nivo_mmp_active() ) {
			$mmp_options = get_option( 'ubermediasettings_settings', array() );
			$mmp_sources = $this->nivo_default_val( $mmp_options, 'ubermediasettings_sources_available', array() );
			if ( $mmp_sources ) {
				foreach ( $mmp_sources as $mmp_source => $mmp_settings ) {
					$mmp_source_name = str_replace( '-settings', '', $mmp_source );
					if ( ! class_exists( 'media_manager_plus_source_' . $mmp_source_name ) ) {
						continue;
					}
					if ( array_key_exists( $mmp_source_name, $exclude ) ) {
						continue;
					}
					$sources[ $mmp_source_name ] = ucfirst( $mmp_source_name ) . ' Feed';
				}
			}
		}

		return apply_filters( $this->labels['post_type'] . '_get_image_sources', $sources );
	}

	/**
	 * Returns the default images sources for the plugin available
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return array
	 */
	public function image_sources_defaults() {
		$defaults               = array(
			$this->labels['manual_name'] => __( 'Manual', 'nivo-slider' ),
		);
		$defaults['disabled-1'] = __( 'Gallery', 'nivo-slider' );
		$defaults['disabled-2'] = __( 'Category', 'nivo-slider' );
		$defaults['disabled-3'] = __( 'Sticky Posts', 'nivo-slider' );
		$defaults['disabled-4'] = __( 'Custom Post Type', 'nivo-slider' );

		return apply_filters( $this->labels['post_type'] . '_image_sources_defaults', $defaults );
	}

	/**
	 * Returns the default selected image source for the plugin
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return string
	 */
	public function image_source_default() {
		return apply_filters( $this->labels['post_type'] . '_image_source_default', $this->labels['manual_name'] );
	}

	/**
	 * Prepares the attached image url
	 *
	 * @since   2.2.*
	 * @access  private
	 *
	 * @param   string $url The URL.
	 *
	 * @return string
	 */
	private function prepare_attachment_link( $url ) {
		$url = trim( strip_tags( $url ) );
		if ( false === strpos( $url, '://' ) && $url != '' ) {
			$url = 'http://' . $url;
		}

		return $url;
	}
}
