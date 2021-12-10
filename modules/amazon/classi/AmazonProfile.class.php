<?php

class AmazonProfile extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'amazon_profile'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = ''; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	
	function getStore(){
		return AmazonStore::withId($this->store);
	}
	
	function setCategory($category=NULL){
		$this->data['category'] = $category;
	}

	/*function getObjCategory($product=NULL){
		$category = $this->data['category'];
		
		if( file_exists('category/'.$category.".php") ){
	
			require_once('category/'.$category.".php");
			$obj = new $category();
			$obj->setData($this->data);
			if( $product ){
				$obj->prepareData($product);
			}

			return $obj;
		}

		return false;
	}*/

	function getObjCategory($market=NULL){
		$category = $this->data['category'];
		
		if( file_exists('category/'.$category.".php") ){
	
			require_once('category/'.$category.".php");
			$obj = new $category();
			
			$obj->setData($this->data);
			$mapping = $this->mapping[$market];
			
			$obj->init($market,$mapping);
			

			return $obj;
		}

		return false;
	}
	

	function getDataMarket($market=NULL){
		if( $market ){
			$database = _obj('Database');
			$dati = $database->select('*','amazon_profile_marketplace',"market='{$market}' AND id_profile={$this->id}");

			if( okArray($dati) ){
				$dati = $dati[0];

				$data = unserialize($dati['data']);
				$this->data = $data;
				
			}

		}

		
	}


	function delete(){
		$database = _obj('Database');
		$database->delete('amazon_profile_marketplace',"id_profile={$this->id}");
		parent::delete();
		
	}

	

	function getForm($market=NULL){
		
		if($this->data['category']){
			
			$obj = $this->getObjCategory($market);
			
			if( is_object($obj) ){
				return $obj->getForm($market);
			}
			
		}
		return '';
	}

	function checkForm($data){
		if($this->data['category']){
			
			$obj = $this->getObjCategory($market);
			
			if( is_object($obj) ){
				return $obj->checkForm($data);
			}
			
		}
		return '';
	}
	

	





	function getXmlProduct(&$product,$market){
		$this->getDataMarket($market);
		
		$obj = $this->getObjCategory($market);
		
		if( is_object($obj) ){
			
			$xml = $obj->getXmlProduct($product);
			



		}
		
		return $xml;
		
		
	}



	function getMappingMarket($market=NULL){

		
		if( $market ){
			
			$this->getDataMarket($market);
			$obj = $this->getObjCategory($market);
			if( is_object($obj) ){
				
				$map = $obj->getMappedValues();
				
				
			}
		}

		return $map;

	}


}



?>