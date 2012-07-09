<?php

	namespace ar\template;
		
	class SubstitutionEngine implements TemplateEngineInterface {
	
		private var $defaults = array();
		
		function __construct( $defaultArguments = array() ) {
			$this->defaults = $defaultArguments;
		}
	
		protected function getSubstitution( $match, $arguments ) {
			if ( $arguments instanceof \ar\KeyValueStoreInterface ) {
				$result = $arguments->getvar( $match );
			} else {
				$result = $arguments[ $match ];
			}
			if (!$result && $arguments !== $this->defaults ) {
				$result = $this->getSubstitution( $matches, $this->default );
			}
			if ( !$result ) {
				$result = '{$'.$match.'}';
			}
			return $result;
		}
	
		function render( $text, $arguments = null ) {
			$variableRE = '/([^\\]|\b)\{(\$)?([^}]+)\}/m';
			if ( !isset( $arguments ) ) {
				$arguments = $this->defaults;
			}
			$text = preg_replace_callback( $variableRE, function( $matches ) use $arguments {
				return $matches[1] . $this->getSubstitution( $matches[2], $arguments );
			}, (string) $text );
			return new PartialSubstitution( $text );
		}
		
		function decompile( $text, $arguments ) {
			$text = (string) $text;
			foreach( $arguments as $name => $value ) {
				if ( $value ) {
					$text = str_replace( $value, '{\$'.$name.'}', $text );
				}
			}
			if ( $arguments != $this->defaults ) {
				$text = $this->decompile( $text, $this->defaults );
			}
			return $text;
		}
		
		function show( $text, $arguments = null ) {
			echo $this->compile( $text, $arguments = null );
		}
		
	}
	
?>