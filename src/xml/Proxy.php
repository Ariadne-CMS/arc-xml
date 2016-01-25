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
 * This class is a proxy for both the SimpleXMLElement and DOMElement
 * properties and methods.
 * @property \SimpleXMLElement nodeValue
 */
class Proxy extends \ArrayObject implements DOMElement, SimpleXMLElement {

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

    private function _isDomProperty( $name ) {
        $domProperties = [
            'tagName', 'nodeType', 'parentNode',
            'firstChild', 'lastChild', 'previousSibling', 'nextSibling',
            'ownerDocument', 'namespaceURI', 'prefix',
            'localName', 'baseURI', 'textContent'
        ];
        return in_array( $name, $domProperties );
    }

    private function _getTargetProperty($name) {
        $value = null;
        if ( !$this->_isDomProperty($name) ) {
            $value = $this->target->{$name};
        } else {
            $dom = dom_import_simplexml($this->target);
            if ( isset($dom) ) {
                $value = $dom->{$name};
            }
        }
        return $value;
    }

    private function _proxyResult( $value ) {
        if ( $value instanceof \DOMElement ) {
            $value = simplexml_import_dom($value);
        }
        if ( $value instanceof \SimpleXMLElement ) {
            return new static( $value, $this->parser );
        } else {
            return $value;
        }
    }

    public function __get( $name) {
        if ($name == 'nodeValue') {
            return $this->target;
        }
        return $this->_proxyResult( $this->_getTargetProperty($name) );
    }

    private function _domCall( $name, $args ) {
        $dom = dom_import_simplexml($this->target);
        foreach ( $args as $index => $arg ) {
            if ( $arg instanceof \arc\xml\Proxy ) {
                $args[$index] = dom_import_simplexml( $arg->nodeValue );
            } else if ( $arg instanceof \SimpleXMLElement ) {
                $args[$index] = dom_import_simplexml( $arg );
            }
        }
        $importMethods = [
            'appendChild', 'insertBefore', 'replaceChild'
        ];
        if ( in_array( $name, $importMethods ) ) {
            if ( isset($args[0]) && $args[0] instanceof \DOMNode ) {
                if ( $args[0]->ownerDocument !== $this->ownerDocument ) {
                    $args[0] = $this->ownerDocument->importNode( $args[0], true);
                }
            }
        }
        $result = call_user_func_array( [ $dom, $name], $args );
        if ( isset($result) && is_object($result) ) {
            if ( $result instanceof \DOMElement ) {
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
    }

    public function __call( $name, $args ) {
        if ( !method_exists($this->target, $name) ) {
            return $this->_domCall( $name, $args );
        } else {
            return $this->ProxyCall($name, $args);
        }
    }

    /**
     * Search through the XML DOM with a single CSS selector
     * @param string $query the CSS selector, most CSS 2 selectors work
     * @return Proxy
     */
    public function find( $query) {
        $xpath = \arc\xml::css2Xpath( $query );
        $temp  = $this->target->xpath( $xpath );
        foreach ($temp as $key => $value) {
            $temp[ $key ] = new static( $value, $this->parser );
        }
        return $temp;
    }

    /**
     * Ssearches through the subtree for an element with the given id and returns it
     * @param string $id
     * @return Proxy
     */
    public function getElementById( $id ) {
        return current($this->find('#'.$id));
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
