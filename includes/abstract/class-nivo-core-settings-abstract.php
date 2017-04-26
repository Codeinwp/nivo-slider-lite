<?php
/**
 * The Abstract Model functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/abstract
 */

/**
 * The Abstract Core Settings functionality of the plugin.
 *
 * Provides a set of methods to interact with the core settings
 * defined during initialization.
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/abstract
 * @author     ThemeIsle <friends@themeisle.com>
 */
abstract class Nivo_Core_Settings_Abstract {
	/**
	 * All of the specific plugin data
	 *
	 * @since   3.0.0
	 * @access  protected
	 * @var     Nivo_Settings_Interface $plugin_settings   Store shared plugin settings.
	 */
	protected static $plugin_settings;

	/**
	 * Utility method to define the plugin settings.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   Nivo_Settings_Interface     $plugin_settings    The plugin settings object.
	 */
	public static function set_plugin_settings( $plugin_settings ) {
		self::$plugin_settings = $plugin_settings;
	}

	/**
	 * Utility method to retrieve plugin settings.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @return Nivo_Settings_Interface
	 */
	public static function get_plugin_settings() {
		return self::$plugin_settings;
	}
}
