<?php
/**
 *
 * Drop-in file for optml recommendation.
 */

/**
 * Class Nivo_Slider_Optml.
 */
class Nivo_Slider_Optml {

	/**
	 * @var null Instance object.
	 */
	protected static $instance = null;
	/**
	 * Dismiss option key.
	 *
	 * @var string Dismiss option key.
	 */
	protected static $dismiss_key = 'optml_upsell_off';

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Drop-in actions
	 */
	public function init() {
		add_action( 'admin_notices', array( $this, 'admin_notice' ), defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : 99999 );
		add_action( 'admin_init', array( $this, 'remove_notice' ), defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : 99999 );
	}

	/**
	 * Remove notice;
	 */
	public function remove_notice() {
		if ( ! isset( $_GET['optml_upsell'] ) ) {
			return;
		}
		if ( $_GET['optml_upsell'] !== 'yes' ) {
			return;
		}
		if ( ! isset( $_GET['remove_upsell'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_GET['remove_upsell'], 'remove_upsell_confirmation' ) ) {
			return;
		}
		update_option( self::$dismiss_key, 'yes' );
	}

	/**
	 * Add notice.
	 */
	public function admin_notice() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( is_network_admin() ) {
			return;
		}
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'optimole-wp/optimole-wp.php' ) ) {
			return;
		}
		$screen = get_current_screen();
		static $parent_whitelist = array(
			'edit.php?post_type=nivoslider' => true,
			'upload.php'                    => true,
		);
		static $base_whitelist = array(
			'upload' => true,
		);
		if (
			( ! isset( $screen->parent_file )
			  || ! isset( $parent_whitelist[ $screen->parent_file ] )
			) &&
			( ! isset( $screen->base )
			  || ! isset( $base_whitelist[ $screen->base ] )
			)
		) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( get_option( self::$dismiss_key, 'no' ) === 'yes' ) {
			return;
		}
		?>
		<style type="text/css">



			.post-type-nivoslider #wpbody-content > *:not(#screen-meta):not(#screen-meta-links):not(.wrap):not(#pro-features),
			.upload-php #wpbody-content > *:not(#screen-meta):not(#screen-meta-links):not(.wrap):not(#pro-features),
			.media-new-php #wpbody-content > *:not(#screen-meta):not(#screen-meta-links):not(.wrap):not(#pro-features) {
				display: none;
			}

			#wpbody-content .optimole-notice-upsell .notice-dismiss {
				text-decoration: none;
			}

			#wpbody-content .optimole-notice-upsell {
				margin-top: 20px;
				display: flex !important;
				padding-right: 40px;
				position: relative;
				flex-wrap: wrap;
			}

			.nivoslider_page * #wpbody-content .optimole-notice-upsell ~ * {
				display: none;
			}

			.nivoslider_page * #wpbody-content #pro-features {
				display: block;
			}

			.optimole-notice-upsell p a {
				text-decoration: none;
			}

			.optimole-notice-upsell .optml-logo {
				background: url("<?php echo esc_url( NIVO_SLIDER_PLUGIN_URL . 'assets/images/optimole-logo.png' ); ?>");
				flex: 0 0 60px;
				width: 60px;
				height: 60px;
				background-size: cover;
				display: block;
				margin-right: 10px;
			}
			a.button.optml-upsell-try {
				padding: 0 10px 1px !important;
				margin-top:10px;
				width: 100px;
				flex: 0 0 100px;
			}
			.optml-upsell-text{
				flex: 1;
			}
			@media (max-width: 800px) {
				a.button.optml-upsell-try{
					width:100%;
					flex: 0 0 100%;
					padding:5px !important;
					text-align: center;
				}
			}
		</style>
		<div class="notice notice-success  optimole-notice-upsell">
			<div class="optml-logo"></div>
			<p class="optml-upsell-text">Improve your website loading speed by up to 2 seconds using <strong><a href="http://optimole.com"
			                                                                          target="_blank">Optimole - Image
						Optimization Service</a></strong>. <br/>Optimole compress and delivers in average 70% smaller
				images and is fully integrated with <strong> Nivo Slider</strong>. Try for out for <strong>free</strong>
				or get a 40% early adopter discount with this code: <code>NIVOEARLY40</code>
			</p>
			<?php echo wp_kses_post( $this->get_the_right_cta() ) ?>
			<div class="clear"></div>

			<a href="<?php echo wp_nonce_url( add_query_arg( array( 'optml_upsell' => 'yes' ) ), 'remove_upsell_confirmation', 'remove_upsell' ) ?>"
			   class=" notice-dismiss"><span
						class="screen-reader-text">Dismiss this notice.</span></a>
		</div>
		<?php
	}

	public function get_the_right_cta() {

		$query_images_args = array(
			'post_type'              => 'attachment',
			'post_mime_type'         => 'image',
			'post_status'            => 'inherit',
			'posts_per_page'         => 11,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		);
		$image_check       = new WP_Query( $query_images_args );
		if ( count( $image_check->posts ) < 5 ) {
			return $this->get_plugin_install_btn();
		}
		$site_url = get_site_url();
		$site_url = parse_url( $site_url );
		if ( ! isset( $site_url['host'] ) ) {
			return $this->get_plugin_install_btn();
		}
		if ( ! $this->check_website_online( $site_url['host'] ) ) {
			return $this->get_plugin_install_btn();
		}

		return $this->get_test_button();
	}

	/**
	 * @return string Plugin install code.
	 */
	public function get_plugin_install_btn() {
		add_thickbox();

		return '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=optimole-wp&TB_iframe=true&width=772&height=458' ) . '" target="_blank" class=" thickbox open-plugin-details-modal button optml-upsell-try button-primary"><span class="dashicons dashicons-external"></span> Try out</a>';
	}

	/**
	 * Check if website is online.
	 *
	 * @param string $site Site to check.
	 *
	 * @return bool Website online?
	 */
	public function check_website_online( $site ) {

		$cache_key = '_optml_cache_on' . basename( __FILE__ );
		if ( ( $cached_data = get_transient( $cache_key ) ) !== false ) {

			return ( $cached_data === 'yes' );
		}
		$response = wp_remote_get( sprintf( 'https://downforeveryoneorjustme.com/%s', $site ) );
		if ( is_wp_error( $response ) ) {
			set_transient( $cache_key, 'no', DAY_IN_SECONDS );

			return false;
		}
		if ( strpos( wp_remote_retrieve_body( $response ), 'is up.' ) === false ) {
			set_transient( $cache_key, 'no', DAY_IN_SECONDS );

			return false;
		}
		set_transient( $cache_key, 'yes', DAY_IN_SECONDS );

		return true;
	}

	/**
	 * @return string Test button code.
	 */
	public function get_test_button() {
		return '<a href="https://speedtest.optimole.com/?url=' . esc_url( get_site_url() ) . '" target="_blank" class="button optml-upsell-try button-primary"><span class="dashicons dashicons-external"></span> Test your site</a>';
	}

	/**
	 * Deny clone.
	 */
	public function __clone() {
	}

	/**
	 * Deny un-serialize.
	 */
	public function __wakeup() {

	}

}
