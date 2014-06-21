<?php
	include_once( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');
	
	$files = scandir( __DIR__ );
	foreach ( $files as $file ) {
		if ( is_file( __DIR__ . DIRECTORY_SEPARATOR . $file ) && preg_match( '/^test\..*\.php$/i', $file ) ) {
			include( __DIR__ . DIRECTORY_SEPARATOR . $file );
		}
	}
