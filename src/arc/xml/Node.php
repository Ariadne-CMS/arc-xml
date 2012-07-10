<?php

	namespace arc\xml;

	class Node implements NodeInterface {
		public $domNode = null;
		public $document = null;

		public function __construct( $DOMNode, $document ) {
			$this->domNode = $DOMNode;
			$this->document = $document;
		}

		public function __toString() {
			return $this->toString();
		}

		public function toString() {
			// FIXME: use asXML() method of simpleXML
			if ( $this->domNode instanceof \DOMCdataSection ) {
				return "<![CDATA[" . str_replace( "]]>", "]]&gt;", $this->nodeValue ) . "]]>";
			} else if ( $this->domNode instanceof \DOMComment ) {
				return "<!-- " . $this->nodeValue . " -->";
			} else {
				var_dump( $this->domNode );
				return (string) $this->nodeValue;
			}
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'nodeValue':
				case 'parentNode':
				case 'nextSibling':
				case 'previousSibling':
					return $this->domNode->{$name};
				break;
			}
		}

		public function __set( $name, $value ) {
			switch ( $name ) {
				case 'nodeValue':
					$this->domNode->{$name} = $value;
				break;
			}
		}

		public function __clone() {
			$this->domNode = $this->domNode->cloneNode();
		}

		public function cloneNode( $recurse = false ) {
			return clone $this;
		}

	}
