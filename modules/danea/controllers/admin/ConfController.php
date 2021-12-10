<?php
class ConfController extends ModuleController{
	public $_twig = true;


	function setMedia(){
		parent::setMedia();
		$this->registerJS('../modules/danea/js/script.js','end','10000');
	
	}


	function display(){
		
		$payments_list = $this->getPayments();
		$attributes_list = $this->getAttributes();
		$taxes_list = $this->getTaxes();
		$prices_list = $this->getPrices();

		$attribute_sets_list = $this->getAttributeSets();

		$this->setVar('prices_list',json_encode($prices_list));
		$this->setVar('payments_list',json_encode($payments_list));
		$this->setVar('attributes_list',json_encode($attributes_list));
		$this->setVar('taxes_list',json_encode($taxes_list));
		$this->setVar('attribute_sets_list',json_encode($attribute_sets_list));
		
		
		$this->setMenu('manage_modules');
		

		$filtri_ricerca_ok = Marion::isActivedModule('filtri_ricerca');
		$this->setVar('filtri_ricerca_ok',$filtri_ricerca_ok);
		
		if( $this->isSubmitted()){
			$dati = $this->getFormdata();
			
			
			if( $dati['manage_discount_like_product'] ){
				$update['discount_vat']['obbligatorio'] = 1;
				$update['discount_code']['obbligatorio'] = 1;
			}

			if( $dati['enable_credentials'] ){
				
				$update['username']['obbligatorio'] = 1;
				$update['password']['obbligatorio'] = 1;
			}
		
			$array = $this->checkDataForm('danea_setting',$dati,$update);
			
			


			list($valori,$errori) = $this->checkDataExport($dati);
			
			
			
			$attributes = $valori['mapping_attributes'];
			$payments = $valori['mapping_payments'];
			$taxes = $valori['mapping_taxes'];
			$prices = $valori['mapping_prices'];

			$attribute_sets = $valori['mapping_attribute_sets'];
			
			if( $array[0] == 'ok' ){
				$this->checkAttributesImport($array);
			}


			if( $array[0] == 'ok'){
				if( okArray($errori) ){
					$array[0] = 'nak';
					foreach($errori as $v){
						$this->errors[] = $v;
					}
				}
			}
			if( $array[0] == 'ok'){

				unset($array[0]);
				$array['mapping_features'] = $dati['mapping_features'];
				foreach($array as $k => $v){
					if( is_array($v) ){
						$v = serialize($v);
					}
					Marion::setConfig('danea_setting',$k,$v);
				}

				foreach($valori as $k => $v){
					Marion::setConfig('danea_setting',$k,serialize($v));
				}

				$this->displayMessage('Dati salvati con successo!');

			}else{
				$this->errors[] = $array[1];
			}
		}else{
			$dati = Marion::getConfig('danea_setting');
			$dati['status_orders'] = unserialize($dati['status_orders']);
			$attributes = unserialize($dati['mapping_attributes']);
			$payments = unserialize($dati['mapping_payments']);
			$taxes = unserialize($dati['mapping_taxes']);
			$prices = unserialize($dati['mapping_prices']);
			$attribute_sets = unserialize($dati['mapping_attribute_sets']);

			$dati['mapping_features'] = unserialize($dati['mapping_features']);	
		}
		

		if( $filtri_ricerca_ok ){
			require_once('../modules/filtri_ricerca/classes/ProductFeature.class.php');
			$_features = ProductFeature::prepareQuery()->get();
			foreach($_features as $v){
				$features[$v->id]['value'] = $v->get('name');
				if( $dati['mapping_features'][$v->id] ){
					$features[$v->id]['selected'] = $dati['mapping_features'][$v->id];
				}
			}
			//debugga($features);exit;
			$this->setVar('features',$features);
		}
		

		$tab = _var('current_tab')?_var('current_tab'):0;

		$dataform = $this->getDataForm('danea_setting',$dati);
		$this->setVar('dataform',$dataform);
		$this->setVar('current_tab',$tab);


		
		/*$payments = array(
			'PAYPAL' => 'Paypal',
			'BONIFICO' => 'Bonifio'
		);
		$attributes = array(
			'Nicotina' => 'Size',
			'Dimensione' => 'Color'
		);*/





		$this->setVar('attributes',json_encode($attributes));
		$this->setVar('payments',json_encode($payments));
		$this->setVar('taxes',json_encode($taxes));
		$this->setVar('prices',json_encode($prices));
		$this->setVar('attribute_sets',json_encode($attribute_sets));

		

		
		$this->output('conf.htm');
	}

