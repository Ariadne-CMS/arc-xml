<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc;

	if ( !defined('ARC_BASE_DIR') ) {
		define('ARC_BASE_DIR', dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR );
	}
	require_once( ARC_BASE_DIR . 'arc/Pluggable.php' );
	require_once( ARC_BASE_DIR . 'arc/Loader.php' );

	class arc extends Loader {

		private static $plugins;

		public static function plugin( $filename, $methodSearcher ) {
			if ( is_readable( $filename ) ) {
				include_once( $filename );
			}
			if ( !class_exists( $methodSearcher) ) {
				throw new Exception('Plugin '. $methodSearcher . ' not found in ' . $filename , exceptions::OBJECT_NOT_FOUND );
			}
			spl_autoload_register( $methodSearcher.'::autoload', true, true );
			self::$plugins[] = $methodSearcher;
		}

		public static function getPluginMethod( $namespace, $methodName ) {
			$l = count(self::$plugins);
			for ( $i = $l-1; $i>=0; $i-- ) {
				$plugin = self::$plugins[$i];
				if ( $method = call_user_func( array( $plugin, getPluginMethod), $namespace, $methodName ) ) {
					return $method;
				}
			}
			return null;
		}

		public static function autoload( $className ) {
			$fileName = self::_parseClassName( $className );
			if ( is_readable( ARC_BASE_DIR . $fileName ) ) {
				include_once( ARC_BASE_DIR . $fileName );
			}
		}

		public static function hasClass( $className ) {
			$fileName = self::_parseClassName( $className );
			return is_readable( ARC_BASE_DIR . $fileName );
		}

	}

