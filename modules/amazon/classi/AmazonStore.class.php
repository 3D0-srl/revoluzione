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
	

	public $check_last_operation_finished = false;

	
	function initMarkets(){
		foreach($this->marketplace as $name_market){
			$this->initMarket($name_market);
			/*$market_id = AmazonTool::getMarketplaceId($name_market);
			$url = AmazonTool::getEndpoint($name_market);
			
			$GLOBALS['store'][$name_market]['merchantId'] =  $this->merchantId;//Merchant ID for this store
			$GLOBALS['store'][$name_market]['marketplaceId'] = $market_id; //Marketplace ID for this store
			$GLOBALS['store'][$name_market]['keyId'] = $this->keyId; //Access Key ID
			$GLOBALS['store'][$name_market]['secretKey'] = $this->secretKey; //Secret Access Key for this store
			$GLOBALS['store'][$name_market]['serviceUrl'] = $url; //optional override for Service URL
			$GLOBALS['store'][$name_market]['MWSAuthToken'] = ''; //token needed for web apps and third-party developers*/
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




	function getAsins($market=null,$download=false){
		$database = _obj('Database');
		
		$markets = $this->marketplace;
		
		if( $market ){
			$markets = array($market);
			$asin = $database->select('*','amazon_asin',"marketplace='{$market}'");
		}else{
			$asin = $database->select('*','amazon_asin',"1=1");
			

		}

		foreach($markets as $s => $m){
			$associazione[$m]['tot'] = 0;
			$associazione[$m]['asin'] = array();
			$associazione[$m]['parent_asin'] = array();
			//$associazione[$m]['upc'] = array();
		}

		
		foreach($asin as $m){
			if( !in_array($m['marketplace'],$markets) ) continue; 
			$associazione[$m['marketplace']]['tot'] += 1;
			$associazione[$m['marketplace']]['asin'][$m['value']] = $m['asin'];
			$associazione[$m['marketplace']]['type'][$m['value']] = $m['ProductTypeName'];
			if( $m['asin_parent'] ){
				$associazione[$m['marketplace']]['parent_asin'][$m['value']] = $m['asin_parent'];
			}
		}
		
		
		
		//$download = 1;
		if( $download ){
			$iter = 0;
			
			foreach($markets as $s => $m){
				
				$eans = $database->select('distinct(ean)','product',"ean is not null AND ean not in (select value from amazon_asin where type='ean' AND marketplace='{$m}')");
				
				//$res = $this->ean_check("mdma rete bianco nero'31");
				
				$list_ean = array();
				$list_upc = array();
				foreach($eans as $val){	
					$bc_validator = new BarcodeValidator($val['ean']);
					if( $bc_validator->isValid() ){
						switch($bc_validator->getType()){
							case 'EAN':
								$list_ean[] = $val['ean'];
								break;
							case 'UPC':
								$list_upc[] = $val['ean'];
								break;
						}
					}

				}
				
				$_list = array();
				$this->initMarket($m);
				
				
				
				//$list_ean = array();
				foreach($list_ean as $v){

					
					$_list[] = $v;
					
					if( count($_list) == 5 ){
						
						//error_log(print_r($_list,true));
						$obj = new AmazonProductGetMatchingProductForId($m);
						
						$obj->setIdType('EAN');
						$obj->setIdList($_list);
						
						
						//debugga($_list);
						if( $obj->getProductList() ){


							
							$lista = $obj->AsinList;
							
							
							foreach($lista as $ean => $dati){
								$dati['type'] = preg_replace('[\_]',' ',$dati['type']);
								$dati['type'] = mb_convert_case(strtolower($dati['type']), MB_CASE_TITLE, "UTF-8");
								$dati['type'] = preg_replace('[\s]','',$dati['type']);
								
								$toinsert = array(
									'asin' => $dati['asin'],
									'value' => $ean,
									'type' => 'ean',
									'marketplace' => $m,
									'asin_parent' => $dati['asin_parent'],
									'ProductTypeName' => $dati['type']
								);
								$associazione[$m]['ean'][$ean] = $dati['asin'];
								$associazione[$m]['type'][$ean] = $dati['type'];
								if( $dati['asin_parent'] ){
									$associazione[$m]['parent_ean'][$ean] =$dati['asin_parent'];
								}
								$database->insert('amazon_asin',$toinsert);
							}
							$associazione[$m]['tot'] += $obj->AsinCount;

							
							if( okArray($obj->errors) ){
								foreach($obj->errors as $k4=>$v4){
									$associazione[$m]['errors'][$k4] = $v4;
								}
							}
							
						}
						
						$_list = array();

						if( $iter > 10 ){
							sleep(1);
						}
						$iter++;
						
						//error_log("Iterazione ".$iter);
					}


				}
				
				if( count($_list) <= 5 ){
					
					$obj = new AmazonProductGetMatchingProductForId($m);
						
					$obj->setIdType('EAN');
					$obj->setIdList($_list);

					
					
					if( $obj->getProductList() ){
						$lista = $obj->AsinList;
						
						foreach($lista as $ean => $dati){

							$dati['type'] = preg_replace('[\_]',' ',$dati['type']);
							$dati['type'] = mb_convert_case(strtolower($dati['type']), MB_CASE_TITLE, "UTF-8");
							$dati['type'] = preg_replace('[\s]','',$dati['type']);
							$toinsert = array(
								'asin' => $dati['asin'],
								'value' => $ean,
								'type' => 'ean',
								'marketplace' => $m,
								'asin_parent' => $dati['asin_parent'],
								'ProductTypeName' => $dati['type']
							);
							$associazione[$m]['ean'][$ean] = $dati['asin'];
							$associazione[$m]['type'][$ean] = $dati['type'];
							if( $dati['asin_parent'] ){
								$associazione[$m]['parent_ean'][$ean] =$dati['asin_parent'];
							}
							$database->insert('amazon_asin',$toinsert);
						}
						$associazione[$m]['tot'] += $obj->AsinCount;
						
						if( okArray($obj->errors) ){
							foreach($obj->errors as $k4=>$v4){
								$associazione[$m]['errors'][$k4] = $v4;
							}
						}
						
					}
					if( $iter > 10 ){
						sleep(1);
					}
					$iter++;
				}
				$_list = array();
				foreach($list_upc as $v){

					
					$_list[] = $v;
					
					if( count($_list) == 5 ){
						//debugga($_list);exit;
						//error_log(print_r($_list,true));
						$obj = new AmazonProductGetMatchingProductForId($m);
						
						$obj->setIdType('UPC');
						$obj->setIdList($_list);
						
						
						
						if( $obj->getProductList() ){


							
							$lista = $obj->AsinList;
							
							
							
							foreach($lista as $ean => $dati){
								$dati['type'] = preg_replace('[\_]',' ',$dati['type']);
								$dati['type'] = mb_convert_case(strtolower($dati['type']), MB_CASE_TITLE, "UTF-8");
								$dati['type'] = preg_replace('[\s]','',$dati['type']);
								
								$toinsert = array(
									'asin' => $dati['asin'],
									'value' => $ean,
									'type' => 'upc',
									'marketplace' => $m,
									'asin_parent' => $dati['asin_parent'],
									'ProductTypeName' => $dati['type']
								);
								$associazione[$m]['asin'][$ean] = $dati['asin'];
								$associazione[$m]['type'][$ean] = $dati['type'];
								if( $dati['asin_parent'] ){
									$associazione[$m]['parent_asin'][$ean] =$dati['asin_parent'];
								}
								$database->insert('amazon_asin',$toinsert);
							}
							$associazione[$m]['tot'] += $obj->AsinCount;

							
							if( okArray($obj->errors) ){
								foreach($obj->errors as $k4=>$v4){
									$associazione[$m]['errors'][$k4] = $v4;
								}
							}
							
						}
						
						$_list = array();

						if( $iter > 10 ){
							sleep(1);
						}
						$iter++;
						//error_log("Iterazione ".$iter);
					}


				}

				if( count($_list) <= 5 ){
					
					$obj = new AmazonProductGetMatchingProductForId($m);
						
					$obj->setIdType('UPC');
					$obj->setIdList($_list);

					
					
					if( $obj->getProductList() ){
						$lista = $obj->AsinList;
						
						foreach($lista as $ean => $dati){

							$dati['type'] = preg_replace('[\_]',' ',$dati['type']);
							$dati['type'] = mb_convert_case(strtolower($dati['type']), MB_CASE_TITLE, "UTF-8");
							$dati['type'] = preg_replace('[\s]','',$dati['type']);
							$toinsert = array(
								'asin' => $dati['asin'],
								'value' => $ean,
								'type' => 'upc',
								'marketplace' => $m,
								'asin_parent' => $dati['asin_parent'],
								'ProductTypeName' => $dati['type']
							);
							$associazione[$m]['asin'][$ean] = $dati['asin'];
							$associazione[$m]['type'][$ean] = $dati['type'];
							if( $dati['asin_parent'] ){
								$associazione[$m]['parent_asin'][$ean] =$dati['asin_parent'];
							}
							$database->insert('amazon_asin',$toinsert);
						}
						$associazione[$m]['tot'] += $obj->AsinCount;
						
						if( okArray($obj->errors) ){
							foreach($obj->errors as $k4=>$v4){
								$associazione[$m]['errors'][$k4] = $v4;
							}
						}
						
					}
					if( $iter > 10 ){
						sleep(1);
					}
					$iter++;
				}
			}
		}
		
		

		//debugga($associazione);exit;
		if( $market ){
			return $associazione[$market];
		}else{
			return $associazione;
		}
		
		
	}
	

	function getReportProductAmazon($market='Italy'){
		$path = "reports/".$this->id."_".$market.".csv";

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
			
			$explode = explode('_',$v['seller_sku']);
			
			$list[$explode[0]]['sku'] = $explode[1];
			$list[$explode[0]]['id'] = $explode[0];
			$list[$explode[0]]['asin'] = $v['asin1'];
		}


		
		
		return $list;
		
	}


	function computePrices($where_categories){
		
		$database = _obj('Database');



		$qnt = 1;
		
		
		
		$products = $database->select('id,parent,taxCode,parentPrice','product',"deleted=0 AND visibility=1 AND {$where_categories}");
		
		
		//prendo le tasse
		$tasse = $database->select('*','tax');
		if( okArray($tasse) ){
			foreach($tasse as $v){
				$percentuale_tassa[$v['id']] = $v['percentage'];
			}
		}
		
		foreach($products as $k => $v){
			$id_return = $v['id'];

			if( $v['parentPrice']){
				$id = $v['parent'];
			}else{
				$id = $v['id']; //id del prodotto
			}
			$taxCode = $v['taxCode']; // id della tassa
			
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
			

			$prezzi_id[$id_return] = $prezzo_valore;
				
		
				
		}
		
		
		return $prezzi_id;


		
	}

	function getProducts($market='Italy'){
		
		$loc = AmazonTool::getMarketplaceLang($market);
		
		$amazon_products = $this->getReportProductAmazon($market);
		
		unset($_SESSION[$this->lat_upload_id][$market]);
		if( $_SESSION[$this->lat_upload_id][$market] ){
			$new_products =  $_SESSION[$this->lat_upload_id][$market];
			
		}else{

			$categorie = $this->getCategories();
			$where_categories = "section IN (";
			foreach($categorie as $v){
				if( (int)$v ){
					$where_categories .= "{$v},";
				}
				
			}
			
			$where_categories = preg_replace('/,$/',')',$where_categories);

			$prezzi = $this->computePrices($where_categories);
			
			$database = _obj('Database');
			
			
			$check_exist_upc = $database->execute("SHOW COLUMNS FROM product LIKE 'upc'");
			if( okArray($check_exist_upc)){
				$products = $database->select('p.id,p.type,p.parent,p.stock,p.visibility as active,p.ean,p.upc,p.sku,l.name,l.description,i.disable_sync as disable,i.price as price_override,i.bullet_1,i.bullet_2,i.bullet_3','(product as p join productLocale as l on l.product=p.id)  left outer join amazon_product as i on i.id_product=p.id',"{$where_categories} AND (upc IS NOT NULL OR ean IS NOT NULL) AND locale='{$loc}' AND ((i.id_account IS NULL OR i.id_account={$this->id}) AND  (i.marketplace IS NULL OR i.marketplace='{$market}')) AND p.deleted=0");
				
			}else{	
				$products = $database->select('p.id,p.parent,p.type,p.stock,p.visibility as active,p.ean,p.sku,l.name,l.description,i.disable_sync as disable,i.price as price_override,i.bullet_1,i.bullet_2,i.bullet_3','(product as p join productLocale as l on l.product=p.id)  left outer join amazon_product as i on i.id_product=p.id',"{$where_categories} AND ean IS NOT NULL AND locale='{$loc}' AND ((i.id_account IS NULL OR i.id_account={$this->id}) AND  (i.marketplace IS NULL OR i.marketplace='{$market}')) AND p.deleted=0");

			}
			
			
			
			
			$parent_id = array();
			
			
			
			foreach($products as $k => $p){
				
				$p['price'] = $prezzi[$p['id']];
				if( $p['type'] == 2 && !$p['parent']){
				}else{
					if( !$p['active'] ) $p['disable'] = 1;
					
					if( $p['disable'] && !array_key_exists($p['id'],$amazon_products)){
						
						continue;
					}
				}
				unset($amazon_products[$p['id']]);
				
				
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
			$dati_parent = $database->select('p.id,p.visibility as active,i.disable_sync as disable,i.parent_description,i.price as price_override,i.bullet_1,i.bullet_2,i.bullet_3,p.deleted','product as p left outer join amazon_product as i on i.id_product=p.id',"p.id IN ({$where_parent}) AND ((i.id_account IS NULL OR i.id_account={$this->id}) AND  (i.marketplace IS NULL OR i.marketplace='{$market}'))");
			
		
			foreach($dati_parent as $par){
				foreach($par as $k => $v){
					$new_products[$par['id']][$k] = $v;
				}
				if( $par['deleted'] || !$par['active'] ){
					$new_products[$par['id']]['disable'] = 1;
				}
				
			}
			
			
			
			 $_SESSION[$this->lat_upload_id][$market] = $new_products;
		}
		
		if( okArray($amazon_products) ){
			foreach($amazon_products as $k => $v){
				$new_products[$k] = array(
						'id' => $k,
						'sku' => $v['sku'],
						'asin' => $v['asin'],
						'disable' => 1,
						'active' => 0,
						'name' => $v['sku'],
						'description' => $v['description'],
				);
			}
			
		}
	
		
		
		$this->products = $new_products;
		
	}
	

	function getProducts2($market='Italy'){
		
		$loc = AmazonTool::getMarketplaceLang($market);
		$asins_info = $this->getAsins($market);
		//$amazon_products = $this->getReportProductAmazon($market);
		//debugga($amazon_products);
		$asins = $asins_info['asin'];
		
		$parent_asins = $asins_info['parent_asin'];
		$type_asins = $asins_info['type'];
		
		
		unset($_SESSION[$this->lat_upload_id][$market]);
		if( $_SESSION[$this->lat_upload_id][$market] ){
			$new_products =  $_SESSION[$this->lat_upload_id][$market];
			
		}else{

			$categorie = $this->getCategories();
			$where_categories = "section IN (";
			foreach($categorie as $v){
				if( (int)$v ){
					$where_categories .= "{$v},";
				}
				
			}
			$where_categories = preg_replace('/,$/',')',$where_categories);
			
			
			$database = _obj('Database');
			//$products = $database->select('p.id,p.visibility as active,p.ean,l.name,l.description,p.parent,p.stock,p.parentPrice,pr.value as price,p.taxCode,i.disable_sync as disable,i.price as price_override,i.bullet_1,i.bullet_2,i.bullet_3','((product as p left outer join productLocale as l on l.product=p.id) left outer join price as pr on pr.product=p.id) left outer join amazon_product as i on i.id_product=p.id',"ean is not null AND locale='{$loc}' AND pr.label='default' AND ((i.id_account IS NULL OR i.id_account={$this->id}) AND (i.marketplace IS NULL OR i.marketplace='{$market}')) order by id");
			$products = $database->select('p.id,p.visibility as active,p.ean,p.parent,p.stock,p.type,p.parentPrice,p.taxCode,l.name,l.description,p.parentPrice,pr.value as price,p.taxCode,i.disable_sync as disable,i.price as price_override,i.bullet_1,i.bullet_2,i.bullet_3,i.parent_description','((product as p left outer join productLocale as l on l.product=p.id) left outer join price as pr on pr.product=p.id) left outer join amazon_product as i on i.id_product=p.id',"ean is not null  AND locale='{$loc}' AND (pr.label IS NULL OR pr.label='default') AND ((i.id_account IS NULL OR i.id_account={$this->id}) AND  (i.marketplace IS NULL OR i.marketplace='{$market}')) AND {$where_categories} order by id");
			
			
			
			$parent_id = array();
			
			$taxCode = Tax::prepareQuery()->get();
			foreach($taxCode as $t){
				$val_tax[$t->id] = $t->percentage;
			}
			
			foreach($products as $k => $p){
				if( $p['type'] == 2 && !$p['parent']){
				}else{
					if( !$p['active'] ) $p['disable'] = 1;
					if( $p['disable'] && !in_array($p['id'],$amazon_products)){
						continue;
					}
				}
				$p['tax'] = $val_tax[$p['taxCode']];
				if( $asins[$p['ean']] ){
					$p['asin'] = $asins[$p['ean']];
				}

				if( $asins[$p['upc']] ){
					$p['asin'] = $asins[$p['upc']];
				}
				
				
				if( $p['parent'] ){
					if( $new_products[$p['parent']]['disable'] ){
						$p['disable'] = 1;
					}
					$parent_id[$p['parent']] = $p['parent'];
					
					if( $parent_asins[$p['ean']] ){
						$new_products[$p['parent']]['asin'] = $parent_asins[$p['ean']];
						$new_products[$p['parent']]['product_type'] = $type_asins[$p['ean']];
					}
					if( $parent_asins[$p['upc']] ){
						$new_products[$p['parent']]['asin'] = $parent_asins[$p['upc']];
						$new_products[$p['parent']]['product_type'] = $type_asins[$p['upc']];
					}
					
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
			$dati_parent = $database->select('p.id,pr.value as price,p.taxCode,p.visibility as active,i.disable_sync as disable,i.parent_description','(product as p left outer join price as pr on pr.product=p.id) left outer join amazon_product as i on i.id_product=p.id',"p.id IN ({$where_parent}) AND (pr.label IS NULL OR pr.label='default') AND ((i.id_account IS NULL OR i.id_account={$this->id}) AND  (i.marketplace IS NULL OR i.marketplace='{$market}'))");
			foreach($dati_parent as $v){
				$new_products[$v['id']]['price'] = $v['price'];
				$new_products[$v['id']]['taxCode'] = $v['taxCode'];
				$new_products[$v['id']]['active'] = $v['active'];
				$new_products[$v['id']]['disable'] = $v['disable'];
				$new_products[$v['id']]['id'] = $v['id'];
				$new_products[$v['id']]['parent_description'] = $v['parent_description'];
			}
			
			// DA COMMENTARE
			//$new_products = array_values($new_products);
			//$new_products = array(array_values($new_products[0]['children'])[0]);
			//debugga($new_products);exit;

			
			/*

			
			
			foreach($products as $k => $v){
				if( !$v['parent'] ){
					$tax_code_parent[$v['id']] = $v['taxCode'];
				}
				if( $v['parent'] && $v['parentPrice'] ){
					$children[$v['parent']][] = $v;
				}else{
					if( $v['taxCode'] ){
						$v['price_final'] = $v['value']*$val_tax[$v['taxCode']]/100;
					}else{
						$v['price_final'] = $v['value'];
					}
					$list[] = $v;
				}
			}
			debugga($children);exit;
			if( okArray($children) ){
				$id_parents = array_keys($children);
				foreach($id_parents as $v){
					$where .="{$v},";
				}
				$where = preg_replace('/\,$/','',$where);
				

				$products_parent = $database->select('p.id,p.ean,l.name,l.description,p.parent,p.stock,p.parentPrice,pr.value,p.taxCode','(product as p left outer join productLocale as l on l.product=p.id) left outer join price as pr on pr.product=p.id',"p.id in ({$where}) AND locale='{$loc}' AND pr.label='default'");
				
				//$prezzi =  $database->select('*','price',"product in ({$where}) AND label='default'");

				
				foreach($products_parent as $v){

					$list[] = $v;
					$prezzo[$v['id']] = $v['value'];
				}
				
				foreach($children as $id_parent => $values){
					if( $tax_code_parent[$id_parent] ){
						$price_final = $prezzo[$id_parent]*$val_tax[$tax_code_parent[$id_parent]]/100;
					}else{
						$price_final = $prezzo[$id_parent];
					}
					foreach($values as $c){
						$c['price_final'] = $price_final;
						$list[] = $c;
					}
					
				}
				
			}*/
			 $_SESSION[$this->lat_upload_id][$market] = $new_products;
		}

		
		//debugga(count($new_products));exit;
		$this->products = $new_products;
		
	}
	

	function saveXML($filename,$xml_data,$id_upload){
		if( !file_exists('xml_upload/'.$id_upload) ){
			mkdir('xml_upload/'.$id_upload);
		}
		file_put_contents('xml_upload/'.$id_upload."/".$filename.".xml",$xml_data);
		
	}

	function getXMLFeedNewProducts($xml_data){
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Product</MessageType>
			<PurgeAndReplace>false</PurgeAndReplace>';
		$xml .= $xml_data;
		$xml .= '</AmazonEnvelope>';
		
		return $xml;
	
	}

	function getXmlFeedRelation($product=NULL){
		$feed = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Relationship</MessageType>
			<PurgeAndReplace>false</PurgeAndReplace>';
		
		if( is_object($product) ){
			$tmp = $product;
			unset($product);
			$product = array($tmp);
		}
		foreach($product as $prod){
			if( $prod->hasChildren()){

				foreach($prod->getChildren() as $child){
					if( !is_object($parent_prod)){
						$parent_prod = $child->getParent();
					}
					$feed .= '<Message>
							<MessageID>'.$child->id.'</MessageID>
							<OperationType>Update</OperationType>
							<Relationship>
								<ParentSKU>'.$parent_prod->id.'_'.$parent_prod->sku.'</ParentSKU>
								<Relation>
									<SKU>'.$child->id.'_'.$child->sku.'</SKU>
									<Type>Variation</Type>
								</Relation>
							</Relationship>
					</Message>';
				}
			}
		}
		$feed.='</AmazonEnvelope>';
		return $feed;

	}

	function getXMLFeedPrice2($product,$currency){
		if( is_object($product) ){
			$tmp = $product;
			unset($product);
			$product = array($tmp);
		}
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Price</MessageType>';
			foreach($product as $prod){
				if( $prod->children ){
					foreach($prod->children as $child){
						$prezzo_figlio = $child->amazon_price?$child->amazon_price:$child->getPriceValue();
						$xml .= '<Message> 
							<MessageID>'.$child->id.'</MessageID>
							<OperationType>Update</OperationType> 
							<Price>
								<SKU>'.$child->id.'_'.$child->sku.'</SKU> 
								<StandardPrice currency="'.$currency.'">'.$prezzo_figlio.'</StandardPrice>
							</Price>
							</Message>';
					}
					

				}else{
					if( $prod->hasChildren()){
						foreach($prod->getChildren() as $child){
							$xml .= '<Message> 
								<MessageID>'.$child->id.'</MessageID>
								<OperationType>Update</OperationType> 
								<Price>
									<SKU>'.$child->id.'_'.$child->sku.'</SKU> 
									<StandardPrice currency="'.$currency.'">'.$child->getPriceValue().'</StandardPrice>
								</Price>
								</Message>';
							}
					}else{

						$xml .= '<Message> 
								<MessageID>'.$prod->id.'</MessageID>
								<OperationType>Update</OperationType> 
								<Price>
									<SKU>'.$prod->id.'_'.$prod->sku.'</SKU> 
									<StandardPrice currency="'.$currency.'">'.$prod->getPriceValue().'</StandardPrice>
								</Price>
								</Message>';
							

					}
				}
				
			}
			$xml .= '</AmazonEnvelope>';
			
		return $xml;
	}

	function getXmlFeedImage($product=NULL){
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
			$_protocollo = $_SERVER['HTTP_X_FORWARDED_PROTO'];
		}else{
			$_protocollo = !empty($_SERVER['HTTPS']) ? "https" : "http";
		}
		$baseurl = $_protocollo."://".Marion::getConfig('generale','baseurl');
		$feed = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>ProductImage</MessageType>
			<PurgeAndReplace>false</PurgeAndReplace>';
		
		if( is_object($product) ){
			$tmp = $product;
			unset($product);
			$product = array($tmp);
		}
		foreach($product as $prod){
			
			
			if( $prod->hasChildren()){
				foreach($prod->getChildren() as $child){
					$feed .= '<Message>
							<MessageID>'.$child->id.'</MessageID>
							<OperationType>Update</OperationType>
							<ProductImage>
								<SKU>'.$child->id.'_'.$child->sku.'</SKU>
								<ImageType>Main</ImageType>
								<ImageLocation>'.$baseurl.$child->getUrlImage(0).'</ImageLocation> 
							</ProductImage>
					</Message>';
				}
			}else{
				$feed .= '<Message>
						<MessageID>'.$prod->id.'</MessageID>
							<OperationType>Update</OperationType>
							<ProductImage>
								<SKU>'.$prod->id.'_'.$prod->sku.'</SKU>
								<ImageType>Main</ImageType>
								<ImageLocation>'.$baseurl.$prod->getUrlImage(0).'</ImageLocation> 
							</ProductImage>
					</Message>';

			}
		}
		$feed.='</AmazonEnvelope>';
		return $feed;

	}


	function getXMLFeedProducts(){
		$date_now = AmazonTool::getDateNow();
		//debugga($this->products);exit;
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Product</MessageType>
			<PurgeAndReplace>false</PurgeAndReplace>';
			foreach($this->products as $row){
				$disable = $row['disable']==1?'Delete':'PartialUpdate';
				$parent_flag_disable = $row['disable'];
				$tmp = array();
				if(trim($row['bullet_1'])){
					$tmp[]= $row['bullet_1'];
				}
				if(trim($row['bullet_2'])){
					$tmp[]= $row['bullet_2'];
				}
				if(trim($row['bullet_3'])){
					$tmp[]= $row['bullet_3'];
				}
				if( okArray($tmp) ){
					
					$row['bullets'] = $tmp;
				}
				if( $row['upc'] || $row['ean'] || $row['asin']){
					if( $row['upc'] ){
						$_type = 'UPC';
						$_valore = $row['upc'];
					}elseif($row['ean']){
						$_type = 'EAN';
						$_valore = $row['ean'];
					}else{
						$_type = 'ASIN';
						$_valore = $row['asin'];
					}
					if( !okArray($row['children']) ){
						$xml .= '<Message> 
									<MessageID>'.$row['id'].'</MessageID>
									<OperationType>'.$disable.'</OperationType> 
									<Product> 
										<SKU>'.$row['id'].'_'.$row['sku'].'</SKU> 
										<StandardProductID> 
											<Type>'.$_type.'</Type> 
											<Value>'.$_valore.'</Value> 
										</StandardProductID> 
										<ProductTaxCode>A_GEN_TAX</ProductTaxCode> 
										<LaunchDate>'.$date_now.'</LaunchDate> 
										<Condition> 
											<ConditionType>New</ConditionType> 
										</Condition> 
										<DescriptionData> 
											<Title>'.$row['name'].'</Title>
											<Description>
												'.$row['name'].'
											</Description>
											';
											if( okArray($row['bullets'])){
												foreach($row['bullets'] as $bullet){
													$xml .='<BulletPoint>'.$bullet.'</BulletPoint>';
												}
											}
										$xml .='</DescriptionData>';
										/*if( okArray($row['children'])){
											$xml .= '<ProductData><'.$row['product_type'].'><VariationData><Parentage>parent</Parentage><VariationTheme>Color</VariationTheme></VariationData></'.$row['product_type'].'></ProductData>';
										}*/

									$xml .= '</Product>
								</Message>';
						}
					}
					if( okArray($row['children']) ){
						foreach($row['children'] as $row2){
							if( $row['parent_description'] ){
								$row2['bullets'] = $row['bullets']; 
							}else{
								$tmp = array();
								if(trim($row2['bullet_1'])){
									$tmp[]= $row2['bullet_1'];
								}
								if(trim($row2['bullet_2'])){
									$tmp[]= $row2['bullet_2'];
								}
								if(trim($row2['bullet_3'])){
									$tmp[]= $row2['bullet_3'];
								}
								if( okArray($tmp) ){
									
									$row2['bullets'] = $tmp;
								}
							}
							if( $parent_flag_disable ){
								$disable = 'Delete';
							}else{
								$disable = $row2['disable']==1?'Delete':'PartialUpdate';
							}
							if( $row2['upc'] || $row2['ean'] ){
								if( $row2['upc'] ){
									$_type = 'UPC';
									$_valore = $row2['upc'];
								}else{
									$_type = 'EAN';
									$_valore = $row2['ean'];
								}
								$xml .= '<Message> 
									<MessageID>'.$row2['id'].'</MessageID>
									<OperationType>'.$disable.'</OperationType> 
									<Product> 
										<SKU>'.$row2['id'].'_'.$row2['sku'].'</SKU> 
										<StandardProductID> 
											<Type>'.$_type.'</Type> 
											<Value>'.$_valore.'</Value> 
										</StandardProductID> 
										<ProductTaxCode>A_GEN_TAX</ProductTaxCode> 
										<LaunchDate>'.$date_now.'</LaunchDate> 
										<Condition> 
											<ConditionType>New</ConditionType> 
										</Condition> 
										<DescriptionData> 
											<Title>'.$row2['name'].'</Title>
											<Description>
												'.$row2['name'].'
											</Description>
											';
										if( okArray($row2['bullets'])){
											foreach($row2['bullets'] as $bullet){
												$xml .='<BulletPoint>'.$bullet.'</BulletPoint>';
											}
										}
										$xml .='</DescriptionData>
										</Product>
									</Message>';
								} 
						}
					}
			}
			$xml .= '</AmazonEnvelope>';

			
		return $xml;
	}
	

	function getXMLFeedInventory2($product){
		
		if( is_object($product) ){
			$tmp = $product;
			unset($product);
			$product = array($tmp);
		}
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Inventory</MessageType>';
			foreach($product as $prod){
				if( !$prod->hasChildren()){
					$xml .= '<Message> 
							<MessageID>'.$prod->id.'</MessageID>
							<OperationType>Update</OperationType> 
							<Inventory> 
								<SKU>'.$prod->id.'_'.$prod->sku.'</SKU> 
								<Quantity>'.$prod->stock.'</Quantity>
							</Inventory>
							</Message>';
				}else{
					foreach($prod->getChildren() as $child){
						
						$xml .= '<Message> 
							<MessageID>'.$child->id.'</MessageID>
							<OperationType>Update</OperationType> 
							<Inventory> 
								<SKU>'.$child->id.'_'.$child->sku.'</SKU> 
								<Quantity>'.$child->stock.'</Quantity>
							</Inventory>
							</Message>';
					}
				}
			}
			$xml .= '</AmazonEnvelope>';
			
		return $xml;
	}


	function getXMLFeedInventory(){
		
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Inventory</MessageType>';
			foreach($this->products as $row){
				if( ($row['ean'] || $row['upc']) && !$row['disable']){
					$xml .= '<Message> 
						<MessageID>'.$row['id'].'</MessageID>
						<OperationType>Update</OperationType> 
						<Inventory> 
							<SKU>'.$row['id'].'_'.$row['sku'].'</SKU> 
							<Quantity>'.$row['stock'].'</Quantity>
						</Inventory>
						</Message>';
				}
				if( okArray($row['children']) ){
					foreach($row['children'] as $row2){
						if( ($row2['ean'] || $row2['upc']) && !$row2['disable'] ){
							$xml .= '<Message> 
								<MessageID>'.$row2['id'].'</MessageID>
								<OperationType>Update</OperationType> 
								<Inventory> 
									<SKU>'.$row2['id'].'_'.$row2['sku'].'</SKU> 
									<Quantity>'.$row2['stock'].'</Quantity>
								</Inventory>
								</Message>';
						}
					}
				}
			}
			$xml .= '</AmazonEnvelope>';
			
		return $xml;
	}

	function getXMLFeedPrice($currency){
		
		
		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Price</MessageType>';
			foreach($this->products as $row){
				if( ($row['ean'] || $row['upc']) && !$row['disable']){
					$price = $row['price'];
					if( $row['price_override'] ){
						$price = $row['price_override'];
					}
					$price = round($price,2);
					$price = $price + $price*0.15;
					$price = round($price,2);
					$xml .= '<Message> 
						<MessageID>'.$row['id'].'</MessageID>
						<OperationType>Update</OperationType> 
						<Price>
							<SKU>'.$row['id'].'_'.$row['sku'].'</SKU> 
							<StandardPrice currency="'.$currency.'">'.$price.'</StandardPrice>
						</Price>
						</Message>';
				}
				if( okArray($row['children']) ){
					foreach($row['children'] as $row2){
						$price_child = 0;
						if( $row['parent_description'] ){

							$price_parent = $row['price'];
							if( $row['price_override'] ){
								$price_parent = $row['price_override'];
							}
							$price_child = $price_parent;
						}else{
							$price_child = $row2['price'];
							if( $row2['price_override'] ){
								$price_child = $row2['price_override'];
							}
							$price_child = round($price_child,2);
						}
						$price_child = $price_child + $price_child*0.15;
						$price_child = round($price_child,2);
						if(  ($row2['ean'] || $row2['upc']) && !$row2['disable']){

							$xml .= '<Message> 
								<MessageID>'.$row2['id'].'</MessageID>
								<OperationType>Update</OperationType> 
								<Price>
									<SKU>'.$row2['id'].'_'.$row2['sku'].'</SKU> 
									<StandardPrice currency="'.$currency.'">'.$price_child.'</StandardPrice>
								</Price>
								</Message>';
						}
					}
				}
			}
			$xml .= '</AmazonEnvelope>';
			
		return $xml;
	}


	/*function getXMLRelationFeed(){

		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
			<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd"> 
			<Header> 
				<DocumentVersion>1.01</DocumentVersion>
				<MerchantIdentifier>'.$this->merchantId.'</MerchantIdentifier> 
			</Header> 
			<MessageType>Relationship</MessageType>';
			foreach($this->products as $row){
				
				if( okArray($row['children']) ){
					foreach($row['children'] as $row2){
						if( $row2['asin'] ){

							$xml .= '<Message> 
								<MessageID>'.$row2['id'].'</MessageID>
								<OperationType>Update</OperationType> 
								<Relationship>
									<ParentSKU>MARION_'.$row['id'].'</ParentSKU>
									<Relation>
										<SKU>MARION_'.$row2['id'].'</SKU>
										<Type>Variation</Type>
									</Relation>
								</Relationship>
								</Message>';
						}
					}
				}
			}
			$xml .= '</AmazonEnvelope>';
			
		return $xml;

	}*/


	function checkUpload(){
		$database = _obj('Database');
		$select = $database->select('*','amazon_feed',"id_store={$this->id} AND FeedType='_POST_PRODUCT_DATA_' AND FeedProcessingStatus <> '_DONE_'");

		return !okArray($select);
	}


	function getStatusFeed($type,$id_upload){
		$database = _obj('Database');
		$select = $database->select('*','amazon_feed',"id_store={$this->id} AND FeedType='{$type}' AND FeedProcessingStatus <> '_DONE_' AND id_upload={$id_upload}");

		
		global $store;
		if( okArray($select) ){
			foreach($select as $v){
				$items[$v['marketplace']][] = $v['FeedSubmissionId'];
			}
			
			$tot = count($select);
			$check_tot = 0;
			$check = false;
			foreach($items as $s => $v){
				
			
				$amz=new AmazonFeedList($s);
				$amz->setTimeLimits('- 24 hours'); //limit time frame for feeds to any updated since the given time
				$amz->setFeedIds($v);
				$amz->setFeedStatuses(array("_SUBMITTED_", "_IN_PROGRESS_", "_DONE_")); //exclude cancelled feeds
				$amz->fetchFeedSubmissions(); //this is what actually sends the request

				$res = $amz->getFeedList();
				if( okArray($res) ){
					foreach($res as $v1){
						$database->update('amazon_feed',"FeedSubmissionId={$v1['FeedSubmissionId']}",array('FeedProcessingStatus' => $v1['FeedProcessingStatus']));
					}
					if( $v1['FeedProcessingStatus'] == '_DONE_'){
						
						/*$amz2=new AmazonFeedResult($s,$v1['FeedSubmissionId']); //feed ID can be quickly set by passing it to the constructor
						$response = $amz2->fetchFeedResult();
						debugga($response);exit;
						$data_result = $this->parseFeedResponse($response);
						debugga($data_result);exit;*/
					}
				}
			}
			return $check;
		}
		return true;
	}

	function getStatusReport($type,$id_upload){
		$database = _obj('Database');
		$select = $database->select('*','amazon_report',"id_store={$this->id} AND ReportType='{$type}' AND ReportProcessingStatus <> '_DONE_' AND id_upload={$id_upload}");
		//$select = $database->select('*','amazon_report',"id_store={$this->id} AND ReportType='{$type}' AND id_upload={$id_upload}");
		//$select = $database->select('*','amazon_report',"id_store={$this->id} AND ReportType='{$type}' AND id_upload={$id_upload}");
		
		
		global $store;
		
		if( okArray($select) ){
			foreach($select as $v){
				$items[$v['marketplace']][] = $v['ReportRequestId'];
			}
			$tot = count($select);
			$check_tot = 0;
			$check = false;
			
			foreach($items as $s => $v){
			
				
				$amz=new AmazonReportRequestList($s);

				$amz->setRequestIds($v);
				$amz->fetchRequestList();
				$res = $amz->getList();
				
				if( okArray($res) ){
					foreach($res as $v1){
						$database->update('amazon_report',"ReportRequestId={$v1['ReportRequestId']}",array('ReportProcessingStatus' => $v1['ReportProcessingStatus']));
						
						if( $v1['ReportProcessingStatus'] == '_DONE_'){
							$report = new AmazonReport($s);
							$report->setReportId($v1['GeneratedReportId']);    //5341031714017322 
							$report->fetchReport();
							$result = $report->getRawReport();
							
							$path = 'reports/'.$this->id."_".$s.".csv" ;
							$savefile_status = $report->saveReport($path);
						}
					}
				}
			}
			return $check;
		}
		return true;
	}


	function parseFeedResponse($response){
	
		$xml=simplexml_load_string($response);
		return $xml;

		file_put_contents('response/'.$_GET['feedId'].'.xml',$response);
		
		foreach($xml->Message as $message){
			
			$message_data = array(
				'id' => (string)$message->MessageID
			);

			foreach($message->ProcessingReport->Result as $res){
				if( $res->ResultCode == 'Error'){
					$message_data['errors'][] = array(
						'sku' =>  (string)$res->AdditionalInfo->SKU,
						'error' => (string)$res->ResultDescription,
						);
					
				}


			}
			$messages[] = $message_data;
			
		}
		
		return $messages;
	}
	
	function getAsinsMarkets(){
		$database = _obj('Database');
		$id_upload = $database->insert('amazon_upload',array('id_store' => $this->id,'last_operation' => '_SEARCH_ASIN_'));
		global $store;

		foreach($store as $s => $v){
			$this->getAsins($s,true);
		
		}

		
		return true;

	}


	function sendProductFeed($type='multiple',$market=NULL){
		$database = _obj('Database');
		

		if( $this->last_upload_id ){
			$id_upload = $this->last_upload_id;
			$database->update('amazon_upload',"id = {$id_upload}",array('last_operation' => '_POST_PRODUCT_DATA_'));
		}else{
			$id_upload = $database->insert('amazon_upload',array('id_store' => $this->id,'last_operation' => '_POST_PRODUCT_DATA_','type' => $type));
		}
		
		global $store;
		foreach($store as $s => $v){
			$this->getProducts($s);
			$feed_products = $this->getXMLFeedProducts();
			$this->saveXML($s."_POST_PRODUCT_DATA_",$feed_products,$id_upload);
			$amz=new AmazonFeed($s); //if there is only one store in config, it can be omitted
			$amz->setFeedType("_POST_PRODUCT_DATA_"); //feed types listed in documentation
			//$amz->setMarketplaceIds(array($v['marketplaceId']));
			$amz->setMarketplaceIds(array($v['marketplaceId']));
			$amz->setFeedContent($feed_products);
		
			
			$amz->submitFeed();
			$res = $amz->getResponse();
			
			unset($res['SubmittedDate']);
			$res['marketplace'] = $s;
			$res['id_store'] = $this->id;
			$res['id_upload'] = $id_upload;
			$database->insert('amazon_feed',$res);
			
			$result[$s]['FeedSubmissionId'] = $res['FeedSubmissionId'];
		}

		
		return $result;

		
	}

	function sendRelationFeed($type='multiple') {
		
		$database = _obj('Database');
		
		if( $this->last_upload_id ){
			$id_upload = $this->last_upload_id;
			$database->update('amazon_upload',"id = {$id_upload}",array('last_operation' => '_POST_PRODUCT_RELATIONSHIP_DATA_'));
		}else{
			$id_upload = $database->insert('amazon_upload',array('id_store' => $this->id,'last_operation' => '_POST_PRODUCT_RELATIONSHIP_DATA_','type' => $type));
		}

		
		global $store;
		foreach($store as $s => $v){
			$this->getProducts($s);
			$feed_products_relation = $this->getXMLRelationFeed();
			$this->saveXML($s."_POST_PRODUCT_RELATIONSHIP_DATA_",$feed_products_relation,$id_upload);
			$amz=new AmazonFeed($s); //if there is only one store in config, it can be omitted
			$amz->setFeedType("_POST_PRODUCT_RELATIONSHIP_DATA_"); //feed types listed in documentation
			$amz->setMarketplaceIds(array($v['marketplaceId']));
			$amz->setFeedContent($feed_products_relation);
			$amz->submitFeed();
			$res = $amz->getResponse();

			unset($res['SubmittedDate']);
			$res['marketplace'] = $s;
			$res['id_store'] = $this->id;
			$res['id_upload'] = $id_upload;
			$database->insert('amazon_feed',$res);
			
			$result[$s]['FeedSubmissionId'] = $res['FeedSubmissionId'];
		}
		return $result;
	}

	// $no_check variabile che stabilisce se occorre verificare o meno se l'ultimo feed di questo tipo è stato terminato
	function upload_single_feed($feed_type,$no_check=0,$market=NULL){
		$this->initMarkets();
		$database = _obj('Database');
		$last_upload = $database->select('*','amazon_upload',"id_store={$this->id} AND type='single' AND last_operation='{$feed_type}' AND  finished = 0 ORDER BY id DESC limit 1");
		
		
		if( okArray($last_upload) && !$no_check){
			 $last_upload = $last_upload[0];
			
			 $this->last_upload_id = $last_upload['id'];
			 $this->_status = $feed_type;
			 
			
			 if( in_array($feed_type,array('_GET_MERCHANT_LISTINGS_DATA_')) ){
				 $check_status = $this->getStatusReport($feed_type,$last_upload['id']);
				
			 }else{
				$check_status = $this->getStatusFeed($feed_type,$last_upload['id']);
			 }
			 
			

			if( $check_status ){
				$database->update('amazon_upload',"id={$last_upload['id']}",array('finished' => 1));
				$this->upload_single_feed($feed_type,$no_check);
				return true;
			}
			 
		}else{

			switch($feed_type){
				case '_GET_MERCHANT_LISTINGS_DATA_':
					$this->reportProductAmazon('single');
					break;
				case '_POST_PRODUCT_DATA_':
					$this->sendProductFeed('single',$market);
					break;
				case '_POST_PRODUCT_RELATIONSHIP_DATA_':
					$this->sendRelationFeed('single');
					break;
				case '_POST_PRODUCT_PRICING_DATA_':
					$this->sendPriceFeed('single');
					break;
				case '_POST_INVENTORY_AVAILABILITY_DATA_':
					$this->sendInventoryFeed('single');
					break;
			}

		}

		return false;

	}

	function sendInventoryFeed($type='multiple') {
			
			$database = _obj('Database');

			if( $this->last_upload_id ){
				$id_upload = $this->last_upload_id;
				 $database->update('amazon_upload',"id = {$id_upload}",array('last_operation' => '_POST_INVENTORY_AVAILABILITY_DATA_'));
			}else{
				$id_upload = $database->insert('amazon_upload',array('id_store' => $this->id,'last_operation' => '_POST_INVENTORY_AVAILABILITY_DATA_','type' => $type));
			}
			
			global $store;
			foreach($store as $s => $v){
				$this->getProducts($s);
				$feed_inventory = $this->getXMLFeedInventory();
				$this->saveXML($s."_POST_INVENTORY_AVAILABILITY_DATA_",$feed_inventory,$id_upload);
				
				$amz=new AmazonFeed($s); //if there is only one store in config, it can be omitted
				$amz->setFeedType("_POST_INVENTORY_AVAILABILITY_DATA_"); //feed types listed in documentation
				$amz->setFeedContent($feed_inventory); //can be either XML or CSV data; a file upload method is available as well
				$amz->setMarketplaceIds(array($v['marketplaceId']));
				$amz->submitFeed(); //this is what actually sends the request
				$amz->getResponse();

				$res = $amz->getResponse();
				
				unset($res['SubmittedDate']);
				$res['marketplace'] = $s;
				$res['id_store'] = $this->id;
				
				$res['id_upload'] = $id_upload;
				$database->insert('amazon_feed',$res);
				
				$result[$s]['FeedSubmissionId'] = $res['FeedSubmissionId'];

			}

			return $result;
	}


	function reportProductAmazon($type='multiple'){
			$database = _obj('Database');

			if( $this->last_upload_id ){
				$id_upload = $this->last_upload_id;
				 $database->update('amazon_upload',"id = {$id_upload}",array('last_operation' => '_GET_MERCHANT_LISTINGS_DATA_'));
			}else{
				$id_upload = $database->insert('amazon_upload',array('id_store' => $this->id,'last_operation' => '_GET_MERCHANT_LISTINGS_DATA_','type' => $type));
			}
			
			global $store;
			foreach($store as $s => $v){


				$reportRequest = new AmazonReportRequest($s);
				$reportRequest->setReportType('_GET_MERCHANT_LISTINGS_DATA_');
				$reportRequest->setMarketplaces(array($v['marketplaceId']));
				//$list = $reportRequest->getList();
				$response = $reportRequest->setTimeLimits();
				$response = $reportRequest->requestReport();
				$res = $reportRequest->getResponse();

				

				
				unset($res['SubmittedDate']);
				unset($res['Scheduled']);
				unset($res['StartDate']);
				unset($res['EndDate']);
				$res['marketplace'] = $s;
				$res['id_store'] = $this->id;
				
				$res['id_upload'] = $id_upload;
				
				$database->insert('amazon_report',$res);
				$result[$s]['ReportRequestId '] = $res['ReportRequestId'];
				

			}
			return $result;
	}

	function sendPriceFeed($type='multiple') {
			
			$database = _obj('Database');

			if( $this->last_upload_id ){
				$id_upload = $this->last_upload_id;
				 $database->update('amazon_upload',"id = {$id_upload}",array('last_operation' => '_POST_PRODUCT_PRICING_DATA_'));
			}else{
				$id_upload = $database->insert('amazon_upload',array('id_store' => $this->id,'last_operation' => '_POST_PRODUCT_PRICING_DATA_','type' => $type));
			}
			
			global $store;
			foreach($store as $s => $v){
				$this->getProducts($s);
				$feed_price = $this->getXMLFeedPrice($v['currency']);
				$this->saveXML($s."_POST_PRODUCT_PRICING_DATA_",$feed_price,$id_upload);
				
				$amz=new AmazonFeed($s); //if there is only one store in config, it can be omitted
				$amz->setFeedType("_POST_PRODUCT_PRICING_DATA_"); //feed types listed in documentation
				$amz->setFeedContent($feed_price); //can be either XML or CSV data; a file upload method is available as well
				$amz->setMarketplaceIds(array($v['marketplaceId']));
				$amz->submitFeed(); //this is what actually sends the request
				$amz->getResponse();

				$res = $amz->getResponse();
				
				unset($res['SubmittedDate']);
				$res['marketplace'] = $s;
				$res['id_store'] = $this->id;
				
				$res['id_upload'] = $id_upload;
				$database->insert('amazon_feed',$res);
				
				$result[$s]['FeedSubmissionId'] = $res['FeedSubmissionId'];

			}

			return $result;
	}

	

	function next_feed_type($type){
		switch($type){
			/*case '_POST_PRODUCT_RELATIONSHIP_DATA_':
				$next = '_POST_PRODUCT_PRICING_DATA_';
				break;*/
			case '_GET_MERCHANT_LISTINGS_DATA_':
				$next = '_POST_PRODUCT_DATA_';
				break;
			case '_SEARCH_ASIN_':
				$next = '_GET_MERCHANT_LISTINGS_DATA_';
				break;
			case '_POST_PRODUCT_DATA_':
				$next = '_POST_PRODUCT_PRICING_DATA_';
				break;
			case '_POST_PRODUCT_PRICING_DATA_':
				$next = '_POST_INVENTORY_AVAILABILITY_DATA_';
				break;
			case '_POST_INVENTORY_AVAILABILITY_DATA_':
				$next = '_END_PROCESS_';
				break;
			default:
				$next = '_SEARCH_ASIN_';
				break;
		}

		return $next;
	}


	function upload($no_search=false){

		
		$database = _obj('Database');
		
		$this->initMarkets();
		
		$last_upload = $database->select('*','amazon_upload',"id_store={$this->id} AND type='multiple' ORDER BY id DESC limit 1");
		
		if( okArray($last_upload) && $last_upload[0]['last_operation'] != '_END_PROCESS_' ){
			 $last_upload = $last_upload[0];
			
			 $this->last_upload_id = $last_upload['id'];
			 $this->_status = $last_upload['last_operation'];
			
			 if( $this->_status == '_GET_MERCHANT_LISTINGS_DATA_'){
					$check_status = $this->getStatusReport($last_upload['last_operation'],$last_upload['id']);
					
			 }else{

				 if( $this->check_last_operation_finished){
					 switch( $this->_status){
						case '_SEARCH_ASIN_':
							$check_status = true;
							break;
						default:
							$check_status = $this->getStatusFeed($last_upload['last_operation'],$last_upload['id']);
							break;

					 }
					 
				 }else{
					$check_status = true;
				 }
			 }
			 
			 if( $check_status ){
				$new_feed_type = $this->next_feed_type($last_upload['last_operation']);
				
				$this->_status = $new_feed_type;
				switch($new_feed_type){
					case '_GET_MERCHANT_LISTINGS_DATA_':
						$this->reportProductAmazon();
						break;
					case '_POST_PRODUCT_DATA_':
						$this->sendProductFeed();
						break;
					case '_POST_PRODUCT_RELATIONSHIP_DATA_':
						$this->sendRelationFeed();
						break;
					case '_POST_PRODUCT_PRICING_DATA_':
						$this->sendPriceFeed();
						break;
					case '_POST_INVENTORY_AVAILABILITY_DATA_':
						$this->sendInventoryFeed();
						break;
					case '_END_PROCESS_':
						$this->end_upload();
						break;
				}
			 }
			
		}else{
			//$this->sendProductFeed();
			//$this->_status = '_POST_PRODUCT_DATA_';
			$this->_status = '_GET_MERCHANT_LISTINGS_DATA_';
			$this->reportProductAmazon();
			/*if( $no_search ){
				$this->_status = '_GET_MERCHANT_LISTINGS_DATA_';
				$this->reportProductAmazon();
			}else{
				$this->_status = '_SEARCH_ASIN_';
				$this->getAsinsMarkets();
			}*/
		}
	}



	function end_upload(){
		$database = _obj('Database');

		if( $this->last_upload_id ){
			$id_upload = $this->last_upload_id;
			$database->update('amazon_upload',"id = {$id_upload}",array('last_operation' => '_END_PROCESS_'));
		}
	}

	function getAmazonFeedListDb($market_name=NULL){
		$database = _obj('Database');
		$date = date('Y-m-d');
		if( $market_name ){
		
			$toreturn = $database->select('*','amazon_feed',"id_store={$this->id} AND marketplace='{$market_name}' AND DATE(timestamp)='{$date}' order by timestamp DESC");

			
			
		}else{
			foreach($this->marketplace as $market_name){
				$toreturn[$market_name]  = $database->select('*','amazon_feed',"id_store={$this->id} AND marketplace='{$market_name}' AND DATE(timestamp)='{$date}'  order by timestamp DESC");
			}
		}
		
		return $toreturn;
	}


	function getAmazonFeedList($market_name=NULL){
		
		if( $market_name ){
			$amz = new AmazonFeedList($market_name);
			
			$amz->setTimeLimits('- 24 hours'); //limit time frame for feeds to any updated since the given time
			$amz->setFeedStatuses(array("_SUBMITTED_", "_IN_PROGRESS_", "_DONE_")); //exclude cancelled feeds
			$amz->fetchFeedSubmissions(); //this is what actually sends the request
			$toreturn = $amz->getFeedList();
		}else{
			foreach($this->marketplace as $market_name){
				$amz = new AmazonFeedList($market_name);
				
				$amz->setTimeLimits('- 24 hours'); //limit time frame for feeds to any updated since the given time
				$amz->setFeedStatuses(array("_SUBMITTED_", "_IN_PROGRESS_", "_DONE_")); //exclude cancelled feeds
				$amz->fetchFeedSubmissions(); //this is what actually sends the request
				$res = $amz->getFeedList();
				
				$toreturn[$market_name] = $res;
			}
		}
		
		return $toreturn;
	}

	function getAmazonFeedStatus($feedID,$market_name){
		
		if( $feedID && $market_name ){
		
			
			$amz=new AmazonFeedResult($market_name, $feedId); //feed ID can be quickly set by passing it to the constructor
			
			$amz->setFeedId($feedID); //otherwise, it must be set this way
			$amz->fetchFeedResult();
			return $amz->getRawFeed();
		}
		
		return false;
	}



	


}



?>