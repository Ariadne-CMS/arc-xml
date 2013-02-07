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

	/**
	*	This object is returned for each event you listen for. It allows you to specifically remove 
	*	that event listener.
	*/
	class Listener {
		protected $capture    = false;
		protected $eventName  = '';
		protected $id         = null;
		protected $eventStack = null;

		/**
		*	@param string $eventName The name of the event
		*	@param int $id The id of this listener. This is unique for this eventStack.
		*	@param string $path Optional. The path listened on, defaults to '/'
		*	@param bool $capture Optional. True for capture phase listeners, default is false.
		*	@param Stack $eventStack Optional. The eventStack the listener is registered on. Must be set 
		*		for remove() to work.
		*/
		public function __construct( $eventName, $id, $capture = false, $eventStack = null ) {
			$this->eventName  = $eventName;
			$this->capture    = $capture;
			$this->id         = $id;
			$this->eventStack = $eventStack;
		}

		/**
		*	This method removes this listener from the eventStack. If a matching event is fired later, 
		*	the corresponding listener callback will no longer be called.
		*/
		public function remove() {
			if ( isset($this->id) ) {
				$this->eventStack->removeListener( $this->eventName, $this->id, $this->capture );
			}
		}

		/*
		 *   This allows you to chain listeners and cd() calls on the eventtree.
		 */
		public function __call( $method, $args ) {
			return call_user_func_array( [ $this->eventStack, $method ], $args );
		}

		/* TODO: add an add() method, which re-adds the listener, potentially as last in the list */
	}
