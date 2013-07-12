<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the
	 * LICENSE
	 * file that was distributed with this source code.
	 */

	require_once( __DIR__.'/../bootstrap.php' );
	 
	class TestHash extends UnitTestCase {

		function testHashGet() {
			$hash = [
				'foo' => [
					'bar' => 'This is a bar'
				]
			];
			$result = \arc\hash::get( '/foo/bar/', $hash );
			$this->assertEqual( $result, $hash['foo']['bar'] );
			
			$result = \arc\hash::get( '/foo/baz/', $hash );
			$this->assertTrue( $result === null );
		}

		function testHashExists() {
			$hash = [
				'foo' => [
					'bar' => 'This is a bar'
				]
			];
			$result = \arc\hash::exists( '/foo/bar/', $hash );
			$this->assertTrue( $result );
			$result = \arc\hash::exists( '/foo/baz/', $hash );
			$this->assertFalse( $result );
		}

		function testHashCompile() {
			$path = '/foo/bar/0/';
			$result = \arc\hash::compileName( $path );
			$this->assertEqual( $result, 'foo[bar][0]' );
		}

		function testHashParse() {
			$name = 'foo[bar][0]';
			$result = \arc\hash::parseName( $name );
			$this->assertEqual( $result, '/foo/bar/0/' );
		}
		
		function testTree() {
			$hash = [
				'foo' => [
					'bar' => 'This is a bar'
				]
			];
			$node = \arc\hash::tree( $hash );
			$tree = \arc\tree::collapse( $node );
			$this->assertEqual( $tree, [ '/foo/bar/' => 'This is a bar' ] );
		}
		
	}
