<?php

// Keep working even USER LEAVE
ignore_user_abort(true);

require_once __DIR__ . '/../core/Session/Session.class.php';
require_once __DIR__ . '/../core/Input/Input.class.php';
require_once __DIR__ . '/../core/Validation/Validation.class.php';
require_once __DIR__ . '/../core/DB/DB_delete.class.php';
require_once __DIR__ . '/../core/STATIC/CONSTS.class.php';

$Config = array(

    CONSTS::get( 'session/key' ) => TRUE,

    'manager' => array(

        'session' => new Session(),

        'input' => new Input(),

        'database' => DB_delete::get_instance(),

        'validation' => new Validation()
    ),

    'input' => array(

       'get' => array(

           'post' => array(

               CONSTS::get( 'post/data' ) => 0

           )
       )
    ),

    'closeDatabase' => array(

        'disconnect' => TRUE
    ),

    'user' => array(

        'session' => array(
            'get' => array(
                CONSTS::get( 'session/user' ) => 0
            )
        ),

        'database' => array(
            'connect' => array(

               'host' => CONSTS::get( 'mysql/host' ),
               'user' => CONSTS::get( 'mysql/user' ),
               'pass' => CONSTS::get( 'mysql/pass' ),
               'db_name' => CONSTS::get( 'mysql/db_name/space' )
            ),
            'select' => array(

               'columns' => '*',
               'table' => CONSTS::get( 'mysql/table/permission' ),
               'conditions' => array(
                   'operator' => 'OR',
                   'conditions' => array(
                        array(
                            'column' => 'user',
                            'sign' => '=',
                            'value' => 0
                        ),
                        array(
                            'column' => 'user_all',
                            'sign' => '>',
                            'value' => 0
                        )
                    )
               ),
               'order' => NULL,
               'limit' => NULL,
               'offset' => NULL,
               'result_type' => 'assoc'
            )
        )
    ),

    CONSTS::get( 'action/select' ) => array(

        'database' => array(
            'select' => array(

               'columns' => '*',
               'table' => 0,
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
    )
);