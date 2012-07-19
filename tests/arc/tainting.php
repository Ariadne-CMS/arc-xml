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
	 
	class TestTainting extends UnitTestCase {

		function testTaint() {
			$string = 'A String';
			$string = \arc\tainting::taint( $string );
			$this->assertTrue( $string instanceof \arc\tainting\Tainted );
			$result = (string) $string;
			$this->assertTrue( $result == 'A String' );
			$string = \arc\tainting::untaint( $string );
			$this->assertTrue( $string === 'A String' );
		}

		function testHTMLTaint() {
			$string = 'String <b>with <i>embedded</i> HTML</b>';
			$string = \arc\tainting::taint( $string );
			$this->assertTrue( $string instanceof \arc\tainting\Tainted );
			$result = (string) $string;
			$this->assertTrue( $result == 'String &#60;b&#62;with &#60;i&#62;embedded&#60;/i&#62; HTML&#60;/b&#62;');
			$string = \arc\tainting::untaint( $string, FILTER_UNSAFE_RAW );
			$this->assertTrue( $string === 'String <b>with <i>embedded</i> HTML</b>' );
		}
	}