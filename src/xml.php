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

class xml
{

    public static function __callStatic( $name, $args )
    {
        return call_user_func_array( [ new xml\Writer(), $name ], $args );
    }

    public static function parse( $xml, $encoding = null )
    {
        $parser = new xml\Parser();
        return $parser->parse( $xml, $encoding );
    }

    public static function css2XPath( $cssSelector )
    {
        /* (c) Tijs Verkoyen - http://blog.verkoyen.eu/blog/p/detail/css-selector-to-xpath-query/ */
        $cssSelectors = array(
            // E F: Matches any F element that is a descendant of an E element
            '/(\w+)\s+(?=([^"]*"[^"]*")*[^"]*$)(\w+)/',
            // E > F: Matches any F element that is a child of an element E
            '/(\w+)\s*>\s*(\w+)/',
            // E:first-child: Matches element E when E is the first child of its parent
            '/(\w+):first-child/',
            // E + F: Matches any F element immediately preceded by an element
            '/(\w+)\s*\+\s*(\w+)/',
            // E ~ F: Matches any F element preceded by an element
            '/(\w+)\s*\~\s*(\w+)/',
            // E[foo]: Matches any E element with the "foo" attribute set (whatever the value)
            '/(\w+)\[([\w\-]+)]/',
            // E[foo="warning"]: Matches any E element whose "foo" attribute value is exactly equal to "warning"
            '/(\w+)\[([\w\-]+)\=\"(.*)\"]/',
            // .warning: HTML only. The same as *[class~="warning"]
            '/(^|\s)\.([\w\-]+)+/',
            // div.warning: HTML only. The same as DIV[class~="warning"]
            '/(\w+|\*)\.([\w\-]+)+/',
            // E#myid: Matches any E element with id-attribute equal to "myid"
            '/(\w+)+\#([\w\-]+)/',
            // #myid: Matches any E element with id-attribute equal to "myid"
            '/\#([\w\-]+)/'
        );

        $xPathQueries = array(
            '\1//\3',
            '\1/\2',
            '*[1]/self::\1',
            '\1/following-sibling::*[1]/self::\2',
            '\1/following-sibling::*/self::\2',
            '\1 [ @\2 ]',
            '\1[ contains( concat( " ", normalize-space(@\2), " " ), concat( " ", "\3", " " ) ) ]',
            '*[ contains( concat( " ", normalize-space(@class), " " ), concat( " ", "\2", " " ) ) ]',
            '\1[ contains( concat( " ", normalize-space(@class), " " ), concat( " ", "\2", " " ) ) ]',
            '\1[ @id = "\2" ]',
            '*[ @id = "\1" ]'
        );

        $continue = true;
        while ( $continue ) {
            $cssSelector = (string) preg_replace($cssSelectors, $xPathQueries, $cssSelector);
            $continue = false;
            foreach ( $cssSelectors as $selector ) {
                if ( preg_match($selector, $cssSelector) ) {
                    $continue = true;
                    break;
                }       
            }
        }
        return '//'.$cssSelector;
    }
}
