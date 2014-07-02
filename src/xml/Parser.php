<?php

namespace arc\xml;

class Parser
{

    public $namespaces = array();

    public function __construct( $options = array() )
    {
        $optionList = array( 'namespaces' );
        foreach( $options as $option => $optionValue) {
            if (in_array( $option, $optionList )) {
                $this->{$option} = $optionValue;
            }
        }
    }

    public function parse( $xml, $encoding = null )
    {
        if (!$xml) {
            return Proxy( null );
        }
        if ($xml instanceof Proxy) { // already parsed
            return $xml;
        }
        $xml = (string) $xml;
        try {
            return $this->parseFull( $xml, $encoding );
        } catch( \arc\Exception $e) {
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
        $root .= '>';
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
            return new Proxy( simplexml_import_dom( $dom ), $this );
        }
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors( $prevErrorSetting );
        $message = 'Incorrect xml passed.';
        foreach ($errors as $error) {
            $message .= "\nline: ".$error->line."; column: ".$error->column."; ".$error->message;
        }
        throw new \arc\Exception( $message, \arc\exceptions::ILLEGAL_ARGUMENT );
    }
}
