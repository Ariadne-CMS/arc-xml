<?php
/*
    TODO: implement simplexml access methods to the proxy
    probably needs to extend ArrayObject in some way to do this properly
 */
namespace arc\xml;

class Proxy extends \ArrayObject {

    use \arc\traits\Proxy {
        \arc\traits\Proxy::__construct as private ProxyConstruct;
        \arc\traits\Proxy::__call as private ProxyCall;
    }

    private $parser = null;

    public function __construct( $node, $parser) {
        $this->ProxyConstruct( $node );
        $this->parser = $parser;
    }

    public function __toString() {
        return $this->target->asXML();
    }

    public function __get( $name) {
        if ($name == 'nodeValue') {
            return $this->target.'';
        }
        $value = $this->target->{$name};
        if (is_object( $value )) {
            return new static( $value, $this->parser );
        } else {
            return $value;
        }
    }
    
    public function __call( $name, $args ) {
        if ( !method_exists($this->target, $name) ) {
            $dom = dom_import_simplexml($this->target);
            $result = call_user_func_array( [ $dom, $name], $args );
            if ( isset($result) && is_object($result) ) {
                if ( $result instanceof \DOMNode ) {
                    return new static( $result, $this->parser );
                }
                if ( $result instanceof \DOMNodeList ) {
                    $resultArray = [];
                    for ( $i=0, $l=$result->length; $i<$l; $i ++ ) {
                        $resultArray[$i] = new static( simplexml_import_dom($result->item($i)), $this->parser );
                    }
                    return $resultArray;
                }
            }
            return $result;
        } else {
            return $this->ProxyCall($name, $args);
        }
    }

    public function find( $query) {
        $xpath = \arc\xml::css2Xpath( $query );
        $temp = $this->target->xpath( $xpath );
        foreach ($temp as $key => $value) {
            $temp[ $key ] = new static( $value, $this->parser );
        }
        return $temp;
    }
    
    public function offsetGet( $offset )
    {
        return (string) $this->target[$offset];
    }
    
    public function offsetSet( $offset, $value )
    {
        $this->target[$offset] = $value;
    }
    
    public function offsetUnset( $offset )
    {
        unset( $this->target[$offset] );
    }
}
