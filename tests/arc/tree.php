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
	 
	class TestTree extends UnitTestCase {

		function testExpand() {
			$collapsedTree = array(
				'/a/b/c/' => 'Een c',
				'/a/' => 'Een a',
				'/d/e/' => 'Een e'
			);

			$expandedTree = \arc\tree::expand( $collapsedTree );
			//var_dump($expandedTree);
			$recollapsedTree = \arc\tree::collapse( $expandedTree );
			//var_dump($recollapsedTree);
			$this->assertTrue( $collapsedTree == $recollapsedTree );
			//not a requirement: $this->assertFalse( $collapsedTree === $recollapsedTree );
		}

		function testRecurse() {
			$node = \arc\tree::expand();
			$root = $node;
			for ( $i = 0; $i < 1000; $i ++ ) {
				$node = $node->appendChild($i, $i);
			}
			$arr = \arc\tree::collapse( $root );
		}


		function testAppend() {
			$tree = \arc\tree::expand();
			$tree->childNodes['foo'] = 'bar';
			$tree->cd('/foo/')->appendChild('test', 'a test');
			$collapsed = \arc\tree::collapse( $tree );
			$this->assertTrue( $collapsed == array(
				'/foo/' => 'bar',
				'/foo/test/' => 'a test'
			));
		}

		function testClone() {
			$tree = \arc\tree::expand();
			$tree->childNodes['foo'] = 'bar';
			$clone = clone $tree;
			$clone->childNodes['foo'] = 'foo';
			$this->assertTrue( $tree !== $clone );
			$this->assertTrue( $tree->childNodes !== $clone->childNodes );
			$foo1 = $tree->cd('/foo/');
			$foo2 = $clone->cd('/foo/');
			$this->assertTrue( $foo1 !== $foo2 );
			$this->assertTrue( $tree->childNodes['foo'] == 'bar' );
			$this->assertTrue( $clone->childNodes['foo'] == 'foo' );
		}
	}

