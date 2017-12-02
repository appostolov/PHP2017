<?php

require_once __DIR__ . '/DB_table_select.class.php';
require_once __DIR__ . '/../STATIC/REGEX.class.php';

/*
* Creating new SQL table
* @public [function] table_create - handle the SQL query and creates the table
* @protected [function] parse_row - validates the rows data
* @protected [function] valid_name - validate table and column names
*/
class DB_table_create extends DB_table_select{

	/*
	* Creating SQL table
	* @param [string] $columns - the columns of the table in the SELECT statement, from wich to select data
	* @param [array] $table - the table_name in the FROM statement, from wich to select columns
	* @return [boolean] TRUE if successful, FALSE otherwise
	*/
	public function table_create(
		$table_name,
		$rows
	){

		/*

		*** HOW TO USE ***

		table_create(

			'table_name',                      // the name of the SQL table

			array(                             // rows of the new table

				array(                         // row1

					'name' => 'column_name',   // column NAME
					'type' => 'data_type',     // data TYPE
					'length' => 'max_length',  // max LENGTH
					'not_null' => true,        // default VALUE
					'auto_increment' => true,  // AUTO_INCREMENT
					'primary_key' => true      // PRIMARY KEY
				),
				array(                         // row2

					'name' => 'column_name',   // column NAME
					'type' => 'data_type',     // data TYPE
					'length' => 'max_length'   // max LENGTH
				)
			)
		);
		*/

		// Resetting the main values ( error, values, result )
		$this->reset_values();

		// Validating the table name
		$_valid_name = $this->valid_name( $table_name );

		// If the name is not valid or already exists
		if( $_valid_name === false || $this->table( $table_name ) !== "" ){

			$this->set_error('table_create:bad_table_name');

			return false;
		}

		// Starting SQL query
		$sql = "CREATE TABLE " . $table_name . " ( ";

		for( $a = 0; $a < count($rows); $a ++){

			// Validate ROW data
			$row = $this->parse_row( $rows[$a] );

			if( $row === false ){

				$this->set_error('table_create:parse_row[' . $a . ']');

				return false;
			}

			// Add row data to the SQL query
			$sql .= $row;

			// Add separator if it's not last
			if( $a < count($rows) -1 ){

				$sql .= ", ";
			}
		}

		// Ends SQL query
		$sql .= " )";

		// PDO prepare() the SQL query
		if( $this->prepare( $sql )->error() !== false ){

			$this->set_error('table_create:prepare');

			return false;
		}

		// PDO bind values and execute() the SQL query
		if( $this->execute()->error() !== false ){

			$this->set_error('table_create:execute');

			return false;
		}
		
		// Reset databases, tablse and columns
		$this->set_tables_and_columns();
		
		return true;
	}

	/*
	* Validates table row data
	* @param [array] $row - the row data
	* @return [string] SQL query part with the row data
	*/
	protected function parse_row( $row ){

		// Validate row name
		$_valid_name = $this->valid_name( $row['name'] );

		if( $_valid_name === false ){

			$this->set_error('bad_column_name');

			return false;
		}

		// Start SQL query part
		$sql = $row['name'] . " ";

		// Data types allowed
		$types = array( "blob", "boolean", "float", "int", "text", "longtext", "varchar", "timestamp" );

		// Validate data type
		$valid_type = in_array( $row['type'] , $types );
		if( $valid_type === false ){

			$this->set_error('bad_column_type');

			return false;
		}

		// Adding data type to the SQL query part
		$sql .= $row['type'];

		// If max length is set
		if( isset($row['length']) === true ){

			// Max lengths
			$types_size = array(

				"int" => 11,
				"varchar" => 255

			);

			// If length needed
			if( $row['type'] === "int" || $row['type'] === "varchar" ){

				// Validate the lenght
				if( gettype($row['length']) === 'integer' && $row['length'] > 1 && $row['length'] <= $types_size[$row['type']] ){

					// Adding the data length into the SQl query part
					$sql .= "(" . $row['length'] . ")";
				}
			}
		}

		// If NOT NULL is set
		if( isset($row['not_null']) === true ){

			// Adding to the SQL query part
			$sql .= " NOT NULL";
		}

		// If AUTO_INCREMENT is set
		if( isset($row['auto_increment']) === true ){

			// Adding to the SQL query part
			$sql .= " AUTO_INCREMENT";
		}

		// If PRIMARY KEY is set
		if( isset($row['primary_key']) === true ){

			// Adding to the SQL query part
			$sql .= " PRIMARY KEY";
		}
		return $sql;
	}

	/*
	* Validate a name
	* @param [string] $name - table or column NAME
	* @return [boolean] TRUE if successful, FALSE otherwise
	*/
	protected function valid_name( $name ){
		
		// All chars letter, number or underscore
		$all = REGEX::regex_match( $name, "/^[a-zA-Z0-9_]+$/" );
		
		// Frst char is a letter
		$first = REGEX::regex_match( substr($name, 0, 1), "/^[a-zA-Z]+$/" );
		
		// If both TRUE
		if( $all === true && $first === true ){
			
			return true;
			
		}else{
			
			if( $all === false ){// Uncknown charachters

				$this->set_error('chars_not_allowed');
			}
	
			if( $first === false ){// First char not letter
	
				$this->set_error('start_whit_letter');
			}
		}
		return false;
	}
}