<?php

require_once __DIR__ . '/DB_database_create.class.php';

/*
* Delete SQL database
* @public [function] database_delete - handle SQL query and deletes a database
*/
class DB_database_delete extends DB_database_create{
	
	/*
	* Deletes SQL database
	* @param [string] $db_name - the name of the table to delete
	* @return [boolean] TRUE if successful, FALSE otherwise
	*/
	public function database_delete( $db_name ){
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();

		// If db_name dont exist
		if( $this->database( $db_name ) === "" ){

			$this->set_error('database_delete:bad_database_name');

			return false;
		}
		
		// Start SQL query
		$sql = "DROP DATABASE " . $db_name;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){

			$this->set_error('database_delete:prepare');

			return false;
		}

		// PDO bind values and execute() the SQL query
		if( $this->execute()->error() !== false ){

			$this->set_error('database_delete:execute');

			return false;
		}
		
		// Reset databases, tablse and columns
		$this->set_tables_and_columns();
		
		return true;
	}
}

