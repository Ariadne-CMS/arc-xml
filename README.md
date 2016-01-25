ARC: Ariadne Component Library 
==============================

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-xml/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ariadne-CMS/arc-xml/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/arc/xml/v/stable.svg)](https://packagist.org/packages/arc/xml)
[![Total Downloads](https://poser.pugx.org/arc/xml/downloads.svg)](https://packagist.org/packages/arc/xml)
[![Latest Unstable Version](https://poser.pugx.org/arc/xml/v/unstable.svg)](https://packagist.org/packages/arc/xml)
[![License](https://poser.pugx.org/arc/xml/license.svg)](https://packagist.org/packages/arc/xml)

arc/xml
=======

This component provides a unified xml parser and writer. The writer allows for readable and correct xml in code, not using 
templates. The parser is a wrapper around both DOMDocument and SimpleXML. 

The parser and writer also work on fragments of XML. The parser also makes sure that the output is identical to the input.
When converting a node to a string, \arc\xml will return the full xml string, including tags. If you don't want that, you 
can always access the 'nodeValue' property to get the original SimpleXMLElement.

Finally the parser also adds the ability to use basic CSS selectors to find elements in the XML.

```php5
<?php
    use \arc\xml as x;
    $xmlString = 
        x::preamble()
        .x::rss(['version'=>'2.0'],
             x::channel(
                 x::title('Wikipedia'),
                 x::link('http://www.wikipedia.org'),
                 x::description('This feed notifies you of new articles on Wikipedia.')
             )
        );
```

```php5
    $xml = \arc\xml::parse($xmlString);
    $title = $xml->channel->title->nodeValue; // SimpleXMLElement 'Wikipedia'
    $titleTag = $xml->channel->title; // <title>Wikipedia</title>
```

CSS selectors
-------------

```php5
    $title = current($xml->find('title'));
```

The find() method always returns an array, which may be empty. By using current() you get the first element found, or null if nothing was found.

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

SimpleXML
---------

The parsed XML behaves almost identical to a SimpleXMLElement, with the exceptions noted above. So you can access attributes just like SimpleXMLElement allows:

```php5
    $version = $xml['version'];
    $version = $xml->attributes('version');
```

You can walk through the node tree:

```php5
    $title = $xml->channel->title;
```

Any method or property available in SimpleXMLElement is included in \arc\xml parsed data.

DOMElement
----------

In addition to SimpleXMLElement methods, you can also call any method that is available in DOMElement.

```php5
    $version = $xml->getAttributes('version');
    $title = $xml->getElementsByTagName('channel')[0]
        ->getElementsByTagName('title')[0];
```

But right now you cannot access the properties of DOMElement. This will be fixed soonish.

Parsing fragments
-----------------

The arc\xml parser also accepts partial XML content. It doesn't require a single root element. 

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
    $titles = $xml->find('title');
```

And when you convert the xml back to a string, it will still be a partial XML fragment.


Why use this instead of DOMDocument or SimpleXML?
-------------------------------------------------

arc\xml::parse has the following differences:

  - When converted to string, it returns the original XML, without additions you didn't make.
  - You can use it with partial XML fragments.
  - No need to remember calling importNode() before appendChild() or insertBefore()
  - No need to switch between SimpleXML and DOMDocument, because you need that one method only available in the other API.
  - When returning a list of elements, you always get a standard array, not a magic NodeList.

In addition the arc\xml writer is a simple way to generate valid and indented XML, with readable and self-validating code.

  - Filters all illegal characters from your XML
  - Automatically translate &, < and > characters to &amp;, &lt; and &gt;
  - Automatically translate " to &quot; in attributes

You can include raw unfiltered XML with the \arc\xml::raw() method
