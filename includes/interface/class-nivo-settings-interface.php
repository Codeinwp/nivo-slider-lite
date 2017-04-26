<?php
/**
 * The settings interface.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/interface
 */

/**
 * The settings interface.
 *
 * Defines the interface required for settings class.
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/interface
 * @author     ThemeIsle <friends@themeisle.com>
 */
interface Nivo_Settings_Interface {
	function set_labels( $labels );
	function get_labels();
	function get_label( $name );
	function set_lite( $is_lite );
	function get_lite();
}
