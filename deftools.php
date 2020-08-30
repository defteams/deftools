<?php
/**
 * Plugin Name: DefTools
 * Description: Defteams debugging tool
 * Author: Lafif Astahdziq
 * Author URI: https://lafif.me
 * Author Email: hello@lafif.me
 * Version: 1.0.0
 * Text Domain: deft
 * Domain Path: /languages/
 */




if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'DefTools' ) ) :

	/**
	 * Main DefTools Class
	 *
	 * @class DefTools
	 * @version 1.0.0
	 */
	final class DefTools {

		/**
		 * Text domain
		 * @var string
		 */
		const TEXT_DOMAIN = 'deftools';

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '1.0.0';

		/**
		 * Version of scripts.
		 *
		 * @var string A date in the format: YYYYMMDD
		 */
		const SCRIPT_VERSION = '00000000';

		/**
		 * Version of database schema.
		 *
		 * @var string A date in the format: YYYYMMDD
		 */
		const DB_VERSION = '00000000';

		/**
		 * Plugin capability
		 * @var string
		 */
		const CAPABILITY = 'manage_options';

		/**
		 * The absolute path to this plugin's directory.
		 *
		 * @since 1.0.0
		 *
		 * @var   string
		 */
		private $directory_path;

		/**
		 * The URL of this plugin's directory.
		 *
		 * @since 1.0.0
		 *
		 * @var   string
		 */
		private $directory_url;

		/**
		 * Store of registered objects.
		 */
		private $registry;

		/**
		 * A placeholder to hold the file iterator so that directory traversal is only
		 * performed once.
		 */
		private $file_iterator;

		/**
		 * Main DefTools Instance
		 *
		 * Ensures only one instance of DefTools is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return DefTools - Main instance
		 */
		public static function instance() {
			static $instance = false;

			if ( ! $instance ) {
				$instance = new self();
			}

			return $instance;
		}

		/**
		 * DefTools Constructor.
		 */
		public function __construct() {

			$this->directory_path = plugin_dir_path( __FILE__ );
			$this->directory_url  = plugin_dir_url( __FILE__ );

			$this->define_constants();

			$this->includes();

			$this->init_hooks();
		}

		/**
		 * Define required constants that can be override on wp-config
		 */
		private function define_constants() {
			$upload_dir = wp_upload_dir( null, false );
			$this->define( 'DEFTOOLS_LOG_DIR', $upload_dir['basedir'] . '/deftools-logs/' );
			$this->define( 'DEFTOOLS_LOG_SOCKET_URL', 'tcp://127.0.0.1:8888' );
			$this->define( 'DEFTOOLS_LOG_ENABLE_SOCKET_HANDLER', false );

			// GIT
			$this->define( 'DEFTOOLS_GIT_DIRS', '' );

			// EMAILS
			$this->define( 'DEFTOOLS_EMAIL_DEBUG', '' );
			$this->define( 'DEFTOOLS_EMAIL_ENABLE_SMTP', false );
			$this->define( 'DEFTOOLS_EMAIL_SET_RETURN_PATH', false ); // Sets $phpmailer->Sender if true
			$this->define( 'DEFTOOLS_EMAIL_SMTP_HOST', 'smtp.gmail.com' ); // The SMTP mail host
			$this->define( 'DEFTOOLS_EMAIL_SMTP_PORT', 587 ); // TLS : 587 | SSL : 465
			$this->define( 'DEFTOOLS_EMAIL_SSL', 'tls' ); // Possible values '', 'ssl', 'tls' - note TLS is not STARTTLS
			$this->define( 'DEFTOOLS_EMAIL_SMTP_AUTH', true ); // True turns on SMTP authentication, false turns it off
			$this->define( 'DEFTOOLS_EMAIL_SMTP_USER', '' ); // SMTP authentication username, only used if TEST_SMTP_AUTH is true
			$this->define( 'DEFTOOLS_EMAIL_SMTP_PASS', '' ); // SMTP authentication password, only used if TEST_SMTP_AUTH is true
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {

			/**
			 * DEPENDENCIES
			 */
			if ( file_exists( $this->get_path( 'libraries' ) . 'autoload.php' ) ) {
				include_once( $this->get_path( 'libraries' ) . 'autoload.php' );
			}

			/**
			 * CLI
			 */
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once( $this->get_path() . '/cli/deft-cli.php' );
			}

			spl_autoload_register( array( $this, 'autoloader' ) );

			include_once( $this->get_path( 'includes' ) . 'functions.php' );
			include_once( $this->get_path( 'includes' ) . 'class-deftools-registry.php' );
		}

		/**
		 * Hook into actions and filters
		 * @since  1.0.0
		 */
		private function init_hooks() {

			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			add_action( 'plugins_loaded', array( $this, 'start' ), 1 );
		}

		/**
		 * Run the startup sequence.
		 *
		 * This is only ever executed once.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function start() {

			/* If we've already started (i.e. run this function once before), do not pass go. */
			if ( did_action( 'deftools_start' ) || current_filter() == 'deftools_start' ) {
				return;
			}

			$this->registry();

			/**
			 * We do this on priority 20 so that any functionality that is loaded on init (such
			 * as addons) has a chance to run before the event.
			 */
			add_action( 'init', array( $this, 'do_deftools_actions' ), 20 );

			do_action( 'deftools_start', $this );
		}

		/**
		 * Setup main registry
		 *
		 * @since  1.0.0
		 */
		public function registry() {

			if ( ! isset( $this->registry ) ) {
				$this->registry = new DefTools_Registry();

				$this->registry->register_object( DefTools_Toolbar::instance() );
				$this->registry->register_object( DefTools_Logs::instance() );
				$this->registry->register_object( DefTools_Email::instance() );
				$this->registry->register_object( DefTools_User::instance() );
				$this->registry->register_object( DefTools_Git::instance() );

				/**
				 * @dev-note
				 * Load classes that only needed in admin screens (wp-admin)
				 */
				if ( $this->is_request( 'admin' ) ) {
					$this->registry->register_object( DefTools_Admin::instance() );
				}

				if ( $this->is_request( 'ajax' ) ) {
					// $this->registry->register_object( DefTools_Ajax::instance() );
				}

				if ( $this->is_request( 'frontend' ) ) {

				}
			}

			return $this->registry;
		}

		/**
		 * If a deftools_action event is triggered, delegate the event using do_action.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function do_deftools_actions() {
			/**
			 * @dev-note
			 * Let's say you have a form somewhere in a module,
			 * you can add a input type hidden with name `deftools_action` and a meaningfull value like `save_form`
			 * then you will need to add an action `deftools_save_form` to handle the form submission
			 */
			if ( isset( $_REQUEST['deftools_action'] ) ) {

				$action = $_REQUEST['deftools_action'];

				/**
				 * Handle DefTools action.
				 *
				 * @since 1.0.0
				 */
				do_action( 'deftools_' . $action );
			}
		}

		/**
		 * Shortcut to get registered object
		 * @param  [type] $class_key [description]
		 * @return [type]            [description]
		 */
		public function get( $class_key ) {
			return $this->registry()->get( $class_key );
		}

		/**
		 * Shortcut to register object
		 * @param  [type] $class_key [description]
		 * @return [type]            [description]
		 */
		public function register( $class_key ) {
			return $this->registry()->register_object( $class_key );
		}

		/**
		 * Dynamically loads the class attempting to be instantiated elsewhere in the
		 * plugin by looking at the $class_name parameter being passed as an argument.
		 *
		 * @param  string $class_name The fully-qualified name of the class to load.
		 * @return boolean
		 */
		public function autoloader( $class_name ) {
			/* If the specified $class_name already exists, bail. */
			if ( class_exists( $class_name ) ) {
				return false;
			}

			/* If the specified $class_name does not include our namespace, duck out. */
			if ( false === strpos( $class_name, 'DefTools_' ) ) {
				return false;
			}

			$directory = new RecursiveDirectoryIterator( $this->get_path( 'includes' ), RecursiveDirectoryIterator::SKIP_DOTS );

			if ( ! isset( $this->file_iterator ) ) {
				$this->file_iterator = new RecursiveIteratorIterator( $directory, RecursiveIteratorIterator::LEAVES_ONLY );
			}

			$filename = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';
			foreach ( $this->file_iterator as $file ) {
				// use strpos to autoload abstract-class-*
				if ( strpos( $file->getFilename(), $filename ) !== false ) {
					if ( $file->isReadable() ) {
						include_once $file->getPathname();
					}
					return true;
					break;
				}
			}

			return false;
		}

		/**
		 * All install stuff
		 * @return [type] [description]
		 */
		public function activate( $network_wide = false ) {
			/**
			 * @dev-note
			 * Do something when plugin activated
			 * i.e create table, create user role
			 * if the process complex enough, then you can package is as a new class like `DefTools_Install`
			 * and then just call `new DefTools_Install()` here
			 */
		}

		/**
		 * All uninstall stuff
		 * @return [type] [description]
		 */
		public function deactivate() {
			/**
			 * @dev-note
			 * Do something when plugin deactivated
			 * i.e remove created table, remove user role, cleanup database
			 * if the process complex enough, then you can package is as a new class like `DefTools_Uninstall`
			 * and then just call `new DefTools_Uninstall()` here
			 */

		}

		/**
		 * Define constant if not already set
		 * @param  string $name
		 * @param  string|bool $value
		 */
		public function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * What type of request is this?
		 * string $type ajax, frontend or admin
		 * @return bool
		 */
		public function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Returns various plugin paths or urls
		 * @param  string  $type          type of path
		 * @param  boolean $use_url       return as url or as absolute path
		 * @return string                 path / url to the desired type
		 */
		public function get_path( $type = '', $use_url = false ) {

			$base = $use_url ? $this->directory_url : $this->directory_path;

			switch ( $type ) {
				case 'includes':
					$path = $base . 'includes/';
					break;

				case 'cli':
					$path = $base . 'cli/';
					break;

				case 'libraries':
					$path = $base . 'includes/libraries/';
					break;

				case 'abstracts':
					$path = $base . 'includes/abstracts/';
					break;

				case 'admin':
					$path = $base . 'includes/admin/';
					break;

				case 'admin_view':
					$path = $base . 'includes/admin/views/';
					break;

				case 'dist':
					$path = $base . 'dist/';
					break;

				case 'docs':
					$path = $base . 'docs/';
					break;

				case 'templates':
					$path = $base . 'templates/';
					break;

				default:
					$path = $base;
					break;

			}//end switch

			return $path;
		}

		/**
		 * Get Ajax URL.
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}
	}

endif;

// boot up
DefTools::instance();
