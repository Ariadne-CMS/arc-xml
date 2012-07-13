<?php

	class arc_plugin_methodsearcher extends \arc\Loader {

		public static function autoload( $className ) {
			if ( $className == 'arc_plugin' ) {
				include_once( __DIR__.'/arc_plugin.php');
			}
		}

		public static function getPluginMethod( $namespace, $methodName ) {
			if ( $namespace=='arc\arc' && $methodName=='arc_plugin_test') {
				self::autoload( 'arc_plugin.php' );
				return array( 'arc_plugin', $methodName );
			}
		}

		public static function hasClass( $className ) {
			return ( $className == 'arc_plugin' );
		}
	}

