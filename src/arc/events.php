<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc;

	/**
	 *	This component implements an event system very similar to events in modern browsers. Events have 
	 *	a seperate capture and listen phase. Events are fired and listened to on a 'path' - like a 
	 *	filesystem path - instead of an object in the DOM. In the capture phase listeners are called in
	 *	order starting with listeners on the root path '/'. Then - if the event has not been cancelled -
	 *	in the listen phase listeners are called in the reverse order - with the root path being called 
	 *	last.
	 *	If the context stack is available you can change the default path events are fired on and listened 
	 *	to by changing the 'path' entry in the context stack.
	 *
	 *	@requires \arc\path
	 *	@suggests \arc\context
	 */
	class events extends Pluggable {

		protected static $stack;

		/**
		 *	Factory method for the static stack. Returns the shared stack only. Use new \arc\events\Stack 
		 *	or your own factory method to create a seperate Stack instance.
		 */
		public static function getStack() {
			if ( !self::$stack ) {
				$context = class_exists( '\arc\context' ) ? context::getStack() : null;
				self::$stack = new events\EventStack( $context );
			}
			return self::$stack;
		}

		/**
		 *	Returns an IncompleteListener for the given event, objectType and path.
 		 *
		 *	Usage:
		 *		\arc\events::listen( 'onsave' )->call( function( $event ) { 
		 *			$path = $event->data['path'];
		 *			if ( $path == '/foo/bar/' ) {
		 *				$event->preventDefault();
		 *				return false; // cancel all other listeners
		 *			}
		 *		});
		 *
		 *	@param string $eventName The name of the event to listen for.
		 *	@return IncompleteListener 
		 */
		public static function listen( $eventName ) {
			return self::getStack()->listen( $eventName );
		}

		/**
		 *	Returns an IncompleteListener for the given event, objectType and path. The listener
		 *	will trigger in the capture phase - before any listeners in the listen phase.
 		 *
		 *	@param string $eventName The name of the event to listen for.
		 *	@return IncompleteListener 
		 */
		public static function capture( $eventName ) {
			return self::getStack()->capture( $eventName );
		}

		/**
		 *	Fires an event. If the event objects preventDefault() method has been called it
		 *	will return false, otherwise the - potentially changed - eventData will be returned.
		 *
		 *	Usage:
		 *		$eventData = \arc\events::fire( 'onbeforesave', array( 'path' => '/foo/bar/' ) );
		 *		if ( $eventData ) {
		 *			$path = $eventData['path'];
		 *			// now save it
		 *		}
		 *
		 *	@param string $eventName The name of the event to fire.
		 *	@param mixed $eventData Optional. Data passed to each handler through the event object.
		 *	@return false or $eventData - which may have been modified
		 */
		public static function fire( $eventName, $eventData = array() ) {
			return self::getStack()->fire( $eventName, $eventData );
		}

		/**
		 *	Returns a new IncompleteListener with the given path.
		 *	@param string $path The path to change to, may be a relative path.
		 *	@return IncompleteListener
		 */
		public static function cd( $path ) {
			return self::getStack()->cd( $path );
		}

	}

