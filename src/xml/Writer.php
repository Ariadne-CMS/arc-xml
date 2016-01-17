<?php

namespace arc\xml;

class Writer {

    public $indent = false;

    public function __construct( $options = [])
    {
        $optionList = [ 'indent' ];
        foreach( $options as $option => $optionValue) {
            if (in_array( $option, $optionList )) {
                $this->{$option} = $optionValue;
            }
        }
    }

    public function __call( $name, $args)
    {	
        return call_user_func_array( [ new NodeList( [], $this ), $name ], $args );
    }

    public static function name( $name)
    {
        return preg_replace( '/^[^:a-z_]*/isU', '',
            preg_replace( '/[^-.0-9:a-z_]/isU', '', $name
        ) );
    }

    public static function value( $value)
    {
        if (is_array( $value )) {
            $content = array_reduce( $value, function( $result, $value)
            {
                return $result . ' ' . self::value( $value );
            } );
        } else if (is_bool( $value )) {
            $content = $value ? 'true' : 'false';
        } else {
            $value = (string) $value;
            if (preg_match( '/^\s*<!\[CDATA\[/', $value )) {
                $content = $value;
            } else {
                $content = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
            }
        }
        return $content;
    }

    public static function attribute( $name, $value)
    {
        if ($name === $value) {
            return ' ' . self::name( $name );
        } else if (is_numeric( $name )) {
            return ' ' . self::name( $value );
        } else {
            return ' ' . self::name( $name ) . '="' . self::value( $value ) . '"';
        }
    }

    public static function comment( $content)
    {
        return '<!-- ' . self::value( $content ) . ' -->';
    }

    public static function cdata( $content)
    {
        return '<![CDATA[' . str_replace( ']]>', ']]&gt;', $content ) . ']]>';
    }

    public static function preamble( $version = '1.0', $encoding = null, $standalone = null)
    {
        if (isset($standalone)) {
            if ($standalone === 'false') {
                $standalone = 'no';
            } else if ($standalone !== 'no') {
                $standalone = ( $standalone ? 'yes' : 'no' );
            }
            $standalone = self::attribute( 'standalone', $standalone );
        } else {
            $standalone = '';
        }
        $preamble = '<?xml version="' . self::value($version) . '"';
        if (isset( $encoding )) {
            $preamble .= ' " encoding="' . self::value($encoding) . '"';
        }
        $preamble .= $standalone . ' ?>';
        return $preamble;
    }
}
