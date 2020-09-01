<?php
/**
 * DefTools_Logs Class.
 *
 * @class   DefTools_Logs
 * @version 1.0.0
 * @author  Lafif Astahdziq <hello@lafif.me>
 */

use Monolog\Logger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\SocketHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'DefTools_Logs' ) ) :

	/**
	 * DefTools_Logs class.
	 */
	class DefTools_Logs {


		public $log;

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
			$this->setup_log_channel();

			add_filter( 'deftools/toolbar/submenus', array( $this, 'add_toolbar_submenus' ), 10, 1 );
		}

		public function add_toolbar_submenus( $submenus ) {
			$log_debug_title = sprintf( __( 'Logs: %s', 'deftools' ), ! empty( $this->get_handlers() ) ? 'enabled' : 'disabled' );
			$submenus[]      = array(
				'title' => $log_debug_title,
				'id'    => 'deftools-logs',
				'href'  => '/',
				'meta'  => array( 'target' => '_blank' ),
			);
			return $submenus;
		}

		public function log( $message, $data = array(), $type = 'debug' ) {
			if ( ! method_exists( $this->log, $type ) || ! is_callable( array( $this->log, $type ) ) ) {
				return;
			}

			if ( ! is_array( $data ) ) {
				$data = array( $data );
			}

			// add records to the log
			try {
				$this->log->{$type}( $message, array_filter( $data ) );
			} catch ( \Exception $e ) {
			}
		}

		protected function setup_log_channel() {
			// create a log channel
			$_log     = new Logger( sprintf( 'DeftLog - %s', get_bloginfo( 'name' ) ) );
			$handlers = $this->get_handlers();

			if ( ! empty( $handlers ) ) {
				foreach ( $handlers as $handler ) {
					$_log->pushHandler( $handler );
				}
			}

			$this->log = $_log;
		}

		protected function get_handlers() {
			$handlers = array();

			if ( DEFTOOLS_LOG_ENABLE_SOCKET_HANDLER ) {
				$socket_handler = new SocketHandler( DEFTOOLS_LOG_SOCKET_URL );
				$socket_handler->setPersistent( false ); // Set true to persistent connection.
				$socket_handler->setFormatter( new JsonFormatter() );
				$handlers['socket'] = $socket_handler;
			}

			// $handlers['stream'] = $this->log->pushHandler(new StreamHandler( DEFTOOLS_LOG_DIR . 'deftlog.log', Logger::WARNING));
			// $handlers['chrome'] = $this->log->pushHandler(new ChromePHPHandler(Logger::DEBUG));

			return apply_filters( 'deftools/log/handlers', $handlers );
		}
	}

endif;
