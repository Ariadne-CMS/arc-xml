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

	class config extends Pluggable {

		protected static $configuration = null;

		protected static function getConfiguration( $context = null ) {
			if ( !isset( $context ) && class_exists( '\arc\context' ) ) {
				$context = \arc\context::getContext();
			}
			if ( !self::$configuration ) {
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

		public static function get( $path ) {
			return self::getConfiguration()->get( $path );
		}

	}

