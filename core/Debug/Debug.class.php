<?php

require_once __DIR__ . '/../Root/Root.class.php';

/*
* Handling user input
* @public [function] exists - check if the input field exists
* @public [function] get - get user input field
*/
class Debug extends Root{

    public function debug_add( $str ){

        $this->set( "debug/" . $this->debug_count(), $str );

        return FALSE;
    }

    public function debug_reset(){

        $this->set( "debug", array() );
    }

    public function debug_string( $sep = "<br /><br />" ){

        $result = "";
        $config = $this->get( "debug" );

        foreach( $config as $val ){

            if( strlen( $result ) > 0 ) $result .= $sep;

            $result .= $val;
        }
        return $result;
    }

    public function debug_count(){

        if( gettype( array() ) !== gettype( $this->get( "debug" ) ) ) $this->debug_reset();

        return count( $this->get( "debug" ) );
    }
}