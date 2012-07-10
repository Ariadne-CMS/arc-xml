<?php

	namespace arc\template;

	interface PartialTemplateInterface {

		public function render( $arguments = array() );

		public function show( );

		public function clean();

	}
?>