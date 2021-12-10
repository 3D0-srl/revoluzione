<?php
use Marion\Controllers\FrontendController;
use Shop\{Cart,Eshop};
class CouponController extends FrontendController{


	

	function ajax(){
		$action = $this->getAction();
		
		switch($action){
			case 'check_coupon':
				
				$this->checkCoupon();
				break;
			case 'remove_coupon':
				
				$this->removeCoupon();
				break;
			default:
				break;
		}

	}



	function removeCoupon(){
		

		unset($_SESSION['manage_coupon']);

		if( $_SESSION['manage_coupon'] ) {
			$risposta = array(
				'result'	=>	'nak',
				'msg'		=>	'Problemi con la rimozione del coupon, si prega di riprovare'
			);
		} else {
			$risposta = array(
				'result'	=>	'ok',
				'msg'		=>	_translate('deleted_coupon','manage_coupon')
			);
		}

		echo json_encode($risposta);

	}




	function checkCoupon(){
		
		$name = _var('name');
		
		require_once('modules/manage_coupon/classes/Coupon.class.php');
		
		$totale = Cart::getCurrentTotal();
		
		$query = Coupon::prepareQuery()->where('name',$name);

		
		$obj = $query->getOne();
		$database = Marion::getDB();
		
		$user = Marion::getUser();

		if( is_object($obj) ){
			$obj = (array) $obj;

			$obj['discount_value'] = (double) $obj['discount_value'];
			$totale = (double) $totale;

			if($obj['expiry_date'] && ($obj['expiry_date'] < date("Y-m-d"))){ //scaduto
				$risposta = array(
					'result'	=>	'nak',
					'msg'		=>	_translate('expired_coupon','manage_coupon')
				);
			} elseif($obj['discount_type']=="fixed" && $obj['discount_value']>$totale){ //quota coupon maggiore del carrello
				$risposta = array(
					'result'	=>	'nak',
					'msg'		=>	_translate('coupon_greater','manage_coupon')
				);
			} elseif((int)$obj['multiple_use'] == 0 && $obj['used'] == 1){ //utilizzato
				$risposta = array(
					'result'	=>	'nak',
					'msg'		=>	_translate('used_coupon','manage_coupon')
				);
			} elseif($obj['min_level'] > $totale){ //totale carrello inferiore alla soglia minima
				
				$message = _translate('cart_less_than','manage_coupon');
				
				$risposta = array(
					'result'	=>	'nak',
					'msg'		=>	sprintf($message,Eshop::formatMoney($obj['min_level']),$GLOBALS['activecurrency'])
				);
		
			} else {
				

				if($obj['use_limit'] ){ 

					
					if( $obj['use_limit'] == 'category_users' ){
						
						if( $user = Marion::getUser()){
							$userCategory = $user->category;
						}else{
							$userCategory = 1;
						}
						if( !in_array($userCategory,$obj['user_category']) ){
							$risposta = array(
								'result'	=>	'nak',
								'msg'		=>	_translate('coupon_not_valid','manage_coupon')
							);
							echo json_encode($risposta);
							exit;
						}
					}elseif( $obj['use_limit'] == 'specific_users'){
						$users = explode(';',$obj['users']);

						
						foreach($users as $k => $v2){
							$users[$k] = strtolower(trim($v2));
						}
						
						$email = '';
						if( is_object($user) ){
							$email = $user->email;
						}
						if( !in_array(strtolower(trim($email)),$users) ){
							$risposta = array(
								'result'	=>	'nak',
								'msg'		=>	_translate('coupon_not_valid','manage_coupon')
							);
							echo json_encode($risposta);
							exit;
						}
					}
					
				}
				
				if($obj['multiple_use'] && $user ){ 
					$id_user = $user->id;
					$num = $database->select('count(distinct carrello) as cont','coupon_cart as co join cart as c on c.id=co.carrello',"id_user = {$id_user} AND coupon_name='{$name}' AND c.status <> 'active'");
				
					if( okarray($num) ){
						$num = $num[0]['cont'];
						if( $num >= $obj['num_repeat'] ){

							$risposta = array(
								'result'	=>	'nak',
								'msg'		=>	_translate('coupon_not_valid','manage_coupon')
							);
							echo json_encode($risposta);
							exit;

						}
					}

				}


				$obj['discount_type_symbol'] = $this->check_discount_type($obj['discount_type']);
				$_SESSION['manage_coupon'] = $obj;

				$risposta = array(
					'result'		=>	'ok',
					'msg'		=>	_translate('coupon_not_valid','coupon_ok'),
					'name'			=>	$name,
					'discount_type'	=>	$obj['discount_type'],
					'discount_value'=>	$obj['discount_value']
				);

			}

		} else {
			$risposta = array(
				'result'	=>	'nak',
				'msg'		=>	_translate('coupon_not_valid','manage_coupon')
			);
		}

		echo json_encode($risposta);
	}
	


	function check_discount_type($type) {

		if($type=="fixed")
			return "�";
		elseif($type=="percentage")
			return "%";
		else
			return "";
	}
	
	
}


?>