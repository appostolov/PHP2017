<?php

require_once __DIR__ . '/../Page.class.php';
require_once __DIR__ . '/../../STATIC/STRINGS.class.php';

/*
* The page's pourpose is to output front end code
* @public [function] init - execute Page specific things
*/
class OutputPage extends Page{
	
	/*
	*** HOW TO USE ***
	
	$config = array(
	
		'templ' => array(
		
			'url' => 'path/to/template.html',
			'data' => array(
			
				'title' => "???",
				'body' => "<p>!!!</p>"
			)
		)
	);
	
	$page = new Step2( $config );

	*/
	
	/*
	* Parse and show the FrontEnd
	* @returns [object] OutputPage onject
	*/
	public function init(){
		
		if( $this->get( 'key' ) === TRUE ) $this->setUIKey();
		
		$this->setScripts();
		
		$this->show(  STRINGS::parse_file(  $this->get( 'templ/url' ), $this->get( 'templ/data' ), TRUE ) );
	}
	
	protected function setUIKey(){
		
		$this->restart( $this->get() );
		
		$sm = new Session();
		
		$sm->set( CONSTS::get( 'session/key' ), $this->get( 'id' ) );
		
		$this->set( 'templ/data/key', $sm->get( CONSTS::get( 'session/key' ) ) );
		
		return $this;
	}
	
	protected function setScripts(){
		
		$scripts = $this->get( 'templ/data/scripts' );
		
		if( gettype( "" ) === gettype( $scripts ) ){
			
			if( $scripts != "" ) $this->set( 'templ/data/scripts', $this->scriptTag( $scripts ) );
			
		}else if( gettype( array() ) === gettype( $scripts ) ){
			
			$result = "";
			
			foreach( $scripts as $src ){
				
				$result .= $this->scriptTag( $src );
			}
			$this->set( 'templ/data/scripts', $result );
			
		}else{
			
			$this->set( 'templ/data/scripts', "" );
		}
	}
	
	protected function scriptTag( $src ){
		
		return "<script src='$src'></script>";
	}
}