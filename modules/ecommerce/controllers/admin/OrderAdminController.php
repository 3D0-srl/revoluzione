<?php
use Marion\Controllers\Elements\ListActionBulkButton;
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
use Marion\Entities\Cms\Notification;
use Shop\{Cart,CartStatus,Eshop,PaymentMethod};
use \Wkhtmltopdf;
class OrderAdminController extends AdminModuleController{
	public $_auth = 'ecommerce';

	private $excluded =  array(
			'deleted',
			'active',
			'redirect_gateway'
		);

	function displayContent(){
		$action = $this->getAction();
		switch($action){
			case 'download_invoice':
				$this->downloadInvoice();
				break;
			case 'create_invoice':
				$this->createInvoice();
				break;
			case 'print_order':
				$id = $this->getId();
				$cart = Cart::withId($id);
				$this->printOrder($cart);
				break;
			case 'unarchive':
				$this->unarchiveOrder();
				break;
			case 'archive':
				$this->archiveOrder();
				break;
		}
	}

	/**
	 * displayForm
	 */

	function displayForm(){
		$this->setMenu('manage_orders');

		$this->addTemplateFunction(
			new \Twig\TwigFunction('tabActive', function ($val1,$val2) {
				if( $val1 == $val2){
					return "active in";
				}
				return '';
			})
		);
		
		$id = $this->getId();
		$tabActive = _var('tab');
		if( !$tabActive ) $tabActive = 'order_resume';
		$template_var['tabActive'] =$tabActive;

		$stati = CartStatus::prepareQuery()->get();
		foreach($stati as $v){
			if( in_array($v->label,$this->excluded)) continue;
			$template_var['status'][$v->label] = "<span class='label' style='background:".$v->color."'>".strtoupper($v->get('name'))."</span>";
			$template_var['status_color'][$v->label] = $v->color;
			$template_var['status_name'][$v->label] = strtoupper($v->get('name'));
			$template_var['status_email'][$v->label] = $v->send_mail?1:0;
			$template_var['status_sent'][$v->label] = $v->sent?1:0;
		
		}
		
		$cart = Cart::withId($id);
		
		//elimino la notifica se esiste
		$current_user = Marion::getUser();
		$notification = Notification::prepareQuery()
			->where('receiver',$current_user->id)
			->where('custom',$id)
			->getOne();
		if( is_object($notification) ){
			$notification->set(array('view'=>1))->save();
		}

		//prendo gli stati	
		$stati = CartStatus::prepareQuery()->get();
		foreach($stati as $v){
			$status[$v->label] = "<span class='label' style='background:".$v->color."'>".strtoupper($v->get('name'))."</span>";
		}

		
		
		$cart->status_display = $status[$cart->status];
		
		
		//se il carrello è stato effettuato da un utente registrato allora prelevo tutti i suoi altri carrelli
		if($cart->user){
			$other = Cart::prepareQuery()->where('user',$cart->user)
								->where('status','active','<>')
								->where('status','canceled','<>')
								->where('status','waiting','<>')
								//->where('id',$cart->id,'<>')
								->orderBy('evacuationDate','DESC')
								->get();
			$total_other = 0;
			$total_shipping_other = 0;
			if(okArray($other)){
				foreach($other as $v){
					$v->status_display = $status[$v->status];
					$total_other += $v->getTotal();
					$total_shipping_other += $v->shippingPrice+$v->paymentPrice;
				}
			}
			$template_var['total_other'] = $total_other;
			$template_var['total_other_witout_VAT'] = Eshop::removeVATFromPrice($total_other);
			$template_var['total_other_VAT'] = Eshop::extractVATFromPrice($total_other);
			$template_var['total_shipping_other'] = $total_shipping_other;
			$template_var['count_other'] = count($other);
			$template_var['other'] = $other;

		}
		
		//prendo gli ordini del carrello
		$ordini = $cart->getOrders();
			
		foreach($ordini as $k => $ord){
			$prodotto = $ord->getProduct();		
			if(is_object($prodotto)){
				if($prodotto->hasParent()){
					$ordini[$k]->parent = $prodotto->parent;
				}else{
					$ordini[$k]->parent = $prodotto->id;
				}
				$ordini[$k]->category = $prodotto->getFullNameSection();
				$ordini[$k]->productname = $prodotto->getName();
				$ordini[$k]->sku = $prodotto->get('sku');
				$ordini[$k]->link = $prodotto->getUrl();
				$ordini[$k]->img = $prodotto->getUrlImage(0,'thumbnail');
			}
		}


		//prendo la storia del carrello
		$history = $cart->getHistory();
		
		
		


		if (Marion::exists_action('ecommerce_order_view')){
			Marion::do_action('ecommerce_order_view',array(&$cart,&$ordini));
		}
		
		

		$template_var['ordini'] = $ordini;
		$template_var['cart'] = $cart;
		if(okArray($history)){
			$template_var['history'] = $history;
		}
		
		
			
		


		//creo il form per il cambio di stato dell'ordine
		$datachangeStatusOrder['status'] = $cart->status;
		$datachangeStatusOrder['cartId'] = $cart->id;
		
		
		$dataform_change_status = $this->getDataForm('changeStatus',$datachangeStatusOrder,$this);
		$this->setVar('dataform_change_status',$dataform_change_status);
		
		/*if( $cart->recurrentPayment ){
			
			$opz_status = $elements['formdata[status]']->children;
			foreach($opz_status as $k =>$v){
				
				if( !in_array($v->attributes['value'],array('active_subscription','cancelled_subscription')) ){
					unset($opz_status[$k]);
				}
			}
			$elements['formdata[status]']->children = array_values($opz_status);
			//debugga($opz_status);exit;
			//debugga($elements['formdata[status]']->children);exit;
		}*/

		//creo il form per l'invio di una mail al cliente
		$emailCart['email'] = $cart->email;
		$emailCart['cartId'] = $cart->id;

		$this->setVar('dataform_send_mail_cart',$emailCart);
		
		$template_var['enable_invoice'] = Marion::getConfig('eshop','enableInvoice');
		

		/*if( $cart->recurrentPayment ){
			
			if( $cart->paymentMethod == 'PAYPAL' ){
				

				$info_payment = $cart->getStatusRecurrentPaymentPaypal();
			
				$frequencyPayment = $cart->getFrequencyPaymentPaypal();
				$template->description_recurrent_payment = $frequencyPayment['description'];

				if( $info_payment['next_payment'] ){
					$template->next_payment_recurrent_payment = $info_payment['next_payment'];
				}
			}
		}*/

		foreach($template_var as $k => $v){
			$this->setVar($k,$v);
		}


		
		
		$this->output('form_order.htm');

		

	}

	

