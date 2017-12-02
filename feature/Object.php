<?php

// Keep working even USER LEAVE
ignore_user_abort(true);

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/HandlerFeature.class.php';
require_once __DIR__ . '/../core/STATIC/FILTER.class.php';

$config = array(

    CONSTS::get( 'action/insert' ) => array(

        'filter' => array(
            array(
                "id" => array(
                   "required" => true
               ),
               "user" => array(
                   "required" => true
               ),
               "space" => array(
                    "required" => true
               ),
               "presence" => array(
                    "required" => true
               ),
               "x" => array(
                    "required" => true
               ),
               "y" => array(
                    "required" => true
               ),
               "z" => array(
                    "required" => true
               ),
               "img" => array(
                   "required" => true
              ),
               "descr" => array(
                    "required" => true
               )
           ),
           array(
               "user" => array(
                    "data_type" => 'integer',
                   "min_number" => 1
               ),
               "space" => array(
                    "data_type" => 'integer',
                  "min_number" => 1
               ),
               "presence" => array(
                    "data_type" => 'integer',
                    "min_number" => 1
               ),
               "x" => array(
                   "data_type" => 'integer'
               ),
               "y" => array(
                   "data_type" => 'integer'
               ),
               "z" => array(
                   "data_type" => 'integer'
               ),
               "img" => FILTER::get( 'text/normal' ),
               "descr" => FILTER::get( 'text/required' )
           )
        ),

        'database' => array(
            'insert' => array(

                'table' => CONSTS::get( 'mysql/table/object' ),
                'values' => array(),
                'columns' => NULL
            )
        )
    ),

    CONSTS::get( 'action/update' ) => array(

        'filter' => array(
            array(
                "id" => array(
                    "data_type" => 'integer',
                    "min_number" => 1
                ),
                "user" => array(
                    "data_type" => 'integer',
                    "min_number" => 1
                ),
                "space" => array(
                    "data_type" => 'integer',
                    "min_number" => 1
                ),
                "presence" => array(
                    "data_type" => 'integer',
                    "min_number" => 1
                ),
                "code" => FILTER::get( 'text/required' ),
                "x" => array(
                    "data_type" => 'integer'
                ),
                "y" => array(
                    "data_type" => 'integer'
                ),
                "z" => array(
                    "data_type" => 'integer'
                ),
                "img" => FILTER::get( 'text/normal' ),
                "descr" => FILTER::get( 'text/required' )
            )
        ),
        'database' => array(

            'update' => array(

                'table' => CONSTS::get( 'mysql/table/object' ),
                'set' => 0,
                'conditions' => NULL
            )
        )
    ),
    CONSTS::get( 'action/delete' ) => array(

        'database' => array(

            'delete' => array(

                'table' => CONSTS::get( 'mysql/table/object' ),
                'conditions' => NULL
            )
        )
    )
);

new HandlerFeature( array_merge( $Config, $config ) );