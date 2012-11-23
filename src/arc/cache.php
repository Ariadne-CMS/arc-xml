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
	 *	@suggests \arc\context
	 */

	class cache extends Pluggable {

		static $cacheStore = null;
		
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
			if ( !isset( $context ) && class_exists( '\arc\context' ) ) {
				$context = context::getStack();
				$path = $context['arc.path'];
			} else {
				$path = '/';
			}
			$fileStore = new cache\FileStore( ARC_CACHE_DIR . '/' . $prefix, $path );
			return new cache\Store( $fileStore, $context, $timeout );
		}

		public static function getStore() {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
			return self::$cacheStore;
		}

		public static function __callStatic( $name, $args ) {
			$store = self::getStore();
			if ( method_exists( $store, $name ) ) {
				return call_user_func_array( array( $store, $name), $args);
			} else {
				return parent::__callStatic( $name, $args );
			}
		}
		
		public static function proxy( $object, $timeout = 7200 ) {
			return new cache\Proxy( $object, self::getStore(), $timeout );
		}

	}
