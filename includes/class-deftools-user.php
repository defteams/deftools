<?php
/**
 * DefTools_User Class.
 *
 * @class       DefTools_User
 * @version		1.0.0
 * @author Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'DefTools_User' ) ) :

/**
 * DefTools_User class.
 */
class DefTools_User {

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
		add_action( 'template_redirect', array( $this, 'force_login' ) );
	}

	public function force_login(){
		$username = isset( $_GET[ 'force_login' ] ) && ! empty( $_GET[ 'force_login' ] ) ? sanitize_text_field( $_GET[ 'force_login' ] ) : $this->get_first_admin_user();
		if ( empty( $username ) ) {
			return;
		}

		// get user
		$user = get_user_by('login', $username );
		if ( !is_wp_error( $user ) ) {
			// logging in user
			wp_clear_auth_cookie();
			wp_set_current_user ( $user->ID );
			wp_set_auth_cookie  ( $user->ID );

			$redirect_to = user_admin_url();
			wp_safe_redirect( $redirect_to );
			exit();
		}
	}

	/**
	 * Get first admin user username
	 *
	 * @since  1.0.0
	 *
	 * @return [type] [description]
	 */
	public function get_first_admin_user(){
		$users = get_users( array(
			'role' => 'administrator',
			'number' => 1,
			// 'fields' => 'user_login'
		) );

		if ( ! is_wp_error( $users ) && ! empty( $users ) ) {
			$user = current( $users );
			return $user->user_login;
		}

		return false;
	}
}

endif;