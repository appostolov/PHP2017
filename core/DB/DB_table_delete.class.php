<?php

require_once __DIR__ . '/DB_table_update.class.php';

/*
* Deleting COLUMNS in a SQL table
* @public [function] table_delete - handle SQL query and deletes a table
*/
class DB_table_delete extends DB_table_update{
	
	/*
	* Deletes SQL table
	* @param [string] $table - the name of the table to delete
	* @return [boolean] TRUE if successful, FALSE otherwise
	*/
	public function table_delete( $table ){
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();
		
		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){
			
			$this->set_error('table_delete:bad_table_name');
			
			return false;
		}
		
		// Start SQL statement
		$sql = "DROP TABLE " . $_table;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){

			$this->set_error('table_delete:prepare');

			return false;
		}

		// PDO bind values and execute() the SQL query
		if( $this->execute()->error() !== false ){

			$this->set_error('table_delete:execute');

			return false;
		}
		
		// Reset databases, tablse and columns
		$this->set_tables_and_columns();
		
		return true;
	}
}