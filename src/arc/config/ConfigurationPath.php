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
		protected $path = '/';

		public function __construct( $configuration, $path ) {
			$this->configuration = $configuration;
			$this->path = $path;
		}

		// ConfigurationInterface
		public function acquire( $name ) {
			return $this->configuration->acquire( $name, $this->path );
		}

		public function configure( $name, $value ) {
			return $this->configuration->configure( $name, $value, $this->path );
		}

		public function cd( $path ) {
			$path = \arc\path::normalize( $path, $this->path );
			return new ConfigurationPath( $this->configuration, $path );
		}

		// \arc\KeyValueStoreInterface
		public function getVar( $name ) {
			return $this->acquire( $name );
		}

		public function putVar( $name, $value ) {
			return $this->configure( $name, $value );
		}

	}
