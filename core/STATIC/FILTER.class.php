<?php

require_once __DIR__ . '/CONFIG.class.php';

class FILTER{
	
	private static $consts = array(

	    'text' => array(

	        'normal' => array(

               "data_type" => "string",
               "max_length" => 1000
            ),

            'required' => array(

               "data_type" => "string",
               "min_length" => 5,
               "max_length" => 1000
            )
         )
    );
	
	public static function get( $path ){

		 return CONFIG::get( self::$consts, $path );
	}
}