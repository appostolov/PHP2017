<?php

require_once __DIR__ . '/../permission/HandlerPermission.class.php';

class HandlerFeature extends HandlerPermission{

    protected function construct_HandlerPermission(){

        switch( $this->get( 'userInput/action' ) ){

            case CONSTS::get( 'action/select' ):
                $this->featureSelect();
                break;

            case CONSTS::get( 'action/insert' ):
                $this->featureCreate();
                break;

            case CONSTS::get( 'action/update' ):
                $this->featureUpdate();
                break;

            case CONSTS::get( 'action/delete' ):
                $this->featureDelete();
                break;
        }
        $this->Database( 'closeDatabase' );

        $result = $this->get( 'result' );
        if( $result !== NULL ) die( $result );

        die( $this->debug_string() );
    }

    protected function featureSelect(){

        $action = CONSTS::get( 'action/select' );

        $this->set(
            $action . '/database/select/table',
            $this->get( 'userInput/table' )
        );
        $this->set(
            $action . '/database/select/conditions',
            $this->prepareWHERE()
        );

        $this->Database( $action . '/database' );

        $error = $this->get( $action . '/database/connect/error' );
        if( $this->get( $action . '/database/connect' ) !== NULL && $error !== FALSE ) return $this->debug_add( 'featureSelect: ' . var_dump( $error ) );

        $error = $this->get( $action . '/database/select/error' );
        if( $error !== FALSE ) return $this->debug_add( 'featureSelect: ' . var_dump( $error ) );

        $result = $this->get( $action . '/database/select/result' );
        $this->set( 'result', json_encode( $result ) );
    }

    protected function featureCreate(){

        $action = CONSTS::get( 'action/insert' );

        $this->set( 'time', time() );
        $this->set(
            $action . '/database/insert/values',
            $this->get( 'userInput/values' )
        );
        if( !$this->validateVALUES() ) return;

        $this->Database( $action . '/database' );

        $error = $this->get( $action . '/database/insert/error' );
        if( $error !== FALSE ) return $this->debug_add( 'featureCreate: ' . var_dump( $error ) );

        $this->set( 'result', '1' );
    }

    protected function featureUpdate(){

        $action = CONSTS::get( 'action/update' );

        $set = $this->get( 'userInput/set' );
        if( $set === NULL || gettype( array() ) !== gettype( $set ) ) return $this->debug_add( 'featureUpdate: SET incompatible: '. var_dump( $set ) );

        $restricted = array(
            CONSTS::get( 'mysql/table/user' ),
            CONSTS::get( 'mysql/table/space' ),
            "created"
        );
        foreach( $set as $key => $val ){

            if( array_key_exists( $val['column'], $restricted ) ) return $this->debug_add( 'featureUpdate: setting restricted columns!');
        }
        $this->get( "manager/validation" )->init(
            $set,
            $this->get( $action . "/filter" )
        );
        $errors = $this->get( "manager/validation" )->error();
        if( count( $errors ) > 0 ) return $this->debug_add( 'featureUpdate: UPDATE data not valid: ' . var_dump( $errors ) );

        $this->set(
            $action . '/database/update/conditions',
            $this->prepareWHERE()
        );
        $this->set(
            $action . '/database/update/set',
            $set
        );
        $this->Database( $action . '/database' );

        $error = $this->get( $action . '/database/update/error' );
        if( $error !== FALSE ) return $this->debug_add( 'featureUpdate: ' . var_dump( $error ) );

        $this->set( 'result', '1' );
    }

    protected function featureDelete(){

        $action = CONSTS::get( 'action/delete' );

        $this->set(
            $action . '/database/delete/conditions',
            $this->prepareWHERE()
        );
        $this->Database( $action . '/database' );

        $error = $this->get( $action . '/database/connect/error' );
        if( $this->get( $action . '/database/connect' ) !== NULL && $error !== FALSE ) return $this->debug_add( 'featureDelete: ' . var_dump( $error ) );

        $error = $this->get( $action . '/database/delete/error' );
        if( $error !== FALSE ) return $this->debug_add( 'featureDelete: ' . var_dump( $error ) );

        $this->set( 'result', '1' );
    }

    protected function prepareWHERE(){

        $where = array(

            "column" => CONSTS::get( 'mysql/table/space' ),
            "sign" => "=",
            "value" => $this->get( 'userInput/space' )
        );
        if( $this->get( 'userInput/where' ) === NULL ) return $where;

        return array(
            "operator" => "AND",
            "conditions" => array(
                $where,
                $this->get( 'userInput/where' )
            )
        );
    }

    protected function setInsertValues( $path ){

        $default = array(

            "id" => NULL,
            "user" => $this->get( 'user/id' ),
            "space" => $this->get( 'userInput/space' )
        );
        $input = $this->get( $path );

        if( $this->get( 'userInput/table' ) === CONSTS::get( 'mysql/table/permission' ) ){

            $default = array( "id" => NULL );
        }else{

            $inputNew = array();

            foreach( $input as $key => $val ){

                if( $key !== "user" && $key !== "space" ) $inputNew[$key] = $val;
            }
            $input = $inputNew;
        }
        $time = array( "created" => $this->get( 'time' ) );

        $result = array_merge( $default, $input, $time );
        $this->set(
            $path,
            $result
        );
        return $result;
    }

    protected function normalizeInsertValues( $path ){

        $values = $this->get( $path );
        $result = array();

        foreach( $values as $val ){

            array_push( $result, $val );
        }
        $this->set( $path, $result );
    }

    protected function validateVALUES(){

        $values = $this->get( 'userInput/values' );

        if( $values === NULL ) return $this->debug_add( 'validateVALUES: No values for INSERT!' );
        if( gettype( array() ) !== gettype( $values ) ) return $this->debug_add( 'validateVALUES: values not ARRAY: ' . var_dump( $values ) );

        $action = CONSTS::get( 'action/insert' );

        $filter = $this->get( $action . "/filter" );

        if( gettype( array() ) === gettype( $values[0] ) ){

            foreach( $values as $key => $value ){

                $tmpValue = $this->setInsertValues( $action . "/database/insert/values/" . $key );

                $this->get( "manager/validation" )->init(
                    $tmpValue,
                    $filter
                );
                $errors = $this->get( "manager/validation" )->error();
                if( count( $errors ) > 0 ) return $this->debug_add( 'validateVALUES: INSERT data not valid: ' . var_dump( $errors ) );

                $this->normalizeInsertValues( $action . "/database/insert/values/" . $key );
            }
            return TRUE;
        }
        $tmpValue = $this->setInsertValues( $action . "/database/insert/values" );

        $this->get( "manager/validation" )->init(
            $tmpValue,
            $filter
        );
        $errors = $this->get( "manager/validation" )->error();
        if( count( $errors ) > 0 ) return $this->debug_add( 'validateVALUES: INSERT data not valid: ' . var_dump( $errors ) );

        $this->normalizeInsertValues( $action . "/database/insert/values" );

        return TRUE;
    }
}