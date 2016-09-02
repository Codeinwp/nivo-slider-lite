<?php
/**
 * Admin Settings Class
 *
 * @package     Plugin Core
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Settings Core Class
 *
 * @since 2.2
 */
class Dev7_Core_Admin_Settings extends Dev7_Core {

	/**
	 * Plugin options
	 *
	 * @var array
	 * @access private
	 * @since  2.2
	 */
	private $options;

	/**
	 * "construct" for the [gallery] settings page
	 */
	protected function core_init() {
		$this->options = get_option( $this->labels->options_key );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'remove_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'after_plugin_row_' . $this->labels->plugin_basename, array( $this, 'plugin_row' ), 11 );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
	}

	/**
	 * Adds a new menu item under the plugin post type
	 *
	 * @since  2.2
	 * @access public
	 */
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . $this->labels->post_type, 'Settings', 'Settings', 'manage_options', $this->labels->post_type . '-settings', array(
				$this,
				'settings_page'
			)
		);
	}

	/**
	 * Controls the visibility of the plugin menu. Can be filtered.
	 *
	 * @since  2.2
	 * @access public
	 */
	public function remove_admin_menu() {
		$options = $this->options;
		if ( isset( $options['custom-roles'] ) ) {
			$custom_roles = $options['custom-roles'];
			if ( $custom_roles ) {
				$allowed_roles = apply_filters( $this->labels->post_type . '_allowed_roles', $custom_roles );
				global $current_user;
				$user_roles = $current_user->roles;
				$show       = false;
				foreach ( $allowed_roles as $role ) {
					if ( in_array( $role, $user_roles ) ) {
						$show = true;
					}
				}
				if ( ! $show ) {
					remove_menu_page( 'edit.php?post_type=' . $this->labels->post_type );
				}
			}
		}
	}

	/**
	 * Renders the settings page for the plugin
	 *
	 * @since  2.2
	 * @access public
	 */
	public function settings_page() {
		?>
		<div id="<?php echo $this->labels->post_type; ?>-wrap" class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php echo apply_filters( $this->labels->post_type . '_settings_page_header', 'Settings' ); ?></h2>

			<form action="options.php" method="post">
				<?php settings_fields( $this->labels->post_type . '-settings' ); ?>
				<?php do_settings_sections( $this->labels->post_type . '-settings' ); ?>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e( 'Save Changes', 'dev7-core' ); ?>">
				</p>
			</form>
		</div>
	<?php
	}

	/**
	 * Registers the settings and sections
	 *
	 * @since  2.2
	 * @access public
	 */
	public function register_settings() {
		register_setting(
			$this->labels->post_type . '-settings', $this->labels->options_key, array(
				$this,
				'settings_validate'
			)
		);
		add_settings_section(
			$this->labels->post_type . '-settings', '', array(
				$this,
				'display_settings_intro'
			), $this->labels->post_type . '-settings'
		);

		$settings[] = array( 'slug' => 'license-settings', 'title' => __( 'License', 'dev7-core' ) );
		$settings[] = array( 'slug' => 'custom-roles', 'title' => __( 'Management Roles', 'dev7-core' ) );

		$settings = apply_filters( $this->labels->post_type . '_register_settings', $settings );

		foreach ( $settings as $setting ) {
			$scope    = ( isset( $setting['scope'] ) ) ? $setting['scope'] : $this;
			$function = 'setting_' . str_replace( '-', '_', $setting['slug'] );
			add_settings_field(
				$setting['slug'], $setting['title'], array(
					$scope,
					$function
				), $this->labels->post_type . '-settings', $this->labels->post_type . '-settings'
			);
		}
	}

	/**
	 * Settings helper to render HTML settings
	 *
	 * @since  2.2
	 *  @access public
	 *
	 * @param array $args settings array
	 */
	public function settings_helper( $args ) {
		$element_name    = $this->labels->options_key . '[' . $args['name'] . ']';
		$element_default = ( isset( $args['default'] ) ) ? $args['default'] : '';
		$element_value   = dev7_default_val( $this->options, $args['name'], $element_default );
		$element_class   = ( isset( $args['class'] ) ) ? $args['class'] : '';
		switch ( $args['type'] ) {

			case 'text':
				?>
				<input type="text" name="<?php echo $element_name; ?>" value="<?php echo $element_value; ?>" class="<?php echo $element_class; ?> " />
				<?php
				break;

			case 'textarea':
				$rows = ( isset( $args['rows'] ) ) ? $args['rows'] : 5;
				$cols = ( isset( $args['cols'] ) ) ? $args['cols'] : 80;

				?>
				<textarea class="<?php echo $element_class; ?>" name="<?php echo $element_name; ?>" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>"><?php echo $element_value; ?></textarea>
				<?php
				break;

			case 'number':
				?>
				<input type="number" name="<?php echo $element_name; ?>" value="<?php echo $element_value; ?>" class="<?php echo $element_class; ?> " />
				<?php
				break;

			case 'checkbox':
				?>
				<input type="hidden" name="<?php echo $element_name; ?>" value="off" />
				<input type="checkbox" name="<?php echo $element_name; ?>" value="on"<?php if ( $element_value == 'on' ) {
					echo ' checked="checked"';
				} ?> class="<?php echo $element_class; ?> " />
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
	 * @since  2.2
	 * @access public
	 */
	public function display_settings_intro() {
		echo apply_filters( $this->labels->post_type . '_settings_intro', '' );
	}

	/**
	 * Validation of settings on save.
	 *
	 * @since  2.2
	 * @access public
	 */
	public function settings_validate( $input ) {
		$options = $this->options;
		if ( $options && is_array( $options ) ) {
			foreach ( $options as $key => $option ) {
				if ( ! array_key_exists( $key, $input ) && $key != 'license_status' && $key != 'license_key' ) {
					$input[$key] = $option;
				}
			}
		}
		$input = $this->save_custom_roles( $input );

		return apply_filters( $this->labels->post_type . '_settings_validate', $input );
	}

	/**
	 * Renders setting for custom roles
	 *
	 * @since  2.2
	 * @access public
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
				$roles[$key]               = get_role( $key );
				$roles[$key]->display_name = $value;
				if ( in_array( $roles[$key]->name, $options['custom-roles'] ) ) {
					$roles[$key]->selected = true;
				}
			}
		}
		echo '<input type="hidden" name="' . $this->labels->options_key . '[roles]" value="roles">';
		foreach ( $roles as $role ):
			echo '<input type="checkbox" id="' . $role->name . '" name="' . $this->labels->options_key . '[' . $role->name . ']" ' . ( isset( $role->selected ) && ( $role->selected ) ? ' checked="checked"' : '' ) . '/> ';
			echo $role->display_name . '<br/>';
		endforeach;
	}

	/**
	 * Custom save method for saving custom roles
	 *
	 * @since  2.2
	 * @access private
	 */
	private function save_custom_roles( $input ) {
		if ( $input && is_array( $input ) && isset( $input['roles'] ) ) {
			$custom_roles = array();
			global $wp_roles;
			$role_names = $wp_roles->get_names();
			$roles      = array();
			foreach ( $role_names as $key => $value ) {
				if ( $key != 'administrator' ) {
					$roles[$key]               = get_role( $key );
					$roles[$key]->display_name = $value;
				}
			}
			foreach ( $input as $key => $value ) {
				if ( array_key_exists( $key, $roles ) ) {
					$custom_roles[] = $key;
					unset( $input[$key] );
				}
			}
			unset( $input['roles'] );
			$custom_roles[]        = 'administrator';
			$input['custom-roles'] = $custom_roles;
		}

		return $input;
	}

	/**
	 * Renders setting for license
	 *
	 * @since  2.2
	 * @access public
	 */
	public function setting_license_settings() {
		$license_key = dev7_get_license_key( $this->labels->post_type, $this->options );
		$status      = dev7_default_val( $this->options, 'license_status', false );
		echo '<div id="dev7license">';
		echo '<input type="hidden" name="' . $this->labels->options_key . '[license_status]" value="' . $status . '">';
		if ( $status !== false && $status == 'valid' ) {
			echo '<input type="hidden" name="' . $this->labels->options_key . '[license_key]" value="' . $license_key . '">';
			echo '<span style="color:green;">' . __( 'Active: ', 'dev7-core' ) . '</span>';
			echo '<span class="regular-text">' . $license_key . '</span><br>  ';
			echo '<input id="deactivate-license" type="button" class="button-secondary" value="' . __( 'Deactivate License', 'dev7-core' ) . '"/>';
		} else {
			if ( dev7_license_constant( $this->labels->post_type ) ) {
				echo '<input type="hidden" name="' . $this->labels->options_key . '[license_key]" value="' . $license_key . '">';
				echo '<span class="regular-text">' . $license_key . '</span><br>  ';
				echo '<label class="description">' . __( 'Activate this license key to enable automatic upgrades', 'dev7-core' ) . '</label><br>';
			} else {
				echo '<input type="text" name="' . $this->labels->options_key . '[license_key]" class="regular-text" value="' . $license_key . '"><br>  ';
				echo '<label class="description">' . __( 'Enter a valid license key to enable automatic upgrades', 'dev7-core' ) . '</label><br>';
			}
			echo '<input id="activate-license" type="button" class="button-primary" value="' . __( 'Activate License', 'dev7-core' ) . '"/>';
		}
		echo '<span class="spinner"></span></div>';
	}

	/**
	 * Displays a custom message about license activation in the plugin table list
	 *
	 * @since  2.2
	 * @access public
	 */
	public function plugin_row() {
		$license_key = dev7_get_license_key( $this->labels->post_type, $this->options );
		$status      = dev7_default_val( $this->options, 'license_status', 'invalid' );
		if ( ( empty( $license_key ) || $license_key == '' ) || $status == 'invalid' ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=' . $this->labels->post_type . '&page=' . $this->labels->post_type . '-settings' ), __( 'Settings', 'dev7-core' ) );
			$message       = 'To finish activating ' . $this->labels->plugin_name . ', please go to ' . $settings_link . ' and enter your license key and activate it to enable automatic updates.';
		} else {
			return;
		}
		?>
		<tr class="plugin-update-tr dev7-custom">
			<td colspan="3" class="plugin-update">
				<div class="update-message">
					<?php echo $message; ?>
				</div>
			</td>
		</tr>
	<?php
	}

	/**
	 * Adds a new link to the plugin settings on the plugin table list
	 *
	 * @since  2.2
	 * @access public
	 */
	public function plugin_action_links( $links, $file ) {
		if ( $file == $this->labels->plugin_basename ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=' . $this->labels->post_type . '&page=' . $this->labels->post_type . '-settings' ), __( 'Settings', 'dev7-core' ) );
			array_unshift( $links, $settings_link );
		}

		return $links;
	}
}