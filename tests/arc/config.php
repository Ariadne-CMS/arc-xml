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
	 
	class TestConfig extends UnitTestCase {

		function testContextLessConfigureAcquire() {
			$config = new \arc\config\Configuration();
			$config->configure('a.b', true);
			$result = $config->acquire('a.b');
			$this->assertTrue( $result );
		}

		function testConfigureAcquireByPath() {
			\arc\config::configure('a.b', true);
			$result = \arc\config::acquire('a.b');
			$this->assertTrue( $result );
			$result = \arc\config::get('/test/')->acquire('a.b');
			$this->assertTrue( $result );
			\arc\config::get('/test/')->configure('a.b', false );
			$result = \arc\config::get('/test/')->acquire('a.b');
			$this->assertFalse( $result );
			$result = \arc\config::get('/test/child/')->acquire('a.b');
			$this->assertFalse( $result );	
		}

		function testConfigurationMerge() {
			\arc\config::get('/test/child/')->configure('a.c', 'test');
			$result = \arc\config::get('/test/child/')->acquire('a');
			$this->assertFalse( $result['b'] );
			$this->assertTrue( $result['c'] == 'test' );
			//$c = \arc\config::getConfiguration();
			//$c->debug();
		}

	}