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
	 
	class TestUrl extends UnitTestCase {

		function testSaveUrl() {
				$starturl = 'http://www.ariadne-cms.org/?frop=1';
				$url = \arc\url::safeUrl($starturl);
				$this->assertIsA($url, '\arc\url\SafeUrl');
				$this->assertEqual($url.'', $starturl);

				$starturl = 'http://www.ariadne-cms.org/?frop=1&frop=2';
				$url = \arc\url::safeUrl($starturl);
				$url->fragment = 'test123';
				$this->assertEqual($url.'', $starturl .'#test123');
		}

		function testparseUrl() {
				$starturl = 'http://www.ariadne-cms.org/?frop=1';
				$url = \arc\url::Url($starturl);
				$this->assertIsA($url, '\arc\url\ParsedUrl');
				$this->assertEqual($url.'', $starturl);

				$starturl = 'http://www.ariadne-cms.org/?frop=1&frml=2';
				$url = \arc\url::Url($starturl);
				$url->fragment = 'test123';
				$this->assertEqual($url.'', $starturl .'#test123');
				var_dump($url);
		} 



		
	}
