<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc;

/**
 * Any method statically called on this class
 * will reroute the call to the XML writer instance at 
 * \arc\xml::$writer. Except for the methods:
 * parse, css2XPath, name, value, attribute, comment, cdata and preamble
 * If you need those call the Writer instance directly
 */
class xml
{
    /**
     * @var xml\Writer The writer instance to use by default
     */
    public static $writer = null;

    public static function __callStatic( $name, $args )
    {
        if ( !isset(static::$writer) ) {
            static::$writer = new xml\Writer();
        }
        return call_user_func_array( [ static::$writer, $name ], $args );
    }

    /**
     * This parses an XML string and returns a Proxy
     * @param string|Proxy $xml
     * @return Proxy
     * @throws \arc\Exception
     */
    public static function parse( $xml=null, $encoding = null )
    {
        $parser = new xml\Parser();
        return $parser->parse( $xml, $encoding );
    }

    /**
     * This method turns a single CSS 2 selector into an XPath query
     * @param string $cssSelector
     * @return string
     */
    public static function css2XPath( $cssSelector )
    {
        /* based on work by Tijs Verkoyen - http://blog.verkoyen.eu/blog/p/detail/css-selector-to-xpath-query/ */
        $translateList = array(
            // E F: Matches any F element that is a descendant of an E element
            '/(\w+)\s+(?=([^"]*"[^"]*")*[^"]*$)(\w+)/'
            => '\1//\3',
            // E > F: Matches any F element that is a child of an element E
            '/(\w+)\s*>\s*(\w+)/'
            => '\1/\2',
            // E:first-child: Matches element E when E is the first child of its parent
            '/(\w+|\*):first-child/'
            => '*[1]/self::\1',
            // Matches E:checked, E:disabled or E:selected (and just for scrutinizer: this is not code!)
            '/(\w+|\*):(checked|disabled|selected)/'
            => '\1 [ @\2 ]',
            // E + F: Matches any F element immediately preceded by an element
            '/(\w+)\s*\+\s*(\w+)/'
            => '\1/following-sibling::*[1]/self::\2',
            // E ~ F: Matches any F element preceded by an element
            '/(\w+)\s*\~\s*(\w+)/'
            => '\1/following-sibling::*/self::\2',
            // E[foo]: Matches any E element with the "foo" attribute set (whatever the value)
            '/(\w+)\[([\w\-]+)]/'
            => '\1 [ @\2 ]',
            // E[foo="warning"]: Matches any E element whose "foo" attribute value is exactly equal to "warning"
            '/(\w+)\[([\w\-]+)\=\"(.*)\"]/'
            => '\1[ contains( concat( " ", normalize-space(@\2), " " ), concat( " ", "\3", " " ) ) ]',
            // .warning: HTML only. The same as *[class~="warning"]
            '/(^|\s)\.([\w\-]+)+/'
            => '*[ contains( concat( " ", normalize-space(@class), " " ), concat( " ", "\2", " " ) ) ]',
            // div.warning: HTML only. The same as DIV[class~="warning"]
            '/(\w+|\*)\.([\w\-]+)+/'
            => '\1[ contains( concat( " ", normalize-space(@class), " " ), concat( " ", "\2", " " ) ) ]',
            // E#myid: Matches any E element with id-attribute equal to "myid"
            '/(\w+)+\#([\w\-]+)/'
            => "\\1[@id='\\2']",
            // #myid: Matches any E element with id-attribute equal to "myid"
            '/\#([\w\-]+)/'
            => "*[@id='\\1']"
        );

        $cssSelectors = array_keys($translateList);
        $xPathQueries = array_values($translateList);
        do {
            $continue    = false;
            $cssSelector = (string) preg_replace($cssSelectors, $xPathQueries, $cssSelector);
            foreach ( $cssSelectors as $selector ) {
                if ( preg_match($selector, $cssSelector) ) {
                    $continue = true;
                    break;
                }
            }
        } while ( $continue );
        return '//'.$cssSelector;
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
                return $result . ' ' . static::value( $value );
            } );
        } else if (is_bool( $value )) {
            $content = $value ? 'true' : 'false';
        } else {
            $value = (string) $value;
            if (preg_match( '/^\s*<!\[CDATA\[/', $value )) {
                $content = $value;
            } else {
                $content = htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
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
        return ' ' . static::name( $name ) . '="' . static::value( $value ) . '"';
    }

    /**
     * Returns a guaranteed valid XML comment. Removes illegal characters.
     * @param string $content
     * @return string
     */
    public static function comment( $content)
    {
        return static::raw('<!-- ' . static::value( $content ) . ' -->');
    }

    /**
     * Returns a guaranteed valid XML CDATA string. Removes illegal characters.
     * @param string $content
     * @return string
     */
    public static function cdata( $content)
    {
        return static::raw('<![CDATA[' . str_replace( ']]>', ']]&gt;', $content ) . ']]>');
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
            $standalone = static::attribute( 'standalone', $standalone );
        } else {
            $standalone = '';
        }
        $preamble = '<?xml version="' . static::value($version) . '"';
        if (isset( $encoding )) {
            $preamble .= ' " encoding="' . static::value($encoding) . '"';
        }
        $preamble .= $standalone . ' ?>';
        return $preamble;
    }

    public static function raw( $contents='' ) {
        return new xml\RawXML($contents);
    }

}