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

	class http {

		public static function request( $method = null, $url = null, $query = null, $options = array() ) {
			$client = new http\ClientStream();
			return $client->send( $method, $url, $query, $options );
		}

		public static function client( $options = array() ) {
			return new http\ClientStream( $options );
		}

		public static function get( $url, $query = null, $options = array() ) {
			return self::request( 'GET', $url, $query, $options);
		}

		public static function post( $url, $query = null, $options = array() ) {
			return self::request( 'POST', $url, $query, $options);
		}

		public static function put( $url, $query = null, $options = array() ) {
			return self::request( 'PUT', $url, $query, $options);
		}

		public static function delete( $url, $query = null, $options = array() ) {
			return self::request( 'DELETE', $url, $query, $options);
		}

	}

