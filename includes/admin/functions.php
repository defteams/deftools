<?php
/**
 * DefTools Core Admin Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Load a view from the admin/views folder.
 *
 * If the view is not found, an Exception will be thrown.
 *
 * Example usage: deftools_admin_view('metaboxes/campaign-title');
 *
 * @since  1.0.0
 *
 * @param  string $view      The view to display.
 * @param  array  $view_args Optional. Arguments to pass through to the view itself.
 * @return boolean True if the view exists and was rendered. False otherwise.
 */
function deftools_admin_view( $view, $view_args = array() ) {

	$base_path = deftools()->get_path( 'admin_view' );

	if( isset($view_args[ 'base_path' ]) ){
		$base_path = $view_args[ 'base_path' ];
		unset($view_args[ 'base_path' ]);
	}

	if ( ! empty( $view_args ) && is_array( $view_args ) ) {
		extract( $view_args ); // @codingStandardsIgnoreLine
	}

	/**
	 * Filter the path to the view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path      The default path.
	 * @param string $view      The view.
	 * @param array  $view_args View args.
	 */
	$filename  = apply_filters( 'deftools_admin_view_path', $base_path . $view . '.php', $view, $view_args );

	ob_start();

	include( $filename );

	ob_end_flush();

	return true;
}



function deftools_admin_view_html( $view, $view_args = array() ){
	ob_start();
	deftools_admin_view( $view, $view_args );
	return ob_get_clean();
}

function deftools_is_admin_page( $id = false ){
	$screen = get_current_screen();
	$deftools_screens = deftools()->get( 'admin_pages' )->get_screen_id();

	if( !$screen )
		return false;

	if( $id ){
		$is_page = deftools()->get( 'admin_pages' )->get_screen_id( $id ) == $screen->id;
	} else {
		$is_page = in_array($screen->id, $deftools_screens );
	}

	return apply_filters( 'deftools_is_admin_page', $is_page, $id );
}

/**
 * Returns whether we are currently viewing the DefTools settings area.
 *
 * @since  1.0.0
 *
 * @param  string $tab Optional. If passed, the function will also check that we are on the given tab.
 * @return boolean
 */
function deftools_is_settings_view( $tab = '' ) {
	if ( ! empty( $_POST ) ) {
		$is_settings = array_key_exists( 'option_page', $_POST ) && 'deftools-settings' === $_POST['option_page'];

		if ( ! $is_settings || empty( $tab ) ) {
			return $is_settings;
		}

		return array_key_exists( 'deftools-settings', $_POST ) && array_key_exists( $tab, $_POST['deftools-settings'] );
	}

	$is_settings = isset( $_GET['page'] ) && 'deftools-settings' == $_GET['page'];

	if ( ! $is_settings || empty( $tab ) ) {
		return $is_settings;
	}

	/* The general tab can be loaded when tab is not set. */
	if ( 'general' == $tab ) {
		return ! isset( $_GET['tab'] ) || 'general' == $_GET['tab'];
	}

	return isset( $_GET['tab'] ) && $tab == $_GET['tab'];
}

/**
 * Processes arbitrary form attributes into HTML-safe key/value pairs
 *
 * @since  1.0.0
 *
 * @param  array $field Array defining the form field attributes.
 * @return string The formatted HTML-safe attributes.
 */
function deftools_get_arbitrary_attributes( $field ) {
	if ( ! isset( $field['attrs'] ) ) {
		$field['attrs'] = array();
	}

	$output = '';

	foreach ( $field['attrs'] as $key => $value ) {
		$escaped_value = esc_attr( $value );
		if ( empty( $escaped_value ) ) {
			continue;
		}

		$output       .= " $key=\"$escaped_value\" ";
	}

	return apply_filters( 'deftools_arbitrary_field_attributes', $output );
}

/**
 * Print out the settings fields for a particular settings section.
 *
 * This is based on WordPress' do_settings_fields but allows the possibility
 * of leaving out a field lable/title, for fullwidth fields.
 *
 * @see    do_settings_fields
 *
 * @since  1.0.0
 *
 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
 *
 * @param  string  $page       Slug title of the admin page who's settings fields you want to show.
 * @param  string  $section    Slug title of the settings section who's fields you want to show.
 * @return string
 */
function deftools_do_settings_fields( $page, $section ) {
	global $wp_settings_fields;

	if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
		return;
	}

	foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
		$class = '';

		if ( ! empty( $field['args']['class'] ) ) {
			$class .= ' tr-' . esc_attr( $field['args']['class'] );
		}

		if ( isset( $field['args']['type']) && $field['args']['type'] == 'hidden' ) {
			$class .= ' hidden';
		}

		echo '<tr class="' . $class . '">';

		if ( ! empty( $field['args']['label_for'] ) ) {
			echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
			echo '<td>';
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
		} elseif ( ! empty( $field['title'] ) ) {
			echo '<th scope="row">' . $field['title'] . '</th>';
			echo '<td>';
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
		} else {
			echo '<td colspan="2" class="deftools-fullwidth">';
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
		}

		echo '</tr>';
	}
}

/**
 * Add new tab to the DefTools settings area.
 *
 * @since  1.0.0
 *
 * @param  string[] $tabs
 * @param  string $key
 * @param  string $name
 * @param  mixed[] $args
 * @return string[]
 */
function deftools_add_settings_tab( $tabs, $key, $name, $args = array() ) {
	$defaults = array(
		'index' => 3,
	);

	$args   = wp_parse_args( $args, $defaults );
	$keys   = array_keys( $tabs );
	$values = array_values( $tabs );

	array_splice( $keys, $args['index'], 0, $key );
	array_splice( $values, $args['index'], 0, $name );

	return array_combine( $keys, $values );
}