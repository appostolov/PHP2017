<?php

// Keep working even USER LEAVE
ignore_user_abort(true);

require_once __DIR__ . '/../core/Validation/Validation.class.php';
require_once __DIR__ . '/../core/Session/Session.class.php';
require_once __DIR__ . '/../core/Input/Input.class.php';
require_once __DIR__ . '/../core/DB/DB_delete.class.php';
require_once __DIR__ . '/../core/Cookie/Cookie.class.php';
require_once __DIR__ . '/HandlerUser.class.php';
require_once __DIR__ . '/../core/STATIC/CONSTS.class.php';

function get_action(){

    if( isset( $_POST[CONSTS::get( 'post/email' )] ) ){

        return 'create';

    }else if( isset( $_POST[CONSTS::get( 'post/activate' )] ) ){

        return 'activate';

    }else if( isset( $_POST[CONSTS::get( 'post/generate' )] ) ){

        return 'generate';

    }else if( isset( $_POST[CONSTS::get( 'post/logout' )] ) ){

         return 'logout';

    }else if( isset( $_POST[CONSTS::get( 'post/auto' )] ) ){

        return 'auto';

    }else return 'login';
}

$user_filter = array(
    "user" => array(
        'data_type' => 'string',
        'different' => 'c',
        "min_length" => 1,
        "max_length" => 100,
        "allowed" => '/^[a-zA-Z0-9 _.-]*$/'
    )
);

$pass_filter = array(
    "pass" => array(
        'data_type' => 'string',
        "min_length" => 6,
        "max_length" => 100,
        "allowed" => '/^[a-zA-Z0-9_.-]*$/'
    )
);

