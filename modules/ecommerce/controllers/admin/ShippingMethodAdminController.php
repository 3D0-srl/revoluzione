<?php
use Marion\Core\Marion;
use  Marion\Controllers\AdminModuleController;
use Shop\{ShippingMethod,ShippingArea,Tax};
use \Country;
class ShippingMethodAdminController extends AdminModuleController{
	public $_auth = 'ecommerce';
	


	function displayForm(){
		$this->setMenu('manage_shippings');
		
		$action = $this->getAction();


		

		if( $this->isSubmitted()){
			$formdata = $this->getFormdata();
			
			$array = $this->checkDataForm('shipping',$formdata);
			
			if( $array[0] == 'ok'){

				if( $action == 'add'){
					$obj = ShippingMethod::create();
				}else{
					$obj = ShippingMethod::withId($array['id']);
				}
				$obj->set($array);
				$obj->delAllWeights();
			
				if( okArray($formdata['pesi']) ){
					foreach($formdata['pesi'] as $v){
						
						$obj->addWeight($v['weight']);
					}
				}
				
				

				$res = $obj->save();
				if( is_object($res) ){
					$this->saved();
				}else{
					$this->errors[] = $res;
				}


			}else{
				$this->errors[] = $array[1];
			}

			$dati = $array;

			if( okArray($formdata['pesi']) ){
				
				$this->setVar('cont_pesi',count($formdata['pesi']));
				
				foreach($formdata['pesi'] as $v){
					$weight[] = $v['weight'];
				}
				$this->setVar('weight',$weight);
			}
			

		}else{
		
			createIDform();
		
			$id = $this->getID();
			
			if($action != 'add'){
				$obj = ShippingMethod::withId($id);
				
				$dati =  $obj->prepareForm2();

				$weight = $obj->getWeights();
				$this->setVar('cont_pesi',count($weight));
				$this->setVar('weight',$weight);
				
				

				if($action == 'duplicate'){
					unset($dati['id']);
					unset($dati['images']);
					$action = "add";
				}
			}else{
				$dati = NULL;
			}
		}
		$dataform = $this->getDataForm('shipping',$dati);
				
		$this->setVar('dataform',$dataform);
		$this->output('form_shipping_method.htm');

	}


	function setMedia(){
		$this->registerJS($this->getBaseUrl().'modules/ecommerce/js/admin/shipping.js','end');

	}
	

	function displayList(){
		$this->setMenu('manage_shippings');
		$this->showMessage();


		$query = ShippingMethod::prepareQuery();
		$shippings = $query->get();

		
		$this->setVar('shippings',$shippings);
		$this->output('list_shipping_method.htm');
			
	}

	
	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Metodo di spedizione salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Metodo di spedizione eliminato con successo','success');
		}

		if( _var('saved_sates') ){
			$this->displayMessage('Tariffe salvate con successo','success');
		}
	}

	function saved(){
		$this->redirectTolist(array('saved'=>1));
	}


	function delete(){
		$id = $this->getID();

		$obj = ShippingMethod::withId($id);
		if( is_object($obj) ){
			
			$obj->delete();
			$obj->delAllWeights();
			
		}
		$this->redirectToList(array('deleted'=>1));
		

		
	}



	function displayContent(){
		$this->setMenu('manage_shippings');
		$action = $this->getAction();
		

		switch($action ){
			case 'rates':
				$this->rates();
				break;
			
		}
		

	
	}
	

	//todo
	function checkRates($dati){

		foreach($dati as $k => $v){
			foreach($v['price'] as $w => $p){
				
			}
		}
		//debugga($dati);exit;
	}


	function rates(){
		

		if( $this->isSubmitted()){

			$formdata = $this->getFormdata();
			
			//todo
			//$this->checkRates($formdata['valori']);
			
			$id = $formdata['shipping'];
			$shipping = ShippingMethod::withId($id);
			$shipping->delAllPrice();

			foreach($formdata['valori'] as $v){
					$data = array(
						'country' => $v['country'],
						);
					foreach($v['price'] as $weight => $price){
						$data['weight'] = (int)$weight;
						
						if(!$price && !is_numeric($price)){
							$data['price'] = -1;
						}else{
							$data['price'] = (float)$price;
						}
						//debugga($data);exit;
						$shipping->addPrice($data);
					}
			}
			$this->redirectTolist(array('saved_sates'=>1));
		}else{

			$id = $this->getID();
			$shipping = ShippingMethod::withId($id);
			
			$weight = $shipping->getWeights();
			$colspan = count($weight)+1;
			$this->setVar('colspan' ,$colspan);
			//prendo le aree
			$aree = ShippingArea::prepareQuery()->get();
			foreach($aree as $v){
				$select_aree[$v->id] = $v->name;
			}
			$this->setVar('select_aree' ,$select_aree);
			$price = $shipping->getAllPrice();
			
			
			foreach($price as $v){
				$config_shipping[$v['country']][$v['weight']] = $v['price'];
			}
			$i = 1;
			foreach($config_shipping as $k => $v){
				$tmp = array();
				$tmp['values'] = $v;
				$tmp['country'] = $k;
				$toreturn[$i] = $tmp;
				$i++;
			}
			
			$this->setVar('config_shipping' ,$toreturn);
			$this->setVar('num_config_shipping' ,count($toreturn));
			foreach($weight as $k => $v){
				if(!$weight[$k-1]) {
					$inizio = 0;
				}else{
					$inizio = $weight[$k-1];
				}
				$fascia[$k]['inizio'] = $inizio;
				$fascia[$k]['fine'] = $v;
			}
			
			$this->setVar('fascia' ,$fascia);
			//debugga($fascia);exit;
			$this->setVar('weight' ,$weight);
			$this->setVar('shipping' ,$shipping);
			
			$this->output('shipping_rates.htm');
		}
	}



	function ajax(){
		
		$action = $this->getAction();
		$id = $this->getID();
		switch($action){
			case 'change_visibility':
				$obj = ShippingMethod::withId($id);
				if( is_object($obj) ){
					if( $obj->visibility ){
						$obj->visibility = 0;
					}else{
						$obj->visibility = 1;
					}
					
					$obj->save();
					$risposta = array(
						'result' => 'ok',
						'status' => $obj->visibility
					);
				}else{
					$risposta = array(
						'result' => 'nak'	
					);
				}
				break;
				
		}

		echo json_encode($risposta);
		
	}

	function array_nazioni(){
		//$toreturn = array( $GLOBALS['gettext']->strings['seleziona'] );
		$nazioni = Country::getAll();
		foreach($nazioni as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}

	function array_taxCode(){
		$toreturn = array( 'Nessuna tassa' );
		$tasse = Tax::prepareQuery()->where('active',1)->orderBy('percentage')->get();
		if( okArray($tasse) ){
			foreach( $tasse as $tax){
				$toreturn[$tax->id] = $tax->get('name');	
			}
		}
		return $toreturn;
	}


	

}



?>