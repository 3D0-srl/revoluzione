<?php
class PrivaliaListController extends AdminModuleController{
	
	function setMedia(){
		parent::setmedia();
		$this->loadJS('multiselect');
	}
	

	
	function displayForm(){
		$action = $this->getAction();
		$id = $this->getId();
		
		if( $this->isSubmitted()){
			$dati = $this->getFormData();
			$array = $this->checkDataForm('privalia_shop_list',$dati);
			if( $array[0] == 'ok' ){
				unset($array[0]);
				if( $action == 'edit'){
					$obj = PrivaliaList::withid($array['id']);
				}else{
					$obj = PrivaliaList::create();
					
				}
				$obj->setCategories($array['categories']);
				$obj->set($array)->save();
				$this->redirectToList();
			}else{
				$this->errors[] = $array[1];
			}

			
		}else{
			if( $action == 'edit' || $action == 'duplicate' ){
				if( $id ){
					$obj = PrivaliaList::withid($id);
					if( is_object($obj) ){
						$dati = $obj->prepareForm2();
						if( $action == 'duplicate' ){
							unset($dati['id']);
						}
					}
				
				}
			}
		}

		$dataform = $this->getDataForm('privalia_shop_list',$dati);
		$this->setVar('dataform',$dataform);
		$this->output('form_list.htm');
	}


	function getList(){

			$database = _obj('Database');
		
			
			$condizione = "1=1 AND ";
			$limit = $this->getListOption('per_page');
			
			if( $name = _var('name') ){
				$condizione .= "name LIKE '%{$name}%' AND ";
			}

			if( $id_profile = _var('id_profile') ){
				$condizione .= "id_profile = {$id_profile} AND ";
			}
			
			if( $id_channel = _var('id_channel') ){
				$condizione .= "id_channel = {$id_channel} AND ";
			}

			if( $id = _var('id') ){
				$condizione .= "id = {$id} AND ";
			}

			$condizione = preg_replace('/AND $/','',$condizione);
			

			$tot = $database->select('count(*) as tot','privalia_list',$condizione);

			
			

			if( $order = _var('orderBy') ){
				$order_type = _var('orderType');
				$condizione .= " ORDER BY {$order} {$order_type}";
			}


			$condizione .= " LIMIT {$limit}";
			if( $page_id = _var('pageID') ){
				$condizione .= " OFFSET ".(($page_id-1)*$limit);
				
			}

			
			

			$list = $database->select('pl.id,pl.name,id_channel,id_profile,pr.name as name_profile,c.name as name_channel','privalia_list as pl join privalia_profile as pr on pr.id=pl.id_profile left outer join privalia_channel as c on c.id=pl.id_channel',$condizione);
			
			
			$this->setListOption('total_items',$tot[0]['tot']);
			$this->setDataList($list);

		}

		function displayList(){

		

			$this->resetToolButtons();
			$this->addToolButton(
				(new UrlButton('home'))
				->setText('Torna alla Home')
				->setIcon('fa fa-home')
				->setIconType('icon')
				->setClass('btn btn-inf')
				->setUrl('index.php?mod=privalia')
			);
			$this->addToolButton(
				(new UrlButton('add'))
				->setText(_translate('add'))
				->setIcon('fa fa-plus')
				->setIconType('icon')
				->setClass('btn btn-principale')
				->setUrl($this->getUrlAdd())
			);

			$database = _obj('Database');
			
			$channels = array('--select--');
			$profiles = array('--select--');
			
			$select = $database->select('id,name','privalia_profile');
			
			foreach($select as $v){
				$profiles[$v['id']] = $v['name'];
			}
			$selected = unserialize(Marion::getConfig('privalia','channels'));
			$select = $database->select('id,name','privalia_channel');
			
			foreach($select as $v){
				if( in_array($v['id'],$selected)){
					$channels[$v['id']] = $v['name'];
				}
			}
			$fields = array(
				0 => array(
					'name' => 'ID',
					'field_value' => 'id',
					'searchable' => true,
					'sortable' => true,
					'sort_id' => 'id',
					'search_name' => 'id',
					'search_value' => '',
					'search_type' => 'input',
				),
				1 => array(
					'name' => 'Nome',
					'field_value' => 'name',
					'function_type' => 'value',
					'function' => 'strtoupper',
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				),
				2 => array(
					'name' => 'Profilo',
					'field_value' => 'name_profile',
					'function_type' => 'value',
					'function' => 'strtoupper',
					'searchable' => true,
					'search_name' => 'id_profile',
					'search_value' => _var('id_profile'),
					'search_type' => 'select',
					'search_options' => $profiles,
				),
				3 => array(
					'name' => 'Privalia channel',
					'field_value' => 'name_channel',
					'function_type' => 'value',
					'function' => 'strtoupper',
					'searchable' => true,
					'search_name' => 'id_channel',
					'search_value' => _var('id_channel'),
					'search_type' => 'select',
					'search_options' => $channels,
				),
				

			);
			/*$buttons = $this->getListOption('buttons');
			$buttons['right_side']['add']['url'] .= "&market="._var('market');
			$this->setListOption('buttons',$buttons);*/
			
			$this->setListOption('title','Liste di vendita');
			$this->setListOption('fields',$fields);
			$this->getList();
			parent::displayList();
		}



		function delete(){
			$id = $this->getId();
			$obj = PrivaliaList::withId($id);
			if( is_object($obj) ){
				$obj->delete();
			}
			parent::delete();
		}

		function profiles(){
			$database = _obj('Database');
			$sel = $database->select('*','privalia_profile');
			foreach($sel as $v){
				$toreturn[$v['id']] = $v['name'];
			}
			return $toreturn;

		}

		function channels(){
			$database = _obj('Database');
			$selected = unserialize(Marion::getConfig('privalia','channels'));
			
			$sel = $database->select('*','privalia_channel');
			foreach($sel as $v){
				if( in_array($v['id'],$selected)){
					$toreturn[$v['id']] = $v['name'];
				}
			}
			return $toreturn;

		}

		function categories(){
			return Section::getAll(1);
		}


		

}