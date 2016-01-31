ARC: Ariadne Component Library 
==============================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-xml/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-xml/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-xml/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-xml/)
[![Latest Stable Version](https://poser.pugx.org/arc/xml/v/stable.svg)](https://packagist.org/packages/arc/xml)
[![Total Downloads](https://poser.pugx.org/arc/xml/downloads.svg)](https://packagist.org/packages/arc/xml)
[![Latest Unstable Version](https://poser.pugx.org/arc/xml/v/unstable.svg)](https://packagist.org/packages/arc/xml)
[![License](https://poser.pugx.org/arc/xml/license.svg)](https://packagist.org/packages/arc/xml)

arc/xml
=======

This component provides a unified xml parser and writer. The writer allows for readable and always correct xml in code, without using templates. The parser is a wrapper around both DOMDocument and SimpleXML. 

The parser and writer also work on fragments of XML. The parser also makes sure that the output is identical to the input.
When converting a node to a string, \arc\xml will return the full xml string, including tags. If you don't want that, you 
can always access the 'nodeValue' property to get the original SimpleXMLElement.

Finally the parser also adds the ability to use basic CSS selectors to find elements in the XML.

The problem with XML
====================

XML was supposed to be easy. A single way to share machine readable data of any kind. But the complexity has exploded. XSL, WSDL, SOAP, XML Schemas, RDF, the list goes on and on. Yet the basic XML standard is still fairly simple. But the tools are just insufficient (SimpleXML) or overly verbose and insistent on ceremony (DOMDocument). And thats before you start with namespaces.

It's no wonder people prefer to generate XML by just concatenating strings together. This results in approximately half the RSS feeds online being broken. Either blissfully inserting HTML, or encoding it but forgetting that HTML entities are generally not allowed in XML or just inserting characters that are outside the valid range or not UTF-8 without specifying a different encoding.

arc\xml is an attempt to cut through all the ceremony and boilerplate in DOMDocument and merge it with SimpleXML to make something better than either of them. It also provides an alternative to string concatenation and templates when writing XML, making sure that what you write is valid XML, always.

Improvements over SimpleXML
---------------------------

- parses and prints XML fragments, not just full documents
- input equals output, arc\xml fragments default to XML when printed
- supports simple css selectors with find()
- handles namespaced elements and attributes just like non-namespaced ones

Improvements over DOMDocument
-----------------------------

In addition to the improvements above:
- automatically imports nodes to the document when needed
- no more NodeLists, all lists are just arrays
- you get all the SimpleXML methods and properties for free as well

Parsing XML
==========

Examples
--------

For these examples we'll use the following XML

```xml
<rdf:RDF 
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
    xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel rdf:about="http://slashdot.org/">
        <title>Slashdot</title>
        <link>http://slashdot.org/</link>
        <description>News for nerds, stuff that matters</description>
        <dc:language>en-us</dc:language>
        <dc:date>2016-01-30T20:38:08+00:00</dc:date>
    </channel>
    <item rdf:about="http://hardware.slashdot.org/story/1757209/">
        <title>Drone Races To Be Broadcast To VR Headsets</title>
        <link>http://hardware.slashdot.org/story/1757209/</link>
    </item>
    <item rdf:about="http://it.slashdot.org/story/1720259/">
        <title>FTDI Driver Breaks Hardware Again</title>
        <link>http://it.slashdot.org/story/1720259/</link>
    </item>
</rdf:RDF>
```

###Getting the title
```php5
$xml = \arc\xml::parse( $xmlString );
$title = $xml->channel->title;
echo $title;
```

result:
```
<title>Slashdot</title>
```    

The parser returns the full XML element by default. If you just want the contents, you must be explicit:

```php5
$title = $xml->channel->title->nodeValue;
echo $title;
```

result:
```
Slashdot
```    

Instead of the default in SimpleXML, arc\xml must be explicitly told to get the value of the node using the `nodeValue` property.

###Setting the title
```php5
$xml->channel->title = 'Update title';
```

As you can see, there is no need to mention the nodeValue here, the name 'title' is enough to select the correct element. It would not make sense to turn the title into another tag entirely here. You can still use the `nodeValue` if you prefer though.

###Getting attributes
```php5
$about = $xml->channel['rdf:about'];
```

result
```
http://slashdot.org/
```

Just what you would expect, even though there is a namespace in there. When you use a namespace that the parser hasn't been told about before, it will simply look it up in the document and re-use it.

Since attributes aren't XML nodes, there is no nodeValue. Attributes are always returned as just a string.

###Setting attributes
```php5
$xml->channel['title-attribute'] = 'This is a title attribute'; 
```

This adds the `title-attribute` if it wasn't there before, or updates it if it was.

###Removing attributes
```php5
unset($xml->channel['title-attribute']);
```

###Searching the document
```php5
$items = $xml->find('item');
echo implode($items);
```

result:
```xml
    <item rdf:about="http://hardware.slashdot.org/story/1757209/">
        <title>Drone Races To Be Broadcast To VR Headsets</title>
        <link>http://hardware.slashdot.org/story/1757209/</link>
    </item>
    <item rdf:about="http://it.slashdot.org/story/1720259/">
        <title>FTDI Driver Breaks Hardware Again</title>
        <link>http://it.slashdot.org/story/1720259/</link>
    </item>
```

Again, you get the full XML of the result and it is just an array. (Its been joined here using `implode` for clarity).

The `find()` method accepts most CSS2.0 selectors. For now you can't enter more than one selector, so you can't select 'item, channel' for instance.
Either use the SimpleXML `xpath()` method or run multiple queries.

###Searching using namespaces
```php5
$xml->registerNamespace('dublincore','http://purl.org/dc/elements/1.1/');
$date = current($xml->find('dublincore|date));
echo $date;
```

result:
```xml
<dc:date>2016-01-30T20:38:08+00:00</dc:date>
```

Again, you get the full XML by default. But in addition, though you've used a namespace alias not known in the document ( `dublincore` ), `find()` returns the `<dc:date>` element for you. The alias is different, but the namespace is the same and that is what matters.

The find() method always returns an array, which may be empty. By using current() you get the first element found, or null if nothing was found.

###Supported CSS Selectors

The following CSS selectors are supported:

- `tag1 tag2`<br>
  This matches `tag2` which is a descendant of `tag1`.
- `tag1 > tag2`<br>
  This matches `tag2` which is a direct child of `tag1`.
- `tag:first-child`<br>
  This matches `tag` only if its the first child.
- `tag1 + tag2`<br>
  This matches `tag2` only if its immediately preceded by `tag1`.
- `tag1 ~ tag2`<br>
  This matches `tag2` only if it has a previous sibling `tag1`.
- `tag[attr]`<br>
  This matches `tag` if it has the attribute `attr`.
- `tag[attr="foo"]`<br>
  This matches `tag` if it has the attribute `attr` with the value `foo` in its value list.
- `tag#id`<br>
  This matches any `tag` with id `id`.
- `#id`<br>
  This matches any element with id `id`.
- `ns|tag`<br>
  This matches `ns:tag` or more generally `tag` in the namespace indicated by the alias `ns`


###DOMElement methods

In addition to SimpleXMLElement methods, you can also call any method that is available in DOMElement.

```php5
    $version = $xml->getAttributes('version');
    $title = $xml->getElementsByTagName('channel')[0]
        ->getElementsByTagName('title')[0];
```

###Parsing fragments

The arc\xml parser accepts partial XML content. It doesn't require a single root element. 

```php5
    $xmlString = <<< EOF
<item>
    <title>An item</title>
</item>
<item>
    <title>Another item</title>
</item>
EOF;
    $xml = \arc\xml::parse($xmlString);
    echo $xml;
```

result:
```xml
<item>
    <title>An item</title>
</item>
<item>
    <title>Another item</title>
</item>
```
