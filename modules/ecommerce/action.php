<?php
use \Marion\Core\Marion;
use Shop\{Cart,UserCategory,Price,Tax,PriceList,Order,CartStatus};
use Catalogo\{Product,ProductTabAdminController};
use Marion\Components\WidgetComponent;
/*function ecommerce_register_twig_templates_dir(&$direcories=array()){
	$direcories[] = _MARION_MODULE_DIR_."ecommerce/templates_twig";
	return;
}

Marion::add_action('action_register_twig_templates_dir','ecommerce_register_twig_templates_dir');

*/

function ecommerce_preview_order_in_home(){
		

		$widget = new WidgetComponent('ecommerce');
		
		$user = Marion::getUser();
		$carrelli = Cart::prepareQuery()
				->where('user',$user->id)
				->where('status','active','<>')
				->limit(4)
				->orderBy('evacuationDate','DESC')
				->get();
		$stati = CartStatus::prepareQuery()->get();
		foreach($stati as $v){
			
			$status[$v->label] = "<span class='label' style='background:".$v->color."'>".strtoupper($v->get('name'))."</span>";
		}

		
		
		if(okArray($carrelli)){

			foreach($carrelli as $v){
				$ord =Order::prepareQuery()->where('cart',$v->id)->orderBy('id','DESC')->getOne();
				if( is_object($ord) ){
					$prod = $ord->getProduct();
					if( is_object($prod) ){
						$v->image = $prod->getUrlImage(0,'original');
					}
				}
				$v->evacuationDate = strftime('%d/%m/%Y %H:%M',strtotime($v->evacuationDate)); 
				$v->status = $status[$v->status];
				$v->productname = $prod->get('name');
			}
			
			$widget->setVar('carrelli',$carrelli);
		}
		

		
		$widget->output('block_home.htm');
		
		

		
			
}



Marion::add_action('display_backend_home','ecommerce_preview_order_in_home');


class EcommerceTabAdmin extends ProductTabAdminController{
	


	public function getTitle(): string{
		return 'Shop';
	}

	public function getTag():string{
		
		return 'ecommerce';
	}


	function setMedia(){
		$this->registerJS('../modules/ecommerce/js/admin/product_form.js');
	}

	function getContent(){
		
		$formdata = $this->getFormdata();
		if($formdata['type']){
			$this->setVar('product_type',$formdata['type']);
		}
		
		
		$id = $this->getID();
		$action = $this->getAction();
		
		//se sto modificando un prodotto già salvato
		if( $id && $action != 'add'){
			
			$categorie = UserCategory::prepareQuery()->get();
			$this->setVar('categorie',$categorie);
			$pricelist = PriceList::prepareQuery()->orderBy('priority')->get();
			$this->setVar('pricelist',$pricelist);
			
			
			$prodotto = Product::withId($id);
			$this->setVar('product_type',$prodotto->type);
		
		
			
			
			if( $prodotto->parent){
				
				$this->setVar('has_parent',true);
			}


			$database = _obj('Database');
			$dati = $database->select('*','product_shop_values',"id_product={$prodotto->id}");
			
			if( okArray($dati) ){
				$dati = $dati[0];
			}


			//prendo i prezzi di listino se presenti
			$prezzi_listino = Price::prepareQuery()
					->where('product',$prodotto->getId())
					->where('label','default','<>')
					->where('label','barred','<>')
					->get();
			
			if( okArray($prezzi_listino) ){
				foreach($prezzi_listino as $k => $v){
					if( $v->dateStart || $v->dateEnd || $v->userCategory ){
						$v->advanced = 1;
					}
					if( $v->dateStart ){
						$v->dateStart = strftime("%d/%m/%Y",strtotime($v->dateStart));
					}
					if( $v->dateEnd ){
						$v->dateEnd = strftime("%d/%m/%Y",strtotime($v->dateEnd));
					}
					$app_price_list = (array)$v;
					if( $action == 'dup' ){
						unset($app_price_list['id']);
					}
					$prezzi_listino_array[$k] = $app_price_list;

				}
				
				$this->setVar('listini_prezzi',$prezzi_listino_array);
				$this->setVar('cont_price_list',count($prezzi_listino));

				
				$dati['manager_pricelist'] = 1;
			}else{
				$dati['manager_pricelist'] = 0;
				$this->setVar('cont_price_list',0);
			}
			
			

			//prendo il prezzo di default
			$prezzo_default = Price::prepareQuery()
					->where('product',$prodotto->getId())
					->where('label','default')
					->getOne();
			if(is_object($prezzo_default)){
				$dati['price_default'] = $prezzo_default->value;	
			}
			

			//prendo il prezzo barrato
			$prezzo_list = Price::prepareQuery()
					->where('product',$prodotto->getId())
					->where('label','barred')
					->getOne();
			if(is_object($prezzo_list)){
				$dati['price_barred'] = $prezzo_list->value;
				
			}
		
		
			
		}else{
			if( $id && $action == 'add'){
				$this->setVar('product_type',1);
				$this->setVar('has_parent',true);
			}
			$dati['manager_pricelist'] = 0;
			$dati['min_order'] = 1;
			$dati['max_order'] = 0;
			
		}
		
		

		
		
		

		$dataform = $this->getDataForm('ecommerce_values',$dati);
		$this->setVar('dataform',$dataform);
	
		

	
		$this->output('tab_product_prices.htm');

		




	}
	

