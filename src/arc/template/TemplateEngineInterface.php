<?php

	namespace arc\template;

	interface TemplateEngineInterface {

		public function render( $template, $arguments = null );

		public function show( $template, $arguments = null );

	}
