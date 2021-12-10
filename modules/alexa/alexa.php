<?php
class Alexa extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){
			$database = _obj('Database');
			$dataabase->execute("
				CREATE TABLE alexa_users (
				  id bigint(20) UNSIGNED NOT NULL,
				  email varchar(100) NOT NULL,
				  site varchar(100) NOT NULL,
				  url varchar(300) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=latin1;

			");
			$dataabase->execute("ALTER TABLE alexa_users ADD UNIQUE KEY id (id);");
			$dataabase->execute("ALTER TABLE alexa_users MODIFY id bigint(20) UNSIGNED NOT NUL AUTO_INCREMENT;");
			
		}


		return $res;
	}



	function uninstall(){
		$res = parent::uninstall();
		if( $res ){
			$database = _obj('Database');
			$database->execute('DROP TABLE alexa_uesers');

		}	
		return $res;
	}

}



?>