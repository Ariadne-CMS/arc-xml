<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */
	 
	 /*
		TODO: 
		- parents() method, callback or just return an array of nodes?
		- getPath() method, how to make this fast?
		- cd() must accept absolute paths
		- map / reduce should start with the current node, not the child nodes
	 */

	namespace arc\tree;

	class NamedNode {

		private $parentNode = null;
		private $childNodes = null;
		public $nodeValue = null;
		private $name = '';

		public function __construct( $name='', $parentNode = null, $childNodes = null, $nodeValue = null ) {
			$this->name = $name;
			$this->parentNode = $parentNode;
			$this->childNodes = new NodeList( (array) $childNodes, $this );
			$this->nodeValue = $nodeValue;
		}

		public function __get( $name ) {
			switch ( $name ) {
				case 'name' :
					return $this->name;
				break;
				case 'childNodes' :
					return $this->childNodes;
				break;
				case 'parentNode' :
					return $this->parentNode;
				break;
			}
		}

		public function __set( $name, $value ) {
			switch ( $name ) {
				case 'name' :
					if ( $this->parentNode ) {
						if ( $this->parentNode->childNodes[$value] !== $this ) {
							$this->parentNode->childNodes[$value] = $this;
						}
					}
					$this->name = $value;
				break;
				case 'childNodes' :
					// make sure nodelists aren't shared between namednodes.
					$this->childNodes = new NodeList( (array) $value, $this );
				break;
				case 'parentNode' :
					if ( $value instanceof NamedNode ) {
						$value->appendChild( $this->name, $this );
					} else if ( isset($value) ) {
						throw new \arc\Exception( 'parentNode is not a \arc\tree\NamedNode', \arc\exceptions::ILLEGAL_ARGUMENT );
					}
				break;
			}
		}

		public function __clone() {
			$this->parentNode = null;
			$this->childNodes = clone $this->childNodes;
			foreach( $this->childNodes as $child ) {
				$child->parentNode = $this;
			}
		}

		public function __toString() {
			return (string) $this->nodeValue;
		}

		/**
		 *	Adds a new child element to this node with the given name as index in the child list.
		 *	If an existing child has the same name, that child will be discarded.
		 *	@param string $name The index name of the child
		 *	@param mixed $data The data for the new child. If $data is not an instance of \arc\tree\NamedNode
		 *		a new instance will be constructed with $data as its nodeValue.
		 *	@return \arc\tree\NamedNode The new child node.
		 */
		public function appendChild( $name, $child=null ) {
			if ( !( $child instanceof \arc\tree\NamedNode ) ) {
				$child = new \arc\tree\NamedNode( $name, $this, null, $child );
			}
			if ( $child->parentNode !== $this ) {
				if ( isset($child->parentNode) ) {
					$child->parentNode->removeChild( $child->name );
				}
				if ( isset( $this->childNodes[ $name ] ) ) {
					$oldChild = $this->childNodes[ $name ];
					$oldChild->parentNode = null;
				}
				$child->parentNode = $this;
			}
			$this->childNodes[$name] = $child;
			return $child;
		}

		/**
		 *	Removes an existing child with the given name from this node.
		 *	@param string $name The index name of the child
		 *	@return \arc\tree\NamedNode The removed child or null.
		 */
		public function removeChild( $name ) {
			if ( isset( $this->childNodes[ $name ] ) ) {
				$child = $this->childNodes[ $name ];
				unset( $this->childNodes[$name] );
				$child->parentNode = null;
				return $child;
			} else {
				return null; 
			}
		}

		/**
		 */
		public function getPath( $root = null ) {
			return $this->dive( 
				function( $name, $parent ) {
					return $parent === $root ? '/' : null;
				},
				function( $name, $parent, $result ) {
					return $result . $name . '/';
				}
			);
		}

		/**
		 *	Returns the node with the given path, relative to this node.
		 *	@param string $path The path to change to
		 *	@param calleable $notFoundCallback optional Callback method that is invoked when a child node 
		 *		is unavailable.
		 *	@return \arc\tree\Node The target node corresponding with the given path.
		 *	@throws \arc\Exception OBJECT_NOT_FOUND when the given path has no corresponding node and no
		 *		$notFoundCallback is defined.
		 */
		public function cd( $path, $notFoundCallback = null ) {
			return \arc\path::reduce( $path, function( $node, $name ) {
				switch( $name ) {
					case '..':
						return ( isset( $node->parentNode ) ? $node->parentNode : $node );
					break;
					case '.':
					case '':
						return $node;
					break;
					default:
						if ( isset( $node->childNodes[ $name ] ) ) {
							return $node->childNodes[$name];
						}
						if ( isset( $notFoundCallback ) ) {
							return call_user_func( $notFoundCallback, $node, $name );
						} else {
							throw new \arc\Exception( 'Node '.$name.' not found', \arc\exceptions::OBJECT_NOT_FOUND );
						}
					break;
				}
			}, $this);
		}


		/**
		 *	Returns the node with the given path, relative to this node. Creates empty nodes where needed.
		 *	@param string $path The path to change to.
		 *	@return \arc\tree\Node The target node corresponding with the given path.
		 */
		public function mkdir( $path ) {
			return $this->cd( $path, function( $node, $name ) {
				return $this->appendChild( $name );
			});
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
				$result[$child->name] = call_user_func( $callback, $child );
			}
			return $result;
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
		public function parents( $root = null ) {
		
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
			$path = $root . $this->name . '/';
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