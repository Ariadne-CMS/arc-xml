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
			$config = \arc\config::getConfiguration();
			$config->configure('a.b', true);
			$result = $config->acquire('a.b');
			$this->assertTrue( $result === true );
		}

		function testConfigureAcquireByPath() {
			\arc\config::configure('a.b', true);
			$result = \arc\config::acquire('a.b');
			$this->assertTrue( $result );
			$result = \arc\config::cd('/test/')->acquire('a.b');
			$this->assertTrue( $result );
			\arc\config::cd('/test/')->configure('a.b', false );
			$result = \arc\config::cd('/test/')->acquire('a.b');
			$this->assertFalse( $result );
			$result = \arc\config::cd('/test/child/')->acquire('a.b');
			$this->assertFalse( $result );	
		}

		function testConfigurationMerge() {
			\arc\config::cd('/test/child/')->configure('a.c', 'test');
			$result = \arc\config::cd('/test/child/')->acquire('a');
			$this->assertFalse( $result['b'] );
			$this->assertTrue( $result['c'] == 'test' );
		}


	}