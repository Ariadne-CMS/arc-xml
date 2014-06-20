<?php

	/*
	 * PSR-0 autoloader for ARC
	 */

	if ( !defined('ARC_BASE_DIR') ) {
		define('ARC_BASE_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR );
	}

	require_once( ARC_BASE_DIR . 'arc.php' );

	spl_autoload_register( '\arc\arc::autoload', true);
