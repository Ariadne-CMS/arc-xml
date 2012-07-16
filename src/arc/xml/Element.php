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

	class Element extends \ArrayObject implements NodeInterface {
		public $document = null;
		public $domNode = null;

		public function __construct( $element, $document ) {
			$this->document = $document;
			$this->domNode = $element;
		}

		public function proxy( $el ) {
			return $this->document->proxy( $el );
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'attributes' :
					return $this->proxy( $this->domNode->attributes, $this );
				break;
				case 'nodeValue' :
				case 'prefix' :
				case 'tagName' :
				case 'localName' :
				case 'namespaceURI' :
					return $this->domNode->{$name};
				break;
				case 'parentNode' :
				case 'firstChild' :
				case 'lastChild' :
				case 'nextSibling' :
				case 'previousSibling' :
				case 'childNodes' :
					return $this->proxy( $this->domNode->{$name}, $this );
				break;
				default:
					return new Query( $name, $this->document, $this );
				break;
			}
		}

		public function __set( $name, $value ) {
			switch ( $name ) {
				case 'attributes' :
					// replace all attributes with new array
				break;
				case 'nodeValue' :
				case 'prefix' :
					$this->domNode->{$name} = $value;
				break;
				case 'tagName' :
				case 'localName' :
				case 'namespaceURI' :
					// normally readonly, change tagname
				break;
				case 'parentNode' :
					// appendChild on new parentNode
				break;
				case 'firstChild' :
				case 'lastChild' :
				case 'nextSibling' :
				case 'previousSibling' :
					// replaceChild
				break;
				case 'childNodes' :
					// replaceChild on all children
				break;
				default:
					// replace existing node or add a new node
					// name can contain namespace URI or prefix
					// value can be a parseable xml string - in that case parse it
					$oldNodes = $this->getChildrenByTagName( $name );
					foreach( $oldNodes as $key => $oldNode ) {
						$this->domNode->removeChild( $oldNode );
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

		public function appendChild( $el ) {
			if ( is_string( $el ) ) {
				$el = \arc\xml::parse( $el );
			}
			if ( is_array( $el ) || $el instanceof \iterable ) {
				foreach ( $el as $item ) {
					$this->appendChild( $item );
				}
			} else {
				if ( is_string( $el ) ) {
					$el = $this->document->createTextNode( $el );
				}
				if ( $el instanceof NodeInterface ) {
					if ( $this->document !== $el->document ) {
						if ( $el->parentNode ) {
							$el->parentNode->removeChild( $el ); // remove element from original document for consistency
						}
						$this->document->importNode( $el );
					}
					$this->domNode->appendChild( $el->domNode );
				} else if ( $el instanceof \ArrayObject ) {
					foreach ( $el as $item ) {
						$this->appendChild( $item );
					}
				} else {
					throw new \Exception('unknown type '.serialize($el),101);
				}
			}
			return $el;
		}

		public function removeChild( $el ) {
			if ( is_array( $el ) || $el instanceof \iterable ) {
				foreach ( $el as $item ) {
					$this->appendChild( $item );
				}
			} else {
				$this->domNode->removeChild( $el->domNode );
			}
			return $el;
		}

		public function replaceChild( $el, $referenceEl ) {
			if ( is_array( $referenceEL ) || $referenceEl instanceof \iterable ) {
				// remove all referenceEls except first one, assign that to referenceEl
				$firstReferenceEl = false;
				foreach ( $referenceEl as $item ) {
					if ( !$firstReferenceEl ) {
						$firstReferenceEl = $item;
						$result[] = $firstReferenceEl;
						continue;
					}
					$result[] = $this->removeChild( $item );
				}
				$result[] = $this->replaceChild( $el, $firstReferenceEl );
				return $result;
			} else if ( is_array( $el ) || $el instanceof \iterable ) {
				// insert all elements before the referenceElement, then remove it
				foreach( $el as $item ) {
					$result = $this->insertBefore( $el, $referenceEl );
				}
				return $this->removeChild( $referenceEl );
			} else {
				return $this->proxy( $this->domNode->replaceChild( $el->domNode, $referenceEl->domNode ) );
			}
		}

		public function insertBefore( $el, $referenceEl = null ) {
			if ( is_array( $referenceEL ) || $referenceEl instanceof \iterable ) {
				foreach ( $referenceEl as $item ) {
					$referenceEl = $item;
					break;
				}
			}
			if ( is_array( $el ) || $el instanceof \iterable ) {
				foreach ( $el as $item ) {
					$result[] = $this->insertBefore( $item, $referenceEl );
				}
				return $result;
			} else {
				return $this->proxy( $this->domNode->insertBefore( $el->domNode, $referenceEl->domNode ) );
			}
		}

		public function getAttribute( $name ) {
			return $this->domNode->getAttribute( $name );
		}

		public function hasAttribute( $name ) {
			return $this->domNode->hasAttribute( $name );
		}

		public function setAttribute( $name, $value ) {
			$this->domNode->setAttribute( $name, $value );
		}

		public function setAttributes( $attributes ) {
			foreach ( $attributes as $name => $value ) {
				$this->domNode->setAttribute( $name, $value );
			}
		}

		public function removeAttribute( $name ) {
			return $this->domNode->removeAttribute( $name );
		}

		public function hasClass( $className ) {

		}

		public function addClass( $className ) {

		}

		public function removeClass( $className ) {

		}

		public function getChildrenByTagName( $tagName ) {
			return new Query( '/'.$tagName, $this->document, $this->domNode );
		}

		public function getElementsByTagName( $tagName ) {
			return new Query( '//'.$tagName, $this->document, $this->domNode );
		}

		public function getElementById( $id ) {
			return $this->document->getElementById( $id );
		}

		public function __toString() {
			return $this->toString();
		}

		public function toString() {
			if ( isset( $this->domNode ) ) {
				$s = simplexml_import_dom( $this->domNode );
				$result = (string) $s->asXML();
				if ( $this->domNode == $this->document->domDocument->documentElement ) {
					// simplexml_element::asXML() adds a preamble and any comments to the xml for the root element.
					$result = trim(preg_replace( '/<\?[^>]*\?>/','', $result ));
					$result = preg_replace( '/^<!--[^>]*-->/','', $result );
				}
				return $result;
			} else {
				return '';
			}
		}

		public function offsetGet( $offset ) {
			if ( is_string( $offset ) ) {
				list( $prefix, $namespace, $localName) = $this->document->parseName( $offset );
				if ( $namespace ) {
					return $this->domNode->getAttributeNS( $namespace, $localName );
				} else {
					return $this->domNode->getAttribute( $offset );
				}
			}
			return null;
		}

		public function offsetSet( $offset, $value ) {
			if ( is_string( $offset ) ) {
				list( $prefix, $namespace, $localName) = $this->document->parseName( $offset );
				if ( $namespace ) {
					$this->domNode->setAttributeNS( $namespace, $prefix.':'.$localName, $value );
				} else {
					$this->domNode->setAttribute( $offset, $value );
				}
			}
		}

		public function offsetExists( $offset ) {
			return $this->offsetGet( $offset ) !== null;
		}

		public function xpath( $xpathQuery ) {
			return $this->document->xpath( $xpathQuery, $this );
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