<?php

	namespace arc;

	/**
	 *	noxss is an XSS attack detection and prevention class. It contains two methods, detect and prevent. 
	 *  The detect() method must be called at the start of handling any request, e.g. in your front loader / router
	 *  The prevent() method must be called at the end of handling any request.
	 *	
	 *	Usage:
	 *    <?php
	 *        \arc\noxss::detect();
	 *        \\ handle request normally
	 *        try {
	 *            \arc\noxss::prevent();
	 *        } catch ( \arc\IllegalRequestException $e ) {
	 *            header('HTTP/1.1 400 Bad Request');
	 *        }
	 *    ?>
	 */
	class noxss {

		/**
		 * @var (bool) A flag to indicate if there might be an XSS attack going on
		 */
		public static $potentialXSS = false;

		/**
		 * @var (array) A container for inputs potentially containing XSS attacks
		 */
		public static $xss;

		/**
		 * @var (string) buffered output caught by prevent.
		 */
		public static $output;
		
		/**
		 * @var (string) Regular expression that matches any input containing quotes, tag start or end delimiters or &.
		 * I don't know any XSS attack that doesn't require at least one of these characters.
		 */
		public static $reXSS = '/[\'"<>&]/';

		/**
		 * @var (array) A list of _SERVER variables sent by client header and thus potential attack vectors
		 */
		public static $xssHeaders = array('HTTP_ACCEPT','HTTP_ACCEPT_CHARSET','HTTP_ACCEPT_ENCODING','HTTP_ACCEPT_LANGUAGE','HTTP_REFERER','REMOTE_HOST');

		private static function gatherXSSInput( $input ) {
			if ( is_array( $input ) ) {
				foreach ($input as $key => $value) {
					self::gatherXSSInput( $value );
				}
			} else {
				$input = (string) $input;
				if ( ( strlen( $input ) > 10 ) && preg_match( self::$reXSS, $input, $matches)) {
					self::$xss[strlen($input)][$input] = $input;
				}
			}
		}

		/**
		 * This method checks all user inputs ( get/post/cookie variables, client sent headers ) for potential XSS attacks
		 * If found it flags these and sets self::$potentialXSS to true and starts an output buffer
		 */
		public static function detect() {
			foreach (array($_GET, $_POST, $_COOKIE ) as $method) {
				if (is_array( $method )) {
					self::gatherXSSInput( $method );
				}
			}
			foreach ( self::$xssHeaders as $header ) {
				if ( array_key_exists( $header, $_SERVER ) ) {
					self::gatherXSSInput( $_SERVER[$header] );
				}
			}

			if ( !self::$potentialXSS && count( self::$xss ) ) {
				ob_start();
				self::$potentialXSS = true;
			}
		}

		/**
		 * This method checks if self::$potentialXSS to see if an XSS attack might be going on. If so
		 * the output buffer is ended and the output content retrieved. All inputs flagged as potential XSS attacks
		 * are checked to see if any of these is in the output content _in_unchanged_form_ !
		 * If so, there is a vulnerability to XSS which is being exploited ( or at least triggered ) and the only
		 * safe option is to not sent the output but sent a 400 Bad Request header instead.
		 * This method doesn't actually send this header but it does throw an exception allowing you to handle it
		 * any way you see fit.
		 * @throws \arc\ExceptionIllegalRequest
		 */
		public static function prevent() {
			if ( self::$potentialXSS )	 {
				$xssDetected = false;
				self::$output = ob_get_contents();
				ob_end_clean();

				krsort(self::$xss, SORT_NUMERIC);
				foreach (self::$xss as $length => $values) {
					if (is_array($values)) {
						foreach ($values as $value) {
							$occurances = substr_count( self::$output, $value);
							if ($occurances > 0 ) {
								$xssDetected = true;
								break 2;
							}
						}
					}
				}

				if ($xssDetected) {
					throw new ExceptionIllegalRequest('Possible XSS attack detected.', 503 );
				} else {
					echo $image;
				}
			}
		}
	}