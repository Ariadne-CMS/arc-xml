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
		SafeUrl parses a URL string and returns an object with the seperate parts. You can change
		these and when cast to a string SafeUrl will regenerate the URL string and make sure it
		is valid.
		SafeUrl doesn't parse the query string, so it isn't destroyed by PHP's parse_str method.
		See ParsedUrl for a class that does parse the query string.

		Usage:
			$url = new \arc\url\SafeUrl( 'http://www.ariadne-cms.org/' );
			$url->path = '/docs/search/';
			$url->query = 'a=1&a=2';
			echo $url; // => 'http://www.ariadne-cms.org/docs/search/?a=1&a=2'
	*/
    class SafeUrl {
		protected $components;

		/**
			All parts of the URL format, as returned by parse_url.
			scheme://user:pass@host:port/path?query#fragment
		*/
		public $scheme, $user, $pass, $host, $port, $path, $query, $fragment;

		/**
			Reference to $this->pass, since its the only property that is shortened by parse_url.
		*/
		public $password;

		/**
			@param string $url The URL to parse, the query part will remain a string.
		*/
		public function __construct( $url ) {
			$componentList = array( 
				'scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment'
			);
			$components = parse_url( $url );
			foreach( $componentList as $component ) {
				$this->{$component} = $components[$component];
			}
			$this->password = &$this->pass;
		}

		public function __toString() {
			$url = '';
			if ( isset($this->host) ) {
				if ( isset($this->scheme) ) {
					$url .= $this->scheme . '://';
				}
				if ( isset($this->user) ) {
					$url .= $this->user;
					if ( isset($this->pass) ) {
						$url .= ':' . $this->pass;
					}
					$url .= '@';
				}
				$url .= $this->host;
				if ( isset($this->port) ) {
					$url .= ':' . $this->port;
				}
				if ( isset($this->path) ) {
					if ( substr( $this->path, 0, 1 ) !== '/' ) {
						$url .= '/';
					}
				}
			}
			$url .= $this->path;
			$query = (string) $this->query; // ParsedUrl has a query object with a __toString
			if ( $query ) {
				$url .= '?' . $query ;
			}
			if ( isset($this->fragment) ) {
				$url .= '#' . $this->fragment;
			}
			return $url;
		}

	}
