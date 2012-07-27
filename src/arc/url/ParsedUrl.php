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
		ParsedUrl parses the given URL string and seperates the component parts. You can access
		these as properties on ParsedUrl objects and add, change or delete them.
		ParsedUrl works similar to SafeUrl, except it parses the query string, so if it isn't 
		compatible with PHP's parse_str it won't survive intact.
         
		Usage:
			$url = new \arc\url\ParsedUrl( 'http://www.ariadne-cms.org/' );
			$url->path = '/docs/search/';
			$url->query->searchstring = 'test';
			echo $url; // => 'http://www.ariadne-cms.org/docs/search/?searchstring=test'
	*/
	class ParsedUrl extends SafeUrl implements \arc\KeyValueStoreInterface {

		/**
			@param string $url The url to parse, the query part must be compatible with php.
		*/
		public function __construct( $url ) {
			parent::__construct( $url );
			$this->query = new UrlQuery( $this->query );
		}

		/**
			Import a query string or an array of key => value pairs into the UrlQuery.

			Usage: 
				$url->query->import( 'foo=bar&bar=1' );
				$url->query->import( array( 'foo' => 'bar', 'bar' => 1 ) );

			@param string|array $values query string or array of values to import into this query
		*/
		public function import( $values ) {
			$this->query->import( $values );
		}

		// === \arc\KeyValueStoreInterface ===

		/**
			@param string $name name of the query parameter
			@return mixed
		*/
		public function getvar( $name ) {
			return $this->query->$name;
		}

		/**
			@param string $name name for the query parameter
			@param mixed $value value of the query parameter
		*/
		public function putvar( $name, $value ) {
			$this->query->{$name} = $value;
		}

	}
