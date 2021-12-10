<?php
class DaneaExportOrder{
    public $_rows = array(); //righe dell'ordine
    public $_payments = array(); // pagamenti relativi all'ordine
    public $_customer_data = array(
        'CustomerCode' => '', // Codice anagrafica; serve per associare l'ordine all'eventuale cliente già inserito in anagrafica. Durante l'importazione, i presenza di <CustomerCode> e in assenza altri codici <Customer...>, i relativi campi anagrafica del documento saranno riempiti con i dati dell'anagrafica corrispondente a <CustomerCode> e già codificata nell'archivio.
        'CustomerWebLogin' => '', //Login web del cliente (usato nell'integrazione e-commerce); nella fase di identificazione dell'anagrafica, questo campo viene preso in considerazione solo in mancanza di CustomerCode.
        'CustomerName' => '', //Cognome e nome o denominazione sociale.
        'CustomerAddress' => '', //Indirizzo
        'CustomerPostcode' => '', //CAP
        'CustomerCity' => '', //Città
        'CustomerProvince' => '', //Provincia (2 caratteri).
        'CustomerCountry' => '', //Nazione
        'CustomerVatCode' => '', //Partita IVA
        'CustomerFiscalCode' => '', //Codice Fiscale
        'CustomerEInvoiceDestCode' => '', //Codice destinatario o PEC per l'invio della fattura elettronica.
        'CustomerTel' => '', //Numero di telefono.
        'CustomerCellPhone' => '', //Numero di cellulare
        'CustomerFax' => '', //Fax
        'CustomerEmail' => '', //Indirizzo email
        'CustomerPec' => '', //PEC
        'CustomerReference' => '', //Persona di riferimento.
        
    );

    public $_order_data = array(
        'Date' => '', // Data del documento nel fomrto yyyy-mm-dd
        'Number' => '', //Numero documento
        'Total' => '', //totale dell'ordine comprensivo delle tasse
        'TotalWithoutTax' => '', //Totale senza tasse
        'VatAmount' => '', //Totale Iva calcolata
        'PaymentName' => '', //Nome pagamento (deve essere già presente nella tabella "Tipi pagamento" di Easyfatt).
        'PaymentBank' => '', // Banca Pagamento
        'InternalComment' => '', //Commento (nell'e-commerce, usare questo campo per indicare note libere dell'acquirente durante la fase dell'ordine).
        'CustomField1' => '', //Campo note 1.
        'CustomField2' => '', //Campo note 2.
        'CustomField3' => '', //Campo note 3.
        'CustomField4' => '', //Campo note 4.
        'FootNotes' => '', //Note a fine pagina.
        'SalesAgent' => '',
        'PricesIncludedVat' => '', // booleano che stabilisce se i prezzi degli ordini sono iva inclusa
		'CostAmount' => '',
		'CostDescription' => '',
		'PricesIncludeVat' => false
    );

    public $_address_data = array(
        'DeliveryName' => '',//Nome e cognome o denominazione.
        'DeliveryAddress' => '', //Indirizzo.
        'DeliveryPostcode' => '', //CAP.
        'DeliveryCity' => '', //Città.
        'DeliveryProvince' => '', //Provincia (2 caratteri).
        'DeliveryCountry' => '', //Nazione.
    );


    public $_item_fields = array(
        'Code',
        'Description',
        'Qty',
        'Um',
        'Size',//Taglia (usato nel settore dell'abbigliamento).
        'Color', //
        'Price',
        'Discounts',
        'VatCode',
        'Notes'
    );

    public $_payment_fields = array(
        'Advance',  //boolean
        'Date',
        'Amount', 
        'Paid', //boolean
    );
	

	 //imposta i dati di fatturazione del cliente
    function setData($data=array()){
        $_fields = array_keys($this->_order_data);
        foreach($data as $k => $v){
            if( in_array($k,$_fields) ){
                $this->_order_data[$k] = $v;
            }
        }
    }
   


     //imposta i dati di fatturazione del cliente
    function setCustomerData($data=array()){
        $_fields = array_keys($this->_customer_data);
        foreach($data as $k => $v){
            if( in_array($k,$_fields) ){
                $this->_customer_data[$k] = $v;
            }
        }
    }
    //imposta i dati di spedzione
    public function setAddressData($data){
        $_fields = array_keys($this->_address_data);
        foreach($data as $k => $v){
            if( in_array($k,$_fields) ){
               
                $this->_address_data[$k] = $v;
            }
        }
    }

    //aggiunge una riga all'ordine
    function addRow($data){

        $_fields = $this->_item_fields;
        $row = array();
        foreach($data as $k => $v){
            if( in_array($k,$_fields) ){
                $row[$k] = $v;
            }
        }
        $this->_rows[] = $row;
    }

    //aggiunge un pagamento all'ordine
    function addPayment($data){

        $_fields = $this->_payment_fields;
        $row = array();
        foreach($data as $k => $v){
            if( in_array($k,$_fields) ){
                $row[$k] = $v;
            }
        }
        $this->_payments[] = $row;
    }


    function builderRowsXML(){
        $xml = '';
        foreach($this->_rows as $row){
            $xml .= '<Row>';
            foreach($row as $k => $v){
                if( $k == 'Color' || $k == 'Size' ){
					if( trim($v) ){
						$xml .= "<{$k}>".$this->encodeVal($v)."</{$k}>";
					}else{
						$xml .= "<{$k}>-</{$k}>";
					}
				}else{
					if( trim($v) ){
						$xml .= "<{$k}>".$this->encodeVal($v)."</{$k}>";
					}
				}
            }
            $xml .= '</Row>';
        }
        return $xml;
    }
    function builderPaymentsXML(){
        $xml = '';
        foreach($this->_payments as $row){
            $xml .= '<Payment>';
            foreach($row as $k => $v){
                if( trim($v) ){
                    $xml .= "<{$k}>".$this->encodeVal($v)."</{$k}>";
                }
            }
            $xml .= '</Payment>';
        }
        return $xml;
    }

    function buildCustomerXML(){
        $xml = '';
        foreach($this->_customer_data as $k => $v){
            if( trim($v) ){
                $xml .= "<{$k}>".$this->encodeVal($v)."</{$k}>";
            }
        }
        return $xml;
    }

	function buildDataXML(){
        $xml = '';
		
        foreach($this->_order_data as $k => $v){
            if( trim($v) ){
                $xml .= "<{$k}>".$this->encodeVal($v)."</{$k}>";
            }
        }
        return $xml;
    }

    function buildXML(){
        $xml = '<Document><DocumentType>C</DocumentType>';
        $xml .= $this->buildCustomerXML();
		$xml .= $this->buildDataXML();
        $xml .= '<Rows>'.$this->builderRowsXML().'</Rows>';
        $xml .= '<Payments>'.$this->builderPaymentsXML().'</Payments>';
        $xml .= '</Document>';
        return $xml;
    }
	


	function encodeVal($val){
		return mb_convert_encoding(htmlspecialchars($val, ENT_NOQUOTES, 'Windows-1252'), 'UTF-8', 'Windows-1252');
	}




   

}


?>