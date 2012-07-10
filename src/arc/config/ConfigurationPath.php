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

	class ConfigurationPath implements \arc\KeyValueStoreInterface {

		protected $configuration = null;
		protected $path = '/';

		public function __construct( $configuration, $path ) {
			$this->configuration = $configuration;
			$this->path = $path;
		}

		public function acquire( $name ) {
			return $this->configuration->acquire( $name, $this->path );
		}

		public function configure( $name, $value ) {
			return $this->configuration->configure( $name, $value, $this->path );
		}

		public function getVar( $name ) {
			return $this->acquire( $name );
		}

		public function putVar( $name, $value ) {
			return $this->configure( $name, $value );
		}

	}
