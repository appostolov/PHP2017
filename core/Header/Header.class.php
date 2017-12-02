<?php

/*
* Mahage PHP headers
* @public [function] set - sets a PHP header
* @public [function] location - sets a PHP header location
*/
class Header{
	
	/*
	* @param [string] $text - the text of the header
	* @param [boolean] $replace - allows multiply headers
	* @param [number] $http_response_code - ???
	*/
	public function set(
		$text,
		$replace = TRUE,
		$http_response_code = 302
	){
		// Sets a PHP header
		header( $text, $replace, $http_response_code );
	}
	
	/*
	* @param [string] $location - valid url
	* @param [boolean] $replace - allows multiply headers
	* @param [number] $http_response_code - ???
	*/
	public function location(
		$location,
		$replace = TRUE,
		$http_response_code = 302
	){
		
		// Create header's text
		$text = 'Location: ' . $location;
		
		// Sets the PHP header
		$this->set( $text, $replace, $http_response_code );
	}
}