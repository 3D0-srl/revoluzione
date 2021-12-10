<?php
use Marion\Core\Marion;
use Marion\Core\Form;
function _obj($object,$options=NULL){
	$class_path = array();
	switch( $object ){
		case 'Database':
			$alias = "DatabaseCache";
			$class_path = array( 
				dirname(__FILE__).'/classes/db/Database2.class.php',
				dirname(__FILE__).'/classes/db/DatabaseCache.class.php'
			);
			if(!okArray($options)){
				$options = $GLOBALS['setting']['default']['DATABASE']['options'];
			}
			$cache = true;
			break;
		case 'PDF':
			$alias = $object;
			$class_path = array( 
				dirname(__FILE__).'/classes/pdf/Pdf.class.php',
			);
			if(!okArray($options)){
				//$options = 	$GLOBALS['setting']['default']['CAPTCHA'];
			}
			
			$cache = false;
			break;
		case 'PDF2':
			$alias = "wkhtmltopdf";
			
			$class_path = array( 
				dirname(__FILE__).'/classes/pdf/wkhtmltopdf.class.php',
			);
			if(!okArray($options)){
				//$options = 	$GLOBALS['setting']['default']['CAPTCHA'];
			}
			
			$cache = false;
			break;
		
		case 'Mail':
			$alias = 'Mail2';
			$class_path = array( 
				dirname(__FILE__).'/classes/mail/Mail.class.php'
			);
			$cache = false;
			break;
		case 'ImageDisplay':
			$alias = $object;
			$class_path = array( 
				dirname(__FILE__).'/classes/image/ImageDisplay.class.php'
			);
			$cache = false;
			break;
		case 'Cache':
			$alias = 'phpFastCache';
			$class_path = array( 
				dirname(__FILE__)."/classes/cache/phpfastcache.php",
			);
			if(!okArray($options)){

				 $options = array(
					"storage"       =>  "", // blank for auto
					"default_chmod" =>  0777, // 0777 , 0666, 0644
					/*
					 * Fall back when old driver is not support
					 */
					"fallback"  => "files",

					"securityKey"   =>  "auto",
					"htaccess"      => true,
					"path"      =>  "",

					"memcache"        =>  array(
						array("127.0.0.1",11211,1),
						//  array("new.host.ip",11211,1),
					),

					"redis"         =>  array(
						"host"  => "127.0.0.1",
						"port"  =>  "",
						"password"  =>  "",
						"database"  =>  "",
						"timeout"   =>  ""
					),

					"extensions"    =>  array(),
				);



				if( $GLOBALS['setting']['default']['CACHE']['securityKey'] ){
					$options['securityKey'] = $GLOBALS['setting']['default']['CACHE']['securityKey'];
				}
				if( $GLOBALS['setting']['default']['CACHE']['path'] ){
					$options['path'] = $GLOBALS['setting']['default']['CACHE']['path'];
				}
				
			}
			$cache = false;
			break;
		default:
			$alias = $object;
			$cache = false;
			break;
		
	}
	
	
	if( $cache && !empty($GLOBALS[$object]) ){
		 $class_type = "My_{$object}";
		 $class_type2 = $alias;
		 if( is_a($GLOBALS[$object],$class_type) || is_a($GLOBALS[$object],$class_type2)){
			 return $GLOBALS[$object];
	 	 }
	
	 }
	if( is_array($class_path) && count($class_path) > 0 ){
		foreach	($class_path as $path){
			require_once $path;
		}
	}
	if( $alias == 'phpFastCache'){
		if( count($options) > 0 ){
			$toreturn = new $alias('auto',$options);
		}else{
			$toreturn = new $alias();
		}
	}else{
		if( class_exists('My_'.$alias) ){
			if( is_array($options) && count($options) > 0  ){
				$class = "My_{$alias}";
				$toreturn = new $class($options);
			}else{
				$class = "My_{$alias}";
				$toreturn = new $class();
			}	
			
		}elseif(class_exists($alias)){
			
			if( is_array($options) && count($options) > 0  ){
			
				$toreturn = new $alias($options);
			}else{
				$toreturn = new $alias();
			}	
		}else{
			return false;
		}
	}
	if( $cache ){
		$GLOBALS[$object] = $toreturn;	
	}
	return $toreturn;
		
}


