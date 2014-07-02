<?php
/*
    TODO: implement simplexml access methods to the proxy
    probably needs to extend ArrayObject in some way to do this properly
 */
namespace arc\xml;

class Proxy {

    use \arc\traits\Proxy {
        \arc\traits\Proxy::__construct as private ProxyConstruct;
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
            return new Proxy( $value, $this->parser );
        } else {
            return $value;
        }
    }

    public function find( $query) {
        $xpath = \arc\xml::css2Xpath( $query );
        $temp = $this->target->xpath( $xpath );
        foreach ($temp as $key => $value) {
            $temp[ $key ] = new Proxy( $value, $this->parser );
        }
        return $temp;
    }
}
