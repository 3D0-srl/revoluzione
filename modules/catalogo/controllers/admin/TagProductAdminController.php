<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Catalogo\TagProduct;
class TagProductAdminController extends AdminModuleController{
	public $_auth = 'catalog';

	function displayForm(){
		$this->setMenu('tagProduct');
		

		createIDform();
		$id =  $this->getID();
		$action =  $this->getAction();
		
		
		if( $this->isSubmitted() ){

			$formdata = $this->getFormdata();
			
		

			
			$array = $this->checkDataForm('tagProduct',$formdata);
			if( $array[0] == 'ok' ){

				if(	$action == 'add'){
					$obj = TagProduct::create();
				}else{
					$obj = TagProduct::withId($array['id']);
				}
				$obj->set($array);
				$res = $obj->save();
				
				
				if(is_object($res)){
					$this->redirectToList(array('saved'=>1));
				}else{
					$this->errors[] = $res;
				}
				$dati = $array;
				
			}else{
				$dati = $array;
				$this->errors[] = $array[1];
				
				
			}
			

			
		}else{
			
			$dati = NULL;
			if( $action != 'add'){
				$utente = TagProduct::withId($id);
				if(is_object($utente) ){
					$dati = $utente->prepareForm2();
					
				}
				if( $action == 'duplicate'){
					unset($dati['id']);
					$action = 'add';
				}
			}

		}
		
		$dataform = $this->getDataForm('tagProduct',$dati);
			
		$this->setVar('dataform',$dataform);
		$this->output('catalogo/tag/form.htm');

		

	}


	function getList(){
		$database = Marion::getDB();;
		
		$condizione = "locale = '{$GLOBALS['activelocale']}' AND ";
		
		
		$limit = $this->getListOption('per_page');
		
		if( $name = _var('name') ){
			$condizione .= "name LIKE '%{$name}%' AND ";
		}

		if( $label = _var('label') ){
			$condizione .= "label LIKE '%{$label}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','tagProduct as m join tagProductLocale as l on l.id_tagProduct=m.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('m.id,m.label,l.name','tagProduct as m join tagProductLocale as l on l.id_tagProduct=m.id',$condizione);
		
		
		$this->setListOption('total_items',$tot[0]['tot']);
		$this->setDataList($list);
		
	}

	function displayList(){
			$this->setMenu('tagProduct');

			if( _var('saved') ){
				$this->displayMessage(_translate('tag_product_saved'));
			}
			if( _var('deleted') ){
				$this->displayMessage(_translate('tag_product_deleted'),'success');
			}
			/*$database = Marion::getDB();;
			
			
			
			
			$limit = $this->getLimitList();
			$offset = $this->getOffsetList();


			$user = Marion::getUser();
			$query = TagProduct::prepareQuery()
				->offset($offset)
				->limit($limit);
				
			
			
			
			$list = $query->get();
			
			
			
			$tot = $tot[0]['cont'];
			
			
			$pager_links = $this->getPagerList($tot);

			
			$this->setVar('list',$list);
			$this->setVar('links',$pager_links);
			
			$this->output('catalogo/tag/list.htm');*/

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
					'name' => 'Tag',
					'field_value' => 'label',
					'sortable' => true,
					'sort_id' => 'label',
					'searchable' => true,
					'search_name' => 'label',
					'search_value' => _var('label'),
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

			);

			
			$this->setTitle('Tag Prodotto');
			$this->setListOption('fields',$fields);
			$this->getList();
			parent::displayList();
	}


	function delete(){
		$id = $this->getID();

		$obj = TagProduct::withId($id);
		if( is_object($obj) ){
			$obj->delete();
		}
		$this->redirectToList(array('deleted'=>1));
		

		
	}

}



?>