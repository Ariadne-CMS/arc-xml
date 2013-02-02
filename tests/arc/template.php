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
	 
	class TestTemplate extends UnitTestCase {

		function testSimpleSubstitution() {
			$template = 'Hello {{someone}}';
			$args = [ 'someone' => 'World!' ];
			$parsed = \arc\template::parse( $template, $args );
			$this->assertTrue( $parsed === 'Hello World!' );
		}

		function testFunctionSubstitution() {
			$template = 'Hello {{someone}}';
			$args = [ 'someone' => function() { return 'World!'; } ];
			$parsed = \arc\template::parse( $template, $args );
			$this->assertTrue( $parsed === 'Hello World!' );
		}

		function testPartialSubstitution() {
			$template = 'Hello {{someone}} from {{somewhere}}';
			$args = [ 'someone' => 'World!' ];
			$parsed = \arc\template::parse( $template, $args );
			$this->assertTrue( $parsed === 'Hello World! from {{somewhere}}' );
		}

		function testClean() {
			$template = 'Hello {{someone}} from {{somewhere}}';
			$args = [ 'someone' => 'World!' ];
			$parsed = \arc\template::clean( \arc\template::parse( $template, $args ) );
			$this->assertTrue( $parsed === 'Hello World! from ' );			
		}
	}
