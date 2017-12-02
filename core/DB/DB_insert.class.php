<?php

require_once __DIR__ . '/DB_select.class.php';

/*
* Inserting data into database
* @public [function] insert - handle the sql query and inserts the data
* @protected [function] values - prepare values for binding and returns the VALUES sql statement
*/
class DB_insert extends DB_select{
	
	// COMMAND        VALUE                    DATA TYPE

	// INSERT INTO    table_name               [string]
	//                (column1, column2,...)   [array] || [string]
	// VALUES         (value0, value1, ...)    [array] || [string] 
	
	/*
	* Inserts data into database
	* @param [string] $table - the table_name in the INSERT INTO statement, where to insert the data
	* @param [array] $values - the values in the VALUES statement, wich to insert in the table
	* @param [array] $columns - the columns, where to push the values
	* @return [boolean] if successful TRUE, otherwise FALSE
	*/
	public function insert(
		$table,
		$values,
		$columns = NULL
	){
		
		/*
		
		*** HOW TO USE ***
		
		insert(
		
			'table_name',                      // WHERE to insert
			
			array( column1, column2,...)       // columns, where to insert
			
			array( value1, value2,...),        // VALUES to insert
		);
		*/
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();
		
		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){
			
			$this->set_error('insert:bad_table_name');
			
			return false;
		}
		
		// Starting the SQL query
		$sql = "INSERT INTO " . $_table;
		
		if( isset($columns) ){
			
			// Checking the columns names
			$_columns = $this->what( $_table, $columns );
			if( $_columns === false || $_columns === '*' ){
				
				$this->set_error('insert:COLUMNS');
				
				return false;
			}
			// Adding the columns names to the SQL query
			$sql .= " (" . $_columns . ")";
		}
		
		// Preparing values for the SQL query
		$_values = $this->values($values);
		if( $_values === false ){
			
			$this->set_error('insert:VALUES');
			
			return false;
		}
		
		// Adding the values to the SQL query
		$sql .= $_values;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){
			
			return var_dump($sql);
			
			$this->set_error('insert:prepare:'.$sql);
			
			return false;
		}
		
		// PDO bind values and execute() the SQL query
		if( $this->execute( $this->_values )->error() !== false ){
			
			$this->set_error('insert:execute');
			
			return false;
		}
		
		return true;
	}
	
	/*
	* Preparing the values for the SQL query
	* @param [array] $values - values to insert
	* @return [string] the VALUES staement of the SQL query
	*/
	protected function values( $values ){
		
		// Starting the SQL query
		$sql = " VALUES ";
		
		if( gettype( "" ) === gettype( $values ) ){
			
			// Pushing the value to wait for binding
			$this->_values[':val0'] = $values;
			
			// Adding the value to the SQL query
			$sql .= "(:val0)";
			
		}else if( gettype( array() ) === gettype( $values ) ){
			
			if( count($values) < 1 ) return false;

			if( gettype( array() ) === gettype( $values[0] ) ){

				for( $a = 0; $a < count( $values ); $a ++){

					$sql .= $this->values2( $values[$a] );

					if( $a < count( $values ) - 1 ){

						// Adding a separator between the keys
						$sql .= ", ";

					}
				}
			}else{

				$sql .= $this->values2( $values );
			}
		}else{
			
			return false;
		}
		// Returns the SQL query
		return $sql;
	}

	protected function values2( $values ){

		// Starting the SQL query
		$sql = "(";

		for( $a = 0; $a < count( $values ); $a ++){

			// Add the value for binding under specific key
			$count = count( $this->_values );
			$key = ":val" . $count;
			$this->_values[$key] = $values[$a];

			// Adding the key into the SQL query
			$sql .= $key;

			if( $a < count( $values ) - 1 ){

				// Adding a separator between the keys
				$sql .= ", ";

			}else{

				// Close the VALUES statement in the SQL query
				$sql .= ")";
			}
		}
		return $sql;
	}
}