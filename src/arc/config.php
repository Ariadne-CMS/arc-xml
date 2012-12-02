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
		@suggests \arc\context
	*/
	class config extends Pluggable {

		protected static $configuration = null;

		public static function getConfiguration() {
			if ( !self::$configuration ) {
				$context = class_exists( '\arc\context' ) ? context::getStack() : null;
				if ( isset($context) ) {
					$path = $context['arc.path'];
				} else {
					$path = '/';
				}
				self::$configuration = new config\Configuration( \arc\tree::expand()->cd( $path ) );
			}
			return self::$configuration;
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

