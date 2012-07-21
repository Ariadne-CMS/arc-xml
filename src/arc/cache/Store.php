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

	class Store implements StoreInterface, \arc\KeyValueStoreInterface, \arc\PathTreeInterface {

		protected $timeout = 7200;
		protected $contextStack = null;
		protected $storage = null;

		public function __construct( $storage, $context = null, $timeout = 7200, $currentPath = null ) {
			$this->contextStack = $context;
			$this->timeout = $this->getTimeout( $timeout );
			if ( !isset( $currentPath ) ) {
				$currentPath = ( isset($context) ? $context['path'] : '/' );
			}
			$this->currentPath = $currentPath;
			$this->storage = $storage->cd( $this->currentPath );
		}

		/* \arc\KeyValueStoreInterface */
		public function getVar( $name ) {
			return $this->getIfFresh( $name );
		}

		public function putVar( $name, $value ) {
			$this->set( $name, $value, $this->timeout );
		}

		/* PathTreeInterface */
		public function cd( $path ) {
			$path = \arc\path::normalize( $path, $this->currentPath );
			return new Store( $this->storage, $this->contextStack, $this->timeout, $path);
		}
		
		public function ls() {
			return $this->storage->ls();
		}

		/* StoreInterface */
		public function cache( $name, $calculateCallback, $path = null ) {
			if ( $this->isFresh( $name ) ) {
				return $this->getVar( $name );
			} else {
				$result = call_user_func( $calculateCallback );
				$this->putVar( $name, $result );
				return $result;
			}
		}

		public function timeout( $timeout ) {
			return new Store( $this->storage, $this->contextStack, $timeout, $this->currentPath );
		}
		
		public function get( $name ) {
			$content = $this->storage->getVar( $name );
			if ( $content ) {
				return unserialize( $content );
			} else {
				return null;
			}
		}

		public function set( $name, $value, $timeout = null ) {
			$timeout = $this->getTimeout( $timeout );
			$value = serialize( $value );
			if ( $this->storage->putVar( $name, $value ) ) {
				$result = $this->storage->setInfo( $name, array( 'mtime' => $timeout ) );
			} else {
				$result = false;
			}
			$this->unlock( $name );
			return $result;
		}

		public function getInfo( $name ) {
			return $this->storage->getInfo( $name );
		}

		public function isFresh( $name, $freshness = 0 ) {
			$freshness = $this->getTimeout( $freshness );
			$info = $this->getInfo( $name );
			if ( $info && $info['mtime'] >= $freshness ) {
				return true;
			} else {
				return false;
			}
		}
		
		public function getIfFresh( $name, $freshness = 0 ) {
			$freshness = $this->getTimeout( $freshness );
			$info = $this->getInfo( $name );
			if ( $info && $info['mtime'] >= $freshness ) {
				return $this->get( $path );
			} else {
				return null;
			}
		}

		public function lock( $name, $blocking = false ) {
			return $this->storage->lock( $name, $blocking );
		}

		public function wait( $name ) {
			$this->lock( $name, true);
			$this->unlock( $name );
		}

		public function unlock( $path ) {
			return $this->storage->unlock( $path );
		}

		public function remove( $name ) {
			return $this->storage->remove( $name );
		}

		public function purge( $name = null ) {
			return $this->storage->purge( $name );
		}

		protected function getTimeout( $timeout ) {
			if ( !isset( $timeout ) ) {
				$timeout = time();
			} else if ( is_string( $timeout ) ) {
				$timeout = strtotime( $timeout );
			} else {
				$timeout = time() + $timeout;
			}
			return $timeout;
		}
		
	}