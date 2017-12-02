<?php

require_once __DIR__ . '/DB_table_create.class.php';

/*
* Updating columns in a SQL table
* @public [function] add_column - add new COLUMN in the SQL table
* @public [function] delete_column - deletes COLUMN from SQL table
* @public [function] update_column - updating COLUMN in a SQL table
*/
class DB_table_update extends DB_table_create{
	
	/*
	* Adds COLUMN into SQL table
	* @param [string] $table - the name of the table where adding column
	* @param [array] $column_data - column data (name, type, length ...)
	* @return [boolean] TRUE if successful, FALSE otherwise
	*/
	public function add_column(
		$table,
		$column_data
	){
		
		/*

		*** HOW TO USE ***

		add_column(

			'table_name',                  // the name of the SQL table

			array(						   // new column data
			
				'name' => 'column_name',   // column NAME
				'type' => 'data_type',     // data TYPE
				'length' => 'max_length',  // max LENGTH
				'not_null' => true,        // default VALUE
				'auto_increment' => true,  // AUTO_INCREMENT
				'primary_key' => true      // PRIMARY KEY
			)
		);
		*/
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();
		
		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){
			
			$this->set_error('table_update:add_column:bad_table_name');
			
			return false;
		}
		
		// Validate ROW data
		$row = $this->parse_row( $column_data );

		if( $row === false ){

			$this->set_error('table_update:add_column:parse_row');

			return false;
		}
		
		// Starting the SQL query
		$sql = "ALTER TABLE " . $_table . " ADD " . $row;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){

			$this->set_error('table_update:add_column:prepare');

			return false;
		}

		// PDO bind values and execute() the SQL query
		if( $this->execute()->error() !== false ){

			$this->set_error('table_update:add_column:execute');

			return false;
		}
		
		// Reset databases, tablse and columns
		$this->set_tables_and_columns();
		
		return true;
	}
	
	/*
	* Adds COLUMN into SQL table
	* @param [string] $table - the name of the table where deleting column
	* @param [string] $column - the name of the column to delete
	* @return [boolean] TRUE if successful, FALSE otherwise
	*/
	public function delete_column(
		$table,
		$column
	){
		
		/*

		*** HOW TO USE ***

		delete_column(

			'table_name',                  // the name of the SQL table
			'column_name',                  // the name of the SQL column
			
			)
		);
		*/
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();
		
		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){
			
			$this->set_error('table_update:delete_column:bad_table_name');
			
			return false;
		}
		
		// Validate the column name
		$_column = $this->column( $table, $column );
		if( $_column === "" ){
			
			$this->set_error('table_update:delete_column:bad_column_name');
			
			return false;
		}
		
		// Starting SQL query
		$sql = "ALTER TABLE " . $_table . " DROP COLUMN " . $_column;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){

			$this->set_error('table_update:delete_column:prepare');

			return false;
		}

		// PDO bind values and execute() the SQL query
		if( $this->execute()->error() !== false ){

			$this->set_error('table_update:delete_column:execute');

			return false;
		}
		
		// Reset databases, tablse and columns
		$this->set_tables_and_columns();
		
		return true;
	}
	
	/*
	* Udates COLUMN in SQL table
	* @param [string] $table - the name of the table where deleting column
	* @param [array] $column_data - the column data
	* @return [boolean] TRUE if successful, FALSE otherwise
	*/
	public function update_column(
		$table,
		$column_data
	){
		
		/*

		*** HOW TO USE ***

		update_column(

			'table_name',                  // the name of the SQL table

			array(						   // columns data new
			
				'name' => 'column_name',   // column NAME
				'type' => 'data_type',     // data TYPE
				'length' => 'max_length',  // max LENGTH
				'not_null' => true,        // default VALUE
				'auto_increment' => true,  // AUTO_INCREMENT
				'primary_key' => true      // PRIMARY KEY
			)
		);
		*/
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();
		
		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){
			
			$this->set_error('table_update:update_column:bad_table_name');
			
			return false;
		}
		
		// Validate the column name
		$_column = $this->column( $table, $column_data['name'] );
		if( $_column === "" ){
			
			$this->set_error('table_update:update_column:bad_column_name');
			
			return false;
		}
		
		// Validate ROW data
		$row = $this->parse_row( $column_data );

		if( $row === false ){

			$this->set_error('table_update:update_column:parse_row');

			return false;
		}
		
		// Starting SQL query
		$sql = "ALTER TABLE " . $_table . " MODIFY COLUMN " . $row;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){

			$this->set_error('table_update:update_column:prepare');

			return false;
		}

		// PDO bind values and execute() the SQL query
		if( $this->execute()->error() !== false ){

			$this->set_error('table_update:update_column:execute');

			return false;
		}
		
		// Reset databases, tablse and columns
		$this->set_tables_and_columns();
		
		return true;
	}
}