	function getList(){
		
		$fields = "c.id,c.evacuationDate,c.number,c.paymentDate,c.shippingDate,c.status,c.name,c.surname,c.total,c.user,c.shippingPrice,c.paymentPrice,c.paymentMethod,c.shippingMethod,c.comesFrom,c.currency,c.archived,c.hasInvoice";

		$database = Marion::getDB();
		
		$condizione = "1=1 AND ";

		foreach($this->excluded as $v){
			$condizione .= " status <> '{$v}' AND ";
		}

		$limit = $this->getListContainer()->getPerPage();
		
		if( $number = _var('number') ){
			$condizione .= "number LIKE '%{$number}%' AND ";
		}
		
		
		if( $dateStart = _var('dateStart') ){
			$tmp = explode('/',$dateStart);
			$dateStart =$tmp[2]."-".$tmp[1].'-'.$tmp[0];
			$condizione .= "c.evacuationDate >= '{$dateStart}' AND ";
		}

		if( $dateEnd = _var('dateEnd') ){
			$tmp = explode('/',$dateEnd);
			$dateEnd =$tmp[2]."-".$tmp[1].'-'.$tmp[0];
			$condizione .= "c.evacuationDate <= '{$dateEnd}' AND ";
		}

		if( $paymentMethod = _var('paymentMethod') ){
			$condizione .= "paymentMethod LIKE '%{$paymentMethod}%' AND ";
		}

		if( $comesFrom = _var('comesFrom') ){
			$condizione .= "comesFrom LIKE '%{$comesFrom}%' AND ";
		}

		if( $id = _var('id') ){
			$condizione .= "id = {$id} AND ";
		}

		if( $status = _var('status') ){
			$condizione .= "status = '{$status}' AND ";
		}

		if( $name = _var('name') ){
			$condizione .= "(name LIKE '{$name}' OR surname LIKE '{$name}') AND ";
		}
		
		

	

		$registred = _var('registred');
		if( isset($_GET['registred']) && $registred != -1 ){
			if( $registred ){
				$condizione .= "user > 0 AND ";
			}else{
				$condizione .= "(user IS NULL OR user = 0) AND ";
			}
		}

		$image = _var('image');
		if( isset($_GET['image']) && $image != -1 ){
			$images = serialize(array());
			
			if( $image ){
				$condizione .= "images <> '{$images}' AND ";
			}else{
				$condizione .= "images = '{$images}' AND ";
			}
			
		}
		
		$condizione = preg_replace('/AND $/','',$condizione);
		

		$tot = $database->select('count(*) as tot','cart',$condizione);

		
		

		if( $order = _var('orderBy') ){
			$order_type = _var('orderType');
			$condizione .= " ORDER BY {$order} {$order_type}";
		}else{
			$condizione .= " ORDER BY evacuationDate DESC";
		}


		$condizione .= " LIMIT {$limit}";
		if( $page_id = _var('pageID') ){
			$condizione .= " OFFSET ".(($page_id-1)*$limit);
			
		}

		
		$list = $database->select("{$fields}, c.total+c.shippingPrice+c.paymentPrice as grantotal",'cart as c',"{$condizione}");
		
		$total_items = $tot[0]['tot'];
		if( $total_items > 0 ){
			$this->getListContainer()
			->setDataList($list)
			->setTotalItems($total_items);

		}else{
			$this->getListContainer()
			->setTotalItems(0);
		}
		


	}


