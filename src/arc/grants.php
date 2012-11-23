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
	 *	@requires \arc\path;
	 * 	@requires \arc\config;
	 *	@suggests \arc\context;
	 */

	class grants extends Pluggable {

		protected static $grantsConfig = null;

		public static function getGrantsConfiguration( $config = null ) {
			if ( !isset( $config ) ) {
				if ( !isset( self::$grantsConfig ) ) {
					$config = \arc\config::getConfiguration();
					self::$grantsConfig = new grants\GrantsConfiguration( $config );	
				}
				return self::$grantsConfig;
			} else {
				return new grants\GrantsConfiguration( $config );
			}
		}

		public static function check( $grant ) {
			return self::getGrantsConfiguration()->check( $grant );
		}

		public static function on( $id ) {
			return self::getGrantsConfiguration()->on( $id );
		}

		public static function root( $path ) {
			return self::getGrantsConfiguration()->root( $path );
		}

		public static function cd( $path ) {
			return self::getGrantsConfiguration()->cd( $path );
		}

		public static function ls() {
			return self::getGrantsConfiguration()->ls();
		}

	}
