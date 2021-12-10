<?php
require(_MARION_MODULE_DIR_.'amazon/classes/AmazonStore.class.php');
require(_MARION_MODULE_DIR_.'amazon/classes/AmazonTool.class.php');
require(_MARION_MODULE_DIR_.'amazon/classes/BarcodeValidator.class.php');
require(_MARION_MODULE_DIR_.'amazon/classes/AmazonSyncro.class.php');
require(_MARION_MODULE_DIR_.'amazon/classes/AmazonOrders.class.php');
require(_MARION_MODULE_DIR_.'amazon/cpigroup/php-amazon-mws/includes/classes.php');
class CronController extends FrontendController{

    function display()
    {
        
        $action = $this->getAction();
        $id_store = _var('id_store');
        $market = _var('market');
        switch($action){
            case 'report_responses':
                $obj = AmazonSyncro::init($id_store);
                $obj->getReportsResponse();
                break;
            case 'feed_responses':
                $obj = AmazonSyncro::init($id_store);
                $obj->getFeedsResponse();
                break;
            case 'send_products':
               
                if( !$market ) return false;
                $obj = AmazonSyncro::init($id_store,$market);
                $obj->send(array('_POST_PRODUCT_DATA_'));
                $obj->send();
                break;
            case 'send_prices_and_inventory':
                exit;
                if( !$market ) return false;
                $obj = AmazonSyncro::init($id_store,$market);
                $obj->send(array('_POST_PRODUCT_PRICING_DATA_','_POST_INVENTORY_AVAILABILITY_DATA_'));
                $obj->send();
                break;
            case 'send_prices':
              
                if( !$market ) return false;
                $obj = AmazonSyncro::init($id_store,$market);
                $obj->send(array('_POST_PRODUCT_PRICING_DATA_'));
                $obj->send();
                break;
            case 'send_inventory':
                
                if( !$market ) return false;
                $obj = AmazonSyncro::init($id_store,$market);
                $obj->send(array('_POST_INVENTORY_AVAILABILITY_DATA_'));
                $obj->send();
                break;
            /*case 'import_orders':
                
                AmazonOrder::import($id_store,$market);
                
                break;*/
            case 'import_orders':
                $amz_store = AmazonStore::withId($id_store);
                $amz_order = AmazonOrders::init($amz_store);
                $items =$amz_order->download();
                if( _var('preview') ){
                    echo json_encode($items);
                    exit;
                }else{
                    $result = [];
                    if(okArray($items) ){
                        foreach($items as $order_id => $data){
                            $result[] = $amz_order->import($order_id,$data['cart'],$data['orders']);
                        }
                    }


                    
                    
                }
                break;
            case 'reports':
                if( !$market ) return false;
                $obj = AmazonSyncro::init($id_store,$market);
                $obj->reports(array('_GET_MERCHANT_LISTINGS_DATA_','_GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_'));
                break;
            case 'active_listings_report':
                if( !$market ) return false;
                $obj = AmazonSyncro::init($id_store,$market);
                $obj->reports(array('_GET_MERCHANT_LISTINGS_DATA_'));
                break;
            case 'unshipped_orders_report':
                if( !$market ) return false;
                $obj = AmazonSyncro::init($id_store,$market);
                
                $obj->reports(array('_GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_'));
               
                break;
            /*
            case 'orders':
                require('classes/AmazonOrders.class.php');
                if( !$market ){ 
                    $market = 'Europe';
                }
                $obj = AmazonSyncro::init($id_store,$market);
                $res = $obj->orders();
                echo json_encode($res);
                break;*/
            case 'acks':
                if( !$market ){ 
                    $market = 'Europe';
                }
                $obj = AmazonSyncro::init($id_store,$market);
                $obj->send(array('_POST_ORDER_ACKNOWLEDGEMENT_DATA_'));
                break;
            /*case 'order_status':
                if( !$market ){ 
                    $market = 'Europe';
                }
                $obj = AmazonSyncro::init($id_store,$market);
                $obj->send(array('_POST_ORDER_FULFILLMENT_DATA_'));
                break;*/
        
        
        }
        echo json_encode(array('result' => 'ok'));
    }

   
}
?>