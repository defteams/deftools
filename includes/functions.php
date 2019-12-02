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