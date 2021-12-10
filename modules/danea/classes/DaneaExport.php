<?php
class DaneaExport{
    private $orders = array();

    private $_company_data = array(
        'Name' => '',
        'Address' => '',
        'Postalcode' => '',
        'City' => '',
        'Province' => '',
        'Country' => '',
        'FiscalCode' => '',
        'VatCode' => '',
        'Tel' => '',
        'Fax' => '',
        'Email' => '',
        'HomePage' => '',
    );


    function setOrders(array $orders){
        $this->orders = $orders;
    }



    //imposta i dati dell'azienda
    function setCompanyData($data=array()){
        $_company_fields = array_keys($this->_company_data);
        foreach($data as $k => $v){
            if( in_array($k,$_company_fields) ){
                $this->_company_data[$k] = $v;
            }
        }
    }
  


    function getOrders(){
        return $this->orders;
    }

    


    //general l'XML relativo ai dati dell'azienda
    function buildCompanyXML(){
        $xml = '';
        foreach($this->_company_data as $k => $v){
            if( trim($v) ){
                $xml .= "<{$k}>".$this->encodeVal($v)."</{$k}>";
            }
        }
		
        return $xml;
    }


	function encodeVal($val){
		return mb_convert_encoding(htmlspecialchars($val, ENT_NOQUOTES, 'Windows-1252'), 'UTF-8', 'Windows-1252');
	}





    function buildXML(){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <EasyfattDocuments AppVersion="2" Creator="Danea Soft" CreatorUrl="www.danea.it">
            <Company>
               '.$this->buildCompanyXML().'
            </Company><Documents>';
        
        foreach($this->orders as $ord){
            $xml .= $ord->buildXML();
        }
        $xml .= '</Documents> </EasyfattDocuments>';
		
        return $xml;
    }



}


?>