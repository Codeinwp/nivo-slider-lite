<?php
/**
 * Sample class for PHPUnit.
 *
 * @package     nivo-slider
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, Marius Cristea
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

/**
 * Sample test class.
 */
class Test_Nivo extends WP_UnitTestCase {

	/**
	 * @since   3.0.0
	 * @access  private
	 * @var Nivo_Slider_Admin;
	 */
	private static $pa;

	/**
	 * Initiating Plugin
	 */
	public static function setUpBeforeClass() {
		// Include Plugin
		require_once 'nivo-slider-lite.php';
		$plugin_admin = new Nivo_Slider_Admin( 'nivo-slider', '3.0.0' );
		self::set_plugin_admin( $plugin_admin );
	}

	/**
	 * Utility to set shared plugin_admin instance.
	 *
	 * @since   3.0.0
	 * @access  private
	 *
	 * @param   Nivo_Slider_Admin $plugin_admin Plugin instance.
	 */
	private static function set_plugin_admin( $plugin_admin ) {
		self::$pa = $plugin_admin;
	}

	/**
	 * Test method to check custom post type and taxonomy registration.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function test_nivo_post_type_register() {
		$plugin_model = new Nivo_Core_Model();
		$plugin_model->register();
		$this->assertTrue( post_type_exists( 'nivoslider' ) );
		$this->assertTrue( taxonomy_exists( 'nivo_slider' ) );
	}

	/**
	 * Test method to check slider creation and update.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function test_nivo_post_type_save() {
		$plugin_model = new Nivo_Core_Model();
		// Set Defaults and prepare
		$plugin_admin  = self::$pa;
		$random_name   = $this->get_rand_name();
		$post_type     = $plugin_admin::get_plugin_settings()->get_label( 'post_type' );
		$taxonomy      = $plugin_admin::get_plugin_settings()->get_label( 'taxonomy' );
		$post_meta_key = $plugin_admin::get_plugin_settings()->get_label( 'post_meta_key' );
		$plugin_name   = $plugin_admin::get_plugin_settings()->get_label( 'plugin_name' );
		$user_id       = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		wp_set_current_user( $user_id );
		$p = $this->factory->post->create_and_get( array(
			'post_title'  => $random_name,
			'post_type'   => $post_type,
			'post_author' => $user_id,
		) );
		$settings = array(
			'manual_image_ids' => '139,22',
			'source'           => 'manual',
			'type_gallery'     => '1',
			'type_category'    => '4',
			'enable_captions'  => 'on',
			'number_images'    => '',
			'sizing'           => 'responsive',
			'wp_image_size'    => 'full',
			'dim_x'            => '400',
			'dim_y'            => '150',
			'theme'            => '',
			'effect'           => 'fade',
			'slices'           => '15',
			'boxCols'          => '8',
			'boxRows'          => '4',
			'animSpeed'        => '500',
			'controlNavThumbs' => 'off',
			'thumbSizeWidth'   => '70',
			'thumbSizeHeight'  => '50',
			'pauseTime'        => '3000',
			'startSlide'       => '0',
			'directionNav'     => 'on',
			'controlNav'       => 'on',
			'imageLink'        => 'on',
			'targetBlank'      => 'on',
			'pauseOnHover'     => 'on',
			'manualAdvance'    => 'off',
			'randomStart'      => 'off',
		);
		$_POST[ $post_type . '_noncename' ] = wp_create_nonce( $plugin_name );
		$_POST['post_type']                 = $post_type;
		$_POST[ $post_meta_key ]            = $settings;
		$taxonomy_names = get_object_taxonomies( $p );
		// Test Create
		$this->assertTrue( $plugin_model->save_post( $p->ID ) );
		$post_meta          = get_post_meta( $p->ID );
		$post_meta          = $post_meta[ $post_meta_key ][0];
		$post_meta_settings = unserialize( $post_meta );
		$this->assertEquals( $p->post_title, $random_name );
		$this->assertEquals( $p->post_type, $post_type );
		$this->assertTrue( in_array( $taxonomy, $taxonomy_names ) );
		$this->assertEqualSets( $settings, $post_meta_settings );
		// Test Update
		$settings2                = $settings;
		$settings2['randomStart'] = 'on';
		$_POST[ $post_meta_key ]  = $settings2;
		$this->assertTrue( $plugin_model->save_post( $p->ID ) );
		$post_meta          = get_post_meta( $p->ID );
		$post_meta          = $post_meta[ $post_meta_key ][0];
		$post_meta_settings = unserialize( $post_meta );
		$this->assertEqualSets( $settings2, $post_meta_settings );
	}

	/**
	 * Utility method to generate a random 5 char string.
	 *
	 * @since   3.0.0
	 * @access  private
	 * @return string
	 */
	private function get_rand_name() {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$result     = '';
		for ( $i = 0; $i < 5; $i ++ ) {
			$result .= $characters[ mt_rand( 0, 61 ) ];
		}

		return $result;
	}

	/**
	 * Test method to check settings.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function test_nivo_settings() {
		$plugin_admin = self::$pa;
		$plugin_admin->register_settings();
		$options_key = $plugin_admin::get_plugin_settings()->get_label( 'options_key' );
		$options     = get_option( $options_key );
		global $wp_roles;
		$role_names     = $wp_roles->get_names();
		$input          = array();
		$input['roles'] = 'roles';
		if ( ! isset( $options['custom-roles'] ) ) {
			$defaults = array();
			foreach ( $role_names as $key => $value ) {
				if ( $key != 'administrator' ) {
					$defaults[]    = $key;
					$input[ $key ] = 'on';
				}
			}
			$options['custom-roles']   = $defaults;
			$options['custom-roles'][] = 'administrator';
		}
		$validated = $plugin_admin->settings_validate( $input );
		// Test Settings get validated
		$this->assertEqualSets( $options, $validated );
	}

}
