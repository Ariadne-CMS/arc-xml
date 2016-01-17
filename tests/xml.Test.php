<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 
class TestXML extends PHPUnit_Framework_TestCase 
{

	var $RSSXML = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>
<rss version="2.0">
        <channel>
                <title>Wikipedia</title>
                <link>http://www.wikipedia.org</link>
                <description>This feed notifies you of new articles on Wikipedia.</description>
        </channel>
</rss>
';
	var $incorrectXML = "<?xml standalone=\"false\"><rss></rss>";
	var $namespacedXML = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<testroot xmlns:foo=\"http://www.example.com/\">\n<foo:bar>something</foo:bar><bar>something else</bar><bar foo:attr=\"an attribute\">something else again</bar>\n</testroot>";

	function testXMLBasics() 
	{
		$preamble = \arc\xml::preamble();
		$cdata = \arc\xml::cdata('Some " value');
		$this->assertEquals( (string) $preamble, '<?xml version="1.0" ?>' );
		$this->assertEquals( (string) $cdata, '<![CDATA[Some " value]]>' );
		$comment = \arc\xml::comment('A comment');
		$this->assertEquals( (string) $comment, '<!-- A comment -->' );
	}

	function testXMLWriter() 
	{
		$xml = \arc\xml::ul( [ 'class' => 'menu' ],
			\arc\xml::li('menu 1')
			->li('menu 2')
		);
		$this->assertTrue( $xml == "<ul class=\"menu\"><li>menu 1</li><li>menu 2</li></ul>" );
	}

	function testXMLParsing() 
	{
		$xml = \arc\xml::parse( $this->RSSXML );
		$error = null;
		$xmlString = ''.$xml;
		$this->assertTrue( $xml == $this->RSSXML );
		$this->assertTrue( $xml->channel->title == '<title>Wikipedia</title>' );
		$this->assertTrue( $xml->channel->title->nodeValue == 'Wikipedia' );

		try {
				$result = \arc\xml::parse( $this->incorrectXML );
		} catch( \arc\Exception $error ) {
		}
		$this->assertTrue( $error instanceof \arc\Exception );
	}

	function testXMLFind() 
	{
		$xml = \arc\xml::parse( $this->RSSXML );
		$title = $xml->find('channel title')[0];
		$this->assertTrue( $title->nodeValue == 'Wikipedia' );
	}

	function testProxyForAttributes()
	{
		$xml = \arc\xml::parse( $this->RSSXML );
		$this->assertEquals( '2.0', $xml['version'] );
		$attributes = $xml->attributes();
		$this->assertEquals( '2.0', $attributes['version']);
		$version = $xml->getAttribute('version');
		$this->assertEquals( '2.0', $version );
	}

	function testProxyForMethods()
	{
		$xml = \arc\xml::parse( $this->RSSXML );
						
	}
}
