<?php
/**
 * DefTools_Email Class.
 *
 * @class   DefTools_Email
 * @version 1.0.0
 * @author  Lafif Astahdziq <hello@lafif.me>
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
			$this->debug_email = ! empty( DEFTOOLS_EMAIL_DEBUG ) ? DEFTOOLS_EMAIL_DEBUG : deftools_get_option( 'debug_email', false );
			add_filter( 'deftools/toolbar/submenus', array( $this, 'add_toolbar_submenus' ), 10, 1 );

			add_action( 'phpmailer_init', array( $this, 'change_phpmailer' ), 10, 1 );
			add_filter( 'wp_mail', array( $this, 'change_email_args' ), 100, 1 );
			add_action( 'init', array( $this, 'test_email' ), 10 );
		}

		public function add_toolbar_submenus( $submenus ) {
			$email_debug_title = sprintf( __( 'Email debug: %s', DefTools::TEXT_DOMAIN ), ! empty( $this->debug_email ) ? $this->debug_email : 'disabled' );
			$submenus[]        = array(
				'title' => $email_debug_title,
				'id'    => 'deftools-email-debug',
				'href'  => admin_url( 'admin.php?page=deftools-settings' ),
				'meta'  => array( 'target' => '_blank' ),
			);
			return $submenus;
		}

		public function change_phpmailer( $phpmailer ) {
			if ( ! $this->is_custom_smtp_enabled() ) {
				return;
			}

			$phpmailer->Mailer = 'smtp';
			if ( DEFTOOLS_EMAIL_SET_RETURN_PATH ) {
				$phpmailer->Sender = $phpmailer->From;
			}

			$phpmailer->SMTPSecure = DEFTOOLS_EMAIL_SSL;
			$phpmailer->Host       = DEFTOOLS_EMAIL_SMTP_HOST;
			$phpmailer->Port       = DEFTOOLS_EMAIL_SMTP_PORT;

			if ( DEFTOOLS_EMAIL_SMTP_AUTH ) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = DEFTOOLS_EMAIL_SMTP_USER;
				$phpmailer->Password = DEFTOOLS_EMAIL_SMTP_PASS;
			}
		}

		public function change_email_args( $args ) {
			$debug_email = $this->debug_email;
			if ( empty( $debug_email ) ) {
				return $args;
			}

			// Remove BCC
			$tempheaders = is_array( $args['headers'] ) ? $args['headers'] : explode( "\n", str_replace( "\r\n", "\n", $args['headers'] ) );
			$bcc_found   = preg_grep( '/^BCC.*/', $tempheaders );

			if ( ! empty( $bcc_found ) ) {
				foreach ( $bcc_found as $key => $bcc_value ) {
					unset( $tempheaders[ $key ] );
				}

				$args['headers'] = implode( "\n", $tempheaders );
			}

			// if ( true ) {
			//     $args['from'] = DEFTOOLS_EMAIL_SMTP_USER;
			// }

			$to              = $args['to'];
			$args['to']      = $debug_email;
			$args['subject'] = '[' . $to . '] ' . $args['subject'];

			return $args;
		}

		public function test_email() {
			if ( ! isset( $_GET['test-email'] ) ) {
				return;
			}

			global $phpmailer;

			if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
				include_once ABSPATH . WPINC . '/class-phpmailer.php';
				include_once ABSPATH . WPINC . '/class-smtp.php';
				$phpmailer = new PHPMailer( true );
			}

			// Set up the mail variables
			$to      = ( ! empty( $_GET['test-email'] ) ) ? $_GET['test-email'] : get_option( 'admin_email' );
			$subject = 'Email Test';
			$message = sprintf( 'Test email from %s', get_bloginfo( 'name' ) );

			// SMTP DEBUG
			if ( isset( $_GET['debug'] ) && ( 'true' == $_GET['debug'] ) ) {
				// Set SMTPDebug to true
				$phpmailer->SMTPDebug = true;
			}

			// Start output buffering to grab smtp debugging output
			ob_start();

			// Send the test mail
			$send = wp_mail( $to, $subject, $message );

			// Output the response
			?>
		<p>Email <?php echo ( $send ) ? 'berhasil' : 'gagal'; ?> dikirim ke <?php echo $to; ?>.</p>

			<?php
			$message = ob_get_clean();

			// Destroy $phpmailer so it doesn't cause issues later
			unset( $phpmailer );

			wp_die( $message, 'Test Email' );
		}

		protected function is_custom_smtp_enabled() {
			if ( ! DEFTOOLS_EMAIL_ENABLE_SMTP ) {
				return false;
			}

			if ( DEFTOOLS_EMAIL_SMTP_AUTH && ( empty( DEFTOOLS_EMAIL_SMTP_USER ) || empty( DEFTOOLS_EMAIL_SMTP_PASS ) ) ) {
				return false;
			}

			return true;
		}
	}

endif;
