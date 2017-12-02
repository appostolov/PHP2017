<?php

// Keep working even USER LEAVE
ignore_user_abort(true);

require_once __DIR__ . '/../core/Session/Session.class.php';
require_once __DIR__ . '/../core/Input/Input.class.php';
require_once __DIR__ . '/../core/DB/DB_update.class.php';
require_once __DIR__ . '/HandlerVisit.class.php';
require_once __DIR__ . '/../core/STATIC/CONSTS.class.php';

$config = array(

    CONSTS::get( 'session/key' ) => ( isset( $_POST[CONSTS::get( 'post/visit' )] ) === TRUE ) ? FALSE : TRUE,

    'manager' => array(

        'session' => new Session(),

        'input' => new Input(),

        'database' => DB_update::get_instance()
    ),

    'closeDatabase' => array(

        'disconnect' => TRUE
    ),

    'Page' => array(

        'input' => array(

            'get' => array(

                'post' => array(

                    CONSTS::get( 'post/url' ) => 0
                )
            )
        ),

        'database' => array(

            'connect' => array(

                'host' => CONSTS::get( 'visit/mysql/host' ),
                'user' => CONSTS::get( 'visit/mysql/user' ),
                'pass' => CONSTS::get( 'visit/mysql/pass' ),
                'db_name' => CONSTS::get( 'mysql/db_name/visit' )
            ),
            'select' => array(

                'columns' => '*',
                'table' => CONSTS::get( 'mysql/table/page' ),
                'conditions' => array(
                    'column' => 'url',
                    'sign' => '=',
                    'value' => 0
                ),
                'order' => NULL,
                'limit' => NULL,
                'offset' => NULL,
                'result_type' => 'assoc'
            )
        )
    ),

    'Visitor' => array(

        'database1' => array(

            'select' => array(

                'columns' => '*',
                'table' => CONSTS::get( 'mysql/table/visitor' ),
                'conditions' => array(

                    'operator' => 'AND',

                    'conditions' => array(

                        array(

                            'column' => 'userAgent',
                            'sign' => '=',
                            'value' => $_SERVER ['HTTP_USER_AGENT']
                        ),
                        array(

                            'column' => 'IP',
                            'sign' => '=',
                            'value' => $_SERVER ['REMOTE_ADDR']
                        )
                    )
                ),
                'order' => NULL,
                'limit' => NULL,
                'offset' => NULL,
                'result_type' => 'assoc'
            )
        ),

        'database2' => array(

            'insert' => array(

                'table' => CONSTS::get( 'mysql/table/visitor' ),
                'values' => array(

                    NULL,
                    $_SERVER ['HTTP_USER_AGENT'],
                    $_SERVER ['REMOTE_ADDR'],
                    time()
                ),
                'columns' => NULL
            )
        ),

        'database3' => array(

            'select' => array(

                'columns' => '*',
                'table' => CONSTS::get( 'mysql/table/visitor' ),
                'conditions' => array(

                    'operator' => 'AND',

                    'conditions' => array(

                        array(

                            'column' => 'userAgent',
                            'sign' => '=',
                            'value' => $_SERVER ['HTTP_USER_AGENT']
                        ),
                        array(

                            'column' => 'IP',
                            'sign' => '=',
                            'value' => $_SERVER ['REMOTE_ADDR']
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
    'Visit' => array(

        'session' => array(

            'get' => array(

                CONSTS::get( 'session/visit' ) => 0
            )
        ),

        'database' => array(

            'insert' => array(

                'table' => CONSTS::get( 'mysql/table/visit' ),
                'values' => array(

                    NULL,
                    0,
                    0,
                    0,
                    time(),
                    0
                ),
                'columns' => NULL
            )
        ),

        'database1' => array(

            'select' => array(

                'columns' => '*',
                'table' => CONSTS::get( 'mysql/table/visit' ),
                'conditions' => array(

                    'operator' => 'AND',

                    'conditions' => array(

                        array(

                            'column' => 'page',
                            'sign' => '=',
                            'value' => 0
                        ),
                        array(

                            'column' => 'visitor',
                            'sign' => '=',
                            'value' => 0
                        ),
                        array(

                            'column' => 'start',
                            'sign' => '=',
                            'value' => 0
                        )
                    )
                ),
                'order' => NULL,
                'limit' => NULL,
                'offset' => NULL,
                'result_type' => 'assoc'
            )
        ),

        'session1' => array(

            'set' => array(

                CONSTS::get( 'session/visit' ) => 0
            )
        ),
    ),

    'Click' => array(

        'input' => array(

            'get' => array(

                'post' => array(

                    CONSTS::get( 'post/ad' ) => 0
                )
            )
        ),

        'database1' => array(

            'select' => array(

                'columns' => '*',
                'table' => CONSTS::get( 'mysql/table/ad' ),
                'conditions' => array(

                    'column' => 'id',
                    'sign' => '=',
                    'value' => 0
                ),
                'order' => NULL,
                'limit' => NULL,
                'offset' => NULL,
                'result_type' => 'assoc'
            )
        ),

        'database2' => array(

            'insert' => array(

                'table' => CONSTS::get( 'mysql/table/click' ),
                'values' => array(

                    NULL,
                    0,
                    0
                ),
                'columns' => NULL
            )
        )
    ),

    'End' => array(

        'input' => array(

            'get' => array(

                'post' => array(

                    CONSTS::get( 'post/visit' ) => 0
                )
            )
        ),

        'database1' => array(

            'connect' => array(

                'host' => CONSTS::get( 'visit/mysql/host' ),
                'user' => CONSTS::get( 'visit/mysql/user' ),
                'pass' => CONSTS::get( 'visit/mysql/pass' ),
                'db_name' => CONSTS::get( 'mysql/db_name/visit' )
            ),

            'select' => array(

                'columns' => '*',
                'table' => CONSTS::get( 'mysql/table/visit' ),
                'conditions' => array(

                    'column' => 'id',
                    'sign' => '=',
                    'value' => 0
                ),
                'order' => NULL,
                'limit' => NULL,
                'offset' => NULL,
                'result_type' => 'assoc'
            )
        ),

        'database2' => array(

            'update' => array(

                'table' => CONSTS::get( 'mysql/table/visit' ),
                'set' => array(

                    "column" => "end",
                    "value" => time()
                ),
                'conditions' => array(

                    'column' => 'id',
                    'sign' => '=',
                    'value' => 0
                )
            )
        )
    )
);

new HandlerVisit( $config );