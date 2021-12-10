<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require ('../../../config.inc.php');
$template = _obj('Template');
/* https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_1_9/amzn-base.xsd*/
require('classes/AmazonStore.class.php');
require('classes/AmazonProfile.class.php');
require('classes/AmazonCategory.class.php');
require('classes/AmazonTool.class.php');
require('classes/AmazonOrders.class.php');
require('classes/BarcodeValidator.class.php');
require('classes/AmazonSyncro.class.php');

/*$classes = scandir('category');
foreach($classes as $c){
	if( is_file('category/'.$c)){
		require_once('category/'.$c);
	}
}*/

$action = _var('action');



if( $action == 'setting' ){

	Marion::setMenu('amazon');
	$template->amazon_action = 'setting';
	
	$list = AmazonStore::prepareQuery()->get();
	
	foreach($list as $v){
		$nome_store[$v->id] = $v->name;
		foreach($v->marketplace as $y){
			$image = AmazonTool::getMarketplaceImage($y);
			$v->image .= "<div class='img_market'><img src='{$image}'></div>";
		}
	}
	

	$template->amazon_token = Marion::getConfig('amazon_module','token');


	//controllo se esistono e sono scrivibili le cartelle reports e xml_upload
	if( !file_exists('reports') ){
		$errori[] = 'La cartella <b>reports</b> non esiste. Per il corretto funzionamento del modulo occorre creare la cartella nella root del modulo e assegnarle i permessi di scrittura.';
	}else{
		if ( ! is_writable('reports')) {
			$errori[] = 'La cartella <b>reports</b> non è scrivibile';
		}
	}

	if( !file_exists('xml_upload') ){
		$errori[] = 'La cartella <b>xml_upload</b> non esiste. Per il corretto funzionamento del modulo occorre creare la cartella nella root del modulo e assegnarle i permessi di scrittura.';
	}else{
		if ( ! is_writable('xml_upload')) {
			$errori[] = 'La cartella <b>xml_upload</b> non è scrivibile';
		}
	}


	if( !file_exists('responses') ){
		$errori[] = 'La cartella <b>responses</b> non esiste. Per il corretto funzionamento del modulo occorre creare la cartella nella root del modulo e assegnarle i permessi di scrittura.';
	}else{
		if ( ! is_writable('responses')) {
			$errori[] = 'La cartella <b>responses</b> non è scrivibile';
		}
	}

	$template->errori = $errori;
	

	$template->stores = $list;
	$template->output_module(basename(__DIR__),'conf.htm');
}elseif( $action == 'map' ){
	Marion::setMenu('amazon');

	$template->amazon_action = 'mapping';
	//$variations = $category->getMappingVariations();
	$id = _var('id');
	$profilo = AmazonProfile::withId($id);
	$template->profilo = $profilo;
	$store = $profilo->getStore();

	foreach($store->marketplace as $m){
		$map = $profilo->getMappingMarket($m);
		
		if( okArray($map) ){
			$dati[$m] = $map;
		}

	}
	
	$iter = 0;
	foreach($dati as $k => $v){
		$compos[$k]['iter'] = $iter;
		$iter++;
		$compos[$k]['img'] = AmazonTool::$img_markets[$k];
		$compos[$k]['composition'] =$v;
		
	}
	//debugga($compos);exit;
	$template->mappatura = $compos;
	$template->output_module(basename(__DIR__),'mapping_composition.htm');
}elseif( $action == 'save_mapping' ){
	Marion::setMenu('amazon');
	$formdata = serialize(_formdata());
	$id = _var('id');
	$database = _obj('Database'); 
	$database->update('amazon_profile',"id={$id}",array('mapping' => $formdata));
	
	$risposta = array(
			'result' => 'ok',
	);

	echo json_encode($risposta);
	Marion::closeDB();
	exit;
	

}elseif( $action == 'mapping' ){
	Marion::setMenu('amazon');

	$template->amazon_action = 'mapping';

	$list = AmazonStore::prepareQuery()->get();
	
	foreach($list as $v){
		$nome_store[$v->id] = $v->name;
	}

	$profiles = AmazonProfile::prepareQuery()->get();
	foreach($profiles as $p){
		
		

		$category = $p->getObjCategory();
		
		
		
		//debugga($variations);exit;
		
		$p->nome_store = $nome_store[$p->store];
	}



	//debugga($profiles);exit;
	
	$template->profiles = $profiles;
	$template->output_module(basename(__DIR__),'mapping.htm');
}elseif( $action == 'add_store' || $action == 'mod_store' ){
	Marion::setMenu('amazon');
	

	$corrieri = AmazonTool::getCarriers(); 
	$corrieri_exit = AmazonTool::getCarriersExit(); 
	$markets = AmazonTool::getMarkets(); 
	$template->markets = $markets;
	$template->corrieri_amazon = $corrieri;
	$template->corrieri_amazon_exit = $corrieri_exit;
	$corrieri_marion = AmazonTool::getMarionCarriers(); 
	$template->corrieri_marion = $corrieri_marion;
	


	if( $action == 'mod_store' ){
		
		$id = _var('id');
		$obj = AmazonStore::withId($id);
		$data = $obj->prepareForm();

		$profili = AmazonProfile::prepareQuery()->where('id_store',$id)->get();
		$mapping = $data['mapping_profile'];
		
		foreach($profili as $p){
			$select_profili[$p->id] = $p->name;
		}
		$template->profili = $select_profili;
		$template->mapping_categories = $mapping;

		$template->categorie_selezionate = $obj->getCategories();
		
		$template->map_corrieri = $obj->getCarriers();
		//debugga($template->map_corrieri);exit;
		$template->map_corrieri_exit = $obj->getCarriersExit();
		$template->cont_map_corrieri = count($template->map_corrieri);
		$template->cont_map_corrieri_exit = count($template->map_corrieri_exit);
	}


	$sections = Catalog::getSectionTree(1);
	$template->sections = $sections;
	//debugga($sections);exit;
	
	get_form($elements,'amazon_store',$action."_ok",$data);
	$template->output_module(basename(__DIR__),'form_store.htm',$elements);
}elseif( $action == 'add_store_ok' || $action == 'mod_store_ok' ){
	Marion::setMenu('setting');
	$formdata = _var('formdata');
	
	
	if( $action == 'mod_store_ok' ){
		$profili = AmazonProfile::prepareQuery()->where('id_store',$array['id'])->get();
		
		foreach($profili as $p){
			$select_profili[$p->id] = $p->name;
		}
		$template->profili = $select_profili;
		$template->mapping_categories = $formdata['profile'];

	}
	
	
	$sections = Catalog::getSectionTree(1);
	$template->sections = $sections;
	$template->categorie_selezionate = json_decode($formdata['categories']);
	$template->map_corrieri = $formdata['carrier'];
	$template->map_corrieri_exit = $formdata['carrier_exit'];

	$template->cont_map_corrieri = count($template->map_corrieri);
	$template->cont_map_corrieri_exit = count($template->map_corrieri_exit);


	$corrieri = AmazonTool::getCarriers(); 
	$corrieri_exit = AmazonTool::getCarriersExit(); 
	$markets = AmazonTool::getMarkets(); 
	$template->markets = $markets;
	$template->corrieri_amazon = $corrieri;
	$template->corrieri_amazon_exit = $corrieri_exit;
	$corrieri_marion = AmazonTool::getMarionCarriers(); 
	$template->corrieri_marion = $corrieri_marion;
	//debugga($formdata);
	$array = check_form($formdata,'amazon_store');
	//debugga($array);
	if( $array[0] == 'ok'){
		foreach($formdata['carrier'] as $v){
			$amazon_carr[] = $v['id_amazon'];
		}
		if( count($amazon_carr) != count(array_unique($amazon_carr)) ){
			$array[0] = 'nak';
			$array[1] = 'Mappatura Corrieri in entrata: qualche corriere di amazon è stato inserito più volte.';
		}
		
	}

	

	
	
	if( $array[0] == 'ok'){
		if( $action == 'add_store_ok'){
			$obj = AmazonStore::create();
		}else{
			$obj = AmazonStore::withId($array['id']);
		}

		$array['mapping_profile'] = serialize($formdata['profile']);

		


		$obj->set($array)->save();

		$database = _obj('Database'); 
		$database->delete('amazon_carrier',"id_store={$obj->id}");
		foreach($formdata['carrier'] as $v){
			$v['id_store'] = $obj->id;
			$database->insert('amazon_carrier',$v);
			
		}
		$database->delete('amazon_carrier_exit',"id_store={$obj->id}");
		foreach($formdata['carrier_exit'] as $v){
			$v['id_store'] = $obj->id;
			$database->insert('amazon_carrier_exit',$v);
			
			
		}
		
		$template->link = '/admin/modules/amazon/index.php?action=setting';
		$template->output('continua.htm');

	}else{
		$template->errore = $array[1];
		get_form($elements,'amazon_store',$action,$array);
		$template->output_module(basename(__DIR__),'form_store.htm',$elements);

	}
	
}elseif( $action == 'del_store'){
	Marion::setMenu('amazon');
	$id = _var('id');
	$obj = AmazonStore::withId($id);
	if( is_object($obj) ){
		$obj->delete();
	}
	$template->link = '/admin/modules/amazon/index.php?action=setting';
	$template->output('continua.htm');

}elseif( $action == 'profiles' ){
	Marion::setMenu('amazon');
	$list = AmazonProfile::prepareQuery()->get();
	$template->profiles = $list;
	$template->output_module(basename(__DIR__),'profiles.htm');
}elseif( $action == 'del_profile' ){
	Marion::setMenu('amazon'); 
	$id = _var('id');
	$obj = AmazonProfile::withId($id);
	if( is_object($obj) ){
		$obj->delete();
	}
	$template->link = '/admin/modules/amazon/index.php?action=mapping';
	$template->output('continua.htm');

}elseif( $action == 'new_profile' ){
	$template->amazon_action = 'mapping';
	
	get_form($elements,'amazon_profile','add_profile_ok',$data);
	
	$template->output_module(basename(__DIR__),'form_new_profile.htm',$elements);
}elseif( $action == 'add_profile' || $action == 'mod_profile' ){
	Marion::setMenu('amazon');
	$template->amazon_action = 'mapping';
	if( $action == 'mod_profile' ){
		$id = _var('id');
		$obj = AmazonProfile::withId($id);

		if( is_object($obj) ){
			$store = AmazonStore::withId($obj->store);
			if( is_object($store) ){

				
				foreach($store->marketplace as $mark){
					$markets[$mark] = AmazonTool::getMarketplaceImage($mark);
				}
				$template->marketplaces = $markets;
			}
			$data = $obj->prepareForm();
		}
	}
	get_form($elements,'amazon_profile',$action."_ok",$data);
	
	$template->output_module(basename(__DIR__),'form_profile.htm',$elements);
}elseif( $action == 'add_profile_ok' || $action == 'mod_profile_ok' ){
	Marion::setMenu('amazon');
	$template->amazon_action = 'mapping';
	$formdata = _var('formdata');
	
	if( $action == 'add_profile_ok' ){
		$campi_aggiuntivi['category']['obbligatorio'] = 'f';
	}
	
	$array = check_form($formdata,'amazon_profile',$campi_aggiuntivi);
	
	
	
	if( $array[0] == 'ok'){
		if( $action == 'add_profile_ok'){
			$obj = AmazonProfile::create();
		}else{
			$obj = AmazonProfile::withId($array['id']);
		}
		$obj->set($array)->save();
		if( $action == 'add_profile_ok'){
			$template->link = '/admin/modules/amazon/index.php?action=mod_profile&id='.$obj->id;
		}else{
			$template->link = '/admin/modules/amazon/index.php?action=mapping';
		}
		$template->output('continua.htm');

	}else{
		$template->errore = $array[1];
		get_form($elements,'amazon_profile',$action,$array);
		if( $action == 'add_profile_ok' ){
			$template->output_module(basename(__DIR__),'form_new_profile.htm',$elements);
		}else{
			$template->output_module(basename(__DIR__),'form_profile.htm',$elements);
		}

	}
}elseif( $action == 'test' ){
	$obj = AmazonProfile::withId(1);
	$product = Product::withId(3692);
	$cat = $obj->getObjCategory($product);
	

	
	$cat->getXML();
}elseif( $action == 'ship'){
	//GetEligibleShippingServices 

	require('cpigroup/php-amazon-mws/includes/classes.php');
	$store_obj = AmazonStore::prepareQuery()->getOne();
	$store_obj->initMarket('Italy');
	
	global $stores;

	
	try{
	$feed = new AmazonMerchantServiceList('Italy');
	//$lista = $list->fetchShipments();	
	debugga($feed);exit;
	}catch(Exception $e ){
		debugga($e);exit;
	}
}elseif( $action == 'get_asin'){
	$id = _var('id');
	$market = _var('market');
	ini_set("output_buffering", "0");
	set_time_limit(0); 
	ob_implicit_flush(true);
	ob_end_flush();

	
	$num_to_insert = 0;

	require('cpigroup/php-amazon-mws/includes/classes.php');

	$obj = AmazonStore::withId($id);
	if( is_object($obj) ){
		$obj->initMarket($market);
		$database = _obj('Database');
		$asins = $database->select('*','amazon_asin',"marketplace='{$market}'");
		foreach($asins as $m){
			$asin[$m['value']] = $m['asin'];
		}
		
		
		$eans = $database->select('distinct(ean) as code','product',"ean is not null AND ean not in (select value from amazon_asin where marketplace='{$market}')");

		
		
		$tot = $database->select('count(distinct ean) as tot','product',"ean is not null AND ean in (select value from amazon_asin where marketplace='{$market}')");
		if( okArray($tot)){
			$num_to_insert = $tot[0]['tot'];
		}

		$check_exist_upc = $database->execute("SHOW COLUMNS FROM product LIKE 'upc'");
		if( okArray($check_exist_upc)){
			$upcs = $database->select('distinct(upc) as code','product',"upc is not null AND upc not in (select value from amazon_asin where marketplace='{$market}')");
			$tot = $database->select('count(distinct upc) as tot','product',"upc is not null AND upc in (select value from amazon_asin where marketplace='{$market}')");
			if( okArray($upcs) ){
			
				if( okArray($eans) ){
					$eans = array_merge($eans,$upcs);
				}else{
					$eans = $upcs;
				}
			}
			if( okArray($tot)){
				$num_to_insert += $tot[0]['tot'];
			}
		}
		
		//$res = $this->ean_check("mdma rete bianco nero'31");
		
		$list_ean = array();
		$list_upc = array();
		foreach($eans as $val){	
			$bc_validator = new BarcodeValidator($val['code']);
			if( $bc_validator->isValid() ){
				switch($bc_validator->getType()){
					case 'EAN':
						$list_ean[] = $val['code'];
						break;
					case 'UPC':
						$list_upc[] = $val['code'];
						break;
				}
			}

		}
		
		$totale = count($list_upc)+count($list_ean);
		
		
		$current = $totale;
		$_list = array();
		foreach($list_ean as $v){


			$_list[] = $v;
				
			if( count($_list) == 5 ){
				
				//error_log(print_r($_list,true));
				$obj = new AmazonProductGetMatchingProductForId($market);
				
				$obj->setIdType('EAN');
				$obj->setIdList($_list);
				
				
				
				if( $obj->getProductList() ){


					
					$lista = $obj->AsinList;
					debugga($lista);
					
					foreach($lista as $ean => $dati){
						$dati['type'] = preg_replace('[\_]',' ',$dati['type']);
						$dati['type'] = mb_convert_case(strtolower($dati['type']), MB_CASE_TITLE, "UTF-8");
						$dati['type'] = preg_replace('[\s]','',$dati['type']);
						
						$toinsert = array(
							'asin' => $dati['asin'],
							'value' => $ean,
							'type' => 'ean',
							'marketplace' => $market,
							'asin_parent' => $dati['asin_parent'],
							'ProductTypeName' => $dati['type']
						);
						
						$check_insert = $database->insert('amazon_asin',$toinsert);
						if( $check_insert ){
							$num_to_insert++;
						}
					}
					
				}
				
				$_list = array();

				$current = $current-5;
				$perc = round(100-(100*$current/$totale));
				echo $perc."||".$num_to_insert;
				ob_get_contents();
				flush();
				ob_flush();
					
				if( $iter > 10 ){
					sleep(1);
				}
				$iter++;
				
				//error_log("Iterazione ".$iter);
			}


			

		}
		if(count($_list) >= 1 ){
			//error_log(print_r($_list,true));
			$obj = new AmazonProductGetMatchingProductForId($market);
			
			$obj->setIdType('EAN');
			$obj->setIdList($_list);
			
			
			
			if( $obj->getProductList() ){

				
				
				$lista = $obj->AsinList;
				debugga($lista);
				
				foreach($lista as $ean => $dati){
					$dati['type'] = preg_replace('[\_]',' ',$dati['type']);
					$dati['type'] = mb_convert_case(strtolower($dati['type']), MB_CASE_TITLE, "UTF-8");
					$dati['type'] = preg_replace('[\s]','',$dati['type']);
					
					$toinsert = array(
						'asin' => $dati['asin'],
						'value' => $ean,
						'type' => 'ean',
						'marketplace' => $market,
						'asin_parent' => $dati['asin_parent'],
						'ProductTypeName' => $dati['type']
					);
					
					$check_insert = $database->insert('amazon_asin',$toinsert);
					if( $check_insert ){
						$num_to_insert++;
					}
				}
				
			}
			$current = $current-5;
			$perc = round(100-(100*$current/$totale));
			echo $perc."||".$num_to_insert;
			ob_get_contents();
			flush();
			ob_flush();
				
			if( $iter > 10 ){
				sleep(1);
			}
			$iter++;

		}

		$_list = array();
		foreach($list_upc as $v){


			$_list[] = $v;
				
			if( count($_list) == 5 ){
				
				//error_log(print_r($_list,true));
				$obj = new AmazonProductGetMatchingProductForId($market);
				
				$obj->setIdType('UPC');
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
							'type' => 'upc',
							'marketplace' => $market,
							'asin_parent' => $dati['asin_parent'],
							'ProductTypeName' => $dati['type']
						);
						
						$check_insert = $database->insert('amazon_asin',$toinsert);
						if( $check_insert ){
							$num_to_insert++;
						}
					}
					
				}
				
				$_list = array();

				$current = $current-5;
				$perc = round(100-(100*$current/$totale));
				echo $perc."||".$num_to_insert;
				ob_get_contents();
				flush();
				ob_flush();
					
				if( $iter > 10 ){
					sleep(1);
				}
				$iter++;
				
				//error_log("Iterazione ".$iter);
			}


			

		}
		if(count($_list) >= 1 ){
			//error_log(print_r($_list,true));
			$obj = new AmazonProductGetMatchingProductForId($market);
			
			$obj->setIdType('UPC');
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
						'type' => 'upc',
						'marketplace' => $market,
						'asin_parent' => $dati['asin_parent'],
						'ProductTypeName' => $dati['type']
					);
					
					$check_insert = $database->insert('amazon_asin',$toinsert);
					if( $check_insert ){
						$num_to_insert++;
					}
				}
				
			}
			$current = $current-5;
			$perc = round(100-(100*$current/$totale));
			echo $perc."||".$num_to_insert;
			ob_get_contents();
			flush();
			ob_flush();
				
			if( $iter > 10 ){
				sleep(1);
			}
			$iter++;

		}
		
		
		
	}

	
	sleep(1);
	echo "ok";
	
	exit;
	

	
	
}elseif( $action == 'check'){
	set_time_limit(0);
	require('cpigroup/php-amazon-mws/includes/classes.php');
	
	
	
	$store_list = AmazonStore::prepareQuery()->get();
	$img_market = array();
	foreach($store_list as $store_obj){
	
		$associazione = $store_obj->getAsins();
		
		
		foreach($associazione as $k => $v){
			$associazione[$k]['img'] = AmazonTool::getMarketplaceImage($k);
			$img_market[$k] = $associazione[$k]['img'];
		}	
		$template->list[] =
			array(
				'id_account' => $store_obj->id,
				'account' => $store_obj->name,
				'composizione' => $associazione
			);
		
		

		

		
	}


	$template->images = $img_market;
	if(  count($template->list) == 1 ){
		$template->one_account = 1;
	}
	ob_start();
	$template->output_module(basename(__DIR__),'check_asin_result.htm');
	$html = ob_get_contents();
	ob_end_clean();
	ob_flush();
	
	$risposta = array(
		'result' => 'ok',
		'data' => $html
		
	);

	echo json_encode($risposta);
	Marion::closeDB();
	exit;
	
}elseif( $action == 'status'){

	

	require('cpigroup/php-amazon-mws/includes/classes.php');
	$store_obj = AmazonStore::prepareQuery()->get();

	if( okArray($store_obj) ){
		foreach($store_obj as $s){
			$s->initMarkets();
			$result = $s->getStatusFeed('_POST_PRODUCT_DATA_');
			if( $result ){

			}

			
		}
	}
}elseif( $action == 'test_ack'){
	//$database->update('amazon_order',"id_narion=1",array('id_marion' => 1));

	require('cpigroup/php-amazon-mws/includes/classes.php');
	$store_obj = AmazonStore::prepareQuery()->getOne();


	$store_obj->initMarkets();

	//$feeds = $store_obj->getAmazonFeedList('Italy');
	
	$stat = $store_obj->getAmazonFeedStatus('50356017935','Italy');
	
	$order_obj = AmazonOrders::init($store_obj);
	$data = array(
		'id_amazon'=>'404-5155564-5934742',
		'id_marion'=>'166',
		'market' => 'Italy'
	);
	$database = _obj('Database');
	$orders = $database->select('*','amazon_order_item','1=1');
	
	
	$order_obj->ack($data,$orders);
	debugga($order_obj);exit;
}elseif( $action == 'import_orders'){
	
	require('cpigroup/php-amazon-mws/includes/classes.php');
	set_time_limit(0);
	$orders = json_decode(_var('orders'));
	$token_amazon = _var('amazon_token');
	if( !authUser()){
		
		if( !$token_amazon ||  $token_amazon != Marion::getConfig('amazon_module','token') ){
			exit;
		}else{
			unset($_SESSION['amazon_orders']);
		}
	}else{
		
		if( $token_amazon &&  $token_amazon == Marion::getConfig('amazon_module','token') ){
			
			unset($_SESSION['amazon_orders']);
		}
	}

	
	if( okArray($_SESSION['amazon_orders']) ){
		
		$temp = $_SESSION['amazon_orders'];
	}else{
		
		
		$store_obj = AmazonStore::prepareQuery()->getOne();
		
		$list_store[$store_obj->id] = $store_obj;
		$order_obj = AmazonOrders::init($store_obj);
		
		$temp[$store_obj->id] = $order_obj->download();
		
	}
	
	foreach($temp as $id_store => $items){
		
		if( $list_store[$id_store] ){
			$store_obj = $list_store[$id_store];
		}else{
			$store_obj = AmazonStore::withId($id_store);
		}
		
		$corrieri = $store_obj->getCarriers();
		foreach($corrieri as $v){
			$map_corrieri_entrata[$v['id_amazon']] = $v['id_marion'];
		}
		if( $token_amazon ){
			
			foreach($items as $item){
				
				
				$id = AmazonOrders::import($item['preview']['order_id'],$item['cart'],$item['orders'],$map_corrieri_entrata);
				
				if( okArray($id) ){
					$errors[] = $item['preview']['order_id'];
					$errors_messages[] = $id;
				}else{
					$toreturn[] = $order_id;
				}
				
			}
		}else{
			
			
			foreach($orders as $order_id){
				$item = $items[$order_id];
				
				$id = AmazonOrders::import($item['preview']['order_id'],$item['cart'],$item['orders'],$map_corrieri_entrata);
				
				if( okArray($id) ){
					$errors[] = $order_id;
					$errors_messages[] = $id;
				}else{
					$toreturn[] = $order_id;
				}
				
			}
		}
	}

	$risposta = array(
		'result' => 'ok',
		'success' => $toreturn,
		'error' => $errors,
		'error_messages' => $errors_messages
		
	);
	
	echo json_encode($risposta);
	Marion::closeDB();
	exit;
}elseif( $action == 'change_status_orders'){
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$token = _var('amazon_token');
	if( !Marion::auth('ecommerce') && $token != Marion::getConfig('amazon_module','token') ){
		exit;
	}
	AmazonOrders::changeStatus2();


}elseif( $action == 'ack'){	
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$token = _var('amazon_token');
	if( !Marion::auth('ecommerce') && $token != Marion::getConfig('amazon_module','token') ){
		exit;
	}

	AmazonOrders::acks2();

	
	debugga('finito');
	exit;
	
}elseif( $action == 'orders'){
	set_time_limit(0); 
	unset($_SESSION['amazon_orders']);
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$store_obj = AmazonStore::prepareQuery()->getOne();
	
	$order_obj = AmazonOrders::init($store_obj);

	$items = $order_obj->download();
	
	if( okArray($items) ){
		$_SESSION['amazon_orders'][$store_obj->id] = $items;
	}
	$items = $_SESSION['amazon_orders'];
	
	
	ob_start();
	$template->list = $items;
	$template->output_module(basename(__DIR__),'orders.htm');
	$html = ob_get_contents();
	ob_end_clean();
	
	$risposta = array(
		'result' => 'ok',
		'data' => $html
		
	);
	
	echo json_encode($risposta);
	Marion::closeDB();
	exit;
	debugga($items);exit;
}elseif( $action == 'delete_upload'){
	$id = _var('id');
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$store_obj = AmazonStore::withId($id);
	$database = _obj('Database');
	$check = $database->select('*','amazon_upload',"id_store = {$store_obj->id} order by id desc limit 1");
	$id = 0;
	if( okArray($check) ){
		$database->update('amazon_upload',"id = {$check[0]['id']}",array('last_operation' => '_END_PROCESS_'));
	}

	$risposta = array(
		'result' => 'ok',
		'upload' => $id
	);
	echo json_encode($risposta);
	Marion::closeDB();
	exit;

}elseif( $action == 'update_inventory'){
	$id = _var('id');
	$template->id_account= $id;
	ob_start();
	$template->output_module('amazon','confirm_update_inventory.htm');
	$html = ob_get_contents();
	ob_end_clean();
	$risposta = array(
		'result' => 'ok',
		'upload' => $id,
		'html' => $html,
	);
	echo json_encode($risposta);
	Marion::closeDB();
	exit;
}elseif( $action == 'check_upload'){
	$id = _var('id');
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$store_obj = AmazonStore::withId($id);
	$database = _obj('Database');
	$check = $database->select('*','amazon_upload',"id_store = {$store_obj->id} AND type='multiple' order by id desc limit 1");
	$template->id_account= $id;
	$id = 0;
	if( okArray($check) ){
		if( $check[0]['last_operation'] != '_END_PROCESS_'){
			$id =  $check[0]['id'];
		}
	}
	$template->new_upload = true;
	if( $id ){
		$template->new_upload = false;
	}
	ob_start();
	$template->output_module('amazon','confirm_new_upload.htm');
	$html = ob_get_contents();
	ob_end_clean();
	$risposta = array(
		'result' => 'ok',
		'upload' => $id,
		'html' => $html,
	);
	echo json_encode($risposta);
	Marion::closeDB();
	exit;
}elseif( $action == 'send_single2'){
	

	set_time_limit(0);
	$type_feed = '_POST_PRODUCT_DATA_';
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$store_obj = AmazonStore::prepareQuery()->getOne();
	
	
	$store_obj->initMarkets();
	$store_obj->getProducts('Italy');

	//debugga($store_obj);exit;
	$feed_products = $store_obj->getXMLFeedProducts();
	
		//getAmazonFeedList


	$res = $store_obj->upload_single_feed($type_feed,1);


	$risposta = array(
		'result' => 'ok',
	);
	echo json_encode($risposta);
	exit;
}elseif( $action == 'prova'){
	$store_obj = AmazonStore::prepareQuery()->getOne();

	$products = $store_obj->getProducts('Germany');
	debugga($store_obj);exit;
}elseif( $action == 'reports'){
	require('cpigroup/php-amazon-mws/includes/classes.php');

	$database = _obj('Database');
	$_stores = AmazonStore::prepareQuery()->get();
	foreach($_stores as $store_obj){
		
		
		
		$select = $database->select('*','amazon_upload',"type='single' AND last_operation='_GET_MERCHANT_LISTINGS_DATA_' AND finished=0 AND id_store={$store_obj->id}");
		
		
		$store_obj->initMarkets();
		if( okArray($select) ){
			$select = $select[0];
			
			
			
			$status = $store_obj->getStatusReport('_GET_MERCHANT_LISTINGS_DATA_',$select['id']);
			
			if( $status ){
				$database->update('amazon_upload',"type='single' AND last_operation='_GET_MERCHANT_LISTINGS_DATA_' AND finished=0",array('finished'=>1));
			}
			
			
			//controllo se è finito

		}else{
			$res = $store_obj->upload_single_feed('_GET_MERCHANT_LISTINGS_DATA_',1);
			

		}
	}
	echo "qua";
	exit;
}elseif( $action == 'sincro_new'){
	require('cpigroup/php-amazon-mws/includes/classes.php');
	
	/*$obj = AmazonSyncro::init(1,'Italy');
	

	$obj->send('_POST_INVENTORY_AVAILABILITY_DATA_');*/
	
	

	
	$obj = AmazonSyncro::init(1);
	$obj->getResponses();
	
	
	debugga('finito');exit;
}elseif( $action == 'send_single'){
	$token = _var('amazon_token');
	$market = _var('market');
	if( !Marion::auth('ecommerce') && $token != Marion::getConfig('amazon_module','token') ){
		exit;
	}

	set_time_limit(0);
	$type = _var('type');
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$store_obj = AmazonStore::prepareQuery()->getOne();
	$no_check = 1;
	switch($type){
		case 'inventory';
			$type_feed = '_POST_INVENTORY_AVAILABILITY_DATA_';
			break;
		case 'reports';
			$no_check = 0;
			$type_feed = '_GET_MERCHANT_LISTINGS_DATA_';
			break;
		case 'catalog';
			$type_feed = '_POST_PRODUCT_DATA_';
			break;
		case 'price';
			$type_feed = '_POST_PRODUCT_PRICING_DATA_';
			break;
		default:
			$type_feed = '_POST_INVENTORY_AVAILABILITY_DATA_';
			break;
	}
	
	
	$res = $store_obj->upload_single_feed($type_feed,$no_check);
	if( $type == 'catalog'){
		//$res = $store_obj->upload_single_feed('_POST_PRODUCT_PRICING_DATA_',1);
	}
	

	$risposta = array(
		'result' => 'ok',
	);
	echo json_encode($risposta);
	exit;
	
}elseif( $action == 'cronjob'){
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$database = _obj('Database');
	
	
	//AGGIORNAMENTO QUANTITA'
	
	$select = $database->select('*','amazon_upload',"type='single' AND last_operation='_POST_INVENTORY_AVAILABILITY_DATA_' AND finished=0");
	
	if( okArray($select) ){
		$select = $select[0];
		
		$store_obj = AmazonStore::withId($select['id_store']);
		$res = $store_obj->upload_single_feed($select['last_operation']);

		
		//controllo se è finito

	}else{
		$select = $database->select('*','amazon_upload',"type='single' AND last_operation='_POST_INVENTORY_AVAILABILITY_DATA_' AND finished=1 order by id DESC limit 1");
		if( okArray($select) ){
			$select = $select[0];

			$diff = time()-strtotime($select['timestamp']);
			$min = $diff/60; 
			if( $min > 10 ){
				$store_obj = AmazonStore::withId($select['id_store']);
				$res = $store_obj->upload_single_feed($select['last_operation']);
			}
		}else{
			$store_obj = AmazonStore::prepareQuery()->getOne();

			$type = '_POST_INVENTORY_AVAILABILITY_DATA_';
			$res = $store_obj->upload_single_feed($type);

		}
		

	}
	debugga($select);exit;

}elseif( $action == 'create_dir'){
	try{
		$res = mkdir('xml_upload');
	}catch( Exception $e ){
		debugga($e);exit;
	}
	
	mkdir('reports');

	$chmod = "0777";
	chmod('xml_upload', octdec($chmod));
	chmod('reports', octdec($chmod));

	debugga('qua');exit;

}elseif( $action == 'send'){
	set_time_limit(0);
	//error_log('INIZIO PROCEDURA');
	require('cpigroup/php-amazon-mws/includes/classes.php');
	
	
	$id_account = _var('id');
	
	if( !$id_account ){
		return false;
		
	}
	$store_obj = AmazonStore::withId($id_account);
	
	

	/*
	$store_obj->getStatusReport('_GET_MERCHANT_LISTINGS_DATA_',36);
	debugga($store_obj);exit;*/

	//13863599330017872
	//$store_obj->getAsins('Italy',1);
	//$store_obj->getProducts();
	//exit;

	//$xml = $store_obj->getXMLFeedProducts();
	//debugga($xml);exit;
	/*$associazione = $store_obj->getAsins();

	debugga($associazione);exit;
	debugga('finito');
	exit;*/
	

	
	//$store_obj->upload(1);
	$store_obj->upload(0);
	
	switch($store_obj->_status){
		case '_GET_MERCHANT_LISTINGS_DATA_':
		case '_SEARCH_ASIN_':
				$message = 'RICERCA PRODOTTI SU AMAZON';
				$image = '1_ricerca.png';
				break;
		case '_POST_PRODUCT_DATA_':
				$message = 'INVIO DATI AD AMAZON';
				$image = '2_invio-dati.png';
				break;
			case '_POST_PRODUCT_PRICING_DATA_':
				$message = 'INVIO PREZZI AD AMAZON';
				$image = '3_invio-prezzi.png';
				break;
			case '_POST_INVENTORY_AVAILABILITY_DATA_':
				$message = 'INVIO GIACENZE AD AMAZON';
				$image = '4_invio-giacenze.png';
				break;
			case '_END_PROCESS_':
				$message = 'FINE IMPORTAZIONE';
				$image = '5_sincronizzazione-avvenuta.png';
				break;
	}
	if( $store_obj->_status == '_GET_MERCHANT_LISTINGS_DATA_'){
		$delay = 10000;
	}else{
		$delay = 500;
	}
	$risposta = array(
		'result' => 'ok',
		'status' => $store_obj->_status,
		'message' => $message,
		'image' => "images/".$image,
		'delay' => $delay,
		'fine' => ($store_obj->_status == '_END_PROCESS_')?1:0
	);
	echo json_encode($risposta);
	exit;
	debugga($store_obj->_status);exit;


	/*if( okArray($store_obj) ){
		foreach($store_obj as $s){
		
			

			

			$s->upload();
			debugga($s->_status);exit;
		}
	}*/

}elseif( $action == 'list_feeds_market'){
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$id = _var('id');
	$market = _var('market');
	$database = _obj('Database');
	$list = $database->select('*','amazon_feed_sync',"marketplace='{$market}' AND id_store={$id} order by timestamp DESC limit 24");
	
	$file = "reports/{$id}_{$market}.csv";
	if( file_exists($file) ){

		$date = strftime('%d/%m/%Y %H:%M',filemtime($file));
		$template->date_last_report = $date;
	}
	
	
	/*$obj = AmazonStore::withId($id);
	
	$obj->initMarkets();
	$list = $obj->getAmazonFeedListDb($market);*/
	
	foreach($list as $k => $v){
		
		switch($v['FeedType']){
			case '_POST_INVENTORY_AVAILABILITY_DATA_':
				$list[$k]['FeedType'] = 'INVENTARIO';
				break;
			case '_POST_PRODUCT_PRICING_DATA_':
				$list[$k]['FeedType'] = 'PREZZI';
				break;
			case '_POST_PRODUCT_IMAGE_DATA_':
				$list[$k]['FeedType'] = 'IMMAGINI';
				break;
			case '_POST_PRODUCT_DATA_':
				$list[$k]['FeedType'] = 'PRODOTTI';
				break;
			case '_POST_PRODUCT_RELATIONSHIP_DATA_':
				$list[$k]['FeedType'] = 'RELAZIONI';
				break;
			case '_GET_MERCHANT_LISTINGS_DATA_':
				$list[$k]['FeedType'] = 'REPORTS';
				break;
			case '_POST_ORDER_ACKNOWLEDGEMENT_DATA_':
				$list[$k]['FeedType'] = 'CHECK ORDINI';
				break;
			case '_POST_ORDER_FULFILLMENT_DATA_':
				$list[$k]['FeedType'] = 'AGGIORNAMENTO STATO ORDINI';
				break;
				break;
		}
		
		switch($v['FeedProcessingStatus']){
			case '_DONE_':
				$html = "<span class='label label-success'>DONE</span>";
				$list[$k]['ok'] = 1;
				break;
			case '_IN_PROGRESS_':
				$html = "<span class='label label-warning'>PROCESSING</span>";
				break;
			case '_SUBMITTED_':
				$html = "<span class='label label-info'>SUBMITTED</span>";
				break;
		}
		$list[$k]['FeedProcessingStatus'] = $html;
				
	}
	
	$template->account_id = $id;
	$template->market = $market;
	$template->list = $list;
	ob_start();
	$template->output_module(basename(__DIR__),'feeds.htm');
	$html = ob_get_contents();
	ob_end_clean();
	ob_flush();
	
	$risposta = array(
		'result' => 'ok',
		'data' => $html
		
	);

	echo json_encode($risposta);
	exit;
	
}elseif( $action == 'acks'){
	require('cpigroup/php-amazon-mws/includes/classes.php');

	
	
	AmazonOrders::acks2();


	
	exit;
}elseif( $action == 'feeds2'){
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$list = AmazonStore::prepareQuery()->get();
	foreach($list as $obj){
		$obj->initMarkets();
		$list = $obj->getAmazonFeedList();
		debugga($list);exit;
	}
	exit;
}elseif( $action == 'list_feeds'){
	Marion::setMenu('amazon');
	$template->amazon_action = 'reports'; 
	

	
	$list = AmazonStore::prepareQuery()->get();
	foreach($list as $obj){
		$markets = array();
		foreach($obj->marketplace as $v){
			$markets[] = array(
				'name' => $v,
				'img' => AmazonTool::getMarketplaceImage($v)
			);
		}

		$markets[] = array(
			'name' => 'Europe',
			'img' => AmazonTool::getMarketplaceImage('Europe')."?v=1"
		);

		
		$feeds[] = array(
			'account_id' => $obj->id,
			'account' => $obj->name,
			'markets' => $markets
		);
		
	}

	
	

	$template->list_feeds = $feeds;
	$template->output_module(basename(__DIR__),'list_feeds.htm');
}elseif( $action == 'get_feed'){
	Marion::setMenu('amazon');
	$feedID = _var('feedID');
	$market = _var('market');
	$id_store = _var('id_store');
	
	if( !file_exists('responses/'.$feedID.".json") ){
		/*require('cpigroup/php-amazon-mws/includes/classes.php');
		
		$obj = AmazonStore::withId($id_store);
		
		$obj->initMarkets();
		$res = $obj->getAmazonFeedStatus($feedID,$market);*/
	}else{
		
		$res = json_decode(file_get_contents('responses/'.$feedID.".json"));
	}
	//debugga($res);exit;

	if( is_object($res) ){
		$template->reports = (array)$res->header;
		foreach($res->messages as $v){
			$messaggi[] = (array)$v;
		}
		$template->dati = $messaggi;
	}else{
		$template->no_result = true;
	}
	
	/*exit;
	$database = _obj('Database');
	$dati_input = $database->select('*','amazon_feed',"FeedSubmissionId='{$feedID}'");
	if( okArray($dati_input) ){
		$file_input = "xml_upload/".$dati_input[0]['id_upload']."/".$dati_input[0]['marketplace'].$dati_input[0]['FeedType'].".xml";
		
		$feed_input = htmlentities(file_get_contents($file_input));
		//debugga($feed_input);exit;
		//$feed_input = preg_replace('<','&lt;',$feed_input);
		//$feed_input = preg_replace('>','&gt',$feed_input);

		$template->feed_input = $feed_input;
	}
	
	if( $res ){
		file_put_contents('responses/'.$feedID.".xml",$res);
		
		$xml = simplexml_load_string($res);
		$titolo = (string)$xml->MessageType;
		$template->titolo = $titolo;
		$reports = (array)$xml->Message->ProcessingReport->ProcessingSummary;


		$result = $xml->Message->ProcessingReport->Result;
		foreach($result as $v){
			
			$dati = array(
				'message_id'=> (string)$v->MessageID,
				'sku'=> (string)$v->AdditionalInfo->SKU,
				'message'=> (string)$v->ResultDescription,
				'result'=> (string)$v->ResultCode,
				'error_code'=> (string)$v->ResultMessageCode,
	
			);
			$explode = explode('_',$dati['sku']);
			$dati['id_product'] = $explode[0];
			$template->dati[] = $dati;

		}


		$template->reports = $reports;
		

	}*/
	
	

	//debugga($dati);exit;
	//if( !$res ) $template->no_result = true;
	$template->feed_id = $feedID;
	$template->feed = htmlentities($res);
	$template->output_module(basename(__DIR__),'view_feed.htm');
}elseif( $action == 'test_p'){
	require('cpigroup/php-amazon-mws/includes/classes.php');
	/*$id = 82;
	$product = Product::withId($id);
	$profilo = AmazonProfile::withId(9);

	
	$_store = $profilo->getStore();
	*/
	$_store = AmazonStore::withId(1);
	
	$_store->initMarkets();
	//$store->initMarkets();
	

	
	$_store->getProducts('Italy');
	
	$market = 'Italy';
	
	$xml_product = $profilo->getXMLProduct($product,$market);
	debugga($product);exit;
	$xml = $_store->getXMLFeedNewProducts($xml_product);

	
	$amz=new AmazonFeed($market); //if there is only one store in config, it can be omitted
	$amz->setFeedType("_POST_PRODUCT_DATA_"); //feed types listed in documentation
	$amz->setFeedContent($xml); //can be either XML or CSV data; a file upload method is available as well
	$amz->submitFeed(); 

	debugga('finito');exit;
	


	$xml = $_store->getXmlFeedRelation($product,$market);
	
	$amz=new AmazonFeed($market); //if there is only one store in config, it can be omitted
	$amz->setFeedType("_POST_PRODUCT_RELATIONSHIP_DATA_"); //feed types listed in documentation
	$amz->setFeedContent($xml); //can be either XML or CSV data; a file upload method is available as well
	$amz->submitFeed();
	


	$xml = $_store->getXmlFeedImage($product,$market);
	
	$amz=new AmazonFeed($market); //if there is only one store in config, it can be omitted
	$amz->setFeedType("_POST_PRODUCT_IMAGE_DATA_"); //feed types listed in documentation
	$amz->setFeedContent($xml); //can be either XML or CSV data; a file upload method is available as well
	$amz->submitFeed();
	
	//$xml = $_store->getXMLFeedNewProducts($xml);
	debugga('finito');exit;
	
	 //what actually sends the request
	//echo '<pre>'; print_r($amz->getResponse()); echo '</pre>';
	debugga($amz->getResponse());exit;
	debugga($profilo);exit;
	debugga($id);exit;
}elseif( $action == 'import_new_products'){
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$id_store = _var('store');
	$market = _var('market');
	$_store = AmazonStore::withId($id_store);

	if( is_object($_store) ){
		$_store->initMarket($market);
		$xml_feed_product = '';
		$products = $_SESSION['amazon_module']['new_products'][$id_store][$market];


		
		foreach($products as $p){

			//prendo l'id del profilo
			$id_profilo = $_store->mapping_profile[$p['section']];
			if( $id_profilo ){
				if( $profilo_visto[$id_profilo] ){
					$profilo = $profilo_visto[$id_profilo];
				}else{
					$profilo = AmazonProfile::withId($id_profilo);
					$profilo_visto[$id_profilo] = $profilo;
				}


				if( is_object($profilo) ){
					// se l'oggetto profilo esiste creo la richiesta XML per il prodotto in esame
					$product = Product::withId($p['id_product']);
					$_products[] = $product;
					$xml_product = $profilo->getXMLProduct($product,$market,$p);
					$xml_feed_product.= $xml_product;
				}
			}
			
		}
		
		
		
		
		if( $xml_feed_product ){
			$database = _obj('Database');
			$xml_feed_product = $_store->getXMLFeedNewProducts($xml_feed_product);
			
			$amz=new AmazonFeed($market); 
			$amz->setFeedType("_POST_PRODUCT_DATA_"); 
			$amz->setFeedContent($xml_feed_product);
			$amz->submitFeed();
			
			$id_upload = $database->insert('amazon_upload',array('id_store' => $id_store,'last_operation' => '_END_PROCESS_','type' => 'multiple','finished' => 1));
			$res = $amz->getResponse();

			unset($res['SubmittedDate']);
			$res['marketplace'] = $market;
			$res['id_store'] = $id_store;
			$res['id_upload'] = $id_upload;
			$database->insert('amazon_feed',$res);
			$_store->saveXML($market."_POST_PRODUCT_DATA_",$xml_feed_product,$id_upload);
			

			

		
			
			$xml = $_store->getXmlFeedRelation($_products,$market);
			
			$amz=new AmazonFeed($market); //if there is only one store in config, it can be omitted
			$amz->setFeedType("_POST_PRODUCT_RELATIONSHIP_DATA_"); //feed types listed in documentation
			$amz->setFeedContent($xml); //can be either XML or CSV data; a file upload method is available as well
			$amz->submitFeed();
			$_store->saveXML($market."_POST_PRODUCT_RELATIONSHIP_DATA_",$xml,$id_upload);
			//$id_upload = $database->insert('amazon_upload',array('id_store' => $id_store,'last_operation' => '_POST_PRODUCT_RELATIONSHIP_DATA_','type' => 'single'));

			$res = $amz->getResponse();

			unset($res['SubmittedDate']);
			$res['marketplace'] = $market;
			$res['id_store'] = $id_store;
			$res['id_upload'] = $id_upload;
			$database->insert('amazon_feed',$res);
			


			$xml = $_store->getXmlFeedImage($_products,$market);
			
			$amz=new AmazonFeed($market); //if there is only one store in config, it can be omitted
			$amz->setFeedType("_POST_PRODUCT_IMAGE_DATA_"); //feed types listed in documentation
			$amz->setFeedContent($xml); //can be either XML or CSV data; a file upload method is available as well
			$amz->submitFeed();
			$_store->saveXML($market."_POST_PRODUCT_IMAGE_DATA_",$xml,$id_upload);
			//$id_upload = $database->insert('amazon_upload',array('id_store' => $id_store,'last_operation' => '_POST_PRODUCT_IMAGE_DATA_','type' => 'single'));
			$res = $amz->getResponse();

			unset($res['SubmittedDate']);
			$res['marketplace'] = $market;
			$res['id_store'] = $id_store;
			$res['id_upload'] = $id_upload;
			$database->insert('amazon_feed',$res);


			$currency = AmazonTool::$currency_markets[$market];
			$xml = $_store->getXMLFeedPrice2($_products,$currency);

			

			$amz=new AmazonFeed($market); //if there is only one store in config, it can be omitted
			$amz->setFeedType("_POST_PRODUCT_PRICING_DATA_"); //feed types listed in documentation
			$amz->setFeedContent($xml); //can be either XML or CSV data; a file upload method is available as well
			$amz->submitFeed();
			$_store->saveXML($market."_POST_PRODUCT_PRICING_DATA_",$xml,$id_upload);
			//$id_upload = $database->insert('amazon_upload',array('id_store' => $id_store,'last_operation' => '_POST_PRODUCT_IMAGE_DATA_','type' => 'single'));
			$res = $amz->getResponse();
		
			unset($res['SubmittedDate']);
			$res['marketplace'] = $market;
			$res['id_store'] = $id_store;
			$res['id_upload'] = $id_upload;
			$database->insert('amazon_feed',$res);

			
			$xml = $_store->getXMLFeedInventory2($_products);

			$amz=new AmazonFeed($market); //if there is only one store in config, it can be omitted
			$amz->setFeedType("_POST_INVENTORY_AVAILABILITY_DATA_"); //feed types listed in documentation
			$amz->setFeedContent($xml); //can be either XML or CSV data; a file upload method is available as well
			$amz->submitFeed();
			$_store->saveXML($market."_POST_INVENTORY_AVAILABILITY_DATA_",$xml,$id_upload);
			//$id_upload = $database->insert('amazon_upload',array('id_store' => $id_store,'last_operation' => '_POST_PRODUCT_IMAGE_DATA_','type' => 'single'));
			$res = $amz->getResponse();
		
			unset($res['SubmittedDate']);
			$res['marketplace'] = $market;
			$res['id_store'] = $id_store;
			$res['id_upload'] = $id_upload;
			$database->insert('amazon_feed',$res);
			
	


		}
		
	}

	$risposta = array(
		'result' => 'ok',
		
		
	);

	echo json_encode($risposta);
	exit;

	


	
}elseif( $action == 'get_new_products'){
	$id_store = _var('store');
	$market = _var('market');
	

	$_store = AmazonStore::withId($id_store);
	if( is_object($_store) ){
		
		$categories = json_decode($_store->categories);
		foreach($categories as $v){
			if( (int)$v ){
				if( $_store->mapping_profile[$v] ){
					$where .= "{$v},";
				}
			}
		}
		$where = preg_replace('/\,$/','',$where);
		
	}

	$database = _obj('Database');
	$select = $database->select('a.*,p.section','amazon_product as a join product as p on p.id=a.id_product',"id_account={$id_store} AND marketplace='{$market}' AND new_product=1 AND (deleted IS NULL OR deleted = 0) AND section IN ({$where}) AND disable_sync = 0");

	
	


	

	$_SESSION['amazon_module']['new_products'][$id_store][$market] = $select;
	
	
	$tot = count($select);
	

	$template->products_info = array(
		'tot' => $tot,
		'img' => AmazonTool::$img_markets[$market],
		'id_store' => $id_store,
		'market' => $market
	
	);

	ob_start();
	$template->output_module(basename(__DIR__),'new_products.htm');
	$html = ob_get_contents();
	ob_end_clean();
	ob_flush();

	$risposta = array(
		'result' => 'ok',
		'data' => $html
		
	);

	echo json_encode($risposta);
	exit;
}elseif( $action == 'get_all_feeds'){
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$market = 'Italy';
	$stores = AmazonStore::prepareQuery()->get();
	foreach($stores as $_store){
		$_store->initMarkets();

		$amz=new AmazonFeedList('Italy');
		$amz->setTimeLimits('- 24 hours'); //limit time frame for feeds to any updated since the given time
		//$amz->setFeedStatuses(array("_SUBMITTED_", "_IN_PROGRESS_", "_DONE_")); //exclude cancelled feeds
		$amz->fetchFeedSubmissions(); //this is what actually sends the request

		$res = $amz->getFeedList();
		debugga($res);exit;
	}
}elseif( $action == 'parse_file'){
	$path = 'reports/1__GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_.csv';
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
	debugga($result);exit;
}elseif( $action == 'new_products'){

	$stores = AmazonStore::prepareQuery()->get();
	
	foreach($stores as $k => $store){
		
		
	
		foreach($store->marketplace as $m){
			$img = AmazonTool::$img_markets[$m];

			$markets[$store->id][$m] = $img;
		}

	}

	if( count($stores) == 1 ){
		$template->one_account = $stores[0];
	}
	
	$template->markets = $markets;
	$template->stores = $stores;

	ob_start();
	$template->output_module(basename(__DIR__),'new_products.htm');
	$html = ob_get_contents();
	ob_end_clean();
	ob_flush();
	
	$risposta = array(
		'result' => 'ok',
		'data' => $html
		
	);

	echo json_encode($risposta);
	exit;
/*}elseif( $action == 'n'){
	
	require('cpigroup/php-amazon-mws/includes/classes.php');
	$_store = AmazonStore::withId(1);
	$_store->initMarkets();
	
	$market = 'Italy';
	$_store->getProducts($market);

	$xml = $_store->getXMLFeedProducts();
	debugga($xml);exit;
	//$store->initMarkets();
	

*/
}else{
	Marion::setMenu('amazon');

	$template->amazon_action = 'azioni';
	$template->token = Marion::getConfig('amazon_module','token');
	$list = AmazonStore::prepareQuery()->get();
	if( count($list) == 0) $template->no_amazon = true;
	if( count($list) == 1){ 
		$template->one_account = $list[0];
	}
	$template->list = $list;
	$template->output_module(basename(__DIR__),'index.htm');
}
Marion::closeDB();

function array_amazon_categories(){
	$array = array(
		'' => '----',
		'Clothing' => 'Abbigliamento',
		'Shoes' => 'Scarpe e Accessori'
	);

	return $array;
}

function array_amazon_stores(){
	$list = AmazonStore::prepareQuery()->get();
	foreach($list as $v){
		$array[$v->id] = $v->name;
	}
	return $array;
}



function array_amazon_marketplace(){
	return array_keys(AmazonTool::$markets);
}



function array_status_mapping_amazon(){
	if( class_exists('CartStatus') ){
		$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->where('label','active','<>')->where('deleted','active','<>')->get();
		foreach($status_avaiables as $v){
			$toreturn[$v->label] = $v->get('name');
		}
	}else{
		$template = _obj('Template');
		$toreturn = $template->array_status_cart();
	}

	return $toreturn;
}
?>