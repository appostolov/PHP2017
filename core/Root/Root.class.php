<?php

require_once __DIR__ . '/../STATIC/CONFIG.class.php';
require_once __DIR__ . '/../STATIC/REGEX.class.php';

/*
* This class is a wrapper of several core classes.
* It's purpose is to help with data management.
*
* @private [array] $config - holds the configuration data, and serve as core for getting and setting data
* @public [function] __construct - sets the config, check the key if needed, invoke the construct stack of the child $this->construct_Root(), and returns the object itself
* @protected [function] construct_Root - gives child's access to the __construct through polymorphism
* @protected [function] checkKey - check and compare POST and Session key data
* @protected [function] path2path - sets a config member value to other member's value
* @protected [function] setConfig - sets the $config
* @protected [function] get - returns $config's member value by using CONFIG class
* @protected [function] set - sets $config's member value by using CONFIG class
* @protected [function] functionInvoke - invokes array of a class member functions
* @protected [function] Input - gets / sets input data
* @protected [function] Session - gets / sets session data
* @protected [function] Database - deal with databases
*/
class Root{

    private $config = array();

    public function __construct( $config ){

        $this->setConfig( $config );

        if( $this->get( 'key' ) === TRUE && $this->checkKey() === FALSE ) die();

        $this->construct_Root();

        return $this;
    }
    protected function construct_Root(){}

    function hasType( $data, $type ){
        // All data types except object and resource
        $types = array(

            "string" => "",
            "integer" => 0,
            "double" => 0.5,
            "boolean" => true,
            "array" => array(),
            "NULL" => NULL
        );

        // Check against data types in the array
        if( array_key_exists( $type, $types ) === TRUE ){

            // IF type i different than the filter's condition
            if( gettype( $types[ $type ] ) !== gettype( $data ) ) return FALSE;

        }else if( $type === "object" ){// IF condition is object

            return is_object( $data );

        }else if( $type === "resource" ){// IF condition is resource

            return is_resource( $data );

        }else{

            return FALSE;
        }
        return TRUE;
    }

    protected function filterDataSelection( $selection, $filter ){

        if( !$this->hasType( $selection, 'array' ) ) return FALSE;

        $data = array();
        $tmp = NULL;

        foreach( $selection as $key => $path ){

            $tmp = $this->get( $path );

            if( $tmp !== NULL ) $data[$key] = $tmp;
        }
        return $this->get( 'manager/validation' )->init( $data, $filter );
    }

    protected function checkKey(){

        if( is_a( $this->get( 'manager/session' ), 'Session' ) === FALSE ) return FALSE;

        if( is_a( $this->get( 'manager/input' ), 'Input' ) === FALSE ) return FALSE;

        if( $this->get( 'manager/session' )->exists( 'key' ) === FALSE || $this->get( 'manager/input' )->exists( 'key' ) === FALSE ) return FALSE;

        if( $this->get( 'manager/session' )->get( 'key' ) !== $this->get( 'manager/input' )->get( 'key' ) ) return FALSE;

        return TRUE;
    }

    protected function path2path( $from, $to ){

        if( !CONFIG::_exists( $from, $this->config ) === FALSE ) return FALSE;

        if( $this->set( $to, $this->get( $from ) ) !== FALSE ) return TRUE;
    }

    protected function setConfig( $config ){

        $this->config = $config;
    }

    protected function get( $path ){

        return CONFIG::get( $this->config, $path );
    }

    protected function set( $path, $value ){

        $this->config = CONFIG::set( $this->config, $path, $value );

        return $this;
    }

    protected function functionInvoke( $config ){

        foreach( $config as $key => $val ){

           call_user_func($key, $val);
        }
    }

    protected function Input( $basePath ){

        foreach( $this->get( $basePath ) as $key_base => $val_base ){

            foreach( $val_base as $key_method => $val_method ){

                foreach( $val_method as $key_field => $val_field ){

                    switch( $key_base ){

                        case 'get':

                            $this->set( $basePath . '/' . $key_base . '/result/' . $key_method . '/' . $key_field, $this->get( 'manager/input' )->get( $key_field, $key_method ) );
                            break;

                        case 'set':

                            $this->set( $basePath . '/' . $key_base . '/result/' . $key_method . '/' . $key_field, $this->get( 'manager/input' )->set( $key_field, $val_field, $key_method ) );
                            break;
                    }
                }
            }
        }
    }

    protected function Session( $basePath ){

        foreach( $this->get( $basePath ) as $key_base => $val_base ){

            foreach( $val_base as $key_field => $val_field ){

                switch( $key_base ){

                    case 'get':

                        $this->set( $basePath . '/' . $key_base . '/result/' . $key_field, $this->get( 'manager/session' )->get( $key_field ) );
                        break;

                    case 'set':

                        $this->set( $basePath . '/' . $key_base . '/result/' . $key_field, $this->get( 'manager/session' )->set( $key_field, $val_field ) );
                        break;
                }
            }
        }
    }

    protected function Cookie( $basePath ){

        foreach( $this->get( $basePath ) as $action => $config ){

            switch( $action ){

                case 'exists':

                    $this->set(
                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/cookie' )->exists( $this->get( $basePath . '/' . $action . '/name' ) )
                    );
                    break;

                case 'set':

                    $this->set(
                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/cookie' )->set(

                             $this->get( $basePath . '/' . $action . '/name' ),
                             $this->get( $basePath . '/' . $action . '/value' ),
                             $this->get( $basePath . '/' . $action . '/expire' ),
                             $this->get( $basePath . '/' . $action . '/path' )
                         )
                    );
                    break;

                case 'get':

                    $this->set(
                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/cookie' )->get(

                             $this->get( $basePath . '/' . $action . '/name' )
                         )
                    );
                    break;


                case 'remove':

                    $this->set(
                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/cookie' )->remove(

                             $this->get( $basePath . '/' . $action . '/name' )
                         )
                    );
                    break;


                case 'close':

                    $this->set(
                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/cookie' )->close()
                    );
                    break;
            }
        }
    }

    protected function Database( $basePath ){

        foreach( $this->get( $basePath ) as $action => $config ){

            switch( $action ){

                case 'connect':

                    // Connect database
                    $this->get( 'manager/database' )->connect(

                        $config['host'],
                        $config['user'],
                        $config['pass'],
                        $config['db_name']
                    );
                    break;

                case 'disconnect':

                    // Connect database
                    $this->get( 'manager/database' )->disconnect();
                    break;

                case 'select':

                    $this->set(

                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/database' )->select(

                            $config['columns'],
                            $config['table'],
                            $config['conditions'],
                            $config['order'],
                            $config['limit'],
                            $config['offset'],
                            $config['result_type']
                         ) );
                    break;

                case 'insert':

                    $this->set(

                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/database' )->insert(

                            $config['table'],
                            $config['values'],
                            $config['columns']
                         )
                    );
                    break;

                case 'update':

                    $this->set(

                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/database' )->update(

                            $config['table'],
                            $config['set'],
                            $config['conditions']
                         )
                    );
                    break;

                case 'delete':

                    $this->set(

                        $basePath . '/' . $action . '/result',
                        $this->get( 'manager/database' )->delete(

                            $config['table'],
                            $config['conditions']
                         )
                    );
                    break;
            }
            $this->set( $basePath . '/' . $action . '/error', $this->get( 'manager/database' )->error() );
        }
    }
}