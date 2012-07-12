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

	class Listener {
		private $capture    = false;
		private $path       = '/';
		private $eventName  = '';
		private $id         = null;
		private $eventStack = null;

		public function __construct( $eventName, $id, $path = '/', $capture = false, $eventStack = null ) {
			$this->eventName  = $eventName;
			$this->path       = $path;
			$this->capture    = $capture;
			$this->id         = $id;
			$this->eventStack = $eventStack;
		}

		public function remove() {
			if ( isset($this->id) ) {
				$this->eventStack->removeListener( $this->eventName, $this->path, $this->capture, $this->id );
			}
		}

		/* FIXME: add a add() method, which re-adds the listener, potentially as last in the list */
	}
