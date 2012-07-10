<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc;

	class url {

		public static function url( $url ) {
			return new url\ParsedUrl( $url );
		}

		public static function safeUrl( $url ) {
			return new url\SafeUrl( $url );
		}

	}

?>