<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Dev7_Core_Plugin' ) ) {
	require_once NIVO_SLIDER_LITE_PLUGIN_DIR . 'includes/core/plugin.php';
}

/**
 * Main Plugin Class
 */
class Dev7_Nivo_Slider_Lite extends Dev7_Core_Plugin {

	/**
	 * Plugin Version
	 *
	 * @var string
	 * @access private
	 * @since  2.2
	 */
	private $version;

	/**
	 * Post Type
	 *
	 * @var string
	 * @access private
	 * @since  2.2
	 */
	private $post_type;

	/**
	 * Plugin labels
	 *
	 * @var object
	 * @access private
	 * @since  2.2
	 */
	private $labels;

	/**
	 * Main construct
	 *
	 * @since 2.2
	 *
	 * @param string $version
	 */
	public function __construct( $version ) {
		$this->version   = $version;
		$this->labels    = array(
			'dev7_item_name'  => 'Nivo Slider Lite WordPress Plugin',
			'plugin_name'     => 'Nivo Slider Lite',
			'plugin_version'  => $version,
			'plugin_file'     => NIVO_SLIDER_LITE_PLUGIN_FILE,
			'plugin_basename' => NIVO_SLIDER_LITE_PLUGIN_BASENAME,
			'plugin_url'      => NIVO_SLIDER_LITE_PLUGIN_URL,
			'post_type'       => 'nivoslider',
			'shortcode'       => 'nivoslider',
			'function'        => 'nivo_slider',
			'slug'            => 'nivo-slider',
			'post_meta_key'   => 'nivo_settings',
			'options_key'     => 'nivoslider_settings',
			'source_name'     => 'type',
			'manual_name'     => 'manual',
			'type_name'       => 'type',
			'singular'        => __( 'Slider', 'nivo-slider' ),
			'plural'          => __( 'Sliders', 'nivo-slider' ),
			'documentation'   => 'http://docs.themeisle.com/'
		);
		$this->post_type = $this->labels['post_type'];

		$this->add_filters();
		$this->add_actions();

		parent::__construct( $this->labels, true );
	}

