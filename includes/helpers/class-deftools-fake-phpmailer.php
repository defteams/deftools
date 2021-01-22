<?php
/**
 * DefTools_Fake_PHPMailer Class.
 *
 * @class   DefTools_Fake_PHPMailer
 * @version 1.0.0
 * @author  Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

if ( ! class_exists( 'DefTools_Fake_PHPMailer' ) ) :

	/**
	 * DefTools_Fake_PHPMailer class.
	 */
	class DefTools_Fake_PHPMailer extends PHPMailer\PHPMailer\PHPMailer {
		public function send() {
			try {
				if ( ! $this->preSend() ) {
					return false;
				}
				return true;
			} catch ( Exception $exc ) {
				$this->mailHeader = '';
				$this->setError( $exc->getMessage() );
				if ( $this->exceptions ) {
					throw $exc;
				}

				return false;
			}
		}
	}

endif;
