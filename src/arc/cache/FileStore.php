<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>0
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */
	// FIXME: not specific to \arc\cache really
	namespace arc\cache;

	class FileStore implements \arc\PathTreeInterface, \arc\KeyValueStoreInterface {

		protected $root = null;
		protected $currentPath = null;
		protected $basePath = null;
		protected $mode = null;

		public function __construct( $root, $currentPath = '/', $mode = 0770 ) {
			$this->root = $root;
			$this->currentPath = $currentPath;
			$this->basePath = $root . \arc\path::normalize( $currentPath );
			$this->mode = $mode;
		}

		protected function getPath( $name ) {
			return $this->basePath . base64_encode( $name );
		}

		public function getVar( $name ) {
			$filePath = $this->getPath( $name );
			if ( file_exists( $filePath ) ) {
				return file_get_contents( $filePath );
			}
		}

		public function putVar( $name, $value ) {
			$filePath = $this->getPath( $name );
			$dir = dirname( $filePath );
			if ( !file_exists( $dir ) ) {
				mkdir( $dir, $this->mode, true ); //recursive
			}
			return file_put_contents( $filePath, $value, LOCK_EX );	
		}

		public function getInfo( $name ) {
			$filePath = $this->getPath( $name );
			if ( file_exists( $filePath ) && is_readable( $filePath ) ) {
				return array(
					'size' => filesize($filePath),
					'ctime' => filectime( $filePath ),
					'mtime' => filemtime( $filePath )
				);
			} else {
				return null;
			}
		}

		public function setInfo( $name, $info ) {
			$filePath = $this->getPath( $name );
			if ( file_exists( $filePath ) && is_readable( $filePath ) ) {
				foreach ( $info as $key => $value ) {
					switch( $key ) {
						case 'mtime': 
							touch( $filePath, $value );
						break;
						case 'size':
						case 'ctime':
							// FIXME: ignore silently? other storage mechanisms might need this set explicitly?
						break;
					}
				}
				return true;
			} else {
				return false;
			}
		}

		public function cd( $path ) {
			return new FileStore( $this->root, \arc\path::normalize( $path, $this->currentPath ), $this->mode );
		}

		public function ls() {
			$filePath = $this->root . \arc\path::normalize( $path, $this->currentPath );
			$dir = dir( $filePath );
			$result = array();
			if ( $dir ) {
				while ( $name = $dir->read() ) {
					if ( !is_dir($filePath . $name ) ) {
						$name = base64_decode($name);
					}
					$result[] = $name;
				}
				$dir->close();
			}
			return $result;
		}

		public function remove( $name ) {
			$filePath = $this->getPath( $name );
			return unlink( $filePath );
		}

		protected function cleanup( $dir ) {
			foreach ( glob( $dir . '/*' ) as $file ) {
				if ( is_dir( $file ) ) {
					$this->cleanup( $file );
				} else {
					unlink( $file );
				}
			}
			rmdir( $dir );
		}

		protected function rmdir( $path, $cleanup = null ) {
			if ( !isset( $cleanup ) ) {
				$cleanup = array( $this, 'cleanup' );
			}
			call_user_func( $cleanup, $path );
		}

		public function purge( $name = null ) {
			if ( $name ) {
				$this->clear( $name );
			}
			$dirPath = $this->basePath . \arc\path::normalize( \arc\path::clean( $name ) );
			if ( file_exists( $dirPath ) && is_dir( $dirPath ) ) {
				$this->rmdir( $dirPath );
			}
			return true;
		}

		public function lock( $name, $blocking = false ) {
			$filePath = $this->getPath( $name );
			$dir = dirname( $filePath );
			if ( !file_exists( $dir ) ) {
				mkdir( $dir, $this->mode, true ); //recursive
			}
			$lockFile = fopen( $filePath, 'c' );
			$lockMode = LOCK_EX;
			if ( !$blocking ) {
				$lockMode = $lockMode|LOCK_NB;
			}
			return flock( $lockFile, $lockMode );
		}

		public function unlock( $name ) {
			$filePath = $this->getPath( $name );
			$lockFile = fopen( $filePath, 'c' );
			return flock( $lockFile, LOCK_UN);
		}

	}