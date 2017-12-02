<?php

require_once __DIR__ . '/DB_insert.class.php';

/*
* Updating data in database
* @public [function] update - handle the sql query and updates the table
* @protected [function] set - prepare sql SET statement
*/
class DB_update extends DB_insert{
	
	// COMMAND     VALUE                                                             DATA TYPE

	// UPDATE      table_name                                                        [string]
	// SET         (column1=value1, column2=value2,...)                              [array] || [string]
	// WHERE       column1 = value1 AND ( column2 <= value2 OR column2 != value3 )...[array] || [object]
	
	/*
	* Updates data into database
	* @param [string] $table - the table_name in the UPDATE statement, where to update the data
	* @param [array] $set - the columns and values in the SET statement, wich to update
	* @param [array] $conditions - conditions in the WHERE statement for specifying the update
	* @return [boolean] if successful TRUE, otherwise FALSE
	*/
	public function update(
		$table,
		$set,
		$conditions = NULL
	){
		
		/*
		
		*** HOW TO USE ***
		
		update(
		
			'table_name',                      // WHERE to update
			
			array(                             // VALUES to insert
				array(
					'column' => 'key',
					'value' => 'val1'
				),
				array(
					'column' => 'key',
					'value' => 'val2'
				)
			),
			
			array(                             // CONDITIONS wich are valid for the value of the table column
			
				'operator' => 'AND' || 'OR',   // OPERATOR between the conditions
				
				'conditions' => array(         // set of CONDITIONS array( condition1, condition2, ...)
				
					array(                     // condition1
					
						'column' => 'column_name',
						'sign' => '!=',
						'value' => value
					),
					array(                     // condition2
					
						'column' => 'column_name',
						'sign' => '!=',
						'value' => value
					)
				)
			)
		);
		*/
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();
		
		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){
			
			$this->set_error('update:bad_table_name');
			
			return false;
		}
		
		// Starting the SQL query
		$sql = "UPDATE " . $_table;
		
		// Preparing values for the SQL query
		$_set = $this->set( $set, $_table );
		if( $_set === false ){
			
			$this->set_error('update:SET');
			
			return false;
		}
		
		// Adding SET sql statement to the query
		$sql .= " SET " . $_set . "";
		
		// Add/Validate the WHERE statement
		if( $conditions !== NULL ){
			
			$_conditions = $this->conditions( $_table, $conditions );
			if( $_conditions === false ){
				
				$this->set_error('update:WHERE');
				
				return false;
			}
			
			// Adding WHERE sql statement to the query
			$sql .= " WHERE " . $_conditions;
		}
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){
			
			$this->set_error('update:prepare:'.$sql);
			
			return false;
		}
		// PDO bind values and execute() the SQL query
		if( $this->execute( $this->_values )->error() !== false ){
			
			$this->set_error('update:execute');
			
			return false;
		}
		
		return true;
	}
	
	/*
	* Preparing the SET sql statement
	* @param [array] $set - COLUMNS to set with VALUES
	* @param [string] $table - TABLE where are the columns
	* @return [string] the SET staement of the SQL query
	*/
	protected function set( $set, $table ){
		
		// If it's only one column with one new value
		if( isset( $set['column'] ) === true && isset( $set['value'] ) === true ){
			
			// Check if the column name is avialable
			if( $this->column( $table, $set['column'] ) === "" ) return false;
			
			// Add the value for binding under specific key
			$count = count( $this->_values );
			$key = ":val" . $count;
			$this->_values[$key] = $set['value'];
			
			return $set['column'] . "=" . $key;
			
		}else{
			
			// Start SQL statement
			$sql = "";
			
			for( $a = 0; $a < count( $set ); $a ++ ){
				
				// Recursion for every column = value
				$result = $this->set( $set[$a], $table );
				
				if( $result === false ) return false;
				
				// Adding column = value to the SQL statement
				$sql .= $result;
				
				// Add separator if it's not last
				if( $a < count( $set ) -1 ){
					
					$sql .= ", ";
				}
			}
			return $sql;
		}
		return false;
	}
}