<?php

namespace arc;

class template {

	/**
	* This method replaces {{key}} entries in a string with the value of that key in an arguments array
	* If the key isn't in the arguments array, it will remain in the returned string as-is.
	* <code>
	*   $parsedTemplate = \arc\template::parse( 'Hello {{world}}', [ 'world' => 'World!' ] );
	*</code>
	* @param string $template
	* @param array $arguments
	*/
	public static function parse( $template, $arguments ) {
		if ( is_object($arguments) && !($arguments instanceof \ArrayObject ) ) {
			$arguments = get_object_vars( $arguments );
		}
		$regex = '\{\{(' . join( array_keys( (array) $arguments ), '|' ) . ')\}\}';
		return preg_replace_callback( '/'.$regex.'/g', function( $matches ) use ( $arguments ) {
			$argument = $arguments[ $matches[1] ];
			if ( is_callable( $argument ) ) {
				$argument = call_user_func( $argument );
			}
			return $argument;
		}, $template );
	}
	
	public static function clean( $template ) {
		return preg_replace('/\{\{.*\}\}/g', '', $template );
	}
}