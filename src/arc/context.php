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

	class context extends Pluggable {

		protected static $contextStack = null;

		public static function getContextStack() {
			if ( !self::$contextStack ) {
				self::$contextStack = new context\ContextStack();
			}
			return self::$contextStack;
		}

		public static function push( $params ) {
			return self::getContextStack()->push( $params );
		}

		public static function top() {
			return self::getContextStack()->top();
		}

		public static function pop() {
			return self::getContextStack()->pop();
		}

		public static function peek( $level = 0 ) {
			return self::getContextStack()->peek( $level );
		}

		public static function putVar( $name, $value ) {
			self::getContextStack()->putVar( $name, $value );
		}

		public static function getVar( $name ) {
			return self::getContextStack()->getVar( $name );
		}

	}
