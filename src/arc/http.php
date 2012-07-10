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

	class http extends \arc\Pluggable {

		public static $tainting = true;

		public static function getvar( $name = null, $method = null) {
			$result = null;
			switch($method) {
				case 'GET' :
				case 'POST' :
				case 'COOKIE' :
				case 'SERVER' :
					$result = isset($name) ? ${'_'.$method}[$name] : ${'_'.$method};
				break;
				default :
					$result = !isset($name) ? $_REQUEST :
						( isset($_POST[$name]) ? $_POST[$name] : $_GET[$name] );
				break;
			}
			if ( self::$tainting && class_exists( '\arc\tainting' ) ) {
				$result = \arc\tainting::taint( $result );
			}
			return $result;
		}

		public static function request( $method = null, $url = null, $postdata = null, $options = array() ) {
			$client = new http\ClientStream();
			return $client->send( $method, $url, $postdata, $options );
		}

		public static function client( $options = array() ) {
			return new http\ClientStream( $options );
		}

		public static function configure( $option, $value ) {
			switch ( $option ) {
				case 'tainting' :
					self::$tainting = $value;
				break;
			}
		}

		public static function get( $url, $request = null, $options = array() ) {
			return self::request( 'GET', $url, $request, $options);
		}

		public static function post( $url, $request = null, $options = array() ) {
			return self::request( 'POST', $url, $request, $options);
		}

	}

?>