	function displayList(){
		$this->setMenu('manage_orders');
		$this->showMessage();
	
		$this->status_name = array();
		$status_list = array('--SELECT--');
		$stati = CartStatus::prepareQuery()->get();
		
		foreach($stati as $v){
			if( in_array($v->label,$this->excluded)) continue;
			$status_list[$v->label] = strtoupper($v->get('name'));
			$this->status_name[$v->label] = "<span class='label' style='background:".$v->color."'>".strtoupper($v->get('name'))."</span>";
		
		}
		

		$fields = array(
			
			0 => array(
				'name' => 'ID',
				'field_value' => 'id',
				'searchable' => true,
				'sortable' => true,
				'sort_id' => 'id',
				'search_name' => 'id',
				'search_value' => '',
				'search_type' => 'input',
			),
			1 => array(
				'name' => 'numero',
				'field_value' => 'number',
				'sortable' => true,
				'sort_id' => 'number',
				'searchable' => true,
				'search_name' => 'number',
				'search_value' => _var('number'),
				'search_type' => 'input',
			),
			2 => array(
				'name' => 'Origine',
				'field_value' => 'comesFrom',
				'sortable' => true,
				'sort_id' => 'comesFrom',
				'searchable' => true,
				'search_name' => 'comesFrom',
				'search_value' => _var('comesFrom'),
				'search_type' => 'input',
			),
			3 => array(
				'name' => 'Registrato?',
				'function_type' => 'row',
				'function' => function($row){
					if( $row['user'] ){
						return 'SI';
					}else{
						return 'NO';
					}
				},
				'sortable' => true,
				'sort_id' => 'registred',
				'searchable' => true,
				'search_name' => 'registred',
				'search_value' => (isset($_GET['registred']))? _var('registred'):-1,
				'search_type' => 'select',
				'search_options' => array(
					-1 => '--SELECT--',
					0 => 'NO',
					1 => 'SI',
					
				),
			),
			4 => array(
				'name' => 'cliente',
				'function' => function($row){
					return $row['name']." ".$row['surname'];
				},
				'function_type' => 'row',
				'sortable' => true,
				'sort_id' => 'name',
				'searchable' => true,
				'search_name' => 'name',
				'search_value' => _var('name'),
				'search_type' => 'input',
			),
			5 => array(
				'name' => 'Metodo di pagamento',
				'field_value' => 'paymentMethod',
				'sortable' => true,
				'sort_id' => 'paymentMethod',
				'searchable' => true,
				'search_name' => 'paymentMethod',
				'search_value' => _var('paymentMethod'),
				'search_type' => 'input',
			),

			6 => array(
				'name' => 'Data',
				'field_value' => 'evacuationDate',
				'sortable' => true,
				'sort_id' => 'evacuationDate',
				'searchable' => true,
				'search_name1' => 'dateStart',
				'search_name2' => 'dateEnd',
				'search_value1' => _var('dateStart'),
				'search_value2' => _var('dateEnd'),
				'search_type_value1' => 'date',
				'search_type_value2' => 'date',
				'search_type' => 'range',

			),
			7 => array(
				'name' => 'Stato',
				'function_type' => 'row',
				'function' => function($row){
					return array_key_exists($row['status'],$this->status_name)?$this->status_name[$row['status']]:'';
				},
				'sortable' => true,
				'sort_id' => 'status',
				'searchable' => true,
				'search_name' => 'status',
				'search_value' => _var('status'),
				'search_type' => 'select',
				'search_options' => $status_list
			),
			
			

		);

		if( Marion::getConfig('eshop','enableInvoice') ){
			$fields[8] = array(
				'name' => '',
				'function_type' => 'row',
				'function' => function($row){
					$html = '';
					if ($row['hasInvoice'] == 1 ){
							$url = $this->getUrlScript()."&action=download_invoice&id={$row['id']}";
							$html = "<a href='{$url}' target='_blank' class='btn btn-sm btn-default'><i class='fa fa-file-text-o'></i></a>";
					}
					return $html;
				},
			);
		}

		$container = $this->getListContainer()
			->setFieldsFromArray($fields)
			->enableExport(true)
			->enableBulkActions(true)
			->addActionBulkButtons(
				[
					(new ListActionBulkButton('download_invoices'))
					->setConfirm(true)
					->setConfirmMessage('Sicuro di voler scaricare le fatture degli ordini selezionati?')
					->setText('scarica fatture')
					->setIcon('fa fa-download'),
					(new ListActionBulkButton('create_invoices'))
					->setConfirm(true)
					->setConfirmMessage('Sicuro di voler generare le fatture per gli ordini selezionati?')
					->setText('genera fatture')
					->setIcon('fa fa-file-text-o'),
					(new ListActionBulkButton('print_orders'))
					->setConfirm(true)
					->setConfirmMessage('Sicuro di voler stampare gli ordini selezionati?')
					->setText('stampa ordini')
					->setIcon('fa fa-print'),
					
					
				]
			)
			->addEditActionRowButton()
			->addCopyActionRowButton()
			->addDeleteActionRowButton()
			->build();


		
		$this->setTitle('Ordini');
		$this->resetToolButtons();
		$this->getList();

		parent::displayList();
	}




