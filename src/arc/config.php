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
				self::$configuration = new config\Configuration( $context );
			}
			return self::$configuration;
		}

		public static function acquire( $name ) {
			return self::getConfiguration()->acquire( $name );
		}

		public static function configure( $name, $value ) {
			return self::getConfiguration()->configure( $name, $value );
		}

		public static function cd( $path ) {
			return self::getConfiguration()->cd( $path );
		}

	}

