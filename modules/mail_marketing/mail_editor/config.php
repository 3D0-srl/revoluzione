<?php


$file_config = $_SERVER['DOCUMENT_ROOT'].'/../config.ini';

if( file_exists($file_config) ){
	$conf = parse_ini_file($file_config,1);
	$conf_db = $conf['DATABASE'];
}


//main variables

 define("SITE_URL", 'http://'.$_SERVER['HTTP_HOST']. '/modules/mail_marketing/mail_editor/');
 define("SITE_DIRECTORY",$_SERVER['DOCUMENT_ROOT'] .'/modules/mail_marketing/mail_editor/');

 //elements.json file directory
 define("ELEMENTS_DIRECTORY",SITE_DIRECTORY.'elements.json');

 //uploads directory,url
define("UPLOADS_DIRECTORY",SITE_DIRECTORY.'../../../media/images/');
define("UPLOADS_URL",'http://'.$_SERVER['HTTP_HOST'].'/media/images/');

//EXPORTS directory,url
define("EXPORTS_DIRECTORY",SITE_DIRECTORY.'exports/');
define("EXPORTS_URL",SITE_URL.'exports/');

//Db settings
define('DB_SERVER',$conf_db['options.host']);
define('DB_USER',$conf_db['options.user']);
define('DB_PASS' ,$conf_db['options.password']);
define('DB_NAME', $conf_db['options.nome']);


define('EMAIL_SMTP','smtp address');
define('EMAIL_PASS' ,'email address password');
define('EMAIL_ADDRESS', 'email address ');


//for check used in demo or not
define('IS_DEMO', false);


?>
