<?php

	/*
	 * This file is part of the Ariadne Component Library.
	 *
	 * (c) Muze <info@muze.nl>
	 *
	 * For the full copyright and license information, please view the LICENSE
	 * file that was distributed with this source code.
	 */

	namespace arc;

	class xml {
		
		static public function __callStatic( $name, $args ) {
			return call_user_func_array( [ self::writer(), $name ], $args );
		}

		static public function parse( $xml, $encoding = null ) {
			$P = new xml\Parser();
			return $p->parse( $xml, $encoding );
		}

	}
