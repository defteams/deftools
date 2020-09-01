<?php
/**
 * DefTools_Toolbar Class.
 *
 * @class   DefTools_Toolbar
 * @version 1.0.0
 * @author  Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'DefTools_Toolbar' ) ) :

	/**
	 * DefTools_Toolbar class.
	 */
	class DefTools_Toolbar {


		public $toolbar_id = 'deftools-admin-bar';

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
			add_action( 'admin_bar_menu', array( $this, 'add_toolbar_items' ), 100, 1 );
		}

		public function add_toolbar_items( $wp_admin_bar ) {
			if ( ! current_user_can( DefTools::CAPABILITY ) ) {
				return;
			}

			$subs = apply_filters( 'deftools/toolbar/submenus', array() );
			if ( empty( $subs ) ) {
				return;
			}

			$wp_admin_bar->add_menu(
				array(
					'id'    => $this->toolbar_id,
					'title' => __( 'Deftools', 'deftools' ),
					'href'  => '#',
				)
			);

			foreach ( $subs as $sub_toolbar ) {
				$sub_toolbar['parent'] = $this->toolbar_id;
				$wp_admin_bar->add_menu( $sub_toolbar );
			}
		}
	}

endif;
