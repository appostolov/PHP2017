<?php

require_once __DIR__ . '/CONFIG.class.php';

class CONSTS{
	
	private static $consts = array(

	    "mysql" => array(
	        "host" => "localhost",
	        'user' => 'Your mysql user',
	        'pass' => 'Your mysql pass',
	        'db_name' => array(
	            "user" => "user",
	            "visit" => "track",
	            "space" => "3Dspace"
	        ),
	        "table" => array(
	            //USER
	            "user" => "user",
	            "key" => "activation_key",
	            //VISIT
	            "page" => "page",
	            "visitor" => "visitor",
	            "visit" => "visit",
	            "ad" => "ad",
	            "click" => "click",
	            //SPACE
	            "space" => "space",
	            "object" => "object",
	            "presence" => "presence",
	            "location" => "location",
	            "permission" => "permission"
	        )
	    ),

	    "post" => array(
	        // USER
            'user' => 'user',
            'pass' => 'pass',
            'email' => 'email',
            'activate' => 'activate',
            'generate' => 'generate',
            'remember' => 'remember',
            'logout' => 'logout',
            'auto' => 'auto',
            //VISIT
            'url' => 'url',
            'ad' => 'ad',
            'visit' => 'visit',
            //FEATURE
            "data" => "data"
	    ),

	    "session" => array(
	        "key" => "key",
	        "user" => "user",
	        "visit" => "visit"
	    ),

	    "cookie" => array(
	        "user" => array(
                'name' => 'user_login',
                'lifetime' => 2592000,// 30 days
                'separator' => ","
	        )
	    ),

	    "action" => array(
	        "select" => "select",
	        "insert" => "insert",
	        "update" => "update",
	        "delete" => "delete"
	    ),

        'object' => array(
            'range' => array(
                'min' => 50000,
                'max' => 200000
            )
        )
    );
	
	public static function get( $path ){
		
		 return CONFIG::get( self::$consts, $path );
	}
}