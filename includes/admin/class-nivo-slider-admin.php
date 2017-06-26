<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Slider_Admin extends Nivo_Core_Abstract {

	/**
	 * The ID of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    3.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   string $plugin_name The name of this plugin.
	 * @param   string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$plugin_settings   = new Nivo_Plugin_Settings();
		$plugin_settings->set_labels( $labels = $this->default_labels() );
		$plugin_settings->set_lite( $is_lite = $this->default_lite() );
		self::set_plugin_settings( $plugin_settings );
		parent::__construct();
		$this->options = get_option( $this->labels['options_key'] );
	}

	private function default_labels() {
		// TODO maybe simplify the labels array?
		return array(
			'nivo_item_name'  => 'Nivo Slider WordPress Plugin',
			'plugin_name'     => $this->plugin_name,
			'plugin_version'  => $this->version,
			'plugin_file'     => NIVO_SLIDER_PLUGIN_FILE,
			'plugin_basename' => NIVO_SLIDER_PLUGIN_BASENAME,
			'plugin_url'      => NIVO_SLIDER_PLUGIN_URL,
			'post_type'       => 'nivoslider',
			'shortcode'       => 'nivoslider',
			'function'        => 'nivo_slider',
			'slug'            => 'nivo-slider',
			'taxonomy'        => 'nivo_slider',
			'post_meta_key'   => 'nivo_settings',
			'options_key'     => 'nivoslider_settings',
			'source_name'     => 'type',
			'manual_name'     => 'manual',
			'type_name'       => 'type',
			'singular'        => __( 'Nivo Slider', 'nivo-slider' ),
			'plural'          => __( 'Nivo Sliders', 'nivo-slider' ),
		);
	}

	private function default_lite() {
		return false;
	}

	public function get_nivo_settings() {
		return $this::get_plugin_settings();
	}

	/**
	 * Track the nivo slider usage.
	 *
	 * @return bool Either we should track or not.
	 */
	public function check_logger() {
		return ( get_option( 'nivo_logger_flag', 'no' ) === 'yes' );
	}

	/**
	 * Registers the TinyMCE plugins
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function init_tinymce() {
		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) && get_user_option( 'rich_editing' ) == 'true' ) {
			add_filter( 'mce_external_plugins', array( $this, 'mce_add_plugin' ) );
			add_filter( 'mce_buttons_2', array( $this, 'mce_register_button' ) );
		}
	}

	/**
	 * Registers the TinyMCE script
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function mce_add_plugin( $plugin_array ) {
		$plugin_array[ $this->labels['post_type'] ] = plugins_url( 'assets/js/tinymce.js', $this->labels['plugin_file'] );

		return $plugin_array;
	}

	/**
	 * Registers the TinyMCE button
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function mce_register_button( $buttons ) {
		array_push( $buttons, '|', $this->labels['post_type'] );

		return $buttons;
	}

	/**
	 * Prints the [galleries] as javascript to be used by the TinyMCE plugin
	 * and localize scripts.
	 *
	 * @since  3.0.0
	 * @access public
	 */
	public function admin_print_scripts() {
		// Bellow was added to this function from 2.2
		global $post;
		$settings = array(
			'post_id'   => ( isset( $post->ID ) ) ? $post->ID : '',
			'labels'    => $this->labels,
			'track_url' => add_query_arg( array(
				'nonce'  => wp_create_nonce( 'nivo-track' ),
				'action' => 'track_url',
			), admin_url( 'admin-ajax.php' ) ),
			'nonce'     => wp_create_nonce( $this->labels['post_type'] ),
		);
		$settings = apply_filters( $this->labels['post_type'] . '_script_settings', $settings );
		wp_localize_script( $this->labels['plugin_name'] . '_core_admin', 'nivo_plugin', $settings );
		wp_localize_script( $this->labels['plugin_name'] . '_admin', 'nivo_plugin', $settings );
		wp_localize_script( $this->labels['plugin_name'] . '_image_admin', 'nivo_plugin', $settings );
		// Addition ends here.
		// Galleries list for TinyMCE dropdown
		$galleries = get_posts( array(
			'post_type'      => $this->labels['post_type'],
			'posts_per_page' => - 1,
		) );
		$list      = array();
		foreach ( $galleries as $gallery ) {
			$list[] = array(
				'id'   => $gallery->ID,
				'name' => $gallery->post_title,
			);
		}
		$settings = array(
			'galleries' => json_encode( $list ),
		);
		$html     = "<script type='text/javascript'>\n";
		$html     .= 'var ' . $this->labels['post_type'] . ' = ' . json_encode( $settings ) . '';
		$html     .= "\n</script>";
		echo $html;
	}

	/**
	 * Custom post type menu icon url
	 *
	 * @since   2.2.*
	 * @updated 3.0.0
	 * @access  public
	 * @return string
	 */
	public function menu_icon() {
		return NIVO_SLIDER_PLUGIN_URL . 'assets/images/nivo.png';
	}

	/**
	 * Custom post type labels
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array $labels
	 *
	 * @return array $labels
	 */
	public function plugin_labels( $labels ) {
		$name           = __( 'Nivo Slider', 'nivo-slider' );
		$labels['name'] = $labels['singular_name'] = $labels['menu_name'] = $name;

		return $labels;
	}

	/**
	 * Adds a new menu item under the plugin post type
	 *
	 * @since    2.2.*
	 * @updated  3.0.3
	 * @access    public
	 */
	public function admin_menu() {
		if ( ! class_exists( 'Nivo_Slider_PRO_Admin' ) ) {
			add_submenu_page( 'edit.php?post_type=' . $this->labels['post_type'], __( 'More Features', 'nivo-slider' ), __( 'More Features', 'nivo-slider' ) . '<span class="dashicons 
		dashicons-star-filled more-features-icon" style="   width: 17px;  height: 17px;  margin-left: 4px;  color: #ffca54;  font-size: 17px; vertical-align: -3px;"></span>', 'manage_options', 'nivo-slider-admin-menu-pro-upsell', array(
				$this,
				'render_upsell',
			) );
		}
		add_submenu_page(
			'edit.php?post_type=' . $this->labels['post_type'], 'Settings', 'Settings', 'manage_options', $this->labels['post_type'] . '-settings', array(
				$this,
				'settings_page',
			)
		);
	}

	/**
	 * Method used to render upsell page.
	 *
	 * @since   3.0.3
	 * @access  public
	 */
	public function render_upsell() {
		$this->load_layout( 'nivo-upsell' );
	}

	/**
	 * Method used to render pages
	 *
	 * @since   3.0.3
	 * @access  public
	 *
	 * @param   string $layout_name The name of the layout.
	 *
	 * @return mixed
	 */
	public function load_layout( $layout_name ) {
		wp_enqueue_style( 'nivo-upsell', NIVO_SLIDER_PLUGIN_URL . '/includes/layouts/css/upsell.css' );
		include( NIVO_SLIDER_PLUGIN_DIR . '/includes/layouts/' . $layout_name . '.php' );
	}

	/**
	 * Controls the visibility of the plugin menu. Can be filtered.
	 *
	 * @since    2.2.*
	 * @access    public
	 */
	public function remove_admin_menu() {
		$options = $this->options;
		if ( isset( $options['custom-roles'] ) ) {
			$custom_roles = $options['custom-roles'];
			if ( $custom_roles ) {
				$allowed_roles = apply_filters( $this->labels['post_type'] . '_allowed_roles', $custom_roles );
				global $current_user;
				$user_roles = $current_user->roles;
				$show       = false;
				foreach ( $allowed_roles as $role ) {
					if ( in_array( $role, $user_roles ) ) {
						$show = true;
					}
				}
				if ( ! $show ) {
					remove_menu_page( 'edit.php?post_type=' . $this->labels['post_type'] );
				}
			}
		}
	}

	/**
	 * Renders the settings page for the plugin
	 *
	 * @since    2.2.*
	 * @access    public
	 * @return mixed
	 */
	public function settings_page() {
		?>
		<div id="<?php echo $this->labels['post_type']; ?>-wrap" class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php echo apply_filters( $this->labels['post_type'] . '_settings_page_header', 'Settings' ); ?></h2>

			<form action="options.php" method="post">
				<?php settings_fields( $this->labels['post_type'] . '-settings' ); ?>
				<?php do_settings_sections( $this->labels['post_type'] . '-settings' ); ?>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button-primary"
					       value="<?php _e( 'Save Changes', 'nivo-slider' ); ?>">
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Registers the settings and sections
	 *
	 * @since    2.2.*
	 * @access    public
	 */
	public function register_settings() {
		register_setting(
			$this->labels['post_type'] . '-settings', $this->labels['options_key'], array(
				$this,
				'settings_validate',
			)
		);
		add_settings_section(
			$this->labels['post_type'] . '-settings', '', array(
			$this,
			'display_settings_intro',
			), $this->labels['post_type'] . '-settings'
		);
		$settings[] = array(
			'slug'  => 'custom-roles',
			'title' => __( 'Management Roles', 'nivo-slider' ),
		);
		$settings   = apply_filters( $this->labels['post_type'] . '_register_settings', $settings );
		foreach ( $settings as $setting ) {
			$scope    = ( isset( $setting['scope'] ) ) ? $setting['scope'] : $this;
			$function = 'setting_' . str_replace( '-', '_', $setting['slug'] );
			add_settings_field(
				$setting['slug'], $setting['title'], array(
				$scope,
				$function,
				), $this->labels['post_type'] . '-settings', $this->labels['post_type'] . '-settings'
			);
		}
	}

	/**
	 * Settings helper to render HTML settings
	 *
	 * @since    2.2.*
	 * @access    public
	 *
	 * @param    array $args Settings array.
	 *
	 * @return mixed
	 */
	public function settings_helper( $args ) {
		$element_name    = $this->labels['options_key'] . '[' . $args['name'] . ']';
		$element_default = ( isset( $args['default'] ) ) ? $args['default'] : '';
		$element_value   = $this->nivo_default_val( $this->options, $args['name'], $element_default );
		$element_class   = ( isset( $args['class'] ) ) ? $args['class'] : '';
		switch ( $args['type'] ) {
			case 'text':
				?>
				<input type="text" name="<?php echo $element_name; ?>" value="<?php echo $element_value; ?>"
				       class="<?php echo $element_class; ?> "/>
				<?php
				break;
			case 'textarea':
				$rows = ( isset( $args['rows'] ) ) ? $args['rows'] : 5;
				$cols    = ( isset( $args['cols'] ) ) ? $args['cols'] : 80;
				?>
				<textarea class="<?php echo $element_class; ?>" name="<?php echo $element_name; ?>"
				          rows="<?php echo $rows; ?>"
				          cols="<?php echo $cols; ?>"><?php echo $element_value; ?></textarea>
				<?php
				break;
			case 'number':
				?>
				<input type="number" name="<?php echo $element_name; ?>" value="<?php echo $element_value; ?>"
				       class="<?php echo $element_class; ?> "/>
				<?php
				break;
			case 'checkbox':
				?>
				<input type="hidden" name="<?php echo $element_name; ?>" value="off"/>
				<input type="checkbox" name="<?php echo $element_name; ?>"
				       value="on"<?php if ( $element_value == 'on' ) {
							echo ' checked="checked"';
} ?> class="<?php echo $element_class; ?> "/>
				<?php
				break;
			case 'select':
				?>
				<select name="<?php echo $element_name; ?>" class="<?php echo $element_class; ?> ">
					<?php
					foreach ( $args['options'] as $value => $name ) {
						?>
						<option value="<?php echo $value; ?>"<?php if ( $value == $element_value ) {
							echo ' selected="selected"';
} ?>><?php echo $name; ?></option>
					<?php } ?>
				</select>
				<?php
				break;
		}
		if ( isset( $args['descp'] ) && $args['descp'] != '' ) {
			?>
			<span class="description"><?php echo $args['descp']; ?></span>
			<?php
		}
	}

	/**
	 * Displays the introduction on the settings page
	 *
	 * @since    2.2.*
	 * @access    public
	 * @return string
	 */
	public function display_settings_intro() {
		echo apply_filters( $this->labels['post_type'] . '_settings_intro', '' );
	}

	/**
	 * Validation of settings on save.
	 *
	 * @since    2.2.*
	 * @access    public
	 *
	 * @param    array $input The input array.
	 *
	 * @return array
	 */
	public function settings_validate( $input ) {
		$options = $this->options;
		if ( $options && is_array( $options ) ) {
			foreach ( $options as $key => $option ) {
				if ( ! array_key_exists( $key, $input ) ) {
					$input[ $key ] = $option;
				}
			}
		}
		$input = $this->save_custom_roles( $input );

		return apply_filters( $this->labels['post_type'] . '_settings_validate', $input );
	}

	/**
	 * Custom save method for saving custom roles
	 *
	 * @since    2.2.*
	 * @access    private
	 *
	 * @param    array $input The input array.
	 *
	 * @return array
	 */
	private function save_custom_roles( $input ) {
		if ( $input && is_array( $input ) && isset( $input['roles'] ) ) {
			$custom_roles = array();
			global $wp_roles;
			$role_names = $wp_roles->get_names();
			$roles      = array();
			foreach ( $role_names as $key => $value ) {
				if ( $key != 'administrator' ) {
					$roles[ $key ]               = get_role( $key );
					$roles[ $key ]->display_name = $value;
				}
			}
			foreach ( $input as $key => $value ) {
				if ( array_key_exists( $key, $roles ) ) {
					$custom_roles[] = $key;
					unset( $input[ $key ] );
				}
			}
			unset( $input['roles'] );
			$custom_roles[]        = 'administrator';
			$input['custom-roles'] = $custom_roles;
		}

		return $input;
	}

	/**
	 * Renders setting for custom roles
	 *
	 * @since    2.2.*
	 * @access    public
	 * @return mixed
	 */
	public function setting_custom_roles() {
		$options = $this->options;
		global $wp_roles;
		$role_names = $wp_roles->get_names();
		if ( ! isset( $options['custom-roles'] ) ) {
			$defaults = array();
			foreach ( $role_names as $key => $value ) {
				if ( $key != 'administrator' ) {
					$defaults[] = $key;
				}
			}
			$options['custom-roles'] = $defaults;
		}
		$roles = array();
		foreach ( $role_names as $key => $value ) {
			if ( $key != 'administrator' ) {
				$roles[ $key ]               = get_role( $key );
				$roles[ $key ]->display_name = $value;
				if ( in_array( $roles[ $key ]->name, $options['custom-roles'] ) ) {
					$roles[ $key ]->selected = true;
				}
			}
		}
		echo '<input type="hidden" name="' . $this->labels['options_key'] . '[roles]" value="roles">';
		foreach ( $roles as $role ) :
			echo '<input type="checkbox" id="' . $role->name . '" name="' . $this->labels['options_key'] . '[' . $role->name . ']" ' . ( isset( $role->selected ) && ( $role->selected ) ? ' checked="checked"' : '' ) . '/> ';
			echo $role->display_name . '<br/>';
		endforeach;
	}


	/**
	 * Adds a new link to the plugin settings on the plugin table list
	 *
	 * @since  2.2
	 * @access public
	 */
	public function plugin_action_links( $links, $file ) {
		if ( $file == $this->labels['plugin_basename'] ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=' . $this->labels['post_type'] . '&page=' . $this->labels['post_type'] . '-settings' ), __( 'Settings', 'nivo-slider' ) );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Adds custom settings to the plugin's printed javascript object
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array $settings
	 *
	 * @return array
	 */
	public function script_settings( $settings ) {
		$themes             = $this->get_themes();
		$settings['themes'] = $themes;

		return $settings;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nivo_Slider_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nivo_Slider_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name . '_nivo_css', NIVO_SLIDER_PLUGIN_URL . 'assets/css/nivo-slider.css', array(), $this->version, 'all' );
		if ( get_post_type() == $this->labels['post_type'] ) {
			wp_enqueue_style( $this->plugin_name . '_admin_css', NIVO_SLIDER_PLUGIN_URL . 'assets/css/admin.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nivo_Slider_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nivo_Slider_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name . '_core_admin', NIVO_SLIDER_PLUGIN_URL . 'assets/js/core-admin.js', array( 'jquery' ), $this->version, false );
		//wp_enqueue_script( $this->plugin_name . '_admin', NIVO_SLIDER_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_image_admin', NIVO_SLIDER_PLUGIN_URL . 'assets/js/image-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 *  Returns the upsell message for specific area.
	 */
	public function add_upsell( $type ) {
		switch ( $type ) {
			case 'slider_type':
				return '<span class="nivo-field-upsell">' . sprintf( __( 'You can automatically build slider from post galleries, categories and sticky posts using the %1$s FULL%2$s version.', 'nivo-slider' ), '<a href="' . NIVO_PRO_UPSELL . '" target="_blank">', '</a>' ) . '</span>';
				break;
		}
	}

}
