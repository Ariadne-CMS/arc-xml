<?php

namespace arc\lambda;

/**
 * This class allows you to create throw-away objects with methods and properties. It is meant to be used
 * as a way to create rendering objects for a certain data set. e.g.
 * <code>
 * $view = \arc\lambda::prototype( [
 *		'menu' => function( $children ) {
 *			return \arc\html::ul(['class' => 'menu'], array_map( $this->menuitem, (array) $children ) );
 *		},
 *		'menuitem' => function( $input ) {
 *			return \arc\html::li( $this->menulink( $input ), ( isset( $input['children'] ) ? $this->menu( $input['children'] ) : null ) );
 *		},
 *		'menulink' => function( $input ) {
 *			return \arc\html::a( [ 'href' => $input['url'] ], $input['name'] );
 *		}
 * ] );
 * echo $view->menu( $menulist );
 * </code>
 */
final class Prototype {
	
	/**
	* @var Object prototype Readonly reference to a prototype object. Can only be set in the constructor.
	*/
	private $prototype = null;

	public function hasOwnProperty( $name ) {
		return isset( $this->{$name} );
	}

	public function extend( $properties ) {
		$properties['prototype'] = $this;
		$descendant = new static($properties);
		return $descendant;
	}

	public function hasPrototype( $object ) {
		if ( !$this->prototype ) {
			return false;
		}
		if ( $this->prototype === $object ) {
			return true;
		}
		return $this->prototype->hasPrototype( $object );
	}

	public function __construct( $properties ) {
		foreach( $properties as $property => $value ) {
			if ( !is_numeric( $property ) ) {
				$this->{$property} = $this->_bind( $value );
			}
		}
	}

	public function __call( $name, $args ) {
		if ( isset( $this->{$name} ) && is_callable( $this->{$name} ) ) {
			return call_user_func_array( $this->{$name}, $args );
		} else if ( is_object( $this->prototype) ) {
			$method = $this->_bind( $this->prototype->{$name} );
			if ( is_callable( $method ) ) {
				return call_user_func_array( $method, $args );
			}
		}
		throw new \arc\ExceptionMethodNotFound( $name.' is not a method on this Object', \arc\exceptions::OBJECT_NOT_FOUND );
	}

	public function __get( $name ) {
		switch ( $name ) {

			case 'prototype':
				return $this->prototype;
			break;

			case 'properties':
				$getLocalProperties = function($o) { return get_object_vars($o); }; // get public properties only, so use closure to escape local scope.
				return ( is_object( $this->prototype ) 
						? array_merge( $this->prototype->properties, $getLocalProperties( $this ) ) 
						: $getLocalProperties( $this ) );
			break;

			default:
				if ( is_object( $this->prototype ) ) {
					return $this->_bind( $this->prototype->{$name} );
				} else {
					return null;
				}
			break;
		}
	}

	public function __isset( $name ) {
		return ( is_object( $this->prototype ) && isset( $this->prototype->{$name} ) );
	}

	public function __destruct() {
		return $this->_tryToCall( $this->destruct );
	}

	public function __serialize(){
		return $this->_tryToCall( $this->serialize );
	}

	public function __toString() {
		return $this->_tryToCall( $this->toString );
	}

	public function __clone() {
		foreach( get_object_vars( $this ) as $property ) {
			$this->{$property} = $this->_bind( $property );
		}
		$this->_tryToCall( $this->clone );
	}

	private function _bind( $property ) {
		if ( is_callable( $property ) ) {
			// make sure any internal $this references point to this object and not the prototype or undefined
			return \Closure::bind( $property, $this );
		} else {
			return $property;
		}			
	}

	private function _tryToCall( $f ) {
		if ( is_callable( $f ) ) {
			return $f();
		}
	}

}