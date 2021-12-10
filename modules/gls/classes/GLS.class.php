<?php
class GLS{
	public $dateStart;
	public $dateEnd;
	public $statusOrder = array();
	public $changeStatus = false;
	public $_file;
	
	//stabilisce l'ordine del tracciato su weblabeling
	public $match = array(
		'ragione_sociale' => 1,
		'indirizzo' => 2,
		'localita' => 3,
		'zip_code' => 4,
		'provincia' => 5,
		'bda' => 6,
		'colli' => 8,
		'peso' => 10,
		'importo_contrassegno' => 11,
		//'note_autista' => 20,
		'email_notifica' => 25,
		//'telefono' => 26,
		'telefono' => 20,
	);
	function __construct(){
		
       
        
	}
	


	function getFileName(){
		$this->_file = 'GLSexport_'.date('Y-m-d_H-i-s').'.csv';
	}
	

	function getOrders($corrieri=array(),$orderId=array()){
		if( !okArray($orderId) ){
			$status = "(status IN (";
			foreach($this->statusOrder as $v){
				$status .= "'{$v}',";
			}
		}else{

			$status = "(id IN (";
			foreach($orderId as $v){
				$status .= "{$v},";
			}

		}

		
		if( okArray($corrieri) ){
			$courier = "(shippingMethod IN (";
			foreach($corrieri as $v){
				$courier .= "{$v},";
			}
			$courier = preg_replace('/,$/','))',$courier);
		}
		$status = preg_replace('/,$/','))',$status);
		$query = Cart::prepareQuery();
		if( !okArray($orderId) ){
			$query->whereExpression("evacuationDate >= '{$this->dateStart} 00:00' AND evacuationDate <= '{$this->dateEnd} 23:59'");
		}

		$query->whereExpression($status);
		if( $courier ){
			$query->whereExpression($courier);
		}
		$list = $query->get();

		
		

		
		
		return array_values($list);
	}



	public static function exportOrders($dateStart,$dateEnd,$statusOrder=array(),$changeStatus = false){
			$conf_gls = Marion::getConfig('GLS_export');
			
			$conf_gls['courier'] = unserialize($conf_gls['courier']);
			$conf_gls['appuntamento'] = unserialize($conf_gls['appuntamento']);
			

			if( $conf_gls['cod_payment'] ){
				$cod = PaymentMethod::withId($conf_gls['cod_payment']);
				if( is_object($cod) ){
					$cod_name = $cod->code;
				}
			}

			
			

			$obj = new GLS();

			$obj->dateStart = $dateStart;
			$obj->dateEnd = $dateEnd;
			$obj->statusOrder = $statusOrder;
			$obj->changeStatus = $changeStatus;

			$orders = $obj->getOrders($conf_gls['courier']);
			
			$obj->getFileName();
			
			
			if( okArray($orders) ){
				//$obj->getFileName();
				
				$path = _MARION_MODULE_DIR_.'gls/export/'.$obj->_file;


				$fd = @fopen($path, 'w');
				
				if($fd) {
					
					foreach($orders as $v){
						$line = $obj->createLine($v,$conf_gls);
						
						$obj->myFputcsv($fd, $line);
							
						if ($obj->changeStatus) {
							if( $conf_gls['status_sent'] ){
								$v->changeStatus($conf_gls['status_sent'],1);
							}
						}
						
					}
				}
				
				fclose($fd);
                $filename = $obj->_file;
			}else{
				$filename = -1;
			}
			return $filename;

	}
	

	function createLine($v,$conf_gls){
		if (in_array($v->shippingMethod, $conf_gls['appuntamento'])) {
			$note = 'Consegna su appuntamento Tel '.trim(str_replace(' ', '', (!empty($v->shippingCellular) ? $v->shippingCellular : $v->shippingPhone))).' - '.$msg;
		} else {
			$note = $msg;
		}
		
		$line = array();
		$n_colli = 1;
		$weight = 0;
		$order_total = '';
		$msg = '';
		if( $conf_gls['cod_payment'] ){
			$cod = PaymentMethod::withId($conf_gls['cod_payment']);
			if( is_object($cod) ){
				$cod_name = $cod->code;
			}
		}

		if ($cod_name && $v->paymentMethod == $cod_name) {
			$order_total = number_format($v->getTotalFinal(),2);
		}
		$name = '';
		if( $v->company ){
			$name = $v->company;
		}else{
			$name = $v->shippingName.' '.$v->shippingSurname;
		}
		
		$max = max($this->match);
		foreach($this->match as $k => $v1){
			$ordine[$v1] = $k;
		}
		
		for($k = 1; $k <= $max; $k++){
			if( $ordine[$k] ){
				$valore = '';
				switch($ordine[$k]){
					case 'ragione_sociale':
						$valore = $name;
						break;
					case 'indirizzo':
						$valore = $v->shippingAddress;
						if( $v->shippingStreetNumber  ){
							$valore .= " ".$v->shippingStreetNumber ;
						}
						break;
					case 'localita':
						$valore = $v->shippingCity;
						break;
					case 'zip_code':
						$valore = $v->shippingPostalCode;
						break;
					case 'provincia':
						$valore = $v->shippingProvince;
						break;
					case 'bda':
						$valore = $v->id;
						break;
					case 'peso':
						$valore = $v->getWeight()/1000;
						break;
					case 'colli':
						$valore = $n_colli;
						break;
					case 'importo_contrassegno':
						$valore = $order_total;
						break;
					case 'note_autista':
						$valore = $note;
						break;
					case 'email_notifica':
						$valore = $v->shippingEmail;
						break;
					case 'telefono':
						$valore = trim(str_replace(' ', '', (!empty($v->shippingCellular) ? $v->shippingCellular : $v->shippingPhone)));
						break;
					
				}
				$line[] = $valore;
			}else{
				$line[] = '';
			}
		}
		

		
		return $line;

	}
	public static function exportOrdersById($orderId=array(),$changeStatus = false){
			$conf_gls = Marion::getConfig('GLS_export');
			
			$conf_gls['courier'] = unserialize($conf_gls['courier']);
			$conf_gls['appuntamento'] = unserialize($conf_gls['appuntamento']);
			

			
			
			

			$obj = new GLS();
			$obj->changeStatus = $changeStatus;
			

			$orders = $obj->getOrders($conf_gls['courier'],$orderId);


		
			$obj->getFileName();
			
			
			if( okArray($orders) ){
				//$obj->getFileName();
				
				$path = _MARION_MODULE_DIR_.'gls/export/'.$obj->_file;
				
				$fd = @fopen($path, 'w');
				
				if($fd) {
					
					foreach($orders as $v){
						$line = $obj->createLine($v,$conf_gls);
						$obj->myFputcsv($fd, $line);
					
						if ($obj->changeStatus) {
							if( $conf_gls['status_sent'] ){
								
								$v->changeStatus($conf_gls['status_sent'],1);
								
							}
						}
						
					}
				}
				
				fclose($fd);
                $filename = $obj->_file;
			}else{
				$filename = -1;
			}
			return $filename;

	}

	


