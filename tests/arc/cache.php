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
	 
	class takesTooLong {

		function __construct( $output ) {
			$this->output = $output;
		}

		function waitForIt() {
			sleep(1);
			return $this->output;
		}

		function sub($arg) {
			return new takesTooLong( $arg );
		}
	}

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

		function testPath() {
			$testCache = \arc\cache::create('test');
			$testCache->cd('foo')->set('bar', 'ok');
			$subCache = $testCache->cd('foo');
			$result = $subCache->get('bar');
			$this->assertTrue( $result == 'ok');
		}

		function testProxy() {
			\arc\cache::purge();
			$testProxy = \arc\cache::proxy( new takesTooLong( true ) );
			$start = microtime(true);
			$result = $testProxy->waitForIt();
			$middle = microtime(true);
			$result2 = $testProxy->waitForIt();
			$end = microtime(true);
			$this->assertTrue( $result === $result2 );
			$this->assertTrue( $result );
			$this->assertTrue( ($end-$middle) < ($middle-$start) );

			$proxy2 = $testProxy->sub( false );
			$start = microtime(true);
			$result = $proxy2->waitForIt();
			$middle = microtime(true);
			$result2 = $proxy2->waitForIt();
			$end = microtime(true);
			$this->assertTrue( $result === $result2 );
			$this->assertFalse( $result );
			$this->assertTrue( ($end-$middle) < ($middle-$start) );			
		}
	}