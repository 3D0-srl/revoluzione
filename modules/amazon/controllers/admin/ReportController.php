<?php
require_once(_MARION_MODULE_DIR_.'amazon/controllers/admin/AmazonAdminController.php');
class ReportController extends AmazonAdminController{
    public $id_store = 0;

    function init($options = array())
    {
        parent::init($options);
        $this->id_store = _var('id_store');
    }



    function setMedia(){
        parent::setMedia();
        $this->registerJS('../modules/amazon/javascript/reports.js');
    }


    function displayContent()
    {
        $action = $this->getAction();
        switch($action){
            case 'get':

                $this->getReport();
                break;
        }
    }

    function displayList(){
        $this->setMenu('amazon_store');
        $this->setTab('reports');

       
        $store = AmazonStore::withId($this->id_store);
        if( is_object($store) ){
           
            $this->setListOption('title','Reports');  
        }else{
            $this->error('Store not present');
        }
        $marketplaces = array('----SELECT---');
        foreach($store->marketplace as $v){
            $marketplaces[$v] = $v;
        }

        $reportTypes = array(
            '' => '----SELECT---',
            '_GET_MERCHANT_LISTINGS_DATA_' => 'Active Listings Report',
            '_GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_' => 'Unshipped Orders Report',

        );

       
		$fields = array(
			1 => array(
				'name' => 'Request ID',
				'field_value' => 'ReportRequestId',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'ReportRequestId',
				'search_name' => 'ReportRequestId',
				'search_value' => _var('ReportRequestId'),
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
                'field_value' => 'ReportType',
                'function_type' => 'value',
                'function' => 'getReportType',
				'sortable' => true,
				'sort_id' => 'ReportType',
				'searchable' => true,
				'search_name' => 'ReportType',
				'search_value' => _var('ReportType'),
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
				'sort_id' => 'ReportProcessingStatus',
				'searchable' => true,
                'search_name' => 'ReportProcessingStatus',
                'search_value' => _var('ReportProcessingStatus'),
				'search_options' => array(
                    '' => '--SELECT--',
                    '_SUBMITTED_' => 'SOTTOMESSO',
                    '_PROCESSING_' => 'IN ELABORAZIONE',
                    '_DONE_' => 'FINITO',
                ),
				'search_type' => 'select',
            ),
           

        );

       
        $this->setListOption('fields',$fields);
        
        
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
            ->setUrl("javascript:refresh_reports(".$this->id_store.")")
        );


        $row_actions = $this->getListOption('row_actions');
        $row_actions['actions'] = array(
            'download' => array(
                'text' => 'download',
                'icon_type' => 'icon',
                'icon' => 'fa fa-download',
                'enable_function' => 'checkDownload',
                'url_function' => 'downloadReport'
                
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

		if( $id = _var('ReportRequestId') ){
			$condizione .= "ReportRequestId LIKE '%{$id}%' AND ";
        }
        
        if($status = _var('ReportProcessingStatus')){
            $condizione .= "ReportProcessingStatus = '{$status}' AND ";
        }
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','amazon_report_sync',$condizione);

		
		

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

		
		

		$list = $database->select('*','amazon_report_sync',$condizione);

		$total_items = $tot[0]['tot'];

		
		$this->setListOption('total_items',$total_items);
		$this->setDataList($list);

    }

    function getImageMarket($market){
        if(_var('export')){
            return $market;
        }
        $image = $this->getBaseUrl()."modules/amazon/".AmazonTool::getMarketplaceImage($market);
        return "<img style='width:30px;' src='{$image}'>";
    }


    function getReportType($reportType){
        switch($reportType){
            case '_GET_MERCHANT_LISTINGS_DATA_':
                $reportType = 'Active Listings Report';
            break;
            case '_GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_':
                $reportType = 'Unshipped Orders Report';
            break;
        }
        return $reportType;
    }

    function downloadReport($row){
        $file = _MARION_MODULE_DIR_."amazon/reports/responses/".$row['ReportRequestId'].".csv";
        if( file_exists($file)){
            $url = _MARION_BASE_URL_.'modules/amazon/reports/responses/'.$row['ReportRequestId'].".csv";
            return $url;
        }else{
            return '';
        }
    }

    function checkDownload($row){
        $file = _MARION_MODULE_DIR_."amazon/reports/responses/".$row['ReportRequestId'].".csv";
        return file_exists($file);
    }

    function getStatus($row){
        switch($row['ReportProcessingStatus']){
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
                $status = $row['ReportProcessingStatus'];
                break;
        }
        
        if( _var('export') ){
            return $status;
        }else{
            return "<span class='label label-{$class}'>".$status."</span>";
        }
    }

    function getReport(){
        debugga('qua');exit;
    }
}

?>