<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 *
	 * This file must be included for the Ariadne Component Library to work
	 * If you want to keep this library fully PSR-0 compliant, move this file
	 * one directory up.
	 */

	namespace arc;

	abstract class Loader {

		protected static function _parseClassName( $className ) {
			$fileName = preg_replace( '/[^a-z0-9_\.\\\\\/]/i', '', $className );
			$fileName = str_replace( '\\', '/', $fileName );
			$fileName = str_replace( '_', '/', $fileName );
			$fileName = preg_replace( '/\.\.+/', '.', $fileName );
			return $fileName . '.php';
		}

		public function __invoke( $name ) {
			$this->autoload( $name );
			return new $name();
		}

		public static function hasClass( $className ) {
			throw new \arc\ExceptionMethodNotFound('Method hasClass Not Implemented', \arc\exceptions::OBJECT_NOT_FOUND );
		}

		public static function autoload( $className ) {
			throw new \arc\ExceptionMethodNotFound('Method autoload Not Implemented', \arc\exceptions::OBJECT_NOT_FOUND );
		}

	}