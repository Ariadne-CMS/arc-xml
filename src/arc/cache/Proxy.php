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

	class Proxy extends \arc\Wrapper {
		// TODO: allow more control on retrieval:
		// - get contents from cache even though cache may be stale
		//   perhaps through an extra option in __construct?
		var $cacheStore = null;
		var $cacheController = null;
		var $cacheTimeout = '2 hours';

		public function __construct( $object, $cacheStore, $cacheTimeout = null, $cacheController = null ) {
			parent::__construct( $object );
			$this->cacheStore = $cacheStore;
			$this->cacheController = $cacheController;
			if ( isset($cacheTimeout) ) {
				$this->cacheTimeout = $cacheTimeout;
			}
		}

		protected function __callCatch( $method, $args ) {
			ob_start();
			$result = parent::__call( $method, $args );
			$output = ob_get_contents();
			ob_end_clean();
			return array(
				'output' => $output,
				'result' => $result
			);
		}

		protected function __callCached( $method, $args, $path ) {
			if ( !$cacheData = $this->cacheStore->getIfFresh( $path ) ) {
				if ( $this->cacheStore->lock( $path ) ) {
					$cacheData = $this->__callCatch( $method, $args );
					$this->cacheStore->set( $path, $cacheData, $this->cacheTimeout );
				} else if ( $this->cacheStore->wait( $path ) ){
					$cacheData = $this->cacheStore->get( $path );
				} else {
					$cacheData = $this->__callCatch( $method, $args ); // just get the result and return it
				}
			}
			return $cacheData;
		}

		public function __call( $method, $args ) {
			$path = $method . '(' . md5( serialize($args) ) . ')';
			$cacheData = $this->__callCached( $method, $args, $path );
			echo $cacheData['output'];
			$result = $cacheData['result'];
			if ( is_object( $result ) ) {
				$result = new Proxy( $result, $this->cacheStore->subStore( $path ) );
			}
			return $result;
		}

		public function __get( $name ) {
			$result = parent::__get( $name );
			if ( is_object( $result ) ) {
				$result = new Proxy( $result, $this->cacheStore->subStore( $name ) );
			}
			return $result;
		}

	}
