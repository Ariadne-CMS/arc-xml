<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	/* TODO: 
	 * - check performance of hash based grants storage vs. strings
	 * - perhaps refactor grants storage and check to seperate class/object as nodeValue in the tree
	 */

	namespace arc\grants;

	class GrantsTree {
		use \arc\traits\Proxy {
			\arc\traits\Proxy::__construct as private ProxyConstruct;
		}	

		private $tree = null;
		private $user   = null;
		private $groups = array();
		

		/**
		*	@param \arc\tree\NamedNode $tree The tree storage for event listeners.
		*   @param string $user
		*   @param array $groups
		*/
		public function __construct( $tree, $user, $groups = array() ) {
			$this->ProxyConstruct( $tree );
			$this->tree = $tree;
			$this->user = $user;
			$this->groups = $groups;
		}

		public function cd( $path ) {
			return new GrantsTree( $this->tree->cd( $path ), $this->user );
		}

		public function ls() {
			// FIXME: implement this
		}

		public function switchUser( $user, $groups = array() ) {
			return new GrantsTree( $this->tree, $user, $groups );
		}

		public function setUserGrants( $grants = null ) {
			if ( isset( $grants ) ) {
				$this->tree->nodeValue['user.'.$this->user ] = ' ' . trim( $grants ) . ' ';
			} else {
				unset( $this->tree->nodeValue['user.'.$this->user ] );
			}
		}

		public function setGroupGrants( $group, $grants = null ) {
			if ( isset( $grants ) ) {
				$this->tree->nodeValue['group.'.$group ] = ' ' . trim( $grants ) . ' ';
			} else {
				unset( $this->tree->nodeValue['group.'.$group ] );
			}
		}

		public function check( $grant ) {
			// uses strpos since it is twice as fast as preg_match for the most common cases
			$grants = $this->fetchGrants();
			if ( strpos( $grants, $grant.' ' ) === false ) { // exit early if no possible match is found
				return false;
			}
			return ( strpos( $grants, ' '.$grant.' ')!==false 
				|| strpos( $grants, ' ='.$grant.' ')!==false );
		}

		private function fetchGrants() {
			$user = $this->user;
			$groups = array_fill_keys( $this->groups, 1 );
			$grants = (string) \arc\tree::dive( 
				$this->tree, 
				function( $node ) use ( $user ) {
					if ( isset( $node->nodeValue['user.'.$user] ) ) {
						return $node->nodeValue['user.'.$user];
					}
				}, 
				function( $node, $grants ) use ( &$user, $groups ) {
					if ( !$user ) { // don't do this for user grants the first time
						$grants = preg_replace( 
							array( '/\=[^ ]*/', '/\>([^ ]*)/' ), 
							array( '', '$1' ), 
							$grants 
						);
					}
					$user = false;
					foreach ( $groups as $group ) {
						if ( isset( $node->nodeValue[ 'group.'.$group ] ) ) {
							$grants .= $node->nodeValue['group.'.$group ];
						}
					}
					return $grants;
				}
			);
			return $grants;
		}

	}