	function showMessage(){
		$bulk_success = _var('bulk_success');
		switch($bulk_success){
			case 'create_invoices':
				$this->displayMessage('Fatture generate con successo','success');
				break;
			case 'delete':
				$this->displayMessage('Ordini eliminati con successo','success');
				break;
			
		}
		
		if( _var('deleted') ){
			$this->displayMessage('Ordine eliminato con successo','success');
		}

	}


	function bulk(){
		$action = $this->getBulkAction();
		$ids = $this->getBulkIds();


		switch($action){
			
			case 'delete':
				foreach($ids as $id){
					$obj = Cart::withId($id);
					if( is_object($obj) ){
						$obj->changeStatus('deleted');
					}
				}
				break;
			case 'download_invoices':
				$files = array();
				foreach($ids as $id){
					$obj = Cart::withId($id);
					if( is_object($obj) && $obj->hasInvoice ){
						$files[] = $obj->getPathInvoice();
					}
				}
				if( okArray($files) ){

					
					$zip = new ZipArchive();
					$zip_file = tempnam("tmp", "zip");
					if ($zip->open($zip_file, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) == TRUE) {
						foreach($files as $i => $file){	
							 $explode = explode('/',$file); 
							 $zip->addFromString($explode[count($explode)-1], file_get_contents($file) );
						}
					}
					
					$zip->close();
					header('Content-Type: application/zip');
					header('Content-Length: ' . filesize($zip_file));
					header('Content-Disposition: attachment; filename="invoices.zip"');
					readfile($zip_file);
					unlink($zip_file); 

					exit;
				}
				
				break;
			case 'print_orders':
				$zip = new ZipArchive();
				$zip_file = tempnam("tmp", "zip");
				if ($zip->open($zip_file, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE) == TRUE) {
					foreach($ids as $id){
						
						$obj = Cart::withId($id);
						if( is_object($obj) ){
							$string = $this->printOrder($obj,true);
							
							$zip->addFromString("order_".$obj->id.".pdf", $string );
							
						}
					}
				}
				
				$zip->close();
				header('Content-Type: application/zip');
				header('Content-Length: ' . filesize($zip_file));
				header('Content-Disposition: attachment; filename="invoices.zip"');
				readfile($zip_file);
				unlink($zip_file); 

				exit;
				
				
				break;
			case 'create_invoices':
				foreach($ids as $id){
					$obj = Cart::withId($id);
					if( is_object($obj) && !$obj->hasInvoice ){
						$obj->createInvoice();
					}
				}
				break;

			
		}
		parent::bulk();
	}






