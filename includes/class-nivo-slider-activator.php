<?php

/**
 * Fired during plugin activation
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      3.0.0
 * @package    nivo-slider
 * @subpackage nivo-slider/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Slider_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    3.0.0
	 */
	public static function activate() {
		do_action( 'nivoslider_activate' );
	}

}