	/**
	 * Adds custom filters for the plugin
	 *
	 * @since  2.2
	 * @access private
	 */
	private function add_filters() {
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 11, 2 );
		add_filter( $this->post_type . '_post_type_labels', array( $this, 'post_type_labels' ) );
		add_filter( $this->post_type . '_post_type_menu_icon', array( $this, 'post_type_menu_icon' ) );
		add_filter( $this->post_type . '_settings_page_header', array( $this, 'settings_page_header' ) );
		add_filter( $this->post_type . '_settings_intro', array( $this, 'settings_intro' ) );
		add_filter( $this->post_type . '_admin_edit_settings', array( $this, 'admin_edit_settings' ) );
		add_filter( $this->post_type . '_post_meta_save', array( $this, 'post_meta_save' ) );
		add_filter( $this->post_type . '_script_settings', array( $this, 'script_settings' ) );
		add_filter( $this->post_type . '_shortcode_core_styles', array( $this, 'shortcode_core_styles' ) );
		add_filter( $this->post_type . '_shortcode_scripts', array( $this, 'shortcode_scripts' ) );
		add_filter( $this->post_type . '_shortcode_styles', array( $this, 'shortcode_styles' ) );
		add_filter( $this->post_type . '_shortcode_styles_enqueue', array( $this, 'shortcode_styles_enqueue' ), 10, 2 );
		add_filter( $this->post_type . '_shortcode_output', array( $this, 'shortcode_output' ), 10, 5 );
		add_filter( $this->post_type . '_get_image_sources', array( $this, 'get_image_sources' ) );
	}

	/**
	 * Adds custom actions for the plugin
	 *
	 * @since  2.2
	 * @access private
	 */
	private function add_actions() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'remove_settings_menu' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( $this->post_type . '_admin_scripts', array( $this, 'admin_scripts' ) );
		add_action( $this->post_type . '_manual_type_description', array( $this, 'manual_type_description' ) );
	}

	/**
	 * Add Upgrade metabox to the edit screen
	 */
	public function admin_init() {
		add_meta_box( $this->post_type . '_upgrade_box', __( 'Upgrade to Pro', 'nivo-slider' ), array(
			$this,
			'meta_box_upgrade',
		), $this->post_type, 'side' );
	}

	/**
	 * Remove the settings page for the Lite plugin
	 */
	public function remove_settings_menu() {
		remove_submenu_page( 'edit.php?post_type=' . $this->post_type, 'nivoslider-settings' );
	}

	/**
	 * Registers and enqueues custom admin scripts
	 *
	 * @since  2.2
	 * @access public
	 */
	public function admin_scripts() {
		wp_register_script( $this->post_type . '-lite-admin', plugins_url( 'assets/js/admin.js', dirname( __FILE__ ) ), array(
			'jquery',
			$this->post_type . '-core-admin',
		), $this->version );
		wp_enqueue_script( $this->post_type . '-lite-admin' );
	}

	/**
	 * Registers and enqueues custom admin styles
	 */
	public function admin_styles() {
		wp_register_style( $this->post_type . '-lite-admin', plugins_url( 'assets/css/admin.css', dirname( __FILE__ ) ), array(), $this->version );
		wp_enqueue_style( $this->post_type . '-lite-admin' );
	}

	/**
	 * Adds custom scripts to Shortcode script array for conditional loading
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $scripts
	 *
	 * @return array $scripts
	 */
	public function shortcode_scripts( $scripts ) {
		$scripts = array( 'dev7-ns-nivoslider' => plugins_url( 'assets/js/jquery.nivo.slider.pack.js', dirname( __FILE__ ) ) );

		return $scripts;
	}

	/**
	 * Adds custom styles to Shortcode style array for loading to <head>
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $styles
	 *
	 * @return array $styles
	 */
	public function shortcode_core_styles( $styles ) {
		return array( 'dev7-ns-nivoslider' => plugins_url( 'assets/css/nivo-slider.css', dirname( __FILE__ ) ) );
	}

	/**
	 * Adds custom styles to Shortcode style array for conditional loading
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $styles
	 *
	 * @return array $styles
	 */
	public function shortcode_styles( $styles ) {
		$styles = array();
		$themes = $this->get_themes();
		foreach ( $themes as $theme ) {
			$styles[ 'nivoslider-theme-' . $theme['theme_name'] ] = $theme['theme_url'];
		}

		return $styles;
	}

	/**
	 * Custom shortcode enqueuing of scripts for selected Nivo theme
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $styles
	 * @param array $options
	 *
	 * @return array $styles
	 */
	public function shortcode_styles_enqueue( $styles, $options ) {
		$slider_theme = 'nivoslider-theme-' . dev7_default_val( $options, 'theme' );
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
	 * Custom post type labels
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $labels
	 *
	 * @return array $labels
	 */
	public function post_type_labels( $labels ) {
		$name           = __( 'Nivo Slider', 'nivo-slider' );
		$labels['name'] = $labels['singular_name'] = $labels['menu_name'] = $name;

		return $labels;
	}

	/**
	 * Custom post type menu icon url
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param string $icon_url
	 *
	 * @return string $icon_url
	 */
	public function post_type_menu_icon( $icon_url ) {
		return NIVO_SLIDER_LITE_PLUGIN_URL . 'assets/images/favicon.png';
	}

	/**
	 * Custom settings page header
	 *
	 * @since  2.2
	 * @access public
	 * @return string
	 */
	public function settings_page_header() {
		return 'Nivo Slider Lite Settings';
	}

	/**
	 * Custom settings page intro
	 *
	 * @since  2.2
	 * @access public
	 * @return string
	 */
	public function settings_intro() {
		return '';
	}

	/**
	 * Adds custom settings to the plugin's printed javascript object
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $settings
	 *
	 * @return array $settings
	 */
	public function script_settings( $settings ) {
		$themes             = $this->get_themes();
		$settings['themes'] = $themes;

		return $settings;
	}

	/**
	 * Custom post meta settings saving logic
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $settings
	 *
	 * @return array $settings
	 */
	public function post_meta_save( $settings ) {
		if ( ! is_numeric( $settings['dim_x'] ) || $settings['dim_x'] <= 0 ) {
			$settings['dim_x'] = 400;
		}
		if ( ! is_numeric( $settings['dim_y'] ) || $settings['dim_y'] <= 0 ) {
			$settings['dim_y'] = 150;
		}
		if ( ! is_numeric( $settings['slices'] ) || $settings['slices'] <= 0 ) {
			$settings['slices'] = 15;
		}
		if ( ! is_numeric( $settings['boxCols'] ) || $settings['boxCols'] <= 0 ) {
			$settings['boxCols'] = 8;
		}
		if ( ! is_numeric( $settings['boxRows'] ) || $settings['boxRows'] <= 0 ) {
			$settings['boxRows'] = 4;
		}
		if ( ! is_numeric( $settings['animSpeed'] ) || $settings['animSpeed'] <= 0 ) {
			$settings['animSpeed'] = 500;
		}
		if ( ! is_numeric( $settings['pauseTime'] ) || $settings['pauseTime'] <= 0 ) {
			$settings['pauseTime'] = 3000;
		}
		if ( ! is_numeric( $settings['startSlide'] ) || $settings['startSlide'] < 0 ) {
			$settings['startSlide'] = 0;
		}
		if ( ! isset( $settings['thumbSizeWidth'] ) || ! is_numeric( $settings['thumbSizeWidth'] ) || $settings['thumbSizeWidth'] <= 0 ) {
			$settings['thumbSizeWidth'] = 70;
		}
		if ( ! isset( $settings['thumbSizeHeight'] ) || ! is_numeric( $settings['thumbSizeHeight'] ) || $settings['thumbSizeHeight'] <= 0 ) {
			$settings['thumbSizeHeight'] = 50;
		}

		return $settings;
	}

	/**
	 * Post edit screen settings
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param array $settings
	 *
	 * @return array $settings
	 */
	public function admin_edit_settings( $settings ) {
		$settings   = array();
		$settings[] = array(
			'name'     => 'enable_captions',
			'default'  => 'on',
			'type'     => 'checkbox',
			'title'    => __( 'Enable Captions', 'nivo-slider' ),
			'descp'    => __( 'Enable automatic captions from post titles', 'nivo-slider' ),
			'sub'      => true,
			'tr_class' => 'dev7_captions',
		);
		$settings[] = array(
			'name'     => 'number_images',
			'default'  => '',
			'type'     => 'text',
			'title'    => __( 'Number of Images', 'nivo-slider' ),
			'descp'    => __( 'The number of images to use in the slider. Leave blank for all images. External sources default to 20', 'nivo-slider' ),
			'sub'      => true,
			'tr_class' => 'dev7_non_manual',
			'reload'   => true,
		);
		$settings[] = array(
			'name'    => 'sizing',
			'default' => 'fixed',
			'type'    => 'select',
			'title'   => __( 'Slider Sizing', 'nivo-slider' ),
			'descp'   => '<span class="dev7-pro-feature">' . sprintf( '<strong>%s</strong> %s <a href="%s" target="_blank">%s</a>', __( 'Pro Feature:', 'nivo-slider' ), __( 'Create fully <strong>responsive</strong> sliders.', 'nivo-slider' ), 'https://themeisle.com/plugins/nivo-slider/?utm_source=wp_plugin&utm_medium=upgrade_link&utm_content=size_setting&utm_campaign=' . $this->post_type . '_lite_plugin', __( 'Upgrade to Pro', 'nivo-slider' ) ) . '</span>',
			'options' => array(
				'fixed' => __( 'Fixed Size', 'nivo-slider' ),
			),
		);
		$settings[] = array(
			'name'     => 'wp_image_size',
			'default'  => 'full',
			'type'     => 'select',
			'title'    => __( 'Image Size', 'nivo-slider' ),
			'descp'    => __( 'Select the size of image from the WordPress media library', 'nivo-slider' ),
			'options'  => Dev7_Core_Images::get_image_sizes(),
			'tr_class' => 'wp-image-size',
		);
		$settings[] = array(
			'name'    => array( 'dim_x', 'dim_y' ),
			'default' => array( 400, 150 ),
			'type'    => 'number',
			'title'   => __( 'Slider Size', 'nivo-slider' ),
			'descp'   => __( '(Size in px) Images will be cropped to these dimensions (eg 400 x 150)', 'nivo-slider' ),
			'connect' => ' x ',
			'parent'  => 'sizing',
			'visible' => 'fixed',
		);
		$settings[] = array(
			'name'    => 'theme',
			'default' => 'default',
			'type'    => 'select',
			'title'   => __( 'Slider Theme', 'nivo-slider' ),
			'descp'   => '<span class="dev7-pro-feature">' . sprintf( '<strong>%s</strong> %s <a href="%s" target="_blank">%s</a>', __( 'Pro Feature:', 'nivo-slider' ), __( 'Select from our selection of beautiful <strong>pre-built themes</strong> or provide your own.', 'nivo-slider' ), 'https://themeisle.com/plugins/nivo-slider/?utm_source=wp_plugin&utm_medium=upgrade_link&utm_content=themes_setting&utm_campaign=' . $this->post_type . '_lite_plugin', __( 'Upgrade to Pro', 'nivo-slider' ) ) . '</span>',
			'options' => array(
				''        => __( 'None', 'nivo-slider' ),
				'default' => __( 'Nivo Slider Default Theme', 'nivo-slider' ),
			),
		);
		$effects    = array(
			'fade'        => __( 'Fade', 'nivo-slider' ),
			'fold'        => __( 'Fold', 'nivo-slider' ),
			'sliceDown'   => __( 'Slice Down', 'nivo-slider' ),
			'sliceUp'     => __( 'Slice Up', 'nivo-slider' ),
			'sliceUpDown' => __( 'Slice Up/Down', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'effect',
			'default' => 'fade',
			'type'    => 'select',
			'title'   => __( 'Transition Effect', 'nivo-slider' ),
			'descp'   => '<span class="dev7-pro-feature">' . sprintf( '<strong>%s</strong> %s <a href="%s" target="_blank">%s</a>', __( 'Pro Feature:', 'nivo-slider' ), __( 'Enable all <strong>16 stunning transition effects</strong>.', 'nivo-slider' ), 'https://themeisle.com/plugins/nivo-slider/?utm_source=wp_plugin&utm_medium=upgrade_link&utm_content=transition_setting&utm_campaign=' . $this->post_type . '_lite_plugin', __( 'Upgrade to Pro', 'nivo-slider' ) ) . '</span>',
			'options' => $effects,
		);
		$settings[] = array(
			'name'    => 'slices',
			'default' => '15',
			'type'    => 'number',
			'title'   => __( 'Slices', 'nivo-slider' ),
			'descp'   => __( 'The number of slices to use in the "Slice" transitions (eg 15)', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => array( 'boxCols', 'boxRows' ),
			'default' => array( 8, 4 ),
			'type'    => 'number',
			'title'   => __( 'Box (Cols x Rows)', 'nivo-slider' ),
			'descp'   => __( 'The number of columns and rows to use in the "Box" transitions (eg 8 x 4)', 'nivo-slider' ),
			'connect' => ' x ',
		);
		$settings[] = array(
			'name'    => 'animSpeed',
			'default' => '500',
			'type'    => 'number',
			'title'   => __( 'Animation Speed', 'nivo-slider' ),
			'descp'   => __( 'The speed of the transition animation in milliseconds (eg 500)', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'controlNavThumbs',
			'default' => 'off',
			'type'    => 'custom',
			'title'   => __( 'Enable Thumbnail Navigation', 'nivo-slider' ),
			'descp'   => '<span class="dev7-pro-feature">' . sprintf( '<strong>%s</strong> %s <a href="%s" target="_blank">%s</a>', __( 'Pro Feature:', 'nivo-slider' ), __( 'Enable <strong>thumbnail navigation</strong> and control thumb sizes.', 'nivo-slider' ), 'https://themeisle.com/plugins/nivo-slider/?utm_source=wp_plugin&utm_medium=upgrade_link&utm_content=thumbnail_setting&utm_campaign=' . $this->post_type . '_lite_plugin', __( 'Upgrade to Pro', 'nivo-slider' ) ) . '</span>',
		);
		$settings[] = array(
			'name'    => 'pauseTime',
			'default' => '3000',
			'type'    => 'number',
			'title'   => __( 'Pause Time', 'nivo-slider' ),
			'descp'   => __( 'The amount of time to show each slide in milliseconds (eg 3000)', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'startSlide',
			'default' => '0',
			'type'    => 'number',
			'title'   => __( 'Start Slide', 'nivo-slider' ),
			'descp'   => __( 'Set which slide the slider starts from (zero based index: usually 0)', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'directionNav',
			'default' => 'on',
			'type'    => 'checkbox',
			'title'   => __( 'Enable Direction Navigation', 'nivo-slider' ),
			'descp'   => __( 'Prev &amp; Next arrows', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'controlNav',
			'default' => 'on',
			'type'    => 'checkbox',
			'title'   => __( 'Enable Control Navigation', 'nivo-slider' ),
			'descp'   => __( 'eg 1,2,3...', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'imageLink',
			'default' => 'on',
			'type'    => 'checkbox',
			'title'   => __( 'Enable Images Links', 'nivo-slider' ),
			'descp'   => __( 'If a link has been added to an image when configuring, the image links to the url.', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'targetBlank',
			'default' => 'on',
			'type'    => 'checkbox',
			'title'   => __( 'Open Links in New Window', 'nivo-slider' ),
			'descp'   => __( 'Open the links in a new window.', 'nivo-slider' ),
			'parent'  => 'imageLink',
			'visible' => 'on',
		);
		$settings[] = array(
			'name'    => 'pauseOnHover',
			'default' => 'on',
			'type'    => 'checkbox',
			'title'   => __( 'Pause the Slider on Hover', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'manualAdvance',
			'default' => 'off',
			'type'    => 'checkbox',
			'title'   => __( 'Manual Transitions', 'nivo-slider' ),
			'descp'   => __( 'Slider is always paused', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'randomStart',
			'default' => 'off',
			'type'    => 'custom',
			'title'   => __( 'Random Start Slide', 'nivo-slider' ),
			'descp'   => '<span class="dev7-pro-feature">' . sprintf( '<strong>%s</strong> %s <a href="%s" target="_blank">%s</a>', __( 'Pro Feature:', 'nivo-slider' ), __( 'Enable <strong>random start slide</strong>.', 'nivo-slider' ), 'https://themeisle.com/plugins/nivo-slider/?utm_source=wp_plugin&utm_medium=upgrade_link&utm_content=random_setting&utm_campaign=' . $this->post_type . '_lite_plugin', __( 'Upgrade to Pro', 'nivo-slider' ) ) . '</span>',
		);

		return $settings;
	}

	/**
	 * Get Nivo themes and any custom themes from uploads/nivo-themes/
	 *
	 * @since  2.2
	 * @access private
	 *
	 * @param bool $select
	 *
	 * @return array $themes
	 */
	private function get_themes( $select = false ) {
		$nivo_theme_specs = array(
			'SkinName'       => 'Skin Name',
			'SkinURI'        => 'Skin URI',
			'Description'    => 'Description',
			'Version'        => 'Version',
			'Author'         => 'Author',
			'AuthorURI'      => 'Author URI',
			'SupportsThumbs' => 'Supports Thumbs',
		);

		$plugin_themes = glob( NIVO_SLIDER_LITE_PLUGIN_DIR . '/assets/themes/*', GLOB_ONLYDIR );

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
						$theme_url = plugins_url( 'assets/themes/' . $theme_name . '/' . $theme_name . '.css', NIVO_SLIDER_LITE_PLUGIN_FILE );
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

	/**
	 * Remove link to the plugin settings on the plugin table list
	 *
	 * @since  2.2
	 * @access public
	 */
	public function plugin_action_links( $links, $file ) {
		if ( basename( $file, '.php' ) == 'nivo-slider-lite' ) {
			if ( isset( $links[0] ) ) {
				unset( $links[0] );
			}
		}

		return $links;
	}

	/**
	 * Override the type manual description
	 */
	public function manual_type_description() {
		echo '<span class="dev7-pro-feature">' . sprintf( '<strong>%s</strong> %s <a href="%s" target="_blank">%s</a>', __( 'Pro Feature:', 'nivo-slider' ), __( 'Automatically populate sliders from <strong>posts galleries</strong>, <strong>posts in categories</strong> and <strong>sticky posts</strong>.', 'nivo-slider' ), 'https://themeisle.com/plugins/nivo-slider/?utm_source=wp_plugin&utm_medium=upgrade_link&utm_content=type_setting&utm_campaign=' . $this->post_type . '_lite_plugin', __( 'Upgrade to Pro', 'nivo-slider' ) ) . '</span>';
	}

	/**
	 * Restrict image sources
	 *
	 * @param $sources
	 *
	 * @return array
	 */
	public function get_image_sources( $sources ) {
		$new_sources   = array();
		$new_sources['manual'] = $sources['manual'];

		return $new_sources;
	}

	/**
	 * Adds the Upgrade meta box to the edit screen
	 */
	public function meta_box_upgrade() {
		echo '<p>' . __( 'Get more awesome features by upgrading to the full plugin:', 'nivo-slider' ) . '</p>';
		echo '<ul>';
		echo '<li>' . __( 'Create sliders from Galleries, Categories and Sticky Posts', 'nivo-slider' ) . '</li>';
		echo '<li>' . __( 'Responsive sliders', 'nivo-slider' ) . '</li>';
		echo '<li>' . __( 'Pre-built and custom themes', 'nivo-slider' ) . '</li>';
		echo '<li>' . __( 'All 16 transition effects', 'nivo-slider' ) . '</li>';
		echo '<li>' . __( 'Thumbnail navigation', 'nivo-slider' ) . '</li>';
		echo '<li>' . __( 'Random start slide', 'nivo-slider' ) . '</li>';
		echo '<li>' . __( 'Nivo Slider Widget', 'nivo-slider' ) . '</li>';
		echo '<li>' . __( 'Media Manager Plus integration', 'nivo-slider' ) . '</li>';
		echo '<li>' . __( 'Access to our support Help Desk', 'nivo-slider' ) . '</li>';
		echo '</ul>';
		echo '<a href="https://themeisle.com/plugins/nivo-slider/?utm_source=wp_plugin&utm_medium=upgrade_link&utm_content=upgrade_metabox&utm_campaign=' . $this->post_type . '_lite_plugin" target="_blank" class="button-primary">' . __( 'Upgrade Now', 'nivo-slider' ) . '</a>';
	}

	/**
	 * Custom shortcode plugin output
	 *
	 * @since  2.2
	 * @access public
	 *
	 * @param int    $id
	 * @param string $output
	 * @param array  $options
	 * @param array  $images
	 * @param string $slider_type
	 *
	 * @return string $output
	 */
	public function shortcode_output( $id, $output, $options, $images, $slider_type ) {
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
		if ( $options['sizing'] == 'fixed' ) {
			$output .= ' style="width:' . $options['dim_x'] . 'px;height:' . $options['dim_y'] . 'px;"';
		}
		$output .= '>';
		$i = 0;
		foreach ( $images as $image ) {

			$image_link   = dev7_default_val( $options, 'imageLink', 'on' );
			$target_blank = dev7_default_val( $options, 'targetBlank', 'on' );
			if ( ( isset( $image['post_permalink'] ) && $image['post_permalink'] != '' ) && $image_link == 'on' ) {
				$target = ( $target_blank == 'on' ) ? ' target="_blank"' : '';
				$output .= '<a ' . $target . ' href="' . $image['post_permalink'] . '">';
			}

			if ( $options['sizing'] == 'fixed' && isset( $image['attachment_id'] ) ) {
				$resized_image = Dev7_Core_Images::resize_image( $image['attachment_id'], '', $options['dim_x'], $options['dim_y'], true );
				if ( is_wp_error( $resized_image ) ) {
					echo '<p>Error: ' . $resized_image->get_error_message() . '</p>';
					$output .= '<img src="" ';
				} else {
					$output .= '<img src="' . $resized_image['url'] . '" ';
				}
			} else {
				$output .= '<img src="' . $image['image_src'] . '" ';
			}

			if ( ( $options['type'] == 'manual' || $options['type'] == 'gallery' ) && isset( $image['post_title'] ) && $image['post_title'] != '' ) {
				$captions[] = $image['post_title'];
				$output .= 'title="#nivoslider-' . $id . '-caption-' . $i . '" ';
				$i++;
			}
			if ( ( $options['type'] == 'category' || $options['type'] == 'sticky' || $options['type'] == 'custom' ) && $options['enable_captions'] == 'on' && isset( $image['post_title'] ) && $image['post_title'] != '' ) {
				$captions[] = $image['post_title'];
				$output .= 'title="#nivoslider-' . $id . '-caption-' . $i . '" ';
				$i++;
			}

			if ( isset( $options['controlNavThumbs'] ) && $options['controlNavThumbs'] == 'on' ) {
				if ( isset( $image['thumbnail'] ) ) {
					$output .= 'data-thumb="' . $image['thumbnail'] . '" ';
				} else {
					$resized_image = Dev7_Core_Images::resize_image( $image['attachment_id'], '', $options['thumbSizeWidth'], $options['thumbSizeHeight'], true );
					if ( is_wp_error( $resized_image ) ) {
						echo '<p>Error: ' . $resized_image->get_error_message() . '</p>';
						$output .= 'data-thumb="" ';
					} else {
						$output .= 'data-thumb="' . $resized_image['url'] . '" ';
					}
				}
			}

			$output .= 'alt="' . __( $image['alt_text'] ) . '" />';
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
			$output .= __( $caption );
			$output .= '</div>';
			$i++;
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

		return $output;
	}

}

/**
 * Template function to wrap the plugin shortcode
 *
 * @since 2.2
 *
 * @param mixed $slider
 * @param bool  $return
 */
if ( ! function_exists( 'nivo_slider' ) ) {
	function nivo_slider( $slider, $return = false ) {

		$slug = '';
		$id   = 0;

		if ( is_numeric( $slider ) ) {
			$id = $slider;
		} else {
			$slug = $slider;
		}

		if ( $return ) {
			return do_shortcode( '[nivoslider slug="' . $slug . '" id="' . $id . '" template="1"]' );
		} else {
			echo do_shortcode( '[nivoslider slug="' . $slug . '" id="' . $id . '" template="1"]' );
		}
	}
}
