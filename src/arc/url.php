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

	class url extends Pluggable {

		/**
		 *	Returns a new URL object with easy access to the components (scheme, host, port, path, etc) and
		 *	the query parameters in the url. It parses these according to PHP's own rules. If the URL is
		 *	incompatible with PHP, use \arc\url\safeUrl() instead.
		 *	@param string $url
		 *	@return \arc\url\Url The parsed url object
		*/
		public static function url( $url ) {
			return new url\Url( $url, new url\PHPQuery() );
		}

		/**
		 *	Returns a new URL object with easy access to the components (scheme, host, port, path, etc)
		 *	It will not parse the query string for you.
		 *	@param string $url
		 *	@return \arc\url\Url The url object
		*/
		public static function safeUrl( $url ) {
			return new url\Url( $url, new url\Query() );
		}

	}
