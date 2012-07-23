<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\events;

	class IncompleteListener implements StackInterface {
		private $path       = '/';
		private $eventName  = null;
		private $objectType = null;
		private $capture    = false;
		private $eventStack = null;

		public function __construct( $eventStack, $eventName = null, $path = '/', $objectType = null, $capture = false ) {
			$this->path       = $path;
			$this->eventName  = $eventName;
			$this->objectType = $objectType;
			$this->capture    = $capture;
			$this->eventStack = $eventStack;
		}

		public function call( $method, $args = array() ) {
			return $this->eventStack->addListener( $this->eventName, $method, $args, $this->path, $this->objectType, $this->capture );
		}

		public function listen( $eventName, $objectType = null, $capture = false ) {
			$this->eventName = $eventName;
			$this->objectType = $objectType;
			$this->capture = $capture;
			return $this;
		}

		public function capture( $eventName, $objectType = null ) {
			$this->eventName = $eventName;
			$this->objectType = $objectType;
			$this->capture = true;
			return $this;
		}

		public function fire( $eventName, $eventData = array(), $objectType = null, $path = null ) {
			if ( !isset( $path ) ) {
				$path = $this->path;
			} else if ( isset($this->path) ) {
				$path = \arc\path::normalize( $path, $this->path );
			}
			return $this->eventStack->fire( $eventName, $eventData, $objectType, $path );
		}

		public function cd( $path ) {
			$path = \arc\path::normalize( $path, $this->path );
			return new IncompleteListener( $this->eventStack, $path, null, null, false );
		}
	}
