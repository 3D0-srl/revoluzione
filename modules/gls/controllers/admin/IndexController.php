<?php
class IndexController extends ModuleController{

	

	function display(){
		$action = $this->getAction();
		switch($action){
			case 'export':
				$this->export();
				break;
			case 'conf':
				$this->conf();
				break;
		}
	}
	

	function export(){
		$this->setMenu('gls_export');
		if( $this->isSubmitted()){
			

			$type = _var('type');
			switch($type){
				case 'status':
					$dati = $this->getFormData();
					$array = $this->checkDataForm('gls_export_export',$dati);
					if( $array[0] == 'ok'){
						
						$filename = GLS::exportOrders($array['dateStart'],$array['dateEnd'],$array['status_order'],$array['change_status']);
						

						if( $filename  ){
							if( $filename == -1 ){
								$this->displayMessage('Nessun ordine da esportare','warning');
							}else{
								$this->setVar('filename',$filename);
								$this->displayMessage('Il file è stato generato con successo');
							}
						}
					}else{
						$this->errors[] = $array[1];
					}
					$dati['dateStart'] = $array['dateStart'];
					$dati['dateEnd'] = $array['dateEnd'];

					break;
				case 'orders':
					$dati1 = $this->getFormData();
					$array = $this->checkDataForm('gls_export_conf_by_id',$dati1);

					if( $array[0] == 'ok'){
						$list_orders = explode(',',$array['orders']);
						foreach($list_orders as $v){
							if( (int)trim($v)){
								$ordini[] = trim($v);
							}else{
								$array[0] = 'nak';
								$array[1] = "Gli <b>ID</b> degli ordini non sono validi";
							}
						}
					}
					if( $array[0] == 'ok'){
						
						$filename = GLS::exportOrdersById($ordini,$array['change_status1']);
						if( $filename ){
							if( $filename == -1 ){
								$this->displayMessage('Nessun ordine da esportare','warning');
							}else{
								$this->setVar('filename',$filename);
								$this->displayMessage('Il file è stato generato con successo');
							}
						}
						$this->displayMessage('Il file è stato generato con successo');
					}else{
						$this->errors[] = $array[1];
					}
					

					break;
			}
			

			
		}
			

		$dataform = $this->getDataForm('gls_export_export',$dati);
		$dataform1 = $this->getDataForm('gls_export_conf_by_id',$dati1);
		
		$this->setVar('dataform',$dataform);
		$this->setVar('dataform1',$dataform1);
		$this->output('export.htm');
	}


	function conf(){
		$this->setMenu('manage_modules');

		
		$metodi = ShippingMethod::prepareQuery()->get();
		$this->setVar('metodi',$metodi);


		if( $this->isSubmitted()){

			
			$dati = $this->getFormData();
			
			$array = $this->checkDataForm('gls_conf',$dati);
			
			if( $array[0] == 'ok'){
				unset($array[0]);
				foreach($array as $k => $v){
					if( okArray($v) ){
						Marion::setConfig('GLS_export',$k,serialize($v));
					}else{
						Marion::setConfig('GLS_export',$k,$v);
					}
				}
				
				$this->displayMessage('Dati saòvati con successo');
			}else{
				$this->errors[] = $array[1];
			}

			$this->setVar('appuntamento',$dati['appuntamento']);
			$this->setVar('courier',$dati['courier']);
				
			

			/*if( _var('type') == 'couriers'){
				$array = $this->checkDataForm('gls_select_courier',$dati);
				
				if( $array[0] == 'ok'){
					unset($array[0]);
					foreach($array as $k => $v){
						Marion::setConfig('GLS_export',$k,serialize($v));
					}
					
					$this->displayMessage('Dati saòvati con successo');
				}else{
					$this->errors[] = $array[1];
				}
				
			}
			
			$dati['dateStart'] = $array['dateStart'];
			$dati['dateEnd'] = $array['dateEnd'];*/

			
		}else{
			$dati = Marion::getConfig('GLS_export');
			$this->setVar('appuntamento',unserialize($dati['appuntamento']));
			$this->setVar('courier',unserialize($dati['courier']));
		}

		
			

		$dataform = $this->getDataForm('gls_conf',$dati);
		
		$this->setVar('dataform',$dataform);
		$this->output('conf.htm');
	}

	function statusOrder(){
		$list = CartStatus::prepareQuery()->where('active',1)->get();

		
		foreach($list as $v){
			$toreturn[$v->label] = $v->get('name');
		}
		return $toreturn;
	}

	function statusOrderSent(){
		$list = CartStatus::prepareQuery()->where('active',1)->where('sent',1)->get();

		
		foreach($list as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}

	
	function payments(){
		$list = PaymentMethod::prepareQuery()->where('enabled',1)->get();
		
		foreach($list as $v){
			$toreturn[$v->id] = $v->get('name');
		}

		return $toreturn;
	}


	function shippingMethods(){
		$list = ShippingMethod::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->id] = $v->get('name');
		}

		return $toreturn;
	}
}

?>