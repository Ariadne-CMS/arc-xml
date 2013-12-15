<?php

namespace arc;

class lambda {

	public static function prototype( $properties ) {
		// do not ever use a single prototype for every other lambda\Prototype
		// it will allow evil stuff with state shared across everything
		return new lambda\Prototype( $properties );
	}

	/** 
	* Returns a function with the given arguments already entered or partially applied. 
	* @param callable $function The function to curry
	* @param mixed $argument,... unlimited Optional arguments to curry the function with
	* @return callable
	*/
	public static function partial( $callable, $partialArgs, $defaultArgs = [] ) {
		return function() use ( $callable, $partialArgs, $defaultArgs ) {
			return call_user_func_array( $callable, self::partialMerge( $partialArgs, func_get_args(), $defaultArgs ) );
		};
	}

	private static function partialMerge( $partialArgs, $addedArgs, $defaultArgs = [] ) {
		end( $partialArgs );
		$l = key( $partialArgs );
		for( $i=0; $i<=$l; $i++ ) {
			if ( !array_key_exists($i, $partialArgs) && count($addedArgs) ) {
				$partialArgs[ $i ] = array_shift( $addedArgs );
			}
		}
		if ( count($addedArgs) ) { // there are $addedArgs left, so there should be no 'holes' in $partialArgs
			$partialArgs =array_merge( $partialArgs, $addedArgs );
		} 
		// fill any 'holes' in $partialArgs with entries from $defaultArgs
		$result =  array_replace( $defaultArgs, $partialArgs );
		ksort($result);
		return $result;
	}

	/**
	* Returns a function with named arguments. The peppered function accepts one argument - a named array of values
	* @param callable $function The function or method to pepper
	* @param array $namedArgs Optional. The named arguments to pepper the function with, the order must be the order 
	*        in which the unpeppered function expects them. If not set, pepper will use Reflection to get them.
	* @return callable
	*/
	public static function pepper( callable $callable, $namedArgs=null) {
		if ( !is_array( $namedArgs ) ) {
			if ( !is_array( $callable ) ) {
				$ref = new ReflectionFunction( $callable );
			} else {
				$ref = new ReflectionMethod( $callable );
			}
			$namedArgs = [];
			foreach( $ref->getParameters() as $parameter ){
				$namedArgs[ $parameter->getName() ] = $parameter->getDefaultValue();
			}
		}
		return function( $otherArgs ) use ( $callable, $namedArgs ) {
			return call_user_func_array( $callable, array_values( array_merge( $namedArgs, $otherArgs ) ) );
		};
	}

	/**
	* Returns a method that will generate and call the given function only once and return its result for every call.
	* The first call generates the result. Each subsequent call simply returns that same result. This allows you
	* to create in-context singletons for any kind of object.
	* <code>
	*   $proto = \arc\lambda::prototype([
	*     'getSingleton' => \arc\lambda::singleton( function() {
	*       return new ComplexObject();
	*     })
	*   ]);
	* </code>
	* @param callable $f The function to generate the singleton.
	* @return mixed The singleton.
	*/
	public static function singleton( $f ) {
		return function() use ($f) {
			static $result;
			if ( null === $result ) {
				$result = $f();
			}
			return $result;		
		};
	}


}