<?php
class StoreController extends AdminModuleController{
	


	function setMedia(){
		parent::setMedia();
		$this->registerCSS('../modules/amazon/js/jstree/themes/default/style.min.css');
		$this->registerCSS('../modules/amazon/css/default.css');
		$this->registerJS('../modules/amazon/js/jstree/jstree.min.js','end');
		$this->registerJS('../modules/amazon/javascript/store.js','end');
	
	}


	function getList(){
		$database = _obj('Database');
		
		$condizione = "1=1 AND ";
		
		
		$limit = $this->getListOption('per_page');
		
		

		if( $name = _var('name') ){
			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','amazon_store',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('id,name','amazon_store',$condizione);

		$total_items = $tot[0]['tot'];

		
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);
	}

	function displayList(){

		
		
			//debugga($this);exit;
		$this->setMenu('amazon_store');
		$this->showMessage();
		
		
		/*
		
		$fields = array(
			1 => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			2 => array(
				'name' => 'Nome',
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),
			3 => array(
				'name' => '',
				'function_type' => 'row',
				'function' => 'getReportLink'
				
			),
			4 => array(
				'name' => '',
				'function_type' => 'row',
				'function' => 'getFeedLink'
				
			),
			5 => array(
				'name' => '',
				'function_type' => 'row',
				'function' => 'getOrdersLink'
				
			),
			6 => array(
				'name' => '',
				'function_type' => 'row',
				'function' => 'getMarketplacesLink'
				
			),
		);


		$this->getListContainer()
			->setTitle('Amazon Stores')
			->addCopyActionRowButton()
			->addDeleteActionRowButton()
			->addEditActionRowButton()
			->addActionRowButtons(
				[
					ListActionRowButton::create('save')
						->setText('salva')
						->setIcon('fa fa-list')
						->setUrlFunction(function($data){
							return 'ciao';
						}),
					ListActionRowButton::create('savee')
					->setText('salvae')
					->setUrlFunction(function($data){
						return 'ciao';
					}),

				]
			)
			->enablebulkActions(false)
			->enableExport(true)
			->setExportTypes(array('pdf'))
			->enableSearch(false)
			->enableSort(false)
			->setFieldsFromArray($fields)
			->setTotalItems(100)
			->build();
		
		

		$this->setListOption('title','Amazon Stores');
		$this->setListOption('fields',$fields);
		$this->getList();
		*/

		//parent::displayList();

		$database = _obj('Database');
		$list = $database->select('id,name','amazon_store');
		$this->setVar('list',$list);
		$this->output('store/list.htm');
	}

	function displayForm(){
		$this->setMenu('amazon_store');
		$action = $this->getAction();
		if( $this->isSubmitted()){
			$dati = $this->getFormData();
			
			

			$array = $this->checkDataForm('amazon_store',$dati);

			if( $array[0] == 'ok'){
				foreach($dati['carrier'] as $v){
					$amazon_carr[] = $v['id_amazon'];
				}
				if( count($amazon_carr) != count(array_unique($amazon_carr)) ){
					$array[0] = 'nak';
					$array[1] = 'Mappatura Corrieri in entrata: qualche corriere di amazon è stato inserito più volte.';
				}
				
			}
			
			if( $array[0] == 'ok'){
				
				if( $action == 'add'){
					$obj = AmazonStore::create();
				}else{
					$obj = AmazonStore::withId($array['id']);
				}
				//debugga($dati);exit;
				$array['mapping_profile'] = serialize($dati['profile']);
				
				$obj->set($array)->save();
				
				$database = _obj('Database'); 
				$database->delete('amazon_carrier',"id_store={$obj->id}");
				foreach($dati['carrier'] as $v){
					$v['id_store'] = $obj->id;
					$database->insert('amazon_carrier',$v);
					
				}
				$database->delete('amazon_carrier_exit',"id_store={$obj->id}");
				foreach($dati['carrier_exit'] as $v){
					$v['id_store'] = $obj->id;
					$database->insert('amazon_carrier_exit',$v);
					
					
				}
				$this->redirectToList(array('saved'=>1));

			}else{
				$map_corrieri = $dati['carrier'];
				$map_corrieri_exit = $dati['carrier_exit'];
				$cont_map_corrieri = count($map_corrieri);
				$cont_map_corrieri_exit = count($map_corrieri_exit);
				$this->setVar('map_corrieri',$map_corrieri);
				$this->setVar('map_corrieri_exit',$map_corrieri_exit);
				$this->setVar('cont_map_corrieri',$cont_map_corrieri);
				$this->setVar('cont_map_corrieri_exit',$cont_map_corrieri_exit);
				$this->errors[] = $array[1];
			}
		}else{
			if( $action == 'edit'){
				$id = $this->getID();
				$store = AmazonStore::withId($id);
				$dati = $store->prepareForm2();
				$categorie_selezionate = $store->getCategories();
	
				
				$map_corrieri = $store->getCarriers();

				
				//debugga($template->map_corrieri);exit;
				$map_corrieri_exit = $store->getCarriersExit();

				
				$cont_map_corrieri = count($map_corrieri);
				$cont_map_corrieri_exit = count($map_corrieri_exit);

				$this->setVar('map_corrieri',$map_corrieri);
				$this->setVar('map_corrieri_exit',$map_corrieri_exit);
				$this->setVar('cont_map_corrieri',$cont_map_corrieri);
				$this->setVar('cont_map_corrieri_exit',$cont_map_corrieri_exit);
			

				
				$this->setVar('categorie_selezionate',$categorie_selezionate);
				
			}


		}

		$dataform = $this->getDataForm('amazon_store',$dati);
		
		$this->setVar('selected_markets',$dati['marketplace']);

		$this->marketplaces();
		$this->profiles();
		$this->couriers();
		$categories = Catalog::getSectionTree(1);
		$this->setVar('categories',$categories);
		$this->setVar('dataform',$dataform);
		$this->output('store/form.htm');
	}
	function showMessage(){

	}


