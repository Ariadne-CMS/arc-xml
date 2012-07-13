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
	 
	class TestArc extends UnitTestCase {

		function testPluggable() {

			try {
				\arc\arc::thisMethodDoesNotExist();
			} catch( \Exception $e ) {
				$this->assertTrue( $e instanceof \arc\Exception );
				$this->assertTrue( $e->getCode() == \arc\exceptions::OBJECT_NOT_FOUND );
			}

			try {
				\arc\arc::plugin( __DIR__.'/arc/arc_plugin_helper.php', 'arc_wrong_methodsearcher' );
			} catch( \Exception $e ) {
				$this->assertTrue( $e instanceof \arc\Exception );
				$this->assertTrue( $e->getCode() == \arc\exceptions::OBJECT_NOT_FOUND );				
			}

			try {
				\arc\arc::plugin( __DIR__.'/arc/arc_plugin_helper.php', 'arc_plugin_methodsearcher' );
				$this->assertTrue(true);
			} catch( \Exception $e ) {
				echo $e;
				$this->assertTrue(false); //should not throw an exception
			}

			$result = arc_plugin::arc_plugin_test();
			$this->assertTrue( $result );

			$result = \arc\arc::arc_plugin_test();
			$this->assertTrue( $result );
		}


	}
