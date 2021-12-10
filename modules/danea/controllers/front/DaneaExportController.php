<?php
class DaneaExportController extends FrontendController{
	public $enable_export = true;
	public $id_shop = 1;
	public $id_inventory = 1;
	public $currency = '€'; 
	
	/* VARIABILI MODULO DANEA */
	public $limit_days = 2;

	public $enable_credentials = false;
	public $username = '';
	public $password = '';


	public $use_import_setting = true;
	
	

	public $status_orders = array('confirmed'); // array contenebte gli stati degli ordini da esportare

	public $unita_misura = 'pz'; // uniotà di misura dei prodotti
	
	
	// mappatura degli attributi dei prodotti con gli attributi Taglia Colore di Danea
	public $mappping_attributes = array(
		'taglia' => 'Size',
		'colore' => 'Color',
		'lunghezza' => 'Size',
		'lettera' => 'Size'
	);
	
	// mappatura dei codici di pagamento di Danea con quello di Marion
	public $mapping_payments = array(
		'PAYPAL' => 'PayPal',
		'contrassegno' => 'contrassegno',
		'bonifico' => 'bonifico',
	);
	

	//codice identificativo del prodotto 
	public $cod_danea = 'sku'; //valori ammessi: id,ean,upc
	

	//tassa di default
	public $default_tax = 22;
	

	//mappatura delle tasse di MArion con quelle di Danea
	public $mapping_taxes = array(
		1 => '22',
		2 => '10',
		3 => '4',
	);
	
	

	public $manage_discount_like_product = 1;
	public $discount_code = 'COUPON';
	public $discount_vat = '22';

	public $manage_shipping_like_product = 0;
	public $shipping_code = 'SPEDIZIONE';
	public $shipping_vat = '22';

	public $manage_payment_like_product = 1;
	public $payment_code = 'PAGAMENTO';
	public $payment_vat = '22';



	/****** IMPORT **********/
	/*
	stabilisce quale insieme attributi utilizzare per il prodotto se ha variazioni. Se l'insieme attributi ha due variazioni la prima variazione è Color e la seconda è Size. Se l'insieme attributi ha un'unica variazione viene presa la prima variazione non nulla nell'ordine Color, Size
	N.B. l'insieme attributi deve essere già creato sull'ecommerce e il prodotto su Danea deve essere un articolo con magazzino (taglie/colori)
	*/
	/*public $_field_attribute_set = 'CustomField1';
	
	//stabilisce se il codice artcicolo del prodotto figlio deve essere generato in automatico o meno
	public $sku_child_dinamic = true;

	//stabilisce quale è il codice articolo (sku) del prodotto figlio
	public $sku_child = 'parent_sku'; // valori ammessi: 'parent_sku', 'barcode'

	//stabilisce a quale campo del prodotto deve essere assegnato il barcode
	public $map_barcode_field = 'ean'; //ean,upc 
	
	public $default_price_list = 1; //listino prezzo di default
	public $prices_with_tax = true; //stabilisce se i prezzi in fase di importazione sono già ivati
	

	//permette di mappare i listini di danea in listini di Marion
	public $mapping_price_list = array(
		2 => 'special',
		3 => 'saldi'
	);


	public $mapping_features = array(
		'CustomField1' => 1, //id di una feature (o caratteristica)
		'CustomField2' => 2,
		'CustomField3' => 3,
	);

	public $disable_update_fields = array(
		'section',
		'description',
		'name',
		//'quantity',

	);

	public $create_variations_on_import = false;
	public $create_categories_on_import = false;


	public $path_xml_products = 'xml/articoli.xml';
	
	*/

	function getParametersExport(){
		
		$dati = Marion::getConfig('danea_setting');
		
		$this->enable_export = $dati['enable_export'];
		$this->enable_credentials = $dati['enable_credentials'];
		$this->username = $dati['username'];
		$this->password = $dati['password'];

		
		$this->status_orders = unserialize($dati['status_orders']);
		$this->mappping_attributes = unserialize($dati['mapping_attributes']);
		$this->mapping_payments = unserialize($dati['mapping_payments']);
		$this->mapping_taxes = unserialize($dati['mapping_taxes']);

		
		$this->manage_discount_like_product = $dati['manage_discount_like_product'];
		$this->manage_shipping_like_product = $dati['manage_shipping_like_product'];
		$this->manage_payment_like_product = $dati['manage_payment_like_product'];
		$this->discount_code = $dati['discount_code'];
		$this->shipping_code = $dati['shipping_code'];
		$this->payment_code = $dati['payment_code'];
		$this->limit_days = $dati['limit_days'];
		$this->payment_vat = $dati['payment_vat'];
		$this->shipping_vat = $dati['shipping_vat'];
		$this->discount_vat = $dati['discount_vat'];
		$this->use_import_setting = $dati['use_import_setting'];

		if( $this->use_import_setting ){
			$this->getMappingAttributesImport($dati);
		}
		
	
	}

