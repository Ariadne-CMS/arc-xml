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
		static public function collapse( $node, $prefix = '' ) {
			return $node->map( function( $name, $child ) {
				return $child->nodeValue;
			} );
		}

		/**
		 *	@param $tree [ $path => $data, ... ]
		 *	@return \arc\tree\Node an object tree with parent/children relations
		 */
		static public function expand( $tree = null ) {
			$root = new \arc\tree\NamedNode();
			if ( !is_array($tree) ) {
				return $root; // empty tree
			}
			ksort($tree); // sort by path, so parents are always earlier in the array than children
			$previousPath = '/';
			$previousParent = $root;
			$parentStack = array( $previousPath => $previousParent );
			foreach( $tree as $path => $data ) {
				// find the nearest matching parent as created from the input tree
				$previousParent = end( $parentStack );
				$previousPath = key( $parentStack );
	 			while ( $previousPath!='/' && !\arc\path::isChild( $path, $previousPath) ) {
					array_pop( $parentStack );
					$previousParent = end( $parentStack );
					$previousPath = key( $parentStack );
				}
				// add the new node
				$subPath = \arc\path::getRelativePath( $path, $previousPath );
				if ( $subPath ) {
					// create missing parent nodes, input tree may be sparsely filled
					$node = \arc\path::reduce( 
						$subPath, 
						function( $previous, $name ) {
							return $previous->appendChild( $name );
						}, 
						$previousParent
					);
				} else {
					// means the previousParent is equal to the current path, e.g. the root
					$node = $previousParent;
				}
				$node->nodeValue = $data;
				$parentStack[$path] = $node;
			}
			return $root;
		}

	}