<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */
	 
	namespace arc\tree;

	class NamedNode implements \Serializable {

		public $nodeValue = null;
		private $parentNode = null;
		private $childNodes = null;
		private $nodeName = '';

		public function __construct( $nodeName='', $parentNode = null, $childNodes = null, $nodeValue = null ) {
			$this->nodeName = $nodeName;
			$this->parentNode = $parentNode;
			$this->childNodes = new NamedNodeList( (array) $childNodes, $this );
			$this->nodeValue = $nodeValue;
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'nodeName' :
					return $this->nodeName;
				break;
				case 'childNodes' :
					return $this->childNodes;
				break;
				case 'parentNode' :
					return $this->parentNode;
				break;
/*				default:
					return $this->cd( $name );
				break;
*/			}
		}

		public function __set( $name, $value ) {
			switch ( $name ) {
				case 'nodeName' :
					if ( $this->parentNode ) {
						if ( $this->parentNode->childNodes[$value] !== $this ) {
							$this->parentNode->childNodes[$value] = $this;
						}
					}
					$this->nodeName = $value;
				break;
				case 'childNodes' :
					// make sure nodelists aren't shared between namednodes.
					$this->childNodes = new NamedNodeList( (array) $value, $this );
				break;
				case 'parentNode' :
					if ( $value instanceof NamedNode ) {
						$value->appendChild( $this->nodeName, $this );
					} else if ( isset($value) ) {
						throw new \arc\Exception( 'parentNode is not a \arc\tree\NamedNode', \arc\exceptions::ILLEGAL_ARGUMENT );
					} else if ( $this->parentNode ) {
						$this->parentNode->removeChild( $this->nodeName );
					}
				break;
/*
				default:
					$this->childNodes[ $name ] = $value;
				break;
*/
			}
		}

		public function __isset( $name ) {
			switch( $name ) {
				case 'nodeName' :
				case 'childNodes':
					return true; // these are always _set_, but may be empty
				break;
				case 'parentNode':
					return isset( $this->parentNode );
				break;
				default:
					return isset( $this->childNodes[ $name ] );
				break;
			}
		}

		/* The tree itself must always be deep cloned, a single node cannot have two parentNodes.
		 * The nodeValue may be whatever - so if it is an object, that object will not be cloned.
		 */
		public function __clone() {
			$this->parentNode = null;
			$this->childNodes = clone $this->childNodes;
			$this->childNodes->parentNode = $this;
		}

		public function __toString() {
			return (string) $this->nodeValue;
		}

		// \Serializable interface 
		public function serialize() {
			return serialize( \arc\tree::collapse( $this ) );
		}
		
		public function unserialize( $data ) {
			return \arc\tree::expand( unserialize( $data ) );
		}
		
		/**
		 *	Adds a new child element to this node with the given name as index in the child list.
		 *	If an existing child has the same name, that child will be discarded.
		 *	@param string $name The index name of the child
		 *	@param mixed $data The data for the new child. If $data is not an instance of \arc\tree\NamedNode
		 *		a new instance will be constructed with $data as its nodeValue.
		 *	@return \arc\tree\NamedNode The new child node.
		 */
		public function appendChild( $nodeName, $child=null ) {
			if ( !( $child instanceof \arc\tree\NamedNode ) ) {
				$child = new \arc\tree\NamedNode( $nodeName, $this, null, $child );
			}
			if ( $child->parentNode !== $this ) {
				if ( isset($child->parentNode) ) {
					$child->parentNode->removeChild( $child->nodeName );
				}
				if ( isset( $this->childNodes[ $nodeName ] ) ) {
					$oldChild = $this->childNodes[ $nodeName ];
					$oldChild->parentNode = null;
				}
				$child->parentNode = $this;
			}
			$this->childNodes[ $nodeName ] = $child;
			return $child;
		}

		/**
		 *	Removes an existing child with the given name from this node.
		 *	@param string $name The index name of the child
		 *	@return \arc\tree\NamedNode The removed child or null.
		 */
		public function removeChild( $nodeName ) {
			if ( isset( $this->childNodes[ $nodeName ] ) ) {
				$child = $this->childNodes[ $nodeName ];
				unset( $this->childNodes[ $nodeName ] );
				$child->parentNode = null;
				return $child;
			} else {
				return null; 
			}
		}

		/**
		 */
		public function getPath( $root = null ) {
			return $this->parents( 
				function( $node, $result ) {
					return $result . $node->nodeName . '/';
				}
			);
		}


		public function getRootNode() {
			$result = $this->dive(
				function( $node ) {
					return isset( $node->parentNode ) ? null : $node;
				}
			);
			return $result;
		}

		/**
		 *	Returns the node with the given path, relative to this node. If the path
		 *  does not exist, missing nodes will be created automatically.
		 *	@param string $path The path to change to
		 *	@return \arc\tree\Node The target node corresponding with the given path.
		 */
		public function cd( $path ) {
			if ( \arc\path::isAbsolute( $path ) ) {
				$node = $this->getRootNode();
			} else {
				$node = $this;
			}
			$result = \arc\path::reduce( $path, function( $node, $name ) {
				switch( $name ) {
					case '..':
						return ( isset( $node->parentNode ) ? $node->parentNode : $node );
					break;
					case '.':
					case '':
						return $node;
					break;
					default:
						if ( !isset( $node->childNodes[ $name ] ) ) {
							return $node->appendChild( $name );
						} else {
							return $node->childNodes[ $name ];
						}
					break;
				}
			}, $node);
			return $result;
		}


		/**
		 *  Calls a callback method on each child of this node, returns an array with name => result pairs.
		 *  The callback method must accept two parameters, the name of the child and the child node itself.
		 *  @param callable $callback The callback method to run on each child.
		 *  @return array An array of result values with the name of each child as key.
		 */
		public function ls( $callback ) {
			$result = array();
			foreach( $this->childNodes as $child ) {
				$result[ $child->nodeName ] = call_user_func( $callback, $child );
			}
			return $result;
		}

		public function sort( $callback ) {
			$this->map( function( $node ) {
				$this->childNodes->uasort( $callback );
			} );
		}
		
		/**
		 * Calls the first callback method on each successive parent untill a non-null value is returned. Then
		 * calls all the parents from that point back to this node with the second callback in reverse order.
		 * The first callback (dive) must accept two parameters, the name of each child and the child node itself.
		 * The second callback (rise) must accept threee parameters, the name of each child, the child node and the result upto then.
		 * @param callable $diveCallback The callback for the dive phase.
		 * @param callable $riseCallback The callback for the rise phase.
		 * @return mixed
		 */
		public function dive( $diveCallback = null, $riseCallback = null ) {
			$result = null;
			if ( is_callable( $diveCallback ) ) {
				$result = call_user_func( $diveCallback, $this );
			}
			if ( !isset( $result ) && $this->parentNode ) {
				$result = $this->parentNode->dive( $diveCallback, $riseCallback );
			}
			if ( is_callable( $riseCallback ) ) {
				return call_user_func( $riseCallback, $this, $result );
			} else {
				return $result;
			}
		}

		/**
		*/
		public function parents( $callback = null ) {
			if ( !isset( $callback ) ) {
				$callback = function( $node, $result ) {
					return ( (array) $result ) + array( $node );
				};
			}
			return $this->dive(	null, $callback );
		}
		
		/**
		 * depth first search in the tree structure.
		 */
		public function walk( $callback ) {
			$result = call_user_func( $callback, $this );
			if ( isset( $result ) ) {
				return $result;
			}
			foreach( $this->childNodes as $child ) {
				$result = $child->walk( $callback );
				if ( isset( $result ) ) {
					return $result;
				}
			}
			return null;	
		}

		/**
		 */
		public function map( $callback, $root = '' ) {
			$result = array();
			$path = $root . $this->nodeName . '/';
			$callbackResult = call_user_func( $callback, $this );
			if ( isset($callbackResult) ) {
				$result[ $path ] = $callbackResult;
			}
			foreach ( $this->childNodes as $child ) {
				$result += $child->map( $callback, $path );
			}
			return $result;
		}

		/**
		 */
		public function reduce( $callback, $initial = null ) {
			$result = call_user_func( $callback, $initial, $child );
			foreach ( $this->childNodes as $child ) {
				$result = $child->reduce( $callback, $result );
			}
			return $result;
		}

 	}