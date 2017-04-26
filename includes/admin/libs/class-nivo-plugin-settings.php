<?php
/**
 * The admin-view functionality of the plugin.
 *
 * @link       http://themeisle.com/plugins/nivo-slider/
 * @since      3.0.0
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 */

/**
 * The admin-view functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/admin/libs
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Nivo_Plugin_Settings implements Nivo_Settings_Interface {

	/**
	 * The labels shared by all classes.
	 *
	 * @var     array
	 * @since   3.0.0
	 * @access  private
	 */
	private $labels;

	/**
	 * The lite flag shared by all classes.
	 *
	 * @var     boolean
	 * @since   3.0.0
	 * @access  private
	 */
	private $is_lite;

	/**
	 * Utility method to set labels.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   array   $labels The array to assign to labels.
	 */
	public function set_labels( $labels ) {
		$this->labels = $labels;
	}

	/**
	 * Utility method to set is_lite.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @param   boolean $is_lite The value to assign to is_lite.
	 */
	public function set_lite( $is_lite ) {
		$this->is_lite = $is_lite;
	}

	/**
	 * Utility method to get labels.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @return array
	 */
	public function get_labels() {
		return $this->labels;
	}

	/**
	 * Utility method to get specific label item.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_label( $name ) {
		if ( isset( $this->labels[ $name ] ) ) {
			return $this->labels[ $name ];
		} else {
			return '';
		}
	}

	/**
	 * Utility method to value of is_lite.
	 *
	 * @since   3.0.0
	 * @access  public
	 * @return boolean
	 */
	public function get_lite() {
		return $this->is_lite;
	}

}
