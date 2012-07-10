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
	
	/* ParsedUrl tries to parse the query part, so if it isn't compatible with PHP it won't survive */
	class ParsedUrl extends SafeUrl implements \arc\KeyValueStoreInterface {
	
		private $components, $query, $skipQueryParsing;
		
		public function __construct( $url ) {
			parent::__construct( $url );
			$this->query = new UrlQuery( $this->components['query'] );
		}
		
		public function __get( $var ) {
			if ( $var == 'query' ) {
				return $this->query;
			} else {
				return parent::__get($var);
			}
		}
		
		public function __set( $var, $value ) {
			switch($var) {
				case 'query' :
					if ( is_string( $value ) ) {
						$this->query = new UrlQuery( $value );
					} else if ( $value instanceof UrlQuery ) {
						$this->query = $value;
					} else if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
						$this->query = new UrlQuery( $value );
					}
				break;
				default :
					parent::__set( $var, $value );
				break;
			}
		}

		public function __toString() {
			$this->components['query'] = ''.$this->query;
			$result = parent::__toString();
			return $url;
		}
		
		public function getvar( $name ) {
			return $this->query->$name;
		}
		
		public function putvar( $name, $value ) {
			$this->query->{$name} = $value;
		}

		public function import( $values ) {
			$this->query->import( $values );
		}
		
	}
?>