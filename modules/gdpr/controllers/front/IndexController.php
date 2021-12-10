<?php
use Marion\Core\Marion;
class IndexController extends \Marion\Controllers\BackendController{
	

	
	function setMedia(){
		parent::setMedia();
		$this->registerCSS('modules/ecommerce/css/backend_ecommerce.css');
	}


	function display(){

		$this->setMenu('gdpr');
		
		$action = $this->getAction();
		
		switch($action){
			
			case 'export_data':
				$this->exportData();
				break;
			case 'export_orders':
				$this->exportOrders();
				break;
			case 'export_addresses':
				$this->exportAddresses();
				break;
			default:
				if( $action == 'destroy' ){
					if( _var('allow') == 1 ){
						$this->destroy();
					}else{
						$this->errors[] = _translate('errore_consenso','gdpr');
					}
				}
				if( class_exists('Address')){
					$user = Marion::getUser();
					$list = Address::prepareQuery()->where('id_user',$user->id)->get();
					$this->setVar('list',$list);
				}
				if( Marion::isActivedModule('ecommerce') ){
					
					$this->setVar('ecommerce',1);
				}

				$this->output('gdpr.htm');
				
				break;
			
		}


		
	}


	function destroy(){
		$user = Marion::getUser();
		$user->delete();
		Marion::setUser(null);
		$this->output('destroy_ok.htm');
		exit;
	}

	function exportData(){
		header("Content-type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=my_data.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		$this->output('export_data.htm');

	}

	function exportOrders(){
		$user = Marion::getUser();
		$list = Cart::prepareQuery()->where('user',$user->id)->get();
		foreach($list as $v){
			$dati = $v->prepareForm();
			$orders = $v->getOrders();
			if( okArray($orders)){
				foreach($orders as $t){
					$p = $t->getProduct();
					if( is_object($p)){
						$tmp = array(
							'sku' => $p->sku,
							'name_product' => $p->getName(),
							'qnt' => $t->quantity
						);
					}else{
						$tmp = array(
							'sku' => '',
							'name_product' => '',
							'qnt' => $t->quantity,
							'prezzo' => $t->price
						);
					}
					
					$tmp = array_merge($dati,$tmp);
					$tmp['total_final'] = $v->currency." ".$v->getTotalFinalFormatted();
					$toreturn[] = $tmp;
				}
			}
			
		}

		

		
		header("Content-type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=my_orders.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		$this->setVar('list',$toreturn);
		$this->output('export_orders.htm');

	}

	function exportAddresses(){
		$user = Marion::getUser();
		$list = Address::prepareQuery()->where('id_user',$user->id)->get();
		header("Content-type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=my_addresses.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		$this->setVar('list',$list);
		$this->output('export_addresses.htm');

	}

	

	
}


?>