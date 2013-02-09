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
	 * 	@requires \arc\tree;
	 *	@requires \arc\context;
	 */

	class grants {

		public static function getGrantsTree() {
			$context = \arc\context::$context;
			if ( !$context->arcUser ) {
				$context->arcUser = 'public';
			}
			if ( !$context->arcGroups ) {
				$context->arcGroups  = [ 'public' ];
			}
			if ( !$context->arcGrants ) {
				$context->arcGrants = new grants\GrantsTree( \arc\tree::expand()->cd( $context->arcPath ), $context->arcUser, $context->arcGroups );
			}
			return $context->arcGrants;
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
