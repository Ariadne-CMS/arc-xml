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

        function testSafeUrl() {
            $starturl = 'http://www.ariadne-cms.org/?frop=1';
            $url = \arc\url::safeUrl($starturl);
            $this->assertIsA($url, '\arc\url\Url');
            $this->assertEqual($url.'', $starturl);

            $starturl = 'http://www.ariadne-cms.org/?frop=1&frop=2';
            $url = \arc\url::safeUrl($starturl);
            $url->fragment = 'test123';
            $this->assertEqual($url.'', $starturl .'#test123');

            $starturl = 'http://www.ariadne-cms.org/view.html?some+thing';
            $url = \arc\url::safeUrl($starturl);
            $this->assertIsA($url, '\arc\url\Url');
            $this->assertEqual($url.'', $starturl);
            $this->assertEqual($url->query[0], 'some thing');

            $starturl = 'http://www.ariadne-cms.org/view.html?some%20thing';
            $url = \arc\url::safeUrl($starturl);
            $this->assertIsA($url, '\arc\url\Url');
            $this->assertEqual($url->query[0], 'some thing');
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

            $starturl = 'http://www.ariadne-cms.org/view.html?foo=some+thing';
            $url = \arc\url::url($starturl);
            $this->assertIsA($url, '\arc\url\Url');
            $this->assertEqual($url.'', $starturl);
            $this->assertEqual($url->query['foo'], 'some thing');

        } 

        function testParseAuthority() {
            $starturl = 'http://foo:bar@www.ariadne-cms.org:80/';
            $url = \arc\url::url($starturl);
            $this->assertIsA($url, '\arc\url\Url');
            $this->assertEqual($url.'', $starturl);
        }

        function testParseCommonURLS() {
            $commonUrls = [
                'ftp://ftp.is.co.za/rfc/rfc1808.txt',
                'http://www.ietf.org/rfc/rfc2396.txt',
                'ldap://[2001:db8::7]/c=GB?objectClass?one',
                'mailto:John.Doe@example.com',
                'news:comp.infosystems.www.servers.unix',
                'tel:+1-816-555-1212',
                'telnet://192.0.2.16:80/',
                'urn:oasis:names:specification:docbook:dtd:xml:4.1.2',
                '//google.com',
                '../../relative/',
                'file:///C:/'
            ];
            foreach ( $commonUrls as $sourceUrl ) {
                $url =\arc\url::safeUrl( $sourceUrl );
                $this->assertEqual( ''.$url, $sourceUrl );
            }
        }
        
    }
