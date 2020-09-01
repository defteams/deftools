<?php
/**
 * DefTools_Admin_Notices Class.
 *
 * @class       DefTools_Admin_Notices
 * @version     1.0.0
 * @author Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'DefTools_Admin_Notices' ) ) :

	/**
	 * DefTools_Admin_Notices
	 *
	 * @since 1.0.0
	 */
	class DefTools_Admin_Notices {

		/**
		 * The array of notices.
		 *
		 * @since 1.0.0
		 *
		 * @var   array
		 */
		protected $notices;

		/**
		 * Create class object. A private constructor, so this is used in a singleton context.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->load_notices();

			add_action( 'admin_notices', array( $this, 'render' ) );
			add_action( 'shutdown', array( $this, 'shutdown' ) );
		}

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
		 * Adds a notice message.
		 *
		 * @since   1.0.0
		 *
		 * @param   string $message
		 * @param   string $type
		 * @param   string $key     Optional. If not set, next numeric key is used.
		 * @return  void
		 */
		public function add_notice( $message, $type, $key = false, $dismissible = true ) {
			if ( false === $key ) {

				$this->notices[ $type ][] = array(
					'message'     => $message,
					'dismissible' => $dismissible,
				);

			} else {

				$this->notices[ $type ][ $key ] = array(
					'message'     => $message,
					'dismissible' => $dismissible,
				);

			}
		}

		/**
		 * Adds an error message.
		 *
		 * @since   1.0.0
		 *
		 * @param   string $message
		 * @param   string $key     Optional. If not set, next numeric key is used.
		 * @return  void
		 */
		public function add_error( $message, $key = false, $dismissible = false ) {
			$this->add_notice( $message, 'error', $key, $dismissible );
		}

		/**
		 * Adds a warning message.
		 *
		 * @since   1.0.0
		 *
		 * @param   string $message
		 * @param   string $key     Optional. If not set, next numeric key is used.
		 * @return  void
		 */
		public function add_warning( $message, $key = false, $dismissible = false ) {
			$this->add_notice( $message, 'warning', $key, $dismissible );
		}

		/**
		 * Adds a success message.
		 *
		 * @since   1.0.0
		 *
		 * @param   string $message
		 * @param   string $key     Optional. If not set, next numeric key is used.
		 * @return  void
		 */
		public function add_success( $message, $key = false, $dismissible = false ) {
			$this->add_notice( $message, 'success', $key, $dismissible );
		}

		/**
		 * Adds an info message.
		 *
		 * @since   1.0.0
		 *
		 * @param   string $message
		 * @param   string $key     Optional. If not set, next numeric key is used.
		 * @return  void
		 */
		public function add_info( $message, $key = false, $dismissible = false ) {
			$this->add_notice( $message, 'info', $key, $dismissible );
		}

		/**
		 * Adds a version update message.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $message
		 * @param   string  $key         Optional. If not set, next numeric key is used.
		 * @param   boolean $dismissible Optional. Set to true by default.
		 * @return  void
		 */
		public function add_version_update( $message, $key = false, $dismissible = true ) {
			$this->add_notice( $message, 'version', $key, $dismissible );
		}

		/**
		 * Render notices.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function render() {

			foreach ( $this->get_notices() as $type => $notices ) {

				foreach ( $notices as $key => $notice ) {
					$this->render_notice( $notice['message'], $type, $notice['dismissible'], $key );
				}
			}
		}

		/**
		 * Render a notice.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $notice
		 * @param   string  $type
		 * @param   boolean $dismissible
		 * @param   string  $notice_key
		 * @return  void
		 */
		public function render_notice( $notice, $type, $dismissible = false, $notice_key = '' ) {

			$class = 'notice deftools-notice';

			switch ( $type ) {
				case 'error':
					$class .= ' notice-error';
					break;

				case 'warning':
					$class .= ' notice-warning';
					break;

				case 'success':
					$class .= ' updated';
					break;

				case 'info':
					$class .= ' notice-info';
					break;

				case 'version':
					$class .= ' deftools-upgrade-notice';
					break;
			}

			if ( $dismissible ) {
				$class .= ' is-dismissible';
			}

			printf(
				'<div class="%s" %s><p>%s</p></div>',
				esc_attr( $class ),
				strlen( $notice_key ) ? 'data-notice="' . esc_attr( $notice_key ) . '"' : '',
				$notice
			);

			if ( strlen( $notice_key ) ) {
				unset( $this->notices[ $type ][ $notice_key ] );
			}

		}

		/**
		 * Return all notices as an array.
		 *
		 * @since  1.0.0
		 *
		 * @return array
		 */
		public function get_notices() {
			return $this->notices;
		}

		/**
		 * When PHP finishes executing, stash any notices that haven't been rendered yet.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function shutdown() {
			set_transient( 'deftools_notices', $this->notices );
		}

		/**
		 * Load the notices array.
		 *
		 * If there are any stuffed in a transient, pull those out. Otherwise, reset a clear array.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function load_notices() {
			$this->notices = get_transient( 'deftools_notices' );

			if ( ! is_array( $this->notices ) ) {
				$this->clear();
			}
		}

		/**
		 * Clear out all existing notices.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function clear() {
			$clear = array(
				'error'   => array(),
				'warning' => array(),
				'success' => array(),
				'info'    => array(),
				'version' => array(),
			);

			$this->notices = $clear;
		}
	}

endif;
