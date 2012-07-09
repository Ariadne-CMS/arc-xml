<?php

	namespace ar\xml;

	class AttributeList extends \ArrayObject {
		protected $attributes = null;
		protected $parser = null;
		protected $element = null;

		public function __construct( $attributes, $parser, $element ) {
			$this->attributes = $attributes;
			$this->parser = $parser;
			$this->element = $element;
		}

		public function offsetGet( $offset ) {

		}

		public function offsetSet( $offset, $value ) {

		} 

		public function offsetExists( $offset ) {

		}

		public function offsetUnset( $offset ) {

		}

	}