function _form($name){
	if( class_exists('My_Form') ){
	    return new My_Form($name);
    }else{ 
    	return new Form($name);
	}
}

// funzione che genere gli elementi di un form
// INPUT::
// name :: nome del form
// action :: azione di ritorno
// dati :: dati di default
// campi_aggiunti :: campi da aggiungere a quelli presenti nel form. Valido solo se il form e' memorizzato nel database
/*
function get_form(&$elements=array(),$name, $action=NULL, $dati=NULL, $url=NULL, $campi_aggiunti=NULL,$num=NULL,$name_array_form='formdata'){
	$template = _obj('Template');
	
 	
	 if( !empty($GLOBALS['campi_'.$name]) && okArray($GLOBALS['campi_'.$name])  ){
	 	return $template->_form($name,$action,$dati,$url,'',$elements,$num,$name_array_form);
 	}else{
	 	$form = _form($name);
	 	
	 	if( $form->exists ){
		 	if( okArray($campi_aggiunti) ){
			 	$form->addElements($campi_aggiunti);
		 	}
		 	
		 	if( !$url){
			 	$url = $form->url;
		 	}
		 	//debugga($url);exit;
		 	if( !$action){
			 	$action = $form->action;
		 	}
		 	$_SESSION['campi_form'][$name]['campi'] = $form->getElements();
		 	$_SESSION['campi_form'][$name]['action'] = $action;
		 	
		 	
		 	
		 	$html_dati = $form->getDatiHtml();
		 	
		 	if(okArray($html_dati)){
		 		$template->html_dati = $html_dati;
	 		}
	 		
		 	return $template->_form($name,$_SESSION['campi_form'][$name]['action'],$dati,$url,$_SESSION['campi_form'][$name]['campi'],$elements,$num,$name_array_form);
	 	} else {
		 	return false;
	 	}
 	}

}

// funzione che genere gli elementi di un form
// INPUT::
// name :: nome del form
// action :: azione di ritorno
// dati :: dati di default
// campi_aggiunti :: campi da aggiungere a quelli presenti nel form. Valido solo se il form e' memorizzato nel database

function get_form2(&$elements=array(),$name, $action=NULL, $dati=NULL, $url=NULL, $campi_aggiunti=NULL,$num=NULL,$name_array_form='formdata'){
	$template = _obj('Template');
	
 	
	 if( !empty($GLOBALS['campi_'.$name]) && okArray($GLOBALS['campi_'.$name])  ){
	 	return $template->_form2($name,$action,$dati,$url,'',$elements,$num,$name_array_form);
 	}else{
	 	$form = _form($name);
	 	
	 	if( $form->exists ){
		 	if( okArray($campi_aggiunti) ){
			 	$form->addElements($campi_aggiunti);
		 	}
		 	
		 	if( !$url){
			 	$url = $form->url;
		 	}
		 	//debugga($url);exit;
		 	if( !$action){
			 	$action = $form->action;
		 	}
		 	$_SESSION['campi_form'][$name]['campi'] = $form->getElements();
		 	$_SESSION['campi_form'][$name]['action'] = $action;
		 	
		 	
		 	
		 	$html_dati = $form->getDatiHtml();
		 	
		 	if(okArray($html_dati)){
		 		$template->html_dati = $html_dati;
	 		}
	 		
		 	return $template->_form2($name,$_SESSION['campi_form'][$name]['action'],$dati,$url,$_SESSION['campi_form'][$name]['campi'],$elements,$num,$name_array_form);
	 	} else {
		 	return false;
	 	}
 	}

}

function check_form($formdata,$name,$update=NULL){
	$template = _obj('Template');
	
	 if(okArray($GLOBALS['campi_'.$name]) ){
	 	 	
		if( okArray($update) ){
			foreach( $update as $campo => $array){
				if( array_key_exists($campo,$GLOBALS['campi_'.$name]) ){
					foreach( $array as $k => $v){
						$GLOBALS['campi_'.$name][$campo][$k] = $v;
					}
				}else{
					foreach( $array as $k => $v){
						$GLOBALS['campi_'.$name][$campo][$k] = $v;
					}
				}
			}
		}
		
		return $template->_form_ok($formdata,$name);
	 	
 	}else{
	 	$form = _form($name);
	 	
	 	if( $form->exists ){
		 	$elements = $form->getElements();
		 	
		 	if($form->captcha == 1){
			 	$captcha = array(
			 		'campo' => 'captcha',
			 		'etichetta' => 'captcha',
			 		'type' => 'captcha',
			 	);
				if(_var('formID')){
					$captcha['value_real'] = $_SESSION[_var('formID')]['captcha_text'];
				}else{
					$captcha['value_real'] = $_SESSION['captcha_text'];
				}
			 	$elements['captcha'] = $captcha;
		 	}
		 	
		 	
		 	$_SESSION['campi_form'][$name]['campi'] = $elements;
		 	if( okArray($update) ){
			 	foreach( $update as $campo => $array){
				 	if( array_key_exists($campo,$elements) ){
					 	foreach( $array as $k => $v){
					 		$elements[$campo][$k] = $v;
				 		}
		 			}else{
						foreach( $array as $k => $v){
					 		$elements[$campo][$k] = $v;
				 		}
					}
			 	}
		 	}
			
		 	return $template->_form_ok($formdata,$name,$elements);
	 	}else{
		 	return false;
	 	}
 	}
}

function check_form2($formdata,$name,$update=NULL){
	$template = _obj('Template');
	
	
	

	 if(okArray($GLOBALS['campi_'.$name]) ){
	 	 	
		if( okArray($update) ){
			foreach( $update as $campo => $array){
				if( array_key_exists($campo,$GLOBALS['campi_'.$name]) ){
					foreach( $array as $k => $v){
						$GLOBALS['campi_'.$name][$campo][$k] = $v;
					}
				}else{
					foreach( $array as $k => $v){
						$GLOBALS['campi_'.$name][$campo][$k] = $v;
					}
				}
			}
		}
		
		return $template->_form_ok2($formdata,$name);
	 	
 	}else{
	 	$form = _form($name);
	 	
	 	if( $form->exists ){
		 	$elements = $form->getElements();
		 	
		 	if($form->captcha == 1){
			 	$captcha = array(
			 		'campo' => 'captcha',
			 		'etichetta' => 'captcha',
			 		'type' => 'captcha',
			 	);
				if(_var('formID')){
					$captcha['value_real'] = $_SESSION[_var('formID')]['captcha_text'];
				}else{
					$captcha['value_real'] = $_SESSION['captcha_text'];
				}
			 	$elements['captcha'] = $captcha;
		 	}
		 	
		 	
		 	$_SESSION['campi_form'][$name]['campi'] = $elements;
		 	if( okArray($update) ){
			 	foreach( $update as $campo => $array){
				 	if( array_key_exists($campo,$elements) ){
					 	foreach( $array as $k => $v){
					 		$elements[$campo][$k] = $v;
				 		}
		 			}else{
						foreach( $array as $k => $v){
					 		$elements[$campo][$k] = $v;
				 		}
					}
			 	}
		 	}
			
		 	return $template->_form_ok2($formdata,$name,$elements);
	 	}else{
		 	return false;
	 	}
 	}
}
function override_dati(&$elements,$name,$dati){
	$valori = array_keys($dati);
	$children = $elements[$name]->children;
	foreach($children as $k =>$v){
		if( !in_array($v->attributes['value'],array_keys($dati)) ){
			unset($children[$k]);
		}	
	}
	$elements[$name]->children = $children;
	return;
}
*/

