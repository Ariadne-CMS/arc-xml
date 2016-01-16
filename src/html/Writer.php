<?php

namespace arc\html;

class Writer {

	public $indent = false;

	public function __construct( $options = [] ) 
	{
		$optionList = ['indent'];
		foreach( $options as $option => $optionValue ) {
			if ( in_array( $option, $optionList ) ) {
				$this->{$option} = $optionValue;
			}
		}
	}

	public function __call( $name, $args ) 
	{
		return call_user_func_array( [ new \arc\html\NodeList( [], $this), $name], $args );
	}

	static public function name( $name ) 
	{
		return strtolower( \arc\xml::name( $name ) );
	}

	static public function value( $value ) 
	{
		if ( is_array( $value ) ) {
			$content = array_reduce( $value, function( $result, $value ) {
				return $result . ' ' . self::value( $value );
			} );
		} else if ( is_bool( $value ) ) {
			$content = $value ? 'true' : 'false';
		} else {
			$content = htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
		}
		return $content;
	}

	static public function attribute( $name, $value )
	{
		return \arc\xml::attribute( $name, $value );
	}

	static public function comment( $content )
	{
		return \arc\xml::comment( $content );
	}

	static public function doctype( $version='html5' )
	{
		$doctypes = [
			'html5'        => '<!doctype html>',
			'html4'        => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
			'transitional' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
			'frameset'     => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
			'xhtml'        => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
		];
		return isset( $doctypes[$version] ) ? $doctypes[$version] : $doctypes['html5'];
	}
}

