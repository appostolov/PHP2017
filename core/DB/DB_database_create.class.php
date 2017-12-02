<?php

require_once __DIR__ . '/DB_table_delete.class.php';

/*
* Create SQL database
* @public [function] database_create - handle SQL query and creates a database
*/
class DB_database_create extends DB_table_delete{
	
	/*
	* Creates SQL database
	* @param [string] $db_name - the name of the created table
	* @return [boolean] TRUE if successful, FALSE otherwise
	*/
	public function database_create( $db_name ){
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();

		// If the name is not valid or already exists
		if( $this->valid_name( $db_name ) === false || $this->database( $db_name ) !== "" ){

			$this->set_error('database_create:bad_database_name');

			return false;
		}
		
		// Start SQL query
		$sql = "CREATE DATABASE " . $db_name;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){

			$this->set_error('database_create:prepare');

			return false;
		}

		// PDO bind values and execute() the SQL query
		if( $this->execute()->error() !== false ){

			$this->set_error('database_create:execute');

			return false;
		}
		
		// Reset databases, tablse and columns
		$this->set_tables_and_columns();
		
		return true;
	}
}