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

	/**
		@requires \arc\path
		@suggest \arc\context
	*/
	class events extends Pluggable {

		protected static $stack;

		public static function getStack( $path = null ) {
			if ( !self::$stack ) {
				$context = class_exists( '\arc\context' ) ? context::getStack() : null;
				self::$stack = new events\Stack( $context );
			}
			if ( isset($path) ) { 
					return self::$stack->get( $path );
			}
			return self::$stack;
		}

		public static function listen( $eventName, $objectType = null, $path = null ) {
			return self::getStack( $path )->listen( $eventName, $objectType );
		}

		public static function capture( $eventName, $objectType = null, $path = null ) {
			return self::getStack( $path )->capture( $eventName, $objectType );
		}

		public static function fire( $eventName, $eventData = array(), $objectType = null, $path = null ) {
			return self::getStack( $path )->fire( $eventName, $eventData, $objectType );
		}

		public static function event() {
			return self::getStack()->event();
		}

		public static function get( $path ) {
			return self::getStack( $path );
		}

	}

