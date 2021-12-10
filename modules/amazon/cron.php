<?php
require ('../../../config.inc.php');

require('classes/AmazonStore.class.php');
require('classes/AmazonTool.class.php');
require('classes/BarcodeValidator.class.php');
require('classes/AmazonSyncro.class.php');
require('cpigroup/php-amazon-mws/includes/classes.php');

$type = _var('type');
$market = _var('market');
$id_store = _var('id_account');
if( !$id_store ) return false;

switch($type){
	case 'responses':
		$obj = AmazonSyncro::init($id_store);
		$obj->getResponses();
		break;
	case 'products':
		if( !$market ) return false;
		$obj = AmazonSyncro::init($id_store,$market);
		$obj->send(array('_POST_PRODUCT_DATA_'));
		$obj->send();
		break;
	case 'prices_inventory':
		if( !$market ) return false;
		$obj = AmazonSyncro::init($id_store,$market);
		$obj->send(array('_POST_PRODUCT_PRICING_DATA_','_POST_INVENTORY_AVAILABILITY_DATA_'));
		$obj->send();
		break;
	case 'prices':
		if( !$market ) return false;
		$obj = AmazonSyncro::init($id_store,$market);
		$obj->send(array('_POST_PRODUCT_PRICING_DATA_'));
		$obj->send();
		break;
	case 'inventory':
		if( !$market ) return false;
		$obj = AmazonSyncro::init($id_store,$market);
		$obj->send(array('_POST_INVENTORY_AVAILABILITY_DATA_'));
		$obj->send();
		break;
	case 'reports':
		if( !$market ) return false;
		$obj = AmazonSyncro::init($id_store,$market);
		$obj->reports(array('_GET_MERCHANT_LISTINGS_DATA_'));
		break;
	case 'order_reports':
		if( !$market ) return false;
		$obj = AmazonSyncro::init($id_store,$market);
		
		$obj->reports(array('_GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_'));
		break;
	case 'orders':
		require('classes/AmazonOrders.class.php');
		if( !$market ){ 
			$market = 'Europe';
		}
		$obj = AmazonSyncro::init($id_store,$market);
		$res = $obj->orders();
		echo json_encode($res);
		break;
	case 'acks':
		if( !$market ){ 
			$market = 'Europe';
		}
		$obj = AmazonSyncro::init($id_store,$market);
		$obj->send(array('_POST_ORDER_ACKNOWLEDGEMENT_DATA_'));
		break;
	case 'order_status':
		if( !$market ){ 
			$market = 'Europe';
		}
		$obj = AmazonSyncro::init($id_store,$market);
		$obj->send(array('_POST_ORDER_FULFILLMENT_DATA_'));
		break;


}
debugga('fine');
Marion::closeDB();









?>