<?php

// Keep working even USER LEAVE
ignore_user_abort(true);

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/HandlerFeatureUser.class.php';

$config = array(

    CONSTS::get( 'action/select' ) => array(

        'database' => array(
            'connect' => array(

               'host' => CONSTS::get( 'mysql/host' ),
               'user' => CONSTS::get( 'mysql/user' ),
               'pass' => CONSTS::get( 'mysql/pass' ),
               'db_name' => CONSTS::get( 'mysql/db_name/user' )
            ),
            'select' => array(

               'columns' => array( 'id', 'name', 'email', 'active', 'created' ),
               'table' => CONSTS::get( 'mysql/table/user' ),
               'conditions' => NULL,
               'order' => array(
                    'sc' => 'ASC',
                    'column' => 'id'
               ),
               'limit' => NULL,
               'offset' => NULL,
               'result_type' => 'assoc'
            )
        )
    ),
    CONSTS::get( 'action/delete' ) => array(

        'database' => array(

            'connect' => array(

               'host' => CONSTS::get( 'mysql/host' ),
               'user' => CONSTS::get( 'mysql/user' ),
               'pass' => CONSTS::get( 'mysql/pass' ),
               'db_name' => CONSTS::get( 'mysql/db_name/user' )
            ),

            'delete' => array(

                'table' => CONSTS::get( 'mysql/table/user' ),
                'conditions' => NULL
            )
        )
    )
);



new HandlerFeatureUser( array_merge( $Config, $config ) );