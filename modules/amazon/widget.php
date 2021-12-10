<?php
/*
function product_amazon_tab(){
	
	$action = _var('action');
	$formdata = _var('formdata');
	if( !okArray($formdata) ){
		$formdata = _formdata();
	}
	$id = _var('id');
	if( $action == 'add_child' || $action == 'add' || $action == 'add_ok' ){
		return false;
	}else{
		$product = Product::withId($id);
		if( !is_object($product) ){
			return false;
		}
	}
	$html = "<li><a href='#product_amazon' data-toggle='tab'><img src='modules/amazon/images/amazon-logo_prod.png?v=2' style='width:40px;'/></a></li>";
	return $html;
	
}
Marion::add_widget('form_prodotto.htm','product_amazon_tab','tab_product','admin',1,'append');
Marion::add_widget('form_prodotto_multilocale.htm','product_amazon_tab','tab_product','admin',1,'append');



function product_amazon_tab_content(){
	
	$database = _obj('Database');
	$action = _var('action');
	$formdata = _var('formdata');
	if( !okArray($formdata) ){
		$formdata = _formdata();
	}
	
	$id = _var('id');
	if( $action == 'add_child' || $action == 'add' || $action == 'add_ok' ){
		return false;
	}else{
		$product = Product::withId($id);
		if( !is_object($product) ){
			return false;
		}
	}
	$module_dir = 'amazon';
	$widget = Marion::widget($module_dir);
	
	require_once('classes/AmazonProduct.class.php');
	$list = AmazonProduct::prepareQuery()->where('id_product',$id)->get();
	foreach($list as $v){
		$old[$v->id_account][$v->marketplace] = $v;
	}
	

	$tabs = array();
	$stores = $database->select('*','amazon_store',"1=1");
	if( okArray($stores) ){
		foreach($stores as $k => $v){
			$tabs[$k]['id'] = $v['id'];
			$tabs[$k]['name'] = $v['name'];
			$markets = unserialize($v['marketplace']);
			//debugga($markets);exit;
			foreach($markets as $k2 => $m){
				switch($m){
					case 'Italy':
						$tabs[$k]['markets'][$k2]['img'] = 'it.png';
						break;
					case 'UK':
						$tabs[$k]['markets'][$k2]['img'] = 'gb.png';
						break;
					case 'Germany':
						$tabs[$k]['markets'][$k2]['img'] = 'de.png';
						break;
					case 'France':
						$tabs[$k]['markets'][$k2]['img'] = 'fr.png';
						break;
					case 'Spain':
						$tabs[$k]['markets'][$k2]['img'] = 'es.png';
						break;
					case 'US':
						$tabs[$k]['markets'][$k2]['img'] = 'us.png';
						break;
					case 'China':
						$tabs[$k]['markets'][$k2]['img'] = 'cn.png';
						break;
					case 'Japan':
						$tabs[$k]['markets'][$k2]['img'] = 'jp.png';
						break;
					case 'Mexico':
						$tabs[$k]['markets'][$k2]['img'] = 'mx.png';
						break;
					case 'Canada':
						$tabs[$k]['markets'][$k2]['img'] = 'ca.png';
						break;
					case 'India':
						$tabs[$k]['markets'][$k2]['img'] = 'in.png';
						break;
					case 'Brazil':
						$tabs[$k]['markets'][$k2]['img'] = 'br.png';
						break;
					
				}
				if( $old[$v['id']][$m] ){
					$tabs[$k]['markets'][$k2]['price'] = $old[$v['id']][$m]->price;
					$tabs[$k]['markets'][$k2]['bullet_1'] = $old[$v['id']][$m]->bullet_1;
					$tabs[$k]['markets'][$k2]['bullet_2'] = $old[$v['id']][$m]->bullet_2;
					$tabs[$k]['markets'][$k2]['bullet_3'] = $old[$v['id']][$m]->bullet_3;
					$tabs[$k]['markets'][$k2]['disable_sync'] = $old[$v['id']][$m]->disable_sync;
					$tabs[$k]['markets'][$k2]['parent_description'] = $old[$v['id']][$m]->parent_description;
					$tabs[$k]['markets'][$k2]['new_product'] = $old[$v['id']][$m]->new_product;
				}else{
					$tabs[$k]['markets'][$k2]['disable_sync'] = 0;
					$tabs[$k]['markets'][$k2]['parent_description'] = 0;
				}
				$tabs[$k]['markets'][$k2]['name'] = $m;
				
			}
			
		}
		
	}

	if( count($tabs) == 1 ){
		$widget->one_account = 1;
	}
	
	if( $product->type == 2 && $product->parent == 0){
		$widget->hasChildren = true;
	}
	

	if( $product->parent ){
		$widget->is_children = true;
	}
	
	
	
	
	
	$widget->tabs = $tabs;
	
	ob_start();
	if( isMultilocale()){
		$widget->output('tab_product_multilocale.htm',$elements);
	}else{
		$widget->output('tab_product.htm',$elements);
	}
	
	$html = ob_get_contents();
	ob_end_clean();
	//debugga($html);exit;
	return $html;
	
}

Marion::add_widget('form_prodotto.htm','product_amazon_tab_content','tab_product_content','admin',1,'append');
Marion::add_widget('form_prodotto_multilocale.htm','product_amazon_tab_content','tab_product_content','admin',1,'append');

*/
?>