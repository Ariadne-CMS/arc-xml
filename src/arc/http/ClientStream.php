<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc\http;

	class ClientStream implements ClientInterface {

		private $options = array();

		public $responseHeaders = null;

		protected function parseRequestURL( $url ) {
			$components = parse_url( $url );
			return isset($components['query']) ? $components['query'] : false;
		}

		protected function mergeOptions( ) {
			$args = func_get_args();
			array_unshift( $args, $this->options );
			return call_user_func_array( 'array_merge', $args );
		}

		protected function buildURL( $url, $request ) {
			if ( is_array( $request ) || $request instanceof \ArrayObject ) {
				$request = http_build_query( (array) $request );
			}
			$request = (string) $request; // to force a \ar\connect\url\urlQuery to a possibly empty string.
			if ( $request ) {
				if ( strpos( (string) $url, '?' ) === false ) {
					$request = '?' . $request;
				} else {
					$request = '&' . $request;
				}
				$url .= $request;
			}
			return $url;
		}

		public function send( $type, $url, $request = null, $options = array() ) {
			if ( $type == 'GET' && $request ) {
				$url = $this->buildURL( $url, $request );
				$request = '';
			}
			$options = $this->mergeOptions( array(
				'method' => $type,
				'content' => $request
			), $options );
			$context = stream_context_create( array( 'http' => $options ) );
			$result = @file_get_contents( (string) $url, false, $context );
			$this->responseHeaders = $http_response_header; //magic php variable set by file_get_contents.
			$this->requestHeaders = isset($options['header']) ? $options['header'] : '';
			return $result;
		}

		public function __construct( $options = array() ) {
			$this->options = $options;
		}

		public function get( $url, $request = null, $options = array() ) {

			if ( !isset($request) ) {
				$request = $this->parseRequestURL($url);
			}
			return $this->send( 'GET', $url, $request, $options );
		}

		public function post( $url, $request = null, $options = array() ) {
			return $this->send( 'POST', $url, $request, $options );
		}

		public function put( $url, $request = null, $options = array() ) {
			return $this->send( 'PUT', $url, $request, $options );
		}

		public function delete( $url, $request = null, $options = array() ) {
			return $this->send( 'DELETE', $url, $request, $options );
		}

		public function headers( $headers ) {
			if (is_array($headers)) {
				$headers = join("\r\n", $headers);
			}
			if (!isset($this->options['header'])) {
				$this->options['header'] = '';
			}
			$this->options['header'] = $this->options['header'].$headers;
			return $this;
		}

	}
?>
