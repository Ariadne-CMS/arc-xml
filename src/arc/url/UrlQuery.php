<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\url;

	/**
		UrlQuery parses a given query string with parse_str and makes all the arguments and 
		values available as key => value pairs in an array-like object.
		It also allows you to import PHP variables from another query string or an array with 
		key => value pairs.
		When cast to string UrlQuery generates a valid query string compatible with PHP.
         
		Usage:
			$query = new \arc\url\UrlQuery( 'a[0]=1&a[1]=2&test=foo');
			$query['a'][] = 3;
			$query['bar']= 'test';
			unset( $queyr['foo'] );
			echo $query; // => 'a[0]=1&a[1]=2&a[2]=3&bar=test';
	*/
   	class UrlQuery extends \ArrayObject implements \arc\KeyValueStoreInterface {

   		/**
   			@param string $query The query part of an URL, must be parseable by php.
   		*/
		public function __construct( $query ) {
			$arguments = array();
			if ( $query ) {
				parse_str( $query, $arguments );
				if ( class_exists('\arc\connect\http') && \arc\connect\http::$tainting ) {
					$arguments = \arc\tainting::taint($arguments);
				}
			}
			parent::__construct( $arguments, \ArrayObject::ARRAY_AS_PROPS );
		}

		public function __toString() {
			$arguments = (array) $this;
			$arguments = \arc\tainting::untaint( $arguments, FILTER_UNSAFE_RAW );
			$result = http_build_query( (array) $arguments );
			$result = str_replace( '%7E', '~', $result ); // incorrectly encoded, obviates the need for oauth_encode_url
			return $result;
		}

		/**
			Import a query string or an array of key => value pairs into the UrlQuery.

			Usage: 
				$query->import( 'foo=bar&bar=1' );
				$query->import( array( 'foo' => 'bar', 'bar' => 1 ) );

			@param string|array $values query string or array of values to import into this query
		*/
		public function import( $values ) {
			if ( is_string( $values ) ) {
				parse_str( $values, $result );
				$values = $result;
			}
			if ( is_array( $values ) ) {
				foreach( $values as $name => $value ) {
					$this->offsetSet( $name, $value );
				}
			}
		}

		// === \arc\KeyValueStoreInterface ===

		/**
			@param string $name name of the query parameter
			@return mixed
		*/
		public function getvar( $name ) {
			return $this->offsetGet($name);
		}

		/**
			@param string $name name for the query parameter
			@param mixed $value value of the query parameter
		*/
		public function putvar( $name, $value ) {
			$this->offsetSet($name, $value);
		}

	}
