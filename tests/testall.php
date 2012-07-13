<?php
	include_once(__DIR__ . '/bootstrap.php');
	
	$files = scandir(__DIR__ . '/arc/');
	foreach ( $files as $file ) {
		print $file."\n";
		if( is_file(__DIR__ . '/arc/'.$file) && basename( __DIR__ . '/arc/'. $file ,'.php') != $file ) {
			include(__DIR__ . '/arc/'.$file);
		}
	}