	function checkData(){
		$formdata = $this->getFormdata();
		$action = _var('action');
		/*if( !$formdata['id']  || $action == 'add'){
			$this->is_new = true;
		}
		if( $this->is_new ) return 1;
		*/
		
		$error = 1;
		
		if( $formdata['type'] == 2){
			
			
			$campi_aggiuntivi['min_order']['obbligatorio'] = 0;
			$campi_aggiuntivi['max_order']['obbligatorio'] = 0;
		}

		if( $formdata['parent'] && $formdata['parent_price']){
			$campi_aggiuntivi['price_default']['obbligatorio'] = 0;
		}
		
	
		$array = $this->checkDataForm('ecommerce_values',$formdata,$campi_aggiuntivi);
		
		if( $array[0] == 'ok'){
			$this->checked_data = $array;
			$pricelists = $formdata['pricelist'];
			//controllo dei prezzi di listino
			
			if( $formdata['manager_pricelist'] && count($pricelists) > 0 ){
				
				//controllo se i dati inseriti per i prezzi sono corretti
				$check_prices = $this->checkPrices($pricelists);
				
				if( $check_prices[0] == 'nak'){
					$array = $check_prices;
					$error = $check_prices[1];
				}else{

					//array contentente i prezzi 
					$prices_data = $check_prices['data'];
					$this->checked_prices = $prices_data;
					//controllo se esitono prezzi duplicati a parità di condizioni
					$check_duplicate = $this->checkDuplicatePrices($prices_data);
					
					if( $check_duplicate[0] == 'nak'){
						$error = $check_duplicate[1];
						
					}
				}
			}
		}else{
			$error = $array[1];
		}
		
		return $error;
	}

	function reloadContent():bool{
		return false;
	}

	function reloadPage():bool{
		return false;
		
	}


	function process($product=null){
		
		if( !$this->is_new ){

			$data = $this->checked_data;
			$this->saveData($product,$data);
			$this->savePrices($product);
		}else{
			
			
			if( $product->parent ){
				//sto inserendo una variazione
				$database = Marion::getDB();
				$data_parent = $database->select('*','product_shop_values',"id_product={$product->parent}");
				if( okArray($data_parent)) $data_parent = $data_parent[0];
				$data = array(
					'min_order' => 1,
					'max_order' => 0,
					'cost' => $data_parent['cost'],
					'parent_price' => 1,
					'id_tax' => $data_parent['id_tax']
				);
				
				$this->saveData($product,$data);
			}else{
				$data = array(
					'min_order' => 1,
					'max_order' => 0,
					'cost' => 0,
					'parent_price' => 1,
					'id_tax' => 0
				);
				
				$this->saveData($product,$data);
			}
		}


	
		
	}


	//FUNZIONI AUSILIARE
	

