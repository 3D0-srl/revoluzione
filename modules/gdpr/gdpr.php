<?php
class Gdpr extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){

			$database = _obj('Database');
			
			$database->execute("
				CREATE TABLE gdpr_log (
				  id bigint(20) UNSIGNED NOT NULL,
				  type varchar(200) DEFAULT NULL,
				  log varchar(500) DEFAULT NULL,
				  timestamp timestamp NULL DEFAULT CURRENT_TIMESTAMP,
				  id_user(20) UNSIGNED NOT NULL,
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			");

			$database->execute("
				ALTER TABLE gdpr_log ADD UNIQUE KEY id (id);
			");
			$database->execute("
				ALTER TABLE gdpr_log MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
			");

			$list = $database->select('*','pageWithEmail');
			if( okArray($list) ){
				foreach($list as $v){
					if( $v['check_name_form'] ){
						$form = $database->select('*','form',"nome='{$v['check_name_form']}'");
						if( okArray($form)){
							$campi = $database->select('*','form_campo',"form={$form[0]['codice']} AND campo='privacy'");
							
							if( okArray($campi) ){
								foreach($campi as $v){
									$database->update('form_campo',"codice={$v['codice']}",array('default_value'=>0));
								}
							}
						}
					}
				}
			}
			
		}


		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			$database = _obj('Database');
			$database->execute("DROP TABLE gdpr_log;");

		}	
		return $res;
	}

}



?>