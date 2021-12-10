<?php
class CountryController extends \Marion\Controllers\AdminController{
    public $_auth = 'cms';
    
    function getList(){
        $db = Marion::getDB();
        $condizione = "locale = '"._MARION_LANG_."' AND ";
        $limit = $this->getListOption('per_page');
        
        if( $name = _var('name') ){

			$condizione .= "name LIKE '%{$name}%' AND ";
        }
        
        if( $code = _var('id') ){

			$condizione .= "country LIKE '%{$code}%' AND ";
		}


        $condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $db->select('count(*) as tot','countryLocale',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			if( $order_type == 'name' ){
				$condizione .= " ORDER BY name {$order_type}, surname {$order_type}";
			}else{
				$condizione .= " ORDER BY {$order} {$order_type}";
			}
		}
        

		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
        }
        $list = $db->select('country,name','countryLocale',$condizione);
        
		
		
		$this->setListOption('total_items',$tot[0]['tot']);
		$this->setDataList($list);
    }

    function displayList(){
        

        $fields = array(
            0 => array(
                'name' => 'CODE',
                'field_value' => 'country',
                'searchable' => true,
                'sortable' => true,
                'sort_id' => 'id',
                'search_name' => 'id',
                'search_value' => '',
                'search_type' => 'input',
            ),
            1 => array(
                'name' => 'Name',
                'field_value' => 'name',
                'sortable' => true,
                'sort_id' => 'name',
                'searchable' => true,
                'search_name' => 'name',
                'search_value' => _var('name'),
                'search_type' => 'input',
            ),


        );

        $this->setTitle('Countries');
        $this->setListOption('fields',$fields);
        $this->getList();

        $this->enableBulkActions(false);
        //$this->enableExport(false);
        
        parent::displayList();
        
    
    }



    function displayForm(){

    
    }
}