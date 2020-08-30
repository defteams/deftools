<?php
/**
 * WWP Core Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function deftools() {
	return DefTools::instance();
}

function deftlog( $message, $data = array(), $type = 'debug' ) {
	return deftools()->get( 'logs' )->log( $message, $data, $type );
}

/**
 * This returns the value for a particular setting.
 *
 * @since 1.0.0
 *
 * @param  mixed $key      Accepts an array of strings or a single string.
 * @param  mixed $default  The value to return if key is not set.
 * @param  array $settings Optional. Used when $key is an array.
 * @return mixed
 */
function deftools_get_option( $key, $default = false, $settings = array() ) {
	if ( empty( $settings ) ) {
		$settings = get_option( 'deftools-settings' );
	}

	if ( ! is_array( $key ) ) {
		$key = array( $key );
	}

	$current_key = current( $key );

	/* Key does not exist */
	if ( ! isset( $settings[ $current_key ] ) ) {
		return $default;
	}

	array_shift( $key );

	if ( ! empty( $key ) ) {
		return deftools_get_option( $key, $default, $settings[ $current_key ] );
	}

	return $settings[ $current_key ];
}