	function saveData($product,$data){
		$array = $data;
		$data = array(
			'min_order' => $array['min_order'],
			'max_order' => $array['max_order'],
			'id_tax' => $array['id_tax'],
			'parent_price' => $array['parent_price'],
			'cost' => $array['cost'],
			'id_product' => $product->id
		);
		
		$database = Marion::getDB();
		$check = $database->select('*','product_shop_values',"id_product={$product->id}");
		if( okArray($check) ){
			$database->update('product_shop_values',"id_product={$product->id}",$data);
		}else{
			$database->insert('product_shop_values',$data);
		}

		
		
	}

	
	//salvataggio dei prezzi
	function savePrices($product){
		

		$array = $this->checked_data;
		$dati_prezzi_listino= $this->checked_prices;
		
		$formdata = $this->getFormdata();

		//verifico se il prezzo di default esiste
		$query = Price::prepareQuery()
			->whereMore(
				array(
					'product' => $product->getId(),
					'label' => 'default'
				)
			);
		
		$price = $query->getOne();
		//debugga($query);exit;
		//se esiste lo aggiorno altrimenti lo creo
		if($price){
			$price->set(
				array(
					'value'=>$array['price_default']
				))->save();	
		}else{
			$toinsert_price_default =array(
					'product' => $product->getId(),
					'label' => 'default',
					'value'=>$array['price_default']
			);
			$new_price = Price::create()->set($toinsert_price_default);
			$new_price->save();
			//debugga($new_price);exit;
		}


		
		//verifico se il prezzo barrato esiste
		$query = Price::prepareQuery()
			->whereMore(
				array(
					'product' => $product->getId(),
					'label' => 'barred'
				)
			);
		$price = $query->getOne();
		
		//se esiste lo aggiorno altrimenti lo creo
		if($price){
			$price->set(
				array(
					'value'=>$array['price_barred']
				))->save();	
		}else{
			$toinsert_price_list =array(
					'product' => $product->getId(),
					'label' => 'barred',
					'value'=>$array['price_barred']
			);
			Price::create()->set($toinsert_price_list)->save();
		}
	
		
	
		
		//prelevo i prezzi di listino vecchi
		$old_price_stock = Price::prepareQuery()
						->where('product',$product->getId())
						->where('label','default','<>')
						->where('label','barred','<>')
						->get();
		if(okArray($old_price_stock)){
			foreach($old_price_stock as $k => $v){
				$da_eliminare[$v->getId()] = $v;
			}
		}
		
		
		
		if( okArray($dati_prezzi_listino) ){
			foreach($dati_prezzi_listino as $k => $v){
				
				$v['product'] = $product->getId();
				//debugga($v);exit;
				if( !$v['id'] ){
					$res = Price::create()->set($v)->save();
					
				}else{
					unset($da_eliminare[$v['id']]);
					$res = Price::withId($v['id'])->set($v)->save();
					
				}
				
			}
			

		}


		//se ci sono listini vecchi da eliminare allora li elimino
		if(okArray($da_eliminare)){
			foreach($da_eliminare as  $v){
				$v->delete();
			}
		}

	}



	//controllo i dati relativi ai prezzi
	function checkPrices($pricelists=array()){
		
		foreach($pricelists as $k => $listino){
			
			//controllo i dati inseriti per il prezzo di listino
			$check_listino = $this->checkDataForm('priceListProduct',$listino);
			//debugga($check_listino);exit;
			if( $check_listino[0] == 'nak' ){
				$array[0] = 'nak';
				$array[1] = $check_listino[1];
				$array[2] = $check_listino[2];
				$array[3] = $k;
				//$template->id_price_list = $k;
				//$template->tabActive = 'product_price';
				break;
			}else{
				//controllo le date inserite per il prezzo di listino
				if( $check_listino['dateStart'] && $check_listino['dateEnd'] && strtotime($check_listino['dateStart']) > strtotime($check_listino['dateEnd']) ){
					$array[0] = 'nak';
					$array[1] =  "Listino Prezzi: data fine precedente a quella di inizio";
					$array[2] = $check_listino[2];
					$array[3] = $k;
					//$template->id_price_list = $k;
					//$template->tabActive = 'product_price';
					break;
				}else{
					unset($check_listino[0]);
					$dati[$k] = $check_listino;
				}
			}
			
		}

		if( $array[0] != 'nak'){
			
			$array[0] = 'ok';
			$array['data'] = $dati;
		}
		return $array;
		
	}

