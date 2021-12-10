<?php
require_once(_MARION_MODULE_DIR_.'amazon/controllers/admin/AmazonAdminController.php');
class OrderController extends AmazonAdminController{
    public $id_store = 0;

    function init($options = array())
    {
        parent::init($options);
        $this->id_store = _var('id_store');
    }



    function displayContent()
    {
        $action = $this->getAction();
        switch($action){
            case 'download':

                $this->download();
                break;
        }
    }

    function displayList(){
        $this->setMenu('amazon_store');
        $this->setTab('orders');
        $store = AmazonStore::withId($this->id_store);
        if( is_object($store) ){
           
            $this->setListOption('title','Scarica ordini ');  
        }else{
            $this->error('Store not present');
        }

        if( $errors = _var('errors') ){
            $errors = json_decode($errors);
            $this->errors = $errors;
        }

		$fields = array(
			
			1 => array(
				'name' => 'ID',
				'field_value' => 'order_id',
				/*'sortable' => true,
				'sort_id' => 'marketplace',
				'searchable' => true,
				'search_name' => 'marketplace',
				'search_value' => _var('marketplace'),
				'search_type' => 'input',*/
            ),
            2 => array(
				'name' => 'Marketplace',
                'field_value' => 'market_flag',
                'function_type' => 'value',
                'function' => 'getImageMarket'
				/*'sortable' => true,
				'sort_id' => 'marketplace',
				'searchable' => true,
				'search_name' => 'marketplace',
				'search_value' => _var('marketplace'),
				'search_type' => 'input',*/
            ),

            
            3 => array(
				'name' => 'buyer',
				'field_value' => 'buyer',
				/*'sortable' => true,
				'sort_id' => 'timestamp',
				'searchable' => true,
				'search_name' => 'timestamp',
				'search_value' => _var('timestamp'),
				'search_type' => 'input',*/
            ),
            4 => array(
				'name' => 'Courier',
				'field_value' => 'shipping_method',
				/*'sortable' => true,
				'sort_id' => 'timestamp',
				'searchable' => true,
				'search_name' => 'timestamp',
				'search_value' => _var('timestamp'),
				'search_type' => 'input',*/
            ),
            5 => array(
				'name' => 'Date',
				'field_value' => 'date',
				/*'sortable' => true,
				'sort_id' => 'timestamp',
				'searchable' => true,
				'search_name' => 'timestamp',
				'search_value' => _var('timestamp'),
				'search_type' => 'input',*/
            ),
            6 => array(
				'name' => 'Total',
				'field_value' => 'total',
				/*'sortable' => true,
				'sort_id' => 'timestamp',
				'searchable' => true,
				'search_name' => 'timestamp',
				'search_value' => _var('timestamp'),
				'search_type' => 'input',*/
            ),
            7 => array(
                'name' => 'status',
                'field_value' => 'status',
				
            ),
			
           

        );

       
        $this->setListOption('fields',$fields);
       
        
        $bulk_actions = $this->getListOption('bulk_actions');
        $bulk_actions['enabled'] = 1;
        $bulk_actions['field_id'] = 'order_id';

        $bulk_actions['actions'] = [
            'download' => array(
                'text' => 'download',
                'icon_type' => 'icon',
                'icon' => 'fa fa-download',
                'custom_fields' => array(
                    'id_store' => _var('id_store'),
                    'old_sincro' => 1
                )
            ),
        ];
        $this->setListOption('bulk_actions',$bulk_actions);

       
        $this->resetToolButtons();
        $this->addToolButton(
            (new UrlButton('syncro'))
            ->setText(_translate('download'))
            ->setIcon('fa fa-download')
            ->setIconType('icon')
            ->setClass('btn btn-principale m-b-10')
            ->setUrl('index.php?mod=amazon&ctrl=Order&action=list&sincro=1&id_store='.$this->id_store)
        );


        $row_actions = $this->getListOption('row_actions');
        $this->setListOption('search',false);
        
        $row_actions['actions'] = array(
            'import' => array(
                'text' => 'import',
                'icon_type' => 'icon',
                'icon' => 'fa fa-download',
                //'enable_function' => 'checkErrors',
                'url_function' => 'downloadOrderUrl'
                
            )
        );
        $this->setListOption('row_actions',$row_actions);
        $this->getList();
        parent::displayList();
    }


