<?php

require_once __DIR__ . '../../STATIC/REGEX.class.php';

/*
* Class for vallidating data block
* @private [array] $_data - data to validate
* @private [array] $_error - errors from validation
* @private [array] $_result - data after validation
* @public [function] init - initialyze data validation
* @private [function] _check - checking data against filter conditions
* @private [function] _check_length - checking the length of a string
* @private [function] _data_type - checking data type
* @private [function] _reset - reset $_data, $_error and $_result for new validation
* @public [function] error - returns the error from the last validation
* @public [function] data - returns the filtered data from the last validation
*/
class Validation{
	
	// Data to validate
	private $_data = NULL;

	// Data that is required
	private $_required = array();
	
	private $_filter = NULL;
	
	// Errors from the validation
	private $_error = array();
	
	/*
	* Validation initialyzation
	* @param [array] $data - data to validate
	* @param [array] $filter - conditions for the validation
	* @param [boolean] $stop - flag to stop after first error ocure
	* @returns [object] - $this on success, FALSE otherwise
	*/
	public function init(
		$data,
		$filter,
		$stop = FALSE
	){
		/*
		
			*** HOW TO USE ***
	
			$data = array(

				"data1" => "name",
				"data2" => "1234"
			);
			
			$filter = array(
			
				array(
				
					"data1" => array(
					
						"required" => true
					),
					"data2" => array(
					
						"required" => true
					)
				),
				array(
				
					"data1" => array(
					
						"max_length" => 4
					),
					"data2" => array(
					
						"max_length" => 4
					)
				)
			);
			
			$val = new Validation();
			$val->init( $data, $filter );
		*/
		
		// Reseting the class components for new validation
		$this->_reset( $data, $filter );
		
		// Checking IF $data is ARRAY
		if( gettype( array() ) !== gettype( $data ) ) return FALSE;
		
		// Checking IF $filter is ARRAY
		if( gettype( array() ) !== gettype( $filter ) ) return FALSE;
		
		// Loop through filter's conditions
		foreach( $filter as $data_key => $conditions ){
			
			// IF there are multiply filters
			if( gettype( "" ) !== gettype( $data_key ) ){
				
				// Loop through the current filter block
				foreach( $conditions as $data_key_1 => $conditions_1 ){
					
					// Loop through every filter condition
					foreach( $conditions_1 as $key => $val ){
						
						// Check the data against the condition
						if( $this->_check( $data_key_1, $key, $val ) === FALSE && $stop === TRUE ) return $this;
					}
				}	
			}else{// IF there is only one filter block
				
				// Loop through every filter condition
				foreach( $conditions as $key => $val ){
					
					// Check the data against the condition
					if( $this->_check( $data_key, $key, $val ) === FALSE && $stop === TRUE ) return $this;
				}
			}
		}
		return $this;
	}
	
