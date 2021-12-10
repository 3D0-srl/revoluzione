<?php
use Marion\Entities\UserCategory;
use Marion\Core\Marion;
class UserCategoryAdminController extends \Marion\Controllers\AdminController{
	public $_auth = 'user_management';

	

	function displayForm(){
		$this->setMenu('manage_user_categories');
		
		
		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		
		
		if( $this->isSubmitted() ){
			$formdata = $this->getFormdata();
			
			$array = $this->checkDataForm('userCategory',$formdata);
			
			
			if( $array[0] == 'ok'){



			
				if($action == 'add'){
					$obj = UserCategory::create();
				}else{
					$obj = UserCategory::withId($array['id']);
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
			
			$dati = $array;
			
			
			
		}else{
			createIDform();
			$dati = NULL;
			if($action != 'add'){
				$dati = UserCategory::withId($id)->prepareForm2();
				
				if($action == 'duplicate'){
					unset($dati['id']);
					$action = "add";
				}
			}
		}

		

		
		$dataform = $this->getDataForm('userCategory',$dati);
		
		$this->setVar('dataform',$dataform);
		$this->output('user_category/form.htm');	

		

		

	}


	function getList(){
		$db = Marion::getDB();
		$lang = _MARION_LANG_;
		$condizione = "locale = '{$lang}' AND ";
		
		
		$limit = $this->getListContainer()->getPerPage();
		
		if( $name = _var('name') ){

			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		
	
		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $db->select('count(*) as tot','userCategory as u join userCategoryLocale as l on l.usercategory=u.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			
			$condizione .= " ORDER BY {$order} {$order_type}";
			
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $db->select('id,name,locked','userCategory as u join userCategoryLocale as l on l.usercategory=u.id',$condizione);
		
		
		$this->getListContainer()
			->setTotalItems($tot[0]['tot'])
			->setDataList($list);
		
		
	}
	function displayList(){
			$this->setMenu('manage_user_categories');
			$this->setTitle(_translate('User Categories'));
			
			if( _var('saved') ){
				$this->displayMessage('Categoria utente salvata con successo','success');
			}
			if( _var('deleted') ){
				$this->displayMessage('Categoria utente eliminata con successo','success');
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
					'sortable' => true,
					'sort_id' => 'name',
					'searchable' => true,
					'search_name' => 'name',
					'search_value' => _var('name'),
					'search_type' => 'input',
				),
				


			);

			$container = $this->getListContainer()
				->setFieldsFromArray($fields)
				->enableBulkActions(false)
				->addEditActionRowButton()
				->addCopyActionRowButton()
				->addDeleteActionRowButton();
			
			$container->getActionRowButton('delete')
				->setEnableFunction(function($row){
				return !$row['locked'];
			});
			

			
			$container->build();
			
			$this->getList();

			parent::displayList();
	}


	function bulk(){
		
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();
		
		switch($action){
			case 'delete':
				foreach($ids as $id){
					$obj = UserCategory::withId($id);
					if( is_object($obj) ){
						if( $obj->locked ){
							$this->errors[] = "La categoria <b>{$obj->get('name')}</b> non può essere eliminata";
						}else{
						
							$obj->delete();
						}

					}
				}
				break;
		}

		parent::bulk();
	}



	function delete(){
		$id = $this->getID();

		$obj = UserCategory::withId($id);
		if( is_object($obj) ){
			if( $obj->locked ){
				$this->errors[] = "La categoria <b>{$obj->get('name')}</b> non può essere eliminata";
			}else{
			
				$obj->delete();
			}

		}
		parent::delete();
		
		
		

		
	}

}



?>