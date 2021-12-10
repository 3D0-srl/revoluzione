<?php
namespace Shop;
use Marion\Core\Base;
use Marion\Core\Marion;
class ShippingMethod extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'shippingMethod'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'shippingMethodLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'shippingMethod';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 

	
	//aggiunge un peso al metodo di spedizione
	//ALTER TABLE `shippingMethodWeight` ADD UNIQUE `unique_index`(`weight`, `shippingMethod`);
	public function addWeight($val){
		
		if($this->hasId()){
			$val = (int)$val;
			
			$toinsert = array(
				'shippingMethod' => $this->getId(),
				'weight' => $val,
			);
			$database = Marion::getDB();
			$database->insert('shippingMethodWeight',$toinsert);
		
		}

		return $this;

	}
	

	public function delWeight($value){
		if($this->hasId()){
			$database = Marion::getDB();
			$database->delete('shippingMethodWeight',"shippingMethod={$this->getId()} and weight={$value}");
		}
		return $this;

	}

	public function delAllWeights(){
		if($this->hasId()){
			$database = Marion::getDB();
			$database->delete('shippingMethodWeight',"shippingMethod={$this->getId()}");
		}
		return $this;
	}


	public function getWeights(){
		$toreturn = array();
		if($this->hasId()){
			$database = Marion::getDB();
			$weight = $database->select('weight','shippingMethodWeight',"shippingMethod={$this->getId()} order by weight");
			if(okArray($weight)){
				foreach($weight as $v){
					$toreturn[] = $v['weight'];
				}

			}
		}
		return $toreturn;
	}


	public function delAllPricies(){
		if($this->hasId()){
			$database = Marion::getDB();
			$database->delete('shippingMethodPrice',"shippingMethod={$this->getId()}");
		}
		return $this;
	}

	public function addPrice($array=array()){
		if($this->hasId()){
			if( $array['country'] && $array['weight'] && is_numeric($array['price']) ){
				$array['shippingMethod'] = $this->getId();
				$database = _obj('Database');
				$database->insert('shippingMethodPrice',$array);
				
			}
		}

		return $this;

	}

	//restituisce la tariffa di spedizione per una nazione e per un peso specificato
	// la variabile $config_ecommerce se valorizzata a true valuta le eventuali condizioni di spedizione gratuita stabiliti nella configurazione dell'ecommerce
	public function getPrice($country=NULL,$weight=NULL,$config_ecommerce = true,$total_cart=0){
		if($this->hasId()){
			
			if( $config_ecommerce ){
				//verifico se � abilitata la spedizione gratuita 
				$freeShipping = Marion::getConfig('eshop','enableFreeShipping');
				
				if( $freeShipping ){
					
					$start = Marion::getConfig('eshop','startFreeShipping');
					$end = Marion::getConfig('eshop','endFreeShipping');
					$check = false;
					if( $start && $end){
						if( strtotime($start) <= time() && strtotime($end) > time() ){
							$check = true;
						}
					}elseif($start){
						if( strtotime($start) <= time() ){
							$check = true;
						}
					}elseif($end){
						if( strtotime($end) > time() ){
							$check = true;
						}

					}else{
						$check = true;
					}
					
					if( $check ){
						$shippingMethods =  unserialize(Marion::getConfig('eshop','shippingMethodFreeShipping')); 
						$shippingAreas =  unserialize(Marion::getConfig('eshop','shippingAreaFreeShipping')); 
						
						if( okArray($shippingAreas) && okArray($shippingMethods) ){
							if( in_array($this->id,$shippingMethods) ){
								
								//ora verifico se la nazione sta nelle arre di spedizione
								$check_area = false;
								$database = Marion::getDB();
								foreach($shippingAreas as $area){
									if( okArray($database->select('*','shippingAreaComposition',"area={$area} AND country ='{$country}'")) ){
										$check_area = true;
										break;
									}
								}
								if( $check_area ){
									if( !$total_cart ){
										$total_cart = Cart::getCurrentTotal();
									}
									$threshold = Marion::getConfig('eshop','thresholdFreeShipping');
									if( $total_cart > $threshold ){
										return 0;

									}
								}
							}
						}
					}

				}
			}


			if( $this->freeShipping) {
				if( in_array($country,$this->countriesFreeShipping) ){
					return 0;
				}else{
					return 'NAN';
				}
			}

			//se il peso � 0 allora il costo della spedizione � pari a 0. Questo � un caso che si verifica solo se il prodotto nel carrello ha spese di spedizione gratuite e quindi peso 0
			if( !$weight && is_numeric($weight) ) return 0;
			
			if(!$country && !$weight) return 'ERROR';
			
			$aree = ShippingArea::fromCountry($country);
			$database = Marion::getDB();
			foreach($aree as $area){


				$country = $area->id;
				if(!$country && !$weight) return 'ERROR';
				$where = "shippingMethod={$this->getId()}";
				
				$where .=  " AND country = {$country}";
				
				//PRIMO CONTROLLO
				$where1 = $where;
				$where1 .=  " AND weight >= {$weight} and price >= 0";
				
				//controllo se esiste una tariffa di spedizione per il peso specificato ovvero con peso maggiore o uguale di quello indicato
				$select = $database->select('*','shippingMethodPrice',"{$where1} order by weight ASC limit 1");
				
				//se non esiste prendo la tariffa presente per il peso maggiore 
				if( !okArray($select) ){
					//SECONDO CONTROLLO
					//$select = $database->select('*','shippingMethodPrice',"{$where} order by weight DESC limit 1");
				}
				
				if(okArray($select)){
					$value = $select[0]['price'];
					if( $this->taxCode){
						$tax = Tax::withId($this->taxCode);
				
						if( is_object($tax) ){
							
							$value = ESHOP::addVatToPrice($value,$tax->percentage);
							
						}
					}
					return Eshop::priceValue($value);
				}
			}
			return 'NAN';
			

		}
		return false;
	}

	public function getPriceFormatted($country=NULL,$weight=NULL){
		$price = $this->getPrice($country,$weight);
		if(is_numeric($price)){
			return number_format($price, 2, ',', '');
		}else{
			return $price;
		}
	
	}

	public function getAllPrice(){
		if($this->hasId()){
			$database = Marion::getDB();
			$select = $database->select('*','shippingMethodPrice',"shippingMethod={$this->getId()}");
			return $select;
		}
		return false;
	}

	public function delAllPrice(){
		if($this->hasId()){
			$database = Marion::getDB();
			$select = $database->delete('shippingMethodPrice',"shippingMethod={$this->getId()}");
			return $select;
		}
		return $this;
	}
	

	function getCountries(){
		if($this->hasId()){
			$database = Marion::getDB();
			$select = $database->select('distinct(country)','shippingMethodPrice',"shippingMethod={$this->getId()}");
			$toreturn = array();
			if( okArray($select) ){
				foreach($select as $v){
					$countries = $database->select('country','shippingAreaComposition',"area={$v['country']}");
					
					if( okArray($countries) ){
						foreach($countries as $v1){
							$toreturn[$v1['country']] = $v1['country'];
						}
					}
	
				}
			}
			
			if( okArray($toreturn) ){
				$toreturn = array_values($toreturn);
			}
			
			return $toreturn;
		}
		return false;
	}

	// METODI STATICI
	public static function getAll($country,$weight){
		$database = Marion::getDB();
		$aree = ShippingArea::fromCountry($country);
		
		foreach($aree as $area){
			
			$area_country = $area->id;
			$where =  "country = {$area_country} AND weight >= {$weight} and price >= 0";
			
			$query = self::prepareQuery()
				->where('visibility',1)
				->whereExpression("(freeShipping = 1 OR id in (select shippingMethod from shippingMethodPrice where {$where}))");
			
			$shippingMethods = $query->get();
			//debugga($shippingMethods);exit;
			$database->registerQuery($query->lastquery,'shippingMethodPrice');
			
		
			if(okArray($shippingMethods)){
				foreach($shippingMethods as $shippingMethod){
					if( is_object($shippingMethod) ){
						if( $shippingMethod->freeShipping && !in_array($country,$shippingMethod->countriesFreeShipping) ) continue;
						$toreturn[$shippingMethod->id] = $shippingMethod;
					}
					
				}
				
			}
		}
		if( okArray($toreturn) ){
			return array_values($toreturn);
		}
		return false;

	}

	//restituisce l'immagine all'indice specificato del formato specificato
	function getUrlImage($type='original',$watermark=true,$name_image=NULL){
		if( $this->image ){
			$database = Marion::getDB();
		
			$img = $database->select('i.*',"image as i join imageComposed as c on c.{$type}=i.id","c.id={$this->image}");
			if(okArray($img) ){
				if( $name_image ){
					$name = $name_image;
				}else{
					$name = $img[0]['filename_original'];
					$name = explode('.',$name);
					$name = Marion::slugify($name);
					$name = $name[0].".".$img[0]['ext'];
				}
				
				$type_short = $this->getTypeImageUrl($type);
				
				if( !$watermark ){
					return "/img/{$this->image}/{$type_short}-nw/{$name}";
				}else{
					return "/img/{$this->image}/{$type_short}/{$name}";
				}
			}
		}
		return '';
	}

	function getTypeImageUrl($type){
		switch( $type ){
			case 'thumbnail':
				$type = 'th';
				break;
			case 'small':
				$type = 'sm';
				break;
			case 'medium':
				$type = 'md';
				break;
			case 'large':
				$type = 'lg';
				break;
			case 'original':
				$type = 'or';
				break;
			default:
				$type='or';

		}
		return $type;

	}


	//controllo validita' del metodo di speidzione a partire da condizioni stabilite
	function checkConditions(){
		$check = true;
		Marion::do_action('check_conditions_shipment',array($this,&$check));
		return $check;
	}
}

?>