	function getMappingAttributesImport($dati){

		
		$this->mappping_attributes = array();
		if( $dati['manage_variations_import_advanced'] ){
			$attribute_sets = unserialize($dati['mapping_attribute_sets']);
			foreach($attribute_sets as $set => $values){
				foreach($values as $k =>$v){
					$this->mappping_attributes[$k] = ucwords($v);
				}
			}
			//debugga($attribute_sets);exit;
		}else{
			//debugga($this);
			
			
			$this->mappping_attributes[$dati['mapping_color']] = 'Color';
			$this->mappping_attributes[$dati['mapping_size']] = 'Size';
		}

		
		
	}

	function getOrders(){
		$where_status = '';
		
		
		
		
		foreach($this->status_orders as $status){
			$where_status .= "'{$status}',";
		}
		$where_status = preg_replace('/\,$/','',$where_status);
		$query = Cart::prepareQuery()->whereExpression("(status IN ({$where_status}))")->orderBy('evacuationDate','ASC');
		
		if( $this->limit_days ){
			$date = strftime('%Y-%m-%d',strtotime("-{$this->limit_days} days"));
			
			$query->where('evacuationDate',$date,'>=');
			
			
			
		}
		$carts = $query->get();
		
		
		return $carts;
	}
	

	function display(){


		$action = $this->getAction();
		switch($action){
			case 'products':
				$this->exportProducts();
				break;
			default:
				
				$this->exportOrders();
				break;
		}

	}

	function checkExistsTableInfoShop(){
		$database = _obj('Database');
		$sel = $database->execute("SHOW TABLES LIKE 'product_shop_values'");
		return okArray($sel);
	}
	

	function getCategoria($id_section,$section_info=array(), &$list=array()){
		$info = $section_info[$id_section];
		if( !okArray($list) ){
			$list = [];
		}
		if( $info ){
			$list[] = $info['name'];
			if( $info['parent'] ){
				return $this->getCategoria($info['parent'],$section_info,$list);
			}
		}
		return $list;
	}
	

