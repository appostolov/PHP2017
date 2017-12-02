<?php

require_once __DIR__ . '/../Patterns/Singleton.class.php';

/*
 * Implement connection to database server through PDO
 * @private [object] $_connection - holds the connection to the databse server
 * @private [string] $_host - the IP of the database server to connect
 * @private [string] $_db_name - the name of the database to connect
 * @private [string] $_user - the user for connection
 * @private [string] $_pass - the password of the user for connection
 * @private [array] $_databases - databases avialable on the server
 * @private [array] $_tables - the tables in the database
 * @private [array] $_columns - the columns in the table
 * @private [string] $_error - holds error message from the last operation, or FALSE if no errors
 * @private [array] $_result - holds the result of successful query
 * @public [function] connect - connects to the database
 * @public [function] disconnect - close the connection to database server
 * @public [function] get_connection_data - getting the data used for the current database connection
 * @private [function] _set_connection_data - sets the connection data with the current credentials
 * @public [function] error - returns the current $_error
 * @public [function] set_error - sets the error $_error
 * @private [function] _set_tables - get the table_names and set $_tables
 * @protected [function] table - check if table is avialable
 * @private [function] _set_columns - get the column_names and set $_columns
 * @protected [function] column - check if column is avialable
 * @protected [function] prepare - PDO prepare()
 * @protected [function] bind - PDO bindValue()
 * @protected [function] execute - PDO execute()
 * @protected [function] result - returns the result from execute()
 * @public [function] rowCount - gets the sql result rowCount
*/
class DB extends Singleton{

	private $_connection = NULL;
	
	private $_host = NULL;
	private $_db_name = NULL;
	private $_user = NULL;
	private $_pass = NULL;
	
	private $_databases = NULL;
	private $_tables = NULL;
	private $_columns = NULL;
	
	private $_error = false;
	
	private $_result = NULL;

	/*
	* Applies for database server connection, if successful keeps the connection and keeps the data for connecting.
	* @param [string] $host - the IP of the database server connecting to
	* @param [string] $db_name - database name to connect
	* @param [string] $user - user for connecting to
	* @param [string] $pass - password of the user for connect
	* @return [object] - returns the object it self
	*/
	public function connect(
		$host,
		$user,
		$pass,
		$db_name = NULL
	){
		
		// Resetting the error
		$this->set_error( false );

		if( NULL === $db_name ){

			// Query for connecting server through PDO
			$dsn = "mysql:host=" . $host;

		}else{

			// Query for connecting database through PDO
			$dsn = 'mysql:host=' . $host . ';dbname=' . $db_name;
		}
		
		$dsn .= ';charset=utf8';

		// Close the connection to database
		$this->disconnect();

		try {

			// Creating a new PDO connection with database
			$this->_connection = new PDO($dsn, $user, $pass);

			// Setting PDO driver options
			$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		} catch (PDOException $e) {

			// Close the connection to database
			$this->disconnect();

			// Sets the error message
			$this->set_error($e->getMessage());
			
			// Resetting the $_result
			$this->_result = NULL;

			// Returns the object itself
			return $this;
		}

		// Saving the data for connecting database
		$this->_set_connection_data( $host, $db_name, $user, $pass );
			
		// Setting tables and columns avialable
		$this->set_tables_and_columns();

		// Returns the object itself
		return $this;
	}

	/*
	* Close the database connection
	*/
	public function disconnect(){

		// Close the connection to database
		$this->_connection = NULL;

		// Resetting the data for connecting database
		return $this->_set_connection_data( NULL, NULL, NULL, NULL );
	}

	/*
	* Getting the current data for connecting database.
	* @return [array] $data - the current data for connecting database
	*/
	public function get_connection_data(){

		$data = array(
			'host' => $this->_host,
			'db_name' => $this->_db_name,
			'user' => $this->_user
		);

		return $data;
	}

	/*
	* Setting the connection data
	* @param [string] $host - the IP of the database server connecting to
	* @param [string] $db_name - database name to connect
	* @param [string] $user - user for connecting to
	* @param [string] $pass - password of the user for connect
	* @return [object] - returns the object it self
	*/
	private function _set_connection_data( $host, $db_name, $user, $pass ){

		// Reseting the connection data
		$this->_host = $host;
		$this->_db_name = $db_name;
		$this->_user = $user;
		$this->_pass = $pass;

		// Returns the object itself
		return $this;
	}

	/*
	* Returns the error from the last operation, or FLASE if there is no error.
	*/
	public function error(){

		return $this->_error;
	}
	
	protected function reset_values(){
		
		// Resetting the error
		$this->set_error( false );
		
		// Reset the values for binding
		$this->_values = array();
		
		// Reset the operation result
		$this->_result = NULL;
	}
	
	/*
	* Setting the error
	* @param [string] $error - the current error
	*/
	protected function set_error( $error ){
		
		$message = $error;
		
		// Resetting the error
		if( $message === false ){
			
			$this->_error = $message;
			
			return;
		
		// If it's a string	
		}else if( gettype( "" ) === gettype( $message ) ){
			
			// If the error is not empty
			if( $this->error() !== false ){
			
				$message .= ':' . $this->_error;
			}
			$this->_error = $message;
		}
	}
	
	private function _set_databases(){
		
		try {
			
			// Getting tha table_names from tha database
			$databases = $this->_connection->query("show databases")->fetchAll(PDO::FETCH_NUM);

		} catch (PDOException $e) {

			// Sets the error message
			$this->set_error($e->getMessage());
			
			// Reset the tables
			$this->_databases = NULL;
			
			return false;
		}
		
		// Reset the databases
		$this->_databases = array();
		
		foreach( $databases as $database){
			
			// Adding a table name
			array_push( $this->_databases, $database[0] );
		}
		
		return true;
	}
	
