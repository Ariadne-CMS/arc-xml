<?php

	namespace arc\xml;

	class NodeListIterator implements \Iterator {
		var $current = 0;

		public function __construct( $nodeList ) {
			$this->nodeList = $nodeList;
		}

		public function current() {
			return $this->nodeList[ $this->current ];
		}

		public function key() {
			return $this->current;
		}

		public function next() {
			++$this->current;
		}

		public function rewind() {
			$this->current = 0;
		}

		public function valid() {
			return isset( $this->nodeList[ $this->current] );
		}
	}

