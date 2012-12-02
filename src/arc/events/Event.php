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
	*	The event object that is passed to each listener. May contain extra information in the 'data' property.
	*/
	final class Event {
		/**
		*	May contain extra information for the event listeners, may be any type.
		*/
		public $data = null;

		/**
		*	The name of the event. Can be accessed through __get() but not changed.
		*/
		private $name = '';

		/**
		*	If set to true will make fire() return false. Cannot be changed once set to true 
		*	but can be read through __get().
		*/
		private $preventDefault = false;

		/**
		*	@param string $name The name of the event fired.
		*	@param mixed $data Optional. Extra information for this event.
		*/
		public function __construct( $name, $data = null ) {
			$this->name = $name;
			$this->data = $data;
		}

		/**
		*	Sets the flag which will make \arc\events::fire() return false. Once set it cannot be unset.
		*	@returns false
		*/
		public function preventDefault() {
			$this->preventDefault = true;
			return false;
		}

		public function __get( $name ) {
			switch( $name ) {
				case 'preventDefault':
					return $this->preventDefault;
				break;
				case 'name':
					return $this->name;
				break;
			}
		}

	}