	function exportProducts(){
		$database = _obj('Database');
		$new_marion = $this->checkExistsTableInfoShop();

		$sections = Section::prepareQuery()->get();
		$section_info = array();
		foreach($sections as $v){
			$section_info[$v->id] = Array(
				'name' => $v->get('name'),
				'parent' => $v->parent
			);
		}
		
		$insiemi_attributi = $database->select('*','attributeSet');
		foreach($insiemi_attributi as $v){
			$set = AttributeSet::withId($v['id']);
			/*if( is_object($set) ){
				$composition = $set->getComposition();
				debugga($composition);exit;
			}*/
			$attribute_set_name[$set->id] = $set->label;
		}
		
		
		
		
		if( !$new_marion ){
			$prodotti = $database->select(
				'id,sku,section,attributeSet,type,parent,name,stock,taxCode,minOrder',
				'product as p left outer join productLocale as l on l.product=p.id',
				"locale='{$GLOBALS['activelocale']}' AND p.parent=0 AND p.deleted=0"
			);
		}else{
			$prodotti = $database->select(
				'id,sku,section,attributeSet,type,parent,name,virtual_product',
				'product as p left outer join productLocale as l on l.product=p.id',
				"locale='{$GLOBALS['activelocale']}' AND p.parent=0 AND p.deleted=0"
			);
		}

		

		foreach($prodotti as $v){

			$categorie = $this->getCategoria($v['section'],$section_info);
			if( $new_marion ){
				$info_shop = $database->select('*','product_shop_values',"id_product={$v['id']} AND id_shop={$this->id_shop}");
				

				if( okArray($info_shop) ){
					$v['taxCode'] = $info_shop[0]['id_tax'];
					$v['minOrder'] = $info_shop[0]['min_order'];
				}

				$inventory_shop = $database->select('*','product_inventory',"id_product={$v['id']} AND id_inventory={$this->id_inventory}");

				if( okArray($inventory_shop) ){
					$v['stock'] = $inventory_shop[0]['quantity'];
				}

				
				$prezzo = $database->select('*','price',"product={$v['id']} AND label='default' AND quantity=1 AND id_shop={$this->id_shop}");
			}else{
				$prezzo = $database->select('*','price',"product={$v['id']} AND label='default' AND quantity=1");
			}

			
			if( okArray($prezzo) ){
				$v['price'] = $this->currency." ".Eshop::formatMoney($prezzo[0]['value']);
				
			}
		
			
			rsort($categorie);
			
			$toreturn[] = array(
				'Cod.' => $v['sku'],
				'Descrizione' => $v['name'],
				'Categoria' => $categorie[0],
				'Sottocategoria' => $categorie[1],
				'Cod. Iva' => $this->mapping_taxes[$v['taxCode']]?$this->mapping_taxes[$v['taxCode']]:$this->default_tax,
				'Listino 1' => $v['price'],
				'Extra 1' => $attribute_set_name[$v['attributeSet']],
				'Tipologia' => $attribute_set_name[$v['attributeSet']]?'Art. con magazzino (taglie/colori)':'Articolo con magazzino',
				'Q.tà giacenza' => $v['stock']

			);


		}		
		$testata = array_keys($toreturn[0]);
		

		$html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html lang=it><head><title>Danea Export</title></head><body><table border="1">';
		
		$html .= "<tr>";
	   foreach($testata as $a)
	   {
		 
		  $html .=  "<td>".$a."</td>";
	   }
	   $html .="</tr>";

		foreach($toreturn as $dati)
		{
		   $html .= "<tr>";
		   foreach($dati as $a)
		   {
			 
			  $html .= "<td>".$a."</td>";
		   }
		   $html .="</tr>";
		}

		$html .= "</table></body></html>";
		$filename="danea.xls";
	    header('Content-Transfer-Encoding: binary');
		header("Content-Type: application/octet-stream"); 
		header("Content-Transfer-Encoding: binary"); 
		header('Expires: '.gmdate('D, d M Y H:i:s').' GMT'); 
		header('Content-Disposition: attachment; filename = "Export '.date("Y-m-d").'.xls"'); 
		header('Pragma: no-cache'); 
		echo chr(255).chr(254).iconv("UTF-8", "UTF-16LE//IGNORE", $html);
		exit;
		debugga($html);exit;

		debugga($section_info);exit;

		debugga('qua');exit;
	}

	function checkCredentials(){
		if( !$this->enable_credentials ){ 
			return true;
		}else{
			list($username,$password) = explode(':',base64_decode($_SERVER['HTTP_X_AUTHORIZATION']));
			if( $username == $this->username && $this->password == $password ){
				return true;
			}else{
				return false;
			}
			
		}
	}



	
	