$config = array(

    'action' => get_action(),

    CONSTS::get( 'session/key' ) => TRUE,

    'manager' => array(

        'validation' => new Validation(),

        'session' => new Session(),

        'input' => new Input(),

        'database' => DB_delete::get_instance(),

        'cookie' => Cookie::get_instance()
    ),

    'closeDatabase' => array(

        'disconnect' => TRUE
    ),

    'permissions' => array(
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

    'create' => array(

        'filter' => array(

            array(
                "user" => array(
                    "required" => true
                ),
                "pass" => array(
                    "required" => true
                ),
                "email" => array(
                    "required" => true
                )
            ),
            $user_filter,
            $pass_filter,
            array(
                "email" => array(
                    "filter" => FILTER_VALIDATE_EMAIL
                )
            )
        ),

        'input1' => array(

            'get' => array(

                'post' => array(

                    CONSTS::get( 'post/user' ) => 0,

                    CONSTS::get( 'post/pass' ) => 0,

                    CONSTS::get( 'post/email' ) => 0
                )
            )
        ),

        'database1' => array(
            'connect' => array(

                'host' => CONSTS::get( 'mysql/host' ),
                'user' => CONSTS::get( 'mysql/user' ),
                'pass' => CONSTS::get( 'mysql/pass' ),
                'db_name' => CONSTS::get( 'mysql/db_name/user' )
            ),
            'select' => array(

                'columns' => '*',
                'table' => CONSTS::get( 'mysql/table/user' ),
                'conditions' => array(

                    'operator' => 'OR',

                    'conditions' => array(

                        array(

                            'column' => 'name',
                            'sign' => '=',
                            'value' => 0
                        ),
                        array(

                            'column' => 'email',
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

        'database2' => array(
            'insert' => array(

                'table' => CONSTS::get( 'mysql/table/user' ),
                'values' => array(
                    NULL,
                    'name',
                    'pass',
                    'salt',
                    'email',
                    0,
                    time()
                ),
                'columns' => NULL
            ),
            'select' => array(

                'columns' => '*',
                'table' => CONSTS::get( 'mysql/table/user' ),
                'conditions' => array(
                    'column' => 'name',
                    'sign' => '=',
                    'value' => 0
                ),
                'order' => NULL,
                'limit' => NULL,
                'offset' => NULL,
                'result_type' => 'assoc'
            )
        ),
        'database3' => array(
            'insert' => array(

                'table' => CONSTS::get( 'mysql/table/key' ),
                'values' => array(
                    0,
                    0
                ),
                'columns' => NULL
            )
        )
    ),

    'login' => array(

        'filter' => array(

           array(
               "user" => array(
                   "required" => true
               ),
               "pass" => array(
                   "required" => true
               )
           ),
           $user_filter,
           $pass_filter
        ),

        'input1' => array(

           'get' => array(

               'post' => array(

                   CONSTS::get( 'post/user' ) => 0,

                   CONSTS::get( 'post/pass' ) => 0,

                   CONSTS::get( 'post/remember' ) => 0
               )
           )
        ),

        'database1' => array(
           'connect' => array(

               'host' => CONSTS::get( 'mysql/host' ),
               'user' => CONSTS::get( 'mysql/user' ),
               'pass' => CONSTS::get( 'mysql/pass' ),
               'db_name' => CONSTS::get( 'mysql/db_name/user' )
           ),
           'select' => array(

               'columns' => '*',
               'table' => CONSTS::get( 'mysql/table/user' ),
               'conditions' => array(
                   'column' => 'name',
                   'sign' => '=',
                   'value' => 0
               ),
               'order' => NULL,
               'limit' => NULL,
               'offset' => NULL,
               'result_type' => 'assoc'
           )
        ),
        'session1' => array(
            'set' => array(
                CONSTS::get( 'session/user' ) => 0
            )
        ),
        'cookie1' => array(
            'set' =>array(
                'name' => CONSTS::get( 'cookie/user/name' ),
                'value' => 0,
                'expire' => time() + CONSTS::get( 'cookie/user/lifetime' ),
                'path' => '/'
            )
        )
    ),

    'auto' => array(

        'session1' => array(
            'get' => array(
                CONSTS::get( 'session/user' ) => 0
            )
        ),

        'filter' => array(

           array(
               "credentials" => array(
                   "required" => true,
               )
           ),
           array(
               "credentials" => array(
                   "data_type" => "string",
                   "min_length" => 34,
                   "allowed" => '/^[a-zA-Z0-9, _.-]*$/'
               )
           )

        ),

        'filter1' => array(

           array(
               "user" => array(
                   "required" => true,
               ),
               "pass_hash" => array(
                   "required" => true,
               )
           ),
           $user_filter,
           array(
                "pass_hash" => array(
                    "data_type" => 'string',
                    'allowed' => '/^[a-f0-9]{32}$/i'
                )
            )

        ),

        'cookie1' => array(
            'get' =>array(
                'name' => CONSTS::get( 'cookie/user/name' )
            )
        ),

        'database1' => array(
           'connect' => array(

               'host' => CONSTS::get( 'mysql/host' ),
               'user' => CONSTS::get( 'mysql/user' ),
               'pass' => CONSTS::get( 'mysql/pass' ),
               'db_name' => CONSTS::get( 'mysql/db_name/user' )
           ),
           'select' => array(

               'columns' => '*',
               'table' => CONSTS::get( 'mysql/table/user' ),
               'conditions' => array(
                   'column' => 'name',
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

    'activate' => array(

          'filter' => array(

              array(
                  "user" => array(
                      "required" => true
                  ),
                  "pass" => array(
                      "required" => true
                  ),
                  "activate" => array(
                      "required" => true
                  )
              ),
              $user_filter,
              $pass_filter,
              array(
                  "activate" => array(
                      "data_type" => 'string',
                      'allowed' => '/^[a-f0-9]{32}$/i'
                  )
              )
          ),

          'input1' => array(

              'get' => array(

                  'post' => array(

                      CONSTS::get( 'post/user' ) => 0,

                      CONSTS::get( 'post/pass' ) => 0,

                      CONSTS::get( 'post/activate' ) => 0
                  )
              )
          ),

          'database2' => array(
              'select' => array(

                  'columns' => '*',
                  'table' => CONSTS::get( 'mysql/table/key' ),
                  'conditions' => array(
                      'column' => 'user',
                      'sign' => '=',
                      'value' => 0
                  ),
                  'order' => NULL,
                  'limit' => NULL,
                  'offset' => NULL,
                  'result_type' => 'assoc'
              )
          ),
          'database3' => array(
              'update' => array(

                  'table' => CONSTS::get( 'mysql/table/user' ),
                  'set' => array(

                      "column" => "active",
                      "value" => 1
                  ),
                  'conditions' => array(

                      'column' => 'id',
                      'sign' => '=',
                      'value' => 0
                  )
              ),
              'delete' => array(

                    'table' => CONSTS::get( 'mysql/table/key' ),
                    'conditions' => array(

                      'column' => 'user',
                      'sign' => '=',
                      'value' => 0
                    )
              )
          )
      ),

      'generate' => array(

            'filter' => array(

               array(
                   "user" => array(
                       "required" => true
                   ),
                   "pass" => array(
                       "required" => true
                   )
               ),
               $user_filter,
               $pass_filter
            ),

            'input1' => array(

               'get' => array(

                   'post' => array(

                       CONSTS::get( 'post/user' ) => 0,

                       CONSTS::get( 'post/pass' ) => 0
                   )
               )
            ),

            'database2' => array(
                'select' => array(

                    'columns' => '*',
                    'table' => CONSTS::get( 'mysql/table/key' ),
                    'conditions' => array(
                        'column' => 'user',
                        'sign' => '=',
                        'value' => 0
                    ),
                    'order' => NULL,
                    'limit' => NULL,
                    'offset' => NULL,
                    'result_type' => 'assoc'
                )
            ),
            'database3' => array(
                'insert' => array(

                    'table' => CONSTS::get( 'mysql/table/key' ),
                    'values' => array(

                        0,
                        0
                    ),
                    'columns' => NULL
                )
            ),
            'database4' => array(
                'update' => array(

                    'table' => CONSTS::get( 'mysql/table/key' ),
                    'set' => array(

                        "column" => CONSTS::get( 'mysql/table/key' ),
                        "value" => 0
                    ),
                    'conditions' => array(

                        'column' => CONSTS::get( 'mysql/table/user' ),
                        'sign' => '=',
                        'value' => 0
                    )
                )
            )
        )
);
new HandlerUser( $config );