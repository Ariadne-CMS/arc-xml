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
	require_once( ARC_BASE_DIR . 'arc/Loader.php' );

	class arc extends Loader {

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

