<?php

require_once __DIR__ . '/../core/Debug/Debug.class.php';
require_once __DIR__ . '/../core/STATIC/STRINGS.class.php';
require_once __DIR__ . '/../core/STATIC/CONSTS.class.php';

/*
* This class handles the USER's functionality
*
* @protected [function] construct_Root - its invoked in the parents __construct, switch between actions and handles result of the action
* @protected [function] Create - creates new user
* @protected [function] Login - handle the user's login, and keep logged in
* @protected [function] autoLogin - checks if user logged in, if not, gets credentials from $_COOKIE and logs in
* @protected [function] Activate - improve / activate the created user
* @protected [function] Generate - GENERATES new activation KEY
* @protected [function] Credentials - check the user's post data credentials
* @protected [function] salt - generate new salt
* @protected [function] hash - produce hash from pass and salt
* @protected [function] no_arr_count - checks if variable is not array or has count 0
*/
class HandlerUser extends Debug{

    protected function construct_Root(){

        $user = FALSE;

        if( $this->Credentials( $this->get( 'action' ) ) ){

            switch( $this->get( 'action' ) ){

                case 'create':

                    $user = $this->Create();
                    break;


                case 'activate':

                    $user = $this->Activate();
                    break;


                case 'login':

                    $user = $this->Login();
                    break;

                case 'generate':

                    $user = $this->Generate();
                    break;

                case 'logout':

                    $this->get( 'manager/cookie' )->remove( CONSTS::get( 'cookie/user/name' ) );
                    $this->get( 'manager/session' )->remove( CONSTS::get( 'session/user' ) );

                    break;

                case 'auto':

                    $user = $this->autoLogin();
                    break;
            }
        }
        if( $user !== FALSE && $user['id'] > 0 && strlen( $this->debug_string() ) === 0 ){

            if( $this->get( 'action' ) === 'login' || $this->get( 'action' ) === 'auto' ){

                $this->set(
                    'login/session1/set/' . CONSTS::get( 'session/user' ),
                    $user['id']
                );
                $this->Session( 'login/session1' );
            }
        }
        if( !$user ) $user = array( 'id' => 0 );
        unset( $user["pass"] );
        unset( $user["salt"] );
        $permissions = $this->getPermissions( $user['id'] );
        if( $permissions ) $user['permissions'] = $permissions;

        $this->Database( 'closeDatabase' );

        die( json_encode( $user ) );
    }

    protected function getPermissions( $id ){

        $this->set(
            'permissions/database/select/conditions/conditions/0/value',
             $id
        );
        $this->Database( 'permissions/database' );

        if( $this->get( 'permissions/database/connect/error' ) !== FALSE ) return $this->debug_add( 'getPermissions: SQL connection ERROR!' );

        if( $this->get( 'permissions/database/select/error' ) !== FALSE ) return $this->debug_add( 'getPermissions: SQL select ERROR!' );

        if( $this->no_arr_count( $this->get( 'permissions/database/select/result' ) ) ) return $this->debug_add( 'getPermissions: NO permissions!' );

        return $this->get( 'permissions/database/select/result' );
    }

    protected function Create(){

        $this->set(
            CONSTS::get( 'post/email' ),
            $this->get(
                'create/input1/get/result/post/'. CONSTS::get( 'post/email' )
            )
        );

        $this->set(
            'create/database1/select/conditions/conditions/0/value',
             $this->get( CONSTS::get( 'post/user' ) )
        );

        $this->set(
            'create/database1/select/conditions/conditions/1/value',
             $this->get( CONSTS::get( 'post/email' ) )
        );

        $this->Database( 'create/database1' );

        if( $this->get( 'create/database1/connect/error' ) !== FALSE ) return $this->debug_add( 'Create: SQL connection ERROR!' );

        if( $this->get( 'create/database1/select/error' ) !== FALSE ) return $this->debug_add( 'Create: SQL select ERROR!' );

        if( !$this->no_arr_count( $this->get( 'create/database1/select/result' ) ) ) return $this->debug_add( 'Create: User EXITS!' );

        $this->set( 'salt', $this->salt() );

        $this->set( 'create/database2/insert/values/1', $this->get( CONSTS::get( 'post/user' ) ) );
        $this->set( 'create/database2/insert/values/2', $this->hash( $this->get( CONSTS::get( 'post/pass' ) ), $this->get( 'salt' ) ) );
        $this->set( 'create/database2/insert/values/3', $this->get( 'salt' ) );
        $this->set( 'create/database2/insert/values/4', $this->get( CONSTS::get( 'post/email' ) ) );

        $this->set(
            'create/database2/select/conditions/value',
             $this->get( CONSTS::get( 'post/user' ) )
        );

        $this->Database( 'create/database2' );

        if( $this->get( 'create/database2/insert/error' ) !== FALSE ) return $this->debug_add( 'Create: New USER INSERT error!' );

        if( $this->get( 'create/database2/select/error' ) !== FALSE ) return $this->debug_add( 'Create: New USER SELECT error!' );

        if( $this->no_arr_count( $this->get( 'create/database2/select/result' ) ) ) return $this->debug_add( 'Create: NO USER result!' );

        $this->set( 'create/database3/insert/values/0', $this->get( 'create/database2/select/result/0/id' ) );
        $this->set( 'create/database3/insert/values/1', $this->salt() );

        $this->Database( 'create/database3' );

        if( $this->get( 'create/database3/insert/error' ) !== FALSE ) return $this->debug_add( 'Create: New KEY INSERT error!' );

        return $this->get( 'create/database2/select/result/0' );
    }

