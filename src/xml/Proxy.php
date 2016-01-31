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
        return isset($this->target) ? $this->target->asXML() : '';
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
        } else if ( $value instanceof \DOMNodeList ) {
            $array = [];
            for ( $i=0, $l=$value->length; $i<$l; $i ++ ) {
                $array[$i] = $value[$i];
            }
            $value = $array;
        }
        if ( $value instanceof \SimpleXMLElement ) {
            $value = new static( $value, $this->parser );
        } else if ( is_array($value) ) {
            foreach ( $value as $key => $subvalue ) {
                $value[$key] = $this->_proxyResult( $subvalue );
            }
        }
        return $value;
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
        return call_user_func_array( [ $dom, $name], $args );
    }

    public function __call( $name, $args ) {
        if ( !method_exists( $this->target, $name ) ) {
            return $this->_proxyResult( $this->_domCall( $name, $args ) );
        } else {
            return $this->_proxyResult( $this->ProxyCall( $name, $args ) );
        }
    }

    /**
     * Search through the XML DOM with a single CSS selector
     * @param string $query the CSS selector, most CSS 2 selectors work
     * @return Proxy
     */
    public function find( $query) {
        $xpath = \arc\xml::css2Xpath( $query );
        return $this->_proxyResult( $this->target->xpath( $xpath ) );
    }

    /**
     * Searches through the subtree for an element with the given id and returns it
     * @param string $id
     * @return Proxy
     */
    public function getElementById( $id ) {
        return current($this->find('#'.$id));
    }

    /**
     * Register a namespace alias and URI to use in xpath and find
     * @param string $ns
     * @param string $uri
     */
    public function registerNamespace( $prefix, $ns ) {
        if ( $this->target && $this->target instanceof \SimpleXMLElement ) {
            $this->target->registerXPathNamespace($prefix, $ns);
        }
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
