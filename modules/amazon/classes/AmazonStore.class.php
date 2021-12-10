<?php

class AmazonStore extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'amazon_store'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = ''; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	


	
	function initMarkets(){
		foreach($this->marketplace as $name_market){
			$this->initMarket($name_market);
		}
	}



	function initMarket($name_market){
		

		$market_id = AmazonTool::getMarketplaceId($name_market);
		$url = AmazonTool::getEndpoint($name_market);
		$currency = AmazonTool::getMarketplaceCurrency($name_market);
		
		
		$GLOBALS['store'][$name_market]['currency'] =  $currency;//Merchant ID for this store
		$GLOBALS['store'][$name_market]['merchantId'] =  $this->merchantId;//Merchant ID for this store
		$GLOBALS['store'][$name_market]['marketplaceId'] = $market_id; //Marketplace ID for this store
		$GLOBALS['store'][$name_market]['keyId'] = 'AKIAIE3EOJJQ2PP5374Q';//$this->keyId; //Access Key ID
		$GLOBALS['store'][$name_market]['secretKey'] = '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';//$this->secretKey; //Secret Access Key for this store
		$GLOBALS['store'][$name_market]['serviceUrl'] = $url; //optional override for Service URL
		
		$GLOBALS['store'][$name_market]['MWSAuthToken'] = $this->token; //token needed for web apps and third-party developers
	
	}


	function getCategories(){
		if( $this->categories ){
			$list = json_decode($this->categories);
			return $list;
		}
		return false;
	}
	


	function getCarriers(){
		$database = _obj('Database');
		$sel = $database->select('*','amazon_carrier',"id_store={$this->id}");
		return $sel;

	}

	function getCarriersExit(){
		$database = _obj('Database');
		$sel = $database->select('*','amazon_carrier_exit',"id_store={$this->id}");
		return $sel;

	}



	function delete(){
		$db = marion::getDB();
		$db->delete('amazon_carrier',"id_store={$this->id}");
		$db->delete('amazon_carrier_exit',"id_store={$this->id}");
		parent::delete();
	}


	function getReportProductAmazon($market){
		
		$path = _MARION_MODULE_DIR_."amazon/reports/".$this->id."_".$market.".csv";
		$list = [];
		if( file_exists($path) ){
			$fp = fopen($path,'r');
			if (($headers = fgetcsv($fp, 0, "\t")) !== FALSE){
				if ($headers)
					$newHeaders = array();
				foreach ($headers as $key => $value) {
					$text = substr($value,0,200);
					$text = strtolower($text);
					$newHeaders[] = str_replace('-', '_', $text);
				}
				$result = array();
				while (($line = fgetcsv($fp, 0, "\t")) !== FALSE) {
					if ($line){
						if (sizeof($line)==sizeof($newHeaders)){
							$result[] = array_combine($newHeaders,$line);
						}else{
							fclose($fp);
						}
					}
				}
			}
			foreach($result as $v){
				$list[$v['seller_sku']] = $v['asin1'];
			}
		}
		
		
		
		
		return $list;
		
	}

	function computePrices($where_categories,$percentage=[]){
		
		$database = _obj('Database');

		

		$qnt = 1;
		
		
		
		$products = $database->select('p.sku,p.id,p.parent,s.id_tax,s.parent_price','product as p join product_shop_values as s on s.id_product=p.id',"deleted=0 AND visibility=1 AND {$where_categories}");
		
		
		//prendo le tasse
		$tasse = $database->select('*','tax');
		if( okArray($tasse) ){
			foreach($tasse as $v){
				$percentuale_tassa[$v['id']] = $v['percentage'];
			}
		}
		
		foreach($products as $k => $v){
			$id_return = $v['id'];

			if( $v['parent_price']){
				$id = $v['parent'];
			}else{
				$id = $v['id']; //id del prodotto
			}
			$taxCode = $v['id_tax']; // id della tassa
			
			$group = 1;
			
			$prezzo_default = $database->select('*','price',"product={$id} AND label='default'");
			if( okArray($prezzo_default) ){
				$prezzo_valore = $prezzo_default[0]['value'];
			}
			
			

			$listini = $database->select('p.id,p.dateStart,p.dateEnd','price as p join priceList as l on p.label=l.label',"product={$id} AND p.label <> 'default' AND p.label <> 'barred' and quantity <= {$qnt} AND (userCategory = {$group} OR userCategory = 0) and l.active=1 order by p.quantity DESC,userCategory DESC,l.priority DESC,p.quantity DESC");
			
			
			if( okArray($listini) ){
				$now = date('Y-m-d');
				
				foreach($listini as $k1 => $v1){
					if( $v1['dateStart'] ){
						
						if( strtotime( $v1['dateStart'] ) > strtotime($now) ){
						
							unset($listini[$k1]);
							continue;
						}
					}
					if( $v1['dateEnd'] ){
						if( strtotime( $v1['dateEnd'] ) < strtotime($now) ){
							unset($listini[$k1]);
							continue;
						}
					}
				}
				
				if( okArray($listini) ){
					$listino = array_values($listini)[0];
					
					
					$prezzo = $database->select('*','price',"id={$listino['id']}");
					
					if( okArray($prezzo) ){
						$prezzo = $prezzo[0];
						if( $prezzo['type'] == 'price'){
							$prezzo_valore = $prezzo['value'];

						}else{
							$prezzo_valore = $prezzo_valore - $prezzo_valore*$prezzo['value']/100;
						}
					}
					
				}
			}
			
			//aggiungo la tassa 
			if( $taxCode ){
				$percentuale = $percentuale_tassa[$taxCode];
				if( $percentuale ){
					$prezzo_valore = ESHOP::addVatToPrice($prezzo_valore,$percentuale);
				}
			}
			
			if( okArray($percentage) ){
				foreach($percentage as $perc){
					
					if( $prezzo_valore >= $perc['from'] && $prezzo_valore < $perc['to'] ){
						
						$prezzo_valore += $prezzo_valore*$perc['percentage']/100;
						break;
					}
				}
			}

			$prezzi_id[$id_return] = $prezzo_valore;
				
		
				
		}
		//debugga($percentage);
		//debugga($prezzi_id);exit;
		
		return $prezzi_id;


		
	}

	function getProducts($market){
		$loc = AmazonTool::getMarketplaceLang($market);
		
		$amazon_products = $this->getReportProductAmazon($market);


		$categorie = $this->getCategories();
		$where_categories = "section IN (";
		foreach($categorie as $v){
			if( (int)$v ){
				$where_categories .= "{$v},";
			}
			
		}
		$database = _obj('Database');
		$where_categories = preg_replace('/,$/',')',$where_categories);


		$setting = $database->select('*','amazon_marketplace_setting',"id_store= {$this->id} AND setting_key='percentage' AND marketplace='{$market}'");
		
		$percentage = [];
		if( okArray($setting) ){
			$percentage = unserialize($setting[0]['setting_value']);
		}


		
		$prezzi = $this->computePrices($where_categories,$percentage);
		
		
		
		
		
		$products = $database->select('p.id,p.sku,p.type,p.parent,p.stock,p.visibility as active,p.ean,p.upc,p.sku,l.name,l.description,i.disable_sync as disable,i.price as price_override,i.bullet_1,i.bullet_2,i.bullet_3','(product as p join productLocale as l on l.product=p.id)  left outer join amazon_product as i on i.id_product=p.id',"{$where_categories} AND (upc IS NOT NULL OR ean IS NOT NULL) AND locale='{$loc}' AND ((i.id_account IS NULL OR i.id_account={$this->id}) AND  (i.marketplace IS NULL OR i.marketplace='{$market}')) AND p.deleted=0");
		//debugga($products);exit;
		
		
		
		
		$parent_id = array();
		
		
		$new_products = [];
		foreach($products as $k => $p){
			
			$p['price'] = $prezzi[$p['id']];
			if( $p['type'] == 2 && !$p['parent']){
			}else{
				if( !$p['active'] ) $p['disable'] = 1;
				
				if( $p['disable'] && !array_key_exists($p['sku'],$amazon_products)){
					
					continue;
				}
			}
			unset($amazon_products[$p['sku']]);
			
			
			if( $p['parent'] ){
				//debugga($p['id']);
				if( $new_products[$p['parent']]['disable'] ){
					$p['disable'] = 1;
				}
				$parent_id[$p['parent']] = $p['parent'];
				
				
				$new_products[$p['parent']]['children'][$p['id']] = $p;
			}else{
				$new_products[$p['id']] = $p;
			}
		}
		
		
		
		$where_parent= '';
		foreach($parent_id as $v){
			$where_parent .= "{$v},";
		}
		
		$where_parent = preg_replace('/\,$/','',$where_parent);
		$dati_parent = $database->select('p.id,p.sku,p.visibility as active,i.disable_sync as disable,i.parent_description,i.price as price_override,i.bullet_1,i.bullet_2,i.bullet_3,p.deleted','product as p left outer join amazon_product as i on i.id_product=p.id',"p.id IN ({$where_parent}) AND ((i.id_account IS NULL OR i.id_account={$this->id}) AND  (i.marketplace IS NULL OR i.marketplace='{$market}'))");
		
	
		foreach($dati_parent as $par){
			foreach($par as $k => $v){
				$new_products[$par['id']][$k] = $v;
			}
			if( $par['deleted'] || !$par['active'] ){
				$new_products[$par['id']]['disable'] = 1;
			}
			
		}
		
		
		
		//debugga($new_products);exit;
	
	
		if( okArray($amazon_products) ){
			foreach($amazon_products as $k => $v){
				$new_products[$k] = array(
						'id' => $k,
						'sku' => $v['sku'],
						'asin' => $amazon_products[$v['sku']],
						'disable' => 1,
						'active' => 0,
						'name' => $v['sku'],
						'description' => $v['description'],
				);
			}
			
		}
	
		
		
		$this->products = $new_products;
	}






	


}



?>