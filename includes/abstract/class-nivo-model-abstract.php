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
 * The Abstract Model functionality of the plugin.
 *
 * Provides a set of methods to interact with a defined Model
 * for Nivo Post Type.
 *
 * @package    nivo-slider
 * @subpackage nivo-slider/includes/abstract
 * @author     ThemeIsle <friends@themeisle.com>
 */
abstract class Nivo_Model_Abstract extends Nivo_Core_Settings_Abstract {
	public $item;

	/**
	 * @since   3.0.0
	 * @access  protected
	 * @var     $labels     The labels array defined in $plugin_settings.
	 */
	protected $labels;

	/**
	 * Nivo_Model_Abstract constructor.
	 * @since   3.0.0
	 * @access  public
	 */
	public function __construct() {
		$plugin_settings = self::get_plugin_settings();
		$labels          = $plugin_settings->get_labels();
		if ( isset( $plugin_settings ) && ! empty( $labels ) ) {
			$this->labels = $plugin_settings->get_labels();
		}
	}

	/**
	 * Register the custom post type and taxonomy.
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function register() {
		$defaults = $this->labels;
		$labels = array(
			'name'               => '%2$s',
			'singular_name'      => '%1$s',
			'add_new'            => __( 'Add new Slider', 'nivo-slider' ),
			'add_new_item'       => __( 'Add New %1$s', 'nivo-slider' ),
			'edit_item'          => __( 'Edit %1$s', 'nivo-slider' ),
			'new_item'           => __( 'New %1$s', 'nivo-slider' ),
			'all_items'          => __( 'All %2$s', 'nivo-slider' ),
			'view_item'          => __( 'View %1$s', 'nivo-slider' ),
			'search_items'       => __( 'Search %2$s', 'nivo-slider' ),
			'not_found'          => __( 'No %2$s found', 'nivo-slider' ),
			'not_found_in_trash' => __( 'No %2$s found in Trash', 'nivo-slider' ),
			'parent_item_colon'  => '',
			'menu_name'          => '%2$s',
		);
		foreach ( $labels as $key => $value ) {
			$labels[ $key ] = sprintf( $value, $defaults['singular'], $defaults['plural'] );
		}
		$labels = apply_filters( $defaults['post_type'] . '_post_type_labels', $labels );
		$supports = array( 'title', 'thumbnail' );
		$supports = array_merge( $supports, apply_filters( 'nivo_post_type_supports', array() ) );
		$post_type_args = array(
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'menu_position' => apply_filters( $defaults['post_type'] . '_post_type_menu_position', 100 ),
			'supports'      => $supports,
			'menu_icon'     => apply_filters( $defaults['post_type'] . '_post_type_menu_icon', '' ),
			'taxonomies' => array( $defaults['taxonomy'] ),
		);
		register_taxonomy(
			$defaults['taxonomy'],
			$defaults['post_type'],
			array(
				'label'        => $defaults['singular'],
				'public'       => false,
				'rewrite'      => false,
				'hierarchical' => true,
			)
		);
		register_post_type(
			$this->labels['post_type'],
			apply_filters(
				$defaults['post_type'] . '_post_type_args',
				$post_type_args
			)
		);

	}

	/**
	 * Amend the settings of the [gallery] post before saving
	 *
	 * @since    2.2.*
	 * @updated 3.0.0
	 * @access    public
	 *
	 * @param    integer $post_id Post ID.
	 *
	 * @return boolean
	 */
	public function save_post( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		} elseif ( ! isset( $_POST[ $this->labels['post_type'] . '_noncename' ] ) || ! wp_verify_nonce( $_POST[ $this->labels['post_type'] . '_noncename' ], $this->labels['plugin_name'] ) ) {
			return false;
		} elseif ( 'page' == $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id ) ) {
			return false;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		} else {
			// Good to go
			$settings = $_POST[ $this->labels['post_meta_key'] ];
			$settings = apply_filters( $this->labels['post_type'] . '_post_meta_save', $settings );
			update_post_meta( $post_id, $this->labels['post_meta_key'], $settings );
			$taxonomy = ( isset( $_POST['taxonomy'] ) ) ? $_POST['taxonomy'] : 'slider';
			wp_set_object_terms( $post_id, $taxonomy, $this->labels['taxonomy'] );

			return true;
		}
	}

	/**
	 * Custom post-meta settings saving logic.
	 * Inherited from v2.2.*.
	 *
	 * @since   3.0.0
	 * @access  public
	 *
	 * @param   array $settings The settings array.
	 *
	 * @return array
	 */
	public function save_post_meta( $settings ) {
		if ( ! is_numeric( $settings['dim_x'] ) || $settings['dim_x'] <= 0 ) {
			$settings['dim_x'] = 400;
		}
		if ( ! is_numeric( $settings['dim_y'] ) || $settings['dim_y'] <= 0 ) {
			$settings['dim_y'] = 150;
		}
		if ( ! isset( $settings['slices'] ) || ( ! is_numeric( $settings['slices'] ) || $settings['slices'] <= 0 ) ) {
			$settings['slices'] = 15;
		}
		if ( ! isset( $settings['boxCols'] ) || ( ! is_numeric( $settings['boxCols'] ) || $settings['boxCols'] <= 0 ) ) {
			$settings['boxCols'] = 8;
		}
		if ( ! isset( $settings['boxRows'] ) || ( ! is_numeric( $settings['boxRows'] ) || $settings['boxRows'] <= 0 ) ) {
			$settings['boxRows'] = 4;
		}
		if ( ! isset( $settings['animSpeed'] ) || ( ! is_numeric( $settings['animSpeed'] ) || $settings['animSpeed'] <= 0 ) ) {
			$settings['animSpeed'] = 500;
		}
		if ( ! isset( $settings['pauseTime'] ) || ( ! is_numeric( $settings['pauseTime'] ) || $settings['pauseTime'] <= 0 ) ) {
			$settings['pauseTime'] = 3000;
		}
		if ( ! isset( $settings['startSlide'] ) || ( ! is_numeric( $settings['startSlide'] ) || $settings['startSlide'] < 0 ) ) {
			$settings['startSlide'] = 0;
		}
		if ( ! isset( $settings['thumbSizeWidth'] ) || ( ! is_numeric( $settings['thumbSizeWidth'] ) || $settings['thumbSizeWidth'] <= 0 ) ) {
			$settings['thumbSizeWidth'] = 70;
		}
		if ( ! isset( $settings['thumbSizeHeight'] ) || ( ! is_numeric( $settings['thumbSizeHeight'] ) || $settings['thumbSizeHeight'] <= 0 ) ) {
			$settings['thumbSizeHeight'] = 50;
		}

		return $settings;
	}

}
