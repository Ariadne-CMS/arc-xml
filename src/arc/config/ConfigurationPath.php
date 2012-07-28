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

	class ConfigurationPath implements \arc\KeyValueStoreInterface, ConfigurationInterface {

		protected $configuration = null;
		protected $path = null;
		protected $root = null;

		public function __construct( $configuration, $path, $root = '/' ) {
			$this->configuration = $configuration;
			$this->path = $path;
			$this->root = $root;
		}

		// ConfigurationInterface
		public function acquire( $name, $path = null, $root = null ) {
			$path = \arc\path::collapse( $path, $this->path );
			$root = \arc\path::collapse( $root, $this->root );
			return $this->configuration->acquire( $name, $path, $root );
		}

		public function configure( $name, $value ) {
			return $this->configuration->configure( $name, $value, $this->path );
		}

		public function cd( $path ) {
			$path = \arc\path::collapse( $path, $this->path );
			return new ConfigurationPath( $this->configuration, $path );
		}

		public function ls() {
			return $this->configuration->ls( $this->path );
		}

		public function root( $root ) {
			$root = \arc\path::collapse( $root, $this->path );
			return new ConfigurationPath( $this->configuration, $this->path, $root );
		}

		public function getConfiguredValue( $name, $path = null ) {
			$path = \arc\path::collapse( $path, $this->path );
			return $this->configuration->getConfiguredValue( $name, $path );
		}

		public function getConfiguredPath( $name, $path = null, $root = '/' ) {
			$path = \arc\path::collapse( $path, $this->path );
			return $this->configuration->getConfiguredPath( $name, $path, $root );
		}

		// \arc\KeyValueStoreInterface
		public function getVar( $name ) {
			return $this->acquire( $name );
		}

		public function putVar( $name, $value ) {
			return $this->configure( $name, $value );
		}

	}
