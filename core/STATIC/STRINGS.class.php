<?php

require_once __DIR__ . '/CONFIG.class.php';
require_once __DIR__ . '/ARRAY.class.php';

/*
* Class STRING handle [string] management
* private statuc [string] $key_holder - empty KEY holder
* public static [function] get_dynamic_keys - returns the KEYs in the string
* public static [function] parse - sets the KEYs values
* public static [function] parse_file - get the string from a FILE and sets the KEYs values
*/
class STRINGS{
	
	// The holder that hold the KEYs. The key have to be between the parentheses
	private static $key_holder = "/*?()?*/";
	
	/*
	* That function collect the KEYs from the STRING and returns an array with the KEYs
	* @param [sting] $str - the string to search for the KEYs
	* @returns [array] - array with the KEYs
	*/
	public static function get_dynamic_keys( $str ){
		
		// Checks IF it is a STRING
		if( gettype( "" ) !== gettype( $str ) ) return FALSE;
		
		// Gets HOLDER string LENGTH
		$hold_length = strlen(STRINGS::$key_holder);
		
		// Gets the START (left side) of the HOLDER
		$hold_start = substr( STRINGS::$key_holder , 0, $hold_length/2 );
		
		// Gets the END (right side) of the HOLDER
		$hold_end = substr( STRINGS::$key_holder , $hold_length/2 );
		
		// Explode the string by HOLDER START and clear the empty parts
		$explode_start = _ARRAY::clear( explode( $hold_start, $str ) , "" );
		
		// Explode the string by HOLDER END and clear the empty parts
		$explode_end = _ARRAY::clear( explode( $hold_end, $str ) , "" );
		
		// Check IF every START has an END
		if( count( $explode_start ) !== count( $explode_end ) ) return FALSE;
		
		// KEYs array
		$paths = array();
		
		// For every part of the string between two STARTs or at the end
		foreach( $explode_start as $start ){
			
			// Explode by END and isolate the KEY
			$start_part = explode( $hold_end, $start );
			
			// Check IF there is a KEY
			if(  count( $start_part ) < 1 || $start_part[0] === "" ) return FALSE;
			
			// Add the KEY into the array
			array_push( $paths, $start_part[0] );
		}
		// Returns the KEYs array
		return $paths;
	}
	
	/*
	* This function fill the KEYs in array wit their VALUEs
	* @param [string] $str - the string with dynamic KEYs
	* @param [array] $config - array with VALUES to use as CONFIG
	* @param [boolean] $clear - remove missing keys holders
	* @returns [string] - the string with parsed KEYs
	*/
	public static function parse( $str, $config, $clear = null ){
		
		if( gettype( $str ) !== gettype( "" ) ) return FALSE;
		
		// The KEYs in the STRING
		$keys = STRINGS::get_dynamic_keys( $str );
		
		// Gets HOLDER string LENGTH
		$hold_length = strlen(STRINGS::$key_holder);
		
		// Gets the START (left side) of the HOLDER
		$hold_start = substr( STRINGS::$key_holder , 0, $hold_length/2 );
		
		// Gets the END (right side) of the HOLDER
		$hold_end = substr( STRINGS::$key_holder , $hold_length/2 );
		
		foreach( $keys as $key ){
			
			// The holder with the KEY in it
			$find = $hold_start . $key . $hold_end;
			
			// The value in the CONFIG, KEY used as PATH
			$replace = CONFIG::get( $config, $key );
			
			// IF the value is a STRING
			if( gettype( "" ) === gettype( $replace ) ){
				
				// Change the HOLDER with the VALUE
				$str = str_replace( $find, $replace, $str );

			}else if( $clear === TRUE ){

				// Change the HOLDER with the VALUE
				$str = str_replace( $find, "", $str );
			}
		}
		// Returns the parsed STRING
		return $str;
	}
	
	/*
	* Get FILE contents and parse the result with a KEYs
	* @param [string] $url - the FILE's url
	* @param [array] $config - array with VALUES to use as CONFIG
	* @param [boolean] $clear - remove missing keys holders
	* @returns [string] - the string with parsed KEYs
	*/
	public static function parse_file( $url, $config, $clear = null ){
		
		// Get the file contents
		$str = file_get_contents( $url );
		
		if( $str === FALSE ) return FALSE;
		
		// Returns parset string
		return STRINGS::parse( $str, $config, $clear );
	}

	public static function is_string_length( $str ){

	    if( gettype( "" ) !== gettype( $str ) ) return FALSE;

	    $length = strlen( $str );

	    if( $length === 0 ) return FALSE;

	    return $length;
	}
}