	function delete(){
		$id = $this->getID();
		$obj = Cart::withId($id);
			
		if( is_object($obj) ){
			$obj->changeStatus('deleted');
		}
		parent::delete();
	}



	function setMedia(){
		$this->registerJS($this->getBaseUrl().'modules/ecommerce/js/admin/orders.js','end');
		$action = $this->getAction();
		if( $action == 'list'){
			$this->registerJS($this->getBaseUrl().'plugins/bootstrap-datepicker/bootstrap-datepicker.js','head');
			$this->registerJS($this->getBaseUrl().'plugins/bootstrap-datepicker/locales/bootstrap-datepicker.it.js','head');
		}
		

		
	}


	function createInvoice(){
		$id = $this->getId();
	
		$cart = Cart::withId($id);
		
		$cart->createInvoice();
		$cart->showInvoice();
		exit;
	}

	function downloadInvoice(){
		$id = $this->getID();

		if( (int)$id ){
			$list[] = $id; 
		}else{
			$list = (array)json_decode($id);
		}

		if( count($list) <= 1 ){
	
			$cart = Cart::withId($id);
			$cart->showInvoice();
			exit;

		}else{

	
			$files = array();
			$database = Marion::getDB();
			foreach($list as $v){
				$invoice = $database->select('*','invoice',"cartId={$v}");
				
				if( okArray($invoice) ){
					$path = $invoice[0]['path'];
					$files[] = $path;
					
				}
			}
			if( okArray($files) ){

				$zip = new \ZipArchive();
				$filename = sys_get_temp_dir()."/invoices.zip";
				if ($zip->open($filename, \ZipArchive::CREATE)!==TRUE) {
					exit("cannot open <$filename>\n");
				}
				
				foreach($files as $f){
					if( file_exists($f) ){
						$download_file = file_get_contents($f);
						$zip->addFromString(basename($f),$download_file);
					}
				}
				$zip->close();
				header("Content-type: application/zip"); 
				header("Content-Disposition: attachment; filename=invoices.zip"); 
				header("Pragma: no-cache"); 
				header("Expires: 0"); 
				readfile("$filename");
				exit;
			}else{
				$this->redirectToList(array('no_invoices'=>1,'archived'=>_var('archived')));
			}
		}


		
	}

	/**
	 * printOrder
	 * 
	 */

	function printOrder($cart,$buffer=false){
		
		
		$this->setVar('cart',$cart);
		$logo = "http://" . $_SERVER['SERVER_NAME']._MARION_BASE_URL_."img/".Marion::getConfig('eshop','logoInvoice')."/or/logo.png";

		$this->setVar('path_base',"http://" . $_SERVER['SERVER_NAME']._MARION_BASE_URL_);
		$this->setVar('logo',$logo);
		ob_start();
		$this->output('print_order.htm');
		$html = ob_get_contents();
		ob_end_clean();

		
		
		$pdf = _obj('PDF2');
		$pdf->setHtml($html);
		$pdf->setPageSize(Wkhtmltopdf::SIZE_A4);
		$pdf->setOptions(" --disable-smart-shrinking --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0");
		if( $buffer ){
			return $pdf->output(Wkhtmltopdf::MODE_STRING);
				
		}else{
			$pdf->output(Wkhtmltopdf::MODE_DOWNLOAD,"order_".$cart->id.".pdf");
		}
		
	}