	function mappingStatusPaid(){
		
		$status_avaiables = CartStatus::prepareQuery()
			->where('active',1)
			->where('visibility',1)
			->orderBy('orderView')
			->where('paid',1)
			->get();
		foreach($status_avaiables as $v){
			$toreturn[$v->label] = $v->get('name');
		}
		

		return $toreturn;
	}

	function mappingStatusSent(){
		
		$status_avaiables = CartStatus::prepareQuery()
			->where('active',1)
			->where('visibility',1)
			->orderBy('orderView')
			->where('sent',1)
			->get();
		foreach($status_avaiables as $v){
			$toreturn[$v->label] = $v->get('name');
		}
		

		return $toreturn;
	}


	function marketplaces(){
		$list = array(
			'Europa' => array(
				'UK' => array(
					'name' => 'Regno Unito',
					'image' => '../modules/amazon/images/gb.png',
				),
				'Spain' => array(
					'name' => 'Spagna',
					'image' => '../modules/amazon/images/es.png',
				),
				'France' => array(
					'name' => 'Francia',
					'image' => '../modules/amazon/images/fr.png',
				),
				'Italy' => array(
					'name' => 'Italia',
					'image' => '../modules/amazon/images/it.png',
				),
				'Germany' => array(
					'name' => 'Germania',
					'image' => '../modules/amazon/images/de.png',
				),
				'Netherlands' => array(
					'name' => 'Paesi Bassi',
					'image' => '../modules/amazon/images/nl.png',
				),
				'Sweden' => array(
					'name' => 'Svezia',
					'image' => '../modules/amazon/images/se.png',
				),
			),
			'Nord America' => array(
				'US' => array(
					'name' => 'Stati Uniti',
					'image' => '../modules/amazon/images/us.png',
				),
				'Canada' => array(
					'name' => 'Canada',
					'image' => '../modules/amazon/images/mx.png',
				),
				'Mexico' => array(
					'name' => 'Messico',
					'image' => '../modules/amazon/images/ca.png',
				)
			),
			'Altri Marketplace' => array(
				'india' => array(
					'name' => 'India',
					'image' => '../modules/amazon/images/in.png',
				),
				'japan' => array(
					'name' => 'Giappone',
					'image' => '../modules/amazon/images/jp.png',
				),
				'China' => array(
					'name' => 'Cina',
					'image' => '../modules/amazon/images/cn.png',
				)
			),
		);
		$this->setVar('marketplaces',$list);
	}


	function getMarketplaces(){
		$values = array_keys(AmazonTool::$markets);
		$options = array();
		foreach($values as $v){
			$options[$v] = $v;
		}
		return $options;
	}


	function profiles(){
		$list = array(
			1 => 'Profilo1',
			2 => 'Profilo2'
		);
		$this->setVar('profiles',$list);
	}



	function couriers(){
		
		$corrieri = AmazonTool::getCarriers();
		$corrieri_exit = AmazonTool::getCarriersExit();
		$markets = AmazonTool::getMarkets();
		$corrieri_marion = AmazonTool::getMarionCarriers();
		
		$this->setVar('corrieri_amazon',$corrieri); 
		$this->setVar('corrieri_amazon_exit',$corrieri_exit); 
		$this->setVar('markets',$markets); 
		$this->setVar('corrieri_marion',$corrieri_marion); 
	}



	function delete(){
		$id = $this->getId();
		$obj = AmazonStore::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		parent::delete();
	}



	function getReportLink($row){
		
		return "<a class='btn btn-sm btn-default' href='index.php?mod=amazon&ctrl=Report&action=list&id_store={$row['id']}'><i class='fa fa-list'></i> reports</a>";
	}

	function getFeedLink($row){
		
		return "<a class='btn btn-sm btn-default' href='index.php?mod=amazon&ctrl=Feed&action=list&id_store={$row['id']}'><i class='fa fa-list'></i> feeds</a>";
	}

	function getOrdersLink($row){
		
		return "<a class='btn btn-sm btn-default' href='index.php?mod=amazon&ctrl=Order&action=list&id_store={$row['id']}'><i class='glyph-icon flaticon-shopping80'></i> ordini log</a>";
	}


	function getMarketplacesLink($row){
		return "<a class='btn btn-sm btn-default' href='index.php?mod=amazon&ctrl=Action&id_store={$row['id']}'><i class='fa fa-flash'></i> marketplaces</a>";
	}


	

	
}

?>