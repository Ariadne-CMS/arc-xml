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
	
	class UrlQuery extends \ArrayObject implements \arc\KeyValueStoreInterface {
		
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
		
		public function getvar( $name ) {
			return $this->offsetGet($name);
		}
		
		public function putvar( $name, $value ) {
			$this->offsetSet($name, $value);
		}

		public function __toString() {
			$arguments = (array) $this;
			$arguments = \arc\tainting::untaint( $arguments, FILTER_UNSAFE_RAW );
			$result = http_build_query( (array) $arguments );
			$result = str_replace( '%7E', '~', $result ); // incorrectly encoded, obviates the need for oauth_encode_url
			return $result;
		}
		
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

	}
