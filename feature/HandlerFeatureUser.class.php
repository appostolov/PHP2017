<?php

require_once __DIR__ . '/HandlerFeature.class.php';

class HandlerFeatureUser extends HandlerFeature{

    protected function construct_HandlerPermission(){

        switch( $this->get( 'userInput/action' ) ){

            case CONSTS::get( 'action/select' ):
                $this->featureSelect();
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

    protected function prepareWHERE(){

        if( $this->get( 'userInput/action' ) !== CONSTS::get( 'action/delete' ) ) return $this->get( 'userInput/where' );

        $where = array(

            "column" => "id",
            "sign" => ">",
            "value" => 1
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
}