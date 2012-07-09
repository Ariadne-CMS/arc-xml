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
	
	class IncompleteListener {
		private $path = '/';
		private $eventName = null;
		private $objectType = null;
		private $capture = false;
		private $stack;
		
		public function __construct( $path, $eventName = null, $objectType = null, $capture = false, $stack = null ) {
			$this->path = $path;
			$this->eventName = $eventName;
			$this->objectType = $objectType;
			$this->capture = $capture;
			$this->stack = $stack;
		}
		
		public function call( $method, $args = array() ) {
			return $this->stack->addListener( $this->path, $this->eventName, $this->objectType, $method, $args, $this->capture);
		}
		
		public function listen( $eventName, $objectType = null ) {
			$this->eventName = $eventName;
			$this->objectType = $objectType;
			$this->capture = false;
			return $this;
		}
		
		public function capture( $eventName, $objectType = null ) {
			$this->eventName = $eventName;
			$this->objectType = $objectType;
			$this->capture = true;
			return $this;
		}
		
		public function fire($eventName, $eventData, $objectType = null) {
			return $this->stack->fire($eventName, $eventData, $objectType = null, $this->path);
		}
	}
?>