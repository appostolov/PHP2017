<?php

class FEATURE{
	
	private static $consts = array(

	    'presence' => array(
            'insert' => 0,
            'select' => 0,
            'update' => 0,
            'delete' => 0
        ),
        'space' => array(
            'insert' => 0,
            'select' => 0,
            'update' => 0,
            'delete' => 0
        ),
        'object' => array(
            'insert' => 0,
            'select' => 0,
            'update' => 0,
            'delete' => 0
        ),
        'location' => array(
            'insert' => 0,
            'select' => 0,
            'update' => 0,
            'delete' => 0
        ),
         'user' => array(
             'select' => 0,
             'delete' => 0
         ),
           'permission' => array(
               'insert' => 0,
               'select' => 0,
               'update' => 0,
               'delete' => 0
           )
    );
	
	public static function get(){
		
		 return self::$consts;
	}
}