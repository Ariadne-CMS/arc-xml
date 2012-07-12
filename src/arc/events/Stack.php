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

	class Stack {

		protected $listeners = array();
		protected $event = null;
		protected $contextStack = null;

		public function __construct( $contextStack = null ) {
			$this->contextStack = $contextStack;
		}

		public function listen( $eventName, $objectType = null, $capture = false ) {
			if ( isset( $this->contextStack ) ) {
				$path = $this->contextStack->getPath();
			} else {
				$path = '/';
			}
			return new IncompleteListener( $path, $eventName, $objectType, $capture, $this );
		}

		public function capture( $eventName, $objectType = null ) {
			return $this->listen( $eventName, $objectType, true );
		}

		public function fire( $eventName, $eventData = array(), $objectType = null, $path = '/' ) {
			if ( !$this->listeners['capture'][$eventName]
				&& !$this->listeners['listen'][$eventName] ) {
				return $eventData; // no listeners for this event, so dont bother searching
			}
			$prevEvent = null;
			if ( $this->event ) {
				$prevEvent = $this->event;
			}
			if ( isset( $this->contextStack ) ) {
				$path = $this->contextStack->getPath( array( 'path' => $path ) );
			}
			/*
			FIXME: make this generic
			if ( !isset($objectType) ) {
				$objectType = ar\context::getObjectType( array( 'path' => $path ) );
			} else if ( !$objectType ) { // when set to false to prevent automatic filling of the objectType, reset it to null
				$objectType = null;
			}
			*/
			$this->event = new Event( $eventName, $eventData );
			if ( $this->walkListeners( $this->listeners['capture'][$eventName], $path, $objectType, true ) ) {
				$this->walkListeners( $this->listeners['listen'][$eventName], $path, $objectType, false );
			}

			if ( $this->event->preventDefault ) {
				$result = false;
			} else if ( $this->event->data ) {
				$result = $this->event->data;
			} else {
				$result = true;
			}
			$this->event = $prevEvent;
			return $result;
		}

		protected function walkListeners( $listeners, $path, $objectType, $capture ) {
			$objectTypeStripped = $objectType;
			$pos = strpos('.', $objectType);
			if ( $pos !== false ) {
				$objectTypeStripped = substr($objectType, 0, $pos);
			}
			$pathlist = \arc\path::parents( $path );
			$counter = count( $pathlist );
			reset($pathlist);

			do {
				$currentPath = current( $pathlist );
				if ( is_array( $listeners[$currentPath] ) ) {
					foreach ( $listeners[$currentPath] as $listener ) {
						if ( !isset($listener['type']) ||
							 ( $listener['type'] == $objectType ) ||
							 ( $listener['type'] == $objectTypeStripped ) ||
							 ( is_a( $objectType, $listener['type'] ) ) )
						{
							$result = call_user_func_array( $listener['method'], $listener['args'] );
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
			return new IncompleteListener( $path, null, null, false, $this );
		}

		public function addListener( $path, $eventName, $objectType, $method, $args, $capture = false ) {
			if ( !$path ) {
				$path = '/';
			}
			$when = ($capture) ? 'capture' : 'listen';
			$this->listeners[$when][$eventName][$path][] = array(
				'type' => $objectType,
				'method' => $method,
				'args' => $args
			);
			return new Listener( $eventName, $path, $capture, count($this->listeners[$when][$eventName][$path])-1, $this );
		}

		public function removeListener( $name, $path, $capture, $id ) {
			$when = ($listener['capture']) ? 'capture' : 'listen';
			unset( $this->listeners[$when][$name][$path][$id] );
		}
	}