	protected function database( $database ){
		
		// Check if the tables array contains the table_name
		if( in_array( $database, $this->_databases, TRUE ) ) return $database;
		
		else return "";
	}
	
	/*
	* Setting the tables avialable
	* @return [boolean] - returns TRUE if success, FALSE otherwise
	*/
	private function _set_tables(){
		
		// Checks if database is avialable
		if( $this->_db_name === NULL ) return false;
		
		try {
			
			// Getting tha table_names from tha database
			$tables = $this->_connection->query("show tables")->fetchAll(PDO::FETCH_NUM);

		} catch (PDOException $e) {

			// Sets the error message
			$this->set_error($e->getMessage());
			
			// Reset the tables
			$this->_tables = NULL;
			
			return false;
		}
		
		// Reset the tables
		$this->_tables = array();
		
		foreach( $tables as $table){
			
			// Adding a table name
			array_push( $this->_tables, $table[0] );
		}
		
		return true;
	}
	
	/*
	* Checks it table is avialable
	* @param [string] $table - table_name for check
	* @return [string] - returns the table_name if successful, empty string otherwise
	*/
	protected function table( $table ){
		
		// Check if the tables array contains the table_name
		if( in_array( $table, $this->_tables, TRUE ) ) return $table;
		
		else return "";
	}
	
	/*
	* Setting the columns avialable
	* @return [boolean] - returns TRUE if success, FALSE otherwise
	*/
	private function _set_columns(){
		
		if( $this->_tables === NULL ) return false;
		
		// Reset the columns
		$this->_columns = array();
		
		foreach( $this->_tables as $table ){
			
			try {
				
				// Get column_names from the table
				$result = $this->_connection->query("DESCRIBE " . $table)->fetchAll(PDO::FETCH_NUM);
	
			} catch (PDOException $e) {
	
				// Sets the error message
				$this->set_error($e->getMessage());
				
				// Reset the columns
				$this->_columns = NULL;
				
				return false;
			}
			
			// Holds only column names
			$result_columns = array();
		
			foreach( $result as $column){
				
				// Adding column name
				array_push( $result_columns, $column[0] );
			}
			
			// Setting column names
			$this->_columns[ $table ] = $result_columns;
		}
		return true;
	}
	
	/*
	* Checks it column is avialable
	* @param [string] $table - table_name for check
	* @param [string] $column - column_name for check
	* @return [string] - returns the column_name if successful, empty string otherwise
	*/
	protected function column( $table, $column ){
		
		if( in_array( $column, $this->_columns[$table], TRUE ) ) return $column;
		
		else return "";
	}
	
	protected function set_tables_and_columns(){
		
		// Setting databases avialable
		$this->_set_databases();
		
		// Setting tables avialable
		$this->_set_tables();
		
		// Setting columns avialable
		$this->_set_columns();
	}
	
	/*
	* Preparing an SQL statement for executing
	* @param [string] $sql - valid SQL statement
	* @param [srray] $options - PDO driver options
	* @return [object] - returns the object it self
	*/
	protected function prepare( $sql, $options = array() ){

		try {
			
			// PDO prepare
			$this->_result = $this->_connection->prepare( $sql, $options );

		} catch (PDOException $e) {

			// Sets the error message
			$this->set_error($e->getMessage());
			
			// Resetting the $_result
			$this->_result = NULL;

			// Returns the object itself
			return $this;
		}

		// Returns the object itself
		return $this;
	}
	
	/*
	* Binding values to the SQL statement parameters
	* @param [array] $vals - arrays for every Query parameter with key, value and type
	* @return [object] - returns the object it self
	*/
	protected function bind( $vals ){
		
		foreach( $vals as $val){
			
			// PDO bindValue(...)
			$this->_result->bindValue( $val['key'], $val['value'], $val['type'] );
		}
		// Returns the object itself
		return $this;
	}
	
	/*
	* Executing the prepared SQL query
	* @param [array] - bind values for the prepared query
	* @return [object] - returns the object it self
	*/
	protected function execute( $bind = array() ){

		try {
			
			if( count($bind) > 0 ){
				
				// PDO execute() with bind array
				$this->_result->execute( $bind );
				
			}else{
				
				// PDO execute()
				$this->_result->execute();
			}
			

		} catch (PDOException $e) {

			// Sets the error message
			$this->set_error($e->getMessage());
			
			// Resetting the $_result
			$this->_result = NULL;

			// Returns the object itself
			return $this;
		}

		// Returns the object itself
		return $this;
	}
	
	/*
	* Returns the result from the SQL query in the desired form
	* @param [string] $return_type - data structure for the result
	* @return [object] - returns the result form the SQL query or FALSE
	*/
	protected function result( $return_type ){
		
		// Checks for errors
		if( $this->error() ) return false;

		switch ($return_type){

			case 'num':
			
				// Numeric array
				return $this->_result->fetchAll(PDO::FETCH_NUM);

			case 'assoc':
			
				// Associetive array
				return $this->_result->fetchAll(PDO::FETCH_ASSOC);

			case 'obj':
			
				// Object
				return $this->_result->fetchAll(PDO::FETCH_OBJ);
				
			default:
				return false;
		}
	}
	
	/*
	* Returns the count of the rows from the result
	* @return [number] - row count
	*/
	public function rowCount(){
		
		// PDO rowCount()
		return $this->_result->rowCount();
	}
}