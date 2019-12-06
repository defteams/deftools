<?php
/**
 * DefTools_Logs Class.
 *
 * @class       DefTools_Logs
 * @version		1.0.0
 * @author Lafif Astahdziq <hello@lafif.me>
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

		// create a log channel
		$this->log = new Logger( sprintf( 'DeftLog - %s', get_bloginfo( 'name' ) ) );
		$this->setup_handler();
	}

	public function add_toolbar_submenus( $submenus ){
		$email_debug_title = sprintf( __( 'Logs: %s', DefTools::TEXT_DOMAIN ), 'enabled' );
		$submenus[] = array( 'title' => $email_debug_title, 'id' => 'deftools-logs', 'href' => '/', 'meta' => array('target' => '_blank') );
		return $submenus;
	}

	public function log( $message, $data = array(), $type = 'debug' ){
		if ( ! method_exists( $this->log, $type ) || ! is_callable( array( $this->log, $type ) ) ) {
			return;
		}

		if ( ! is_array( $data ) ) {
			$data = array( $data );
		}

		// add records to the log
		try {
			$this->log->{$type}( $message, array_filter( $data ) );
		} catch (\Exception $e) {}
	}

	protected function setup_handler() {
		// setup handler
		// $this->log->pushHandler(new StreamHandler( DEFTOOLS_LOG_DIR . 'deftlog.log', Logger::WARNING));
		// $this->log->pushHandler(new ChromePHPHandler(Logger::DEBUG));

		// Create the handler
		$handler = new SocketHandler( DEFTOOLS_LOG_SOCKET_URL );
		$handler->setPersistent(false); // Set true to persistent connection.
		$handler->setFormatter(new JsonFormatter());
		$this->log->pushHandler($handler);

		do_action( 'deftools_after_setup_log_handler', $this );
	}
}

endif;