	 public function myFputcsv($fd, $array)
    {
		
        $line = implode('|', $array);
        $line .= "\n";
        if (!fwrite($fd, $line, 4096)) {
            $this->_errors[] = $this->l('Error: cannot write').' '.dirname(__FILE__).'/'.$this->_file.' !';
        }
    }


	public function getGlsUrl($customer = false)
    {
		$customer_code = Marion::getConfig('GLS_export','customer_code');
		$sede_code = Marion::getConfig('GLS_export','sede_code');
        if ($customer) {
            return 'https://www.gls-italy.com/index.php?option=com_gls&view=track_e_trace&mode=search&diretto=yes&locpartenza='.$customer_code;
        } else {
            return 'https://wwwdr.gls-italy.com/XML/get_xml_track.php?CodCli='.$customer_code.'&locpartenza='.$sede_code;
        }
    }


	public static function getTrackingCode(){
		$obj = new GLS();
		$obj->importShippingNumber();
		debugga($obj);exit;
	}
	

	 public function importShippingNumber()
    {

		$num = 236;
		$url = $this->getGlsUrl().'&bda='.$num;
		$xmlData = @simplexml_load_file($this->getGlsUrl().'&bda='.$num, null, LIBXML_NOCDATA);
		debugga($xmlData);exit;
        /*$orders = $this->getNullShippingNumber();
        $gls_os_import = (int)Configuration::get('GLS_OS_IMPORT');
        if (count($orders)) {
            foreach ($orders as $row) {
                $xmlData = @simplexml_load_file($this->getGlsUrl().'&bda='.$row['id_order'], null, LIBXML_NOCDATA);
                if (count($xmlData)) {
                    foreach ($xmlData->SPEDIZIONE as $spedizione) {
                        if (($row['id_order'] == ((int)$spedizione->Bda)) && !empty($spedizione->NumSped)) {
                            $order = new Order((int)$row['id_order']);
                            if (empty($order->shipping_number)) {
                                $order->shipping_number = $spedizione->NumSped;
                                $order->update();

                                // Order Carrier
                                $id_order_carrier = self::getOrderCarrierIdByOrderId($order->id);
                                if ($id_order_carrier) {
                                    $order_carrier = new OrderCarrier($id_order_carrier);
                                    // Update order_carrier
                                    $order_carrier->tracking_number = $spedizione->NumSped;
                                    if ($order_carrier->update()) {
                                        $this->updateShippingNumber($order->id, $order->shipping_number);

                                        $customer = new Customer((int)$order->id_customer);
                                        $carrier = new Carrier((int)$order->id_carrier);
                                        if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($carrier)) {
                                            die(Tools::displayError());
                                        }
                                                
                                        $templateVars = array(
                                            '{followup}' => $this->getGlsUrl(true).'&numsped='.$order->shipping_number,
                                            '{firstname}' => $customer->firstname,
                                            '{lastname}' => $customer->lastname,
                                            '{id_order}' => $order->id,
                                            '{shipping_number}' => $order->shipping_number,
                                            '{order_name}' => $order->getUniqReference()
                                        );
                                            
                                        if ($gls_os_import) {
                                            $this->_changeIdOrderStateImport($order);
                                        }
                                            
                                        Mail::Send((int)$order->id_lang, 'in_transit', Mail::l('Package in transit'), $templateVars, $customer->email, $customer->firstname.' '.$customer->lastname, null, null, null, null, _PS_MAIL_DIR_, true, (int)$order->id_shop);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }*/
    }

}




?>