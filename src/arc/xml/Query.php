<?php

	namespace arc\xml;

	class Query extends \ArrayObject implements NodeInterface {
		protected $query = null;
		public    $document = null;
		protected $contextNode = null;
		protected $list = null;

		public function __construct( $query, $document, $contextNode = null ) {
			$this->contextNode = $contextNode;
			$this->document = $document;
			$this->query = $query; // SimpleXMLElement or ducktyped variant thereof
		}

		public function __get( $name ) {
			switch( $name ) {
				case 'lastChild' :
				case 'firstChild' :
				case 'previousSibling' :
				case 'nextSibling' :
				case 'parentNode' :
					$this->applySearch();
					return $this->list->{$name};
				break;
			}
			return new Query( $this->query . '/' . $name, $this->document, $this->contextNode );
		}

		public function __set( $name, $value ) {
			// FIXME: create new node, make sure required parent nodes are created as needed as well.
		}

		protected function applySearch() {
			if ( !$this->list ) {
				$this->list = $this->document->xpath( $this->query, $this->contextNode );
			}
		}

		public function offsetGet( $offset ) {
			$this->applySearch();
			if ( $this->list ) {
				return $this->document->proxy( $this->list[$offset] );
			} else {
				return null;
			}
		}

		public function getArrayCopy() {
			$this->applySearch();
			$array = array();
			foreach ( $this->list as $el ) {
				$array[] = $this->document->proxy( $el );
			}
			return $array;
		}

		public function toString() {
			$this->applySearch();
			return ( $this->list ? $this->list->__toString() : '' );
		}

		public function __toString() {
			return $this->toString();
		}

		public function offsetSet( $offset, $value ) {
			// change a child node or preamble
			if ( !isset($offset) ) {
				$this->append( $value );
			} else {
				$item = $this->document->proxy( $this->offsetGet( $offset ) );
				if ( $item && $item->parentNode ) {
					$item->parentNode->replaceChild( $value, $item );
				}
			}
		}

		public function offsetUnset( $offset ) {
			// remove a child node or preamble...
			$item = $this->document->proxy( $this->offsetGet( $offset ) );
			if ( $item && $item->parentNode ) {
				$item->parentNode->removeChild( $item );
			}
			unset( $this->list[ $offset ] );
		}

		protected function getNearestParent() {
			// get last matching node(s)
			$query = $this->query;
			while ( $query &&  !($result = $this->document->xpath( $query ) ) ) {
				$query = substr( $query, 0, strrpos( $query, '/' ) );
			};
			$restquery = substr( $this->query, strlen( $query ) );
			return array( $result, $restquery );
		}

		public function append( $value ) {
			// $doc[] = new \arc\xml\Element(...)
			list( $nodes, $query ) = $this->getNearestParent();
			if ( $query ) {
				// implicitly add missing elements to the dom
				while ( $query ) {
					$subnode = substr( $query, 0, strpos( $query, '/' ) );
					$query = substr( $query, strlen( $subnode ) + 1 );
					if ( $subnode ) {
						$nodes = $nodes->appendChild( \arc\xml::el( $subnode )  );
					}
				}
			}
			$nodes->appendChild( $value );
		}

		public function appendChild( $value ) {
			$this->append( $value );
		}

		public function count() {
			$this->applySearch();
			return count( $this->list );
		}
	}
