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

	class cache extends Pluggable {

		static $cacheStore = null;

		public static function create( $prefix = null, $timeout = 7200 ) {
			if ( !$prefix ) { // make sure you have a default prefix, so you won't clear other prefixes unintended
				$prefix = 'default';
			}
			if ( class_exists( '\arc\context' ) ) {
				$prefix = $prefix . context::getPath(); // make sure the cache store is limited to the current path in the context stack
			}
			return new cache\Store( $prefix, $timeout );
		}

		protected static function initStore() {
			if ( !self::$cacheStore ) {
				self::$cacheStore = self::create();
			}
		}

		public static function get( $name ) {
			self::initStore();
			return self::$cacheStore->get( $name );
		}

		public static function getIfFresh( $name, $freshness=0 ) {
			self::initStore();
			return self::$cacheStore->getIfFresh( $name, $freshness );
		}

		public static function lock( $name ) {
			self::initStore();
			return self::$cacheStore->lock( $name );
		}

		public static function wait( $name ) {
			self::initStore();
			return self::$cacheStore->wait( $name );
		}

		public static function set( $name, $value, $timeout = 7200 ) {
			self::initStore();
			return self::$cacheStore->set( $name, $value, $timeout );
		}

		public static function info( $name ) {
			self::initStore();
			return self::$cacheStore->info( $name );
		}

		public static function clear( $name = null ) {
			self::initStore();
			return self::$cacheStore->clear( $name );
		}

		public static function purge( $name = null ) {
			self::initStore();
			return self::$cacheStore->purge( $name );
		}

		public static function proxy( $object, $timeout = null ) {
			self::initStore();
			return new cache\Proxy( $object, self::$cacheStore, $timeout );
		}

	}
