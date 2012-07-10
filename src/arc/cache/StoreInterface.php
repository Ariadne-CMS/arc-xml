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
		public function get( $path );
		public function set( $path, $value, $timeout = 7200 );
		public function info( $path );
		public function clear( $path = null );
		public function subStore( $path );
		public function isFresh( $path );
	}
