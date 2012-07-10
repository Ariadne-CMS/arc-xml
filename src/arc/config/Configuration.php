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

	class Configuration {

		protected $configuration = array();
		protected $context = null;

		public function __construct( $context = null ) {
			$this->context = $context;
		}

		protected function getPath( $path ) {
			if ( !isset( $path ) ) {
				if ( isset( $this->context ) ) {
					$path = $this->context->getPath();
				} else {
					$path = '/';
				}
			}
			return $path;
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

		public function acquire( $name, $path = null ) {
			$path = $this->getPath( $path );
			$parents = \arc\path::parents( $path );
			$parents = array_reverse( $parents );
			$result = array();
			foreach ( $parents as $parent ) {
				if ( isset( $this->configuration[$parent] ) ) {
					$value = $this->getValue( $this->configuration[$parent], $name );
					if ( isset( $value ) ) {
						if ( is_array( $value ) ) {
							$result = $result + $value; // FIXME: smart recursive merge needed
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

		public function get( $path ) {
			return new ConfigurationPath( $this, $path );
		}

	}
