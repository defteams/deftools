<?php
/**
 * DefTools_Admin Class.
 *
 * @class       DefTools_Admin
 * @version     1.0.0
 * @author Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * DefTools_Admin class.
 */
class DefTools_Admin {

	/**
	 * Singleton method
	 *
	 * @return self
	 */
	public static function instance() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'deftools_start', array( $this, 'includes' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 10 );
	}

	public function add_admin_menu() {
		$settings_screen_id = add_options_page( 'Deftools', 'Deftools', DefTools::CAPABILITY, 'deftools-settings', array( DefTools_Admin_Settings::instance(), 'render_page' ) );
		add_action( 'load-' . $settings_screen_id, array( DefTools_Admin_Settings::instance(), 'onload_admin_page' ) );
	}

	/**
	 * Include and register all objects / classes related to this module / class
	 *
	 * @return void
	 */
	public function includes() {
		require_once( deftools()->get_path( 'admin' ) . 'functions.php' );
		// deftools()->register( DefTools_Admin_Pages::instance() );
		// deftools()->register( DefTools_Admin_Notices::instance() );
		deftools()->register( DefTools_Admin_Settings::instance() );
	}
}
