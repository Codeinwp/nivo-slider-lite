<?php
/**
 * The shortcode functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 */

/**
 * The shortcode functionality of the plugin.
 *
 * TODO WRITE DESCRIPTION
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Core_Shortcode extends Nivo_Core_Abstract implements Nivo_Library_Interface {

	/**
	 * Shortcode core scripts
	 *
	 * @var     array
	 * @access  private
	 * @since   2.2.*
	 */
	private $core_scripts = array();

	/**
	 * Shortcode scripts
	 *
	 * @var     array
	 * @access  private
	 * @since   2.2.*
	 */
	private $scripts = array();

	/**
	 * Shortcode core styles
	 *
	 * @var     array
	 * @access  private
	 * @since   2.2.*
	 */
	private $core_styles = array();

	/**
	 * Shortcode styles
	 *
	 * @var     array
	 * @access  private
	 * @since   2.2.*
	 */
	private $styles = array();

	/**
	 * Instance of Dev7 Images Core Class
	 *
	 * @var     object
	 * @access  private
	 * @since   2.2.*
	 */
	private $core_images;

	/**
	 * Whether or not core styles and scripts have been enqueued
	 *
	 * @var     boolean
	 * @access  private
	 * @since   2.4.3
	 */
	private $core_enqueued = false;

	/**
	 * Use this instead of overriding __construct
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function after_core_construct() {
		$this->core_images = new Nivo_Core_Images( self::get_plugin_settings() );

		$this->core_scripts  = $this->shortcode_core_scripts( $this->core_scripts );
		$this->core_styles  = $this->shortcode_core_styles( $this->core_styles );

		$this->scripts = $this->shortcode_scripts( $this->scripts );
		$this->styles  = $this->shortcode_styles( $this->styles );
	}

	/**
	 * Shortcode output for the plugin
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   array   $atts The attributes array.
	 * @return string $output
	 */
	public function shortcode( $atts ) {
		$sc = shortcode_atts(
			array(
				'id'        => 0,
				// Here for backwards compatibility, has the same function as force below.
				'template'  => false,
				// To force the enqueue of core scripts, pass [... force="true"].
				'force'     => false,
				'slug'      => '',
			), $atts
		);

		if ( ! $sc['id'] && ! $sc['slug'] ) {
			return sprintf( __( 'Invalid %1$s', 'nivo-slider' ), $this->labels['singular'] );
		}

		if ( ! $sc['id'] ) {
			$object = get_page_by_path( $sc['slug'], OBJECT, $this->labels['post_type'] );
			if ( $object ) {
				$sc['id'] = $object->ID;
			} else {
				return sprintf( __( 'Invalid %1$s Slug', 'nivo-slider' ), $this->labels['singular'] );
			}
		}

		$output  = '';
		$options = get_post_meta( $sc['id'], $this->labels['post_meta_key'], true );
		if ( isset( $options[ $this->labels['source_name'] ] ) ) {
			$type = $options[ $this->labels['source_name'] ];
		} else {
			$type = $options[ $this->labels['type_name'] ]; // backwards compatibility for < v3.0.0
		}

		$defaults    = $this->core_images->image_sources_defaults();
		$slider_type = ( ! array_key_exists( $type, $defaults ) ) ? 'external' : '';

		$images = $this->core_images->get_images( $sc['id'] );

		$this->enqueue_core_scripts( true );
		$this->enqueue_core_styles( true );

		$this->enqueue_scripts( $options );
		$this->enqueue_styles( $options );

		// generate unique prefix for $id to avoid clashes with multiple same shortcode use
		$prefix = wp_generate_password( 5, false );
		$sc['id']     = $prefix . '-' . $sc['id'];

		if ( $images ) {
			$output = apply_filters( $this->labels['post_type'] . '_shortcode_output', $sc['id'], $output, $options, $images, $slider_type );
		}

		return $output;
	}

	/**
	 * Tests each post for a specific shortcode and enqueues core styles and scripts.
	 *
	 * Because we can't always trust `global $post` on `is_home()` or `is_archive()`
	 * we use this method with the `the_posts` filter to test each post being shown
	 * for the shortcode and enqueue the core styles. This saves us from having
	 * to enqueue the core scripts when they are un-necessary.
	 *
	 * Any list of posts that does not use the shortcode will result in the core
	 * styles not being enqueued.
	 *
	 * @since   2.4.3
	 * @access  public
	 * @param   array   $posts  Posts sent from `the_posts` filter.
	 * @return object
	 */
	public function shortcode_enqueue_core( $posts ) {
		foreach ( $posts as $post ) {
			if ( ! $this->core_enqueued && $this->nivo_has_shortcode_wrap( $post->post_content, $this->labels['shortcode'] ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_core_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_core_styles' ) );

				// Tell this instance that it's been enqueued so we don't do this for every post.
				$this->core_enqueued = true;
			}
		}
		return $posts;
	}

	/**
	 * Enqueues core shortcode scripts in <head>
	 *
	 * @since   2.2.*
	 * @access  public
	 */
	public function enqueue_core_scripts() {
		if ( $this->core_scripts && count( $this->core_scripts ) > 0 ) {
			$required = array( 'jquery' );

			// Enqueue our required scripts.
			wp_enqueue_script( $required );

			// Enqueue all the scripts.
			foreach ( $this->core_scripts as $name => $url ) {
				wp_enqueue_script( $name, $url, $required, $this->labels['plugin_version'] );
				$required[] = $name;
			}
		}
	}

	/**
	 * Enqueues core shortcode styles in <head>
	 *
	 * @since   2.2.*
	 * @access  public
	 */
	public function enqueue_core_styles( $force = false ) {
		if ( $this->core_styles && count( $this->core_styles ) > 0 ) {
			foreach ( $this->core_styles as $name => $url ) {
				wp_enqueue_style( $name, $url, array(), $this->labels['plugin_version'] );
			}
		}
	}

	/**
	 * Enqueue shortcode scripts that are dependant on plugin options in <footer>
	 *
	 * @since   2.2.*
	 * @access  private
	 * @param   array   $options
	 */
	private function enqueue_scripts( $options ) {
		$scripts = apply_filters( $this->labels['post_type'] . '_shortcode_scripts_enqueue', $this->scripts, $options );
		if ( $scripts && count( $scripts ) > 0 ) {
			foreach ( $scripts as $name => $url ) {
				wp_enqueue_script( $name );
			}
		}
	}

	/**
	 * Enqueue shortcode styles
	 *
	 * @since   2.2.*
	 * @access  private
	 * @param   array   $options    The options array.
	 */
	private function enqueue_styles( $options ) {
		$styles = apply_filters( $this->labels['post_type'] . '_shortcode_styles_enqueue', $this->styles, $options );
		if ( $styles && count( $styles ) > 0 ) {
			foreach ( $styles as $name => $url ) {
				wp_enqueue_style( $name );
			}
		}
	}

	/**
	 * Register shortcode scripts
	 *
	 * @since   2.2.*
	 * @access  public
	 */
	public function register_scripts() {
		$this->scripts = apply_filters( 'nivo_external_shortcode_scripts', $this->scripts );
		if ( $this->scripts && count( $this->scripts ) > 0 ) {
			$required = array( 'jquery' );
			foreach ( $this->scripts as $name => $url ) {
				wp_register_script( $name, $url, $required, $this->labels['plugin_version'] );
				$required[] = $name;
			}
		}
	}

	/**
	 * Register shortcode styles
	 *
	 * @since   2.2.*
	 * @access  public
	 */
	public function register_styles() {
		$this->styles = apply_filters( 'nivo_external_shortcode_styles', $this->styles );
		if ( $this->styles && count( $this->styles ) > 0 ) {
			foreach ( $this->styles as $name => $url ) {
				wp_register_style( $name, $url, array(), $this->labels['plugin_version'] );
			}
		}
	}

	/**
	 * Adds custom styles to Shortcode style array for loading to <head>
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   array   $scripts    The scripts array.
	 * @return array
	 */
	public function shortcode_core_scripts( $scripts ) {
		return $scripts;
	}

	/**
	 * Adds custom styles to Shortcode style array for loading to <head>
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   array $styles The styles array.
	 * @return array
	 */
	public function shortcode_core_styles( $styles ) {
		$styles['nivo-ns-nivoslider'] = NIVO_SLIDER_PLUGIN_URL . 'assets/css/nivo-slider.css';
		return $styles;
	}

	/**
	 * Adds custom styles to Shortcode style array for conditional loading
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   array   $styles The styles array.
	 * @return array
	 */
	public function shortcode_styles( $styles ) {
		$themes = $this->get_themes();
		foreach ( $themes as $theme ) {
			$styles[ 'nivoslider-theme-' . $theme['theme_name'] ] = $theme['theme_url'];
		}
		return $styles;
	}

	/**
	 * Adds custom scripts to Shortcode script array for conditional loading
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   array   $scripts    The scripts array.
	 * @return array
	 */
	public function shortcode_scripts( $scripts ) {
		$scripts['nivo-ns-nivoslider'] = NIVO_SLIDER_PLUGIN_URL . 'assets/js/jquery.nivo.slider.pack.js';
		return $scripts;
	}

	/**
	 * Custom shortcode enqueuing of scripts for selected Nivo theme
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   array   $styles     The styles array.
	 * @param   array   $options    The short code options.
	 * @return array
	 */
	public function shortcode_styles_enqueue( $styles, $options ) {
		$slider_theme = 'nivoslider-theme-' . $this->nivo_default_val( $options, 'theme' );
		if ( $styles ) {
			foreach ( $styles as $name => $url ) {
				if ( substr( $name, 0, 17 ) == 'nivoslider-theme-' ) {
					if ( $name != $slider_theme ) {
						unset( $styles[ $name ] );
					}
				}
			}
		}

		return $styles;
	}

	/**
	 * Custom shortcode plugin output
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   int    $id          Short code ID.
	 * @param   string $output      The output string.
	 * @param   array  $options     The short code options array.
	 * @param   array  $images      The images array.
	 * @param   string $slider_type The slider type.
	 * @return string
	 */
	public function shortcode_output( $id, $output, $options, $images, $slider_type ) {
		$module = false;
		list( $prefix, $post_id ) = explode( '-', $id );
		if ( get_post_type( $post_id ) == $this->labels['post_type'] ) {
			$taxonomy = wp_get_post_terms(
				$post_id, 'nivo_slider', array(
					'fields' => 'names',
				)
			);
			if ( isset( $taxonomy[0] ) && in_array( $taxonomy[0], array( 'carousel' ) ) ) {
				$module = true;
				$module_name = $taxonomy[0];
			}
		}
		if ( $module && isset( $module_name ) ) {
			$output = apply_filters( $this->labels['post_type'] . '_' . $module_name, $id, $output, $options, $images, $slider_type );
		} else {
			if ( isset( $options[ $this->labels['source_name'] ] ) ) {
				$type = $options[ $this->labels['source_name'] ];
			} else {
				$type = $options[ $this->labels['type_name'] ]; // backwards compatibility for < v3.0.0
			}

			$captions = array();
			$output .= '<div class="slider-wrapper';
			if ( isset( $options['theme'] ) && $options['theme'] != '' ) {
				$output .= ' theme-' . $options['theme'];
			}
			if ( isset( $options['controlNavThumbs'] ) && $options['controlNavThumbs'] == 'on' ) {
				$output .= ' controlnav-thumbs';
			}
			$output .= '"><div class="ribbon"></div>';
			$output .= '<div id="nivoslider-' . $id . '" class="nivoSlider"';
			if ( isset( $options['sizing'] ) && $options['sizing'] == 'fixed' ) {
				$output .= ' style="width:' . $options['dim_x'] . 'px;height:' . $options['dim_y'] . 'px;"';
			}
			$output .= '>';
			$i = 0;
			foreach ( $images as $image ) {

				$image_link   = $this->nivo_default_val( $options, 'imageLink', 'on' );
				$target_blank = $this->nivo_default_val( $options, 'targetBlank', 'on' );
				if ( ( isset( $image['post_permalink'] ) && $image['post_permalink'] != '' ) && $image_link == 'on' ) {
					$target = ( $target_blank == 'on' ) ? ' target="_blank"' : '';
					$output .= '<a ' . $target . ' href="' . $image['post_permalink'] . '">';
				}

				if ( isset( $options['sizing'] ) && $options['sizing'] == 'fixed' && isset( $image['attachment_id'] ) ) {
					$resized_image = $this->core_images->resize_image( $image['attachment_id'], '', $options['dim_x'], $options['dim_y'], true );
					if ( is_wp_error( $resized_image ) ) {
						echo '<p>Error: ' . $resized_image->get_error_message() . '</p>';
						$output .= '<img src="" ';
					} else {
						$output .= '<img src="' . $resized_image['url'] . '" ';
					}
				} else {
					$output .= '<img src="' . $image['image_src'] . '" ';
				}

				if ( ( $type == 'manual' || $type == 'gallery' ) && isset( $image['post_title'] ) && $image['post_title'] != '' ) {
					$captions[] = $image['post_title'];
					$output .= 'title="#nivoslider-' . $id . '-caption-' . $i . '" ';
					$i ++;
				}
				if ( ( $type == 'category' || $type == 'sticky' || $type == 'custom' ) && $options['enable_captions'] == 'on' && isset( $image['post_title'] ) && $image['post_title'] != '' ) {
					$captions[] = $image['post_title'];
					$output .= 'title="#nivoslider-' . $id . '-caption-' . $i . '" ';
					$i ++;
				}

				if ( isset( $options['controlNavThumbs'] ) && $options['controlNavThumbs'] == 'on' ) {
					if ( isset( $image['thumbnail'] ) ) {
						$output .= 'data-thumb="' . $image['thumbnail'] . '" ';
					} else {
						$resized_image = $this->core_images->resize_image( $image['attachment_id'], '', $options['thumbSizeWidth'], $options['thumbSizeHeight'], true );
						if ( is_wp_error( $resized_image ) ) {
							echo '<p>Error: ' . $resized_image->get_error_message() . '</p>';
							$output .= 'data-thumb="" ';
						} else {
							$output .= 'data-thumb="' . $resized_image['url'] . '" ';
						}
					}
				}

				$output .= 'alt="' . $image['alt_text'] . '" />';
				if ( isset( $image['post_permalink'] ) && $image['post_permalink'] != '' ) {
					$output .= '</a>';
				}
			}
			$output .= '</div></div>';

			if ( isset( $options['controlNavThumbs'] ) && 'on' == $options['controlNavThumbs'] ) {

				// Get the height and width.
				$thumbnail_height = $options['thumbSizeHeight'];
				$thumbnail_width  = $options['thumbSizeWidth'];

				// Force the height/width of thumbnails set in slider settings.
				$output .= "<style type='text/css' media='screen'> \n";
				$output .= ".theme-default .nivo-controlNav.nivo-thumbs-enabled img, \n";
				$output .= ".nivo-thumbs-enabled img { \n";
				$output .= "	width: {$thumbnail_width}px !important; \n";
				$output .= "	height: {$thumbnail_height}px !important; \n";
				$output .= "} \n";
				$output .= "</style> \n";
			}

			$i = 0;
			foreach ( $captions as $caption ) {
				$output .= '<div id="nivoslider-' . $id . '-caption-' . $i . '" class="nivo-html-caption">';
				$output .= $caption;
				$output .= '</div>';
				$i ++;
			}

			if ( count( $images ) > 1 ) {
				$output .= '<script type="text/javascript">' . "\n";
				$output .= 'jQuery(window).load(function(){' . "\n";
				$output .= '    jQuery("#nivoslider-' . $id . '").nivoSlider({' . "\n";
				$output .= '        effect:"' . $options['effect'] . '",' . "\n";
				$output .= '        slices:' . $options['slices'] . ',' . "\n";
				$output .= '        boxCols:' . $options['boxCols'] . ',' . "\n";
				$output .= '        boxRows:' . $options['boxRows'] . ',' . "\n";
				$output .= '        animSpeed:' . $options['animSpeed'] . ',' . "\n";
				$output .= '        pauseTime:' . $options['pauseTime'] . ',' . "\n";
				if ( isset( $options['randomStart'] ) && $options['randomStart'] == 'on' ) {
					$output .= '        startSlide:' . floor( rand( 0, count( $images ) ) ) . ',' . "\n";
				} else {
					$output .= '        startSlide:' . $options['startSlide'] . ',' . "\n";
				}
				$output .= '        directionNav:' . ( ( $options['directionNav'] == 'on' ) ? 'true' : 'false' ) . ',' . "\n";
				$output .= '        controlNav:' . ( ( $options['controlNav'] == 'on' ) ? 'true' : 'false' ) . ',' . "\n";
				$output .= '        controlNavThumbs:' . ( ( isset( $options['controlNavThumbs'] ) && $options['controlNavThumbs'] == 'on' ) ? 'true' : 'false' ) . ',' . "\n";
				$output .= '        pauseOnHover:' . ( ( $options['pauseOnHover'] == 'on' ) ? 'true' : 'false' ) . ',' . "\n";
				$output .= '        manualAdvance:' . ( ( $options['manualAdvance'] == 'on' ) ? 'true' : 'false' ) . "\n";
				$output .= '    });' . "\n";
				$output .= '});' . "\n";
				$output .= '</script>' . "\n";
			} else {
				$output .= '<script type="text/javascript">' . "\n";
				$output .= 'jQuery(window).load(function(){' . "\n";
				$output .= '    jQuery("#nivoslider-' . $id . ' img").css("position","relative").show();' . "\n";
				$output .= '});' . "\n";
				$output .= '</script>' . "\n";
			}
		}

		return $output;
	}
}
