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
	 
	class TestGrants extends UnitTestCase {

		function testGrantsSetGet() {
			$testGrants = \arc\grants::on('test');
			$testGrants->set('read =add >edit >delete');
			$this->assertTrue( $testGrants->check('read') );
			$this->assertTrue( $testGrants->check('add') );
			$this->assertFalse( $testGrants->check('edit') );
			$this->assertFalse( $testGrants->check('foo') );
		}

		function testGrantsOnPath() {
			$config = \arc\config::getConfiguration();
			$grants = \arc\grants::cd('/test/');
			$testGrants = $grants->on('test');
			$this->assertTrue( $testGrants->check('read') );
			$this->assertFalse( $testGrants->check('add') );
			$this->assertTrue( $testGrants->check('edit') );
		}
	}