    function getList(){
        $list = [];
        
        if(_var('old_sincro') ){
            $dati = $_SESSION['amazon_orders'];
            foreach($dati as $k => $v){
                $list[] = $v['preview'];
            }
        }else{

            if(_var('sincro') ){
                $baseurl = $_SERVER['SERVER_NAME']._MARION_BASE_URL_;
                $dati = json_decode(file_get_contents('http://'.$baseurl.'index.php?mod=amazon&action=import_orders&ctrl=Cron&preview=1&id_store='.$this->id_store),true);
                foreach($dati as $k => $v){
                    $list[] = $v['preview'];
                }
                $_SESSION['amazon_orders'] = $dati;
            }

        }

        /*$database = _obj('Database');
		
		$condizione = "1=1 AND ";
		
		
		$limit = $this->getListOption('per_page');
		
		

		if( $name = _var('marketplace') ){
			$condizione .= "marketplace LIKE '%{$name}%' AND ";
		}

		if( $id = _var('ReportRequestId') ){
			$condizione .= "ReportRequestId LIKE '%{$id}%' AND ";
        }
        
        if($status = _var('ReportProcessingStatus')){
            $condizione .= "ReportProcessingStatus = '{$status}' AND ";
        }
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','amazon_order_log',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}else{
			$condizione .= " ORDER BY timestamp DESC";
        }


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		

		$list = $database->select('*','amazon_order_log',$condizione);

		$total_items = $tot[0]['tot'];*/

		
		$this->setListOption('total_items',count($list));
		$this->setDataList($list);

    }

    function getImageMarket($image){
        return "<img src='../modules/amazon/{$image}'>";
    }

    /*function viewErrors($row){
        $file = _MARION_MODULE_DIR_."amazon/reports/responses/".$row['ReportRequestId'].".csv";
        if( file_exists($file)){
            $url = _MARION_BASE_URL_.'modules/amazon/reports/responses/'.$row['ReportRequestId'].".csv";
            return $url;
        }else{
            return '';
        }
    }*/

    function downloadOrderUrl($row){
        return 'index.php?mod=amazon&ctrl=Order&action=download&id_store='.$this->id_store.'&order_id='.$row['order_id'];
    }

    function checkErrors($row){
        
        return $row['error'];
    }

    function getStatus($row){
        
        $status = "<span class='label label-success'>{$row['success']}</span> <span class='label label-danger'>{$row['error']}</span> ";
        return $status;
    }

    function getReport(){
        debugga('qua');exit;
    }





    function bulk(){
        $ids = $this->getBulkIds();

        $action = $this->getBulkAction();
    
        switch($action){
            case 'download':
                require(_MARION_MODULE_DIR_.'amazon/cpigroup/php-amazon-mws/includes/classes.php');
                $id_store = _var('id_store');
                $amz_store = AmazonStore::withId($id_store);
                $amz_order = AmazonOrders::init($amz_store);
                foreach($ids as $order_id){
                    $data = $_SESSION['amazon_orders'][$order_id];
                    $res = $amz_order->import($order_id,$data['cart'],$data['orders']);
                    if( !(int)$res[0]){
                        $this->errors[] = $res[0];
                    }
                }
                //debugga($ids);exit;
            break;
        }
        $this->errors = array_unique($this->errors);
        parent::bulk();
    }


    function download(){
        require(_MARION_MODULE_DIR_.'amazon/cpigroup/php-amazon-mws/includes/classes.php');
        $order_id = _var('order_id');
        $data = $_SESSION['amazon_orders'][$order_id];
        $id_store = _var('id_store');
        $amz_store = AmazonStore::withId($id_store);
        $amz_order = AmazonOrders::init($amz_store);
        $res = $amz_order->import($order_id,$data['cart'],$data['orders']);
       
        if( !(int)$res[0]){
            $this->redirectToList(array('old_sincro' => 1, 'errors' => json_encode(array($res[0])),'id_store' => $this->id_store)) ; 
        }else{
            $this->redirectToList(array('old_sincro' => 1, 'successes' => json_encode(array($res[0])),'id_store' => $this->id_store)) ; 
        }
    }
}

?>