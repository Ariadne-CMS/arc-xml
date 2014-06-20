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

	class arc {

		public static function autoload( $className ) {
			$fileName = self::_parseClassName( $className );
			if ( is_readable( ARC_BASE_DIR . $fileName ) ) {
				include_once( ARC_BASE_DIR . $fileName );
				return true;
			}
			return false;
		}

		public static function hasClass( $className ) {
			$fileName = self::_parseClassName( $className );
			return is_readable( ARC_BASE_DIR . $fileName );
		}

		public function __invoke( $name ) {
			if ( $this->autoload( $name ) ) {
				return new $name();
			} else {
				return null;
			}
		}

		protected static function _parseClassName( $className ) {
			$fileName = preg_replace( '/[^a-z0-9_\.\\\\\/]/i', '', $className );
			$fileName = str_replace( '\\', '/', $fileName );
			$fileName = str_replace( '_', '/', $fileName );
			$fileName = preg_replace( '/\.\.+/', '.', $fileName );
			return $fileName . '.php';
		}

	}
