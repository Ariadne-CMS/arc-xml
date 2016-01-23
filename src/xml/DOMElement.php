<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc\xml;

/**
 * This adds docblock information for DOMElement methods and properties
 * that are available on the \arc\xml\Proxy class.
 *
 * @property string $tagName
 * @property int $nodeType
 * @property Proxy $parentNode
 * @property Proxy $firstChild
 * @property Proxy $lastChild
 * @property Proxy $previousSibling
 * @property Proxy $nextSibling
 * @property \DOMDocument $ownerDocument
 * @property string $namespaceURI
 * @property string $prefix
 * @property string $localName
 * @property string $baseURI
 * @property string $textContent
 * 
 * @method string getAttribute( string $name )
 * @method string getAttributeNS( string $namespaceURI, string $localName )
 * @method array getElementsByTagName( string $name )
 * @method array getElementsByTagNameNS( string $namespaceURI, string $localName)
 * @method bool hasAttribute( string $name )
 * @method bool hasAttributeNS( string $namespaceURI, string $localname )
 * @method bool removeAttribute( string $name )
 * @method bool removeAttributeNS( string $namespaceURI, string $localName )
 * @method DOMAttr setAttribute( string $name, string $value )
 * @method void setAttributeNS( string $namespaceURI, string $qualifiedName, string $value )
 * @method void setIdAttribute( string $name, bool $isId )
 * @method void setIdAttributeNS( string $namespaceURI, string $localName, bool $isId )
 * @method Proxy appendChild( Proxy $child )
 * @method Proxy cloneNode( bool $deep )
 * @method int getLineNo()
 * @method string getNodePath()
 * @method bool hasAttributes()
 * @method bool hasChildNodes()
 * @method Proxy insertBefore( Proxy $newnode, Proxy $refnode )
 * @method bool isDefaultNamespace( string $namespaceURI )
 * @method bool isSameNode( Proxy $node )
 * @method bool isSupported( string $feature, string $version )
 * @method string lookupNamespaceURI( string $prefix )
 * @method string lookupPrefix( string $namespaceURI )
 * @method void normalize()
 * @method Proxy removeChild( Proxy $oldnode )
 * @method Proxy replaceChild( Proxy $newnode, Proxy $oldnode )
 */
interface DOMElement {

}