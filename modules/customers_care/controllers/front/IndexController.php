<?php
class IndexController extends BackendController{

	



	function display(){
		require_once('modules/customers_care/classes/CustomersCareTicketType.class.php');
		require_once('modules/customers_care/classes/CustomersCareTicket.class.php');
		$action = $this->getAction();
		switch($action){
			case 'new':
				$this->newTicket();
				break;
			default:
				$user = Marion::getUser();
				$list = CustomersCareTicket::prepareQuery()->where('owner',$user->id)->get();
				if( okArray($list) ){
					$this->setVar('list', $list);
				}
				$this->output('list.htm');
				break;

		}
		

	}

	function newTicket(){
		
		if( $this->isSubmitted() ){
			$dati = $this->getFormdata();
			$user = Marion::getUser();
			$dati['owner'] = $user->id;
			
			CustomersCareTicket::create()->set($dati)->save();
			

		}
		
		$list = CustomersCareTicketType::prepareQuery()->get();
		$select = array();
		foreach($list as $v){
			$select[$v->id] = $v->get('name');
		}
		$this->setVar('ticket_types',$select);
	
		$this->output('new.htm');
	}
}


?>