<?php

require_once __DIR__ . '/../core/Debug/Debug.class.php';
require_once __DIR__ . '/../core/STATIC/STRINGS.class.php';
require_once __DIR__ . '/../core/STATIC/CONSTS.class.php';
require_once __DIR__ . '/../core/STATIC/FEATURE.class.php';
require_once __DIR__ . '/../core/STATIC/CONFIG.class.php';

/*
* This class handles the PERMISSION's functionality
*
* @protected [function] construct_Root - its invoked in the parents __construct,
 * check if user logged in, get user's permissions
 * and invoke $this->construct_HandlerPermission() on success
* @protected [function] construct_HandlerPermission - leave hook for the child classes
* @protected [function] getUserId - gets the user id from the session
* @protected [function] getUserPermissions - get user's permission from database
* @protected [function] hasPermission - check if user has an permission
*/
class HandlerPermission extends Debug{

    protected function construct_Root(){

        $this->getUserId();

        if( $this->checkUserInput() === FALSE ) die( $this->debug_string() );

        if( !$this->getUserPermissions() ) $this->set( 'user/permissions', array() );

        if( !$this->hasPermission() ) die( $this->debug_string() );

        return $this->construct_HandlerPermission();
    }
    protected function construct_HandlerPermission(){}

    protected function getUserId(){

        $this->Session( 'user/session' );

        $user_id = $this->get('user/session/get/result/' . CONSTS::get( 'session/user' ));

        if( gettype( 0 ) !== gettype( (int)$user_id ) || (int)$user_id <= 0 ) $user_id = 0;

        $this->set( 'user/id', (int)$user_id );

        $this->set(
            'user/database/select/conditions/conditions/0/value',
            $this->get( 'user/id' )
        );
        return TRUE;
    }

    protected function checkUserInput(){

        $this->Input( 'input' );

        $data = $this->get( 'input/get/result/post/' . CONSTS::get( 'post/data' ) );
        if( $data === NULL ) return $this->debug_add( 'checkUserInput: No POST data sent: ' . var_dump( $data ) );

        $dataArr = json_decode( $data, TRUE );
        if( gettype( array() ) !== gettype( $dataArr ) ) return $this->debug_add( 'checkUserInput: JSON decode failed: ' . var_dump( $dataArr ) );

        $this->get( "manager/validation" )->init( $dataArr, array(
            "table" => array(
                "required" => true,
                "in_array" => array(
                    CONSTS::get( 'mysql/table/user' ),
                    CONSTS::get( 'mysql/table/space' ),
                    CONSTS::get( 'mysql/table/object' ),
                    CONSTS::get( 'mysql/table/presence' ),
                    CONSTS::get( 'mysql/table/location' ),
                    CONSTS::get( 'mysql/table/permission' )
                )
            ),
            "action" => array(
                "required" => true,
                "in_array" => array(
                    CONSTS::get( 'action/select' ),
                    CONSTS::get( 'action/insert' ),
                    CONSTS::get( 'action/update' ),
                    CONSTS::get( 'action/delete' )
                )
            ),
            "space" => array(
                "required" => true,
                "data_type" => "integer",
                "min_number" => 1
            )
        ));
        $errors = $this->get( "manager/validation" )->error();
        if( count( $errors ) > 0 ) return $this->debug_add( 'checkUserInput: POST data filter failed: ' . var_dump( $errors ) );

        $this->set( "userInput", $dataArr );
    }

    protected function getUserPermissions(){

        $this->set( 'user/features', FEATURE::get() );

        $this->Database( 'user/database' );
        if( $this->get( 'user/database/connect/error' ) !== FALSE ) return $this->debug_add( 'getUserPermissions: SQL connection ERROR!' );
        if( $this->get( 'user/database/select/error' ) !== FALSE ) return $this->debug_add( 'getUserPermissions: SQL select ERROR!' );

        $result = $this->get( 'user/database/select/result' );
        if( $this->no_arr_count( $result ) ) return FALSE;
        $this->set( 'user/permissions', $result );

        return TRUE;
    }

    protected function hasPermission(){

        if( $this->get( 'user/id' ) === 1 ) return TRUE;

        $feature = $this->get( 'userInput/table' ) . "/" . $this->get( 'userInput/action' );

        foreach( $this->get( 'user/permissions' ) as $key => $val ){

            if(
                $val['feature'] === $feature
                && (
                    (int)$val['space'] === $this->get( 'userInput/space' )
                    || (int)$val['space_all'] > 0
                    )
            ) return TRUE;
        }
        return $this->debug_add( 'hasPermission: permission unavailable' );
    }

    protected function no_arr_count( $arr ){

        if( gettype( array() ) !== gettype( $arr ) || count( $arr ) === 0 ) return TRUE;

        return FALSE;
    }
}