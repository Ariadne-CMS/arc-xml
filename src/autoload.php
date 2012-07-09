<?php

	/*
	 * PSR-0 autoloader for ARC
	 */

	if ( !defined('ARC_BASE_DIR') ) {
		define('ARC_BASE_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
	}

	require_once( ARC_BASE_DIR . 'arc' . DIRECTORY_SEPERATOR . 'arc.php' );

	spl_autoload_register( '\arc\arc::autoload', true);
