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

		function testTemplate() {
			$template = 'Hello {{someone}}';
			$args = [ 'someone' => 'World!' ];
			$parsed = \arc\template::parse( $template, $args );
			$this->assertTrue( $parsed, 'Hello World!' );
		}
	}
