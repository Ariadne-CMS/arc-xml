<?php
	namespace arc;

	class xml extends Pluggable {
		static $namespaces = array();

		static public function el() {
			$args       = func_get_args();
			$name       = array_shift($args);
			$attributes = array();
			$content    = array();
			foreach ($args as $arg) {
				if ( is_array( $arg ) && !( $arg instanceof xml\NodeList ) ) {
					$attributes = array_merge($attributes, $arg);
				} else if ( $arg instanceof xml\NodeList ) {
					$content    = array_merge( $content, (array) $arg);
				} else {
					$content[]  = $arg;
				}
			}
			$document = new xml\NodeList( new \DOMDocument() );
			$el = $document->createElement( $name );
			$el->setAttributes( $attributes );
			if ( $content ) {
				$el->appendChild( $content );
			}
			return $el;
		}

		static public function nodes() {
			$document = new xml\NodeList( new \DOMDocument() );
			$args = func_get_args();
			$result = array();
			foreach ( $args as $key => $arg ) {
				if ( is_array( $arg ) ) {
					$nodes = call_user_func_array('self::nodes', $arg);
					$result = array_merge( $result, $nodes->getArrayCopy() );
				} else if ( $arg instanceof \ArrayObject ) {
					$nodes = call_user_func_array('self::nodes', $arg->getArrayCopy() );
					$result = array_merge( $result, $nodes->getArrayCopy() );
				} else if ( is_string( $arg ) ) {
					if ( strpos( $arg, '<' ) !== false ) {
						$arg = self::parse( $arg );
						if ( $arg instanceof \ArrayObject ) {
							$result = array_merge( $result, $arg->getArrayCopy() );
						} else {
							$result[] = $arg;
						}
						$document->importNode( $arg );
					} else {
						$node = $document->domReference->createTextNode( $arg );
						$arg = new xml\Node( $node, $document );
						$result[] = $arg;
					}
				} else if ( $arg instanceof xml\NodeInterface ) {
					$document->importNode( $arg );
					$result[] = $arg;
				}
			}
			$nodes = new xml\NodeList( $result, $document );
			return $nodes;
		}

		static function preamble( $xmlEncoding = false, $xmlVersion = '1.0', $xmlStandalone = false )  {
			$document = new xml\NodeList( new \DOMDocument );
			return new xml\Preamble( $xmlEncoding, $xmlVersion, $xmlStandalone, $document );
		}

		static public function parseFull( $xml, $encoding = null ) {
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
				return new xml\NodeList( $dom );
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

		static public function parse( $xml, $encoding = null ) {
			try {
				return self::parseFull( $xml, $encoding );
			} catch( \arc\Exception $e ) {
				if ( $xml instanceof xml\NodeInterface ) {
					return $xml;
				}
				if ( !$xml || strpos( $xml, '<' ) === false ) {
					return new xml\Node( $xml );
				}
				try {
					// add a known (single) root element with all declared namespaces
					// libxml will barf on multiple root elements
					// and it will silently drop namespace prefixes not defined in the document
					$namespaces = array_merge( self::$namespaces, $namespaces );
					$root = '<arxmlroot';
					foreach ( $namespaces as $name => $uri ) {
						if ( $name === 0 ) {
							$root .= ' xmlns="';
						} else {
							$root .= ' xmlns:'.$name.'="';
						}
						$root .= htmlspecialchars( $uri ) . '"';
					}
					$root .= '>';
					$result = self::parse( $root.$xml.'</arxmlroot>' );
					$result = $result->firstChild->childNodes;
					return $result;
				} catch( \arc\Exception $e ) {
					return new xml\Node( $xml );
				}
			}
		}

		static public function parseName( $name, $attributes = array() ) {
			$colonPos = strrpos( $name, ':' );
			if ( $colonPos !== false ) {
				$prefix = substr( $name, 0, $colonPos );
				if ( strpos( $prefix, ':' ) ) {
					// no prefix used, but direct namespace uri: <http://namespace/:tagName>
					$namespace = $prefix;
					$prefix = '';
				} else {
					$namespace = self::lookupNamespacePrefix( $prefix );
				}
				$localName = substr( $name, $colonPos );
				return array( 'prefix' => $prefix, 'namespace' => $namespace, 'localName' => $localName );
			} else {
				return array( 'prefix' => '', 'namespace' => '', 'localName' => $name );
			}
		}

		static public function registerNameSpace( $prefix, $namespace ) {
			self::$namespaces[$namespace] = $prefix;
		}

		static public function lookupNameSpaceURI( $prefix ) {
			return array_search( $prefix, self::namespaces );
		}

		static public function lookupNameSpacePrefix( $namespace ) {
			$prefix = self::$namespaces[$namespace];
			if ( !$prefix ) {
				$prefix = '';
			}
			return $prefix;
		}

		static public function css2XPath( $cssSelector ) {

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
