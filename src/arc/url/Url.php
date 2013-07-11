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
	 *	Url parses a URL string and returns an object with the seperate parts. You can change
	 *	these and when cast to a string Url will regenerate the URL string and make sure it
	 *	is valid.
	 *	
	 *	Usage:
	 *		$url = new \arc\url\Url( 'http://www.ariadne-cms.org/' );
	 *		$url->path = '/docs/search/';
	 *		$url->query = 'a=1&a=2';
	 *		echo $url; // => 'http://www.ariadne-cms.org/docs/search/?a=1&a=2'
	 */
	class Url {

		/**
		 *	All parts of the URL format, as returned by parse_url.
		 *	scheme://user:pass@host:port/path?query#fragment
		 */
		public $scheme, $user, $pass, $host, $port, $path, $fragment;
		private $query;

		/**
		 *	@param string $url The URL to parse, the query part will remain a string.
		 *  @param QueryInterface queryObject Optional. An object that parses the query string.
		 */
		public function __construct( $url, $queryObject = null ) {
			$componentList = array( 
				'scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'
			);
			$this->importUrlComponents( parse_url( $url ), $componentList );
			if ( isset( $queryObject ) ) {
				$this->query = $queryObject->import( $this->query );
			}
		}

		public function __toString() {
			return $this->getSchemeAndAuthority() . $this->getPath() . $this->getQuery() . $this->getFragment();
		}

		public function __get( $name ) {
			switch( (string) $name ) {
				case 'password':
					return $this->pass;
				break;
				case 'query':
					return $this->query;
				break;
			}
		}

		public function __set( $name, $value ) {
			switch( (string) $name ) {
				case 'password':
					$this->pass = $value;
				break;
				case 'query':
					if ( is_object( $this->query ) ) {
						$this->query->reset()->import( $value );
					} else {
						$this->query = $value;
					}
				break;
			}
		}

		public function __clone() {
			if ( is_object( $this->query ) ) {
				$this->query = clone $this->query;
			}
		}

		private function importUrlComponents( $components, $validComponents ) {
			array_walk( $validComponents, function( $componentName ) use ( $components ) {
				$this->{$componentName} = ( isset( $components[$componentName] ) ? $components[$componentName] : '' );
			} );
		}

		// note: both '//google.com/' and 'file:///C:/' are valid URL's - so if either a scheme or host is set, add the // part
		private function getSchemeAndAuthority() {
			return ( ( $this->scheme || $this->host ) ? $this->getScheme() . '//' . $this->getAuthority() : '' );
		}

		private function getScheme() {
			return ( $this->scheme ? $this->scheme . ':' : '' );
		}

		private function getAuthority() {
			return ( $this->host ? $this->getUser() . $this->host . $this->getPort() : '' );
		}

		private function getUser() {
			return ( $this->user ? $this->user . $this->getPassword() . '@' : '' );
		}

		private function getPassword() {
			return ( $this->pass ?  ':' . $this->pass : '' );
		}

		private function getPort() {
			return ( $this->port ? ':' . $this->port : '' );
		}

		// note: if either a scheme or host is set, the path _must_ be made absolute or the URL will be invalid
		private function getPath() {
			$path = $this->path;
			if ( ( $this->host || $this->scheme ) && ( !$path || $path[0] !== '/' ) ) {
				$path = '/' . $path;
			}
			return $path;
		}

		private function getQuery() {
			$query = (string) $this->query; // convert explicitly to string first, because the query object may exist but still return an empty string
			return ( $query ? '?' . $query : '' );
		}

		private function getFragment() {
			return ( $this->fragment ? '#' . $this->fragment : '' );
		}

	}