	function checkDataExport($data=array()){
		$errori = array();
		$mapping_keys = array('mapping_payments','mapping_attributes','mapping_taxes','mapping_prices');
		foreach($mapping_keys as $v){
				$data_tmp = $data[$v];
				

				foreach($data_tmp as $v1){
					$valori[$v][$v1['marion']] = trim($v1['danea']);
				}
				if( count($valori[$v]) != count($data_tmp) ){
					switch($v){
						case 'mapping_payments':
							$errori[] = "Due o più mappatture per lo stesso pagamento";
							break;
						case 'mapping_attributes':
							$errori[] = "Due o più mappatture per lo stesso attributo";
							break;
						case 'mapping_taxes':
							$errori[] = "Due o più mappatture per la stessa tassa";
							break;
					}
				}else{
					foreach($valori[$v] as $v1){
						if( !$v1 ){
							switch($v){
						case 'mapping_payments':
								$errori[] = "Una o più mappatture pagamento ha dei dati errati o nulli";
								break;
							case 'mapping_attributes':
								$errori[] = "Una o più mappatture attributo ha dei dati errati o nulli";
								break;
							case 'mapping_taxes':
								$errori[] = "Una o più mappatture tassa ha dei dati errati o nulli";
								break;
						}
						}
					}
				}
		}

		foreach($data['mapping_attribute_sets'] as $k => $v){
			$attribute_sets[] = $v['id'];
			$campi = array_keys($v);
			foreach($campi as $c){
				if( $c != 'id'){
					$valori['mapping_attribute_sets'][$v['id']][$c] = trim($v[$c]);
					if( !trim($v[$c]) ){
						$errori[] = "Una o più mappatture per l'inseieme attributi ha dei dati errati o nulli";
					}
				}
			}
		}
		if( count($attribute_sets) != count(array_unique($attribute_sets)) ){
			$errori[] = "Inseiemi attributi duplicati";
		}
		
		
		return array(
			$valori,
			$errori
		);
		
	}


	function ajax(){
		$action = $this->getAction();
		switch($action){
			/*case 'parameters':
				$toreturn = array(
					'payments' => $this->getPayments(),
					'attributes' => $this->getAttributes(),
					'taxes' => $this->getTaxes(),
				);
				echo json_encode($toreturn);
				break;*/
			case 'logs':
				$file = _var('file');
				$lines = array();
				$fn = fopen('../modules/danea/logs/'.$file.".log","r");
			    while(! feof($fn))  {
					$result = fgets($fn);
					$lines[] = $result;
				}
				fclose($fn);
				$data = '';
				krsort($lines);
				foreach($lines as $v){
					if(trim($v) ){
						$data .= $v."\n";
					}
				}
				
				
				echo json_encode(array('data'=>$data));
				break;
			case 'get_attributes':
				$set = _var('set');
				$attributeSet = AttributeSet::withLabel($set);
				$attributes = $attributeSet->getComposition();
				foreach($attributes as $k => $v){
					$attr = Attribute::withId($k);
					if( is_object($attr) ){
						$toreturn[$attr->label] = $attr->get('name');
					}
				}
				echo json_encode($toreturn);
				exit;
				
				break;
		}
	}


