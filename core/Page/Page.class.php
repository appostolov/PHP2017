<?php

require_once __DIR__ . '/../STATIC/CONSTS.class.php';
require_once __DIR__ . '/../STATIC/CONFIG.class.php';
require_once __DIR__ . '/../Session/Session.class.php';

/*
* Page class creates an object, 
* that can hold custume data 
* including other objects instances.
* @private [string] $id - the ID of the current page
* @private [array] $arr - dynamic data holder
* @public [function] identify - returns the Page's ID
* @public [function] exists - check if data exists in $arr
* @public [function] get - gets searched $arr data
* @public [function] set - sets $arr data
* @public [function] restart - set new Page's ID and $arr data
* @public [function] show - ECHO output DATA
*/
class Page{
	
	// Page's data holder
	protected $conf;
	
	/*
	* @param [array] $config - Page's starting data
	* @returns [object] - the Page
	*/
	public function __construct( $config = NULL ){
		
		$this->restart( $config );
		
		$this->init();
	}
	
	/*
	* @returns [string] - Page's ID
	*/
	public function identify(){
		
		return $this->conf[ 'id' ];
	}

	/*
	* Check if data exists
	* @param [string] $path - the path to desired data
	* @returns [boolean] - TRUE if exists, FALSE otherwise
	*/
	public function exists( $path ){

		return CONFIG::_exist( $path, $this->get() );
	}
	
	/*
	* Gets a part of the Page's data
	* @param [string] $path - the path to desired data
	* @returns [void] - the data value
	*/
	public function get( $path = NULL ){
		
		// IF there is NO PATH specified
		if( $path === NULL ) return $this->conf;
		
		return CONFIG::get( $this->conf, $path );
	}
	
	/*
	* Sets a part of the Page's data
	* @param [void] $path - the path to desired data
	* @param [void] - the data's new value
	* @returns [boolean] - TRUE if successful, FALSE otherwise
	*/
	public function set( $path, $value ){
		
		// Generate the new data
		$config = CONFIG::set( $this->conf, $path, $value );
		
		// If generation succeed Sets the new Page's data
		if( $config !== FALSE ) $this->conf = $config;
		
		return $this;
	}
	
	/*
	* Resets the Page's ID and data
	* @returns [object] - the Page
	*/
	public function restart( $config = NULL ){
		
		// IF there is NO CONFIG
		if( gettype( array() ) !== gettype( $config ) ){
			
			$this->conf = array();
			
		}else{
			
			$this->conf = $config;
		}
		// NEW ID
		$this->conf[ 'id' ] = md5(mt_rand());
		
		return $this;
	}
	
	/*
	* ECHO output DATA
	* @param [string] $str - front end code
	*/
	public function show( $str ){
		
		// OUTPUT front end DATA
		echo $str;
		
		return $this;
	}
	
	public function init(){}
}