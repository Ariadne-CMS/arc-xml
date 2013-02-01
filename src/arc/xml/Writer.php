<?php

namespace arc\xml;

class Writer {
	
	public $indent = false;

	public function __construct( $options = array() ) {
		$optionList = array( 'indent' );
		foreach( $options as $option => $optionValue ) {
			if ( in_array( $option, $optionList ) ) {
				$this->{$option} = $optionValue;
			}
		}
	}

	public function __call( $name, $args ) {
		return call_user_func_array( [ new NodeList( array(), $this ), $name ], $args );
	}

	static public function name( $name ) {
		return preg_replace( '/^[^:a-z_]*/isU', '', 
			preg_replace( '/[^-.0-9:a-z_]/isU', '', $name
		) );
	}

	static public function value( $value ) {
		if ( is_array( $value ) ) {
			$content = array_reduce( $value, function( $result, $value ) {
				return $result . ' ' . self::value( $value );
			} );
		} else if ( is_bool( $value ) ) {
			$content = $value ? 'true' : 'false';
		} else {
			$value = (string) $value;
			if ( preg_match( '/^\s*<!\[CDATA\[/', $value ) ) {
				$content = $value;
			} else {
				$content = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
			}
		}
		return $content;
	}

	static public function attribute( $name, $value ) {
		if ( $name === $value ) {
			return ' ' . self::name( $name );
		} else if ( is_numeric( $name ) ) {
			return ' ' . self::name( $value );
		} else {
			return ' ' . self::name( $name ) . '="' . self::value( $value ) . '"';
		}
	}

	static public function comment( $content ) {
		return '<!-- ' . self::value( $comment ) . ' -->';
	}

	static public function cdata( $content ) {
		return '<![CDATA[' . str_replace( ']]>', ']]&gt;', $content ) . ']]>';
	}

	static public function preamble( $encoding = false, $version = '1.0', $standalone = false ) {
		if ( isset($standalone) ) {
			if ( $standalone === 'false' ) {
				$standalone = 'no';
			} else if ( $standalone !== 'no' ) {
				$standalone = ( $standalone ? 'yes' : 'no' );
			}
			$standalone = self::attribute( 'standalone', $standalone );
		} else {
			$standalone = '';
		}
		return '<?xml version="' . self::value($version) 
			. '" encoding="' . self::value($encoding) . '"' . $standalone . ' ?>';
	}

}