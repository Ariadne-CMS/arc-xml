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
	 * @requires \arc\path
     * @requires \arc\tree
	 * @requires \arc\context
	*/
	class config {

		public static function getConfiguration() {
			$context = \arc\context::$context;
			if ( !$context->arcConfig ) {
				$context->arcConfig = new config\Configuration( \arc\tree::expand()->cd( $context->arcPath ) );
			}
			return $context->arcConfig;
		}

		public static function acquire( $name, $path = null, $root = '/' ) {
			return self::getConfiguration()->acquire( $name, $path, $root );
		}

		public static function configure( $name, $value ) {
			return self::getConfiguration()->configure( $name, $value );
		}

		public static function cd( $path ) {
			return self::getConfiguration()->cd( $path );
		}

		public static function root( $root ) {
			return self::getConfiguration()->root( $root );
		}
	}

