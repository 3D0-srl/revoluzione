<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use ProductFeatures\ProductFeature;
use Catalogo\{TagProduct,Attribute};
class FeatureAdminController extends AdminModuleController{
	public $_auth = 'catalog';

	


	function getList(){
		$database = Marion::getDB();
		$lang = _MARION_LANG_;
		$condizione = "lang = '{$lang}' AND ";
		
		
		$limit = $this->getListOption('per_page');
		
		

		if( $name = _var('name') ){
			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','product_feature as p join product_feature_lang as l on l.id_product_feature=p.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('id,name','product_feature as p join product_feature_lang as l on l.id_product_feature=p.id',$condizione);

		$total_items = $tot[0]['tot'];

		
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);
	}

	function displayList(){
		$this->setMenu('filtri_ricerca_proprieta');

		$this->showMessage();
		
		
		
		
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
				'name' => 'Filtro',
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
				'function' => function($row){
					$url = 'index.php?mod=filtri_ricerca&ctrl=FeatureValueAdmin&action=list&id_feature='.$row['id'];
					return '<a class="btn btn-default btn-sm" href='.$url.'>valori</a>';
				},
				'sortable' => false,
				'searchable' => false,
			),
		);

		

		$this->setTitle('Caratteristiche prodotto');
		$this->setListOption('fields',$fields);
		$this->getList();


		parent::displayList();
	}
	

	function setMedia(){
		$this->loadJS('multiselect');
	}

	function displayContent(){
		$this->setMenu('manage_modules');
		
		if( $this->isSubmitted() ){

			$dati = $this->getFormdata();

			//debugga($dati);exit;
			$array = $this->checkDataform('filtri_ricerca_setting',$dati);
			
			if( $array[0] == 'ok'){
				unset($array[0]);
				if(!isset($array['escludi_attributes']) ) $array['escludi_attributes'] = array();
				if(!isset($array['escludi_tags']) ) $array['escludi_tags'] = array();
				if(!isset($array['escludi_features']) ) $array['escludi_features'] = array();

				foreach($array as $k => $v){
					if( $k == 'filtri' || $k == 'escludi_tags' || $k == 'escludi_attributes' || $k == 'escludi_features'){
						$v = serialize($v);
						
					}
					Marion::setConfig('filtri_ricerca',$k,$v);
				}
				Marion::refresh_config();
				$this->displayMessage('Impostazioni salvate con successo','success');
			}else{
				$this->errors[] = $array[1];
			}
			
		}else{
			$dati = Marion::getConfig('filtri_ricerca');
			if( $dati['filtri'] ) $dati['filtri'] = unserialize($dati['filtri']);
			if( $dati['escludi_tags'] ) $dati['escludi_tags'] = unserialize($dati['escludi_tags']);
		}


		
		$dataform = $this->getDataform('filtri_ricerca_setting',$dati);
		
		$this->setVar('dataform',$dataform);
		$this->output('conf.htm');
	}



	function displayForm(){
		$this->setMenu('filtri_ricerca_proprieta');
		

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		
		
		if( $this->isSubmitted() ){

			$dati = $this->getFormdata();
			
		
			$array = $this->checkDataform('product_feature_edit',$dati);
			//$array = check_form2($formdata,'product_feature_edit');
			if( $array[0] == 'ok' ){

				if(	$action == 'add'){
					$obj = ProductFeature::create();
				}else{
					$obj = ProductFeature::withId($array['id']);
				}
				$obj->set($array);
				$res = $obj->save();
				
				
				if(is_object($res)){
					$this->redirectToList(array('saved'=>1));
				}else{
					$this->errors[] = $res;
				}
				
				
			}else{
				
				$this->errors[] = $array[1];
				
				
			}
			

			
		}else{
			
			$dati = NULL;
			if( $action != 'add'){
				$utente = ProductFeature::withId($id);
				if(is_object($utente) ){
					$dati = $utente->prepareForm2();
					
				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}

		}
		//get_form2($elements,'product_feature_edit',$action,$dati);	
		
		$dataform = $this->getDataform('product_feature_edit',$dati);
		
		$this->setVar('dataform',$dataform);
		$this->output('form_feature.htm');	

		

	}


	function delete(){
		$id = $this->getID();

		$obj = ProductFeature::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
		

		
	}

	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Caratteristica salvata con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Caratteristica eliminata con successo','success');
		}
	}



	function filtri_ricerca(){
		$list = array(
			'attributes' => 'attributi',
			'features' => 'caratteristiche',
			'manufacturers' => 'produttori',
			'prices' => 'prezzi',
			'tags' => 'tag',
			
		);
		return $list;
	}


	function tags(){
		$list =TagProduct::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}

	function attributes(){
		$list =Attribute::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}

	function features(){
		$list =ProductFeature::prepareQuery()->get();
		foreach($list as $v){
			$toreturn[$v->id] = $v->get('name');
		}
		return $toreturn;
	}


}



?>