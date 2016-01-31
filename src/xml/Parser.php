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
 * This class implements a XML parser based on DOMDocument->loadXML()
 * But it returns a Proxy for both SimpleXMLElement and DOMElement.
 * It also allows parsing of partial XML content.
 */
class Parser
{

    /**
     * A list of namespaces to use when importing partial xml
     * @var string[] $namespaces
     */
    public $namespaces = array();

    /**
     * @param array $options Allows you to set the namespaces property immediately
     */
    public function __construct( $options = array() )
    {
        $optionList = array( 'namespaces' );
        foreach( $options as $option => $optionValue) {
            if (in_array( $option, $optionList )) {
                $this->{$option} = $optionValue;
            }
        }
    }

    /**
     * Parses an XML string and returns a Proxy for it.
     * @param string|Proxy|null $xml
     * @param string $encoding The character set to use, defaults to UTF-8
     * @return Proxy
     */
    public function parse( $xml=null, $encoding = null )
    {
        if (!$xml) {
            return Proxy( null );
        }
        if ($xml instanceof Proxy) { // already parsed
            return $xml->cloneNode();
        }
        $xml = (string) $xml;
        try {
            return $this->parseFull( $xml, $encoding );
        } catch( \arc\UnknownError $e) {
            return $this->parsePartial( $xml, $encoding );
        }
    }

    private function parsePartial( $xml, $encoding )
    {
        // add a known (single) root element with all declared namespaces
        // libxml will barf on multiple root elements
        // and it will silently drop namespace prefixes not defined in the document
        $root = '<arcxmlroot';
        foreach ($this->namespaces as $name => $uri) {
            if ($name === 0) {
                $root .= ' xmlns="';
            } else {
                $root .= ' xmlns:'.$name.'="';
            }
            $root .= htmlspecialchars( $uri ) . '"';
        }
        $root  .= '>';
        $result = $this->parseFull( $root.$xml.'</arcxmlroot>', $encoding );
        $result = $result->firstChild->childNodes;
        return $result;
    }

    private function parseFull( $xml, $encoding = null )
    {
        $dom = new \DomDocument();
        if ($encoding) {
            $xml = '<?xml encoding="' . $encoding . '">' . $xml;
        }
        libxml_disable_entity_loader(); // prevents XXE attacks
        $prevErrorSetting = libxml_use_internal_errors(true);
        if ($dom->loadXML( $xml )) {
            if ($encoding) {
                foreach( $dom->childNodes as $item) {
                    if ($item->nodeType == XML_PI_NODE) {
                        $dom->removeChild( $item );
                        break;
                    }
                }
                $dom->encoding = $encoding;
            }
	        libxml_use_internal_errors( $prevErrorSetting );
            return new Proxy( simplexml_import_dom( $dom ), $this );
        }
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors( $prevErrorSetting );
        $message = 'Incorrect xml passed.';
        foreach ($errors as $error) {
            $message .= '\nline: '.$error->line.'; column: '.$error->column.'; '.$error->message;
        }
        throw new \arc\UnknownError( $message, \arc\exceptions::ILLEGAL_ARGUMENT );
    }
}
