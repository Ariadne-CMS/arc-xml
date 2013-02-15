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
	 *	@requires \arc\path
	 *	@requires \arc\context
	 */

	class cache {

		public static function create( $prefix = null, $timeout = 7200, $context = null ) {
			if ( !defined("ARC_CACHE_DIR") ) {
				define( "ARC_CACHE_DIR", sys_get_temp_dir().'/arc/cache' );
			}
			if ( !file_exists( ARC_CACHE_DIR ) ) {
				@mkdir( ARC_CACHE_DIR, 0770, true );
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
			if ( !$prefix ) { // make sure you have a default prefix, so you won't clear other prefixes unintended
				$prefix = 'default';
			}
			$context = \arc\context::$context;
			$fileStore = new cache\FileStore( ARC_CACHE_DIR . '/' . $prefix, $context->arcPath );
			return new cache\Store( $fileStore, $context, $timeout );
		}

		public static function getCacheStore() {
			$context = \arc\context::$context;
			if ( !$context->arcCacheStore ) {
				$context->arcCacheStore = self::create();
			}
			return $context->arcCacheStore;
		}

		public static function __callStatic( $name, $args ) {
			$store = self::getCacheStore();
			if ( method_exists( $store, $name ) ) {
				return call_user_func_array( array( $store, $name), $args);
			} else {
				return parent::__callStatic( $name, $args );
			}
		}
		
		public static function proxy( $object, $timeout = 7200 ) {
			return new cache\Proxy( $object, self::getCacheStore(), $timeout );
		}

	}
