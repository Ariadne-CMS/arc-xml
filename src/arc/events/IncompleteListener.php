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
		This class is an information container for the fluent interface. 
	*/
	class IncompleteListener implements EventStackInterface {
		protected $path       = '/';
		protected $eventName  = null;
		protected $capture    = false;
		protected $eventStack = null;

		/**
			@param Stack $eventStack Reference to the eventStack used.
			@param string $eventName Optional. Name of the event to listen for.
			@param string $path Optional. The path to listen or fire on.
			@param bool $capture Optional. Whether to listen in the capture phase. Defaults to false.
		*/
		public function __construct( $eventStack, $eventName = null, $path = '/', $capture = false ) {
			$this->path       = $path;
			$this->eventName  = $eventName;
			$this->capture    = $capture;
			$this->eventStack = $eventStack;
		}

		/**
			Will call the given callback method for each event that matches the earlier given eventName.
			@param Callable $method The callback method. The first argument is always the event object.
			@param array $args Optional. List of extra arguments to pass to the callback method.
			@returns Listener
		*/
		public function call( $method ) {
			return $this->eventStack->addListener( $this->eventName, $method, $this->path, $this->capture );
		}

		/**
			Will add the listen information needed for a Listener.
			@param string $eventName The event to listen for
			@returns IncompleteListener itself
		*/
		public function listen( $eventName ) {
			$this->eventName  = $eventName;
			$this->capture    = false;
			return $this;
		}

		/**
			Will add the listen information needed for a Listener and set the capture flag to true.
			@param string $eventName The event to listen for
			@returns IncompleteListener itself
		*/
		public function capture( $eventName ) {
			$this->eventName  = $eventName;
			$this->capture    = true;
			return $this;
		}

		/**
			Will fire the given event on the earlier - with cd() - set path.
			@param string $eventName The name of the event to fire
			@param mixed $eventData Optional. Extra information for the event listeners.
			@returns bool|mixed false if $event->preventDefault() is called in a listener, 
				$eventData otherwise.
		*/
		public function fire( $eventName, $eventData = array() ) {
			return $this->eventStack->fire( $eventName, $eventData, $this->path );
		}

		/**
			Returns a new IncompleteListener with its path changed to the given path
			@param string $path The new path.
			@returns IncompleteListener
		*/
		public function cd( $path ) {
			$path = \arc\path::collapse( $path, $this->path );
			return new IncompleteListener( $this->eventStack, $this->eventName, $path, $this->capture );
		}
	}
