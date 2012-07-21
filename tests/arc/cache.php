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
	 
	class TestCache extends UnitTestCase {

		function testCacheCreateGet() {
			$testCache = \arc\cache::create('test');
			$testCache->set('test1', true);
			$result = $testCache->get( 'test1' );
			$this->assertTrue( $result );
			$testCache->remove('test1');
			$result = $testCache->get( 'test1' );
			$this->assertTrue( $result == null );
		}

		function testCacheCache() {
			$testCache = \arc\cache::create('test');
			$result = $testCache->cache('test2', function() {
				return 'Dit is een test';
			});
			$this->assertTrue( $result == 'Dit is een test' );
		}
	}