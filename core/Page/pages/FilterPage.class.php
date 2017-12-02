<?php

require_once __DIR__ . '/../Page.class.php';
require_once __DIR__ . '/../../Validation/Validation.class.php';
require_once __DIR__ . '/../../Input/Input.class.php';
require_once __DIR__ . '/../../Session/Session.class.php';
require_once __DIR__ . '/../../Cookie/Cookie.class.php';
require_once __DIR__ . '/../../DB/DB_database_create.class.php';
require_once __DIR__ . '/../../Header/Header.class.php';

/*
* The page's pourpose is to check data and locate to destination
* @public [function] init - execute Page specific things
*/
class FilterPage extends Page{
	
	/*
	*** HOW TO USE ***
	
	'step2' => array(
		'data' => array(
			'get' => array(
				'test'
			)
		),
		'filter' => array(
			array(
				"test" => array(
					"required" => true
				)
			)
		)
	);
	
	$page = new Step2( $config );

	*/
	
	/*
	* Check and locate
	* @returns [object] FilterPage onject
	*/
	public function init(){
		
		// Setting data, prepared for filtering
		$this->set(
			"prepared_data",
			$this->get_data_all( "*" )
		);
		// Validation and error setting
		$this->set(
			"error",
			$this->manager(
				"validation"
			)->init(
				$this->get("prepared_data"),
				$this->get("filter")
			)->error()
		);
		// Execute FilterPage speciffic things
		$this->filter_init();
		
		return $this;
	}
	// Constructor closure
	protected function filter_init(){}
	
	/*
	* Validate AGAIN with new DATA and new FILTER
	* @param [array] $data - data to check
	* @param [array] $filter - filtering conditions
	* @param [boolean] $stop - stop on first error
	* @returns [object] FilterPage onject
	*/
	public function again(
		$data,
		$filter,
		$stop = FALSE
	){
		// Prepare new data
		$this->set(
			"data",
			$data
		);
		// Set new filter
		$this->set(
			"filter",
			$filter
		);
		// Filtering data
		return $this->filter_init();
	}
	
	/*
	* Get all data from a source
	* @param [string] $type - source type (Input, Database, etc.)
	* @returns [array] the validation prepared data
	*/
	public function get_data_all(
		$type
	){
		$result = array();
		
		switch( $type ){
			// All sources
			case "*":
				$data = $this->get("data");
				foreach( $data as $key => $val ){
					$result = array_merge(
						$result,
						$this->get_data_all( $key )
					);
				}
				break;
			// $_POST
			case "post":
				$result = $this->get_data_input( $type );
				break;
			// $_GET
			case "get":
				$result = $this->get_data_input( $type );
				break;
			// $_SESSION
			case "session":
				$this->manager( $type )->init();
				$result = $this->get_data_session_cookie( $type );
				break;
			// $_COOKIE
			case "cookie":
				$result = $this->get_data_session_cookie( $type );
				break;
			// DB
			case "database":
				$result = $this->get_data_database();
				break;
			default:
				return array();
		}
		return $result;
	}
	
	/*
	* Get all from INPUT
	* @param [string] $type - input type (POST, GET)
	* @returns [array] the validation prepared data
	*/
	protected function get_data_input(
		$type
	){
		// temp result
		$result = array();
		// Get data keys
		$data = $this->get( "data/" . $type );
				
		foreach( $data as $d ){
			// IF exists such INPUT
			if( $this->manager( "input" )->exists( $d, $type ) ){
				// Update temp result
				$result[ $d ] = $this->manager( "input" )->get( $d, $type );
			}
		}
		return $result;
	}
	
	/*
	* Get all from resource
	* @param [string] $type - resource type (SESSION, COOKIE)
	* @returns [array] the validation prepared data
	*/
	protected function get_data_session_cookie(
		$type
	){
		// temp result
		$result = array();
		// Get data keys
		$data = $this->get( "data/" . $type );
				
		foreach( $data as $d ){
			// IF exists such data
			if( $this->manager( $type )->exists( $d ) ){
				// Update temp result
				$result[ $d ] = $this->manager( $type )->get( $d );
			}
		}
		return $result;
	}
	
	/*
	* Constructor closure
	*/
	public function get_data_database(){}
	
	/*
	* Instantiate managers needed
	* @param [string] $type - Validation, Input, etc.
	* @returns [object] the desired manager
	*/
	protected function manager( $type ){
		
		$manager = NULL;
		// SETTING manager INSTANCE
		switch( $type ){
			// Validation
			case "validation":
				if ( !$this->get( "manager/" . $type ) instanceof Validation ) $manager = new Validation();
				else $manager = $this->get( "manager/" . $type );
				break;
			// Input	
			case "input":
				if ( !$this->get( "manager/" . $type ) instanceof Input ) $manager = new Input();
				else $manager = $this->get( "manager/" . $type );
				break;
			// Session
			case "session":
				if ( !$this->get( "manager/" . $type ) instanceof Session ){
					$manager = Session::get_instance();
				}else $manager = $this->get( "manager/" . $type );
				break;
			// Cookie
			case "cookie":
				if ( !$this->get( "manager/" . $type ) instanceof Cookie ) $manager = new Cookie();
				else $manager = $this->get( "manager/" . $type );
				break;
			// DB_select
			case "database":

				if ( !$this->get( "manager/" . $type ) instanceof DB_database_create ) $manager = DB_database_create::get_instance();
				else $manager = $this->get( "manager/" . $type );
				break;
			// Header
			case "header":
				if ( !$this->get( "manager/" . $type ) instanceof Header ) $manager = new Header();
				else $manager = $this->get( "manager/" . $type );
				break;	
			default:
				break;
		}
		return $manager;
	}
}