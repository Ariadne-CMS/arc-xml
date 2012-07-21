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

	class DataBinding {

		public function bindAsArray( $nodes, $name, $type = 'string' ) {
			$total = count( $nodes );
			$this->{$name} = array();
			foreach ( $nodes as $key => $node ) {
				$this->{$name}[$key] = $this->bindValue( $node, $type );
			}
			return $this;
		}

		public function bind( $node, $name, $type = 'string' ) {
			if ( ( is_array($node) || ( $node instanceof \Countable ) ) && count($node)>1 ) {
				return $this->bindAsArray( $node, $name, $type );
			}
			$this->{$name} = $this->bindValue( $node, $type );
			return $this;
		}

		public function __toString() {
			return $this->source->toString();
		}

		protected function bindValue( $source, $type ) {
			if ( $source instanceof NodeInterface ) {
				$nodeValue = $source->nodeValue;
				if (is_array($nodeValue) && !count($nodeValue)) {
					$nodeValue = null;
				}
			} else {
				$nodeValue = $source;
			}
			if ( is_callable($type) ) {
				$nodeValue = call_user_func( $type, $source );
			} else {
				switch ($type) {
					case 'int':
						$nodeValue = (int) $nodeValue;
					break;
					case 'float':
						$nodeValue = (float) $nodeValue;
					break;
					case 'string':
						$nodeValue = (string) $nodeValue;
					break;
					case 'bool':
						$nodeValue = (bool) $nodeValue;
					break;
					case 'url':
						if ( class_exists('\arc\url') ) {
							$nodeValue = \arc\url::url( $nodeValue );
						} else {
							$nodeValue = (string) $nodeValue;
						}
					break;
					case 'xml':
					case 'html':
						if ( $source instanceof NodeInterface ) {
							$nodeValue = (string) $source;
						}
					break;
					default       :
						if ( is_string($type) && class_exists($type) ) {
							$nodeValue = new $type($nodeValue);
						}
					break;
				}
			}
			return $nodeValue;
		}

	}