	//controllo se sono stati inseriti dei prezzi duplicati
	function checkDuplicatePrices($dati_prezzi_listino=array()){
		$check_overlapping = true;

		
		foreach($dati_prezzi_listino as $k => $v){
			if(  $v['dateStart'] ){
				$a1 = strtotime($v['dateStart']);
			}

			if(  $v['dateEnd'] ){
				$a2 = strtotime($v['dateEnd']);
			}
			
			if( $check_overlapping ){
				foreach($dati_prezzi_listino as $k1 => $v1){
					if( $v1['quantity'] != $v['quantity'] ) continue;
					if( $k1 != $k ){
						
						if(  $v['label'] == $v1['label'] && ($v['userCategory'] == $v1['userCategory'] || $v1['userCategory'] == 0 && $v['quantity'] == $v1['qunatity']) ){
							
							//exit;
							if(  $v1['dateStart'] ){
								$b1 = strtotime($v1['dateStart']);
							}

							if(  $v1['dateEnd'] ){
								$b2 = strtotime($v1['dateEnd']);
							}
							
							
							if( $a1  && $a2 && $b1 && $b2){
								if($b1 > $a2 || $a1 > $b2 || $a2 < $a1 || $b2 < $b1)
								{

									continue;
								}else{
									$array[0] = 'nak';
									$array[1] =  "Listino Prezzi: due o più prezzi di listino per lo stesso periodo";
									$array[3] = $k;
									$check_overlapping = false;
									break;
								}
							}elseif( $a1 && $a2 && $b1 ){
								

							}elseif( $a1 && $a2 && $b2 ){


							}elseif( $a1 && $b1 && $b2 ){


							}elseif( $a2 && $b1 && $b2 ){


							}elseif( $a2 && $b1 && $b2 ){

							
							} elseif( $a1 && $b2){

							} elseif( $a2 && $b1){

							}else{
								$array[0] = 'nak';
								$array[1] =  "Listino Prezzi: due o più prezzi di listino per lo stesso periodo";
								$array[3] = $k;
								$check_overlapping = false;
								break;
							}
						
						}

					}
				}
			}else{
				break;
			}

		}

		if( $array[0] != 'nak'){
			$array[0] = 'ok';
		}
		return $array;

	}


	//FUNZIONI FORM
	function listTaxes(){

		$toreturn = array( 'Nessuna tassa' );
		$tasse = Tax::prepareQuery()->where('active',1)->orderBy('percentage')->get();
		if( okArray($tasse) ){
			foreach( $tasse as $tax){
				$perc[$tax->id] = $tax->percentage;
				$toreturn[$tax->id] = $tax->get('name');	
			}
		}
		$this->setVar('percentage_tax',json_encode($perc));
		return $toreturn;

	}

	function array_insieme_attributi(){
		
		$insiemi = AttributeSet::getList();
		
		$select = array('nessuno');
		foreach($insiemi as $k => $v){
			$select[$v->getId()] = $v->getLabel();
		}
		return $select;

	}
}
Product::registerAdminTab('EcommerceTabAdmin');







/*function ecommerce_get_data_product($product){
	
	$database = Marion::getDB();
	$dati = $database->select('*','product_shop_values',"id_product={$product->id}");
	if( okArray($dati) ){
		$dati = $dati[0];
		$product->minOrder = $dati['min_order'];
		$product->maxOrder = $dati['max_order'];
		$product->taxCode = $dati['id_tax'];
		$product->parentPrice = $dati['parent_price'];
	}
}


Marion::add_action('after_load_product','ecommerce_get_data_product');
*/

/*function ecommerce_set_media_ctrl($ctrl){

	$ctrl->registerJS('modules/ecommerce/js/eshop.js?v=1','head');
	
	if( get_class($ctrl) == 'HomeController' ){
		$ctrl->registerCSS('modules/ecommerce/css/backend_ecommerce.css');
	}
}

Marion::add_action('action_register_media_front','ecommerce_set_media_ctrl');
*/

