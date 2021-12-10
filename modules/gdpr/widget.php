<?php
	/*
	function gdpr_link_account($id=null){
			

			$widget = Marion::widget('gdpr');
			$database = _obj('Database');

			
			$options_db = Marion::getConfig('database');
		
			$check = $database->select("*","information_schema.tables","table_schema = '{$options_db['options']['nome']}' AND table_name = 'address'");
			if( okArray($check) ){
				$widget->address = true;
			}
			$widget->output('link_account.htm');

			
				
	}



	
	function gdpr_field_form_user($params=NULL){
		

		//DETERMINO UN OGGETTO TEMPLATE PREATTAMENTE PER IL WIDGET
		$module_dir = 'gdpr';
		$widget = Marion::widget($module_dir);
		
		
	
		//CREO I CAMPI DEL FORM
		$GLOBALS['campi_gdpr_form_user'] = array(		
			'gdpr' =>  array(
				'campo'=>'gdpr',
				'type'=>'checkbox',
				'options' => array('1'),
				'obbligatorio'=>'t',
				'default'=>'0',
				'etichetta'=>'Privacy',
			)

			
		);
		if( okArray($params) ) {
			get_form($elements,'campi_gdpr_form_user','',$params);
		}else{
			get_form($elements,'campi_gdpr_form_user');
		}
		
		$dati =Marion::getConfig('gdpr');
		$widget->link = $dati['link_privacy'];
		$text = unserialize($dati['text_privacy']);
		$widget->text = $text[$GLOBALS['activelocale']];
		//STAMPO L'HTML
		ob_start();
		$widget->output('field_form_user.htm',$elements);
		$html = ob_get_contents();
		ob_end_clean();
			
		
		unset($widget);
		return $html;


	}
	Marion::add_widget('form_utente.htm',"gdpr_field_form_user","fields_form_anchor",'frontend',10,'append');
	*/
?>