<?php

	namespace arc;

	class template extends Pluggable {

		public static function substitute( $text = null, $arguments = null ) {
			if ( !isset( $text ) ) {
				return new template\SubstitutionEngine( $arguments );
			} else {
				$engine = new template\SubstitutionEngine();
				return $engine->render( $text, $arguments );
			}
		}

	}

