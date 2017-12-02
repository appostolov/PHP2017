<?php

require_once __DIR__ . '/DB.class.php';

/*
* Selecting data from database
* @protected [array] $_values - holds values for binding in the sql query
* @public [function] select - handle the sql query and returns the selected data
* @public [function] select_distinct - handle the sql query and returns the selected data
* @protected [function] what - checks the columns in the SELECT statement and returns the sql query part
* @protected [function] conditions - checks the columns and signs in WHERE statement, prepare values for binding and returns the sql query part
* @protected [function] order - checks the columns and order in ORDER BY statement, and returns the sql query part
* @protected [function] positive_int - validate data to positive integer
*/
class DB_select extends DB{

	// COMMAND     VALUE                                                             DATA TYPE

	// SELECT      column1, column2,... || (*)                                       [array] || [string]
	// FROM        table_name                                                        [string]
	// WHERE       column1 = value1 AND ( column2 <= value2 OR column2 != value3 )...[array] || [object]
	// ORDERED BY  column1 ASC, column2 DESC,...                                     [array]
	// LIMIT       3                                                                 [number]
	// OFFSET      120                                                               [number]
	
	protected $_values = array();
	
	/*
	* Selects data from database and returns the result
	* @param [array] $columns - the columns of the table in the SELECT statement, from wich to select data
	* @param [string] $table - the table_name in the FROM statement, from wich to select columns
	* @param [array] $conditions - conditions in the WHERE statement for specifying the slection
	* @param [array] $order - ordering the query in the ORDER BY statement
	* @param [number] $limit - maximum row count of the result
	* @param [number] $offset - count after wich to start
	* @param [string] $result_type - data structure of the result
	* @return [array] the RESULT of the sql query or FALSE
	*/
	public function select( 
		$columns,
		$table,
		$conditions = NULL,
		$order = NULL,
		$limit = NULL,
		$offset = NULL,
		$result_type = 'num'
	){
		
		/*
		
		*** HOW TO USE ***
		
		select(
		
			'*',                               // WHAT to select
			
			'table_name',                      // from WHERE to select
			
			array(                             // CONDITIONS wich are valid for the value of the table column
			
				'operator' => 'AND' || 'OR',   // OPERATOR between the conditions
				
				'conditions' => array(         // set of CONDITIONS array( condition1, condition2, ...)
				
				array(                         // condition1
				
					'column' => 'column_name',
					'sign' => '!=',
					'value' => value
				),
				array(                         // condition2
				
					'column' => 'column_name',
					'sign' => '!=',
					'value' => value
				)
			),
			array(                             // ORDER of the returned result
		
				'sc' => 'ASC' || 'DESC",       // forward or backward ORDER
				
				'column' => 'column_name'      // table COLUMN
			),
			5,                                 // row count LIMIT
				
			14,                                // OFFSET from the first row
		);
		*/
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();
		
		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){
			
			$this->set_error('select:FROM');
			
			return false;
		}
		
		//Validate the SELECT statement
		$_columns = $this->what( $_table, $columns );
		if( $_columns === false ){
			
			$this->set_error('select:SELECT');
			
			return false;
		}
		
		$sql = "SELECT " . $_columns . " FROM " . $_table;
		
		// Add/Validate the WHERE statement
		if( $conditions !== NULL ){
			
			// Reset the values for binding
			$this->_values = array();
			
			$_conditions = $this->conditions( $_table, $conditions );
			if( $_conditions === false ){
				
				$this->set_error('select:WHERE');
				
				return false;
			}
			
			$sql .= " WHERE " . $_conditions;
		}
		
		// Add/Validate the ORDER BY statement
		if( $order !== NULL ){
			
			$_order = $this->order( $_table, $order );
			if( $_order === false ){
				
				$this->set_error('select:ORDER_BY');
				
				return false;
			}
			
			$sql .= " ORDER BY " . $_order;
		}
		
		// Add/Validate LIMIT statement
		if( $limit !== NULL ){
			
			$_limit = $this->positive_int($limit);
			if( $_limit === false ){
				
				$this->set_error('select:LIMIT');
				
				return false;
			}
			
			$sql .= " LIMIT " . $_limit;
		}
		
