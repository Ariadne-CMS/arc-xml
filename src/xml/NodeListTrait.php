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
 * This trait is used by the XML Writer to represent a list of child nodes
 */
trait NodeListTrait {

    protected $writer       = null;
    protected $invalidChars = [];

    /**
     * @param array $list
     * @param Writer $writer
     */
    public function __construct( $list = null, $writer = null )
    {
        parent::__construct( $list );
        $this->writer = $writer;
    }

    public function __call( $name, $args )
    {
        $tagName = $name;
        list( $attributes, $content ) = $this->parseArgs( $args );
        parent::offsetSet( null, $this->element( $tagName, $attributes, $content ) );
        return $this;
    }

    public function __toString()
    {
        $indent = '';
        if (!is_object( $this->writer ) || $this->writer->indent ) {
            $indent = "\r\n"; // element() will indent each line with whatever indent string is in the writer
        }
        return join( $indent, (array) $this );
    }

    protected static function indent( $content, $indent="\t", $newLine="\r\n" )
    {
        if ($indent && ( strpos( $content, '<' ) !== false )) {
            $indent = ( is_string( $indent ) ? $indent : "\t" );
            return $newLine . preg_replace( '/^(\s*[^\<]*)</m', $indent.'$1<', $content ) . $newLine;
        } else {
            return $content;
        }
    }

    protected function escape( $contents ) {
        $contents = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $contents);
        return htmlspecialchars( $contents, ENT_XML1, 'UTF-8');
    }

    protected function parseArgs( $args )
    {
        $attributes = array();
        $content    = '';
        foreach ($args as $arg ) {
            if (is_string( $arg ) ) {
                $content .= $this->escape($arg);
            } else if (is_array( $arg )) {
                foreach( $arg as $key => $subArg ) {
                    if (is_numeric( $key )) {
                        list( $subattributes, $subcontent ) = $this->parseArgs( $subArg );
                        $attributes = array_merge( $attributes, $subattributes);
                        $content = \arc\xml::raw( $content . $subcontent );
                    } else {
                        $attributes[ $key ] = $subArg;
                    }
                }
            } else {
                $content .= $arg;
            }
        }
        return [ $attributes, $content ];
    }

    protected function element( $tagName, $attributes, $content )
    {
        $tagName =  \arc\xml::name( $tagName );
        $el      = '<' . $tagName;
        $el     .= $this->getAttributes( $attributes );
        if ($this->hasContent( $content )) {
            $el .=  '>' . self::indent( $content, $this->writer->indent, $this->writer->newLine );
            $el .= '</' . $tagName . '>';
        } else {
            $el .= '/>';
        }
        return $el;
    }

    protected function getAttributes( $attributes )
    {
        $result = '';
        if (count( $attributes )) {
            foreach ($attributes as $name => $value ) {
                $result .= \arc\xml::attribute( $name, $value );
            }
        }
        return $result;
    }

    protected function hasContent( $content )
    {
        return ( trim( $content ) != '' );
    }

}
