<?php
use Marion\Controllers\BackendController;
use Shop\{Cart,CartStatus,Order};
use JasonGrimes\Paginator;
use Marion\Core\Marion;
class OrdersController extends BackendController{
	private $pager = [
		'per_page' => 10,
		'max_pages_to_show' => 5
	];
	function setMedia(){
		parent::setMedia();
		$this->registerCSS('modules/ecommerce/css/backend_ecommerce.css');
	}


	function __construct($options)
	{
		$this->setMenu('ecommerce_orders');
		parent::__construct($options);
	}

	function index(){
		if( !authUser() ) $this->notAuth();
		$user = Marion::getUser();
		$query = Cart::prepareQuery()
				->where('user',$user->id)
				->where('status','active','<>')
				->orderBy('evacuationDate','DESC')
				->limit($this->limit_pager);

		$offset = (int)_var('pageID');
		if( $offset ){
			$query->offset(($offset-1)*$this->limit_pager);
		}
		$carrelli = $query->get();
				
		$stati = CartStatus::prepareQuery()->get();
		foreach($stati as $v){
			$status[$v->label] = "<span class='label' style='background:".$v->color."'>".strtoupper($v->get('name'))."</span>";
		}

		$database = Marion::getDB();
		$tot = $database->select('count(*) as tot','cart',"user={$user->id}");
		$tot = $tot[0]['tot'];
		
		if(okArray($carrelli)){

			foreach($carrelli as $v){
				$ord =Order::prepareQuery()->where('cart',$v->id)->orderBy('id','DESC')->getOne();
				if( is_object($ord) ){
					$prod = $ord->getProduct();
					if( is_object($prod) ){
						$v->image = $prod->getUrlImage(0,'thumbnail');
					}
				}
				$v->status = $status[$v->status];
			}
			//preparo il pager
			
			$this->setVar('carrelli',$carrelli);
			
			
		}
		$this->output('order/list.htm');
	}


	function buildPaginator($currentPage,$totalItems){
		$itemsPerPage = $this->pager['per_page'];
		//$urlPattern = '/foo/page/(:num)';
		$urlPattern = $this->getUrlCurrent();
		$urlPattern = preg_replace("/&pageID=([0-9]+)/",'',$urlPattern);
		
		$urlPattern .= "&pageID=(:num)";
	
		$paginator = new Paginator($totalItems, $itemsPerPage, $currentPage,$urlPattern);
		$paginator->setMaxPagesToShow($this->pager['max_pages_to_show']);
		return $paginator;

	}



	function view($id){
		$user = getUser();
	
		
		$cart = Cart::withId($id);
		
		if( !is_object($cart) ){ 
			$this->error('cart not exists');
		}
		
		if( is_object($user) ){
			if( $cart->user != $user->id) $this->error('not auth');
		}else{
			$password = _var('pass');
			
			if( $cart->password_not_logged != $password ){
				
				// NON AUTORIZZATO
				$this->notAuth();
			}
		}

		if($cart->status){
			$status = CartStatus::withLabel($cart->status);
			if( is_object($status) ){
				$cart->status = "<span class='label' style='color:".$status->color."'>".strtoupper($status->get('name'))."</span>";
			}
		}
		
		$this->setVar('cart',$cart);

		if( $cart->recurrentPayment ){
			$frequencyPayment = $cart->getFrequencyPaymentPaypal();
			$this->setVar('description_recurrent_payment',$frequencyPayment['description']);
		}

		$ordini = $cart->getOrders();
		
		
		foreach($ordini as $k => $ord){
			$prodotto = $ord->getProduct();
			if(is_object($prodotto)){
				$ordini[$k]->productname = $prodotto->get('name');
				$ordini[$k]->link = $prodotto->getUrl();
				$ordini[$k]->img = $prodotto->getUrlImage(0,'thumbnail');
			}
		}
		$this->setVar('ordini',$ordini);
		

		$this->output('order/detail.htm');

	}




	function ajax(){
		$action = $this->getAction();
		
		switch($action){
			case 'send_mail_customer':
				$risposta = $this->sendMailCustomer();
				break;
			case 'changeStatusOrder':
				
				$risposta = $this->changeStatus();
				break;
				
		}
		echo json_encode($risposta);
		
	}

	function sendMailCustomer(){
		if( Marion::auth('ecommerce') ){
			$formdata = $this->getFormdata(1);
			
			
			
			$array = $this->checkDataForm('emailCart',$formdata);
			
			
			
			
			if($array[0] == 'ok'){

				//prendo il carrello
				
				$cart = Cart::withId($array['cartId']);
				
				if( !is_object($cart) ) {
					$risposta = array(
						'result'=> 'nak',
						'errore'=> 'Errore inatteso',
					);
					return $risposta;
					
				}

				$infosito = Marion::getConfig('generale');
						
				$subject = $array['subject'];
				
				$this->setVar('message_title',$subject);
				$this->setVar('message_text',$array['text']);
				ob_start();
				$this->output('ecommerce_mails/general.htm');
				$html = ob_get_contents();
				ob_end_clean();
			


				$mail = _obj('Mail');
				$mail->cart = $cart;
				$mail->setHtml($html);
				$mail->setSubject($subject);
				$mail->setTo($array['email']);
				$mail->setFrom( Marion::getConfig('eshop','mail') );
				$mail->send();
				$risposta = array('result'=> 'ok');
			}else{
				$risposta = array(
						'result'=> 'nak',
						'errore'=> $array[1],
				);
			}
		}else{
			$risposta = array(
						'result'=> 'nak',
						'errore'=> "permission denied",
				);
		}
		return $risposta;

	}

	function changeStatus(){
		if( Marion::auth('ecommerce') ){
			$formdata = $this->getFormdata();
			
			
			$array = $this->checkDataForm('changeStatus',$formdata);
		
			
	


			if($array[0] == 'ok'){
				unset($array[0]);
				$cart = Cart::withId($array['cartId']);
				
				if(is_object($cart)){

					if( $cart->status != $array['status'] ){
							
						$risposta = array();
						if( $cart->comesFrom ){
							if (Marion::exists_action('ecommerce_change_status_cart')){
								Marion::do_action('ecommerce_change_status_cart',array(&$cart,&$array,&$risposta));
							}
						}else{
							if( $array['trackingCode'] ){
								$cart->set(
									array( 'trackingCode' => $array['trackingCode']
									))->save();
							}

							
							$cart->changeStatus($array['status'],$array['note']);
							$risposta = array(
								'result' => 'ok',
								'id' => $cart->id,
							);
							
						}
					}else{

						$risposta = array(
							'result' => 'nak',
							'errore' => 'Selezionare uno stato differente a quello attuale!',
							);

					}
				}
				
				
			}else{
				$risposta = array(
					'result' => 'nak',
					'errore' => $array[1],
					);
			}
		}else{
			$risposta = array(
						'result'=> 'nak',
						'errore'=> "permission denied",
				);
		}
		return $risposta;

	}
	

		// TWIG FUNCTION 
	function array_status_cart(){
		

		$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->get();
		foreach($status_avaiables as $v){
			$stati[$v->label] = $v->get('name');
		}
		return $stati;
	}


	



	
}


?>