	function ajax(){
		
		$action = $this->getAction();
		
		switch($action){
			case 'manage_order_user':
				$id = $this->getID();
				$obj = Cart::withId($id);
				$type = _var('type');
				if( $type == 'enabled'){
					$_SESSION['ADMIN_CART_USER_MODIFY'] = $id;
					$obj->changeStatus('active');
					$_SESSION['ADMIN_CART_USER_MODIFY_NUMBER'] = $obj->number;
				}else{
					unset($_SESSION['ADMIN_CART_USER_MODIFY']);
					unset($_SESSION['ADMIN_CART_USER_MODIFY_NUMBER']);
				}
				$risposta = array('result' => 'ok');
				break;
			case 'update_history':
				$risposta = $this->getHistory();
				break;
			case 'update_tracking':
				$formdata = $this->getFormdata();
				if( $formdata['cartId'] ){
					$cart = Cart::withId($formdata['cartId']);
					if( is_object($cart) ){
						$cart->set(
									array( 'trackingCode' => $formdata['trackingCode']
									))->save();
					}
				}
				$risposta = array(
					'result' => 'ok'
				);
				break;
		}

		echo json_encode($risposta);
		
	}

	function getHistory(){
		$id = $this->getID();
		$cart = Cart::withId($id);
		$history = $cart->getHistory();
		if(okArray($history)){
			$stati = CartStatus::prepareQuery()->get();
			foreach($stati as $v){
				$template_var['status'][$v->label] = "<span class='label' style='background:".$v->color."'>".strtoupper($v->get('name'))."</span>";
				$template_var['status_color'][$v->label] = $v->color;
				$template_var['status_name'][$v->label] = strtoupper($v->get('name'));
				$template_var['status_email'][$v->label] = $v->send_mail?1:0;
				$template_var['status_sent'][$v->label] = $v->sent?1:0;
			
			}
			foreach($template_var as $k => $v){
				$this->setVar($k,$v);
			}
			$this->setVar('history',$history);
			ob_start();
			$this->output('order_status_history.htm');
			$html = ob_get_contents();
			ob_end_clean();
		}
		$status = CartStatus::withLabel($cart->status);
		if( is_object($status) ){
			$span = "<span style=\"color: {$status->color}\">".strtoupper($status->get('name'))."</span>";
		}
		$risposta = array(
			'result' => 'ok',
			'history' => $html,
			'status' => $span
			);
		if( $cart->datePayment){
			$risposta['datePayment'] = strftime('%d/%m/%Y',strtotime($cart->datePayment));
		}
		if( $cart->dateShipping){
			$risposta['dateShipping'] = strftime('%d/%m/%Y',strtotime($cart->dateShipping));
		}
		return $risposta;
	}
	function unarchiveOrder(){
		$id = $this->getID();

		if( (int)$id ){
			$list[] = $id; 
		}else{
			$list = (array)json_decode($id);
		}
		foreach($list as $v){
			$cart = Cart::withId($v);
			$cart->archived = 0;
			
			$cart->save();
		}
		
		
		
		if( count($list) > 1 ){
			$this->redirectTolist(array('archived'=>1,'unstored_multiple'=>1));
		}else{
			$this->redirectTolist(array('archived'=>1,'unstored_multiple'=>1));
		}
	}


	function archiveOrder(){
		$id = $this->getID();

		if( (int)$id ){
			$list[] = $id; 
		}else{
			$list = (array)json_decode($id);
		}
		foreach($list as $v){
			$cart = Cart::withId($v);
			$cart->archived = 1;
			
			$cart->save();
		}
		if( count($list) > 1 ){
			$this->redirectTolist(array('stored_multiple'=>1));
		}else{
			$this->redirectTolist(array('stored'=>1));
		}

	}








	// TWIG FUNCTION 
	function array_status_cart(){

		$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->get();
		foreach($status_avaiables as $v){
			$stati[$v->label] = $v->get('name');
		}
		return $stati;
	}

	function array_status_cart2(){
		$toreturn[0] = __('seleziona');
		$stati = $this->array_status_cart();
		$toreturn = array_merge($toreturn,$stati);
		return $toreturn;
	}

	function array_code_payments(){
		$payments = PaymentMethod::prepareQuery()->where('enabled',1)->get();
		
		$toreturn[0] = __('seleziona');
		foreach($payments as $v){
			$toreturn[$v->code] = $v->code;
		}

		return $toreturn;
	}

}



?>