<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\grants;

	class GrantsConfiguration {
		protected $context = null;
		protected $user = null;
		protected $root = null;
		protected $path = null;
		
		public function __construct( $context = null, $user = null, $path = null ) {
			$this->context = $context;
			$this->user = $user;
			$this->path = $path;
		}

		public function cd( $path ) {
			$path = \arc\path::collapse( $path, $this->path );
			return new GrantsConfiguration( $this->context, $this->id, $path );
		}

		public function ls() {
			$index = 'grants';
			if ( $this->user ) {
				$index .= '.'.$this->user;
			}
			return $this->config->acquire( $index );
		}

		public function user( $user ) {
			return new GrantsConfiguration( $this->config, $user, $this->path );
		}

		public function set( $grants ) {
			$user = isset( $this->user) ? $this->user : $this->context['arc.user'];
			// save as string with a leading and trailing space, for faster comparison in check()
			$this->config->configure( 'grants.'.$user, ' '.trim($grants).' ' );
		}

		public function check( $grant ) {
			// uses strpos since it is twice as fast as preg_match for the most common cases
			$user = isset( $this->user) ? $this->user : $this->context['arc.user'];
			$grants = $this->config->acquire( 'grants.'.$user ); //, null, $this->root );
			if ( strpos( $grants, $grant.' ' ) === false ) { // exit early if no possible match is found
				return false;
			}
			if ( strpos( $grants, ' '.$grant.' ') !== false ) {	// usual case is checked first - simple grant
				return true;
			}
			if ( strpos( $grants, ' >'.$grant.' ') !== false ) { // less usual case, grants set only for children
				$grantsLocal = $this->config->getConfiguredValue( 'grants.'.$id );
				return !isset($grantsLocal);
			}
			if ( strpos( $grants, ' ='.$grant.' ') !== false ) { // least usual case, grants set only for the configured path
				$grantsLocal = $this->config->getConfiguredValue( 'grants.'.$id );
				return isset($grantsLocal);
			}
			return false;
		}

	}