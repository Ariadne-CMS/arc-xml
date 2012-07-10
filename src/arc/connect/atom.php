<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\connect;

	class atom {

		public static function client( $url = null, $httpClient = null ) {
			if ( !isset($httpClient) ) {
				$httpClient = \arc\http::client();
			}
			return new atom\Client( $url, $httpClient );
		}

		public static function parse( $xml ) {
			$client = new atom\Client();
			return $client->parse( $xml );
		}

	}


