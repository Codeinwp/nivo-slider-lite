<?php
/**
 * Admin Edit Class
 *
 * @package     Plugin Core
 * @subpackage  Admin/Edit
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Edit Core Class
 *
 * @since 2.2
 */
class Dev7_Core_Admin_Edit extends Dev7_Core {

	/**
	 * Instance of Dev7 Images Core Class
	 *
	 * @var object
	 * @access private
	 * @since  2.2
	 */
	private $core_images;

	/**
	 * "construct" setting up the [gallery] edit screen
	 */
	protected function core_init() {
		$this->core_images = new Dev7_Core_Images( $this->labels, $this->is_lite );

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Adds meta boxes to the edit screen
	 *
	 * @since  2.2
	 * @access public
	 */
	public function admin_init() {
		add_meta_box(
			$this->labels->post_type . '_upload_box', sprintf( __( '%1$s Images', 'dev7core' ), $this->labels->singular ), array(
				$this,
				'meta_box_upload'
			), $this->labels->post_type, 'normal'
		);
		add_meta_box(
			$this->labels->post_type . '_settings_box', __( 'Settings', 'dev7core' ), array(
				$this,
				'meta_box_settings'
			), $this->labels->post_type, 'normal'
		);
		add_meta_box(
			$this->labels->post_type . '_shortcode_box', sprintf( __( 'Using this %1$s', 'dev7core' ), $this->labels->singular ), array(
				$this,
				'meta_box_shortcode'
			), $this->labels->post_type, 'side'
		);
		add_meta_box(
			$this->labels->post_type . '_usefullinks_box', __( 'Useful Links', 'dev7core' ), array(
				$this,
				'meta_box_usefullinks'
			), $this->labels->post_type, 'side'
		);
	}

	/**
	 * Adds the Shortcode meta box to the edit screen
	 *
	 * @since  2.2
	 * @access public
	 */
	public function meta_box_shortcode() {
		global $post;
		echo '<p>' . sprintf( __( 'To use this %1$s in your posts or pages use the following shortcode:', 'dev7core' ), strtolower( $this->labels->singular ) ) . '</p>
        <p><code>[' . $this->labels->shortcode . ' id="' . $post->ID . '"]</code>';
		if ( $post->post_name != '' && $post->post_name <> $post->ID ) {
			echo 'or</p><p><code>[' . $this->labels->shortcode . ' slug="' . $post->post_name . '"]</code>';
		}
		echo '</p>';
		echo '<p>' . sprintf( __( 'To use this %1$s in a theme template file use the following code:', 'dev7core' ), strtolower( $this->labels->singular ) ) . '</p>
		<p><code>&lt;?php ' . $this->labels->function . '( ' . $post->ID . ' ); ?></code>';
		if ( $post->post_name != '' && $post->post_name <> $post->ID ) {
			echo ' or</p><p><code>&lt;?php ' . $this->labels->function . '( "' . $post->post_name . '" ); ?></code>';
		}
		echo '</p>';
	}

	/**
	 * Adds the Useful Links meta box to the edit screen
	 *
	 * @since  2.2
	 * @access public
	 */
	public function meta_box_usefullinks() {
		echo '<div class="useful-links">';
		echo '<p>' . __( 'Website:', 'dev7core' ) . ' <a href="' . DEV7_SITE_URL . '/products/' . $this->labels->slug . '/" target="_blank">' . $this->labels->plugin_name . '</a></p>';
		echo '<p>' . __( 'Created by:', 'dev7core' ) . ' <a href="' . DEV7_SITE_URL . '" target="_blank">Dev7studios</a></p>';
		if ( ! $this->is_lite ) {
			echo '<p>' . __( 'Support:', 'dev7core' ) . ' <a href="' . DEV7_SITE_URL . '/support" target="_blank">Support</a></p>';
		}
		echo '<p>' . __( 'Documentation:', 'dev7core' ) . ' <a href="' . $this->get_documentation_link() . '" target="_blank">Documentation</a></p>';
		echo '<p>' . __( 'Changelog:', 'dev7core' ) . ' <a href="' . $this->labels->plugin_url . 'changelog.txt" target="_blank">Changelog</a></p>';
		echo '</div>';
	}

	/**
	 * Get the documentation link
	 *
	 * @return string
	 */
	private function get_documentation_link() {
		if ( isset( $this->labels->documentation ) && $this->labels->documentation ) {
			return $this->labels->documentation;
		}

		return 'http://docs.themeisle.com';
	}

	/**
	 * Adds the Image Upload meta box to the edit screen to configure the [gallery]
	 *
	 * @since  2.2
	 * @access public
	 */
	public function meta_box_upload() {
		global $post;
		$options = get_post_meta( $post->ID, $this->labels->post_meta_key, true );
		?>
		<a id="manual_images_upload" href="#" class="button">Configure</a>
		<input type="hidden" id="_manual_image_ids" name="<?php echo $this->labels->post_meta_key; ?>[manual_image_ids]" value="<?php echo dev7_default_val( $options, 'manual_image_ids', '' ); ?>">
		<ul id="<?php echo $this->labels->post_type; ?>-images" class="dev7plugin-images"></ul>
	<?php
	}

	/**
	 * Adds the [gallery] Settings meta box to the edit screen
	 *
	 * @since  2.2
	 * @access public
	 */
	public function meta_box_settings() {
		global $post;
		$options = get_post_meta( $post->ID, $this->labels->post_meta_key, true );
		wp_nonce_field( plugin_basename( __FILE__ ), $this->labels->post_type . '_noncename' );
		?>
		<table id="<?php echo $this->labels->post_type; ?>-settings" class="form-table">
			<tr valign="top">
				<th scope="row"><?php echo sprintf( __( '%1$s Type', 'dev7-core' ), $this->labels->singular ) ?></th>
				<td>
					<select name="<?php echo $this->labels->post_meta_key; ?>[<?php echo $this->labels->source_name; ?>]">
						<?php
						$selected_source = dev7_default_val( $options, $this->labels->source_name, $this->core_images->image_source_default() );
						$images_sources = $this->core_images->get_image_sources();
						if ( $images_sources ) {
							foreach ( $images_sources as $source => $value ) {
								echo '<option value="' . $source . '"';
								if ( $selected_source == $source ) {
									echo ' selected="selected"';
								}
								echo '>' . __( $value, 'dev7core' ) . '</option>';
							}
						} else {
							echo '<option value="none">' . __( 'No Sources', 'dev7core' ) . '</option>';
						} ?>
					</select>
					<br>
                <span class="manual description">
                	<?php _e( 'Choose to manually upload images or use images from the media library.', 'dev7core' ); ?>
					<br>
					<?php
					if ( ! dev7_mmp_active() ) {
						printf( __( 'You can add images from external sources and use them as feeds with our free plugin %1$s Media Manager Plus %2$s', 'dev7core' ), '<a href="' . admin_url( 'edit.php?post_type=' . $this->labels->post_type . '&page=' . $this->labels->post_type . '-install-plugins' ) . '">', '</a>' );
					} else {
						printf( __( 'Connect more sources using %1$s Media Manager Plus %2$s', 'dev7core' ), '<a href="' . admin_url( 'upload.php?page=uber-media' ) . '">', '</a>' );
					}
					?>
	                <br>
	                <?php do_action( $this->labels->post_type . '_manual_type_description' ); ?>
                </span></td>
			</tr>
			<tr valign="top" id="dev7_type_gallery" class="dev7_type">
				<th scope="row">- <?php _e( 'Gallery Location', 'dev7core' ); ?></th>
				<td>
					<select name="<?php echo $this->labels->post_meta_key; ?>[<?php echo $this->labels->type_name; ?>_gallery]">
						<?php
						$args = array(
							'public' => true,
						);
						$post_types = get_post_types( $args, 'objects' );
						foreach ( $post_types as $post_key => $post_type ) {
							if ( $post_key == 'attachment' ) {
								continue;
							}
							echo '<optgroup label="' . $post_type->labels->name . '">';

							$posts = get_posts( array( 'numberposts' => - 1, 'post_type' => $post_key ) );
							foreach ( $posts as $post_item ) {
								echo '<option value="' . $post_item->ID . '"';
								if ( dev7_default_val( $options, $this->labels->type_name . '_gallery' ) == $post_item->ID ) {
									echo ' selected="selected"';
								}
								echo '>' . $post_item->post_title . '</option>';
							}
							echo ' </optgroup>';
						}
						?>
					</select><br />
					<span class="description"><?php _e( 'Select the post gallery you want to use', 'dev7core' ); ?></span>
				</td>
			</tr>
			<tr valign="top" id="dev7_type_category" class="dev7_type">
				<th scope="row">- <?php _e( 'Category', 'dev7core' ); ?></th>
				<td>
					<select name="<?php echo $this->labels->post_meta_key; ?>[<?php echo $this->labels->type_name; ?>_category]">
						<?php
						$categories = get_categories();
						foreach ( $categories as $category ) {
							echo '<option value="' . $category->cat_ID . '"';
							if ( dev7_default_val( $options, $this->labels->type_name . '_category' ) == $category->cat_ID ) {
								echo ' selected="selected"';
							}
							echo '>' . $category->name . '</option>';
						}
						?>
					</select><br />
					<span class="description"><?php _e( 'Select the category you want to use for post thumbnails', 'dev7core' ); ?></span>
				</td>
			</tr>
			<tr valign="top" id="dev7_type_custom" class="dev7_type">
				<th scope="row">- <?php _e( 'Custom Post Type', 'dev7core' ); ?></th>
				<td>
					<select name="<?php echo $this->labels->post_meta_key; ?>[<?php echo $this->labels->type_name; ?>_custom]">
						<?php
						$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
						foreach ( $post_types as $post_type ) {
							echo '<option value="' . $post_type->name . '"';
							if ( dev7_default_val( $options, $this->labels->type_name . '_custom' ) == $post_type->name ) {
								echo ' selected="selected"';
							}
							echo '>' . $post_type->labels->name . '</option>';
						}
						?>
					</select><br />
					<span class="description"><?php _e( 'Select the custom post type you want to use for post thumbnails', 'dev7core' ); ?></span>
				</td>
			</tr>
			<?php
			// MMP settings for sources
			$images_sources = $this->core_images->get_image_sources();
			if ( $images_sources ) {
				foreach ( $images_sources as $source => $value ) {
					echo $this->get_image_source_details( $source, $selected_source );
				}
			}
			// Plugin settings
			$settings = apply_filters( $this->labels->post_type . '_admin_edit_settings', array() );
			foreach ( $settings as $setting ) {
				$setting = (object) $setting;
				$class   = '';
				$class .= ( isset( $setting->tr_class ) ) ? $setting->tr_class . ' ' : '';
				if ( isset( $setting->parent ) ) {
					if ( ! is_array( $setting->parent ) ) {
						$parents = array( $setting->parent );
					} else {
						$parents = $setting->parent;
					}

					foreach( $parents as $parent ) {
						$class .=  'parent-' . $parent . ' ';
					}

					$class .= ( isset( $setting->visible ) ) ? ' ' . $setting->visible . ' ' : '';
				}
				$id 	= ( isset( $setting->id ) ) ? $setting->id : '';
				?>
				<tr valign="top" id="<?php echo $id; ?>" class="<?php echo $class; ?>">
					<th scope="row"><?php echo ( isset( $setting->sub ) && $setting->sub ) ? '- ' : ''; ?><?php echo $setting->title; ?></th>
					<td>
						<?php

						if ( ! is_array( $setting->name ) ) {
							$setting->name = array( $setting->name );
						}
						foreach ( $setting->name as $setting_key => $setting_name ) {

							$setting->default = ( isset( $setting->default ) ) ? $setting->default : '';
							if ( ! is_array( $setting->default ) ) {
								$setting->default = array( $setting->default );
							}

							$element_id      = $setting_name;
							$element_name    = $this->labels->post_meta_key . '[' . $setting_name . ']';
							$element_default = ( isset( $setting->default[$setting_key] ) ) ? $setting->default[$setting_key] : $setting->default[0];
							$element_value   = dev7_default_val( $options, $setting_name, $element_default );
							$element_class   = '';
							$element_class .= ( isset( $setting->reload ) ) ? 'reload' : '';

							if ( isset( $setting->pre_element ) && $setting->pre_element != '' ) {
								echo $setting->pre_element;
							}
							switch ( $setting->type ) {

								case 'text':
									?>
									<input type="text" name="<?php echo $element_name; ?>" value="<?php echo $element_value; ?>" class="<?php echo $element_class; ?>" />
									<?php
									break;

								case 'number':
									?>
									<input type="number" name="<?php echo $element_name; ?>" value="<?php echo $element_value; ?>" class="<?php echo $element_class; ?>" />
									<?php
									break;

								case 'textarea':
									$rows = ( isset( $setting->rows ) ) ? $setting->rows : 20;
									$cols = ( isset( $setting->cols ) ) ? $setting->cols : 80;

									?>
									<textarea class="<?php echo $element_class; ?>" name="<?php echo $element_name; ?>" rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>"><?php echo $element_value; ?></textarea>
									<?php
									break;

								case 'checkbox':
									?>
									<input type="hidden" name="<?php echo $element_name; ?>" value="off" />
									<input id="<?php echo $element_id; ?>" type="checkbox" name="<?php echo $element_name; ?>" value="on"<?php if ( $element_value == 'on' ) {
										echo ' checked="checked"';
									} ?>/>
									<?php
									break;

								case 'select':
									?>
									<select id="<?php echo $element_id; ?>" name="<?php echo $element_name; ?>" class="<?php echo $element_class; ?>">
										<?php
										foreach ( $setting->options as $value => $name ) {
											?>
											<option value="<?php echo $value; ?>"<?php if ( $value == $element_value ) {
												echo ' selected="selected"';
											} ?>><?php echo $name; ?></option>
										<?php } ?>
									</select>
									<?php
									break;

								case 'custom':
									echo isset($setting->custom) ? $setting->custom : '';
									break;

							}

							if ( isset( $setting->connect ) && ( $setting_key + 1 ) < count( $setting->name ) ) {
								echo $setting->connect;
							}

							if ( isset( $setting->post_element ) && $setting->post_element != '' ) {
								echo $setting->post_element;
							}

							if ( ( $setting_key + 1 ) == count( $setting->name ) && $setting->type != 'checkbox' ) {
								echo '<br>';
							}
						}


						if ( isset( $setting->descp ) && $setting->descp != '' ) {
							?>
							<span class="description"><?php echo $setting->descp; ?></span>
						<?php
						}

						if ( isset( $setting->post_descp ) && $setting->post_descp != '' ) {
							echo $setting->post_descp;
						}
						?>
					</td>
				</tr>
			<?php
			}
			?>
		</table>
	<?php
	}

	/**
	 * Amend the settings of the [gallery] post before saving
	 *
	 * @since  2.2
	 *
	 * @param int $post_id Post ID
	 *
	 * @access public
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST[$this->labels->post_type . '_noncename'] ) || ! wp_verify_nonce( $_POST[$this->labels->post_type . '_noncename'], plugin_basename( __FILE__ ) ) ) {
			return;
		}

		// Check permissions
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		// Good to go
		$settings = $_POST[$this->labels->post_meta_key];
		$settings = apply_filters( $this->labels->post_type . '_post_meta_save', $settings );

		update_post_meta( $post_id, $this->labels->post_meta_key, $settings );
	}

	/**
	 * Amend the settings of the [gallery] post before saving
	 *
	 * @since  2.2
	 *
	 * @param string $image_source    Source of the [gallery] images
	 * @param string $selected_source Selected source of the [gallery] images
	 *
	 * @access public
	 * @return string $html HTML output
	 */
	public function get_image_source_details( $image_source, $selected_source ) {
		if ( ! dev7_mmp_active() ) {
			return;
		}

		$defaults = $this->core_images->image_sources_defaults();
		if ( ! array_key_exists( $image_source, $defaults ) ) {

			$var      = 'media_manager_plus_source_' . $image_source;
			$obj      = new $var();
			$settings = $obj->show_details();

			global $post;
			$options = get_post_meta( $post->ID, $this->labels->post_meta_key, true );

			$display = ( $image_source != $selected_source ) ? 'style="display: none;"' : '';
			$header  = '<tr valign="top" id="dev7_type_' . $image_source . '" class="dev7_type image_source" ' . $display . '>';
			$header .= '<th scope="row"> ' . ucfirst( $image_source ) . ' settings</th>';
			$header .= '<td>';
			$body = '<label>';
			$body .= '	<select name="' . $this->labels->post_meta_key . '[' . $image_source . '_type]" class="image_source_type" >';
			foreach ( $settings as $method => $value ) {
				$selected = ( dev7_default_val( $options, $image_source . '_type', '' ) ) == $method ? 'selected="selected"' : '';
				$body .= '<option ' . $selected . ' value="' . $method . '">' . $value['name'] . '</option>';
			}
			$body .= '	</select><br>';
			$body .= '	<span class="description">Choose the type of images from ' . ucfirst( $image_source ) . ' </span>';
			$body .= '</label>';

			foreach ( $settings as $method => $value ) {
				if ( isset( $value['param_type'] ) ) {
					$body .= '<div id="' . $image_source . '_param_' . $method . '" class="image_source_param">';
					if ( $value['param_type'] == 'text' ) {
						$body .= '<br><input id="' . $image_source . '_' . $method . '" type="text" value="' . dev7_default_val( $options, $image_source . '_' . $method, '' ) . '" name="' . $this->labels->post_meta_key . '[' . $image_source . '_' . $method . ']">';
					}

					if ( $value['param_type'] == 'select' ) {

						if ( $value['param_dynamic'] ) {
							$mmp_options = get_option( 'ubermediasettings_settings', array() );
							$mmp_sources = dev7_default_val( $mmp_options, 'ubermediasettings_sources_available', array() );

							$source_settings = $mmp_sources[$image_source . '-settings'];
							$access_token    = $source_settings['access-token'];

							$param_obj     = new $var( $access_token['oauth_token'], $access_token['oauth_token_secret'] );
							$param_choices = $param_obj->get_param_choices( $method );

						} else {
							$param_choices = $value['param_choices'];
						}

						$body .= '	<select id="' . $image_source . '_' . $method . '" name="' . $this->labels->post_meta_key . '[' . $image_source . '_' . $method . ']">';
						foreach ( $param_choices as $choice_key => $choice_value ) {
							$selected = ( dev7_default_val( $options, $image_source . '_' . $method, '' ) ) == $choice_key ? 'selected="selected"' : '';
							$body .= '<option ' . $selected . ' value="' . $choice_key . '">' . $choice_value . '</option>';
						}
						$body .= '	</select>';
					}

					$body .= ( isset( $value['param_desc'] ) ) ? ' <span class="description">' . $value['param_desc'] . '</span>' : '';
					$body .= '</div>';
				}
			}
			$footer = '</td></tr>';
			$html   = $header . $body . $footer;

			return $html;
		}
	}
}