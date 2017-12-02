<?php

/*
* Extendable class, that implement 'Singleton' design pattern through the inheritance chain.
* @private static [array] $instances - holds maximum one instance of any subclass.
* @public static [function] getInstance() - returns the only available instance of the subclass.
*/
class Singleton{

	//Array of objects from already instantiated subclasses.
    //WARNING! Larger count of subclasses could extend the array size!!!
	private static $_instance = array();

	//Preventing objects creation except ClassName::getInstance()
	private function __construct(){ }
	private function __clone(){ }
	private function __wakeup(){ }

	/*
	* Get the subclass instance available.
	* @local [object] $instance - instance of the current subclass.
	* @return [object] - the only subclass instance available.
	*/
	public static function get_instance(){

		// Creating an instance of the current subclass.
		$instance = new static();

		for( $a = 0; $a < count(self::$_instance); $a ++ ){

			// Checks if we have such an instance.
			if( get_class($instance) === get_class(self::$_instance[$a]) ){

				return self::$_instance[$a];
			}
		}
		
		// Adding new subclass instance in the array.
		array_push( self::$_instance, $instance );

		return $instance;
	}
}