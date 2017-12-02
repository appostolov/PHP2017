<?php

require_once __DIR__ . '/DB_delete.class.php';

/*
* Selecting table data
* @public [function] table_select - returns the table data
*/
class DB_table_select extends DB_delete{

	/*
	* Checks the table and select the data
	* @param [string] $table - name of the SQL table
	* @param [string] $result_type - type of the returned data ( num, assoc, obj )
	* @return [array] the RESULT of the sql query or FALSE
	*/
	public function table_select(
		$table,
		$result_type = 'obj'
	){

		// Resetting the main values ( error, values, result )
		$this->reset_values();

		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){

			$this->set_error('table_select:bad_table_name');

			return false;
		}

		// Starting SQL query
		$sql = "DESCRIBE " . $table;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){
			
			$this->set_error('table_select:prepare');
			
			return false;
		}
		
		// PDO bind values and execute() the SQL query
		if( $this->execute()->error() !== false ){
			
			$this->set_error('table_select:execute');
			
			return false;
		}
		
		// Returns the result from the query
		return $this->result( $result_type );
	}
}