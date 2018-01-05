<?php
/**
 * The admin-images functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 */

/**
 * The admin-images functionality of the plugin.
 *
 * TODO WRITE DESCRIPTION
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Core_Admin_Images extends Nivo_Core_Abstract implements Nivo_Library_Interface {

	/**
	 * Use this instead of overriding __construct
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function after_core_construct() {}

	/**
	 * Checks for themes that haven't added 'post-thumbnails' support required and adds it.
	 *
	 * @since   2.3.2
	 * @access  public
	 */
	public function theme_support_check() {
		$support = get_theme_support( 'post-thumbnails' );
		if ( ! $support ) {
			add_theme_support( 'post-thumbnails' );
		}
	}

	/**
	 * Apply custom strings to the Media View for integrating with Media Manager Plus
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   array  $strings Media View strings
	 * @param   object $post    Current Post
	 * @return array
	 */
	public function custom_media_string( $strings, $post ) {
		if ( isset( $post->post_type ) && $post->post_type == $this->labels['post_type'] ) {
			$strings['createGalleryTitle'] = sprintf( __( 'Create %1$s', 'nivo-slider' ), $this->labels['singular'] );
			$strings['createNewGallery']   = sprintf( __( 'Create %1$s', 'nivo-slider' ), strtolower( $this->labels['singular'] ) );
			$strings['addToGallery']       = sprintf( __( 'Add to %1$s', 'nivo-slider' ), strtolower( $this->labels['singular'] ) );
			$strings['addToGalleryTitle']  = sprintf( __( 'Add to %1$s', 'nivo-slider' ), $this->labels['singular'] );
			$strings['editGalleryTitle']   = sprintf( __( 'Edit %1$s', 'nivo-slider' ), $this->labels['singular'] );
			$strings['insertGallery']      = sprintf( __( 'Save %1$s', 'nivo-slider' ), $this->labels['singular'] );
			$strings['updateGallery']      = sprintf( __( 'Update %1$s', 'nivo-slider' ), $this->labels['singular'] );
			$strings['nivoSideDetails']    = __( 'Slide Details', 'nivo-slider' );

			$strings['ubermediaButton'] = sprintf( __( 'Add to %1$s', 'nivo-slider' ), strtolower( $this->labels['singular'] ) );
			$strings['mmp_menu']        = 'gallery';
			$strings['mmp_menu_prefix'] = __( 'Add from ', 'nivo-slider' );
			sprintf( __( 'Update %1$s', 'nivo-slider' ), $this->labels['singular'] );

			$options                                      = get_post_meta( $post->ID, $this->labels['post_meta_key'], true );
			$strings[ $this->labels['post_type'] . 'Images' ] = $this->nivo_default_val( $options, 'manual_image_ids', '' );
		}

		return $strings;
	}

	/**
	 * Print custom media templates for integrating with Media Manager Plus
	 *
	 * @since   2.2.*
	 * @access  public
	 * @global  object $post WordPress Post
	 * @return mixed
	 */
	public function print_media_templates() {
		global $post;
		if ( isset( $post->post_type ) && $post->post_type == $this->labels['post_type'] ) {
			?>
			<script type="text/html" id="tmpl-attachment-details">
				<h3>
					<?php _e( 'Slide Details', 'nivo-slider' ); ?>
					<span class="settings-save-status">
					<span class="spinner"></span>
					<span class="saved"><?php esc_html_e( 'Saved.', 'nivo-slider' ); ?></span>
				</span>
				</h3>
				<div class="attachment-info">
					<div class="thumbnail">
						<# if ( data.uploading ) { #>
							<div class="media-progress-bar">
								<div></div>
							</div>
							<# } else if ( 'image' === data.type ) { #>
								<img src="{{ data.size.url }}" draggable="false" />
								<# } else { #>
									<img src="{{ data.icon }}" class="icon" draggable="false" />
									<# } #>
					</div>
				</div>
				<# var maybeReadOnly = data.can.save || data.allowLocalEdits ? '' : 'readonly'; #>
					<label class="setting" data-setting="caption">
						<span><?php _e( 'Caption', 'nivo-slider' ); ?></span>
						<textarea {{ maybeReadOnly }}>{{ data.caption }}</textarea>
					</label>
					<# if ( 'image' === data.type ) { #>
						<label class="setting" data-setting="alt">
							<span><?php _e( 'Alt Text', 'nivo-slider' ); ?></span>
							<input type="text" value="{{ data.alt }}" {{ maybeReadOnly }} />
						</label>
						<# } #>
							<label class="setting" data-customsetting="metalink">
								<span><?php _e( 'Link', 'nivo-slider' ); ?></span>
								<input type="text" value="{{ data.metalink }}" {{ maybeReadOnly }} />
							</label>
			</script>
			<script type="text/html" id="tmpl-uberimage-settings">
				<div class="attachment-info">
					<h3>{{{ data.selected_image.title }}}</h3>
					<span id="uberload" class="spinner" style="display: block"></span>
					<input id="full-uber" type="hidden" value="{{ data.selected_image.dataset.full }}" />
					<input id="uber-id" type="hidden" value="{{ data.selected_image.id }}" />

					<div class="thumbnail">
					</div>
				</div>
				<?php if ( ! apply_filters( 'disable_captions', '' ) ) : ?>
					<label class="setting caption">
						<span><?php _e( 'Caption', 'nivo-slider' ); ?></span>
						<textarea id="caption-uber" data-setting="caption"></textarea>
					</label>
				<?php endif; ?>
				<label class="setting alt-text">
					<span><?php _e( 'Title', 'nivo-slider' ); ?></span>
					<input id="title-uber" type="text" data-setting="title" value="{{{ data.selected_image.title }}}" />
					<input name="original-title" type="hidden" value="{{{ data.selected_image.title }}}" />
				</label>
				<label class="setting alt-text">
					<span><?php _e( 'Alt Text', 'nivo-slider' ); ?></span>
					<input id="alt-uber" type="text" data-setting="alt" value="{{{ data.selected_image.title }}}" />
				</label>
			</script>
			<?php
		}
	}

	/**
	 * Upload to the media library and attach images added to [gallery] via Media Manager Plus
	 *
	 * @since   2.2.*
	 * @access  public
	 * @param   array   $response MMP response
	 * @return array $response
	 */
	public function mmp_pre_insert( $response ) {
		if ( isset( $response['fields']['nivo_plugin'] ) && $response['fields']['nivo_plugin'] == $this->labels['post_type'] ) {
			if ( ! isset( $response['fields']['id'] ) ) {
				return $response;
			}
			if ( ! isset( $response['imgsrc'] ) ) {
				return $response;
			}

			$attachment_id = $this->attach_image( $response['imgsrc'], $response['fields']['title'], $response['fields']['postid'], false );
			if ( $attachment_id != '' ) {
				$response['attachment_id'] = $attachment_id;
			}
		}

		return $response;
	}

	/**
	 * Upload to the media library and attach images added to [gallery] via Media Manager Plus
	 *
	 * @since   2.2.*
	 * @access  private
	 * @param   string  $url        external image url
	 * @param   string  $title      external image caption
	 * @param   int     $post_id    ID of [gallery] post to attach it to
	 * @param   bool    $return_src default false. True returns $src, false returns $id
	 * @return mixed
	 */
	private function attach_image( $url, $title, $post_id, $return_src = true ) {
		$tmp  = download_url( $url );
		$file = basename( $url );
		$info = pathinfo( $file );

		$file_name  = ( $title == '' ) ? $file : $title;
		$file_name  = sanitize_file_name( $file_name );
		$file_name  = remove_accents( $file_name );
		$file_name  = $file_title = substr( $file_name, 0, 100 );
		$file_name  = $file_name . '.' . ( isset( $info['extension'] ) ? $info['extension'] : 'jpg' );
		$file_array = array(
			'name'     => $file_name,
			'tmp_name' => $tmp,
		);

		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';

			return '';
		}

		$id = media_handle_sideload( $file_array, $post_id, $file_title );

		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );

			return '';
		}
		if ( $return_src ) {
			$src = wp_get_attachment_url( $id );

			return $src;
		} else {
			return $id;
		}
	}
}
