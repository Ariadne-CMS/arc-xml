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

	class Pluggable {

		protected static $methods = array();

		protected static function _callPlugin( $methodName, $arguments = array() ) {
			$method = self::$methods[ $methodName ];
			if ( !$method ) {
				$method = \arc\arc::getPluginMethod( get_called_class(), $methodName );
			}
			if ( !$method ) {
				throw new Exception( 'Method '. get_called_class() . '::' . $methodName.' not available. Is the required plugin loaded?', exceptions::OBJECT_NOT_FOUND );
			} else {
				self::$methods[ $methodName ] = $method;
				return call_user_func_array( $method, $arguments );
			}
		}

		public static function __callStatic( $name, $arguments ) {
			return self::_callPlugin( $name, $arguments );
		}

		public function __call( $name, $arguments ) {
			return $this->_callPlugin( $name, $arguments );
		}

	}
?>