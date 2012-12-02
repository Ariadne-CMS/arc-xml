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

	interface EventsTreeInterface {

		public function listen( $eventName );

		public function capture( $eventName );

		public function fire( $eventName, $eventData = array() );

		public function cd( $path );

	}