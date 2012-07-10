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

	class events extends \arc\Pluggable {

		protected static $stack;

		protected static function getStack() {
			if ( !self::$stack ) {
				self::$stack = new events\Stack();
			}
			return self::$stack;
		}

		public static function listen( $eventName, $objectType = null, $path = null ) {
			return self::getStack()->get( $path )->listen( $eventName, $objectType );
		}

		public static function capture( $eventName, $objectType = null, $path = null ) {
			return self::getStack()->get( $path )->capture( $eventName, $objectType );
		}

		public static function fire( $eventName, $eventData = array(), $objectType = null, $path = null ) {
			return self::getStack()->get( $path )->fire( $eventName, $eventData, $objectType );
		}

		public static function event() {
			return self::getStack()->event();
		}

		public static function get( $path ) {
			return new events\IncompleteListener( $path, null, null, false, self::getStack() );
		}

	}

