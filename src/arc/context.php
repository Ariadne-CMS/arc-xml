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
		
		public static function getContext() {
			if ( !self::$contextStack ) {
				self::$contextStack = new context\ContextStack();
			}
			return self::$contextStack;
		}
	
		public static function push( $params ) {
			return self::getContext()->push( $params );
		}
		
		public static function top() {
			return self::getContext()->top();
		}
		
		public static function pop() {
			return self::getContext()->pop();		
		}
	
		public static function getPath() {
			$context = self::top();
			return $context['path'];
		}
	}
	