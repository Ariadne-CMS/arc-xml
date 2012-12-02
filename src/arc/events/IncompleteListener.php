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
	*	This class is an information container for the fluent interface. 
	*/
	class IncompleteListener implements EventsTreeInterface {
		protected $eventName  = null;
		protected $capture    = false;
		protected $eventsTree = null;

		/**
		*	@param Stack $eventsTree Reference to the eventsTree used.
		*	@param string $eventName Optional. Name of the event to listen for.
		*	@param string $path Optional. The path to listen or fire on.
		*	@param bool $capture Optional. Whether to listen in the capture phase. Defaults to false.
		*/
		public function __construct( $eventsTree, $eventName = null, $capture = false ) {
			$this->eventName  = $eventName;
			$this->capture    = $capture;
			$this->eventsTree = $eventsTree;
		}

		/**
		*	Will call the given callback method for each event that matches the earlier given eventName.
		*	@param Callable $method The callback method. The first argument is always the event object.
		*	@return Listener
		*/
		public function call( $method ) {
			return $this->eventsTree->addListener( $this->eventName, $method, $this->capture );
		}

		/**
		*	Will add the listen information needed for a Listener.
		*	@param string $eventName The event to listen for
		*	@return IncompleteListener itself
		*/
		public function listen( $eventName ) {
			$this->eventName  = $eventName;
			$this->capture    = false;
			return $this;
		}

		/**
		*	Will add the listen information needed for a Listener and set the capture flag to true.
		*	@param string $eventName The event to listen for
		*	@return IncompleteListener itself
		*/
		public function capture( $eventName ) {
			$this->eventName  = $eventName;
			$this->capture    = true;
			return $this;
		}

		/**
		*	Will fire the given event on the earlier - with cd() - set path.
		*	@param string $eventName The name of the event to fire
		*	@param mixed $eventData Optional. Extra information for the event listeners.
		*	@return bool|mixed false if $event->preventDefault() is called in a listener, 
		*		$eventData otherwise.
		*/
		public function fire( $eventName, $eventData = array() ) {
			return $this->eventsTree->fire( $eventName, $eventData );
		}

		/**
		*	Returns a new IncompleteListener with its path changed to the given path
		*	@param string $path The new path.
		*	@return IncompleteListener
		*/
		public function cd( $path ) {
			return new IncompleteListener( $this->eventsTree->cd( $path ), $this->eventName, $this->capture );
		}
	}