function createIDform(){
	if( getIdForm() ){ 	
		return;
	}
	$request_uri = $_SERVER['REQUEST_URI'];
	
	$id = md5(uniqid(rand(), true)); 
	if( preg_match('/(.*)(\.php\?)(.*)/',$request_uri)){
		$request_uri.="&formID={$id}";
	}else{
		$request_uri.="?formID={$id}";
	}
	$url = $request_uri;
	header("Location:".$url);
	exit;
}

function getIdForm(){
	if(_var('formID')){ 	
		return _var('formID');
	}
	for( $i=1; $i<10; $i++ ){
		if(_var('formID'.$i)){ 	
			return _var('formID'.$i);
		}	
	}
	return false;
}


function _var($value){
    if ( isset($_GET[$value]) ) {
    	$var = $_GET[$value];
    	return $var;
    }elseif( isset($_POST[$value]) ) {
    	$var = $_POST[$value];
   	 	return $var;
    }else{
        return false;
    }
}

function debugga($obj,$label=''){
	if( isset($obj) && !is_null($obj) && $obj !== false ){	
	    echo '<pre>';
	    if( $label ){
			echo "<b>$".$label."</b></br>";
		 }
	    print_r($obj);
	    echo '</pre>';
	}else{
		if( $label ){
				echo "<b>$".$label."</b></br>";
				echo '$undefined';
		}else{
				echo '<b>$undefined</b>';
		}
	
	}
}


