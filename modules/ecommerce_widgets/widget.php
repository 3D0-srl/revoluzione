<?php
use Marion\Components\PageComposerComponent;
use Shop\{Cart,Eshop};
use Marion\Entities\Cms\PageComposer;
class WidgetShipmentComponent extends  PageComposerComponent{
	public $template_html = 'shipment.htm'; //html del widget

	function registerJS($data=null){
		
		PageComposer::registerJS('../plugins/slick/slick.min.js','end');
		PageComposer::registerJS('../modules/ecommerce_widgets/js/shipment.js','end');
		
	}
	function registerCSS($data=null){
		PageComposer::registerCSS('../plugins/slick/slick.css');
		PageComposer::registerCSS('../plugins/slick/slick-theme.css');
		PageComposer::registerCSS('../modules/ecommerce_widgets/css/shipments.css');
	
		
	}

	function build($data=null){

		$list = Cart::prepareQuery()->whereExpression("(paymentDate is not null AND shippingDate is null)")->where('status','deleted','<>')->get();
		$this->setVar('list',$list);
		$this->output($this->template_html);
	}
}

class WidgetIncomesMonthComponent extends  PageComposerComponent{
	public $template_html = 'box1.htm'; //html del widget

	function registerJS($data=null){
		
		PageComposer::registerJS('../plugins/metrojs/metrojs.min.js');
		
	}
	function registerCSS($data=null){
		
		PageComposer::registerCSS('../plugins/metrojs/metrojs.min.css');
		
	}

	function build($data=null){
		$this->output($this->template_html);
	}
}

class WidgetOrdersMonthComponent extends  PageComposerComponent{
	public $template_html = 'box2.htm'; //html del widget

	function registerJS($data=null){
		
		PageComposer::registerJS('../plugins/metrojs/metrojs.min.js');
		
	}
	function registerCSS($data=null){
		
		PageComposer::registerCSS('../plugins/metrojs/metrojs.min.css');
		
	}

	function build($data=null){
		$this->output($this->template_html);
	}
}

class WidgetProductsOnlineComponent extends  PageComposerComponent{
	public $template_html = 'box3.htm'; //html del widget

	function registerJS($data=null){
		
		PageComposer::registerJS('../plugins/metrojs/metrojs.min.js');
		
	}
	function registerCSS($data=null){
		
		PageComposer::registerCSS('../plugins/metrojs/metrojs.min.css');
		
	}

	function build($data=null){

		$database = Marion::getDB();
		$sel = $database->select('count(*) as tot','product',"visibility=1 AND deleted = 0 AND type=1");
		$this->setVar('tot_products',$sel[0]['tot']);
		
		$sel = $database->select('count(*) as tot','section',"visibility=1");
		$this->setVar('tot_sections',$sel[0]['tot']);
		$start = date('Y-m-01');
		$end = date("Y-m-t");
		$sel = $database->select('count(*) as tot','product',"visibility=1 AND deleted = 0 AND type =1 AND dateInsert >= '{$start}' AND dateInsert <= '{$end}'");

		$this->setVar('tot_new_porducts',$sel[0]['tot']);


		$this->output($this->template_html);
	}
}

class WidgetCustomersComponent extends  PageComposerComponent{
	public $template_html = 'box4.htm'; //html del widget

	function registerJS($data=null){
		
		PageComposer::registerJS('../plugins/metrojs/metrojs.min.js');
		
	}
	function registerCSS($data=null){
		
		PageComposer::registerCSS('../plugins/metrojs/metrojs.min.css');
		
	}

	function build($data=null){

		$database = Marion::getDB();
		$sel = $database->select('count(*) as tot','user',"active=1 AND deleted = 0");
		
		$num_users = $sel[0]['tot'];
	
		$start = date('Y-m-01');
		$end = date("Y-m-t");
		$sel = $database->select('count(*) as tot','user',"active=1 AND deleted = 0 AND dateInsert >= '{$start}' AND dateInsert <= '{$end}'");

		$num_users_month = $sel[0]['tot'];

		$sel = $database->select('count(*) as num,sum(total+shippingPrice+paymentPrice-discount) as tot,sum(num_products) as num_products','cart',"evacuationDate >= '{$start}' AND evacuationDate <= '{$end}'");
		$tot_orders_month = $sel[0]['tot'];
		$num_orders_month = $sel[0]['num'];
		$num_prod_mese = $sel[0]['num_products'];
		

		$sel = $database->select('count(*) as num,sum(total+shippingPrice+paymentPrice-discount) as tot,sum(num_products) as num_products','cart',"1=1");
		$tot_orders = $sel[0]['tot'];
		$num_orders = $sel[0]['num'];
		$num_prod = $sel[0]['num_products'];


		$ordine_medio_mese = Eshop::formatMoney($tot_orders_month/$num_orders_month);
		$ordine_medio = Eshop::formatMoney($tot_orders/$num_orders);

		$num_prod_mese = (int)($num_prod_mese/$num_orders_month);
		$num_prod = (int)($tot_orders/$num_orders);
		

		$this->setVar('num_prod_mese',$num_prod_mese);
		$this->setVar('num_prod_totale',$num_prod);
		$this->setVar('ordine_medio_mese',$ordine_medio_mese);
		$this->setVar('ordine_medio_totale',$ordine_medio);
		$this->setVar('tot_users',$num_users);
		$this->setVar('tot_new_users',$num_users_month);
		$this->output($this->template_html);
	}
}

class WidgetLastOrdersComponent extends  PageComposerComponent{
	
	public $template_html = 'render.htm'; //html del widget
	
	

	function build($data=null){
			$this->output($this->template_html);
			return;
			//$widget = Marion::widget(basename(__DIR__));

			//parametri di configurazione del widget
			$parameters = $this->getParameters();
			$carts = Cart::prepareQuery()->orderBy('evacuationDate','DESC')->where('status','deleted','<>')->where('status','active','<>')->limit(5)->get();
			
			if( !okArray($carts)){ 
				return '';

			}else{
				$lib_num = (float)preg_replace('/classes_/','',$GLOBALS['_dir_lib']);
				
				if( (float)preg_replace('/classes_/','',$GLOBALS['_dir_lib']) >= 4.4 ){
					$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->get();
					foreach($status_avaiables as $k => $v){
						$status_color[$v->label] = $v->color;
						$status_name[$v->label] = strtoupper($v->get('name'));
					}
				}else{
					foreach($carts as $k =>$v){
						switch($v->status){
							case 'active':
								$class = 'primary';
								break;
							case 'waiting':
								$class = 'info';
								break;
							case 'canceled':
								$class = 'danger';
								break;
							case 'confirmed':
								$class = 'warning';
								break;
							case 'sent':
								$class = 'success';
								break;
							case 'delivered':
								$class = 'greendark';
								break;
							case 'processing':
								$class = 'orange';
								break;
							default:
								$class = 'default';
							
							
						}
						$status_text = strtoupper(__('status_cart_'.$v->status));
						$status_text = "<span class='label label-{$class} w-300'>{$status_text}</span>";
						
						$carts[$k]->status_html = $status_text;
					}
				}
				$this->setVar('status_color',$status_color);
				$this->setVar('status_name',$status_name);
				$this->setVar('last_orders',$carts);
				$this->output($this->template_html);
			}
	}



	function isEditable(){
		

		return true;
	}
}




?>