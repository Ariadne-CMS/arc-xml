<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\tainting;

	class Tainted {
		public $value = null;

		public function __construct($value) {
			$this->value = $value;
		}

		public function __toString() {
			return filter_var($this->value, FILTER_SANITIZE_SPECIAL_CHARS);
		}
	}
?>