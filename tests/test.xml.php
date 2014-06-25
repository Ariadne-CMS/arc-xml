<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php' );
 
class TestXML extends UnitTestCase 
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
		$this->assertEqual( (string) $preamble, '<?xml version="1.0" ?>' );
		$this->assertEqual( (string) $cdata, '<![CDATA[Some " value]]>' );
		$comment = \arc\xml::comment('A comment');
		$this->assertEqual( (string) $comment, '<!-- A comment -->' );
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



/*
	function testXMLElement() {
		$el = \arc\xml::el( 'name', array( 'title' => 'a title' ) );
		$this->assertEqual( (string) $el, '<name title="a title"></name>' ); //asXML doesn't use immediate close tag
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
		$this->assertEqual( (string) $xml, $this->RSSXML );

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
		$this->assertEqual( $channelTitle, 'Wikipedia' );
		$this->assertEqual( $xmlString, $this->RSSXML );
	
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