    protected function Login(){

        $this->set(
            'login/database1/select/conditions/value',
             $this->get( CONSTS::get( 'post/user' ) )
        );

        $this->Database( 'login/database1' );

        if( $this->get( 'login/database1/connect/error' ) !== FALSE ) return $this->debug_add( 'Login: SQL connection ERROR!' );

        if( $this->get( 'login/database1/select/error' ) !== FALSE ) return $this->debug_add( 'Login: Username select ERROR!' );

        if( $this->no_arr_count( $this->get( 'login/database1/select/result' ) ) ) return $this->debug_add( 'Login: NO USER result!' );

        $user = $this->get( 'login/database1/select/result/0' );

        if( $user[CONSTS::get( 'post/pass' )] !== $this->hash( $this->get( CONSTS::get( 'post/pass' ) ), $user['salt'] ) ) return $this->debug_add( 'Login: Password NOT MATCH!' );

        if( $this->get( 'login/input1/get/result/post/' . CONSTS::get( 'post/remember' ) ) ){

            $this->set(
                'login/cookie1/set/value',
                $user['name'] . CONSTS::get( 'cookie/user/separator' ) . $user['pass']
            );
            $this->Cookie( 'login/cookie1' );
        }
        return $user;
    }

    protected function autoLogin(){

        $this->Session( 'auto/session1' );
        $user_id = $this->get('auto/session1/get/result/' . CONSTS::get( 'session/user' ));

        if( gettype( 0 ) === gettype( (int)$user_id ) && (int)$user_id > 0 ){

            $this->set(
                'auto/database1/select/conditions/column',
                 'id'
            );

            $this->set(
                'auto/database1/select/conditions/value',
                 $user_id
            );

            $this->Database( 'auto/database1' );

            if( $this->get( 'auto/database1/connect/error' ) !== FALSE ) return $this->debug_add( 'Auto login: SQL connection ERROR on registered user!' );

            if( $this->get( 'auto/database1/select/error' ) !== FALSE ) return $this->debug_add( 'Auto login: Username select ERROR on registered user!' );

            if( $this->no_arr_count( $this->get( 'auto/database1/select/result' ) ) ) return $this->debug_add( 'Auto login: NO USER result for registered user!' );

            return $this->get( 'auto/database1/select/result/0' );
        }

        $this->Cookie( 'auto/cookie1' );

        $data = array(
            'credentials' => $this->get( 'auto/cookie1/get/result' )
        );

        $this->get( 'manager/validation' )->init(
            $data,
            $this->get( 'auto/filter' ),
            TRUE
        );

        if( count( $this->get( 'manager/validation' )->error() ) !== 0 ) return $this->debug_add( 'Auto login: Filter errors' );

        $split_result = explode( CONSTS::get( 'cookie/user/separator' ), $data[credentials] );

        if(
            gettype( array() ) !== gettype( $split_result )
            || count( $split_result ) !== 2

        ) return $this->debug_add( 'Auto login: Split result mismatch!' );

        $credentials = array(

            'user' => $split_result[0],
            'pass_hash' => $split_result[1]
        );

        $this->get( 'manager/validation' )->init(
            $credentials,
            $this->get( 'auto/filter1' ),
            TRUE
        );

        if( count( $this->get( 'manager/validation' )->error() ) !== 0 ) return $this->debug_add( 'Auto login: credentials Filter errors' );

        $this->set(
            'auto/database1/select/conditions/value',
             $credentials['user']
        );

        $this->Database( 'auto/database1' );

        if( $this->get( 'auto/database1/connect/error' ) !== FALSE ) return $this->debug_add( 'Auto login: SQL connection ERROR!' );

        if( $this->get( 'auto/database1/select/error' ) !== FALSE ) return $this->debug_add( 'Auto login: Username select ERROR!' );

        if( $this->no_arr_count( $this->get( 'auto/database1/select/result' ) ) ) return $this->debug_add( 'Auto login: NO USER result!' );

        $user = $this->get( 'auto/database1/select/result/0' );

        if( $user[CONSTS::get( 'post/pass' )] !== $credentials[pass_hash] ) return $this->debug_add( 'Auto login: Password NOT MATCH!' );

        return $user;
    }

