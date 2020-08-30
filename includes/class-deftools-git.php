<?php
/**
 * DefTools_Git Class.
 *
 * @class   DefTools_Git
 * @version 1.0.0
 * @author  Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'DefTools_Git' ) ) :

	/**
	 * DefTools_Git class.
	 */
	class DefTools_Git {


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
			if ( empty( DEFTOOLS_GIT_DIRS ) ) {
				return;
			}

			add_filter( 'deftools/toolbar/submenus', array( $this, 'add_toolbar_submenus' ), 10, 1 );
		}

		public function add_toolbar_submenus( $submenus ) {
			$branches = $this->get_tracked_branches();
			if ( empty( $branches ) ) {
				return $submenus;
			}

			$git_submenu = array();
			if ( count( $branches ) > 1 ) {
				$git_submenu = array(
					'title' => __( 'Git Info', DefTools::TEXT_DOMAIN ),
					'id'    => 'deftools-git-info',
					'href'  => '#',
				);
			}

			foreach ( $branches as $location => $branch ) {
				if ( count( $branches ) > 1 ) {
					$git_title             = sprintf( '%s : %s', $location, $branch );
					$git_submenu['subs'][] = array(
						'title' => $git_title,
						'id'    => 'deftools-git-info',
						'href'  => '#',
					);
				} else {
					$git_title   = sprintf( __( 'Git Info: %1$s:%2$s', DefTools::TEXT_DOMAIN ), $location, $branch );
					$git_submenu = array(
						'title' => $git_title,
						'id'    => 'deftools-git-info',
						'href'  => '#',
					);
				}
			}

			$submenus[] = $git_submenu;

			return $submenus;
		}

		protected function get_tracked_branches() {
			$branches = array();

			$_locations = array_map( 'trim', explode( ',', DEFTOOLS_GIT_DIRS ) );
			if ( ! empty( $_locations ) ) {
				foreach ( $_locations as $location ) {
					$head_file = trailingslashit( ABSPATH . $location ) . '.git/HEAD';
					if ( ! file_exists( $head_file ) ) {
						   continue;
					}
					$line                  = file_get_contents( $head_file );
					$_exp                  = explode( '/', $line );
					$branch                = array_pop( $_exp );
					$issue                 = explode( '--', $branch );
					$branches[ $location ] = current( $issue );
				}
			}

			return $branches;
		}
	}

endif;
