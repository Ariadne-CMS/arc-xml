<?php

	if ( ! defined ('SIMPLETESTDIR' ) ) {
		define('SIMPLETESTDIR', __DIR__);
	}

	error_reporting(E_ALL|E_STRICT);
	include_once( SIMPLETESTDIR . '/../vendor/autoload.php');

	require_once( SIMPLETESTDIR . '/vendor/lastcraft/simpletest/autorun.php');
	require_once( SIMPLETESTDIR . '/vendor/lastcraft/simpletest/compatibility.php');
	require_once( SIMPLETESTDIR . '/vendor/lastcraft/simpletest/browser.php');
	require_once( SIMPLETESTDIR . '/vendor/lastcraft/simpletest/web_tester.php');
	require_once( SIMPLETESTDIR . '/vendor/lastcraft/simpletest/unit_tester.php');
