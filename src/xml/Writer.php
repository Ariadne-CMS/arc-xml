<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc\xml;

/**
 * This class allows you to create valid and nicely indented XML strings
 * Any method not explicitly defined is interpreted as a new XML element to create.
 */
class Writer {

    /**
     * @var string $indent The string to ident each level with. Default is a tab.
     */
    public $indent = "\t";

    /**
     * @var string $newLine The string to use as a new line or linebreak. Defaults to \r\n.
     */
    public $newLine = "\r\n";

    /**
     * @param array $options allows you to set the indent and newLine
     * options immediately upon construction
     */
    public function __construct( $options = [])
    {
        $optionList = [ 'indent', 'newLine' ];
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

    /**
     * Returns a guaranteed valid XML name. Removes illegal characters from the name.
     * @param string $name
     * @return string
     */
    public static function name( $name)
    {
        return preg_replace( '/^[^:a-z_]*/isU', '',
            preg_replace( '/[^-.0-9:a-z_]/isU', '', $name
        ) );
    }

    /**
     * Returns a guaranteed valid XML attribute value. Removes illegal characters.
     * @param string|array|bool $value
     * @return string
     */
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

    /**
     * Returns a guaranteed valid XML attribute. Removes illegal characters.
     * @param string $name
     * @param string|array|bool $value
     * @return string
     */
    public static function attribute( $name, $value)
    {
        return ' ' . self::name( $name ) . '="' . self::value( $value ) . '"';
    }

    /**
     * Returns a guaranteed valid XML comment. Removes illegal characters.
     * @param string $content
     * @return string
     */
    public static function comment( $content)
    {
        return '<!-- ' . self::value( $content ) . ' -->';
    }

    /**
     * Returns a guaranteed valid XML CDATA string. Removes illegal characters.
     * @param string $content
     * @return string
     */
    public static function cdata( $content)
    {
        return '<![CDATA[' . str_replace( ']]>', ']]&gt;', $content ) . ']]>';
    }

    /**
     * Returns an XML preamble.
     * @param string $version Defaults to '1.0'
     * @param string $encoding Defaults to null
     * @param string $standalone Defaults to null
     * @return string
     */
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
