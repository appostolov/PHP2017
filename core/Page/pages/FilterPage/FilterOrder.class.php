<?php

require_once __DIR__ . '/../FilterPage.class.php';
require_once __DIR__ . '/../../../STATIC/STRINGS.class.php';

/*
* Custom FilterPage
*/
class FilterOrder extends FilterPage{
	
	protected $order;
	
	protected $created;
	
	protected $new_token;
	
	protected function filter_init(){

		$this->created = time();
		
		if( count( $this->get( "error" ) ) === 0 ){
			
			if( $this->manager( "database" )->connect(
			
				$this->get("order/host"),
				$this->get("order/user/name"),
				$this->get("order/user/pass"),
				$this->get("order/database/name")
				
			)->error() === FALSE ){
				
				$this->create_order($this->get("order/table/order"));
				
				if( $this->manager("database")->error() === FALSE ){
					
					if( $this->save_order_data() === TRUE ){
						
						$this->set( "action/data/success", $this->get( "order/result" ) );
						$this->set( "action/data/success/created", $this->get( "prepared_data/created" ) );

						$this->sendEmail();

						return $this->action( TRUE );
					}
				}
			}
		}
		return $this->action( FALSE );
	}

	protected function sendEmail(){

		$order_data_labels = $this->get("email/order_data");

		$order_data_success = $this->get("action/data/success");

		$order_data_success['product'] = $this->get("product/name");
		$order_data_success['price'] .= $this->get("product/curr");

		$order_data = "";

		foreach( $order_data_labels as $key => $val ){

			$order_data .= "<tr><td bgcolor='#DDDDDD' align='right' width='50%' style='padding: 5px 5px 5px 5px; border-bottom: 2px #ffffff solid;'>" . $val . "</td><td bgcolor='#99FFDD' align='center' width='50%' style='padding: 5px 5px 5px 5px; border-bottom: 2px #ffffff solid;'>" . $order_data_success[$key] . "</td></tr>";
		}
		$this->set( "email/template/data/order_data", $order_data );

		$this->set( "email/template/data/message1", str_replace( $this->get("email/template/product_name_key"), $this->get("product/name"), $this->get("email/template/data/message1") ) );

		$msg = STRINGS::parse_file( $this->get("email/template/url"), $this->get("email/template/data"), TRUE );

		$headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		mail( $order_data_success['mail'], $this->get("email/subject"), $msg, $headers );
	}
	
	protected function action( $result ){
		
		$location = $this->get("action/location");
		
		if( $location === NULL ) return;
		
		if( gettype( $location ) === gettype( "" ) ){
			
			$this->manager( 'header' )->location( $this->setLink( $location, $result ) );
			
		}else if( count( $location ) > 0 ){
			
			switch( $result ){
			
				case TRUE:
			
					$this->manager( 'header' ).location( $this->setLink( $this->get("action/location/success"), $result ) );
					break;
				
				case FALSE:
					
					$this->manager( 'header' ).location( $this->setLink( $this->get("action/location/error"), $result ) );
					break;
					
				default:
					return;
			}
		}
		return FALSE;
	}
	
	protected function setLink( $location, $result ){
		
		switch( $result ){
			
			case TRUE:
				
				return $this->addGetData( $location, $this->get( 'action/data/success' ) );
				
			case FALSE:
				
				return $this->addGetData( $location, $this->get( 'action/data/error' ) );
				
			default:
				return;
		};
	}
	
	protected function addGetData( $link, $data ){
		
		$count = 0;
		
		$url = $link;
		
		foreach( $data as $key => $val ){
			
			$url .= (( $count === 0 ) ? "?" : "&") . $key . "=" . $val ;
			
			$count ++;
		}
		return $url;
	}
	
	protected function create_order( $table ){
		
		$this->manager("database")->insert(
			
			$table,
			array(
				NULL,
				$this->get( 'product/id' ),
				$this->get( 'prepared_data/quant' ),
				$this->get( 'product/price/' . array_search(
				
						$this->get( 'prepared_data/quant' ),
						$this->get( 'product/quant' )
					)
				),
				$this->get( 'prepared_data/visit' ),
				$this->created
			)
		);
	}
	
	protected function save_order_data(){
		
		$result = TRUE;
		
		$order = $this->manager("database")->select(
			'*',
			$this->get( "order/table/order" ),
			array(
				'column' => 'created',
				'sign' => '=',
				'value' => $this->created
			),
			NULL,
			NULL,
			NULL,
			'assoc'
		);
		
		if( $this->manager("database")->error() !== FALSE ) return FALSE;
		
		$order_result = $order[0];
		
		foreach( $this->get( "order/custom_data_type" ) as $key => $val ){
			
			if( !$this->manager("database")->insert(
			
				$this->get( "order/table/data" ),
				array(
					NULL,
					$order_result['id'],
					$key,
					$this->get( 'prepared_data/' . $key ),
					$val,
					$this->created
				)
			) ){
				$result = FALSE;
				break;
			}
			$order_result[$key] = $this->get( 'prepared_data/' . $key );
		}
		
		$this->set( 'order/result', $order_result );
		return $result;
	}
}