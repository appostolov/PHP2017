<?php

require_once __DIR__ . '/../Patterns/Singleton.class.php';

/*
* Handle the PHP cookies
* @public [function] exists - Check if cookie exists
* @public [function] set - Sets a php cookie
* @public [function] remove - Destroy php cookie
* @public [function] close - Destroy all cookies
*/
class Cookie extends Singleton{
	
	/*
	* Check if cookie exists
	* @param [string] $key - the cookie name
	* @returns [boolean] - TRUE on succes, FALSE otherwise
	*/
	public function exists( $key ){
		
		return array_key_exists( $key, $_COOKIE );
	}
	
	/*
	* Sets PHP cookie
	* @param [string] $name - the cookie name
	* @param [string] $value - the cookie name
	* @param [string] $expire - the cookie name
	* @param [string] $path - the cookie name
	* @returns [object] - the object itself
	*/
	public function set(
		$name,
		$value = '',
		$expire = 1,
		$path = "/"
	){	
		// Sets the PHP cookie	
		setcookie($name, $value, $expire, $path);
		
		return $this;
	}
	
	/*
	* Get a cookie
	* @param [string] $key - the cookie name
	* @returns [void] - the cookie value
	*/
	public function get( $key ){
		
		// Check if cookie member exists
		if( $this->exists( $key ) === FALSE ) return;
		
		// Returns the searched cookie member
		return $_COOKIE[ $key ];
	}
	
	/*
	* Removes a cookie's member
	* @param [string] $key - cookie's member key in the cookie array
	* @returns - the cookie object
	*/
	public function remove( $key ){
		
		// chek IF the cookie EXISTS
		if( $this->exists( $key ) ){
			
			// DELETE the COOKIE
			$this->set( $key );
			
			// Update the cookie's array
			unset( $_COOKIE[ $key ] );
		}
		
		return $this;
	}
	
	/*
	* Destroys all cookies
	* @returns - the cookie object
	*/
	public function close(){
		
		// IF there are COOKIEs array
		if (isset($_COOKIE)) {
			
			foreach($_COOKIE as $name => $value) {
				
				// Destroy the cookie
				$this->set( $name );
			}
			// Empty the cookie's array
			$_COOKIE = array();
		}
		
		return $this;
	}
}