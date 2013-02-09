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
	*	This class implements an event stack on which listeners can be added and removed and events can be fired.
	*/
	class EventsTree implements EventsTreeInterface {
		use \arc\traits\Proxy {
			\arc\traits\Proxy::__construct as private ProxyConstruct;
		}	

		private $tree = null;

		/**
		*	@param \arc\tree\NamedNode $tree The tree storage for event listeners.
		*/
		public function __construct( $tree ) {
			$this->ProxyConstruct( $tree );
			$this->tree = $tree;
		}

		/**
		*	Adds an event listener for the given event and returns it.
		*	@param string $eventName The event to listen for
		*   @param callable $callback The function to call when the event occurs.
		*   @return Listener
		*/
		public function listen( $eventName, $callback ) {
			return $this->addListener( $eventName, $callback, false );
		}

		/**
		*	Adds an event listener for the given event and returns it. The listener
		*	will trigger in the capture phase - before any listeners in the listen phase.
		*	@param string $eventName The name of the event to listen for.
		*   @param callable $callback The function to call when the event occurs.
		*	@return Listener 
		*/
		public function capture( $eventName, $callback ) {
			return $this->addListener( $eventName, $callback, true );
		}

		/**
		*	Fires an event. If the event objects preventDefault() method has been called it
		*	will return false, otherwise the - potentially changed - eventData will be returned.
		*	@param string $eventName The name of the event to fire.
		*	@param array $eventData Optional. Data passed to each handler through the event object.
		*	@return false or $eventData - which may have been modified
		*/
		public function fire( $eventName, $eventData = array() ) {
			// FIXME: because this now uses the tree, we can't quickly check if any event listeners have been added for this eventName
			// so there should probably be a common list of all handled eventNames in the entire tree as a performance improvement
			$eventData['arc.path'] = $this->tree->getPath();
			$event = new Event( $eventName, $eventData );
			$this->walkListeners( $event );
			if ( $event->preventDefault ) {
				$result = false;
			} else {
				$result = $event->data;
			}
			return $result;
		}

		/**
		*	Returns a new EventStack with the given path.
		*	@param string $path The path to listen or fire an event.
		*	@return EventStack a new EventStack for the given path.
		*/
		public function cd( $path ) {
			return new EventsTree( $this->tree->cd( $path ) );
		}

		/**
		*	Non-fluent api method to add a listener.
		*	@param string $eventName The name of the event to listen for.
		*	@param Callable $callback The callback method to call.
		*	@param bool $capture Optional. If true listen in the capture phase. Default is false - listen phase.
		*	@return Listener
		*/
		private function addListener( $eventName, $callback, $capture = false ) {
			$listenerSection = ( $capture ? 'capture' : 'listen' ) . '.' . $eventName;
			if ( ! is_callable($callback) ) {
				throw new \arc\ExceptionIlligalRequest('Method is not callable.', \arc\exceptions::ILLEGAL_ARGUMENT);
			}
			if ( !isset( $this->tree->nodeValue[ $listenerSection ]) ) {
				$this->tree->nodeValue[ $listenerSection ] = array();
			}
			$this->tree->nodeValue[ $listenerSection ][] = array(
				'method' => $callback
			);
			$id = max( array_keys( $this->tree->nodeValue[ $listenerSection ] ) );
			return new Listener( $eventName, $id, $capture, $this );
		}

		/**
		*	Non-fluent api method to remove a listener.
		*	@param string $eventName The name of the event the listener is registered for.
		*	@param int $id The id of the listener.
		*	@param string $path Optional. The path the listener is registered on. Default is '/'.
		*	@param bool $capture Optional. If true the listener is triggered in the capture phase. 
		*		Default is false.
		*/
		public function removeListener( $eventName, $id, $capture = false ) {
			$listenerSection = ( $capture ? 'capture' : 'listen' ) . '.' . $eventName;
			unset( $this->tree->nodeValue[ $listenerSection ][ $id ] );
		}

		/**
		*	Calls each listener with the given event untill a listener returns false.
		*/
		private function walkListeners( $event ) {
			$callListeners = function( $listeners, $result = null ) use ( $event ) {
				foreach ( (array) $listeners as $listener ) {
					$result = call_user_func( $listener['method'], $event );
					if ( $result === false ) {
						return false; // this will stop \arc\path::walk, so other event handlers won't be called
					}
				}					
			};
			$result = \arc\tree::parents( 
				$this->tree,
				function( $node, $result ) use ( $callListeners, $event ) {
					if ( $result !== false && isset( $node->nodeValue['capture.'.$event->name] ) ) {
						return call_user_func($callListeners, $node->nodeValue['capture.'.$event->name] );
					}
				}
			);
			if ( !isset( $result ) ) {
				$result = \arc\tree::dive(
					$this->tree,
					function( $node ) use ( $callListeners, $event ) {
						if ( isset( $node->nodeValue['listen.'.$event->name] ) ) {
							return call_user_func($callListeners, $node->nodeValue['listen.'.$event->name ] );
						}
					}
				);
			}
			return !isset( $result ) ? true : false;
		}
	}
