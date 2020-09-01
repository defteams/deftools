<?php
/**
 * Implements deft command.
 */
class Deftools_CLI {

	/**
	 * @subcommand deploy
	 */
	public function deploy( $args, $assoc_args ) {
		WP_CLI::success( ABSPATH );
	}

}

WP_CLI::add_command( 'deft', 'Deftools_CLI' );
