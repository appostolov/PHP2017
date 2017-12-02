<?php

require_once __DIR__ . '/DB_update.class.php';

/*
* Deleting rows drom database table
* @public [function] delete - handle the sql query and delete rows drom the table
*/
class DB_delete extends DB_update{
	
	// COMMAND       VALUE                                                             DATA TYPE

	// DELETE FROM   table_name                                                        [string]
	// WHERE         column1 = value1 AND ( column2 <= value2 OR column2 != value3 )...[array] || [object]
	
	/*
	* Delete data from database table
	* @param [string] $table - the table_name in the UPDATE statement, where to update the data
	* @param [array] $conditions - conditions in the WHERE statement for specifying the update
	* @return [boolean] if successful TRUE, otherwise FALSE
	*/
	public function delete(
		$table,
		$conditions = NULL
	){
		
		/*
		
		*** HOW TO USE ***
		
		delete(
		
			'table_name',                      // WHERE to update
			
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
			
			$this->set_error('delete:bad_table_name');
			
			return false;
		}
		
		// Starting the SQL query
		$sql = "DELETE FROM " . $_table;
		
		// Add/Validate the WHERE statement
		if( $conditions !== NULL ){
			
			$_conditions = $this->conditions( $_table, $conditions );
			if( $_conditions === false ){
				
				$this->set_error('delete:WHERE');
				
				return false;
			}
			
			// Adding WHERE sql statement to the query
			$sql .= " WHERE " . $_conditions;
		}
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){
			
			$this->set_error('delete:prepare:'.$sql);
			
			return false;
		}
		// PDO bind values and execute() the SQL query
		if( $this->execute( $this->_values )->error() !== false ){
			
			$this->set_error('delete:execute');
			
			return false;
		}
		
		return true;
	}
}