	function getPayments(){
		$list = PaymentMethod::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->code] = $v->get('name');
		}
		return $toreturn;

		
	}

	function getAttributes(){
		$list = Attribute::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->label] = $v->get('name');
		}
		return $toreturn;

	
	}

	function getAttributeSets(){
		$database = _obj('Database');
		$list = $database->select('*','attributeSet',"deleted=0");
		
		
		foreach($list as $v){
			$toreturn[$v['label']] = $v['label'];
		}
		return $toreturn;

	
	}

	function getTaxes(){
		$toreturn = array(0 =>'Nessuna');
		$list = Tax::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;

	
	}

	function getPrices(){
		$list = PriceList::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->label] = $v->get('name');
		}
		return $toreturn;

	
	}


	function orderStatus(){
		$list = CartStatus::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->label] = $v->get('name');
		}
		return $toreturn;
	}


	function listiniDanea(){
		
		for( $k = 1; $k <= 9; $k++ ){
			$toreturn[$k] = "Listino ".$k;
		}
		return $toreturn;
	}

	function childSku(){
		return array(
			'parent_sku' => 'Codice articolo padre',
			'barcode' => 'Barcode',
		);
	}


	

	function attributeSetMapping(){
		
		$toreturn[0] = 'Nessuna';
		$list = $this->getAttributeSets();
		foreach($list as $k => $v){
			$toreturn[$k] = $v;
		}
		return $toreturn;
	}


	function customFields(){
		for( $k = 1; $k <= 4; $k++ ){
			$toreturn[$k] = "Libero ".$k;
		}
		return $toreturn;
	}


	function checkAttributesImport(&$array){
		
		if( !$array['manage_variations_import_advanced'] ){
				if( $array['mapping_size'] ){
					$size = Attribute::withLabel($array['mapping_size']);
					if( is_object($size) ){
						$id_size = $size->id;
					}

					$color = Attribute::withLabel($array['mapping_color']);
					if( is_object($color) ){
						$id_color = $color->id;
					}

					if( $array['mapping_size_set'] ){
						if( !$id_size ){
							$array[0] = 'nak';
							$array[1] = "Occorre specificare una mappatura per la variazione 'taglia'";
							return;
						}

						 $size_set = AttributeSet::withLabel($array['mapping_size_set']);
						 if( $size_set ){
							$composition = array_keys($size_set->getComposition());
							if( count($composition) > 1 ){
								$array[0] = 'nak';
								$array[1] = "L'insieme attributo 'taglia' non possiede un'unica variazione";
								return;
							}else{
								if( count(array_intersect($composition,array($id_size))) != 1 ){
									$array[0] = 'nak';
									$array[1] = "L'insieme attributo 'taglia' non possiede la variazione taglia";
									return;
								}
							}
						 }
					}

					if( $array['mapping_color_set'] ){

						if( !$id_color ){
							$array[0] = 'nak';
							$array[1] = "Occorre specificare una mappatura per la variazione 'colore'";
							return;
						}
						 $color_set = AttributeSet::withLabel($array['mapping_color_set']);
						 if( $color_set ){
							$composition = array_keys($color_set->getComposition());
							if( count($composition) > 1 ){
								$array[0] = 'nak';
								$array[1] = "L'insieme attributo 'colore' non possiede un'unica variazione";
								return;
							}else{
								if( count(array_intersect($composition,array($id_color))) != 1 ){
									$array[0] = 'nak';
									$array[1] = "L'insieme attributo 'colore' non possiede la variazione colore";
									return;
								}
							}
						 }
					}

					if( $array['mapping_size_color_set'] ){
						
						if( !$id_color ){
								$array[0] = 'nak';
								$array[1] = "Occorre specificare una mappatura per la variazione 'colore'";
								return;
						}
						if( !$id_size ){
							$array[0] = 'nak';
							$array[1] = "Occorre specificare una mappatura per la variazione 'taglia'";
							return;
						}
						 $size_color_set = AttributeSet::withLabel($array['mapping_size_color_set']);
						 if( $size_color_set ){
							 
							$composition = array_keys($size_color_set->getComposition());
							
							if( count(array_intersect($composition,array($id_color,$id_size))) != 2 ){
								$array[0] = 'nak';
								$array[1] = "L'insieme attributo 'taglia-colore' non possiede entrambe le variazioni taglia e colore";
								return;
							}

							
						 }
					}
				}
			}
	}


	function disabledFields(){
		return array(
			'name' => 'nome',
			'description' => 'descrizione',
			'descriptionShort' => 'descrizione breve',
			//'quantity' => 'quantità',
			//'default_price' => 'prezzo di default',
			'ean' => 'ean',
			'upc' => 'upc',
			'immagini' => 'immagini',
			'section' => 'categoria',
			'manufacturer' => 'produttore',
			
		);
	}


	function descriptionMapping(){
		return array(
			'' => 'nessuno',
			'DescriptionHtml' => 'Descrizione HTML',
			'Notes' => 'Note',
		);
	}

	function barcodeMapping(){
		return array(
			'0' => 'nessuno',
			'Barcode' => 'Barcode',
			'Code' => 'Codice',
		);
	}


	function logFiles(){
		$list = scandir('../modules/danea/logs');
		
		foreach($list as $v){
			if( is_file('../modules/danea/logs/'.$v) ){
				$v = preg_replace('/.log/','',$v);
				$toreturn[$v] = $v;
			}
		}
		krsort($toreturn);
		return $toreturn;
	}

	function deletedProductActions(){
		return array(
			'' => 'nessuna',
			'disable' => "rendi l'articolo offline",
			'delete' => "elimina l'articolo"
		);
	}




	

}



?>