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

	class Configuration implements \arc\KeyValueStoreInterface, ConfigurationInterface {

		protected $contextStack = null;
		protected $configuration = array();

		public function __construct( $contextStack = null ) {
			$this->contextStack = $contextStack;
		}

		protected function getPath( $path ) {
			return \arc\path::normalize( $path, $this->contextStack ? $this->contextStack['path'] : '/' );
		}

		protected function getFilledPath( $path, $name = '' ) {
			if ( $path == '/' ) {
				return $path;
			}
			$parent = $path;
			do {
				$path = $parent;
				$config = $this->configuration[$path];
				$parent = \arc\path::parent( $path );
			} while ( !isset( $config ) && $parent );
			if ( !isset($config) ) {
				return null;
			}
			return $path;
		}

		protected function getValue( $config, $name ) {
			$vars = explode('.', $name);
			foreach( $vars as $var ) {
				if ( !isset( $config[$var] ) ) {
					return null;
				}
				$config = $config[$var];
			}
			return $config;
		}

		// ConfigurationInterface
		public function acquire( $name, $path = null, $root = '/' ) {
			$path = $this->getPath( $path );
			$parents = \arc\path::parents( $path, $root );
			$parents = array_reverse( $parents );
			$result = null;
			foreach ( $parents as $parent ) {
				if ( isset( $this->configuration[$parent] ) ) {
					$value = $this->getValue( $this->configuration[$parent], $name );
					if ( isset( $value ) ) {
						if ( is_array( $value ) ) {
							$result = array_replace_recursive( $value, (array) $result );
						} else {
							return $value;
						}
					}
				}
			}
			return $result;
		}

		public function configure( $name, $value, $path = null ) {
			$path = $this->getPath( $path );
			$config = &$this->configuration[$path];
			if ( !isset( $config ) ) {
				$this->configuration[$path] = $config = array();
			}
			$vars = explode('.', $name);
			foreach( $vars as $var ) {
				if ( !isset( $config[$var] ) ) {
					$config[$var] = array();
				}
				$config = &$config[$var];
			}
			$config = $value;
		}

		public function cd( $path ) {
			return new ConfigurationPath( $this, $path );
		}

		public function root( $root ) {
			$root = \arc\path::normalize( $root );
			return new ConfigurationPath( $this, $path, $root );	
		}

		public function ls( $path = null ) {
			$path = $this->getPath( $path );
			$parents = \arc\path::parents( $path, $root );
			$parents = array_reverse( $parents );
			$result = array();
			foreach ( $parents as $parent ) {
				if ( isset( $this->configuration[$parent] ) ) {
					$result = array_replace_recursive( $this->configuration[$parent], $result );
				}
			}
			return $result;
			/*$path = $this->getPath( $path );
			return $this->configuration[$path];*/
		}

		public function getConfiguredValue( $name, $path = null ) {
			$path = $this->getPath( $path );
			$value = null;
			if ( isset( $this->configuration[$path] ) ) {
				$value = $this->getValue( $this->configuration[$path], $name );
			}
			return $value; 
		}

		public function getConfiguredValues( $path = null ) {
			$path = $this->getPath( $path );
			return $this->configuration[$path];
		}

		public function getConfiguredPath( $name, $path = null, $root = '/' ) {
			$path = $this->getPath( $path );
			$parents = \arc\path::parents( $path, $root );
			$parents = array_reverse( $parents );
			$result = array();
			foreach ( $parents as $parent ) {
				if ( isset( $this->configuration[$parent] ) ) {
					$value = $this->getValue( $this->configuration[$parent], $name );
					if ( isset( $value ) ) {
						return $parent;
					}
				}
			}
		}

		// \arc\KeyValueStoreInterface
		public function getVar( $name ) {
			return $this->acquire( $name );
		}

		public function putVar( $name, $value ) {
			return $this->configure( $name, $value );
		}
	}
