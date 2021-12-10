<?php
class IndexController extends FrontendController{
	private $sandbox_endpoint = 'https://api-pinkstaging.pink-connect.com/v4';
	private $live_endpoint = 'https://api.pink-connect.com/v4';
	private $sandbox = false;
	private $_output_dir = _MARION_MODULE_DIR_.'privalia/outputs/'; 
	private $_params = array(
		'id_tax' => 21,
		'uid_product' => 'sku',
		'id_invetory' => 1,
		'token_sandbox' => 'Z6POxvbJtyoAEZU1h3Jk6gaAt_8-Yyjf',
		'token_live' => 'NQ8cSY-TQy3c1yvxrNQmSXqXB6HLLO3u',
		'shopChannelId' => 38
	);


	function ajax(){
		$this->getParameters();
		$action = $this->getAction();
	    switch($action){
			case 'get_carriers':
				$response = $this->get('/carriers');
				$database = _obj('Database');
				$database->delete('privalia_carrier');
				
				foreach($response as $v){
					$database->insert('privalia_carrier',$v);
				}
				echo json_encode(array('result' => 'ok'));
				break;
			case 'get_channels':
				$response = $this->get('/shop-channels');
				
				$database = _obj('Database');
				$database->delete('privalia_channel',"1=1");
				foreach($response as $v){
					$database->insert('privalia_channel',$v);
				}
				echo json_encode(array('result' => 'ok'));
				break;
			case 'get_status':
				
				$database = _obj('Database');
				$list = $database->select('*','privalia_feed');
				foreach($list as $v){
					$response = $this->get('/status/'.$v['output']);
					$database->update('privalia_feed',"id={$v['id']}",array('status' => $response['status']));
					
					sleep(2);
				}
				echo json_encode(array('result' => 'ok'));
				break;
		}
	}
	

	function display(){
	   $this->getParameters();
	   $action = $this->getAction();
	   switch($action){
			case 'get_orders':
				require_once(_MARION_MODULE_DIR_."privalia/classes/PrivaliaOrder.class.php");
				$response = $this->get('/orders');
				$config = Marion::getConfig('privalia');
				foreach($response as $v){
					$obj = PrivaliaOrder::import($v,$config);
				}
				break;
		  
			case 'get_channels':
				$response = $this->get('/shop-channels');
				$database = _obj('Database');
				$database->delete('privalia_channel',"1=1");
				foreach($response as $v){
					$database->insert('privalia_channel',$v);
					debugga($database->error);
				}
				break;
			case 'get_taxonomy_attribute_values':
				
				$response = $this->get('/taxonomy/value-list');
				
				$database = _obj('Database');
				$database->delete('privalia_taxonomy_attribute_value',"1=1");
				
				foreach($response as $v){
					$v['valori'] = serialize($v['values']);
					unset($v['values']);
					$v['label'] = serialize($v['label']);
					$database->insert('privalia_taxonomy_attribute_value',$v);
					debugga($database->error);
				}
				//debugga($list);exit;
				break;
			case 'get_taxonomy_attributes':
				$this->sandbox = false;
				$this->_params['token_live'] = 'NQ8cSY-TQy3c1yvxrNQmSXqXB6HLLO3u';
				$response = $this->get('/taxonomy/attributes');
				
				$database = _obj('Database');
				$database->delete('privalia_taxonomy_attribute',"1=1");
				
				foreach($response as $v){
					$v['description'] = serialize($v['description']);
					$v['label'] = serialize($v['label']);
					$database->insert('privalia_taxonomy_attribute',$v);
					debugga($database->error);
				}
				//debugga($list);exit;
				break;
			case 'get_taxonomies':
				
				$response = $this->get('/taxonomy');
				
				
				$database = _obj('Database');
				$database->delete('privalia_taxonomy',"1=1");
				foreach($response as $v){
					$v['name'] = serialize($v['name']);
					$v['path'] = serialize($v['path']);
					$database->insert('privalia_taxonomy',$v);
				}
				debugga('finito');exit;
				//debugga($response);exit;
				break;
			case 'get_markets':
				$response = $this->get('/marketplaces');
				debugga($response);exit;
			case 'get_carriers':
				$response = $this->get('/carriers');
				$database = _obj('Database');
				$database->delete('privalia_carrier');
				
				foreach($response as $v){
					$database->insert('privalia_carrier',$v);
				}
				echo json_encode(array('result' => 'ok'));
				break;
			case 'prices':
				break;
			case 'inventory':
				exit;
				$file = $this->getInventoryFile();
				//$output = base64_encode(file_get_contents($file));

				$params = array(
					'stock' => new CURLFile($file, null, 'stock.csv')
				);
				$response = $this->call('/stock',$params);
				
				break;
			case 'get_status':
				$filename = _var('filename');
				$filename = 'SHOP_CATALOG_38_20200717160049.csv';
				$filename = 'PRODUCT_20200723181442.csv';
				
				$response = $this->call('/status/'.$filename,array(),false);
				debugga($response);
				exit;
				$database = _obj('Database');
				$list = $database->select('*','privalia_feed');
				foreach($list as $v){
					$response = $this->get('/status/'.$v['output']);
					$database->update('privalia_feed',"id={$v['id']}",array('status' => $response['status']));
					
					sleep(2);
				}
				
				break;
			case 'catalog':
				
				$id_channel = _var('id_channel');
				$id_channel = 38;
				


				$file = $this->getCatalogFile($id_channel);

				
				$params = array(
					'catalog' => new CURLFile($file, null, 'catalog.csv')
				);
				$response = $this->call('/catalog/'.$this->_params['shopChannelId'],$params);
				debugga($response);
				break;
	   }
	   if( in_array($action,array('catalog','inventory') )){
			$database = _obj('Database');
			$toinsert = array(
				'input' => $file,
				'type' => $action,
				'output' => $response,

			);
			$database->insert('privalia_feed',$toinsert);
	   }
	}


