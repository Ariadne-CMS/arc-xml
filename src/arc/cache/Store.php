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

	/**
	 * This class implements a generic cache store based on the \arc\cache\StoreInterface.
	 * It requires an instance of a storage class, e.g. \arc\cache\FileStore.
	 * You can create a default cache store through the \arc\cache::create factory method.
	 * @param 
	 */
	class Store implements StoreInterface, \arc\KeyValueStoreInterface, \arc\PathTreeInterface {

		protected $timeout = 7200;
		protected $context = null;
		protected $storage = null;

		public function __construct( $storage, $context = null, $timeout = 7200, $currentPath = null ) {
			$this->context = $context;
			$this->timeout = $this->getTimeout( $timeout );
			if ( !isset( $currentPath ) ) {
				$currentPath = ( isset($context) ? $context->arcPath : '/' );
			}
			$this->currentPath = $currentPath;
			$this->storage = $storage->cd( $this->currentPath );
		}

		/* \arc\KeyValueStoreInterface */

		/**
		 *	Part of the KeyValueStoreInterface, this function will return the value for the given name
		 *  but only if it is still fresh. Identical to getIfFresh().
		 *  @param string $name 
		 *  @return mixed value
		 */
		public function getVar( $name ) {
			return $this->getIfFresh( $name );
		}

		/**
		 *	Part of the KeyValueStoreInterface, this function will set a value for the given name
		 *  Identical to set(). It will use the default timeout set when the cache store was constructed.
		 *  @param string $name
		 *  @param string $value
		 *  @return mixed value
		 */
		public function putVar( $name, $value ) {
			$this->set( $name, $value, $this->timeout );
		}

		/* PathTreeInterface */

		/**
		 *	Part of the PathTreeInterface, this function will return a new cache store with the new path.
		 *  @param string $path
		 *  @return \arc\cache\Store
		 */
		public function cd( $path ) {
			$path = \arc\path::collapse( $path, $this->currentPath );
			return new Store( $this->storage, $this->context, $this->timeout, $path);
		}
		
		/**
		 *	Part of the PathTreeInterface, this function will return a list of items in the cache store.
		 *  @return array
		 */
		public function ls() {
			return $this->storage->ls();
		}

		/* StoreInterface */

		/**
		 *	This method will return the cached image with the given name, if it is still fresh, or
		 *  if not call the callable method to generate a new value, store it and return that.
		 *  @param string $name
		 *  @param callable $calculateCallback
		 *  @return mixed
		 */
		public function cache( $name, $calculateCallback ) {
			if ( $this->isFresh( $name ) ) {
				return $this->getVar( $name );
			} else {
				$result = call_user_func( $calculateCallback );
				$this->putVar( $name, $result );
				return $result;
			}
		}

		/**
		 * This method returns a new cache store with the given timeout. In all other respects
		 * the new store is a copy of the current one.
		 * @param mixed $timeout Either a timestamp (int) or a string parseable by strtotime.
		 * @return \arc\cache\Store
		 */
		public function timeout( $timeout ) {
			return new Store( $this->storage, $this->context, $timeout, $this->currentPath );
		}
		
		/**
		 *	This method returns the value stored for the given name - even if no longer fresh - 
		 *  or null if there is no value stored.
		 *  @param string $name
		 *  @return mixed
		 */
		public function get( $name ) {
			$content = $this->storage->getVar( $name );
			if ( $content ) {
				return unserialize( $content );
			} else {
				return null;
			}
		}

		/**
		 *	This method stores a name - value pair, with either a given timeout or the default
		 *  timeout for this Store.
		 *  @param string $name
		 *  @param mixed $value
		 *  @param mixed $timeout Either a timestamp (int) or a string parseable by strtotime.
		 *  @return bool true for successfull storage, false for failure.
		 */
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

		/**
		 *	This method returns metadata for the given name.
		 *  @param string $name
		 *  @return array
		 */
		public function getInfo( $name ) {
			return $this->storage->getInfo( $name );
		}

		/**
		 *	This method checks if the value for the given name is fresh for at least the time
		 *  passed as the freshness param.
		 *  @param string $name
		 *  @param mixed $freshness either a unix timestamp or a string parseable by strtotime
		 *  @return bool
		 */
		public function isFresh( $name, $freshness = 0 ) {
			$freshness = $this->getTimeout( $freshness );
			$info = $this->getInfo( $name );
			if ( $info && $info['mtime'] >= $freshness ) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 *  This method returns the value associated with the given name if it is still fresh,
		 *  otherwise it will return null. You can set a minimum freshness time.
		 *  @param string $path
		 *  @param mixed $freshness either a unix timestamp or a string parseable by strtotime
		 *  @return mixed
		 */
		public function getIfFresh( $name, $freshness = 0 ) {
			$freshness = $this->getTimeout( $freshness );
			$info = $this->getInfo( $name );
			if ( $info && $info['mtime'] >= $freshness ) {
				return $this->get( $name );
			} else {
				return null;
			}
		}

		/**
		 *  This method locks the store for the given name. If blocking is set to true, other
		 *  processes are blocked from reading any current value. Returns true if the lock
		 *  succeeded.
		 *  @param string $name
		 *  @param bool $blocking
		 *  @return bool
		 */
		public function lock( $name, $blocking = false ) {
			return $this->storage->lock( $name, $blocking );
		}


		/**
		 *  This method waits for the store to be unlocked for the given name.
		 *  @param string $name
		 */
		public function wait( $name ) {
			$this->lock( $name, true);
			$this->unlock( $name );
		}

		/**
		 *  This method unlocks the store for the given name. Returns true on succes, 
		 *  false on failure.
		 *  @param string $name
		 *  @return bool
		 */
		public function unlock( $name ) {
			return $this->storage->unlock( $name );
		}

		/**
		 *  This method removes any value for a given name from the store.
		 *  @param string $name
		 *  @return bool
		 */
		public function remove( $name ) {
			return $this->storage->remove( $name );
		}

		/**
		 *  This method removes any value for a given name from the store. If the name
		 *  is a path, it will also remove any values for any child paths.
		 *  @param string $name
		 *  @return bool 
		 */
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