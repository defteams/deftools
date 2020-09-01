<?php
/**
 * DefTools_Admin_Settings_General Class.
 *
 * @class       DefTools_Admin_Settings_General
 * @version     1.0.0
 * @author Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'DefTools_Admin_Settings_General' ) ) :

	/**
	 * DefTools_Admin_Settings_General
	 *
	 * @final
	 * @since   1.0.0
	 */
	class DefTools_Admin_Settings_General {

		private $id = 'general';

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
		 * Add the tab settings fields.
		 *
		 * @since   1.0.0
		 *
		 * @param   array[] $fields
		 * @return  array
		 */
		public function add_fields( $fields = array() ) {
			if ( ! deftools_is_settings_view( $this->id ) ) {
				return array();
			}

			$new_fields = array(
				'section'       => array(
					'title'    => '',
					'type'     => 'hidden',
					'priority' => 10000,
					'value'    => $this->id,
				),
				'section_email' => array(
					'title' => __( 'Email Debug', 'deftools' ),
					'type'  => 'heading',
					// 'priority'          => 10,
				),
				'debug_email'   => array(
					'title'       => __( 'Email', 'deftools' ),
					'type'        => 'email',
					'force_value' => ! empty( DEFTOOLS_EMAIL_DEBUG ) ? DEFTOOLS_EMAIL_DEBUG : false,
					'attrs'       => array(
						'disabled' => ! empty( DEFTOOLS_EMAIL_DEBUG ) ? 'disabled' : '',
					),
					// 'priority'          => 11,
				),
			);

			$fields = array_merge( $fields, $new_fields );

			return $fields;
		}
	}

endif;