	function exportOrders(){
		require_once('modules/danea/classes/DaneaExport.php');
		require_once('modules/danea/classes/DaneaExportOrder.php');
		$this->getParametersExport();
		if( !$this->enable_export ){
			 echo "EXPORT DISABLED";
			 exit;
		  }
		if( !$this->checkCredentials() ){
			echo 'WRONG CREDENTIALS';
			exit;
		}
		$carts = $this->getOrders();

		$nazioni = Country::getAll();
		foreach($nazioni as $v){
			$nome_nazione[$v->id] = $v->get('name');
		}

		//debugga($carts);exit;
		foreach($carts as $cart){
			$order = new DaneaExportOrder;
			
		
			$cart->country = $nome_nazione[$cart->country];
			$cart->shippingCountry = $nome_nazione[$cart->shippingCountry];
			
			
			
		
			$customer_data = array(
				'CustomerName' => $cart->company?$cart->company:$cart->name." ".$cart->surname,
				'CustomerAddress' => $cart->address,
				'CustomerCity' => $cart->city,
				'CustomerProvince' => $cart->province,
				'CustomerCountry' => $cart->country,
				'CustomerWebLogin' => $cart->username,
				'CustomerPostcode' => $cart->postalCode,
				'CustomerTel' => $cart->phone,
				'CustomerCellPhone' => $cart->cellular,
				'CustomerEmail' => $cart->email,
				'CustomerPec' => $cart->pec,
				'CustomerEInvoiceDestCode' => $cart->codice_univoco
			);
			if( $cart->company ){
				$customer_data['CustomerReference'] = $cart->name." ".$cart->surname;
			}
			
			//imposto di dati di fatturazione
			$order->setCustomerData( $customer_data );
			

			
			//imposto i dati di spedizione
			$order->setAddressData(
				array(
					'DeliveryName' => $cart->shippingName." ".$cart->shippingSurname,
					'DeliveryAddress' => $cart->shippingAddress,
					'DeliveryPostcode' => $cart->shippingPostalCode,
					'DeliveryCity' => $cart->shippingCity,
					'DeliveryProvince' => $cart->shippingProvince,
					'DeliveryCountry' => $cart->shippingCountry,
				)
			);
			
			//aggiungo informazioni sul pagamento
			$order->addPayment(
				array(
					'Date' => $cart->paymentDate?strftime('%Y-%m-%d',strtotime($cart->paymentDate)):strftime('%Y-%m-%d',strtotime($cart->evacuationDate)),
					'Amount' => $cart->total+$cart->paymentPrice+$cart->shippingPrice-$cart->discount,
					'Paid' => true,
				)
			);

			$rows = $cart->getOrders();
			
			foreach($rows as $v){
				$product = $v->getProduct();
				
				$sku ='';

				
				$tax = '';
				$variazioni = array();
				if( is_object($product)  ){
					$cod_danea = $this->cod_danea;
					
					if( $product->parent ){
						$parent = $product->getParent();
						$sku = $parent->$cod_danea;
					}else{
						$sku = $product->$cod_danea;
					}
					


					$tax = $this->mapping_taxes[$product->taxCode];
					$attributes = $product->getAttributes();
					if(okArray($attributes) ){
						foreach( $attributes as $label => $id_val){
							$value = AttributeValue::withId($id_val);
							
							if(is_object($value)){
								$valore = $value->get('value');
								
							}else{
								$valore = $id_val;
							}
							$variazioni[$this->mappping_attributes[$label]] = $valore;
						}
					}
				}
				
				$order->addRow(
					array(
						'Code' => $sku,
						'Description' => $product?$product->get('name'):'',
						'Qty' => $v->quantity,
						'Um' => $this->unita_misura,
						'Size' => $variazioni['Size'],
						'Color' => $variazioni['Color'], //
						'Price' => $v->price,
						'VatCode' => $tax?$tax:$this->default_tax
					)
				);
			}
			
			$costi_aggiuntivi = 0;
			$desc_costi_aggiuntivi = '';
			if( $this->manage_discount_like_product ){
				if( $cart->discount ){
					$order->addRow(
					array(
						'Code' => $this->discount_code,
						'Qty' => 1,
						'Um' => $this->unita_misura,
						'Price' => -$cart->discount,
						'VatCode' => $this->discount_vat
						)
					);
				}
			}else{
				if( $cart->discount ){
					$costi_aggiuntivi += -$cart->discount;
					$desc_costi_aggiuntivi .= "|sconto";
				}
			}
			if( $this->manage_shipping_like_product ){
				if( $cart->shippingPrice ){
					$order->addRow(
					array(
						'Code' => $this->shipping_code,
						'Qty' => 1,
						'Um' => $this->unita_misura,
						'Price' => $cart->shippingPrice,
						'VatCode' => $this->shipping_vat
						)
					);
				}
			}else{
				if(  $cart->shippingPrice ){
					$costi_aggiuntivi = $cart->shippingPrice;
					$desc_costi_aggiuntivi .= "|spedizione";
				}
			}
			if( $this->manage_payment_like_product ){
				if( $cart->paymentPrice ){
					$order->addRow(
					array(
						'Code' => $this->payment_code,
						'Qty' => 1,
						'Um' => $this->unita_misura,
						'Price' => $cart->paymentPrice,
						'VatCode' => $this->payment_vat
						)
					);
				}
			}else{
				if( $cart->paymentPrice ){
					$costi_aggiuntivi = $cart->paymentPrice;
					$desc_costi_aggiuntivi .= "|pagamento";
				}
			}

			$desc_costi_aggiuntivi = trim($desc_costi_aggiuntivi,'|');
			
			//imposto i dati generali dell'ordine
			$order->setData(
				array(
					'Date' => strftime('%Y-%m-%d',strtotime($cart->evacuationDate)),
					'Number' => $cart->id,
					'Total' => $cart->total+$cart->paymentPrice+$cart->shippingPrice-$cart->discount,
					'PaymentName' => $this->mapping_payments[$cart->paymentMethod],
					'CostAmount' => $costi_aggiuntivi,
					'CostDescription' => $desc_costi_aggiuntivi,
					'PricesIncludeVat' => true,
					'InternalComment' => $cart->note
				)
			);

			$list[] = $order;
		}

		

		$danea = new DaneaExport;
		$danea->setCompanyData(
			array(
				'Name' => 'Coral Point di Bonelli Liana',
			)
		);
		$danea->setOrders($list);
		header('Content-type: text/xml');
		echo $danea->buildXML();
		//debugga($danea);exit;

	}



}



?>