<?php

/*
* PHP SESSION MANAGER
* @private [boolean] $started - indicate session_start() previous execution
* @public [function] init - execute session_start() if needed
* @public [function] exists - check if session member exists
* @public [function] get - returns session member
* @public [function] set - set session member
* @public [function] remove - removes session member
* @public [function] clear - remove all session members
* @public [function] close - destroys the session
*/
class Session{
	
	/*
	* Starts the PHP session if needed
	* @returns - the Session object
	*/
	public function init(){
		
		// Check if session exists
		if( !isset( $_SESSION ) ){
			
			session_start();
		}
		return $this;
	}
	
	/*
	* Check if session member exists
	* @param [string] $key - session's member key in the session array
	* @returns [boolean] - TRUE on success, FALSE otherwise
	*/
	public function exists( $key ){
		
		$this->init();
		
		return array_key_exists( $key, $_SESSION );
	}
	
	/*
	* Gets session member
	* @param [string] $key - session member key in the session array
	* @returns [void] - the session member value
	*/
	public function get( $key ){
		
		$this->init();
		
		// Check if session member exists
		if( $this->exists( $key ) === FALSE ) return;
		
		// Returns the searched session member
		return $_SESSION[ $key ];
	}
	
	/*
	* Set the session's member value
	* @param [string] $key - session's member key in the session array
	* @param [void] $val - the new value of the session member
	* @returns - the Session object
	*/
	public function set( $key, $val ){
		
		$this->init();
		
		// Setting the session member
		$_SESSION[ $key ] = $val;
		
		return $this;
	}
	
	/*
	* Removes a session's member
	* @param [string] $key - session's member key in the session array
	* @returns - the Session object
	*/
	public function remove( $key ){
		
		$this->init();
		
		// Removing the session member
		unset( $_SESSION[ $key ] );
		
		return $this;
	}
	
	/*
	* Resets the session
	* @returns - the Session object
	*/
	public function clear(){
		
		$this->init();
		
		// Resets the session to empty array()
		$_SESSION = array();
		
		return $this;
	}
	
	/*
	* Destroys the session
	* @returns - the Session object
	*/
	public function close(){
		
		// Destroys the session's coockie
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		// Destroys the session
		session_destroy();
		
		return $this;
	}
}