function okArray($array){
	if(!empty($array) && is_array($array) && count($array)>0){
		return true;
	}else{
		return false;
	}
}





if(!function_exists('mime_content_type')) {

	function mime_content_type($filename) {

		$mime_types = array(

			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = strtolower(array_pop(explode('.',$filename)));
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else {
			return 'application/octet-stream';
		}
	}
}

//restituisce l'utente se loggato sottoforma di oggetto
function getUser(){
	return Marion::getUser();
}

function isCiro(){
	$user = Marion::getUser();
	if($user && $user->username == 'cironapo'){
		return true;
	}
	return false;
}



function isBackendSide(){
	return preg_match('/\/backend\//',$_SERVER['REQUEST_URI']);
}



function authUser(){
	$user = getUser();
	
	if( is_object($user) ){
		return true;
	}else{
		return false;
	}
}

function authUserNotLogged(){
	if( !authUser() && $_SESSION['sessionCart']['data']['password_not_logged']){
		return true;
	}else{
		return false;
	}
}

function authAdminUser(){
	$user = getUser();
	if(!is_object($user)) return false;
	return $user->authAdminUser();
}




function auth($type){
	return Marion::auth($type);
}


function isLocked(){
	return Marion::isLocked();
}



function __($string,$locale=NULL,$parameter=array()){
	
	if( $locale ){
		$filegettext  = '../locale/'.$locale. '/LC_MESSAGES/'.
									$GLOBALS['setting']['default']['TEMPLATE']['options']['textdomain'].'.po';
	}else{
		$filegettext  = '../locale/'.$GLOBALS['activelocale']. '/LC_MESSAGES/'.
									$GLOBALS['setting']['default']['TEMPLATE']['options']['textdomain'].'.po';
	}

	$gettext = new File_Gettext_PO($filegettext);
	$gettext->load();
	
	if( okArray($parameter) ){
		return vsprintf($GLOBALS['gettext']->strings[$string],$parameter);
	}else{
		return $GLOBALS['gettext']->strings[$string];
	}
	
}




function __module($module_dir,$string,$locale=NULL,$parameter=NULL,$out_module=FALSE){
	if( $out_module ){
		if( $locale ){
			$filegettext  = "modules/{$module_dir}/locale/".$locale. '/LC_MESSAGES/'.
										$GLOBALS['setting']['default']['TEMPLATE']['options']['textdomain'].'.po';
		}else{
			$filegettext  = "modules/{$module_dir}/locale/".$GLOBALS['activelocale']. '/LC_MESSAGES/'.
										$GLOBALS['setting']['default']['TEMPLATE']['options']['textdomain'].'.po';
		}
	}else{
		if( $locale ){
			$filegettext  = 'locale/'.$locale. '/LC_MESSAGES/'.
										$GLOBALS['setting']['default']['TEMPLATE']['options']['textdomain'].'.po';
		}else{
			$filegettext  = 'locale/'.$GLOBALS['activelocale']. '/LC_MESSAGES/'.
										$GLOBALS['setting']['default']['TEMPLATE']['options']['textdomain'].'.po';
		}
	}
	
	$gettext = new File_Gettext_PO($filegettext);
	$gettext->load();
	if( okArray($parameter) ){
		$res = vsprintf($gettext->strings[$string],$parameter); 
	}else{
		$res = $gettext->strings[$string];
	}
	
	if( $res ){
		return $res;	
	}else{
		return __($string,$locale,$parameter);
	}
	
}



 /*
	Funzione:: getConfig
	Descrizione: prende il valore di configurazione per una specificata chaive ed etichetta. Questio dati sono memorizzati nel config.ini o letti in  read_config
	Input:
		$key :: chaive del gruppo di appartenenza
		$label :: etichetta del gruppo

*/
function getConfig($key,$label=NULL){
	if( $label ){
		if( $GLOBALS['setting']['default'][strtoupper($key)][$label] ){
			return $GLOBALS['setting']['default'][strtoupper($key)][$label];
		}else{
			return false;
		}
		
	}else{
		return $GLOBALS['setting']['default'][strtoupper($key)];
	}

}


 /*
	Funzione:: isMultilocale
	Descrizione: verifica se il sito è multilocale
	
*/
function isMultilocale(){
	return Marion::isMultilocale();
}

 /*
	Funzione:: isMultilocale
	Descrizione: verifica se il sito è multilocale
	
*/
function isMulticurrency(){
	return Marion::isMulticurrency();
}


function isDev(){
	if( authUser() ){
		$user = getUser();
		
		return $user->auth('superadmin');
	}else{
		return false;
	}
}


function withinModule(){
	 return preg_match('/\/modules\//',$_SERVER['REQUEST_URI']);
}



function _translate($string,$module=null){
	$params = array();
	//controllo se in input è stato passato un array
	if(okArray($string)){
		foreach($string as $k => $v){
			if($k > 0){
				$params[] = $v;
			}
		}
		$string = $string[0];
		
	}

	//controllo se la stringa è vuota
	if( trim($string) ){
			$_root_document = _MARION_ROOT_DIR_;
			$side = 'frontend';
			if( defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ){
				$side = 'backend';
				$_root_document .= $side."/";
				
				
			}
			// controllo se la stringa è di un modulo
			if( $module ){
				$key =  md5(base64_encode($string));
				if( $GLOBALS['_translate'][$module][$side][$key] ){

					if( okArray($params) ){
						return vsprintf($GLOBALS['_translate'][$module][$side][$key],$params);
					}else{
						return $GLOBALS['_translate'][$module][$side][$key];
					}
					
				
				}else{
				
					if( file_exists(_MARION_MODULE_DIR_.$module.'/translate/'.$GLOBALS['activelocale'].'.php') ){
						$_string = '$GLOBALS['."'_translate']['".$module."']['".$side."']['".$key."']=\"{$string}\";//{$string}\n";
						file_put_contents(_MARION_MODULE_DIR_.$module.'/translate/'.$GLOBALS['activelocale'].'.php', $_string, FILE_APPEND);
					}
					$GLOBALS['_translate'][$module][$side][$key] = $string;
					if( okArray($params) ){
						return vsprintf($string,$params);
					}else{
						return $string;
					}
					
				}
				

			}else{
				
				$key = md5(base64_encode($string));
				if( $GLOBALS['_translate'][$key] ){
					
					if( okArray($params) ){
						return vsprintf($GLOBALS['_translate'][$key],$params);
					}else{
						return $GLOBALS['_translate'][$key];
					}
				}else{
					

					if( file_exists($_root_document.'translate/'.$GLOBALS['activelocale'].'.php') ){
						$_string = '$GLOBALS['."'_translate']['".$key."']=\"{$string}\";//{$string}\n";
						file_put_contents($_root_document.'translate/'.$GLOBALS['activelocale'].'.php', $_string, FILE_APPEND);
					}
					

					$GLOBALS['_translate'][$key] = $string;
					if( okArray($params) ){
						return vsprintf($string,$params);
					}else{
						return $string;
					}
				}
				
			}
		}
		
		return "";
}

function numeroProdottiEsauriti(){
		
	$database = _obj('Database');
	$num = $database->select('count(*) as cont',"product as p join product_inventory as i on i.id_product=p.id","quantity=0 AND id_inventory=1 AND (type=1 OR parent is not NULL) AND deleted=0");
	if( $num[0]['cont'] == 0 ){
		return '';
	}
	return $num[0]['cont'];
}


function _formdata($id=NULL,$name = 'formdata'){
    $formdata = _var($name);
	if( $formdata ){
		$formdata = parse_str($formdata, $params);
		if( $params ){
			$formdata = $params[$name.$id];
		}else{
			return false;
		}
	}
    return $formdata;
}


function array_themes(){
	$dir = "themes";
	$list = scandir($dir);
	$array[0] = 'TUTTI';
	if( okArray($list) ){
		foreach($list as $v){
			if( !in_array($v,array('.','..','admin','superadmin')) ){
				$array[$v] = strtoupper($v);		}
		}
	}
	ksort($array);
	return $array;
}



?>