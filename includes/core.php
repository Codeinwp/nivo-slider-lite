<?php
/**
 * Dev7studios Core
 *
 * @package     Plugin Core
 * @subpackage  Core
 * @copyright   Copyright (c) 2014, Dev7studios
 * @license     http://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Core Class
 *
 * @since 2.4.9
 */
abstract class Dev7_Core {

	/**
	 * All of the specific plugin data
	 *
	 * @var object
	 * @access protected
	 * @since  2.2
	 */
	protected $labels;

	/**
	 * Is this plugin a "lite" plugin
	 *
	 * @var bool
	 * @access protected
	 * @since  2.4.8
	 */
	protected $is_lite;

	/**
	 * Main construct
	 *
	 * @since 2.2
	 *
	 * @param array $labels Specific plugin label data
	 * @param bool $is_lite Is this a "lite" plugin
	 */
	public function __construct( $labels, $is_lite = false ) {
		$this->labels  = (object) $labels;
		$this->is_lite = $is_lite;

		$this->core_init();
	}

	/**
	 * Use this instead of overriding __construct
	 */
	protected function core_init()
	{
		//
	}
}