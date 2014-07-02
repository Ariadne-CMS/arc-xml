<?php

namespace arc\xml;

class NodeList extends \ArrayObject {

    protected $writer = null;

    public function __construct( $list = null, $writer = null ) {
        parent::__construct( $list );
        $this->writer = $writer;
    }

    public function __call( $name, $args ) {
        $tagName = $name;
        list( $attributes, $content ) = $this->parseArgs( $args );
        parent::offsetSet( null, $this->element( $tagName, $attributes, $content ) );
        return $this;
    }

    public function __toString() {
        $indent = '';
        if (!is_object( $this->writer ) && $this->writer->indent ) {
            $indent = "\r\n"; // element() will indent each line with whatever indent string is in the writer
        }
        return join( $indent, (array) $this );
    }

    static public function indent( $indent, $content ) {
        if ($indent && ( strpos( $content, '<' ) !== false )) {
            $indent = ( is_string( $indent ) ? $indent : "\t" );
            return "\r\n" . preg_replace( '/^(\s*)</m', $indent.'$1<', $content ) . "\r\n";
        } else {
            return $content;
        }
    }

    private function parseArgs( $args ) {
        $attributes = array();
        $content = '';
        foreach ($args as $arg ) {
            if (is_string( $arg )) {
                $content .= $arg;
            } else if (is_array( $arg )) {
                foreach( $arg as $key => $subArg ) {
                    if (is_numeric( $key )) {
                        list( $subattributes, $subcontent ) = $this->parseArgs( $subArg );
                        $attributes = $subattributes + $attributes;
                        $content .= $subcontent;
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

    protected function element( $tagName, $attributes, $content ) {
        $tagName =  $this->writer->name( $tagName );
        $el = '<' . $tagName;
        $el .= $this->getAttributes( $attributes );
        if ($this->hasContent( $content )) {
            $el .=  '>' . self::indent( $this->writer->indent, $content );
            $el .= '</' . $tagName . '>';
        } else {
            $el .= '/>';
        }
        return $el;
    }

    protected function getAttributes( $attributes ) {
        $result = '';
        if (count( $attributes )) {
            foreach ($attributes as $name => $value ) {
                $result .= ' ' . $this->writer->name( $name );
                $value = $this->writer->value( $value );
                if ($value !== $name ) {
                    $result .= '="' . $value . '"';
                }
            }
        }
        return $result;
    }

    protected function hasContent( $content ) {
        return ( trim( $content ) != '' );
    }
}
