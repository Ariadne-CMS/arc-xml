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

	class path extends Pluggable {

		public static function parents( $path, $cwd = '/' ) {
			// returns all parents starting at the root, up to and including the path itself
			$root = '/';
			$path = self::normalize( $path, $cwd );
			$parents = array();
			$pathticles = explode( '/', $path );
			$prevpath = '/';
			foreach ( $pathticles as $pathticle ) {
				if ( $pathticle ) {
					$prevpath  .= $pathticle . '/';
					if ( strpos( $prevpath, $root ) === 0 ) { // skip parents above the root
						$parents[] = $prevpath;
					}
				}
			}
			if ( $parents[0] !== $root ) {
				array_unshift( $parents, $root );
			}
			return $parents;
		}

		public static function normalize( $path, $cwd = '/' ) {
			// removes '.', changes '//' to '/', changes '\\' to '/', calculates '..' up to '/'
			$path = str_replace('\\', '/', $path);
			$result = '/';
			if ( $path[0] !== '/' ) {
				$path = $cwd . '/' . $path;
			}
			if ( $path ) {
				$splitpath = explode( '/', $path );
				foreach ( $splitpath as $pathticle ) {
					switch( $pathticle ) {
						case '..' :
							$result = dirname( $result );
							if ( $result[1] ) { // fast check to see if there is a dirname
								$result .= '/';
							}
							// php has a bug in dirname( '/' ) -> returns a '\\' in windows
							$result[0] = '/';
						break;
						case '.' : break;
						case ''	 : break;
						default:
							$result .= $pathticle . '/';
						break;
					}
				}
			}
			return $result;
		}

		public static function clean( $path, $filter = FILTER_SANITIZE_ENCODED, $flags = null ) {
			if ( !isset($flags) ) {
				$flags =  FILTER_FLAG_ENCODE_LOW|FILTER_FLAG_ENCODE_HIGH;
			}
			$splitpath = explode( '/', $path );
			$result = '/';
			foreach ( $splitpath as $pathticle ) {
				$pathticle = filter_var( $pathticle, $filter, $flags );
				$result .= $pathticle . '/';
			}
			return $result;
		}

		public static function parent( $path, $root = '/' ) {
			if ( $path == '/' ) {
				return null;
			}
			$parent = dirname( $path );
			if ( strpos( $parent, $root ) !== 0 ) {
				return null;
			}
			if ( $parent[ strlen( $parent ) - 1 ] !== '/' ) {
				$parent .= '/';
			}
			return $parent;
		}

	}

?>