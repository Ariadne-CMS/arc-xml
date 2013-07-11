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

		private $options = array('headers' => array());

		public $responseHeaders = null;
		public $requestHeaders = null;

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

		public function request( $type, $url, $request = null, $options = array() ) {
			if ( $type == 'GET' && $request ) {
				$url = $this->buildURL( $url, $request );
				$request = '';
			}

			$options = $this->mergeOptions( array(
				'method' => $type,
				'content' => $request
			), $options );

			if( isset($options['header']) )  {
				$options['header'] .=  "\r\n";
			} else {
				$options['header'] = '';
			}

			$options['header'] .= isset($options['headers']) ? implode( "\r\n", $options['headers'] ) ."\r\n": '' ;
			unset($options['headers']);

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
			return $this->request( 'GET', $url, $request, $options );
		}

		public function post( $url, $request = null, $options = array() ) {
			return $this->request( 'POST', $url, $request, $options );
		}

		public function put( $url, $request = null, $options = array() ) {
			return $this->request( 'PUT', $url, $request, $options );
		}

		public function delete( $url, $request = null, $options = array() ) {
			return $this->request( 'DELETE', $url, $request, $options );
		}

		public function headers( $headers ) {
			if (!isset($this->options['headers'])) {
				$this->options['headers'] = array();
			}
			if( !is_array($headers) ) {
				$this->headers = explode("\r\n",$headers);
			}

			$this->options['headers'] = array_merge($this->options['headers'], $headers);
				
			return $this;
		}

	}
