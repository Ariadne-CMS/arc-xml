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

		protected static $grantsTree = null;

		public static function getGrantsTree() {
			if ( !isset( self::$grantsTree ) ) {
				if ( class_exists( '\arc\context' ) ) {
					$user = \arc\context::getvar('arc.user');
					$groups = \arc\context::getvar('arc.groups');
					$path = \arc\context::getvar('arc.path');
				} else {
					$user = 'public';
					$groups = array( 'public' );
					$path = '/';
				}
				self::$grantsTree = new grants\GrantsTree( \arc\tree::expand()->cd( $path ), $user, $groups );
			}
			return self::$grantsTree;
		}

		public static function check( $grant ) {
			return self::getGrantsTree()->check( $grant );
		}

		public static function cd( $path ) {
			return self::getGrantsTree()->cd( $path );
		}

		public static function ls() {
			return self::getGrantsTree()->ls();
		}

	}
