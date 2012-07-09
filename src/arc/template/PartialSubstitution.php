<?php

	namespace arc\template;

	class PartialSubstitution implements PartialTemplateInterface {
		
		private $defaults = array();
		private $text = '';
		
		public function __construct( $text, $defaultArguments ) {
			$this->defaults = $defaultArguments;
			$this->text = $text;
		}
	
		public function render( $arguments = array() ) {
			$engine = new SubstitutionEngine( $this->defaults );
			return $engine->render( $this->text, $arguments );
		}
		
		public function __toString( ) {
			return '' . $this->clean();
		}
		
		public function show() {
			echo '' . $this;
		}
		
		public function clean() {
			return preg_replace( '/([^\\]|\b)\{(\$)?([^}]+)\}/m', '$1', (string) $this->text );
		}
		
	}
	
?>