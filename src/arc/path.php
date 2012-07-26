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

		public static function parents( $path, $root = '/' ) {
			// returns all parents starting at the root, up to and including the path itself
			$prevpath = '/';
			$parents = self::reduce( $path, function( $result, $entry ) use ( $root, &$prevpath ) {
				$prevpath .= $entry . '/';
				if ( strpos( $prevpath , $root ) === 0 && $prevpath !== $root ) { // Add only parents below the root
					$result[] = $prevpath;
				}
				return $result;
			}, array( $root ) );
			return $parents;
		}

		public static function normalize( $path, $cwd = '/' ) {
			// removes '.', changes '//' to '/', changes '\\' to '/', calculates '..' up to '/'
			$path = str_replace('\\', '/', $path);
			if ( is_object( $path ) && method_exists( $path, '__toString' ) ) {
				$path = (string) $path;
			}
			if ( !is_string( $path ) ) {
				return $cwd;
			}
			if ( isset($path[0]) ) {
				if ( $path[0] !== '/' ) {
					$path = $cwd . '/' . $path;
				}
			}
			if ( !$path ) {
				return $cwd;
			} else {
				return '/' . self::reduce( $path, function( $result, $entry ) {
					switch ( $entry ) {
						case '..' :
							$result = dirname( $result );
							if ( isset($result[1]) ) { // fast check to see if there is a dirname
								$result .= '/';
							} else if ( $result === '.' ) { // dirname('foo') returns '.' so clear it
								$result = '';
							}
						break;
						case '.':
						case '':
						break;
						default:
							$result .= $entry .'/';
						break;
					}
					return $result;
				});
			}
		}

		public static function clean( $path, $callback = null, $flags = null ) {
			if ( !is_callable( $callback ) ) {
				$filter = $callback;
				if ( !isset( $filter ) ) {
					 $filter = FILTER_SANITIZE_ENCODED;
				}
				if ( !isset($flags) ) {
					$flags = FILTER_FLAG_ENCODE_LOW|FILTER_FLAG_ENCODE_HIGH;
				}
				$callback = function( $entry ) use ( $filter, $flags ) {
					return filter_var( $entry, $filter, $flags);
				};
			}
			return self::map( $path, $callback );
		}

		public static function parent( $path, $root = '/' ) {
			if ( $path == '/' ) {
				return null;
			}
			$parent = dirname( $path );
			if ( isset($parent[1]) ) { // fast check to see if there is a dirname
				$parent .= '/';
			}
			if ( strpos( $parent, $root ) !== 0 ) {
				return null;
			}
			return $parent;
		}

		public static function map( $path, $callback ) {
			$splitpath = array_filter( explode( '/', $path ), function( $entry ) {
				return ( isset( $entry ) && $entry !== '' );
			});
			if ( count($splitpath) ) {
				$result = array_map( $callback, $splitpath );
				return '/' . join( $result, '/' ) .'/';
			} else {
				return '/';
			}
		}

		public static function reduce( $path, $callback, $initial = null ) {
			$splitpath = array_filter( explode( '/', $path ), function( $entry ) {
				return ( isset( $entry ) && $entry !== '' );
			});
			return array_reduce( $splitpath, $callback, $initial );
		}

		public static function collapseTree( $tree ) {
			// TODO: collapse a tree structured array to a realized path / collapsed path array
		}

		public static function expandTree( $array ) {
			// TODO: inverse of collapseTree
		}

	}

