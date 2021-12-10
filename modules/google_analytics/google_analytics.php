<?php
class GoogleAnalytics extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){
		
			$database = _obj('Database');
			$toinsert_form = array(
			'nome' => 'module_analytics',
				'method' => 'POST',
			);
			$id = $database->insert('form',$toinsert_form);
			
			$database->execute(
				"INSERT INTO form_campo (form, campo, etichetta, gettext, checklunghezza, lunghezzamin, lunghezzamax, type, tipo, obbligatorio, valuezero, default_value, codice_php, unique_value, globale, attivo, multilocale, ordine, tipo_valori, function_template, tipo_textarea, tipo_data, tipo_time, tipo_file, tipo_timestamp, ext_image, resize_image, ext_attach, number_files, class, post_function, pre_function, ifisnull, value_ifisnull) VALUES
				({$id}, 'email', 'email', 0, 0, NULL, NULL, 1, 10, 1, 0, NULL, NULL, 0, 0, 1, 0, 1, 1, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL),
				({$id}, 'path_key_p12', 'percorso del file contenente la chiave', 0, 0, NULL, NULL, 1, 0, 1, 0, NULL, NULL, 0, 0, 1, 0, 2, 1, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL),
				({$id}, 'site', 'hostname', 0, 0, NULL, NULL, 1, 0, 1, 0, NULL, NULL, 0, 0, 1, 0, 3, 1, NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL);"
			);
			

			
			$settings = array(
					0 => array(
						'gruppo' => 'analytics',
						'etichetta' => 'analytics',
						'chiave' => 'email',
						'valore' =>  "",
						'descrizione' => '',
						'ordine' => '',
					),
					1 => array(
						'gruppo' => 'analytics',
						'etichetta' => 'analytics',
						'chiave' => 'site',
						'valore' =>  $_SERVER['HTTP_HOST'],
						'descrizione' => '',
						'ordine' => '',
					),

					3 => array(
						'gruppo' => 'analytics',
						'etichetta' => 'analytics',
						'chiave' => 'profileID',
						'valore' =>  "",
						'descrizione' => '',
						'ordine' => '',
					),
					4 =>  array(
						'gruppo' => 'analytics',
						'etichetta' => 'analytics',
						'chiave' => 'UA_ID',
						'valore' =>  "",
						'descrizione' => '',
						'ordine' => '',
					),
					5 => array(
						'gruppo' => 'analytics',
						'etichetta' => 'analytics',
						'chiave' => 'path_key_p12',
						'valore' =>  "modules/".basename(__DIR__)."/eshop-e3c0c72f1565.p12",
						'descrizione' => '',
						'ordine' => '',
					)
			);


			foreach($settings as $k => $v){
				$database->insert('setting',$v);

			}



			$cache = _obj('Cache');
			if( $cache->isExisting("setting") ){
				$cache->delete('setting');
			}
		}
		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		$database = _obj('Database');
		$form = $database->select('*','form',"nome='module_analytics'");
		$form = $form[0]; 

		$database->delete('form',"codice={$form['codice']}");
		$database->delete('form_campo',"form={$form['codice']}");
		
		$database->delete('setting',"gruppo='analytics'");
		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			$cache->delete('setting');
		}
		return $res;
	}

}



?>