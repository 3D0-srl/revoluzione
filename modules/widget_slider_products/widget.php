<?php
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
class WidgetSliderProductsComponent extends  PageComposerComponent{
	

	function registerJS($data=null){
		PageComposer::loadJS('bxslider');
		//PageComposer::registerJS("modules/widget_slider_products/js/script.js");
		PageComposer::registerJS("https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.2/js/swiper.js");
		PageComposer::registerJS("modules/widget_slider_products/js/frontend.js");
	}

	function registerCSS($data=null){
		PageComposer::registerCSS("https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.4.5/css/swiper.min.css");
		PageComposer::registerCSS("modules/widget_slider_products/css/frontend.css");
	}

	function build($data=null){
		
		//$widget = Marion::widget('widget_slider_tag');
		$dati = $this->getParameters();
		
		
		


		if( okArray($dati) ){

			switch($dati['slider_type']){
				case 'products_tag':
					$list = $this->getProductsTag($dati);
					break;
				case 'products_category':
					$list = $this->getProductsCategory($dati);
					break;
				case 'best_selleres':

					break;
				case 'new_arrivals':

					break;
			}
			
			if( okArray($list) ){
				
				$params['prodotti'] = $list;
				
				
				

				if( $dati['title'][$GLOBALS['activelocale']] ){
					$params['titolo'] = $dati['title'][$GLOBALS['activelocale']];
				}
				foreach($dati as $k => $v){
					$params[$k] = $v;
				}
				$escludi = array('num_products','category','slider_type','tag','id_box','enable_wishlist','enable_add_cart_button','enable_quantity_add_cart');
				foreach($dati as $k => $v){
					if( !okArray($v) && !in_array($k,$escludi)){
						
						$opzioni[$k] = $v;
					}else{
						$params[$k] = $v;
					}
				}
				

				$params['opzioni'] = json_encode($opzioni);
				//debugga($opzioni);exit;
				$params['id_box']= $this->id_box;

				foreach($params as $k => $v){
					$this->setVar($k,$v);
				}

				$ctrl = clone $this;
				$this->setVar('ctrl',$ctrl);
				$this->loadTemplatesFunction();


				$this->output('render.htm');

				
			}
		

		}
	}

	function getProductsTag($dati){
		$tag = $dati['tag'];
		$limit= $dati['num_products'];
		if( $tag ){
			//debugga('qua');exit;
			$tag = TagProduct::withId($tag);
			
			if( !is_object($tag) ){
				return false;
			}else{
				//prendo gli id dei prodotti con questo tag
				$ids = $tag->getProductIds();
				

				$list = Catalog::getProduct(
					array(
						'id'=>$ids
					),
					null,
					$limit,
					null
				)->toArray();

				
			}
			return $list;
		}else{
			return false;
		}
		
	}

	function getProductsCategory($dati){
		$id = $dati['category'];
		$limit= $dati['num_products'];
		if( $id ){
			
			$list = Catalog::getProduct(
				array(
					'section'=>$id
				),
				null,
				$limit,
				null
			)->toArray();

				
			
			return $list;
		}else{
			return false;
		}
		
	}

	function getProductsNew($dati){
		
		$limit= $dati['num_products'];
		
			
		$list = Catalog::getProduct(
			null,
			array('dateInsert' => 'DESC'),
			$limit,
			null
		)->toArray();

			
		
		return $list;
		
		
	}

	function getProductsBest($dati){
		
		
		$list = array();
		
		$database = _obj('Database');
		$list = $database->select('p.id,p.parent,count(o.quantity) as num','(cartRow as o join cart as c on c.id=o.cart) join product as p on p.id=o.product',"c.status != 'active' AND c.status != 'canceled' and p.deleted=0 AND p.visibility = 1 group by p.id,p.parent");
		
		$limite= $dati['num_products'];
		$product_quantity = array();
		foreach($list as $v){
			if( $v['parent'] ){
				$product_quantity[$v['parent']] += $v['num'];
			}else{
				$product_quantity[$v['id']] += $v['num'];
			}
		}
		
		function cmp_bestseller($a, $b) {
			if ($a == $b) {
				return 0;
			}
			return ($a > $b) ? -1 : 1;
		}
		uasort($product_quantity,'cmp_bestseller');
		$iter = 0;
		foreach( $product_quantity as $k => $v ){
			$product = Product::withId($k);
			if( is_object($product) && $product->visibility ){
				$product->quantity = $v;
				
				$list[] = $product;
				$iter++;
			}
			if( $iter >= $limite ) break;
		}




		return $list;
		
	}



	
	function loadTemplatesFunction(){
			
			$this->addTemplateFunction(
				new \Twig\TwigFunction('card_product_slider', function ($_ctrl,$obj=NULL,$imgType="small",$class="") {

				$ctrl = clone $_ctrl;
				
				if( is_object($obj) ){
					if( is_a($obj,'Product') ){

						$add_wish = $ctrl->_twig_vars['enable_wishlist'];
						$add_cart = $ctrl->_twig_vars['enable_add_cart_button'];
						$qty = $ctrl->_twig_vars['enable_quantity_add_cart'];
						

						
						$class_qty = '';
						$class_add_cart = '';
						$class_add_wish = '';
					

						if( $add_wish && $add_cart && $qty ){
							$class_add_cart = 'addcart';
							$class_add_wish = 'addwish';
							$class_qty = 'qty_prod';

						}elseif( $add_wish && $add_cart ){
							$class_add_cart = 'cart-wish';
							$class_add_wish = 'wish-cart';
						}elseif( $qty && $add_cart ){
							$class_add_cart = 'cart-qty';
							$class_qty = 'qty-cart';

						}elseif( $add_wish){
							$class_add_wish = 'wish-full';
						}elseif( $add_cart){
							$class_add_cart = 'cart-full';
						}

						$params['class_add_cart'] = $class_add_cart;
						$params['class_add_wish'] = $class_add_wish;
						$params['class_qty'] = $class_qty;
					

						$params['class_row_product'] = $class;
						$params['img_type_product_list'] = $imgType;
						$params['product_row']= $obj;

						foreach($params as $k => $v){
							$ctrl->setVar($k,$v);
						}

						
						$ctrl->output('card_product_slider.htm');
						//$this->output('card_product_slider.htm');
					}
				}
			})
		);
	}
}

?>