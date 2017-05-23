<?php
/**
 * The admin-edit functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 */

/**
 * The admin-edit functionality of the plugin.
 *
 * TODO WRITE DESCRIPTION
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Core_Admin_Edit extends Nivo_Core_Abstract implements Nivo_Library_Interface {

	/**
	 * Plugin options
	 *
	 * @var        array
	 * @access    private
	 * @since    2.2.*
	 */
	private $options;

	/**
	 * Instance of Nivo Images Core Class
	 *
	 * @var        object
	 * @access    private
	 * @since    2.2.*
	 */
	private $core_images;

	/**
	 * Use this instead of overriding __construct
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function after_core_construct() {
		$this->core_images = new Nivo_Core_Images( self::get_plugin_settings() );
		$this->options     = get_option( self::get_plugin_settings()->get_label( 'options_key' ) );
	}

	/**
	 * Post edit screen settings
	 *
	 * @since    2.2.*
	 * @access    public
	 *
	 * @param    array $settings The settings array.
	 *
	 * @return array
	 */
	public function admin_edit_settings( $settings = array() ) {
		$settings   = array();
		$settings[] = array(
			'name'     => 'enable_captions',
			'default'  => 'on',
			'type'     => 'checkbox',
			'title'    => __( 'Enable Captions', 'nivo-slider' ),
			'descp'    => __( 'Enable automatic captions from post titles', 'nivo-slider' ),
			'sub'      => true,
			'tr_class' => 'nivo_captions',
		);
		$settings[] = array(
			'name'     => 'number_images',
			'default'  => '',
			'type'     => 'text',
			'title'    => __( 'Number of Images', 'nivo-slider' ),
			'descp'    => __( 'The number of images to use in the slider. Leave blank for all images. External sources default to 20', 'nivo-slider' ),
			'sub'      => true,
			'tr_class' => 'nivo_non_manual',
			'reload'   => true,
		);
		$settings[] = array(
			'name'    => 'sizing',
			'default' => 'responsive',
			'type'    => 'select',
			'title'   => __( 'Slider Sizing', 'nivo-slider' ),
			'descp'   => __( 'Responsive sliders will fill the width of the container', 'nivo-slider' ),
			'options' => array(
				'responsive' => __( 'Responsive', 'nivo-slider' ),
				'fixed'      => __( 'Fixed Size', 'nivo-slider' ),
			),
		);
		$settings[] = array(
			'name'     => 'wp_image_size',
			'default'  => 'full',
			'type'     => 'select',
			'title'    => __( 'Image Size', 'nivo-slider' ),
			'descp'    => __( 'Select the size of image from the WordPress media library', 'nivo-slider' ),
			'options'  => $this->core_images->get_image_sizes(),
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
		$themes     = $this->get_themes( true );
		$themes     = array_merge( array(
			'' => 'None',
		), (array) $themes );
		$settings[] = array(
			'name'    => 'theme',
			'default' => '',
			'type'    => 'select',
			'title'   => __( 'Slider Theme', 'nivo-slider' ),
			'descp'   => __( 'Use a pre-built theme or provide your own styles.', 'nivo-slider' ),
			'options' => $themes,
		);
		$effects    = array(
			'random'             => __( 'Random', 'nivo-slider' ),
			'fade'               => __( 'Fade', 'nivo-slider' ),
			'fold'               => __( 'Fold', 'nivo-slider' ),
			'sliceDown'          => __( 'Slice Down', 'nivo-slider' ),
			'sliceDownLeft'      => __( 'Slice Down (Left)', 'nivo-slider' ),
			'sliceUp'            => __( 'Slice Up', 'nivo-slider' ),
			'sliceUpLeft'        => __( 'Slice Up (Left)', 'nivo-slider' ),
			'sliceUpDown'        => __( 'Slice Up/Down', 'nivo-slider' ),
			'sliceUpDownLeft'    => __( 'Slice Up/Down (Left)', 'nivo-slider' ),
			'slideInRight'       => __( 'Slide In (Right)', 'nivo-slider' ),
			'slideInLeft'        => __( 'Slide In (Left)', 'nivo-slider' ),
			'boxRandom'          => __( 'Box Random', 'nivo-slider' ),
			'boxRain'            => __( 'Box Rain', 'nivo-slider' ),
			'boxRainReverse'     => __( 'Box Rain (Reverse)', 'nivo-slider' ),
			'boxRainGrow'        => __( 'Box Rain Grow', 'nivo-slider' ),
			'boxRainGrowReverse' => __( 'Box Rain Grow (Reverse)', 'nivo-slider' ),
		);
		$settings[] = array(
			'name'    => 'effect',
			'default' => 'fade',
			'type'    => 'select',
			'title'   => __( 'Transition Effect', 'nivo-slider' ),
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
			'name'     => 'controlNavThumbs',
			'default'  => 'off',
			'type'     => 'checkbox',
			'title'    => __( 'Enable Thumbnail Navigation', 'nivo-slider' ),
			'tr_class' => 'nivo_thumb_nav',
		);
		$settings[] = array(
			'name'     => array( 'thumbSizeWidth', 'thumbSizeHeight' ),
			'default'  => array( 70, 50 ),
			'type'     => 'number',
			'title'    => __( 'Thumbnail Size', 'nivo-slider' ),
			'descp'    => __( 'The width and height of the thumbnails', 'nivo-slider' ),
			'connect'  => ' x ',
			'tr_class' => 'nivo_thumb_size',
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
			'type'    => 'checkbox',
			'title'   => __( 'Random Start Slide', 'nivo-slider' ),
			'descp'   => __( 'Overrides Start Slide value', 'nivo-slider' ),
		);

		return $settings;
	}

	/**
	 * Displays the introduction on the settings page
	 *
	 * @since    2.2.*
	 * @access   public
	 * @return mixed
	 */
	public function display_settings_intro() {
		echo apply_filters( $this->labels['post_type'] . '_settings_intro', '' );
	}

	/**
	 * Adds meta boxes to the edit screen
	 *
	 * @since    2.2.*
	 * @access    public
	 */
	public function admin_init() {
		add_meta_box(
			$this->labels['post_type'] . '_upload_box', sprintf( __( '%1$s Images', 'nivo-slider' ), $this->labels['singular'] ), array(
			$this,
			'meta_box_upload',
			), $this->labels['post_type'], 'normal'
		);
		add_meta_box(
			$this->labels['post_type'] . '_settings_box', __( 'Settings', 'nivo-slider' ), array(
			$this,
			'meta_box_settings',
			), $this->labels['post_type'], 'normal'
		);
		add_meta_box(
			$this->labels['post_type'] . '_shortcode_box', sprintf( __( 'Using this %1$s', 'nivo-slider' ), $this->labels['singular'] ), array(
			$this,
			'meta_box_shortcode',
			), $this->labels['post_type'], 'side'
		);
		if ( ! defined( 'NIVO_SLIDER_PRO' ) ) {
			add_meta_box(
				$this->labels['post_type'] . '_usefullinks_box', __( 'Full Version', 'nivo-slider' ), array(
				$this,
				'meta_box_full_version',
				), $this->labels['post_type'], 'side'
			);
			add_meta_box(
				$this->labels['post_type'] . '_enable_tracking', __( 'Contribute to Nivo', 'nivo-slider' ), array(
				$this,
				'enable_tracking',
				), $this->labels['post_type'], 'side'
			);
		}

	}

	/**
	 * Adds the Shortcode meta box to the edit screen
	 *
	 * @since    2.2.*
	 * @access    public
	 */
	public function meta_box_shortcode() {
		global $post;
		echo '<p>' . sprintf( __( 'To use this %1$s in your posts or pages use the following shortcode:', 'nivo-slider' ), strtolower( $this->labels['singular'] ) ) . '</p>
        <p><code>[' . $this->labels['shortcode'] . ' id="' . $post->ID . '"]</code>';
		if ( $post->post_name != '' && $post->post_name <> $post->ID ) {
			echo 'or</p><p><code>[' . $this->labels['shortcode'] . ' slug="' . $post->post_name . '"]</code>';
		}
		echo '</p>';
		echo '<p>' . sprintf( __( 'To use this %1$s in a theme template file use the following code:', 'nivo-slider' ), strtolower( $this->labels['singular'] ) ) . '</p>
		<p><code>&lt;?php ' . $this->labels['function'] . '( ' . $post->ID . ' ); ?></code>';
		if ( $post->post_name != '' && $post->post_name <> $post->ID ) {
			echo ' or</p><p><code>&lt;?php ' . $this->labels['function'] . '( "' . $post->post_name . '" ); ?></code>';
		}
		echo '</p>';
	}

	/**
	 * Adds upsell box to the edit screen
	 *
	 * @since    2.2.*
	 * @access    public
	 */
	public function meta_box_full_version() {
		?>
		<div class="nivo-upsell-box">
			<h3><?php _e( ' Extend Nivo Slider  ', 'nivo-slider' ); ?></h3>
			<ul>
				<li><?php _e( 'More slider types', 'nivo-slider' ); ?></li>
				<li><?php _e( 'Build carousels', 'nivo-slider' ); ?></li>
				<li><?php _e( 'Create lightboxes', 'nivo-slider' ); ?> </li>
				<li><?php _e( 'Automatic updates', 'nivo-slider' ); ?></li>
				<li><?php _e( 'Priority support', 'nivo-slider' ); ?></li>
			</ul>
			<a href="<?php echo NIVO_PRO_UPSELL; ?>" target="_blank" class="button button-primary">View more
				features</a></div>
		<?php
	}

	/**
	 * Adds tracking box to the edit screen
	 *
	 * @since    2.2.*
	 * @access    public
	 */
	public function enable_tracking() {
		$checked = get_option( 'nivo_logger_flag', 'no' ) === 'yes' ? 'checked' : '';
		?>
		<div class=" nivo-tracking"><label class="nivo-switch" ><input type="checkbox" <?php echo $checked; ?>>
				<div class="nivo-slider visualizer-round"></div>
			</label><span><?php _e( 'Enable Tracking', 'nivo-slider' ); ?> <sup>*</sup></span>
			<p>
				<small><sup>*</sup><?php _e( 'Allow Nivo to anonymously track how this plugin is used and help us make the
					plugin better. No sensitive data is tracked.', 'nivo-slider' ); ?>
				</small>
			</p>
		</div>
		<?php
	}

	/**
	 * Adds the Image Upload meta box to the edit screen to configure the [gallery]
	 *
	 * @since    2.2.*
	 * @access    public
	 */
	public function meta_box_upload() {
		global $post;
		$options = get_post_meta( $post->ID, $this->labels['post_meta_key'], true );
		?>
		<a id="manual_images_upload" href="#" class="button">Configure</a>
		<input type="hidden" id="_manual_image_ids"
		       name="<?php echo $this->labels['post_meta_key']; ?>[manual_image_ids]"
		       value="<?php echo $this->nivo_default_val( $options, 'manual_image_ids', '' ); ?>">
		<ul id="<?php echo $this->labels['post_type']; ?>-images" class="nivo-plugin-images"></ul>
		<?php
	}

	/**
	 * Adds the [gallery] Settings meta box to the edit screen
	 *
	 * @since    2.2.*
	 * @access    public
	 */
	public function meta_box_settings() {
		global $post;
		$options = get_post_meta( $post->ID, $this->labels['post_meta_key'], true );
		wp_nonce_field( $this->labels['plugin_name'], $this->labels['post_type'] . '_noncename' );
		echo '<input type="hidden" name="taxonomy" value="' . ( ( isset( $_GET['taxonomy'] ) ) ? $_GET['taxonomy'] : 'slider' ) . '" />';
		?>
		<table id="<?php echo $this->labels['post_type']; ?>-settings" class="form-table">
			<tr valign="top">
				<th scope="row"><?php echo sprintf( __( ' %1$s Type', 'nivo-slider' ), $this->labels['singular'] ) ?></th>
				<td>
					<select
							name="<?php echo $this->labels['post_meta_key']; ?>[<?php echo $this->labels['source_name']; ?>]">
						<?php
						$selected_source = $this->nivo_default_val( $options, $this->labels['source_name'], $this->core_images->image_source_default() );
						$images_sources  = $this->core_images->get_image_sources();
						if ( $images_sources ) {
							foreach ( $images_sources as $source => $value ) {
								$disabled = '';
								if ( strpos( $source, 'disabled' ) !== false ) {
									$disabled = 'disabled';
								}
								echo ' <option value="' . $source . '"';
								if ( $selected_source == $source ) {
									echo ' selected="selected"';
								}
								echo $disabled . ' > ' . $value . '</option > ';
							}
						} else {
							echo '<option value="none" > ' . __( 'No Sources', 'nivo-slider' ) . ' </option > ';
						} ?>
					</select>
					<br>
					<span class="manual description">
					<?php _e( 'Choose to manually upload images or use images from the media library . ', 'nivo-slider' ); ?>
						<br>
						<?php echo apply_filters( 'nivo_field_upsell', 'slider_type' ); ?>
				</span></td>
			</tr>
			<tr valign="top" id="nivo_type_gallery" class="nivo_type">
				<th scope="row">- <?php _e( 'Gallery Location', 'nivo-slider' ); ?></th>
				<td>
					<select
							name="<?php echo $this->labels['post_meta_key']; ?>[<?php echo $this->labels['type_name']; ?>_gallery]">
						<?php
						$args       = array(
							'public' => true,
						);
						$post_types = get_post_types( $args, 'objects' );
						foreach ( $post_types as $post_key => $post_type ) {
							if ( $post_key == 'attachment' ) {
								continue;
							}
							echo '<optgroup label="' . $post_type->labels->name . '">';
							$posts = get_posts( array(
								'numberposts' => - 1,
								'post_type'   => $post_key,
							) );
							foreach ( $posts as $post_item ) {
								echo '<option value="' . $post_item->ID . '"';
								if ( $this->nivo_default_val( $options, $this->labels['type_name'] . '_gallery' ) == $post_item->ID ) {
									echo ' selected="selected"';
								}
								echo '>' . $post_item->post_title . '</option>';
							}
							echo ' </optgroup>';
						}
						?>
					</select><br/>
					<span
							class="description"><?php _e( 'Select the post gallery you want to use', 'nivo-slider' ); ?></span>
				</td>
			</tr>
			<tr valign="top" id="nivo_type_category" class="nivo_type">
				<th scope="row">- <?php _e( 'Category', 'nivo-slider' ); ?></th>
				<td>
					<select
							name="<?php echo $this->labels['post_meta_key']; ?>[<?php echo $this->labels['type_name']; ?>_category]">
						<?php
						$categories = get_categories();
						foreach ( $categories as $category ) {
							echo '<option value="' . $category->cat_ID . '"';
							if ( $this->nivo_default_val( $options, $this->labels['type_name'] . '_category' ) == $category->cat_ID ) {
								echo ' selected="selected"';
							}
							echo '>' . $category->name . '</option>';
						}
						?>
					</select><br/>
					<span
							class="description"><?php _e( 'Select the category you want to use for post thumbnails', 'nivo-slider' ); ?></span>
				</td>
			</tr>
			<tr valign="top" id="nivo_type_custom" class="nivo_type">
				<th scope="row">- <?php _e( 'Custom Post Type', 'nivo-slider' ); ?></th>
				<td>
					<select
							name="<?php echo $this->labels['post_meta_key']; ?>[<?php echo $this->labels['type_name']; ?>_custom]">
						<?php
						$post_types = get_post_types( array(
							'public'   => true,
							'_builtin' => false,
						), 'objects' );
						foreach ( $post_types as $post_type ) {
							echo '<option value="' . $post_type->name . '"';
							if ( $this->nivo_default_val( $options, $this->labels['type_name'] . '_custom' ) == $post_type->name ) {
								echo ' selected="selected"';
							}
							echo '>' . $post_type->labels->name . '</option>';
						}
						?>
					</select><br/>
					<span
							class="description"><?php _e( 'Select the custom post type you want to use for post thumbnails', 'nivo-slider' ); ?></span>
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
			$settings = apply_filters( $this->labels['post_type'] . '_admin_edit_settings', array() );
			foreach ( $settings as $setting ) {
				$setting = (object) $setting;
				$class   = '';
				$class   .= ( isset( $setting->tr_class ) ) ? $setting->tr_class . ' ' : '';
				if ( isset( $setting->parent ) ) {
					if ( ! is_array( $setting->parent ) ) {
						$parents = array( $setting->parent );
					} else {
						$parents = $setting->parent;
					}
					foreach ( $parents as $parent ) {
						$class .= 'parent-' . $parent . ' ';
					}
					$class .= ( isset( $setting->visible ) ) ? ' ' . $setting->visible . ' ' : '';
				}
				$id = ( isset( $setting->id ) ) ? $setting->id : '';
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
							$element_name    = $this->labels['post_meta_key'] . '[' . $setting_name . ']';
							$element_default = ( isset( $setting->default[ $setting_key ] ) ) ? $setting->default[ $setting_key ] : $setting->default[0];
							$element_value   = $this->nivo_default_val( $options, $setting_name, $element_default );
							$element_class   = '';
							$element_class   .= ( isset( $setting->reload ) ) ? 'reload' : '';
							if ( isset( $setting->pre_element ) && $setting->pre_element != '' ) {
								echo $setting->pre_element;
							}
							switch ( $setting->type ) {
								case 'text':
									?>
									<input type="text" name="<?php echo $element_name; ?>"
									       value="<?php echo $element_value; ?>" class="<?php echo $element_class; ?>"/>
									<?php
									break;
								case 'number':
									?>
									<input type="number" name="<?php echo $element_name; ?>"
									       value="<?php echo $element_value; ?>" class="<?php echo $element_class; ?>"/>
									<?php
									break;
								case 'textarea':
									$rows = ( isset( $setting->rows ) ) ? $setting->rows : 20;
									$cols    = ( isset( $setting->cols ) ) ? $setting->cols : 80;
									?>
									<textarea class="<?php echo $element_class; ?>" name="<?php echo $element_name; ?>"
									          rows="<?php echo $rows; ?>"
									          cols="<?php echo $cols; ?>"><?php echo $element_value; ?></textarea>
									<?php
									break;
								case 'checkbox':
									?>
									<input type="hidden" name="<?php echo $element_name; ?>" value="off"/>
									<input id="<?php echo $element_id; ?>" type="checkbox"
									       name="<?php echo $element_name; ?>"
									       value="on"<?php if ( $element_value == 'on' ) {
												echo ' checked="checked"';
} ?>/>
									<?php
									break;
								case 'select':
									?>
									<select id="<?php echo $element_id; ?>" name="<?php echo $element_name; ?>"
									        class="<?php echo $element_class; ?>">
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
									echo isset( $setting->custom ) ? $setting->custom : '';
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
	 * @since    2.2.*
	 * @access    public
	 *
	 * @param    string $image_source Source of the [gallery] images
	 * @param    string $selected_source Selected source of the [gallery] images
	 *
	 * @return string
	 */
	public function get_image_source_details( $image_source, $selected_source ) {
		if ( ! $this->nivo_mmp_active() ) {
			return '';
		}
		$defaults = $this->core_images->image_sources_defaults();
		if ( ! array_key_exists( $image_source, $defaults ) ) {
			$var      = 'media_manager_plus_source_' . $image_source;
			$obj      = new $var();
			$settings = $obj->show_details();
			global $post;
			$options = get_post_meta( $post->ID, $this->labels['post_meta_key'], true );
			$display = ( $image_source != $selected_source ) ? 'style="display: none;"' : '';
			$header  = '<tr valign="top" id="nivo_type_' . $image_source . '" class="nivo_type image_source" ' . $display . '>';
			$header  .= '<th scope="row"> ' . ucfirst( $image_source ) . ' settings</th>';
			$header  .= '<td>';
			$body    = '<label>';
			$body    .= '	<select name="' . $this->labels['post_meta_key'] . '[' . $image_source . '_type]" class="image_source_type" >';
			foreach ( $settings as $method => $value ) {
				$selected = ( $this->nivo_default_val( $options, $image_source . '_type', '' ) ) == $method ? 'selected="selected"' : '';
				$body     .= '<option ' . $selected . ' value="' . $method . '">' . $value['name'] . '</option>';
			}
			$body .= '	</select><br>';
			$body .= '	<span class="description">Choose the type of images from ' . ucfirst( $image_source ) . ' </span>';
			$body .= '</label>';
			foreach ( $settings as $method => $value ) {
				if ( isset( $value['param_type'] ) ) {
					$body .= '<div id="' . $image_source . '_param_' . $method . '" class="image_source_param">';
					if ( $value['param_type'] == 'text' ) {
						$body .= '<br><input id="' . $image_source . '_' . $method . '" type="text" value="' . $this->nivo_default_val( $options, $image_source . '_' . $method, '' ) . '" name="' . $this->labels['post_meta_key'] . '[' . $image_source . '_' . $method . ']">';
					}
					if ( $value['param_type'] == 'select' ) {
						if ( $value['param_dynamic'] ) {
							$mmp_options     = get_option( 'ubermediasettings_settings', array() );
							$mmp_sources     = $this->nivo_default_val( $mmp_options, 'ubermediasettings_sources_available', array() );
							$source_settings = $mmp_sources[ $image_source . '-settings' ];
							$access_token    = $source_settings['access-token'];
							$param_obj       = new $var( $access_token['oauth_token'], $access_token['oauth_token_secret'] );
							$param_choices   = $param_obj->get_param_choices( $method );

						} else {
							$param_choices = $value['param_choices'];
						}
						$body .= '	<select id="' . $image_source . '_' . $method . '" name="' . $this->labels['post_meta_key'] . '[' . $image_source . '_' . $method . ']">';
						foreach ( $param_choices as $choice_key => $choice_value ) {
							$selected = ( $this->nivo_default_val( $options, $image_source . '_' . $method, '' ) ) == $choice_key ? 'selected="selected"' : '';
							$body     .= '<option ' . $selected . ' value="' . $choice_key . '">' . $choice_value . '</option>';
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

	/**
	 * Adds the custom columns to the post type admin index screen
	 *
	 * @since    2.2.*
	 * @access   public
	 *
	 * @param    array $columns The columns.
	 *
	 * @return array
	 */
	public function edit_columns( $columns ) {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title', 'nivo-slider' ),
			'shortcode' => __( 'Shortcode', 'nivo-slider' ),
			'source'    => __( 'Source', 'nivo-slider' ),
			'type'      => __( 'Type', 'nivo-slider' ),
			'images'    => __( 'Images', 'nivo-slider' ),
			'author'    => __( 'Author', 'nivo-slider' ),
			'date'      => __( 'Date', 'nivo-slider' ),
		);

		return apply_filters( 'nivo_edit_columns', $columns );
	}

	/**
	 * Populates the data in the custom columns
	 *
	 * @since    2.2.*
	 * @access    public
	 *
	 * @param    string $column The column.
	 *
	 * @return mixed
	 */
	public function custom_columns( $column ) {
		global $post;
		if ( $post->post_type != $this->labels['post_type'] ) {
			return;
		}
		$options = get_post_meta( $post->ID, $this->labels['post_meta_key'], true );
		do_action( $this->labels['post_type'] . '_custom_column_switch', $post, $column, $options );
		$version = substr( $this->nivo_default_val( $options, $this->labels['plugin_version'], '2.5.1' ), 0, 1 );
		if ( $version < 3 ) {
			$type = $this->nivo_default_val( $options, 'type', $this->core_images->image_source_default() );
		} else {
			$type = $this->nivo_default_val( $options, $this->labels['source_name'], $this->core_images->image_source_default() );
		}
		$types = $this->core_images->get_image_sources();
		switch ( $column ) {
			case 'images':
				$limit = 5;
				if ( isset( $_GET['mode'] ) && $_GET['mode'] == 'excerpt' ) {
					$limit = 20;
				}
				$images = $this->core_images->get_images( $post->ID, 'thumbnail', $limit );
				if ( $images ) {
					echo '<ul class="nivo-plugin-thumbs">';
					foreach ( $images as $image ) {
						echo '<li><img src="' . $image['image_src'] . '" alt="' . $image['alt_text'] . '" style="width:32px;height:32px;" /></li>';
					}
					echo '</ul>';
				} else {
					if ( ( ! isset( $types[ $type ] ) || $type == $this->labels['manual_name'] ) && ( ! isset( $options['manual_image_ids'] ) || $options['manual_image_ids'] == '' ) ) {
						$images = $this->nivo_get_attached_images( $post->ID, 1 );
						if ( $images ) {
							echo '<a class="reattach-images" data-post="' . $post->ID . '" href="#">Reattach Images</a><div class="reattach-spinner spinner"></div>';
						} else {
							echo 'No images attached';
						}
					}
				}
				break;
			case 'shortcode':
				echo '<code>[' . $this->labels['shortcode'] . ' id="' . $post->ID . '"]</code>';
				if ( $post->post_name != '' ) {
					echo '<br /><code>[' . $this->labels['shortcode'] . ' slug="' . $post->post_name . '"]</code>';
				}
				break;
			case 'type':
			    $taxonomy = wp_get_post_terms( $post->ID, 'nivo_slider', array(
					'fields' => 'names',
				) );

				echo ( ! is_wp_error( $taxonomy ) && isset( $taxonomy[0] ) ) ? $taxonomy[0] : 'slider';
				break;
			case 'source':
				echo isset( $types[ $type ] ) ? $types[ $type ] : 'Manual';
				switch ( $type ) {
					case 'gallery':
						$gallery_post = get_the_title( $this->nivo_default_val( $options, $this->labels['type_name'] . '_gallery' ) );
						echo '<br><em>' . $gallery_post . '</em>';
						break;
					case 'category':
						$category = get_category( $this->nivo_default_val( $options, $this->labels['type_name'] . '_category' ) );
						echo '<br><em>' . $category->name . '</em>';
						break;
					case 'custom':
						$post_type = get_post_type_object( $this->nivo_default_val( $options, $this->labels['type_name'] . '_custom' ) );
						echo '<br><em>' . ( ( isset( $post_type->labels->name ) ) ? $post_type->labels->name : ucfirst( $post_type ) ) . '</em>';
						break;
				}
				break;
		}
	}

	/**
	 * Get the documentation link
	 *
	 * @since    2.2.*
	 * @access    private
	 * @return string
	 */
	private function get_documentation_link() {
		if ( isset( $this->labels['documentation'] ) && $this->labels['documentation'] ) {
			return $this->labels['documentation'];
		}

		return 'http://docs.themeisle.com';
	}
}
