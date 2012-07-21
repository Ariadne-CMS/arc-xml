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

	/* SafeUrl doesn't parse the query part, so it isn't destroyed by PHP's parse_str method */
	class SafeUrl {
		protected $components;

		public function __construct( $url ) {
			$this->components = parse_url( $url );
		}

		public function __get($var) {
			if ( $var == 'password' ) {
				$var = 'pass';
			}
			if ( isset( $this->components[$var] ) ) {
				return $this->components[$var];
			} else {
				return null;
			}
		}

		public function __set($var, $value) {
			switch($var) {
				case 'path' :
					$this->components[$var] = $value;
				break;
				case 'password' :
					$var = 'pass';
					$this->components[$var] = $value;
				break;
				case 'query' :
				case 'scheme':
				case 'host' :
				case 'port' :
				case 'user' :
				case 'pass' :
				case 'fragment' :
					$this->components[$var] = $value;
				break;
			}
		}

		public function __toString() {
			$url = '';
			if ( isset($this->components['host']) ) {
				if ( isset($this->components['scheme']) ) {
					$url .= $this->components['scheme'] . '://';
				}
				if ( isset($this->components['user']) ) {
					$url .= $this->components['user'];
					if ( isset($this->components['pass']) ) {
						$url .= ':' . $this->components['pass'];
					}
					$url .= '@';
				}
				$url .= $this->components['host'];
				if ( isset($this->components['port']) ) {
					$url .= ':' . $this->components['port'];
				}
				if ( isset($this->components['path']) ) {
					if ( substr( $this->components['path'], 0, 1 ) !== '/' ) {
						$url .= '/';
					}
				}
			}
			$url .= $this->components['path'];
			$query = '' . $this->components['query'];
			if ($query) {
				$url .= '?' . $query ;
			}
			if ( isset($this->components['fragment']) ) {
				$url .= '#' . $this->components['fragment'];
			}
			return $url;
		}

	}
