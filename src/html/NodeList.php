<?php

namespace arc\html;

class NodeList extends \ArrayObject {
	use \arc\xml\NodeListTrait;

	protected function canHaveContent( $tagName ) {
		$cantHaveContent = [ 
			'area', 'base', 'basefont', 'br', 
			'col', 'frame', 'hr', 'img', 'input',
			'isindex', 'link', 'meta', 'param'
		];
		return !in_array( trim( strtolower( $tagName ) ), $cantHaveContent );
	}

	protected function element( $tagName, $attributes, $content ) {
		$tagName =  $this->writer->name( $tagName );
		$el = '<' . $tagName;
		$el .= $this->getAttributes( $attributes );
		if ( $this->canHaveContent( $tagName ) ) {
			$el .= '>' . self::indent( $this->writer->indent, $content );
			$el .= '</' . $tagName . '>';
		} else {
			$el .= '>';
		}
		return $el;
	}

}