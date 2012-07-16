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

	class Stack implements StackInterface {

		protected $contextStack = null;
		protected $listeners = array();
		protected $event = null;

		public function __construct( $contextStack = null ) {
			$this->contextStack = $contextStack;
		}

		public function listen( $eventName, $objectType = null, $capture = false ) {
			$path = isset( $this->contextStack ) ? $this->contextStack['path'] : '/';
			return new IncompleteListener( $eventName, $path, $objectType, $capture, $this );
		}

		public function capture( $eventName, $objectType = null ) {
			return $this->listen( $eventName, $objectType, true );
		}

		public function fire( $eventName, $eventData = array(), $objectType = null, $path = null ) {
			$path = \arc\path::normalize( $path, $this->contextStack? $this->contextStack['path'] : '/' );
			if ( !isset($this->listeners['capture'][$eventName])
				&& !isset($this->listeners['listen'][$eventName]) ) {
				return $eventData; // no listeners for this event, so dont bother searching
			}
			$prevEvent = null;
			if ( $this->event ) {
				$prevEvent = $this->event; // remember current event, so you can fire events in an event handler
			}
			$this->event = new Event( $eventName, $eventData );
			// first run the capture phase listeners
			if( 
				!isset(  $this->listeners['capture'][$eventName] ) ||
				$this->walkListeners( $this->listeners['capture'][$eventName], $path, $objectType, true )
			) {
				if ( isset( $this->listeners['listen'][$eventName] ) ) {
					// only if the event isn't cancelled continue with the listen phase
					$this->walkListeners( $this->listeners['listen'][$eventName], $path, $objectType, false );
				}
			}

			if ( $this->event->preventDefault ) {
				$result = false;
			} else {
				$result = $this->event->data;
			}
			$this->event = $prevEvent;
			return $result;
		}

		protected function walkListeners( $listeners, $path, $objectType, $capture ) {
			$pathlist = \arc\path::parents( $path );
			if ( !$capture ) {
				$pathlist = array_reverse( $pathlist ); // listen phase runs listeners from path to root, capture from root to path
			}
			reset($pathlist);
			do {
				$currentPath = current( $pathlist );
				if ( isset(  $listeners[$currentPath] ) && is_array( $listeners[$currentPath] ) ) {
					foreach ( $listeners[$currentPath] as $listener ) {
						if ( !isset($listener['type']) ||
							 ( $listener['type'] == $objectType ) || // allows use of non-php 'types', no inheritance though
							 ( is_a( $objectType, $listener['type'] ) )
						) {
							// always add the event as the first argument to the method
							array_unshift( $listener['args'],  $this->event );
							$result = call_user_func_array( $listener['method'], $listener['args'] );
							// only stop walking over the listeners if a listener returns false
							if ( $result === false ) {
								return false;
							}
						}
					}
				}
			} while( next( $pathlist ) );
			return true;
		}

		public function event() {
			return $this->event;
		}

		public function get( $path ) {
			$path = \arc\path::normalize( $path, $this->contextStack ? $this->contextStack['path'] : '/' );
			return new IncompleteListener( null, $path, null, false, $this );
		}

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

		public function addListener( $eventName, $method, $args=null, $path='/', $objectType = null, $capture = false ) {
			$when = $capture ? 'capture' : 'listen';
			if ( ! is_callable($method) ) {
				throw new \arc\ExceptionIlligalRequest('Method is not callable.',\arc\exceptions::ILLEGAL_ARGUMENT);
			}
			$this->createArray( $this->listeners, $when, $eventName, $path );
			$id = isset($this->listeners[$when][$eventName][$path]) ?
				count( $this->listeners[$when][$eventName][$path] ) : 0 ;
			$this->listeners[$when][$eventName][$path][$id] = array(
				'type' => $objectType,
				'method' => $method,
				'args' => $args
			);
			return new Listener( $eventName, $id, $path, $capture, $this );
		}

		public function removeListener( $eventName, $id, $path = '/', $capture = false ) {
			$when = ($capture) ? 'capture' : 'listen';
			unset( $this->listeners[$when][$eventName][$path][$id] );
		}
	}