	/*
	* Checking peace of data against a filter condition
	* @param [string] $data_key - the key of the data in $this->_data array
	* @param [string] $condition_key - the key of the condition
	* @param [void] $condition_value - th value of the condition
	* @returns [boolean] - TRUE on success, FALSE otherwise
	*/
	public function _check(
		$data_key,
		$condition_key,
		$condition_value
	){
        // If data don't exist and it is not required
	    if(
	        $condition_key !== 'required'
	        && array_key_exists( $data_key, $this->_data ) === false
	        && in_array( $data_key, $this->_required, TRUE ) === FALSE
	    ) return TRUE;

		// Switching between the conditions keys
		switch( $condition_key ){
		
			case 'required':// Required data
				
				// Check if data dont exist
				if( array_key_exists( $data_key, $this->_data ) === FALSE ){
					
					// Add error for missing data
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				array_push( $this->_required, $data_key );
				break;
				
			case 'equal':// Required data
				
				// IF data is not of the required type
				if( $this->_data[ $data_key ] != $condition_value ){
					
					// Add error for wrong data type
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;
				
			case 'identical':// Required data
				
				// IF data is not of the required type
				if( !( $this->_data[ $data_key ] === $condition_value ) ){
					
					// Add error for wrong data type
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;

			case 'different':// Required data

				// IF data is not of the required type
				if( $this->_data[ $data_key ] === $condition_value ){

					// Add error for wrong data type
					array_push( $this->_error, $data_key . ":" . $condition_key );

					return FALSE;
				}
				break;
			
			case 'data_type':// Data have to be of certian type
				
				// IF data is not of the required type
				if( $this->_data_type( $this->_data[ $data_key ], $condition_value ) === FALSE ){
					
					// Add error for wrong data type
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;
			
			case 'min_length':// Minimum data length
				
				// Check if data is shorter than the minimum length
				if( $this->_check_length( $this->_data[ $data_key ], $condition_value, "min" ) === FALSE ){
					
					// Add error for shorter data
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;
			
			case 'max_length':// Maximum data length
			
				// Check if data is longer than the maximum length
				if( $this->_check_length( $this->_data[ $data_key ], $condition_value, "max" ) === FALSE ){
					
					// Add error for longer data
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;
			
			case 'min_number':// Minimum numeric value
				
				// Check if data is smaller than minimum
				if( $this->_data[ $data_key ] < $condition_value ){
					
					// Add error for smaller data
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;
			
			case 'max_number':// Maximum numeric value
				
				// Check if data is bigger than maximum
				if( $this->_data[ $data_key ] > $condition_value ){
					
					// Add error for bigger data
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;
			
			case 'allowed':// Chars allowed in the data
				
				// Check if data contain chars, different than allowed chars
				if( REGEX::regex_match( $this->_data[ $data_key ], $condition_value, FALSE, TRUE ) === FALSE ){
					
					// Add error for illegal chars
					array_push( $this->_error, $data_key . ":" . $condition_key . ":" . REGEX::$_error );
					
					return FALSE;
				}
				break;
			
			case 'denied':// Chars denied in the data
				
				// Check if data contain denied chars
				if( REGEX::regex_match( $this->_data[ $data_key ], $condition_value, FALSE, TRUE ) === TRUE ){
					
					// Add error for illegal chars
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;
			
			case 'filter':// Filtering data with PHP filters
				
				// Filtering for boolean
				if( $condition_value === FILTER_VALIDATE_BOOLEAN ){
					
					// Check if its not boolean
					if( filter_var( $this->_data[ $data_key ], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) === NULL ){
						
						// Add error for filtering faild
						array_push( $this->_error, $data_key . ":" . $condition_key );
					
						return FALSE;
					}
				// Chekc if filtering faild
				}else if( filter_var( $this->_data[ $data_key ], $condition_value ) === FALSE ){
					
					// Add error for filtering faild
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				// Set data after filtering
				$this->_data[ $data_key ] = filter_var( $this->_data[ $data_key ], $condition_value );
				
				break;
			
			case 'in_array':// Check if data is in array
				
				// If data is not in condition's array
				if( gettype( array() ) !== gettype( $condition_value ) || in_array( $this->_data[ $data_key ], $condition_value ) === FALSE ){
					
					// Add error for array mismatch
					array_push( $this->_error, $data_key . ":" . $condition_key );
					
					return FALSE;
				}
				break;
			
			default:
				return FALSE;
		}
		return TRUE;
	}
	
	/*
	* Checking data length
	* @param [string] $data - data to check for length
	* @param [number] $limit - the limit of the length
	* @param [string] $mode - minimum or maximum
	* @returns [boolean] - TRUE on success, FALSE otherwise
	*/
	private function _check_length(
		$data,
		$limit,
		$mode
	){
	
		switch( $mode ){
		
			case 'min':
				
				// IF data is shorter than the limit
				if( strlen( (string)$data ) < $limit ) return FALSE;
			
				break;
			
			case 'max':
				
				// IF data is longer than the limit
				if( strlen( (string)$data ) > $limit ) return FALSE;
				
				break;
			
			default:
				return FALSE;
		}
		return TRUE;
	}
	
	/*
	* Check data type
	* @param [void] $data - data to check
	* @param [string] $type - data type
	* @returns [boolean] - TRUE on success, FALSE otherwise
	*/
	private function _data_type(
		$data,
		$type
	){
		// All data types except object and resource
		$types = array(
		
			"string" => "",
			"integer" => 0,
			"double" => 0.5,
			"boolean" => true,
			"array" => array(),
			"NULL" => NULL
		);
		
		// Check against data types in the array
		if( array_key_exists( $type, $types ) === TRUE ){
			
			// IF type i different than the filter's condition
			if( gettype( $types[ $type ] ) !== gettype( $data ) ) return FALSE;
			
		}else if( $type === "object" ){// IF condition is object
			
			return is_object( $data );
		
		}else if( $type === "resource" ){// IF condition is resource
		
			return is_resource( $data );
			
		}else{
		
			return FALSE;
		}
		return TRUE;
	}
	
	/*
	* Reset the object data for new validation
	* @param [array] $data - data to validate
	*/
	private function _reset( $data, $filter ){

		$this->_required = array();
		$this->_data = $data;
		$this->_filter = $filter;
		$this->_error = array();
	}
	
	/*
	* Returns the error from last validation
	*/
	public function error(){
	
		return $this->_error;
	}
	
	/*
	* Returns the valid data from last validation
	*/
	public function data(){
	
		return $this->_data;
	}
	
	/*
	* Returns the valid data from last validation
	*/
	public function filter(){
	
		return $this->_filter;
	}
}