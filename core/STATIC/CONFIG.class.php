<?php

/*
* Class CONFIG selecting config information
* public static [function] get - returns path value from config
* public static [function] set - sets config's value
* protected static [function] _exist - check if path exist in the config
* protected static [function] managePath - convert string to array and viceversa, using separator
*/
class CONFIG{
	
	/*
	* Get config path value
	* @param [array] $config - configuration to search in
	* @param [string] $path - path to search
	* @returns [void] if path exists, return path value
	*/
	public static function get( $config, $path ){

	    if( $path === NULL || $path === "" ) return $config;
		
		// If there is such a path in the config
		if( TRUE === CONFIG::_exist( $path, $config ) ){
			
			// Converting the path to array of steps
			$steps = CONFIG::managePath( $path, 0 );
			
			foreach( $steps as $key => $val ){
				
				// Go step deeper
				$config = $config[ $val ];
			}
			// Returns finded value in the end of the path
			return $config;
		}
	}
	
	/*
	* Sets config path value
	* @param [array] $config - CONFIG to search in
	* @param [string] $path - PATH to specify the target
	* @param [void] $value - VALUE to set in the CONFIG
	* @returns [array] returns the modifyed CONFIG
	*/
	public static function set( $config, $path, $value ){
		
		// Converting the path to array of steps
		$steps = CONFIG::managePath( $path, 0 );
		
		// IF CONFIG is INVALID
		if( gettype( array() ) !== gettype( $config ) ) return FALSE;
		
		// IF there are NO STEPS OR EMPTY STEP exists
		if( gettype( array() ) !== gettype( $steps ) ) return FALSE;
		
		// Holder of everi CONFIG level through the PATH
		$temp = array();
		
		$b = 0;
		
		// For every PATH steps backward
		for( $a = count($steps) - 1; $a >= 0; $a -- ){
			
			// If this is the last STEP in the PATH
			if( $a === count($steps) - 1 ){
				
				// Sets the new VALUE in the end of the PATH
				array_push( $temp, $value );
				
			}else{// If somewhere before the end of the PATH
			
				// If there is such a PATH in the CONFIG
				if( TRUE === CONFIG::_exist( $path, $config ) ){
					
					// Saves the CONFIG value on the current PATH step
					array_push( $temp, CONFIG::get( $config, $path ) );
					
				}else{
					// Saves the CONFIG value on the current PATH step
					array_push( $temp, array( $steps[ $a + 1 ] => $temp[ $b - 1 ] ) );
				}
			}
			
			$b ++;
			
			// Convert the PATH string to array
			$path_arr = CONFIG::managePath( $path, 0 );
			
			// Remove the last step from the PATH
			array_pop( $path_arr );
			
			// Convert the PATH array to string
			$path = CONFIG::managePath( $path_arr, 1 );
		}
		
		// Get the config for base of the PATH
		array_push( $temp, $config );
		
		// The llast PATH step numver
		$step_num = count($steps) - 1;
		
		// For every CONFIG level
		foreach( $temp as $key => $val ){
			
			// If its not the end of the PATH
			if( $key > 0 ){
				
				// IF temp STEP not ARRAY
				if( gettype( array() ) !== gettype( $temp[ $key ] ) ) $temp[ $key ] = array();
				
				// Update the CONFIG level with the canges from the last CONFIG level
				$temp[ $key ][ $steps[ $step_num ] ] = $temp[ $key - 1 ];
				
				// Moving from the end of the PATH to the CONFIG
				$step_num --;
			}
		}
		// Returns the updated CONFIG
		return $temp[ count( $temp ) - 1 ];
	}
	
	/*
	* Checks if a path eists
	* @param [string] $path - path to search
	* @param [array] $config - configuration to search in
	* @returns [boolean] TRUE if successful, FALSE otherwise
	*/
	public static function _exist( $path, $config ){
		
		// Get config levels
		$step = CONFIG::managePath( $path, 0 );
		
		// IF there are NO STEPS OR EMPTY STEP exists
		if( gettype( array() ) !== gettype( $step ) ) return FALSE;
		
		foreach( $step as $key => $val ){
			
			// No more levels
			if( gettype( array() ) !== gettype( $config ) ) return FALSE;
			
			// No next step
			if( array_key_exists( $step[ $key ], $config ) === FALSE ) return FALSE;
			
			// Go deeper
			$config = $config[ $step[ $key ] ];
		}
		return TRUE;
	}
	
	/*
	* Converts the PATH from/to array/string
	* @param [string] $path - path to search
	* @param [array] $mode - datatype TO datatype
	* @returns [void] the converted PATH
	*/
	public static function managePath( $path, $mode, $sep = "/" ){
		
		// Validate the separator
		if( gettype( "" ) !== gettype( $sep ) && gettype( 1 ) !== gettype( $sep ) ) return;
		
		switch( $mode ){
			
			case 0:// string TO array
				
				// IF the PATH is a NUMBER
				if( gettype( 1 ) === gettype( $path ) ) return array( $path );
				
				// Check it is a STRING
				if( gettype( "" ) === gettype( $path ) ){
					
					// Convert to ARRAY
					$result = explode( $sep, $path );
					
					// IF there are NO STEPS OR EMPTY STEP exists
					if( gettype( array() ) !== gettype( $result ) || in_array( "", $result ) === TRUE ) return;
					
					return $result;
				}
				
			case 1:// array TO string
				
				// Check it is a ARRAY
				if( gettype( array() ) !== gettype( $path ) ) return;
				
				// Future STRING of the PATH
				$result = "";
				
				// For every PATH step
				foreach( $path as $key => $val ){
					
					// Add the step to the PATH string
					$result .= $val;
					
					// If it is not the last PATH step, add a separator
					if( $key < count( $path ) - 1 ) $result .= $sep;
				}
				return $result;
				
			default:
				return;
		}
	}
}