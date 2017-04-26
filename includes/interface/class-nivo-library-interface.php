<?php
/**
 * The library interface.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/interface
 */

/**
 * The library interface.
 *
 * Defines the interface required for all the library classes.
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/interface
 * @author     ThemeIsle <friends@themeisle.com>
 */
interface Nivo_Library_Interface {
	/**
	 * Use this instead of overriding __construct
	 *
	 * @since   3.0.0
	 */
	function after_core_construct();
}
