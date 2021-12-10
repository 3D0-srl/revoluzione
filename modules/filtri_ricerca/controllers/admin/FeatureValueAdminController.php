<?php
use Marion\Controllers\AdminModuleController;
use ProductFeatures\{ProductFeature,ProductFeatureValue};
use Marion\Core\Marion;
use Marion\Controllers\Elements\UrlButton;
class FeatureValueAdminController extends AdminModuleController{
	public $_auth = 'catalog';



	function getList(){
		$database = Marion::getDB();
		$lang = _MARION_LANG_;
		$id_feature = _var('id_feature');
		
		$condizione = "id_product_feature= {$id_feature} AND lang = '{$lang}' AND ";
		
		
		
		$limit = $this->getListOption('per_page');
		
		

		if( $value = _var('value') ){
			$condizione .= "value LIKE '%{$value}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','product_feature_value as p join product_feature_value_lang as l on l.id_product_feature_value=p.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('id,value','product_feature_value as p join product_feature_value_lang as l on l.id_product_feature_value=p.id',$condizione);
		
		$total_items = $tot[0]['tot'];

		
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);
	}
	
	function displayList(){
		$this->setMenu('filtri_ricerca_proprieta');
		$this->showMessage();
		$id_feature = _var('id_feature');


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
				'name' => 'Valore',
				'field_value' => 'value',
				'sortable' => true,
				'sort_id' => 'value',
				'searchable' => true,
				'search_name' => 'value',
				'search_value' => _var('value'),
				'search_type' => 'input',
			),
		);

		
		$productFeature = ProductFeature::withId($id_feature);
		$this->setTitle('Valori di '.$productFeature->get('name'));
		$this->setListOption('fields',$fields);
		$this->getList();


		$add_button = $this->getToolButton('add');
		
		$add_button->setUrl($this->getUrlAdd()."&id_feature=".$id_feature);

		parent::displayList();
	}

	function displayForm(){
		$this->setMenu('filtri_ricerca_proprieta');
		

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		$id_feature = _var('id_feature');
		
		if( $this->isSubmitted() ){

			$dati = $this->getFormdata();
			
		
			$array = $this->checkDataform('product_feature_value_edit',$dati);
			
		

			
			if( $array[0] == 'ok' ){

				if(	$action == 'add'){
					$obj = ProductFeatureValue::create();
				}else{
					$obj = ProductFeatureValue::withId($array['id']);
				}
				$obj->set($array);
				$res = $obj->save();
				
				
				if(is_object($res)){
					$this->redirectToList(array('saved'=>1,'id_feature'=>$res->id_product_feature));
				}else{
					$this->errors[] = $res;
				}
				
				
			}else{
				
				$this->errors[] = $array[1];
				
				
			}
			

			
		}else{
			
			$dati = NULL;
			if( $action != 'add'){
				$utente = ProductFeatureValue::withId($id);
				if(is_object($utente) ){
					$dati = $utente->prepareForm2();
					
				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}
			if( $id_feature ){
				$dati['id_product_feature'] = $id_feature;
			}

		}
		$dataform = $this->getDataform('product_feature_value_edit',$dati);
		
		$this->setVar('dataform',$dataform);
		
		$this->output('form_feature_value.htm');	

		

	}


	function delete(){
		$id = $this->getID();

		$obj = ProductFeatureValue::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1,'id_feature'=>$obj->id_product_feature));
		

		
	}

	function showMessage(){
		if( _var('saved') ){
			$this->displayMessage('Valore salvato con successo','success');
		}
		if( _var('deleted') ){
			$this->displayMessage('Valore eliminato con successo','success');
		}
	}


}



?>