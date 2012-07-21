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
	
	interface StoreInterface {

		public function cache( $name, $calculateCallback );
		public function get( $name );
		public function set( $name, $value, $timeout = 7200 );
		public function getInfo( $name );
		public function timeout( $timeout );

		public function isFresh( $path, $freshness = 0 );
		public function getIfFresh( $path, $freshness = 0 );

		public function lock( $name );
		public function wait( $name );
		public function unlock( $name );

		public function remove( $name );
		public function purge( $name = null );
	}