//debugga(basename(__DIR__));exit;

if( _var('currency') ){
	Cart::updateCurrent();
}



/*function ecommerce_load_cart(){

	Cart::load();
}

Marion::add_action('action_after_login','ecommerce_load_cart');



Marion::add_action('action_after_login','ecommerce_load_cart');

*/
/*function display_error_add_to_cart($prodotto){
	
	echo '<div id="error_add_to_cart"></div>';
}
Marion::add_action('display_product_extra2','display_error_add_to_cart');
*/

// da sistemare

/*function catalog_pagecomposer_layout_blocks($url,$id_page,$block){
	debugga($url);
	debugga($id_page);
	debugga($block);
}

Marion::add_action('pagecomposer_layout_blocks','catalog_pagecomposer_layout_blocks');*/



/*function ecommerce_load_data_shop($prodotto){
	$database = _obj('Database');
	$sel = $database->select('*','product_shop_values',"id_product={$prodotto->id}");
	if( okArray($sel) ){
		$data = $sel[0];
		$prodotto->parentPrice = $data['parent_price'];
		$prodotto->minOrder = $data['min_order'];
		$prodotto->maxOrder = $data['max_order'];
		$prodotto->taxCode = $data['id_tax'];
	
	}
	
	return;
	
}
Marion::add_action('product_after_load','ecommerce_load_data_shop');
*/




/*function ecommerce_clean_data(&$list){
	
	
	$list['ecommerce'] =
		array(
			'name' => 'Ecommerce',
			'entities' => array(
				'orders' => 'Ordini',
				'wishlist' => 'Wishlist',
				'price_lists' => 'Listini',
				'prices' => 'Prezzi',
				'shipping_methods' => 'Metodi di spedizione',
				'shipping_areas' => 'Aree di spedizione',
				'addresses' => 'Indirizzi di spedizione',
			),

		);
	
	return;
	
}


function ecommerce_clean_delete_data($module,$values){
	
	if( $module != 'ecommerce'){
		return;
	}
	$database = Marion::getDB();
	foreach($values as $v){
		switch($v){
			case 'orders':
				//codice per eliminare gli ordini
				
				$database->delete('cartRow');
				$database->delete('cartChangeStatus');
				$database->delete('cart_transaction');
				$database->delete('cart');
				$database->execute("ALTER TABLE cartRow AUTO_INCREMENT = 1");
				$database->execute("ALTER TABLE cart AUTO_INCREMENT = 1");
				$database->execute("ALTER TABLE cart_transaction AUTO_INCREMENT = 1");


				break;
			case 'wishlist':
				$database->delete('wishlist');
				break;
			case 'prices':
				$database->delete('price');
				$database->execute("ALTER TABLE price AUTO_INCREMENT = 1");
				//$database->delete('priceList');
				//codice per eliminare i listini

				break;
			case 'price_lists':
				$database->delete('priceListLocale');
				$database->delete('priceList');
				$database->execute("ALTER TABLE priceList AUTO_INCREMENT = 1");
				//codice per eliminare i listini

				break;
			case 'shipping_methods':
				//codice per eliminare i metodi di spedizione
				$database->delete('shippingMethodLocale');
				$database->delete('shippingMethodPrice');
				$database->delete('shippingMethodWeight');
				$database->delete('shippingMethod');
				$database->execute("ALTER TABLE shippingMethod AUTO_INCREMENT = 1");
				break;
			case 'shipping_areas':
				//codice per eliminare le aree di spedizioni
				$database->delete('shippingArea');
				$database->delete('shippingAreaComposition');
				$database->execute("ALTER TABLE shippingArea AUTO_INCREMENT = 1");

				break;

			case 'addresses':
				$database->delete('cart_address');
				$database->execute("ALTER TABLE cart_address AUTO_INCREMENT = 1");
				//codice per eliminare gli indirizzi

				break;

		}
	}



	return;
}


Marion::add_action('action_clean_data','ecommerce_clean_data');
Marion::add_action('action_clean_delete_data','ecommerce_clean_delete_data');
*/

?>