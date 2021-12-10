<?php
use Marion\Core\Module;
use Marion\Core\Marion;
use Marion\Components\WidgetComponent;
use Shop\{Cart,Order,CartStatus};
class Ecommerce extends Module{
	
	

	function install(){
		$res = parent::install();
		if( $res ){
			
			
		}
		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			
		}
		
		return $res;
	}


	/**
	 * HOOK ACTIONS
	 */

	 /**
	  * actionRegisterTwigTemplatesDir
	  * Metodo registrato per l'hook 'action_register_twig_templates_dir'
	  */

	 function actionRegisterTwigTemplatesDir(&$direcories=array()){
		$direcories[] = _MARION_MODULE_DIR_."ecommerce/templates_twig";
		return;
	 }

	 /**
	  * displayBackendHome
	  * Metodo registrato per l'hook 'display_backend_home'
	  */

	 function displayBackendHome(){
		

		$widget = new WidgetComponent('ecommerce');
		
		$user = Marion::getUser();
		$carrelli = Cart::prepareQuery()
				->where('user',$user->id)
				->where('status','active','<>')
				->limit(5)
				->orderBy('evacuationfDate','DESC')
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
						$v->image = $prod->getUrlImage(0,'thumbnail');
					}
				}
				$v->evacuationDate = strftime('%d/%m/%Y %H:%M',strtotime($v->evacuationDate)); 
				$v->status = $status[$v->status];
			}
			
			$widget->setVar('carrelli',$carrelli);
		}
		$widget->output('block_home.htm');
			
	}
	/**
	 *	actionRegisterMediaFront 
	 *
	 * @param [type] $ctrl
	 * @return void
	 */
	function actionRegisterMediaFront($ctrl){
		
		$ctrl->registerJS('modules/ecommerce/js/eshop.js?v=1','head');
		
		if( get_class($ctrl) == 'HomeController' ){
			$ctrl->registerCSS('modules/ecommerce/css/backend_ecommerce.css');
		}
	}

	/**
	 * actionAfterLogin
	 *
	 * @return void
	 */
	function actionAfterLogin(){
		Cart::load();
	}
	

	/**
	 * displayProductExtra2
	 *
	 * @param [type] $product
	 * @return void
	 */
	function displayProductExtra2($product){
		echo '<div id="error_add_to_cart"></div>';
	}


	/**
	 * productAfterLoad function
	 *
	 * @param [type] $prodotto
	 * @return void
	 */
	function productAfterLoad($prodotto){
		$database = Marion::getDB();
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

	/**
	 * ecommerceCleanData
	 *
	 * @param [type] $list
	 * @return void
	 */
	function ecommerceCleanData(&$list){
	
	
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
	
	/**
	 * ecommerceCleanDeleteData
	 *
	 * @param [type] $module
	 * @param [type] $values
	 * @return void
	 */
	function ecommerceCleanDeleteData($module,$values){
		
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
	

	


}



?>