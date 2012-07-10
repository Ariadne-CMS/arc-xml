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

	class Event {
		public $data = null;
		private $name = '';
		private $preventDefault = false;

		public function __construct( $name, $data = null ) {
			$this->name = $name;
			$this->data = $data;
		}

		public function preventDefault() {
			$this->preventDefault = true;
			return false;
		}

		public function __get( $name ) {
			if ( $name == 'preventDefault' ) {
				return $this->preventDefault;
			}
			if ( $name == 'name' ) {
				return $this->name;
			}
		}

	}
?>