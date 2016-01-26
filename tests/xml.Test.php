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
            ->li('menu 2 ',
                \arc\xml::em('emphasized')
            )
        );
        $this->assertEquals( "<ul class=\"menu\">\r\n\t<li>menu 1</li>\r\n\t<li>\r\n\t\tmenu 2 <em>emphasized</em>\r\n\t</li>\r\n</ul>", (string) $xml );
        $xml = \arc\xml::ul(
            \arc\xml::comment('A comment'),
            \arc\xml::cdata('Some < value >')
        );
        $this->assertEquals( "<ul>\r\n\t<!-- A comment --><![CDATA[Some < value >]]>\r\n</ul>", (string) $xml );
    }

    function testXMLWriterEscaping()
    {
        $xml = \arc\xml::item(
            [ 
                'class' => 'in"valid<stuff>',
                'inva"lid' => "this is \n just wrong"
            ],
            "Escape me\" < really &copy;".chr(8),
            \arc\xml::subitem( 
                \arc\xml::raw("<subsubitem>this should work</subsubsubitem>")
            )
        );
        $expectedValue = <<< EOF
<item class="in&quot;valid&lt;stuff&gt;" invalid="this is 
 just wrong">\r
\tEscape me" &lt; really &amp;copy;<subitem>\r
\t\t<subsubitem>this should work</subsubsubitem>\r
\t</subitem>\r
</item>
EOF;
        $this->assertEquals( $expectedValue, (string) $xml );
    }

    function testXMLParsing() 
    {
        $xml = \arc\xml::parse( $this->RSSXML );
        $error = null;
        $xmlString = ''.$xml;
        $this->assertEquals( $this->RSSXML, $xmlString );
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

    function testCSSSelectors()
    {
        $xmlString = \arc\xml::{'list'}(
            \arc\xml::item(['class' => 'first item', 'id' => 'special'], 'item1',
                \arc\xml::input(['type' => 'radio', 'checked' => 'checked' ], 'a radio')
            ),
            \arc\xml::item(['class' => 'item special-class'], 'item2',
                \arc\xml::item(['class' => 'item last', 'data' => 'extra data'], 'item3')
            )
        );
        $xml = \arc\xml::parse($xmlString);
        $selectors = [
            'list item' => ['item1','item2','item3'],
            'list > item' => ['item1','item2'],
            'item:first-child' => ['item1','item3'],
            'input:checked' => ['a radio'],
            'item + item' => ['item2'],
            'item ~ item' => ['item2'],
            'item[data]' => ['item3'],
            'item[data="extra data"]' => ['item3'],
            'item[data="extra"]' => ['item3'],
            'item#special' => ['item1'],
            '#special' => ['item1'],
            'item.first' => ['item1'],
            'item.last' => ['item3'],
            'item.item' => ['item1', 'item2', 'item3'],
            '.first' => ['item1'],
            '.last' => ['item3'],
            '.item' => ['item1', 'item2', 'item3'],
            'item.special-class' => ['item2'],
            'list > item.item' => ['item1','item2'],
            'list > item > item.last' => ['item3'],
            'list > item ~ item' => ['item2']
        ];

        foreach ( $selectors as $css => $expectedValues ) {
            $result = $xml->find($css);
            foreach ( $result as $index => $value ) {
                $result[$index] = (string) trim($value->nodeValue);
            }
            $this->assertEquals( $expectedValues, $result, 'selector: '.$css );
        }
    }

    function testXMLUpdates()
    {
        $xml = \arc\xml::parse( $this->RSSXML );
        $xml->channel->addChild('language','en-us');
        $language = $xml->channel->language->nodeValue;
        $this->assertEquals('en-us', (string) $language);
        $xml->channel->language = 'nl-NL';
        $this->assertEquals('nl-NL', (string) $xml->channel->language->nodeValue );
        $xml['version'] = '1.0';
        $this->assertEquals('1.0', $xml['version']);
        $category = \arc\xml::parse('<category>Encyclopedia</category>');
        $xml->channel->appendChild( $category );
        $cat = $xml->channel->category->nodeValue;
        $this->assertEquals('Encyclopedia', (string) $cat);
    }

    function testGetElemById()
    {
        $xmlString = '<list><item id="item1">item 1</item><item id="item2">item 2</item></list>';
        $xml = \arc\xml::parse($xmlString);
        $item1 = $xml->getElementById('item1');
        $this->assertEquals('item 1', (string) $item1->nodeValue );
    }

}
