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

	/*
	* @requires \arc\mortar
	*/
	class context {

		public static $context = new mortar\Prototype([
			'arcPath' => '/'
		]);

		public static function push( $params ) {
			self::$context = self::$context->extend( $params );
		}

		public static function pop() {
			self::$context = self::$context->prototype;
		}

		public static function peek( $level = 0 ) {
			$context = self::$context;
			for ( $i=$level; $i>=0; $i-- ) {
				$context = $context->prototype;
			}
			return $context;
		}

	}
