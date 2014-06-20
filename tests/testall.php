<?php
	include_once(__DIR__ . '/bootstrap.php');
	
	$files = scandir( __DIR__ );
	foreach ( $files as $file ) {
		if( is_file( __DIR__ . $file ) && preg_match( '/\btest\..*\.php/', basename( __DIR__ .  $file ) ) ) {
			include( __DIR__ . $file );
		}
	}
