<?php

	namespace arc\xml;

	class Preamble implements NodeInterface {
		public $encoding = 'utf-8';
		public $version  = '1.0';
		public $standalone = false;
		public $document = null;

		public function __construct( $xmlEncoding = false, $xmlVersion = '1.0', $xmlStandalone = false, $document) {
			$this->encoding = $xmlEncoding;
			$this->version = $xmlVersion;
			$this->standalone = $xmlStandalone;
			$this->document = $document;
		}

		public function __toString() {
			return $this->toString();
		}

		public function toString() {
			$result = '<?xml version="'.$this->version.'"';
			if ( $this->encoding ) {
				$result .= ' encoding="'.$this->encoding.'"';
			}
			if ( $this->standalone ) {
				$result .= ' standalone="'.$this->standalone.'"';
			}
			$result .= " ?>";
			return $result;
		}

		public function __get( $name ) {
			switch( $name ) {
				case 'nextSibling':
					if ( $this->document ) {
						return $this->document[1];
					}
				break;
			}
		}

	}

