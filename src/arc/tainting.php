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

	class tainting extends Pluggable {

		public static function taint($value) {
			if ( !is_numeric($value) ) {
				if ( is_array($value) ) {
					array_walk_recursive( $value, array( self, 'taint' ) );
				} else if ( is_string($value) && $value ) { // empty strings don't need tainting
					$value = new tainting\Tainted($value);
				}
			}
			return $value;
		}

		public static function untaint($value, $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = null) {
			if ( $value instanceof tainting\Tainted ) {
				$value = filter_var($value->value, $filter, $flags);
			} else if ( is_array($value) ) {
				array_walk_recursive( $value, 'self::untaintArrayItem', array(
					'filter' => $filter,
					'flags' => $flags
				) );
			}
			return $value;
		}

		protected static function untaintArrayItem( &$value, $key, $options) {
			//FIXME: doublecheck that this works with array_walk_recursive with $value as reference
			$value = self::untaint( $value, $options['filter'], $options['flags'] );
		}

	}


?>