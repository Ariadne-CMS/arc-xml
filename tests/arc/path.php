<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	require_once( __DIR__.'/../bootstrap.php' );
	 
	class TestPath extends UnitTestCase {

		function testMapReduce() {
			$path = '/a/b/c/';
			$result = \arc\path::map( $path, function( $entry ) {
				return strtoupper($entry);
			});
			$this->assertTrue( $result === '/A/B/C/' );

			$result = \arc\path::reduce( $path, function( &$result, $entry ) {
				return $result.$entry;
			});
			$this->assertTrue( $result === 'abc' );

			$result = \arc\path::map( '/', function( $entry ) {
				return 'a';
			});
			$this->assertTrue( $result === '/' );
			
			$result = \arc\path::map( 'frop', function($entry) {
				return 'a';
			});
			$this->assertTrue( $result === '/a/' );
		}

		function testNormalize() {
			$this->assertTrue( \arc\path::normalize('/') === '/' );
			$this->assertTrue( \arc\path::normalize('/test/') === '/test/' );
			$this->assertTrue( \arc\path::normalize('/test//') === '/test/' );
			$this->assertTrue( \arc\path::normalize('/test/../') === '/' );
			$this->assertTrue( \arc\path::normalize('test') === '/test/' );
			$this->assertTrue( \arc\path::normalize( '../', '/test/') === '/' );
			$this->assertTrue( \arc\path::normalize( '..', '/test/foo/') === '/test/' );
			$this->assertTrue( \arc\path::normalize( '/..//../', '/test/') === '/' );
			$this->assertTrue( \arc\path::normalize( null, '/test/') === '/test/' );
			$this->assertTrue( \arc\path::normalize( false, '/test/') === '/test/' );
			$this->assertTrue( \arc\path::normalize( array( 'something' ), '/test/') === '/test/' );
		}

		function testParents() {
			$parents = \arc\path::parents('/test/');
			$this->assertTrue( $parents == array('/','/test/'));
			$parents = \arc\path::parents('/test/foo/','/test/');
			$this->assertTrue( $parents == array( '/test/', '/test/foo/'));
			$parents = \arc\path::parents('/test/','/tost/');
			$this->assertTrue( $parents == array( '/tost/') );
		}

		function testParent() {
			$this->assertTrue( \arc\path::parent('/') == null );
			$this->assertTrue( \arc\path::parent('/test/') == '/');
			$this->assertTrue( \arc\path::parent('/a/b/') == '/a/');
			$this->assertTrue( \arc\path::parent('/a/b/', '/a/b/') == null );
			$this->assertTrue( \arc\path::parent('/a/b/', '/a/') == '/a/' );
			$this->assertTrue( \arc\path::parent('/a/b/', '/test/') == null );
		}		

		function testClean() {
			$this->assertTrue( \arc\path::clean('/a/b/') == '/a/b/' );
			$this->assertTrue( \arc\path::clean(' ') == '/%20/' );
			$this->assertTrue( \arc\path::clean('/#/') == '/%23/');
			$this->assertTrue( \arc\path::clean('/an a/', function( $filename ) {
				return str_replace( 'a','', $filename );
			}) == '/n /');
		}
	}
