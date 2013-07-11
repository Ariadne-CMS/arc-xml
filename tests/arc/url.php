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
				$this->assertIsA($url, '\arc\url\Url');
				$this->assertEqual($url.'', $starturl);

				$starturl = 'http://www.ariadne-cms.org/?frop=1&frop=2';
				$url = \arc\url::safeUrl($starturl);
				$url->fragment = 'test123';
				$this->assertEqual($url.'', $starturl .'#test123');
				
	            $startul = 'http://www.ariadne-cms.org/view.html?some+thing';
				$url = \arc\url::safeUrl($starturl);
				$this->assertIsA($url, '\arc\url\Url');
				$this->assertEqual($url.'', $starturl);
				$this->assertEqual($url->query[0], array('some thing'));

	            $startul = 'http://www.ariadne-cms.org/view.html?some%20thing';
				$url = \arc\url::safeUrl($starturl);
				$this->assertIsA($url, '\arc\url\Url');
				$this->assertEqual($url->query[0], array('some thing'));
		}

		function testparseUrl() {
				$starturl = 'http://www.ariadne-cms.org/?frop=1';
				$url = \arc\url::url($starturl);
				$this->assertIsA($url, '\arc\url\Url');
				$this->assertEqual($url.'', $starturl);

				$starturl = 'http://www.ariadne-cms.org/?frop=1&frml=2';
				$url = \arc\url::url($starturl);
				$url->fragment = 'test123';
				$this->assertEqual($url.'', $starturl .'#test123');

	            $startul = 'http://www.ariadne-cms.org/view.html?foo=some+thing';
				$url = \arc\url::url($starturl);
				$this->assertIsA($url, '\arc\url\Url');
				$this->assertEqual($url.'', $starturl);
				$this->assertEqual($url->query['foo'], array('some thing'));

		} 

        function testParseAuthority() {
            $starturl = 'http://foo:bar@www.ariadne-cms.org:80/';
            $url = \arc\url::url($starturl);
            $this->assertIsA($url, '\arc\url\Url');
            $this->assertEqual($url.'', $starturl);
        }
		
	}
