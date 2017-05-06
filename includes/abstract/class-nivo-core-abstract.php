<?php
/**
 * The Core Abstract Class for Nivo.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/abstract
 */

/**
 * The abstract core functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/abstract
 * @author     ThemeIsle <friends@themeisle.com>
 */
abstract class Nivo_Core_Abstract extends Nivo_Core_Settings_Abstract {

	/**
	 * @since   3.0.0
	 * @access  protected
	 * @var     $labels     The labels array defined in $plugin_settings.
	 */
	protected $labels;

	/**
	 * @since   3.0.0
	 * @access  protected
	 * @var     $is_lite   The lite flag defined in $plugin_settings.
	 */
	protected $is_lite;

	/**
	 * Main construct
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function __construct() {
		$plugin_settings = self::get_plugin_settings();
		$labels          = $plugin_settings->get_labels();
		if ( isset( $plugin_settings ) && ! empty( $labels ) ) {
			$this->labels  = $plugin_settings->get_labels();
			$this->is_lite = $plugin_settings->get_lite();
		}
		$this->after_core_construct();
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
	 * Checks if Media Manager Plus is installed and active
	 *
	 * @since   2.2.*
	 * @access  public
	 * @return boolean
	 */
	public function nivo_mmp_active() {
		$mmp_path = 'uber-media/uber-media.php';

		return $this->nivo_plugin_is_active( $mmp_path );
	}

	/**
	 * Checks if a plugin is installed and active
	 *
	 * @since   2.2.*
	 * @access  public
	 *
	 * @param   array $plugin The plugins array.
	 *
	 * @return boolean
	 */
	public function nivo_plugin_is_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
	}


	/**
	 * Default value helper function
	 *
	 * @since   2.2.*
	 * @access  public
	 *
	 * @param   array $options The options array.
	 * @param   mixed $value The value of options.
	 * @param   string $default The default.
	 *
	 * @return string
	 */
	public function nivo_default_val( $options, $value, $default = '' ) {
		if ( ! isset( $options[ $value ] ) ) {
			return $default;
		} else {
			return $options[ $value ];
		}
	}

	/**
	 * Gets the [gallery] images attached as a gallery or just attached the to the post
	 *
	 * @since   2.2.*
	 * @access  public
	 *
	 * @param   string $post The post ID.
	 * @param   integer $limit The limit param.
	 *
	 * @return array
	 */
	public function nivo_grab_attachment_ids_from_gallery( $post, $limit ) {
		if ( ! $post = get_post( $post ) ) {
			return array();
		}

		if ( ! $this->nivo_has_shortcode_wrap( $post->post_content, 'gallery' ) ) {
			return $this->nivo_get_attached_images( $post->ID, $limit );
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

	/**
	 * Checks to see if content has the plugin shortcode
	 *
	 * @since   2.2.*
	 * @access  public
	 *
	 * @param   string $content The content to check.
	 * @param   string $shortcode The shortcode to look for.
	 *
	 * @return boolean
	 */
	public function nivo_has_shortcode_wrap( $content, $shortcode = '' ) {

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

	/**
	 * Returns the images attached to a [gallery] post
	 *
	 * @since   2.2.*
	 * @access  public
	 *
	 * @param   string $post_id The post ID.
	 * @param   integer $limit The limit param.
	 *
	 * @return array
	 */
	public function nivo_get_attached_images( $post_id, $limit ) {
		$args = array(
			'orderby'        => 'menu_order ID',
			'order'          => 'ASC',
			'post_type'      => 'attachment',
			'post_parent'    => $post_id,
			'post_mime_type' => 'image',
			'post_status'    => null,
			'numberposts'    => $limit,
		);

		return get_posts( $args );
	}

	/**
	 * Get Nivo themes and any custom themes from uploads/nivo-themes/
	 *
	 * @since   2.2.*
	 * @access  protected
	 *
	 * @param   bool $select A flag to toggle select.
	 *
	 * @return array
	 */
	protected function get_themes( $select = false ) {
		$nivo_theme_specs = array(
			'SkinName'       => 'Skin Name',
			'SkinURI'        => 'Skin URI',
			'Description'    => 'Description',
			'Version'        => 'Version',
			'Author'         => 'Author',
			'AuthorURI'      => 'Author URI',
			'SupportsThumbs' => 'Supports Thumbs',
		);

		$plugin_themes = glob( NIVO_SLIDER_PLUGIN_DIR . '/assets/themes/*', GLOB_ONLYDIR );

		$upload_dir  = wp_upload_dir();
		$upload_path = $upload_dir['basedir'];
		$upload_url  = $upload_dir['baseurl'];

		if ( strpos( $upload_path, 'uploads/sites/' ) !== false && is_multisite() ) {
			$upload_path = substr( $upload_path, 0, strpos( $upload_path, '/sites/' ) );
			$upload_url  = substr( $upload_url, 0, strpos( $upload_url, '/sites/' ) );
		}
		$custom_themes = glob( $upload_path . '/nivo-themes/*', GLOB_ONLYDIR );

		if ( ! is_array( $plugin_themes ) ) {
			$plugin_themes = array();
		}
		if ( ! is_array( $custom_themes ) ) {
			$custom_themes = array();
		}
		$nivo_themes = array_merge( $plugin_themes, $custom_themes );

		$themes = array();
		if ( $nivo_themes ) {
			foreach ( $nivo_themes as $theme_dir ) {
				$theme_name = basename( $theme_dir );
				$theme_path = $theme_dir . '/' . $theme_name . '.css';
				if ( file_exists( $theme_path ) ) {
					if ( strpos( $theme_dir, 'uploads/nivo-themes' ) !== false ) {
						$theme_url = $upload_url . '/nivo-themes/' . $theme_name . '/' . $theme_name . '.css';
					} else {
						$theme_url = plugins_url( 'assets/themes/' . $theme_name . '/' . $theme_name . '.css', NIVO_SLIDER_PLUGIN_FILE );
					}
					$themes[ $theme_name ] = array(
						'theme_name'    => $theme_name,
						'theme_path'    => $theme_path,
						'theme_url'     => $theme_url,
						'theme_details' => get_file_data( $theme_path, $nivo_theme_specs ),
					);
				}
			}
		}

		if ( $select ) {
			$options = array();
			foreach ( $themes as $theme ) {
				$options[ $theme['theme_name'] ] = $theme['theme_details']['SkinName'];
			}

			return $options;
		}

		return $themes;
	}
}
