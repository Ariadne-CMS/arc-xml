<?php

namespace arc\xml;

class Parser {
	
	public $namespaces = array();

	public function __construct( $options = array() ) {
		$optionList = array( 'namespaces' );
		foreach( $options as $option => $optionValue ) {
			if ( in_array( $option, $optionList ) ) {
				$this->{$option} = $optionValue;
			}
		}
	}

	public function parse( $xml, $encoding = null ) {
		if ( !$xml ) {
			return Proxy( null );
		}
		if ( $xml instanceof Proxy ) { // already parsed
			return $xml;
		}
		$xml = (string) $xml;
		try {
			return $this->parseFull( $xml, $encoding );
		} catch( \arc\Exception $e ) {
			return $this->parsePartial( $xml, $encoding );
		}
	}

	private function parsePartial( $xml, $encoding ) {
		try {
			// add a known (single) root element with all declared namespaces
			// libxml will barf on multiple root elements
			// and it will silently drop namespace prefixes not defined in the document
			$root = '<arcxmlroot';
			foreach ( $this->namespaces as $name => $uri ) {
				if ( $name === 0 ) {
					$root .= ' xmlns="';
				} else {
					$root .= ' xmlns:'.$name.'="';
				}
				$root .= htmlspecialchars( $uri ) . '"';
			}
			$root .= '>';
			$result = $this->parseFull( $root.$xml.'</arcxmlroot>', $encoding );
			$result = $result->firstChild->childNodes;
			return $result;
		} catch( \arc\Exception $e ) {
			return new Proxy( $xml, $this );
		}
	}

	private function parseFull( $xml, $encoding = null ) {
		$dom = new \DomDocument();
		if ( $encoding ) {
			$xml = '<?xml encoding="' . $encoding . '">' . $xml;
		}
		libxml_disable_entity_loader(); // prevents XXE attacks
		$prevErrorSetting = libxml_use_internal_errors(true);
		if ( $dom->loadXML( $xml ) ) {
			if ( $encoding ) {
				foreach( $dom->childNodes as $item ) {
					if ( $item->nodeType == XML_PI_NODE ) {
						$dom->removeChild( $item );
						break;
					}
				}
				$dom->encoding = $encoding;
			}
			return new Proxy( $dom, $this );
		}
		$errors = libxml_get_errors();
		libxml_clear_errors();
		libxml_clear_errors();
		libxml_use_internal_errors( $prevErrorSetting );
		$message = 'Incorrect xml passed.';
		foreach ( $errors as $error ) {
			$message .= "\nline: ".$error->line."; column: ".$error->column."; ".$error->message;
		}
		throw new \arc\Exception( $message, exceptions::ILLEGAL_ARGUMENT );
	}

	static public function css2XPath( $cssSelector ) {
		/* (c) Tijs Verkoyen - http://blog.verkoyen.eu/blog/p/detail/css-selector-to-xpath-query/ */
		$cssSelectors = array(
			// E F: Matches any F element that is a descendant of an E element
			'/(\w)\s+(\w)/',
			// E > F: Matches any F element that is a child of an element E
			'/(\w)\s*>\s*(\w)/',
			// E:first-child: Matches element E when E is the first child of its parent
			'/(\w):first-child/',
			// E + F: Matches any F element immediately preceded by an element
			'/(\w)\s*\+\s*(\w)/',
			// E[foo]: Matches any E element with the "foo" attribute set (whatever the value)
			'/(\w)\[([\w\-]+)]/',
			// E[foo="warning"]: Matches any E element whose "foo" attribute value is exactly equal to "warning"
			'/(\w)\[([\w\-]+)\=\"(.*)\"]/',
			// div.warning: HTML only. The same as DIV[class~="warning"]
			'/(\w+|\*)?\.([\w\-]+)+/',
			// E#myid: Matches any E element with id-attribute equal to "myid"
			'/(\w+)+\#([\w\-]+)/',
			// #myid: Matches any E element with id-attribute equal to "myid"
			'/\#([\w\-]+)/'
		);

		$xPathQueries = array(
			'\1//\2',
			'\1/\2',
			'*[1]/self::\1',
			'\1/following-sibling::*[1]/self::\2',
			'\1 [ @\2 ]',
			'\1[ contains( concat( " ", @\2, " " ), concat( " ", "\3", " " ) ) ]',
			'\1[ contains( concat( " ", @class, " " ), concat( " ", "\2", " " ) ) ]',
			'\1[ @id = "\2" ]',
			'*[ @id = "\1" ]'
		);

		return (string) '//'. preg_replace($cssSelectors, $xPathQueries, $cssSelector);
	}

}