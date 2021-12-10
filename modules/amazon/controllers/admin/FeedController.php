<?php
require_once(_MARION_MODULE_DIR_.'amazon/controllers/admin/AmazonAdminController.php');
class FeedController extends AmazonAdminController{
    public $id_store = 0;

    function init($options = array())
    {
        parent::init($options);
        $this->id_store = _var('id_store');
    }


    function setMedia(){
        parent::setMedia();
        $this->registerJS('../modules/amazon/javascript/feeds.js');
    }




    function displayList(){
        $this->setMenu('amazon_store');
        $store = AmazonStore::withId($this->id_store);
        if( is_object($store) ){
           
            $this->setTitle('title');  
        }else{
            $this->error('Store not present');
        }
        $marketplaces = array('----SELECT---');
        foreach($store->marketplace as $v){
            $marketplaces[$v] = $v;
        }

        $reportTypes = array(
            '' => '----SELECT---',
            '_POST_INVENTORY_AVAILABILITY_DATA_' => 'inventario',
            '_POST_PRODUCT_DATA_' => 'prodotti',
            '_POST_PRODUCT_PRICING_DATA_' => 'prezzi',


        );

       
		$fields = array(
			1 => array(
				'name' => 'Feed ID',
				'field_value' => 'FeedSubmissionId',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'FeedSubmissionId',
				'search_name' => 'FeedSubmissionId',
				'search_value' => _var('FeedSubmissionId'),
				'search_type' => 'input',
			),
			2 => array(
				'name' => 'Marketplace',
                'field_value' => 'marketplace',
                'function_type' => 'value',
                'function' => 'getImageMarket',
				'sortable' => true,
				'sort_id' => 'marketplace',
				'searchable' => true,
				'search_name' => 'marketplace',
				'search_value' => _var('marketplace'),
                'search_type' => 'select',
                'search_options' => $marketplaces,
            ),
            3 => array(
				'name' => 'Type',
                'field_value' => 'FeedType',
                'function_type' => 'value',
                'function' => 'getFeedType',
				'sortable' => true,
				'sort_id' => 'FeedType',
				'searchable' => true,
				'search_name' => 'FeedType',
				'search_value' => _var('FeedType'),
                'search_type' => 'select',
                'search_options' => $reportTypes
            ),
            4 => array(
				'name' => 'Date',
				'field_value' => 'timestamp',
				'sortable' => true,
				'sort_id' => 'timestamp',
				'searchable' => true,
				'search_name' => 'timestamp',
				'search_value' => _var('timestamp'),
				'search_type' => 'input',
            ),
			5 => array(
                'name' => 'Status',
                'function_type' => 'row',
                'function' => 'getStatus',
				'sortable' => true,
				'sort_id' => 'FeedProcessingStatus',
				'searchable' => true,
                'search_name' => 'FeedProcessingStatus',
                'search_value' => _var('FeedProcessingStatus'),
				'search_options' => array(
                    '' => '--SELECT--',
                    '_SUBMITTED_' => 'SOTTOMESSO',
                    '_PROCESSING_' => 'IN ELABORAZIONE',
                    '_DONE_' => 'FINITO',
                ),
				'search_type' => 'select',
            ),
            6 => array(
                'name' => 'Info',
                'function_type' => 'row',
                'function' => 'getInfoRow'
            ),
           

        );

       
        $this->setListOption('fields',$fields);
        $this->setTab('feeds');
        
        
        $bulk_actions = $this->getListOption('bulk_actions');
        $bulk_actions['enabled'] = 0;
        $this->setListOption('bulk_actions',$bulk_actions);

        
        $this->resetToolButtons();
        $this->addToolButton(
            (new UrlButton('update'))
            ->setText(_translate('update'))
            ->setIcon('fa fa-refresh')
            ->setIconType('icon')
            ->setClass('btn btn-principale m-b-10')
            ->setUrl("javascript:refresh_feeds(".$this->id_store.")")
        );


        $row_actions = $this->getListOption('row_actions');
        $row_actions['actions'] = array(
            'download' => array(
                'text' => 'vedi',
                'icon_type' => 'icon',
                'icon' => 'fa fa-eye',
                'enable_function' => 'checkDownload',
                'url_function' => 'feedResults'
                
            )
        );
        $this->setListOption('row_actions',$row_actions);
        $this->getList();
        parent::displayList();
    }


    function getList(){
        $database = _obj('Database');
		
        $condizione = "id_store= {$this->id_store} AND ";
		
		
		$limit = $this->getListOption('per_page');
		
		

		if( $name = _var('marketplace') ){
			$condizione .= "marketplace LIKE '%{$name}%' AND ";
		}

		if( $id = _var('FeedSubmissionId') ){
			$condizione .= "FeedSubmissionId LIKE '%{$id}%' AND ";
        }
        
        if($status = _var('FeedProcessingStatus')){
            $condizione .= "FeedProcessingStatus = '{$status}' AND ";
        }
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','amazon_feed_sync',$condizione);

		
		

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

		
		

		$list = $database->select('*','amazon_feed_sync',$condizione);
        //debugga($list);exit;
		$total_items = $tot[0]['tot'];

		
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);

    }

    function getImageMarket($market){
        if(_var('export')){
            return $market;
        }
        $image = $this->getBaseUrl()."modules/amazon/".AmazonTool::getMarketplaceImage($market);
        return "<img style='width:30px;'src='{$image}'>";
    }


    function getFeedType($feedType){
        switch($feedType){
            case '_POST_INVENTORY_AVAILABILITY_DATA_':
                $feedType = 'Inventario';
            break;
            case '_POST_PRODUCT_PRICING_DATA_':
                $feedType = 'Prezzi';
            break;
            case '_POST_PRODUCT_DATA_':
                $feedType = 'Prodotti';

        }
        return $feedType;
    }

    function feedResults($row){
        
        $url = 'index.php?mod=amazon&ctrl=FeedResults&action=list&id_feed='.$row['FeedSubmissionId']."&id_store="._var('id_store');
        return $url;
    
    }

    function checkDownload($row){
        //$file = _MARION_MODULE_DIR_."amazon/reports/responses/".$row['ReportRequestId'].".csv";
        return ($row['FeedProcessingStatus'] == '_DONE_');
    }

    function getStatus($row){
        switch($row['FeedProcessingStatus']){
            case '_DONE_':
                $status = 'FINITO';
                $class = "success";
                break;
            case '_SUBMITTED_':
                $status = 'SOTTOMESSO';
                $class = "info";
                break;
            case '_IN_PROGRESS_':
                $status = 'IN ELABORAZIONE';
                $class = "warning";
                break;
            default:
                $status = $row['FeedProcessingStatus'];
                break;
        }
        
        if( _var('export') ){
            return $status;
        }else{
            return "<span class='label label-{$class}'>".$status."</span>";
        }
    }

    

    function getInfoRow($row){
       
        return "<span class='label label-success'>{$row['successes']}</span>&nbsp;<span class='label label-warning'>{$row['warnings']}</span>&nbsp;<span class='label label-danger'>{$row['errors']}</span>";
    }
}

?>