<?php

/*
* Handling user input
* @public [function] exists - check if the input field exists
* @public [function] get - get user input field
*/
class Input{
	
	/*
	* Check if field exists in the input
	* @param [string] $key - field name
	* @param [string] $method - input type
	* @returns [boolean] - TRUE if exists, FALSE otherwise
	*/
	public function exists( $key, $method = 'post' ){
		
		switch( $method ){
			
			case 'post':
				return array_key_exists( $key, $_POST );
			case 'get':
				return array_key_exists( $key, $_GET );
			default:
				return;
		}
	}
	
	/*
	* Gets user input field
	* @param [string] $key - field name
	* @param [string] $method - input type
	* @returns [void] - the input value
	*/
	public function get( $key, $method = 'post' ){
		
		if( $this->exists( $key, $method ) === TRUE ){
			
			switch( $method ){
			
				case 'post':
					return $_POST[ $key ];
				case 'get':
					return $_GET[ $key ];
				default:
					return;
			}
		}
	}
	
	/*
	* Gets user input field
	* @param [string] $key - field name
	* @param [string] $method - input type
	* @returns [void] - the input value
	*/
	public function set( $key, $val, $method = 'post' ){
		
		switch( $method ){
		
			case 'post':
				return $_POST[ $key ] = $val;
			case 'get':
				return $_GET[ $key ] = $val;
			default:
				return;
		}
	}
}