		// Add/Validate OFFSET statement
		if( $offset !== NULL ){
			
			$_offset = $this->positive_int($offset);
			if( $_offset === false ){
				
				$this->set_error('select:OFFSET');
				
				return false;
			}
			
			$sql .= " OFFSET " . $_offset;
		}
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){
			
			$this->set_error('select:prepare');
			
			return false;
		}
		
		// PDO bind values and execute() the SQL query
		if( $this->execute( $this->_values )->error() !== false ){
			
			$this->set_error('select:execute');
			
			return false;
		}
		
		// Returns the result from the query
		return $this->result( $result_type );
	}
	
	/*
	* SELECT DISTINCT data from SQL table
	* @param [string] $table - TABLE name to select from
	* @param [string] $column - COLUMN name to filter distinct values
	* @return [array] the RESULT of the sql query or FALSE
	*/
	public function select_distinct( $table, $column ){
		
		// Resetting the main values ( error, values, result )
		$this->reset_values();
		
		// Validate the table name
		$_table = $this->table($table);
		if( $_table === "" ){
			
			$this->set_error('select_distinct:FROM');
			
			return false;
		}
		
		// Checks if the column_name is avialable
		$_column = $this->column( $table, $column );
		if( $_column === "" ){
			
			$this->set_error('select_distinct:unknown_column');
			
			return false;
		}
		
		// Create SQL query
		$sql = "SELECT DISTINCT " . $column . " FROM " . $table;
		
		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){
			
			$this->set_error('select_distinct:prepare');
			
			return false;
		}
		
		// PDO bind values and execute() the SQL query
		if( $this->execute( $this->_values )->error() !== false ){
			
			$this->set_error('select_distinct:execute');
			
			return false;
		}
		
		// Result from the SQL query
		$sql_result = $this->result( 'num' );
		
		$result = array();
		
		for( $a = 0; $a < count( $sql_result ); $a ++ ){
			
			// Get only the column value
			$result[$a] = $sql_result[$a][0];
		}
		return $result;
	}
	
	/*
	* Checks the columns from where will select data
	* @param [string] $table - table_name where the columns are
	* @param [array] $columns - columns for selection
	* @return [string] - the part of the SQL query for the SELECT statement, or FALSE if fail
	*/
	protected function what( $table, $columns ){
		
		if( gettype( $columns ) === gettype( "" ) ){
			
			// Checks if selecting all columns
			if( $columns === '*' ) return $columns;
			
			// Check if its a column name
			else if( $this->column( $table, $columns ) !== "" ) return $columns;
			
			else return false;
			
		}else if( gettype( $columns ) === gettype( array() ) ){
			
			// Prepare SQL query part
			$sql = "";
			
			for($a = 0; $a < count($columns); $a ++){
				
				// Checks if the column_name is avialable
				if( $this->column( $table, $columns[$a] ) === "" ) return false;
				
				// Add the column_name to the string
				$sql .= $columns[$a];
				
				// Add separator if it's not last
				if( $a < count($columns) -1 ){
					
					$sql .= ", ";
				}
			}
			return $sql;
		}
		return false;
	}
	
	/*
	* Validate the data for sql WHERE statement
	* @param [string] $table - the name of the targeted table
	* @param [array] $conditions - the data for the statement
	* @return [string] - WHERE statement of the SQL query
	*/
	protected function conditions( $table, $conditions ){
		
		// Allowed signs in a condition
		$signs = array('=', '!=', '<', '>', '<=', '>=');
		
		// If it's a condition
		if( count($conditions) === 3 ){
			
			// Checks the column_name in the condition
			if( $this->column( $table, $conditions['column'] ) === "" ) return false;
			
			// Checks the sign in the condition
			if( in_array( $conditions['sign'], $signs ) === false ) return false;
			
			// Add the value for binding under specific key
			$count = count( $this->_values );
			$key = ":val" . $count;
			$this->_values[$key] = $conditions['value'];
			
			// Returns the condition string
			return $conditions['column'] . " " . $conditions['sign'] . " " . $key;
		
		// If it's a set of conditions	
		}else if( count($conditions) === 2 ){
			
			// Checks the operator between the conditions
			if( $conditions['operator'] !== "AND" && $conditions['operator'] !== "OR" ) return false;
			
			// Start SQL qwery statement
			$sql = "( ";
			
			for( $a = 0; $a < count($conditions['conditions']); $a ++ ){
				
				// Makes a recursion for every condition
				$condition_return = $this->conditions( $table, $conditions['conditions'][$a] );
				
				// Returns false if fail
				if( $condition_return === false ) return false;
				
				// Adds the condition to the statement
				$sql .= $condition_return;
				
				// If it's not last condition
				if( $a < count($conditions['conditions']) - 1 ){
					
					// Adds the operator between the conditions
					$sql .= " " . $conditions['operator'] . " ";
				}
			}
			// Ends the SQL statement
			$sql .= " )";
			
			// Returns the WHERE statement
			return $sql;
		}
		return false;
	}
	
	/*
	* Validate the data for sql ORDER BY statement
	* @param [string] $table - the name of the targeted table
	* @param [array] $order - the data for the statement
	* @return [string] - ORDER BY statement of the SQL query
	*/
	public function order( $table, $order ){
		
		// If it's a string
		if( gettype($order) === gettype("") ){
			
			// Check if it's a valid column name
			if( $this->column( $table, $order ) === "" ) return false;
			
			return $order;
		
		// If it's an array	
		}else if( gettype($order) === gettype(array()) ){
			
			// Start SQL query statement
			$sql = "";
			
			// If it's only one order condition
			if( isset($order['column']) ){
				
				// Check if the column name is avialable
				if( $this->column( $table, $order['column'] ) === "" ) return false;
				
				// Add column name to the query statement string
				$sql .= $order['column'];
				
				// If is set specific order ( else ASC is default )
				if( isset($order['sc']) ){
					
					// Checks the order operator
					if( $order['sc'] !== "ASC" && $order['sc'] !== "DESC" ) return false;
					
					// Add the order operator
					$sql .= " " . $order['sc'];
				}
				// Returns the order condition
				return $sql;
			
			// If it's a set of order conditions	
			}else{
				
				for( $a = 0; $a < count( $order ); $a ++ ){
					
					// Makes a recursion for every order condition
					$result = $this->order( $table, $order[$a] );
					// Checks the result of the recursion
					if( $result === false ) return false;
					
					// Adds the order condition in the query statement string
					$sql .= $result;
					
					// If it's not last order condition, adds separator at the end
					if( $a < count( $order ) - 1 ) $sql .= ", ";
				}
				// Returns the ORDER BY statement striing
				return $sql;
			}
		}
		return false;
	}
	
	/*
	* Validate positive integer
	* @param [number] $int - value to check
	* @return [number] - returns the $int if successful, FALSE otherwise
	*/
	protected function positive_int($int){
		
		// Check if it's an integer
		if( gettype($int) === gettype(1) ){
			
			// Checks if it's a positive number
			if( $int >= 0 ) return $int;
		}
		return false;
	}
}
