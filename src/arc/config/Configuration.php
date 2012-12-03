<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\config;

	class Configuration implements ConfigurationInterface, \arc\KeyValueStoreInterface {
	
		private $tree = null;
		
		public function __construct( $tree) {
			$this->tree = $tree;
		}
		
		public function acquire( $name ) {
			return \arc\tree::Dive( 
				$this->tree,
				function( $node ) use ( $name ){
					return $this->getValueIfRoot( $name, $node->nodeValue );
				},
				function( $node, $result ) use ( $name ) {
					return $this->mergeValue( $result, $this->getValue( $name, $node->nodeValue ) );
				}
			);
		}

		public function configure( $name, $value ) {
			if ( !isset( $this->tree->nodeValue ) ) {
				$this->tree->nodeValue = array();
			}
			$this->setValue( $name, $value, $this->tree->nodeValue );
			return $this;
		}
		
		public function cd( $path ) {
			return new Configuration( $this->tree->cd( $path ) );
		}
	
		public function ls() {
			return $this->tree->ls( function( $node ) {
				return new Configuration( $node );
			} );
		}

		// \arc\KeyValueStoreInterface
		public function getVar( $name ) {
			return $this->acquire( $name );
		}

		public function putVar( $name, $value ) {
			return $this->configure( $name, $value );
		}

		private function getValue( $name, $config ) {
			$vars = explode('.', $name);
			$entry = $config;
			foreach( $vars as $var ) {
				if ( !isset( $entry[$var] ) ) {
					return null;
				}
				$entry = $entry[$var];
			}
			return $entry;
		}

		private function setValue( $name, $value, &$config ) {
			$vars = explode('.', $name);
			$lastName = array_pop( $vars );
			$entry = &$config;
			foreach( $vars as $var ) {
				if ( !isset( $entry[$var] ) ) {
					$entry[$var] = array();
				}
				$entry = &$entry[$var];
			}
			if ( !is_array( $entry ) ) {
				throw \arc\ExceptionDefault( 'Unable to configure '.$name.', parent set to non-array value', \arc\exceptions::FIXME );
			} else {
				$entry[ $lastName ] = $value;
			}
		}

		private function getValueIfRoot( $name, $config ) {
			$value = $this->getValue( $name, $config );
			if ( !$this->isHash( $value ) ) {
				return $value;
			}
		}

		private function isHash( $array ) {
			return ( is_array( $array ) && !is_numeric( key( $array ) ) );
		}
		
		private function mergeValue( $initial, $additional ) {
			if ( isset( $additional ) ) {
				if ( !$this->isHash( $initial ) ) {
					return $additional;
				} else if ( $this->isHash( $additional ) ) {
					return array_replace_recursive( $initial, $additional );
				} else {
					return $additional;
				}
			}
			return $initial;
		}
			
	}
?>