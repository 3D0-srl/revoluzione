<?php
class WidgetVetrina extends WidgetBase{
	
	// COSTANTI DI BASE
	const TABLE = 'widget_vetrina'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	
	function getBaseUrl(){
		$protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';


		return $protocol."://".$_SERVER['SERVER_NAME'];
	}



	function getIcon(){
		if( $this->type == 'product'){
			return 'fa fa-archive';
		}else{
			return 'fa fa-book';
		}
	}


	function afterLoad(){
		parent::afterLoad();
		$this->getComposition();
	}
	
	function getUrlEdit(){
		if( $this->type == 'product'){
			return 'widgets/WidgetVetrina/index.php?action=mod_widget_vetrina_prodotti&id='.$this->id;
		}else{
			return 'widgets/WidgetVetrina/index.php?action=mod_widget_vetrina_news&id='.$this->id;
		}
	}

	

	function getLogoUrl(){
		
		if( $this->type == 'product'){
			return 'widgets/WidgetVetrina/img/logo-prodotti.png';
		}else{
			return 'widgets/WidgetVetrina/img/logo-news.png';
		}
	}
	

	

	function afterSave(){
		parent::afterSave();
		$database = _obj('Database');
		$database->delete('widget_vetrina_composizione',"id_vetrina={$this->id}");
		foreach( $this->items as $v ){
				$toinsert = array(
					'id_object' => $v,
					'id_vetrina' => $this->id
				);
				$database->insert('widget_vetrina_composizione',$toinsert);
		}
		return $select;
	}
	
	function getContent(){
		
		$html = '';
		$baseurl = $this->getBaseUrl();
		foreach($this->items as $v){
			switch($this->type){
				case 'product':
					$obj = Product::withId($v);
					if( is_object($obj)){

						
						$dati = array(
							'label' => $obj->getUrlImageLabelPrice(),
							'name' => $obj->get('name'),
							'url' => $baseurl.$obj->getUrl(),
							'image' => $baseurl.$obj->getUrlImage(0,'original'),
							'price' => $GLOBALS['activecurrency']." ".$obj->getPriceFormatted(),
						);
						$prezzo_unitario = $obj->price_unit;
						if( $obj->hasSpecialPrice() ){
							$dati['barrato'] = $obj->getDefaultPriceValueFormatted();
							$sconto = (int)round((($prezzo_unitario->defaultValue -$prezzo_unitario->value)*100)/$prezzo_unitario->defaultValue,2);
							$dati['sconto'] = $sconto;
						}


						
						$data[] = $dati;
					}
					
					break;
				case 'news':
					$obj = News::withId($v);
					if( is_object($obj)){
						$dati = array(
							'name' => $obj->get('title'),
							'url' => $baseurl.$obj->getUrl(),
							'image' => $baseurl.$obj->getUrlImage(0,'original'),
							'description' => $obj->getTruncateContent(150)
						);

						$data[] = $dati;
					}

					break;
			}
			
		}
		//debugga($t);exit;
		$html = $this->getHtml($data);
		return $html;

		
	}


	function getHtml($data){
		$template = _obj('Template');
		$template->items = $data;
		switch($this->type){
			case 'product':
				$page = 'image-';
				break;
			case 'news':
				$page = 'news-';
				break;
		}
		$page = $page.$this->view.'-column.htm';
		foreach($this->conf as $k => $v){
			$template->$k = $v;
		}
		ob_start();
		$template->output_module('mail_marketing/widgets/WidgetVetrina/',$page,NULL,true);
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;

	}
	

	function getComposition(){
		$database = _obj('Database');
		$select = $database->select('*','widget_vetrina_composizione',"id_vetrina={$this->id}");
		$list = array();
		$this->items = array();
		foreach($select as $v){
			$this->items[] = $v['id_object'];
		}

		
	}


	function setComposition($array=array()){
		$this->items = $array;
	}

	function setConf($array=array()){
		$this->conf = serialize($array);
	}
	


}



?>