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

	interface StackInterface {

		public function listen( $eventName, $objectType = null, $capture = false );

		public function capture( $eventName, $objectType = null );

		public function fire( $eventName, $eventData = array(), $path = null, $objectType = null );

		public function cd( $path );

	}