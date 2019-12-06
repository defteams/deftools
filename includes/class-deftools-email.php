<?php
/**
 * DefTools_Email Class.
 *
 * @class       DefTools_Email
 * @version		1.0.0
 * @author Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'DefTools_Email' ) ) :

/**
 * DefTools_Email class.
 */
class DefTools_Email {

	/**
     * Singleton method
     *
     * @return self
     */
	public static function instance(){
		static $instance = false;

		if( ! $instance ){
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Constructor
	 */
	public function __construct(){
		add_action('deftools/toolbar/submenus', array( $this, 'add_toolbar_submenus' ), 10, 1);
	}

	public function add_toolbar_submenus( $submenus ){
		$email_debug_title = sprintf( __( 'Email debug: %s', DefTools::TEXT_DOMAIN ), 'lafifastahdziq@gmail.com' );
		$submenus[] = array( 'title' => $email_debug_title, 'id' => 'deftools-email-debug', 'href' => '/', 'meta' => array('target' => '_blank') );
		return $submenus;
	}
}

endif;