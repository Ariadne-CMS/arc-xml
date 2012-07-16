<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\cache;

	class Store implements StoreInterface, \arc\KeyValueStoreInterface {

		protected $basePath = '';
		protected $timeout = 7200;
		protected $mode = 0777;

		public function __construct( $basePath, $timeout = 7200, $mode = 0777 ) {
			$this->basePath = preg_replace('/\.\./', '', $basePath);

			if ( is_string($timeout) ) {
				$timeout = strtotime( $timeout, 0);
			}
			$this->timeout = $timeout;
			$this->mode = $mode;

			if ( !defined("ARC_CACHE_DIR") ) {
				define( "ARC_CACHE_DIR", sys_get_temp_dir().'/arc/cache/' );
			}
			if ( !file_exists( ARC_CACHE_DIR ) ) {
				mkdir( ARC_CACHE_DIR, $this->mode, true );
			}
			if ( !file_exists( ARC_CACHE_DIR ) ) {
				throw new \arc\ExceptionConfigError("Cache Directory does not exist ( ".ARC_CACHE_DIR." )", \arc\exceptions::CONFIGURATION_ERROR);
			}
			if ( !is_dir( ARC_CACHE_DIR ) ) {
				throw new \arc\ExceptionConfigError("Cache Directory is not a directory ( ".ARC_CACHE_DIR." )", \arc\exceptions::CONFIGURATION_ERROR);
			}
			if ( !is_writable( ARC_CACHE_DIR ) ) {
				throw new \arc\ExceptionConfigError("Cache Directory is not writable ( ".ARC_CACHE_DIR." )", \arc\exceptions::CONFIGURATION_ERROR);
			}
		}

		protected function cachePath( $path ) {
			// last '=' is added to prevent conflicts between subdirectories and cache images
			// images always end in a '=', directories never end in a '='
			return ARC_CACHE_DIR . $this->basePath . preg_replace('/(\.\.|\=)/', '', $path) . '=';
		}

		public function subStore( $path ) {
			return new Store( $this->basePath . preg_replace('/(\.\.|\=)/', '', $path) );
		}

		public function get( $path ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) ) {
				return unserialize( file_get_contents( $cachePath ) );
			} else {
				return null;
			}
		}

		public function getvar( $name ) {
			return $this->get( $name );
		}

		public function isFresh( $path ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) ) {
				return ( filemtime( $cachePath ) > time() );
			} else {
				return false;
			}
		}

		public function getIfFresh( $path, $freshness = 0 ) {
			$info = $this->info( $path );
			if ( $info && $info['timeout'] >= $freshness ) {
				return $this->get( $path );
			} else {
				return false;
			}
		}

		public function lock( $path, $blocking = false ) {
			// locks the file against writing by other processes, so generation of time or resource expensive images
			// will not happen by multiple processes simultaneously
			$cachePath = $this->cachePath( $path );
			$dir = dirname( $cachePath );
			if ( !file_exists( $dir ) ) {
				mkdir( $dir, $this->mode, true ); //recursive
			}
			$lockFile = fopen( $cachePath, 'c' );
			$lockMode = LOCK_EX;
			if ( !$blocking ) {
				$lockMode = $lockMode|LOCK_NB;
			}
			return flock( $lockFile, $lockMode );
		}

		public function wait( $path ) {
			$cachePath = $this->cachePath( $path );
			$lockFile = fopen( $cachePath, 'c' );
			$result = flock( $lockFile, LOCK_EX );
			fclose( $lockFile );
			return $result;
		}

		public function putvar( $name, $value ) {
			return $this->set( $name, $value );
		}

		public function set( $path, $value, $timeout = null ) {
			$cachePath = $this->cachePath( $path );
			if ( !isset( $timeout ) ) {
				$timeout = $this->timeout;
			}
			if ( is_string( $timeout ) ) {
				$timeout = strtotime( $timeout, 0);
			}
			$dir = dirname( $cachePath );
			if ( !file_exists( $dir ) ) {
				mkdir( $dir, $this->mode, true ); //recursive
			}
			if ( false !== file_put_contents( $cachePath, serialize( $value ), LOCK_EX ) ) {
				// FIXME: make sure the lock file made with lock() is gone after file_put_contents
				touch( $cachePath, time() + $timeout );
			} else {
				return false;
			}
		}

		public function info( $path ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) && is_readable( $cachePath ) ) {
				return array(
					'size' => filesize($cachePath),
					'fresh' => $this->isFresh( $path ),
					'ctime' => filectime( $cachePath ),
					'timeout' => filemtime( $cachePath ) - time()
				);
			} else {
				return false;
			}
		}

		public function clear( $path = null ) {
			$cachePath = $this->cachePath( $path );
			if ( file_exists( $cachePath ) ) {
				return unlink( $cachePath );
			} else {
				return true;
			}
		}

		public function purge( $path = null ) {
			$this->clear( $path );
			$cachePath = substr( $this->cachePath( $path ), 0, -1 ); // remove last '='
			if ( file_exists( $cachePath ) ) {
				if ( is_dir( $cachePath ) ){
					$cacheDir = dir( $cachePath );
					while (false !== ($entry = $cacheDir->read())) {
						if ( $entry != '.' && $entry != '..' ) {
							$this->purge( $path . '/' . $entry );
						}
					}
					return rmdir( $cachePath );
				} else {
					return unlink( $cachePath );
				}
			} else {
				return true;
			}
		}

	}