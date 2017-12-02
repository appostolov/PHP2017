<?php

require_once __DIR__ . '/HandlerFeature.class.php';

class HandlerFeatureSpace extends HandlerFeature{

    protected function prepareWHERE(){

        if( $this->get( 'userInput/action' ) !== "delete" ) return $this->get( 'userInput/where' );

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

    protected function setInsertValues( $path ){

        $default = array(

            "id" => NULL,
            "user" => $this->get( 'user/id' )
        );
        $input = $this->get( $path );

        $time = array( "created" => $this->get( 'time' ) );

        $result = array_merge( $default, $input, $time );
        $this->set(
            $path,
            $result
        );
        return $result;
    }
}