<?php

/*
* Class _ARRAY handle the [array] management
* public static [function] clear - removes targeted elements
*/
class _ARRAY{
	
	/*
	* Loop through the array and removes the matched elements
	* @param [array] $arr - the array to clear
	* @param [void] $search - value to match
	*/
	public static function clear( $arr, $search ){
		
		// Array working copy
		$arrr = $arr;
		
		// For every elemeny
		foreach( $arr as $key => $val ){
			
			// If match the search
			if( $val === $search ){
				
				// Remove
				unset( $arrr[ $key ] );
			}
		}
		// The cleared array copy
		return $arrr;
	}
}