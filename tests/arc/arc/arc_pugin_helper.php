<?php

	class arc_plugin_methodsearcher extends \arc\Loader {

		public static function autoload( $className ) {
			if ( $className == 'arc_plugin' ) {
				include_once( __DIR__.'arc_plugin.php');
			}
		}

	}

