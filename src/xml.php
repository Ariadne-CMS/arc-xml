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
 * This class contains the parse and css2XPath methods.
 * In addition any other method statically called on this class
 * will reroute the call to the XML writer instance at 
 * \arc\xml::$writer. It is automatically instantiated if needed.
 * Or you can set it yourself to another Writer instance.
 */
class xml
{
    static $writer = null;

    public static function __callStatic( $name, $args )
    {
        if ( !isset(self::$writer) ) {
            self::$writer = new xml\Writer();
        }
        return call_user_func_array( [ self::$writer, $name ], $args );
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
            // E:checked or E:disabled or E:selected
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
            => '\1[ @id = "\2" ]',
            // #myid: Matches any E element with id-attribute equal to "myid"
            '/\#([\w\-]+)/'
            => '*[ @id = "\1" ]'
        );

        $cssSelectors = array_keys($translateList);
        $xPathQueries = array_values($translateList);
        do {
            $continue = false;
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
}
