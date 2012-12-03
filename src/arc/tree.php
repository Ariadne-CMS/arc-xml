<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc;

	/**
	 *	Utility methods to handle common path related tasks, cleaning, changing relative to absolute, etc.
	 */
	class tree extends Pluggable {


		/**
		 *	@param \arc\tree\Node $tree 
		 *	@return array [ $path => $data, ... ]
		 */
		static public function collapse( $node, $root = '', $nodeName = 'nodeName' ) {
			return \arc\tree::map( 
				$node, 
				function( $child ) {
					return $child->nodeValue;
				},
				$root, 
				$nodeName
			);
		}

		/**
		 *	@param $tree [ $path => $data, ... ]
		 *	@return \arc\tree\NamedNode an object tree with parent/children relations
		 */
		static public function expand( $tree = null ) {
			if ( is_object( $tree ) && isset( $tree->childNodes ) ) {
				return $tree; //FIXME: should we clone the tree to avoid shared state?
			}
			$root = new \arc\tree\NamedNode();
			if ( !is_array($tree) ) {
				return $root; // empty tree
			}
			ksort($tree); // sort by path, so parents are always earlier in the array than children
			$previousPath = '/';
			$previousParent = $root;
			foreach( $tree as $path => $data ) {
				$previousPath = $previousParent->getPath();
				$subPath = \arc\path::getRelativePath( $path, $previousPath );
				if ( $subPath ) {
					// create missing parent nodes, input tree may be sparsely filled
					$node = \arc\path::reduce(
						$subPath, 
						function( $previous, $name ) {
							if ( $name == '..' ) {
								return $previous->parentNode;
							}
							return $previous->appendChild( $name );
						}, 
						$previousParent
					);
				} else {
					// means the previousParent is equal to the current path, e.g. the root
					$node = $previousParent;
				}
				$node->nodeValue = $data;
				$previousParent = $node;
			}
			return $root;
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
		static public function dive( $node, $diveCallback = null, $riseCallback = null ) {
			$result = null;
			if ( is_callable( $diveCallback ) ) {
				$result = call_user_func( $diveCallback, $node );
			}
			if ( !isset( $result ) && $node->parentNode ) {
				$result = \arc\tree::dive( $node->parentNode, $diveCallback, $riseCallback );
			}
			if ( is_callable( $riseCallback ) ) {
				return call_user_func( $riseCallback, $node, $result );
			} else {
				return $result;
			}
		}

		/**
		*/
		static public function parents( $node, $callback = null ) {
			if ( !isset( $callback ) ) {
				$callback = function( $node, $result ) {
					return ( (array) $result ) + array( $node );
				};
			}
			return self::dive( $node, null, $callback );
		}
		

		static public function ls( $node, $callback, $nodeName = 'nodeName' ) {
			$result = array();
			foreach( $node->childNodes as $child ) {
				$name = self::getNodeName( $child, $nodeName );
				$result[ $name ] = call_user_func( $callback, $child );
			}
			return $result;
		}

		/**
		 * depth first search in the tree structure.
		 */
		static public function walk( $node, $callback ) {
			$result = call_user_func( $callback, $node );
			if ( isset( $result ) ) {
				return $result;
			}
			foreach( $node->childNodes as $child ) {
				$result = self::walk( $child, $callback );
				if ( isset( $result ) ) {
					return $result;
				}
			}
			return null;	
		}

		/**
		 */
		static public function map( $node, $callback, $root = '', $nodeName = 'nodeName' ) {
			$result = array();
			$name = self::getNodeName( $node, $nodeName );
			$path = $root . $name . '/';
			$callbackResult = call_user_func( $callback, $node );
			if ( isset($callbackResult) ) {
				$result[ $path ] = $callbackResult;
			}
			foreach ( $node->childNodes as $child ) {
				$result += self::map( $child, $callback, $path, $nodeName );
			}
			return $result;
		}

		/**
		 */
		static public function reduce( $node, $callback, $initial = null ) {
			$result = call_user_func( $callback, $initial, $node );
			foreach ( $node->childNodes as $child ) {
				$result = self::reduce( $child, $callback, $result );
			}
			return $result;
		}

		static public function sort( $node, $callback, $nodeName = 'nodeName' ) {
			self::map( 
				$node,
				function( $node ) {
					$this->childNodes->uasort( $callback );
				},
				'', 
				$nodeName
			);
		}

		static private function getNodeName( $node, $nodeName ) {
			if ( is_callable($nodeName) ) {
				$name = call_user_func( $nodeName, $node );
			} else {
				$name = $node->{$nodeName};
			}
			return $name;
		}
	}