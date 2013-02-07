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
         * Create a simple array from a tree. Each non-null nodeValue will be added with the path to its node as the key
         * @param \arc\tree\Node $node
         * @param string $root
         * @param string $nodeName
         * @internal param \arc\tree\Node $tree
         * @return array [ $path => $data, ... ]
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
         * Creates a NamedNode tree from an array with path => nodeValue entries.
		 * @param array $tree The collapsed tree: [ $path => $data, ... ]
		 * @return \arc\tree\NamedNode an object tree with parent/children relations
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
				$subPath = \arc\path::diff( $path, $previousPath );
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
		 * Calls the first callback method on each successive parent until a non-null value is returned. Then
		 * calls all the parents from that point back to this node with the second callback in reverse order.
		 * The first callback (dive) must accept two parameters, the name of each child and the child node itself.
		 * The second callback (rise) must accept three parameters, the name of each child, the child node and the
         * result up to that point.
         * @param \arc\tree\Node $node A tree node, must have traversable childNodes property and a parentNode property
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
         * Calls the callback method on each parent of the given node, starting at the root.
         * @param \arc\tree\Node $node A tree node, must have traversable childNodes property and a parentNode property
         * @param callable $callback The callback function applied to each parent.
         * @return mixed
         */
		static public function parents( $node, $callback = null ) {
			if ( !isset( $callback ) ) {
				$callback = function( $node, $result ) {
					return ( (array) $result ) + array( $node );
				};
			}
			return self::dive( $node, null, $callback );
		}
		
        /**
         * Calls the callback method on each of the direct child nodes of the given node.
         * @param \arc\tree\Node $node
         * @param callable $callback The callback function applied to each child node
         * @param mixed $nodeName The name of the 'name' property or a function that returns the name of a node.
         * @return array
         */
		static public function ls( $node, $callback, $nodeName = 'nodeName' ) {
			$result = [];
			foreach( $node->childNodes as $child ) {
				$name = self::getNodeName( $child, $nodeName );
				$result[ $name ] = call_user_func( $callback, $child );
			}
			return $result;
		}

		/**
		 * Calls the callback method on each child of the current node, including the node itself, until a non-null
         * result is returned. Returns that result. The tree is walked depth first.
         * @param \arc\tree\Node $node
         * @param callable $callback The callback function applied to each child node
         * @return mixed
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
         * Calls the callback method on each child of the current node, including the node itself. Any non-null result
         * is added to the result array, with the path to the node as the key.
         * @param \arc\tree\Node $node
         * @param callable $callback The callback function applied to each child node
         * @param string $root
         * @param mixed $nodeName The name of the 'name' property or a function that returns the name of a node.
         * @return mixed
		 */
		static public function map( $node, $callback, $root = '', $nodeName = 'nodeName' ) {
			$result = [];
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
         * Calls the callback method on all child nodes of the given node, including the node itself. The result of each
         * call is passed on as the first argument to each succesive call.
         * @param \arc\tree\Node $node
         * @param callable $callback
         * @param mixed $initial The value to pass to the first callback call.
		 */
		static public function reduce( $node, $callback, $initial = null ) {
			$result = call_user_func( $callback, $initial, $node );
			foreach ( $node->childNodes as $child ) {
				$result = self::reduce( $child, $callback, $result );
			}
			return $result;
		}

        /**
         * Sorts the childNodes list of the node, recursively.
         * @param \arc\tree\Node $node
         * @param callable $callback
         * @param mixed $nodeName
         * @throws ExceptionDefault
         */
        static public function sort( $node, $callback, $nodeName = 'nodeName' ) {
            if ( is_array($node->childNodes) )  {
                $sort = function( $node ) {
                    uasort( $node->childNodes, $callback );
                };
            } else if ( $node->childNodes instanceof \ArrayObject ) {
                $sort = function( $node ) {
                    $node->childNodes->uasort( $callback );
                };
            } else {
                throw new \arc\ExceptionDefault( 'Cannot sort this tree - no suitable sort method found',
                    \arc\exceptions::OBJECT_NOT_FOUND);
            }
			self::map( $node, $sort, '', $nodeName );
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