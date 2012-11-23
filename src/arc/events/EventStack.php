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
		This class implements an event stack on which listeners can be added and removed and events can be fired.
	*/
	class EventStack implements EventStackInterface {

		protected $contextStack = null;
		protected $listeners = array();
		protected $event = null;

		/**
			@param \arc\context\ContextStack $contextStack Optional. Used to get the current path: 
				$contextStack['arc.path']
		*/
		public function __construct( $contextStack = null ) {
			$this->contextStack = $contextStack;
		}

		/**
			Returns an IncompleteListener for the given event, objectType and path.

			Usage:
				\arc\events::listen( 'onsave' )->call( function( $event ) { 
					$path = $event->data['path'];
					if ( $path == '/foo/bar/' ) {
						$event->preventDefault();
						return false; // cancel all other listeners
					}
				});

			@param string $eventName The event to listen for
		*/
		public function listen( $eventName ) {
			$path = isset( $this->contextStack ) ? $this->contextStack['arc.path'] : '/';
			return new IncompleteListener( $this, $eventName, $path, false );
		}

		/**
			Returns an IncompleteListener for the given event, objectType and path. The listener
			will trigger in the capture phase - before any listeners in the listen phase.

			@param string $eventName The name of the event to listen for.
			@returns IncompleteListener 

		*/
		public function capture( $eventName) {
			$path = isset( $this->contextStack ) ? $this->contextStack['arc.path'] : '/';
			return new IncompleteListener( $this, $eventName, $path, true );
		}

		/**
			Fires an event. If the event objects preventDefault() method has been called it
			will return false, otherwise the - potentially changed - eventData will be returned.

			Usage:
				$eventData = \arc\events::fire( 'onbeforesave', array( 'path' => '/foo/bar/' ) );
				if ( $eventData ) {
					$path = $eventData['path'];
					// now save it
				}

			@param string $eventName The name of the event to fire.
			@param array $eventData Optional. Data passed to each handler through the event object.
			@param string $path Optional. The path to fire the event on.
			@returns false or $eventData - which may have been modified
		*/
		public function fire( $eventName, $eventData = array(), $path = '/' ) {
			if ( !isset($this->listeners['capture'][$eventName])
				&& !isset($this->listeners['listen'][$eventName]) ) {
				return $eventData; // no listeners for this event, so dont bother searching
			}
			$event = new Event( $eventName, $eventData );
			$event->data['target'] = $path; // also makes sure that fire() returns a true-ish value by default
			// first run the capture phase listeners
			if( 
				!isset(  $this->listeners['capture'][$eventName] ) ||
				$this->walkListeners( $event, $this->listeners['capture'][$eventName], $path, true )
			) {
				if ( isset( $this->listeners['listen'][$eventName] ) ) {
					// only if the event isn't cancelled continue with the listen phase
					$this->walkListeners( $event, $this->listeners['listen'][$eventName], $path, false );
				}
			}
			if ( $event->preventDefault ) {
				$result = false;
			} else {
				$result = $event->data;
			}
			return $result;
		}

		/**
			Calls each listener with the given event untill a listener returns false.
		*/
		protected function walkListeners( $event, $listeners, $path, $capture ) {
			$result = \arc\path::walk( $path, function( $parent ) use ( $listeners, $event ) {
				if (isset( $listeners[$parent] ) ) {
					foreach ( (array) $listeners[$parent] as $listener ) {
						$result = call_user_func( $listener['method'], $event );
						if ( $result === false ) {
							return false; // this will stop \arc\path::walk, so other event handlers won't be called
						}
					}
				}
			}, $capture );
			return !isset( $result ) ? true : false;
		}

		/**
			Returns an IncompleteListener with the given path.
			@param string $path The path to listen or fire an event.
		*/
		public function cd( $path ) {
			$path = \arc\path::collapse( $path, $this->contextStack ? $this->contextStack['arc.path'] : '/' );
			return new IncompleteListener( $this, null, $path, false );
		}

		/**
			Creates nested array keys if they are missing, prevents warnings from missing array keys by php.
		*/
		protected function createArray( $array ) {
			// create a nested array, prevents PHP notices
			$args = func_get_args();
			array_shift( $args ); // remove $array
			foreach ( $args as $arg ) {
				if ( !isset($array[ $arg ]) ) {
					$array[ $arg ] = array();
				}
				$array = &$array[ $arg ];
			}
		}

		/**
			Non-fluent api method to add a listener.
			@param string $eventName The name of the event to listen for.
			@param Callable $callback The callback method to call.
			@param string $path Optional. The path to listen on, default is '/'
			@param bool $capture Optional. If true listen in the capture phase. Default is false - listen phase.
			@returns Listener
		*/
		public function addListener( $eventName, $callback, $path='/', $capture = false ) {
			$when = $capture ? 'capture' : 'listen';
			if ( ! is_callable($callback) ) {
				throw new \arc\ExceptionIlligalRequest('Method is not callable.', \arc\exceptions::ILLEGAL_ARGUMENT);
			}
			$this->createArray( $this->listeners, $when, $eventName, $path );
			$id = isset($this->listeners[$when][$eventName][$path]) ?
				count( $this->listeners[$when][$eventName][$path] ) : 0 ;
			$this->listeners[$when][$eventName][$path][$id] = array(
				'method' => $callback
			);
			return new Listener( $eventName, $id, $path, $capture, $this );
		}

		/**
			Non-fluent api method to remove a listener.
			@param string $eventName The name of the event the listener is registered for.
			@param int $id The id of the listener.
			@param string $path Optional. The path the listener is registered on. Default is '/'.
			@param bool $capture Optional. If true the listener is triggered in the capture phase. 
				Default is false.
		*/
		public function removeListener( $eventName, $id, $path = '/', $capture = false ) {
			$when = ($capture) ? 'capture' : 'listen';
			unset( $this->listeners[$when][$eventName][$path][$id] );
		}
	}
