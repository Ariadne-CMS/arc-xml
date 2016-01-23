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
 * This adds docblock information for DOMElement methods that are 
 * available on the \arc\xml\Proxy class.
 *
 * @method void addAttribute( string $name, string $value, string $namespace )
 * @method Proxy addChild( string $name, string $value, string $namespace )
 * @method string asXML( strinf $filename )
 * @method Proxy attributes( string $ns, bool $is_prefix )
 * @method Proxy children( string $ns, bool $is_prefix )
 * @method int count()
 * @method array getDocNamespaces( bool $recursive, bool $from_root )
 * @method string getName()
 * @method array getNamespaces( bool $recursive )
 * @method bool registerXPathNamespace( string $prefix, string $ns )
 * @method array spath( string $path )
 */
interface SimpleXMLElement {

}