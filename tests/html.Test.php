<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 
class TestHTML extends PHPUnit_Framework_TestCase 
{

	var $html1 = '<html>
	<head>
		<title>Example</title>
	</head>
	<body>
		<h1>Example Title</h1>
		<hr>
		<p>A paragraph</p>
	</body>
</html>
';

	function testHTMLBasics() 
	{
		$doctype = \arc\html::doctype();
		$this->assertEquals( (string) $doctype, '<!doctype html>' );
		$comment = \arc\html::comment('A comment');
		$this->assertEquals( (string) $comment, '<!-- A comment -->' );
	}

	function testHTMLWriter() 
	{
		$html = \arc\html::ul( [ 'class' => 'menu' ],
			\arc\html::li('menu 1')
			->li('menu 2')
		);
		$this->assertEquals( ''.$html, "<ul class=\"menu\"><li>menu 1</li><li>menu 2</li></ul>" );
	}

	function testHTMLParsing() 
	{
		$html = \arc\html::parse( $this->html1 );
		$error = null;
		$htmlString = ''.$html;
		$html2 = \arc\html::parse( $htmlString );
		$this->assertEquals( $html->head->title, '<title>Example</title>' );
		$this->assertTrue( $html->head->title->nodeValue == 'Example' );
		$this->assertEquals( $html->head->title.'', $html2->head->title.'' );
		$this->assertTrue( $html->head->title->nodeValue == 'Example' );
	}

	function testHTMLFind() 
	{
		$html = \arc\html::parse( $this->html1 );
		$title = $html->find('head title')[0];
		$this->assertEquals( $title->nodeValue, 'Example' );
	}



/*
	function testXMLElement() {
		$el = \arc\xml::el( 'name', array( 'title' => 'a title' ) );
		$this->assertEquals( (string) $el, '<name title="a title"></name>' ); //asXML doesn't use immediate close tag
	}

	function testXMLNodes() {
		$nodes = \arc\xml::nodes( 
			\arc\xml::el( 'name', array( 'title' => 'a title' ) ),
			'<a>frop</a>',
			\arc\xml::nodes(
				'<!-- another string -->'
			)
		);
		$a = $nodes->a[0];
		$this->assertTrue( $a instanceof \arc\xml\Element );	// automatic parsing of xml strings
		$this->assertTrue( count( $nodes ) == 3 );			//
		$c = $nodes[2];										// nodelist is accessible as an array 
		$this->assertTrue( $c instanceof \arc\xml\Node );	// nested node lists are normalized
		$this->assertFalse( $c instanceof \arc\xml\NodeList );	// idem
		$this->assertTrue( $nodes->isDocumentFragment );
	}

	function testXMLGeneration() {
		$xml = \arc\xml::nodes(
			\arc\xml::preamble('1.0', 'utf-8', true),
			\arc\xml::el('rss', array( 'xmlns:xml' => 'http://www.w3.org/XML/1998/namespace', 'version' => '2.0' ),
				\arc\xml::el( 'channel',
					\arc\xml::el( 'title', 'Wikipedia'),
					\arc\xml::el( 'link', 'http://www.wikipedia.org' ),
					\arc\xml::el( 'description', 'This feed notifies you of new articles on Wikipedia.' )
				)
			)
		);

		$this->assertTrue( $xml instanceof \arc\xml\NodeList );
		$this->assertEquals( (string) $xml, $this->RSSXML );

	}

	function testXMLParsing() {
		$xml = \arc\xml::parse( $this->RSSXML );
		$error = null;

		$channelTitle = $xml->rss->channel->title->nodeValue;

		$xmlString = (string) $xml;

		try {
			$result = \arc\xml::parse( $this->incorrectXML );
		} catch( \arc\Exception $error ) {
		}
		$this->assertEquals( $channelTitle, 'Wikipedia' );
		$this->assertEquals( $xmlString, $this->RSSXML );
	
		$this->assertTrue( $error instanceof \arc\Exception );
	}

	function testNamespaceLookup() {
		$xml = \arc\xml::parse( $this->namespacedXML );
		$simplistic = $xml->testroot->{'foo:bar'};
		$xml->registerNamespace('test', 'http://www.example.com/');
		$correct = $xml->testroot->{'test:bar'};
		$this->assertTrue( $simplistic[0] instanceof \arc\xml\Element );
		$this->assertTrue( $simplistic[0]->nodeValue == 'something' );
		$this->assertTrue( $correct[0] instanceof \arc\xml\Element );
		$this->assertTrue( $correct[0]->nodeValue == 'something' );			
	}

	function testDataValue() {
		$xml = arc\xml::parse('<?xml version="1.0" encoding="ISO-8859-1" ?>
<rss version="2.0"><channel>
<title>W3Schools Home Page</title><link>http://www.w3schools.com</link>
<description>Free web building tutorials</description>
<item><title>RSS Tutorial</title><link>http://www.w3schools.com/rss</link><description>New RSS tutorial on W3Schools</description></item>
<item><title>XML Tutorial</title><link>http://www.w3schools.com/xml</link><description>New XML tutorial on W3Schools</description></item>
</channel></rss> ');
		$y = (int) $xml->el->nodeValue;
		$this->assertTrue( is_int ($y) );
	}

	function testNamespaceCorrection() {
		$xml = \arc\xml::parse( $this->namespacedXML );
		$ns = $xml->testroot[0]->lookupNamespace('http://www.example.com/','bar');
		\arc\xml::registerNamespace('test', 'http://www.example.com/');
		$ns2 = $xml->testroot[0]->lookupNamespace('http://www.example.com/','bar');
		$xml->testroot[0]->appendChild( \arc\xml::el( 'test:bar', array( 'test:frop' => 'frip' ), 'test' ) );
		$xml->testroot[0]->appendChild('<test:bar test:frop="frup">test</test:bar>');
		$bars = $xml->testroot[0]->{'test:bar'};
		$allbars = $xml->testroot[0]->{'*:bar'};
		$this->assertTrue( $ns == $ns2 && $ns2 == 'foo' );
		$this->assertTrue( count( $bars ) == 3 );
		$this->assertTrue( count( $allbars ) == 5 );
		$this->assertTrue( $bars[1]->tagName == 'foo:bar' );
	}
*/
	
}
