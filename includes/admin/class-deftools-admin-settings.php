<?php
/**
 * DefTools_Admin_Settings Class.
 *
 * @class       DefTools_Admin_Settings
 * @version		1.0.0
 * @author Lafif Astahdziq <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * DefTools_Admin_Settings class.
 */
class DefTools_Admin_Settings {

	/**
	 * Dynamic groups.
	 *
	 * @since 1.0.0
	 *
	 * @var   array
	 */
	private $dynamic_groups;

	/**
	 * List of static pages, used in some settings.
	 *
	 * @since 1.0.0
	 *
	 * @var   array
	 */
	private $pages;

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
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		/**
		 * @todo  | Autoload
		 */
		add_filter( 'deftools-settings_tab_fields_general', array( DefTools_Admin_Settings_General::instance(), 'add_fields' ), 5 );
	}

	public function onload_admin_page(){
		wp_enqueue_style( 'admin-deftools' );
	}

	/**
	 * Return the array of tabs used on the settings page.
	 *
	 * @since  1.0.0
	 *
	 * @return string[]
	 */
	public function get_sections() {
		/**
		 * Filter the settings tabs.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $tabs List of tabs in key=>label format.
		 */
		return apply_filters( 'deftools-settings_tabs', array(
			'general'  => __( 'General', 'deftools' ),
		) );

	}

	/**
	 * Optionally add the extensions tab.
	 *
	 * @since  1.0.0
	 *
	 * @param  string[] $tabs The existing set of tabs.
	 * @return string[]
	 */
	public function maybe_add_extensions_tab( $tabs ) {
		$actual_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

		/* Set the tab to 'extensions' */
		$_GET['tab'] = 'extensions';

		/**
		 * Filter the settings in the extensions tab.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Array of fields. Empty by default.
		 */
		$settings = apply_filters( 'deftools-settings_tab_fields_extensions', array() );

		/* Set the tab back to whatever it actually is */
		$_GET['tab'] = $actual_tab;

		if ( ! empty( $settings ) ) {
			$tabs = deftools_add_settings_tab(
				$tabs,
				'extensions',
				__( 'Extensions', 'deftools' ),
				array(
					'index' => 4,
				)
			);
		}

		return $tabs;
	}

	/**
	 * Add the hidden "extensions" section field.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $fields All the settings fields.
	 * @return array
	 */
	public function add_hidden_extensions_setting_field( $fields ) {
		if ( ! array_key_exists( 'extensions', $fields ) ) {
			return $fields;
		}

		$fields['extensions']['section'] = array(
			'title'    => '',
			'type'     => 'hidden',
			'priority' => 10000,
			'value'    => 'extensions',
			'save'     => false,
		);

		return $fields;
	}

	/**
	 * Register setting.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {

		if ( ! deftools_is_settings_view() ) {
			return;
		}

		register_setting( 'deftools-settings', 'deftools-settings', array(
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
			'show_in_rest'      => false,
		) );

		$fields = $this->get_fields();

		if ( empty( $fields ) ) {
			return;
		}

		$sections = array_merge( $this->get_sections(), $this->get_dynamic_groups() );

		// echo "<pre>";
		// print_r($sections);
		// echo "</pre>";
		// echo "<pre>";
		// print_r($fields);
		// echo "</pre>";
		// exit();

		/* Register each section */
		foreach ( $sections as $section_key => $section ) {
			$section_id = 'deftools-settings_' . $section_key;

			add_settings_section(
				$section_id,
				__return_null(),
				'__return_false',
				$section_id
			);

			if ( ! isset( $fields[ $section_key ] ) || empty( $fields[ $section_key ] ) ) {
				continue;
			}

			/* Sort by priority */
			$section_fields = $fields[ $section_key ];
			// uasort( $section_fields, 'deftools_priority_sort' );

			/* Add the individual fields within the section */
			foreach ( $section_fields as $key => $field ) {
				$this->register_field( $field, array( $section_key, $key ) );
			}
		}
	}

	/**
	 * Sanitize submitted settings before saving to the database.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $values The submitted values.
	 * @return string
	 */
	public function sanitize_settings( $values ) {
		$old_values = get_option( 'deftools-settings', array() );
		$new_values = array();

		if ( ! is_array( $old_values ) ) {
			$old_values = array();
		}

		if ( ! is_array( $values ) ) {
			$values = array();
		}

		/* Loop through all fields, merging the submitted values into the master array */
		foreach ( $values as $section => $submitted ) {
			$new_values = array_merge( $new_values, $this->get_section_submitted_values( $section, $submitted ) );
		}

		$values = wp_parse_args( $new_values, $old_values );

		/**
		 * Filter sanitized settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values     All values, merged.
		 * @param array $new_values Newly submitted values.
		 * @param array $old_values Old settings.
		 */
		$values = apply_filters( 'deftools_save_settings', $values, $new_values, $old_values );

		if( md5( maybe_serialize( $values ) ) !== md5( maybe_serialize( $old_values ) ) ){
			deftools()->get( 'admin_notices' )->add_notice( __( 'Settings saved', 'deftools' ), 'success' );
		}

		return $values;
	}

	/**
	 * Checkbox settings should always be either 1 or 0.
	 *
	 * @since  1.0.0
	 *
	 * @param  mixed $value Submitted value for field.
	 * @param  array $field Field definition.
	 * @return int
	 */
	public function sanitize_checkbox_value( $value, $field ) {
		if ( isset( $field['type'] ) && 'checkbox' == $field['type'] ) {
			$value = intval( $value && 'on' == $value );
		}

		return $value;
	}

	/**
	 * Render field. This is the default callback used for all fields, unless an alternative callback has been specified.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $args Field definition.
	 * @return void
	 */
	public function render_field( $args ) {
		$field_type = isset( $args['type'] ) ? $args['type'] : 'text';

		$args[ 'settings' ] = 'deftools-settings';

		deftools_admin_view( 'settings/fields/' . $field_type, $args );
	}

	/**
	 * Returns an array of all pages in the id=>title format.
	 *
	 * @since  1.0.0
	 *
	 * @return string[]
	 */
	public function get_pages() {
		if ( ! isset( $this->pages ) ) {
			$this->pages = deftools_get_pages_options();
		}

		return $this->pages;
	}

	/**
	 * Recursively add settings fields, given an array.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $field The setting field.
	 * @param  array $keys  Array containing the section key and field key.
	 * @return void
	 */
	private function register_field( $field, $keys ) {
		$section_id = 'deftools-settings_' . $keys[0];

		if ( isset( $field['render'] ) && ! $field['render'] ) {
			return;
		}

		/* Drop the first key, which is the section identifier */
		$field['name'] = implode( '][', $keys );

		if ( ! $this->is_dynamic_group( $keys[0] ) ) {
			array_shift( $keys );
		}

		$field['key']     = $keys;
		$field['classes'] = $this->get_field_classes( $field );
		$callback         = isset( $field['callback'] ) ? $field['callback'] : array( $this, 'render_field' );
		$label            = $this->get_field_label( $field, end( $keys ) );

		// echo sprintf( 'deftools-settings_%s', implode( '_', $keys ) );
		add_settings_field(
			sprintf( 'deftools-settings_%s', implode( '_', $keys ) ),
			$label,
			$callback,
			$section_id,
			$section_id,
			$field
		);
	}

	/**
	 * Return the label for the given field.
	 *
	 * @since  1.0.0
	 *
	 * @param  array  $field The field definition.
	 * @param  string $key   The field key.
	 * @return string
	 */
	private function get_field_label( $field, $key ) {
		$label = '';

		if ( isset( $field['label_for'] ) ) {
			$label = $field['label_for'];
		}

		if ( isset( $field['title'] ) ) {
			$label = $field['title'];
		}

		return $label;
	}

	/**
	 * Return a space separated string of classes for the given field.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $field Field definition.
	 * @return string
	 */
	private function get_field_classes( $field ) {
		$classes = array( 'deftools-settings-field' );

		if ( isset( $field['class'] ) ) {
			$classes[] = $field['class'];
		}

		/**
		 * Filter the list of classes to apply to settings fields.
		 *
		 * @since 1.0.0
		 *
		 * @param array $classes The list of classes.
		 * @param array $field   The field definition.
		 */
		$classes = apply_filters( 'deftools-settings_field_classes', $classes, $field );

		return implode( ' ', $classes );
	}

	/**
	 * Return an array with all the fields & sections to be displayed.
	 *
	 * @uses   deftools-settings_fields
	 * @see    DefTools_Settings::register_setting()
	 * @since  1.0.0
	 *
	 * @return array
	 */
	private function get_fields() {

		/**
		 * Use the deftools-settings_tab_fields to include the fields for new tabs.
		 * DO NOT use it to add individual fields. That should be done with the
		 * filters within each of the methods.
		 */
		$fields = array();

		foreach ( $this->get_sections() as $section_key => $section ) {
			/**
			 * Filter the array of fields to display in a particular tab.
			 *
			 * @since 1.0.0
			 *
			 * @param array $fields Array of fields.
			 */
			$fields[ $section_key ] = apply_filters( 'deftools-settings_tab_fields_' . $section_key, array() );
		}

		/**
		 * Filter the array of settings fields.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Array of fields.
		 */
		return apply_filters( 'deftools-settings_tab_fields', $fields );
	}

	/**
	 * Get the submitted value for a particular setting.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $key       The key of the setting being saved.
	 * @param  array  $field     The setting field.
	 * @param  array  $submitted The submitted values.
	 * @param  string $section   The section being saved.
	 * @return mixed|null        Returns null if the value was not submitted or is not applicable.
	 */
	private function get_setting_submitted_value( $key, $field, $submitted, $section ) {
		$value = null;

		if ( isset( $field['save'] ) && ! $field['save'] ) {
			return $value;
		}

		$field_type = isset( $field['type'] ) ? $field['type'] : '';

		switch ( $field_type ) {

			case 'checkbox':
				$value = intval( array_key_exists( $key, $submitted ) && 'on' == $submitted[ $key ] );
				break;

			case 'multi-checkbox':
				$value = isset( $submitted[ $key ] ) ? $submitted[ $key ] : array();
				break;

			case '':
			case 'heading':
				return $value;

			default:
				if ( ! array_key_exists( $key, $submitted ) ) {
					return $value;
				}

				$value = $submitted[ $key ];

		}//end switch

		/**
		 * General way to sanitize values. If you only need to sanitize a
		 * specific setting, used the filter below instead.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed  $value     The current setting value.
		 * @param array  $field     The field configuration.
		 * @param array  $submitted All submitted data.
		 * @param string $key       The setting key.
		 * @param string $section   The section being saved.
		 */
		$value = apply_filters( 'deftools_sanitize_value', $value, $field, $submitted, $key, $section );

		/**
		 * Sanitize the setting value.
		 *
		 * The filter hook is formatted like this: deftools_sanitize_value_{$section}_{$key}.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $value     The current setting value.
		 * @param array $field     The field configuration.
		 * @param array $submitted All submitted data.
		 */
		return apply_filters( 'deftools_sanitize_value_' . $section . '_' . $key, $value, $field, $submitted );
	}

	/**
	 * Return the submitted values for the given section.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $section   The section being edited.
	 * @param  array  $submitted The submitted values.
	 * @return array
	 */
	private function get_section_submitted_values( $section, $submitted ) {
		$values      = array();
		$form_fields = $this->get_fields();

		if ( ! isset( $form_fields[ $section ] ) ) {
			return $values;
		}

		foreach ( $form_fields[ $section ] as $key => $field ) {
			$value = $this->get_setting_submitted_value( $key, $field, $submitted, $section );

			if ( is_null( $value ) ) {
				continue;
			}

			if ( $this->is_dynamic_group( $section ) ) {
				$values[ $section ][ $key ] = $value;
				continue;
			}

			$values[ $key ] = $value;
		}

		return $values;
	}

	/**
	 * Return list of dynamic groups.
	 *
	 * @since  1.0.0
	 *
	 * @return string[]
	 */
	private function get_dynamic_groups() {
		if ( ! isset( $this->dynamic_groups ) ) {
			/**
			 * Filter the list of dynamic groups.
			 *
			 * @since 1.0.0
			 *
			 * @param array $groups The dynamic groups.
			 */
			$this->dynamic_groups = apply_filters( 'deftools_dynamic_groups', array() );
		}

		return $this->dynamic_groups;
	}

	/**
	 * Returns whether the given key indicates the start of a new section of the settings.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $composite_key The unique key for this group.
	 * @return boolean
	 */
	private function is_dynamic_group( $composite_key ) {
		return array_key_exists( $composite_key, $this->get_dynamic_groups() );
	}

	public function render_page(){

		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		$group      = isset( $_GET['group'] ) ? $_GET['group'] : $active_tab;
		$sections   = $this->get_sections();

		deftools_admin_view( 'settings/settings', array(
			'active_tab' => $active_tab,
			'group' => $group,
			'sections' => $sections,
		) );
	}

	public function return_old_values( $values, $new_values, $old_values ){
		return $old_values;
	}
}