	function getParameters(){
		$dati = Marion::getConfig('privalia');
		
		if( $dati ){
			$this->sandbox = $dati['sandbox'];
			foreach($dati as $v){
				if( array_key_exists($this->_params,$k) ){
					$this->_params[$k] = $v;
				}
			}
		}
	}


	function getCatalogFile($id_channel){
		require_once(_MARION_MODULE_DIR_."privalia/classes/PrivaliaList.class.php");
		$list = PrivaliaList::prepareQuery()->where('id_channel',$id_channel)->get();
		

		$dati_section = array();
		if( okArray($list) ){
			$where = "section IN (";
			foreach($list as $item){
				foreach($item->categories as $v){
					$profile_section[$v] = $item->id_profile;
					$where .= "{$v},";
				}
			}
			$where = preg_replace('/\,$/',')',$where);
			
			$database = _obj('Database');
			$list = $database->select('id,section',"product","parent=0 AND deleted = 0 AND visibility=1 AND {$where}");
			
			require_once(_MARION_MODULE_DIR_."privalia/classes/Tracciato.class.php");
			require_once(_MARION_MODULE_DIR_."privalia/tracciati/Outlet.php");

			$obj = new Outlet();
			$profile = $database->select('*','privalia_prodile',"id=1");
			foreach($list as $v){
				$product = Product::withId($v['id']);
				$obj->addProduct($product,$profile_section[$product->section]);
			}

			$data = $obj->prepareCSV();
			$file = $this->buildCSV('catalog',$data);
			return $file;
		}
		/*debugga($obj->data_csv);exit;
		debugga($obj);exit;


		$model = array(
			'gtin' => '',
			'sku' => '',
			'stock' => '',
		);


		$uid = $this->_params['uid_product'];
		$uid_alias = 'sku';
		if( $uid_alias != 'sku') $uid_alias = 'gtin';
		$data = array();
		foreach($list as $v){
			$copy = $model;
			$copy[$uid_alias] = $v[$uid];
			$copy['stock'] = $v['quantity'];
			$data[] = $copy;
		}
		$file = $this->buildCSV($action,$data);
		return $file;*/
	}


	function getInventoryFile(){
		$database = _obj('Database');
		$list = $database->select('p.ean,p.sku,p.upc,i.quantity',"product as p join product_inventory as i on p.id=i.id_product","i.id_inventory={$this->_params['id_invetory']} AND ((p.parent=0 AND p.type=1) OR p.parent>0) AND p.deleted = 0 AND p.visibility=1");
		
		$model = array(
			'gtin' => '',
			'sku' => '',
			'stock' => '',
		);


		$uid = $this->_params['uid_product'];
		$uid_alias = 'sku';
		if( $uid_alias != 'sku') $uid_alias = 'gtin';
		$data = array();
		foreach($list as $v){
			$copy = $model;
			$copy[$uid_alias] = $v[$uid];
			$copy['stock'] = $v['quantity'];
			$data[] = $copy;
		}
		$file = $this->buildCSV($action,$data);
		return $file;
	}

	function buildCSV($type,$data){
		$date = date('Y-m-d_H-i');
		
		if( !file_exists($this->_output_dir.$type) ){
			if(!mkdir($this->_output_dir.$type, 0777, true)){
				die('Failed to create folders...');
			}
		}
		
		$filename = $this->_output_dir.$type."/".$date.".csv";
		if( file_exists($filename) ){
			unlink($filename);
		}
		$delimiter = ';';
		$f = fopen($filename, 'w'); 
		
		$header = array_keys($data[0]);
		
		fputcsv($f, $header, $delimiter); 
		foreach($data as $row){
			fputcsv($f, array_values($row), $delimiter); 
		}

		return $filename;
		
		

	}


	function getEndpoint(){
		if( $this->sandbox ){
			return $this->sandbox_endpoint;
		}else{
			return $this->live_endpoint;
		}
	}

	
	function call($url,$params=array(),$post=true){
		$apiUrl = $this->getEndpoint().$url;
		
		$ch = curl_init();

		if( $this->sandbox ){
			$authorization = "Authorization: Bearer ".$this->_params['token_sandbox'];
		}else{
			$authorization = "Authorization: Bearer ".$this->_params['token_live'];
		}
		debugga($authorization);
		debugga($apiUrl);
		
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization));
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_URL, $apiUrl);
		curl_setopt($ch, CURLOPT_POST, $post);
		if( okArray($params) ){
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$data = curl_exec($ch);
		curl_close($ch);
		
		$result=json_decode($data,true);
		
		return $result;
	}


	function get($url){
		return $this->call($url,array(),false);
	}
	function post($url,$params=array()){
		return $this->call($url,$params,true);
	}



	
}	

?>