<?php

namespace arc\html;

class Parser 
{
	public $options = [
		'libxml_options' => 0
	];

	public function __construct( $options = array() ) 
	{
		$optionList = [ 'libxml_options' ];
		foreach( $options as $option => $optionValue ) {
			if ( in_array( $option, $optionList ) ) {
				$this->{$option} = $optionValue;
			}
		}
	}

	public function parse( $html, $encoding = null ) 
	{
		if ( !$html ) {
			return Proxy( null );
		}
		if ( $html instanceof Proxy ) { // already parsed
			return $html;
		}
		$html = (string) $html;
		try {
			return $this->parseFull( $html, $encoding );
		} catch( \arc\Exception $e ) {
			return $this->parsePartial( $html, $encoding );
		}
	}

	private function parsePartial( $xml, $encoding ) 
	{
		$root = '<html>';
		$result = $this->parseFull( '<html>'.$xml.'</html>', $encoding );
		$result = $result->firstChild->childNodes;
		return $result;
	}

	private function parseFull( $html ) 
	{
		$dom = new \DomDocument();
		libxml_disable_entity_loader(); // prevents XXE attacks
		$prevErrorSetting = libxml_use_internal_errors(true);
		if ( $dom->loadHTML( $html, $this->options['libxml_options'] ) ) {
			libxml_use_internal_errors( $prevErrorSetting );
			return new \arc\html\Proxy( simplexml_import_dom( $dom ), $this );
		}	
		$errors = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors( $prevErrorSetting );
		$message = 'Incorrect html passed.';
		foreach ( $errors as $error ) {
			$message .= "\nline: ".$error->line."; column: ".$error->column."; ".$error->message;
		}
		throw new \arc\Exception( $message, \arc\exceptions::ILLEGAL_ARGUMENT );
	}

}