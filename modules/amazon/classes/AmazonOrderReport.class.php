<?php
class AmazonOrderReport{
    public static $market; 
    public static function import($id_store,$market){
        self::$market = $market;
        $file = $id_store."_".$market."__GET_FLAT_FILE_ACTIONABLE_ORDER_DATA_.csv";

        $list = self::parseCSV(_MARION_MODULE_DIR_.'amazon/reports/'.$file);
        debugga($list);exit;
    }


    public static function parseCSV($path){
        $fp = fopen($path,'r');
		if (($headers = fgetcsv($fp, 0, "\t")) !== FALSE){
			if ($headers)
				$newHeaders = array();
			foreach ($headers as $key => $value) {
				$text = substr($value,0,200);
				$text = strtolower($text);
				$newHeaders[] = str_replace('-', '_', $text);
			}
			$result = array();
			while (($line = fgetcsv($fp, 0, "\t")) !== FALSE) {
				if ($line){
					if (sizeof($line)==sizeof($newHeaders)){
						$result[] = array_combine($newHeaders,$line);
					}else{
						fclose($fp);
					}
				}
			}
        }

        debugga($result);exit;
        $orders = [];
		foreach($result as $v){
            $orders[$v['order_id']][] = self::parseRow($v);
        }
            
        return $orders;
    }

    public static function parseRow($row){
        
        $data = array(
            'customer' => array(
                'email' => $row['buyer_email'],
                'name' => $row['buyer_name'],
                'phone' => $row['buyer_phone_number'],
            ),
            'cart' => array(
                'shippingName' => $row['recipient_name'],
                'shippingPhone' => $row['buyer_phone_number'],
                'shippingAddress' => $row['ship_address_1'],
                'shippingCity' => $row['ship_city'],
                'shippingProvince' => $row['ship_state'],
                'shippingPostalCode' => $row['ship_postal_code'],
                'shippingCountry' => $row['ship_country'],
                'shippingMethod' => $row['ship_service_level'],
                'paymentMethod' => 'AMAZON',
                'comesFrom' => 'Amazon - '.self::$market

            ),
            'product' => array(
                'sku' => $row['sku'],
                'name' => $row['product_name'],
                'quantity' => $row['quantity_purchased'],
            )
        );

        //debugga($data);exit;
    }
}
?>