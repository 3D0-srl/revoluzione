<?php
require_once(_MARION_MODULE_DIR_.'amazon/controllers/admin/AmazonAdminController.php');
class FeedResultsController extends AmazonAdminController{



    function displayList(){
        $this->setMenu('amazon_store');
        $this->setTab('feeds');
        $id_feed = _var('id_feed');
        
        $this->setListOption('title','Feed '.$id_feed);

        $this->setTab('feeds');
        $this->setVar('back_url','index.php?mod=amazon&ctrl=Feed&action=list&id_store='.$this->store->id);
    
        $fields = array(
			1 => array(
				'name' => 'Message ID',
				'field_value' => 'message_id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'message_id',
				'search_name' => 'message_id',
				'search_value' => _var('message_id'),
				'search_type' => 'input',
			),
			2 => array(
				'name' => 'Sku',
                'field_value' => 'sku',
				'sortable' => true,
				'sort_id' => 'sku',
				'searchable' => true,
				'search_name' => 'sku',
				'search_value' => _var('sku'),
                'search_type' => 'input',
            ),
            3 => array(
				'name' => 'Message',
                'field_value' => 'message',
				'sortable' => true,
				'sort_id' => 'message',
				'searchable' => true,
				'search_name' => 'message',
				'search_value' => _var('message'),
                'search_type' => 'input',
            ),
            4 => array(
				'name' => 'Result',
				'field_value' => 'result',
				'sortable' => true,
				'sort_id' => 'result',
				'searchable' => true,
				'search_name' => 'result',
				'search_value' => _var('result'),
				'search_type' => 'input',
            ),
			5 => array(
                'name' => 'Error code',
                'field_value' => 'error_code',
				'sortable' => true,
				'sort_id' => 'error_code',
				'searchable' => true,
                'search_name' => 'error_code',
                'search_value' => _var('error_code'),
				'search_type' => 'input',
            ),
            
           

        );

        $this->resetToolButtons();

        $row_actions = $this->getListOption('row_actions');
        $row_actions['enabled'] = 1;
        $row_actions['actions'] = [];
        $this->setListOption('row_actions',$row_actions);

        $bulk_actions = $this->getListOption('bulk_actions');
        $bulk_actions['enabled'] = 0;
        $this->setListOption('bulk_actions',$bulk_actions);
       
        $this->setListOption('fields',$fields);

        $this->getList($id_feed);

        
        parent::displayList();
    }


    function getList($id_feed){
        $res = json_decode(file_get_contents(_MARION_MODULE_DIR_.'amazon/responses/'.$id_feed.".json"),true);
       
        $messages = $res['messages'];

        if($message_id = _var('message_id')){
            
            foreach($messages as $k =>$v){
                if( !preg_match("/".$message_id."/",$v['message_id'])){
                    
                    unset($messages[$k]);
                }
            }
        }
        if($sku = _var('sku')){
            
            foreach($messages as $k =>$v){
                if( !preg_match("/".$sku."/",$v['sku'])){
                    
                    unset($messages[$k]);
                }
            }
        }
        if($error_code = _var('error_code')){
            
            foreach($messages as $k =>$v){
                if( !preg_match("/".$error_code."/",$v['error_code'])){
                    
                    unset($messages[$k]);
                }
            }
        }

        if($message = _var('message')){
            
            foreach($messages as $k =>$v){
                if( !preg_match("/".$message."/",$v['message'])){
                    
                    unset($messages[$k]);
                }
            }
        }
        if($result = _var('result')){
            
            foreach($messages as $k =>$v){
                if( !preg_match("/".$result."/",$v['result'])){
                    
                    unset($messages[$k]);
                }
            }
        }
       
        $this->setListOption('per_page',count($messages));
        $this->setListOption('total_items',count($messages));
		$this->setDataList($messages);

    }

   
}
?>