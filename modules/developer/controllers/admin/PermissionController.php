<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Marion\Entities\Permission;
class PermissionController extends AdminModuleController{
	public $_auth = 'superadmin';

    
    function getList(){

        $database = Marion::getDB();
		$lang = _MARION_LANG_;
		$condizione = "locale='{$lang}' AND ";

		
		
		$limit = $this->getListOption('per_page');
		
		if( $nome = _var('name') ){
			$condizione .= "name LIKE '%{$nome}%' AND ";
		}
		
		
		

		if( $id = _var('id') ){
            $condizione .= "codice = {$id} AND ";
            
        }
        
        if( $label = _var('label') ){
            $condizione .= "label = '{$label}' AND ";
            
		}


	

	
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','permission as p join permissionLocale as l on l.permission=p.id',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}

		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		//$list = $database->select('id,name,sku,visibility,section,type,images','product as p left outer join productLocale as l on l.product=p.id',$condizione);
		
		$list = $database->select("*",'permission as p join permissionLocale as l on l.permission=p.id',"{$condizione}");
		
		$total_items = $tot[0]['tot'];

		
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);

        
    }


    function displayList(){
        $this->setMenu('developer_permissions');


        if( _var('deleted') ){
            $this->displayMessage('Voce eliminata con successo');
        }

        if( _var('saved') ){
            $this->displayMessage('Dati salvati con successo');
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
				'name' => 'Label',
				'field_value' => 'label',
				'sortable' => true,
				'sort_id' => 'label',
				'searchable' => true,
				'search_name' => 'label',
				'search_value' => _var('label'),
				'search_type' => 'input',
            ),
            2 => array(
				'name' => 'Name',
				'field_value' => 'name',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
            ),
            3 => array(
				'name' => 'Description',
				'field_value' => 'description',
			
			)

        );
        $this->setListOption('fields',$fields);
        $this->setTitle('Permissions');
        
        $this->getList();
        
    

        parent::displayList();
        
    }



    function displayForm(){
        $this->setMenu('developer_permissions');

        $id = $this->getID();
        $action = $this->getAction();
        if( $this->isSubmitted()){
            $dati = $this->getFormdata();
            //debugga($dati);exit;
            $array = $this->checkDataForm('permission',$dati);
            if( $array[0] == 'ok'){
                if($action == 'edit'){
                    $obj = Permission::withId($id);
                }else{
                    $obj = Permission::create();
                }
                $array['scope'] = 'admin';
                $obj->set($array);
                $obj->save();
               
                $this->redirectToList(array('saved'=>1));
            }else{
                $this->errors[] = $array[1];
            }


        }else{
            $dati = null;
            if($action == 'edit'){
                $obj = Permission::withId($id);
                if(is_object($obj)){
                    $dati = $obj->prepareForm2();
                }
                
            }
           
        }

        $dataform = $this->getDataForm('permission',$dati);
        $this->setVar('dataform',$dataform);
        $this->output('form_permission.htm');
        



        
    }


    function delete(){
        $id = $this->getID();
        $obj = Permission::withId($id);

		
        if(is_object($obj)){
            $obj->delete();
			
        }
        $this->redirectToList((array('deleted' => 1)));
        
    }

    

    

    
}