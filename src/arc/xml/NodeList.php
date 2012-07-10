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

	class NodeList extends \ArrayObject implements NodeInterface {
		public $domReference = null;
		public $domNodeList = null;
		public $document = null;
		protected $namespaces = array();
		protected $iterator = null;
		protected $parentNode = null;
		protected $preamble = null;
		protected $domXPath = null;
		protected $objectStore = null;

		public function __construct( $domReference, $document = null, $parentNode = null ) {
			$this->domReference = $domReference;
			if ( $this->domReference instanceof \DOMDocument ) {
				$this->domXPath = new \DOMXPath( $this->domReference );
				$this->domNodeList = $this->domReference->childNodes;
				$this->objectStore = new \splObjectStorage();
				if ( $this->domReference->documentElement ) {
					// there seems to be no reliable way to check if a preamble was present and what it looked like
					// simplexml's asXML() on the root element comes closest, though it adds a minimal preamble if none
					// is present.
					$s = simplexml_import_dom($this->domReference->documentElement);
					$x = $s->asXML();
					$p = preg_match_all('/<\?xml([[:space:]]+|version="([^"]+)"|encoding="([^"]+)"|standalone="([^"]+)")*\?>/', $x, $matches);
					if ( $matches[2][0] ) {
						$version = $matches[2][0];
					}
					if ( $matches[3][0] ) {
						$encoding = $matches[3][0];
					}
					if ( $matches[4][0] ) {
						$standalone = $matches[4][0];
					}
					$this->preamble = new Preamble( $encoding, $version, $standalone, $this);
				}
			} else if ( $this->domReference instanceof \DOMNodeList ) {
				$this->domNodeList = $this->domReference;
			} else if ( $this->domReference instanceof \ArrayObject || is_array( $this->domReference ) ) {
				$this->domNodeList = (array) $this->domReference;
				if ( $document->domReference ) {
					$this->domReference = $document->domReference;
				} else {
					unset( $this->domReference );
				}
			} else {
				throw new \Exception('ERROR: reference given is of unknown type, must be a DOMNodeList, DOMDocument, ArrayObject or array', 101);
			}
			if ( !$document ) {
				$document = $this;
			}
			$this->document = $document;
			$this->parentNode = $parentNode;
		}

		public function __get( $name ) {
			switch( $name ) {
				case 'lastChild' :
					return ( $this->count() ? $this[ $this->count() - 1 ] : null );
				break;
				case 'firstChild' :
					return $this[ 0 ];
				break;
				case 'previousSibling' :
					return ( $this[ 0 ] ? $this[ 0 ]->previousSibling : null );
				break;
				case 'nextSibling' :
					return ( $this->count() ? $this[ $this->count() - 1 ]->nextSibling : null );
				break;
				case 'parentNode' :
					return $this->parentNode;
				break;
				case 'childNodes' :
					return $this;
				break;
				case 'nodeValue' :
				case 'prefix':
				case 'tagName':
				case 'localName':
				case 'namespaceURI':
					$array = array();
					foreach ( $this as $el ) {
						$array[] = $el->{$name};
					}
					return $array;
				break;
				default:
					$xpathQuery = '/' . $name;
					return new Query( $xpathQuery, $this );
				break;
			}
		}

		public function __set( $name, $value ) {
			switch ( $name ) {
				case 'lastChild':
				break;
				case 'firstChild':
				break;
				case 'previousSibling':
				break;
				case 'nextSibling':
				break;
				case 'parentNode':
				break;
				case 'childNodes':
				break;
				case 'nodeValue':
				case 'prefix':
					foreach ( $this as $el ) {
						$el->{$name} = $value;
					}
				break;
				case 'tagName':
				case 'localName':
				case 'namespaceURI':
					throw \Exception("$name is readonly.",101);
				break;
				default:
					$oldNodes = $this->getChildrenByTagName( $name );
					foreach( $oldNodes as $key => $oldNode ) {
						$this->removeChild( $oldNode );
					}
					if ( $value instanceof NodeInterface ) {
						$newChild = $value;
					} else {
						$newChild = $this->document->createElement( $name, array(), $value );
					}
					$this->appendChild( $newChild );
				break;
			}
		}

		public function registerNameSpace( $prefix, $namespace ) {
			$this->namespaces[$namespace] = $prefix;
			$this->domXPath->registerNamespace( $prefix, $namespace );
		}

		public function lookupNameSpaceURI( $prefix ) {
			$uri = array_search( $prefix, $this->namespaces );
			if ( !$uri ) {
				$uri = array_search( $prefix, \arc\xml::$namespaces );
			}
			return $uri;
		}

		public function lookupPrefix( $namespace ) {
			$prefix = $this->namespaces[$namespace];
			if ( !isset($prefix) ) {
				$prefix = \arc\xml::$namespaces[$namespace];
			}
			if ( !$prefix ) {
				$prefix = '';
			}
			return $prefix;
		}

		public function proxy( $node, $reference = null ) {
			if ( $node == null ) {
				return $node;
			}
			if ( !is_object( $node ) ) {
				return $node;
			}
			if ( $this->objectStore->contains( $node ) ) {
				// node has already been retrieved before, so return the same wrapped object
				// this makes it possible to compare two nodes and see if they are the same
				return $this->objectStore[ $node ];
			}
			if ( $node instanceof \DOMNamedNodeMap ) {
				$wrapped = new AttributeList( $node, $this, $reference );
			} else if ( $node instanceof \DOMElement ) {
				$wrapped = new Element( $node, $this );
			} else if ( $node instanceof \DOMNode ) {
				$wrapped = new Node( $node, $this );
			} else if ( $node instanceof Preamble || $node instanceof Node
					|| $node instanceof Element || $node instanceof NodeList ) {
				$wrapped = $node;
			} else if ( $node instanceof \DOMNodeList ) {
				// don't store DOMNodeLists in the objectStore, as they don't update automatically
				return new NodeList( $node, $this, $reference );
			} else {
				throw new \Exception('FIXME: unknown node type', 1 );
			}
			$this->objectStore[ $node ] = $wrapped;
			return $wrapped;
		}

		public function xpath( $query, $contextNode = null ) {
			if ( $this != $this->document ) {
				return $this->document->xpath( $query, $contextNode );
			}
			if ( $contextNode && $contextNode->domNode ) {
				$contextNode = $contextNode->domNode;
			}
			$result = $this->domXPath->query( $query, $contextNode );
			if ( !$result || $result->length==0 ) {
				return null;
			}
			return $this->document->proxy( $result );
		}

		protected function getDocument() {
			if ( $this->domReference instanceof \DOMDocument ) {
				return $this->domReference;
			} else {
				return $this->domReference->ownerDocument;
			}
		}
		
		public function createTextNode( $value ) {
			$domNode = $this->getDocument()->createTextNode( ''.$value );
			return $this->document->proxy( $domNode );
		}
		
		public function createComment( $value ) {
			$domNode = $this->getDocument()->createComment( ''.$value );
			return $this->document->proxy( $domNode );
		}
		
		public function createCDATASection( $value ) {
			$domNode = $this->getDocument()->createCDATASection( ''.$value );
			return $this->document->proxy( $domNode );
		}

		public function createElement( $name, $attributes = array(), $value = '' ) {
			// must accept wide range of possible arguments
			if ( is_string( $name ) && strpos( $name, '<' ) ) {
				// xml string given
				$xml = \arc\xml::parse( $name );
				$imported = $this->importNode( $xml );
				return $imported;
			} else if ( $name instanceof Element ) {
				// element passed, so just return that, imported in this DOMDocument
				$imported = $this->importNode( $name );
				return $imported;
			} else if ( !is_string( $name ) ) {
				throw new \arc\Exception( 'arc\\xml\\createElement requires name to be a string', 101 );
			} else {
				if ( is_array( $value ) ) {
					// create a node list of elements
					$result = array();
					foreach ( $value as $item ) {
						$result = $this->createElement( $name, $attributes, $item );
					}
					return new NodeList( $result, $this );
				} else {
					// check if value is parseable xml
					$nodeValue = $value;
					// now create the element
					list ( $prefix, $namespace, $localName ) = $this->parseName( $name );
					if ( $this->domReference instanceof \DOMDocument ) {
						$domDocument = $this->domReference;
					} else {
						$domDocument = $this->domReference->ownerDocument;
					}
					if ( $namespace ) {
						$domElement = $domDocument->createElementNS( $namespace, $prefix.':'.$localName, $nodeValue );
					} else {
						$domElement = $domDocument->createElement( $name, $nodeValue );
					}
					return $this->document->proxy( $domElement );
				}
			}
		}

		public function importNode( $el, $deep = true ) {
			if ( $this !== $this->document ) {
				return $this->document->importNode( $el, $deep );
			}
			if ( $el->document !== $this ) {
				if ( $el instanceof Preamble ) {
					// nothing to do, preamble is not a node in DOMDocument
				} else if ( $el instanceof Element || $el instanceof Node ) {
					// import the node into the current domDocument
					$oldNode = $el->domNode;
					unset( $this->objectStore[ $oldNode ] );
					if ( $this->domReference instanceof \DOMDocument ) {
						$domDocument = $this->domReference;
					} else {
						$domDocument = $this->domReference->ownerDocument;
					}
					$el->domNode = $domDocument->importNode( $el->domNode, $deep );
					// also add the nodes wrapper to the list of known wrappers, so we return the same object in later retrievals
					$this->objectStore[ $el->domNode ] = $el;
				} else if ( $el instanceof NodeList ) {
					foreach ( $el as $item ) {
						$this->importNode( $item, $deep );
					}
				} else {
					throw new \Exception('unknown type '.serialize($el), 101);
				}
			}
			return $el;
		}

		public function offsetExists( $offset ) {
			if ( $this->preamble ) {
				if ( $offset == 0 ) {
					return true;
				} else {
					$offset -= 1;
				}
			}
			if ( is_array( $this->domNodeList ) || $this->domNodeList instanceof \ArrayAccess ) {
				$item = $this->domNodeList[ $offset ];
			} else {
				$item = $this->domNodeList->item( $offset );
			}
			return isset( $item );
		}

		public function offsetGet( $offset ) {
			if ( $this->preamble ) {
				if ( $offset == 0 ) {
					return $this->preamble;
				} else {
					$offset -= 1;
				}
			}
			if ( is_array( $this->domNodeList ) || $this->domNodeList instanceof \ArrayAccess ) {
				$item = $this->domNodeList[ $offset ];
			} else {
				$item = $this->domNodeList->item( $offset );
			}
			return $this->document->proxy( $item );
		}

		public function offsetSet( $offset, $value ) {
			if ( $this->preamble ) {
				if ( $offset == 0 ) {
					$this->preamble = null;
					if ( $value instanceof Preamble ) {
						$this->preamble = $value;
						return;
					} else {
						$this->domNodeList = $this->getArrayCopy();
						array_unshift( $this->domNodeList, '' );
					}
				} else {
					$offset -= 1;
				}
			}
			// problem: domNodeList is immutable
			$this->domNodeList = $this->getArrayCopy();
			$originalItem = $this->domNodeList[ $offset ];
			$newItem = $this->createElement( $value );
			if ( $originalItem ) {
				// replace
				$this->replaceChild( $originalItem, $newItem );
			} else {
				// append
				$this->appendChild( $newItem );
			}
			$this->domNodeList[ $offset ] = $newItem;
		}

		public function offsetUnset( $offset ) {
			if ( $this->preamble ) {
				if ( $offset == 0 ) {
					unset( $this->preamble );
					return;
				} else {
					$offset -= 1;
				}
			}
			$this->domNodeList = $this->getArrayCopy();
			$item = $this->domNodeList[ $offset ];
			if ( $item ) {
				$this->removeChild( $item );
			}
			unset( $this->domNodeList[ $offset ] );
		}

		public function getIterator() {
			if ( !$this->iterator ) {
				$this->iterator = new NodeListIterator($this);
			}
			return $this->iterator;
		}

		public function __toString() {
			$result = '';
			foreach ( $this as $el ) {
				$result .= $el . "\n";
			}
			return $result;
		}

		public function append( $value ) {
			$this->appendChild( $value );
		}

		public function count() {
			if ( is_array( $this->domNodeList ) || $this->domNodeList instanceof \ArrayAccess ) {
				return count( $this->domNodeList );
			} else {
				return $this->domNodeList->length;
			}
		}

		public function getArrayCopy() {
			if ( is_array( $this->domNodeList ) || $this->domNodeList instanceof \ArrayAccess ) {
				return (array) $this->domNodeList;
			} else {
				$result = array();
				$l = $this->domNodeList->length;
				for ( $i=0; $i<$l; $i++) {
					$result[] = $this->domNodeList->item( $i );
				}
				return $result;
			}
		}

		public function appendChild( $el ) {
			if ( $this->parentNode ) { // childNodes list
				$this->parentNode->appendChild( $el );
			} else { // result of a search
				if ( $this->lastChild && $this->lastChild->parentNode ) {
					$this->lastChild->parentNode->appendChild( $el );
				}
			}
		}

		public function removeChild( $el ) {
			if ( $el->parentNode ) {
				$el->parentNode->removeChild( $el );
			}
		}

		public function insertBefore( $el ) {

		}

		public function replaceChild( $el, $referenceEl ) {

		}

		public function getAttribute( $name ) {

		}

		public function hasAttribute( $name ) {

		}

		public function setAttribute( $name, $value ) {

		}

		public function setAttributes( $attributes ) {

		}

		public function removeAttribute( $name ) {

		}

		public function getChildrenByTagName( $tagName ) {
			if ( $this->parentNode ) {
				return $this->xpath( '/'.$tagName, $this->parentNode );
			} else {
				list( $prefix, $namespace, $localName ) = $this->parseName( $tagName );
				$result = array();
				if ( $namespace ) {
					foreach( $this as $item ) {
						if ( $item->namespaceURI==$namespace && $item->localName == $localName ) {
							$result[] = $item;
						}
					}
				} else {
					foreach( $this as $item ) {
						if ( $item->tagName == $tagName ) {
							$result[] = $item;
						}
					}
				}
				return new NodeList($result, $this->document);
			}
		}

		public function getElementsByTagName( $tagName ) {
			if ( $this->parentNode ) {
				return $this->xpath( '//'.$tagName, $this->parentNode );
			} else {
				list( $prefix, $namespace, $localName ) = $this->parseName( $tagName );
				$result = array();
				if ( $namespace ) {
					foreach( $this as $item ) {
						if ( $item->namespaceURI==$namespace && $item->localName == $localName ) {
							$result[] = $item;
						}
						$result = array_merge( $result, (array) $item->getElementsByTagName( $tagName ) );
					}
				} else {
					foreach( $this as $item ) {
						if ( $item->tagName == $tagName ) {
							$result[] = $item;
						}
						$result = array_merge( $result, (array) $item->getElementsByTagName( $tagName ) );
					}
				}
				return new NodeList($result, $this->document);
			}
		}

		public function getElementById( $id ) {
			return $this->proxy( $this->getDocument()->getElementById( $id ) );
		}

		public function parseName( $name, $attributes = array() ) {
			//FIXME: copy of  \arc\xml::parseName, needs refactoring
			$colonPos = strrpos( $name, ':' );
			if ( $colonPos !== false ) {
				$prefix = substr( $name, 0, $colonPos );
				if ( strpos( $prefix, ':' ) ) {
					// no prefix used, but direct namespace uri: <http://namespace/:tagName>
					$namespace = $prefix;
					$prefix = '';
				} else {
					$namespace = $this->lookupPrefix( $prefix );
				}
				$localName = substr( $name, $colonPos );
				$result = array( 'prefix' => $prefix, 'namespace' => $namespace, 'localName' => $localName );
			} else {
				$result = array( 'prefix' => '', 'namespace' => '', 'localName' => $name );
			}
			// make list( $a, $b, $c ) = parseName() work
			return array_merge( array_values( $result ), $result ); 
		}

		public function find( $selector ) {
			$xpathQuery = \arc\xml::css2XPath( $selector );
			return $this->xpath( $xpathQuery );
		}

		public function querySelectorAll( $selector ) {
			return $this->find( $selector );
		}
		
		public function bind( $nodes, $name, $type = 'string' ) {
			$b = new DataBinding( );
			return $b->bind( $nodes, $name, $type );
		}

		public function bindAsArray( $nodes, $type = 'string' ) {
			$b = new DataBinding( );
			return $b->bindAsArray( $nodes, 'list', $type)->list;
		}
	}