    protected function Activate(){

        $user = $this->Login();

        if( $user === FALSE ) return $this->debug_add( 'Activate: User NOT EXITS!' );

        if( $user['active'] === '1' ) return $this->debug_add( 'Activate: User ACTIVATED already!' );

        $this->set(
            'activate/database2/select/conditions/value',
             $user['id']
        );

        $this->Database( 'activate/database2' );

        if( $this->get( 'activate/database2/select/error' ) !== FALSE ) return $this->debug_add( 'Activate: KEY SELECT ERROR!' );

        if( $this->no_arr_count( $this->get( 'activate/database2/select/result' ) ) ) return $this->debug_add( 'Activate: Key NOT EXITS!' );

        $key_post = $this->get( 'activate/input1/get/result/post/' . CONSTS::get( 'post/activate' ) );

        $key_sql = $this->get( 'activate/database2/select/result/0/' . CONSTS::get( 'mysql/table/key' ) );

        if( STRINGS::is_string_length( $key_post ) === FALSE || STRINGS::is_string_length( $key_sql ) === FALSE ) return $this->debug_add( 'Activate: NO KEY string LENGTH!' );

        if( $key_post !== $key_sql ) return $this->debug_add( 'Activate: KEY NOT MATCH!' );

        $this->set( 'activate/database3/update/conditions/value', $user['id'] );
        $this->set( 'activate/database3/delete/conditions/value', $user['id'] );

        $this->Database( 'activate/database3' );

        if( $this->get( 'activate/database3/update/error' ) !== FALSE ) return $this->debug_add( 'Activate: KEY UPDATE ERROR!' );
        if( $this->get( 'activate/database3/delete/error' ) !== FALSE ) return $this->debug_add( 'Activate: KEY DELETE ERROR!' );

        return $user;
    }

    protected function Generate(){

        $user = $this->Login();

        if( $user === FALSE ) return $this->debug_add( 'generateKey: User NOT EXITS!' );

        if( $user['active'] === '1' ) return $this->debug_add( 'generateKey: User ACTIVATED already!' );

        $this->set(
            'generate/database2/select/conditions/value',
             $user['id']
        );

        $this->Database( 'generate/database2' );

        $update_key = TRUE;

        if( $this->get( 'generate/database2/select/error' ) !== FALSE ) return $this->debug_add( 'generateKey: KEY SELECT ERROR!' );

        if( $this->no_arr_count( $this->get( 'generate/database2/select/result' ) ) ) $update_key = FALSE;

        $key = $this->hash( $this->get( CONSTS::get( 'post/user' ) ), $this->salt() );

        switch( $update_key ){

            case FALSE:

                $this->set( 'generate/database3/insert/values/0', $user['id'] );
                $this->set( 'generate/database3/insert/values/1', $key );
                $this->Database( 'generate/database3' );

                if( $this->get( 'generate/database3/insert/error' ) !== FALSE ) return $this->debug_add( 'generateKey: KEY INSERT ERROR!' );

                break;

            case TRUE:

                $this->set( 'generate/database4/update/set/value', $key );
                $this->set( 'generate/database4/update/conditions/value', $user['id'] );

                $this->Database( 'generate/database4' );

                if( $this->get( 'generate/database4/update/error' ) !== FALSE ) return $this->debug_add( 'generateKey: KEY UPDATE ERROR!' );

                break;
        }
        if( $this->get( 'generate/database3/insert/result' ) === TRUE || $this->get( 'generate/database4/update/result' ) === TRUE ){

            // TODO: SEND MAIL
            return $user;
        }
    }

    protected function Credentials( $action ){

        if( $action === 'logout' ) return true;
        if( $action === 'auto' ) return true;

        $this->Input( $action . '/input1' );

        if( $this->no_arr_count( $this->get( $action . '/input1/get/result/post' ) ) ) return $this->debug_add( 'Create: No POST result' );

        $this->get( 'manager/validation' )->init(
            $this->get( $action . '/input1/get/result/post' ),
            $this->get( $action . '/filter' ),
            TRUE
        );

        if( count( $this->get( 'manager/validation' )->error() ) !== 0 ) return $this->debug_add( 'Create: Filter errors' );

        $this->set( CONSTS::get( 'post/user' ), $this->get( $action . '/input1/get/result/post/' . CONSTS::get( 'post/user' ) ) );
        $this->set( CONSTS::get( 'post/pass' ), $this->get( $action . '/input1/get/result/post/' . CONSTS::get( 'post/pass' ) ) );

        return TRUE;
    }

    protected function salt(){

        //SECRET :D
    }

    protected function hash( $pass, $salt ){

        //SECRET :D
    }

    protected function no_arr_count( $arr ){

        if( gettype( array() ) !== gettype( $arr ) || count( $arr ) === 0 ) return TRUE;

        return FALSE;
    }
}