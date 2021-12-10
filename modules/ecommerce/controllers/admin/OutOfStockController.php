<?php
use Marion\Controllers\ModuleController;
class OutOfStockController extends ModuleController{
	public $_auth = 'ecommerce';

	
	


	function display(){
		$this->setMenu('items_runout');
		

		$database = _obj('Database');


		$params = array(
			'perPage' => 15
		);
		if( $pagerNumber ){
			$params['perPage'] = $pagerNumber;
		}
		  //prendo lo step corrente del pager
		$next = _var('pageID');
	  
		//limite sulla select dei prodotti
		$limit = $params['perPage'];
		if( $next ){
		  //offset sulla select dei prodotti
		  $offset = ($next-1)*$params['perPage'];
		}

		if( $offset ){
			$limit = "limit {$limit}, offset {$offset}";
		}else{
			$limit = "limit {$limit}";
		}


		$sel = $database->select('p.id',"product as p join product_inventory as i on i.id_product=p.id","quantity=0 AND id_inventory=1 AND (type=1 OR parent is not NULL) AND deleted=0 {$limit}");


		
		$tot = $database->select('count(*) as cont',"product as p join product_inventory as i on i.id_product=p.id","quantity=0 AND id_inventory=1 AND (type=1 OR parent is not NULL) AND deleted=0");
		
		
		
		foreach($sel as $v){
			$where .= "{$v['id']},";
		}
		$where = preg_replace('/\,$/','',$where);
		
		$query = Product::prepareQuery()
			->whereExpression('id IN ('.$where.')')
			->orderBy('parent');
		
		

		$list = $query->get();
		
		$tot = $tot[0]['cont'];

		if( $tot ){
		  
		   $params['totalItems'] = $tot;
		}else{
		   $params['itemData'] = $list;
		}

			
		require_once 'Pager.php';
		$params['path'] = "/admin";
		$pager = &Pager::factory($params);
		if( $tot ){
			
			$data = $list;
		}else{
			$data = $pager->getPageData();
		}

		
		//prendo i link del pager
		$links = $pager->getLinks();
		
		
		
		$this->setVar('products',$data);
		$this->setVar('links',$links);
		
		//debugga($data);exit;
		$this->output('out_of_stock.htm');
	}




	function ajax(){

		

		
		$formdata = _var('formdata');
		
		
		if( !okArray($formdata) ){
			$product = _var('product');
			$stock = _var('stock');
			$formdata[$product] = $stock;
		}
		
		if( okArray($formdata) ){
			foreach($formdata as $id => $stock){
				$product = Product::withId($id);
				
				if(is_object($product) && $stock > 0){
					$product->updateInventory($stock);
					$products_ok[] = $id;
				}

			}
		}
		
		$risposta = array(
			'result' => 'ok',
			'products_ok' => $products_ok,
		);



		echo json_encode($risposta);
		exit;
	}

	

}



?>