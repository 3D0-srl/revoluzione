<?php

class Marion{
	//widgets hooks array   
    private static $widgets = array();
	
	//action hooks array   
	private static $actions = array();
	
	
	public static $actions_module = array();

	//action hooks priority array   
    private static $_priority_actions = array();
	
	public static function loadHooks(){
		$db = self::getDB();
		$select = $db->select('h.name as hook_name,a.function,a.priority,m.active,m.directory as module_name',"(hook as h join hook_action as a on h.id=a.id_hook) join module as m on m.id=a.id_module","m.active=1");
		if( okArray($select) ){
			foreach($select as $v){
				
				//if( !array_key_exists($v['hook_name'],self::$actions_module)){
				$key = "{$v['module_name']}::{$v['function']}";
				self::$actions_module[$v['hook_name']] = array(
					$key => array(
						'priority' => $v['priority'],
						'module' => $v['module_name'],
					)
				);
				//}
	
				self::$actions[$v['hook_name']][] = $v['module_name']."::".$v['function'];
				self::$_priority_actions[$v['hook_name']][] = $v['priority'];
			
			}
		}
		
	}

	public static $modules = array();
	/**
     * create a hook
     * @param $hook_name
     * @param $description
	 * @param $type
	 * @param $id_module
     */
	 public static function create_hook($hook_name,$description,$type,$id_module=0)
    {    
        $hook_name=mb_strtolower($hook_name);
        
		$database = _obj('Database');
		$check = $database->select('*','hook',"name='{$hook_name}'");
		if( !okarray($check) ){
			$id_hook = $check[0]['id'];
			$toinsert = array(
				'name' => $hook_name,
				'description' => $description,
				'type' => $type,
				'id_module' => $id_module
			);
			$id = $database->insert('hook',$toinsert);
			if( $id ){
				return true;
			}
		}
        return FALSE ;
    }
	
	
	/**
     * regiters a function to an action hook
     * @param $hook
     * @param $function
	 * @param $id_module
	 * @param $priority
     */
	 public static function register_action($hook,$function,$id_module=0,$priority=10)
    {    
        $hook=mb_strtolower($hook);
        
		$database = _obj('Database');
		$check = $database->select('*','hook',"name='{$hook}'");
		if( okarray($check) ){
			$id_hook = $check[0]['id'];
			$toinsert = array(
				'function' => $function,
				'id_hook' => $id_hook,
				'id_module' => $id_module,
				'priority' => $priority
			);
			$id = $database->insert('hook_action',$toinsert);
			if( $id ){
				return true;
			}
		}
        return FALSE ;
    }

    /**
     * ads a function to an action hook
     * @param $hook
     * @param $function
	 * @param $priority
     */
    public static function add_action($hook,$function,$priority=10)
    {    
        $hook=mb_strtolower($hook);
        // create an array of function handlers if it doesn't already exist
        if(!self::exists_action($hook))
        {
            self::$actions[$hook] = array(); 
        }
 
        // append the current function to the list of function handlers
        if (is_callable($function))
        {
            self::$actions[$hook][] = $function;
			self::$_priority_actions[$hook][] = $priority;
			
			$ewquired_files_list = get_required_files();
			$last_required_file = $ewquired_files_list[count($ewquired_files_list)-1];
			
			self::$actions_module[$hook][$function] = array(
				'priority' => $priority,
				'module' => basename(dirname($last_required_file)),
			);
			
			
            return TRUE;
        }
		
		
 
        return FALSE ;
    }
 
	/**
     * executes the functions for the given hook
     * @param string $hook
     * @param array $params
     * @return boolean true if a hook was setted
     */
    public static function do_action($hook,$params=NULL)
    {

		
        $hook=mb_strtolower($hook);
        if(isset(self::$actions[$hook]))
        {
			
			//ordino le funzioni per priorità
			foreach(self::$actions[$hook] as $k => $v){
				$actions_sorted[$k]['function'] =$v;
				$actions_sorted[$k]['priority'] =self::$_priority_actions[$hook][$k];
			}

			uasort($actions_sorted,function($a,$b){
				if ($a['priority']==$b['priority']) return 0;
				return ($a['priority']<$b['priority'])?-1:1;
			});

			
            // call each function handler associated with this hook
            foreach($actions_sorted as $function)
            {
				//debugga($function);
				
				if( preg_match('/::/',$function['function']) ){
					list($module,$func) = explode('::',$function['function']);
					$path_class = _MARION_MODULE_DIR_.$module."/".$module.".php";
					require_once($path_class);
					//debugga($path_class);exit;
					$class_name = ucwords($module);
					if( class_exists($class_name) ){
						$obj = new $class_name();
						$obj->$func(is_array($params)?$params:null);
					}
					
				}else{
					if (is_array($params) )
					{
						call_user_func_array($function['function'],$params);
					}
					else 
					{
						call_user_func($function['function']);
					}
				}
				
                //cant return anything since we are in a loop! dude!
            }
            return TRUE;
        }
        return FALSE;
    }
 
	/**
     * gets the functions for the given hook
     * @param string $hook
     * @return mixed 
     */
    public static function get_action($hook)
    {
        $hook=mb_strtolower($hook);
        return (isset(self::$actions[$hook]))? self::$actions[$hook]:FALSE;
    }
 
	/**
     * check exists the functions for the given hook
     * @param string $hook
     * @return boolean 
     */
    public static function exists_action($hook)
    {
        $hook=mb_strtolower($hook);
        return (isset(self::$actions[$hook]))? TRUE:FALSE;
    }


	public static function enabledCache(){
		global $_MARION_ENV;
		return $_MARION_ENV['CACHE']['active'];
	}

	public static function getCacheLifeTime(){
		global $_MARION_ENV;
		return $_MARION_ENV['CACHE']['time'];
	}

	public static function loadTheme(){
		 if (self::exists_action('load_theme'))
        {
            return self::do_action('load_theme',func_get_args());
        }else {	
			//prendo l'oggetto di ccahe
			$cache = _obj('Cache');
			
			
			//verifico se la cache è attiva
			//debugga('qua');exit;
			$data = array();
			// se la cache è attiva allora prendo l'eventuale configurazione salvata
			if(self::enabledCache()){
				$data = $cache->get('setting_themes');
			}
			
			
			if(!okArray($data)){
				$database = _obj('Database');
				$theme_setting = $database->select('chiave,valore','setting',"gruppo='theme_setting'");
				
				$data = array();
				if( okArray($theme_setting) ){
					foreach($theme_setting as $k => $v){
						$data[$v['chiave']] = $v['valore'];
					}
				}
				
				if( self::enabledCache() ){
					
					$cache->set('setting_theme',$data,self::getCacheLifeTime());
				}
					
			}

			if(okArray($data)){
				$GLOBALS['activetheme'] = $data['active'];
				define('_MARION_THEME_',$GLOBALS['activetheme']);
			}
		
		}

	}


	public static function detectClient(){
		//unset($_SESSION['_MARION_DEVICE_']);
		if($_SESSION['_MARION_DEVICE_'] ){
			define('_MARION_DEVICE_',$_SESSION['_MARION_DEVICE_']);
			define('_MARION_BROWSER_',$_SESSION['_MARION_BROWSER_']);
			define('_MARION_ENABLE_WEBP_',$_SESSION['_MARION_ENABLE_WEBP_']);
		}else{
			//classe che permette di verificare se il client è mobile/tablet/web
			
			require_once(dirname(__FILE__)."/utils/Mobile_Detect.php");
			require_once(dirname(__FILE__)."/utils/Browser.php");
			
			$detect = New Mobile_Detect();
			$browser = new Browser();
			
			//debugga($detect);exit;
			// Exclude tablets.
			if( $detect->isMobile() ){
				if( $detect->isTablet() ){
					define('_MARION_DEVICE_','TABLET');
				}else{
					define('_MARION_DEVICE_','MOBILE');
				}
			}else{
				if( $detect->isTablet() ){
					define('_MARION_DEVICE_','TABLET');
				}else{
					define('_MARION_DEVICE_','DESKTOP');
				}
				
			}
			

			define('_MARION_BROWSER_',$browser->getBrowser());
			
			if( (_MARION_BROWSER_ == 'Firefox'|| _MARION_BROWSER_ == 'Chrome') && !$detect->isiOS() ){
				
				define('_MARION_ENABLE_WEBP_',1);
			}else{
				define('_MARION_ENABLE_WEBP_',0);
			}
			
			$_SESSION['_MARION_BROWSER_'] = _MARION_BROWSER_;
			$_SESSION['_MARION_ENABLE_WEBP_'] = _MARION_ENABLE_WEBP_;
			$_SESSION['_MARION_DEVICE_'] = _MARION_DEVICE_;
		}
		

	}

	

	public static function loadLang(){
		$config = array();
		//prendo l'oggetto di ccahe
		$cache = _obj('Cache');
		
		
		//verifico se la cache è attiva
		$cache_active = $GLOBALS['setting']['default']['CACHE']['active'];
		

		// se la cache è attiva allora prendo l'eventuale configurazione salvata
		if($cache_active){
			$config = $cache->get('setting_locale');
		}
		if(!okArray($config)){
			$database = _obj('Database');
			
			$locale_setting = $database->select('chiave,valore','setting',"gruppo='locale'");
			
			foreach($locale_setting as $v){
				if( $v['chiave'] == 'supportati' ){
					$config[$v['chiave']] = unserialize($v['valore']);
					if( okArray($config[$v['chiave']]) ){
						$where = "code in (";
						foreach($config[$v['chiave']] as $v1){
							$where .= "'{$v1}',";
						}
						$where = preg_replace('/\,$/',")",$where);
						$time_locale = $database->select('code,time',"locale",$where);
						if( okArray($time_locale) ){
							foreach($time_locale as $v2){
								$time_locale_config[$v2['code']] = $v2['time'];
							}
							$config['timezone'] = $time_locale_config;
						}
						
					}
				}else{
					$config[$v['chiave']] = $v['valore'];
				}
			}

			
			
			if( $GLOBALS['setting']['default']['CACHE']['active'] ){
				$time_cache = $GLOBALS['setting']['default']['CACHE']['time'];
				$cache->set('setting_locale',$config,$time_cache);
			}
				
		}
		foreach($config as $k => $v){
			$GLOBALS['setting']['default']['LOCALE'][$k] = $v;
		}

		
		if( !isset($GLOBALS['activelocale']) ){
			if( _var('lang') ){
				$GLOBALS['activelocale'] = _var('lang');
				$_SESSION['activelocale'] = $GLOBALS['activelocale'];
			}elseif( !empty($_SESSION['activelocale']) ){
				$GLOBALS['activelocale'] = $_SESSION['activelocale'];
			}elseif ( isset($GLOBALS['setting']['default']['LOCALE']['default']) && !empty($GLOBALS['setting']['default']['LOCALE']['default']) ){
				$GLOBALS['activelocale'] = $GLOBALS['setting']['default']['LOCALE']['default'];
			}else{
				$GLOBALS['activelocale'] = 'it';
			}
		}

		define('_MARION_LANG_',$GLOBALS['activelocale']);

		$_locale_timezone = Marion::getConfig('locale','timezone');
		if( okArray( $_locale_timezone) ){
			$_timezone = $_locale_timezone[$GLOBALS['activelocale']];
			//setLocale('LC_TIME',$_timezone.".UTF-8"); // PHP 7 todo
		}
		unset($_timezone);
		unset($_locale_timezone);
		
		

	}


	public static function loadCurrency(){

		$config = array();

		//prendo l'oggetto di ccahe
		$cache = _obj('Cache');
		
		
		//verifico se la cache è attiva
		$cache_active = $GLOBALS['setting']['default']['CACHE']['active'];
		

		// se la cache è attiva allora prendo l'eventuale configurazione salvata
		if($cache_active){
			$config = $cache->get('setting_currency');
		}
		if(!okArray($config)){
			$database = _obj('Database');
			
			// lettura delle valute attive
			$select_valute = $database->select('*','currency',"1=1");
			if( okArray($select_valute) ){
				
				foreach($select_valute as $v){
					if( $v['active'] ){
						if( $v['defaultValue'] ){
							$config['default'] = $v['code'];
						}
						$config['supported'][] = $v['code']; 
						$config['exchangeRate'][$v['code']] =$v['exchangeRate'];
					}
					$config['html'][$v['code']] =$v['html'];
				}
			}

			
			
			if( $GLOBALS['setting']['default']['CACHE']['active'] ){
				$time_cache = $GLOBALS['setting']['default']['CACHE']['time'];
				$cache->set('setting_currency',$config,$time_cache);
			}
				
		}
		foreach($config as $k => $v){
			$GLOBALS['setting']['default']['CURRENCY'][$k] = $v;
		}
		

		//lettura della valuta corrente
		if( !isset($GLOBALS['activecurrency']) ){
			if( _var('currency') ){
				$GLOBALS['activecurrency'] = _var('currency');
				$_SESSION['activecurrency'] = $GLOBALS['activecurrency'];
			}elseif( !empty($_SESSION['activecurrency']) ){
				$GLOBALS['activecurrency'] = $_SESSION['activecurrency'];
			}elseif ( getConfig('currency','default') ){
				$GLOBALS['activecurrency'] = getConfig('currency','default');
			}else{
				$GLOBALS['activecurrency'] = 'EUR';
			}
		}

		define('_MARION_CURRENCY_',$GLOBALS['activecurrency']);
		


	
		

		

	}


	public static function read_config(){
		
		 if (self::exists_action('read_config'))//if we remove this will perform the hooks plus normal functionality
        {
            return self::do_action('read_config',func_get_args());
        }else {

			$config = array();
			//lettura configurazioni
			self::do_action('before_read_config',func_get_args());
			
			//prendo l'oggetto di ccahe
			$cache = _obj('Cache');
			
			//verifico se la cache è attiva
			$cache_active = $GLOBALS['setting']['default']['CACHE']['active'];
			

			// se la cache è attiva allora prendo l'eventuale configurazione salvata
			if($cache_active){
				$config = $cache->get('setting');
			}
			
			//se non esiste una configurazione salvata allora la ricalcolo
			if(!okArray($config)){
				$config = array();
				$database = _obj('Database'); 
				$select_setting = $database->select('*','setting',"gruppo <> 'image' AND gruppo <> 'locale' order by ordine");
				$select_image = $database->select('*','setting',"gruppo = 'image' order by ordine");
				

				if( okArray($select_setting) ){
					foreach($select_setting as $v){
						$config[strtoupper($v['gruppo'])][$v['chiave']] = $v['valore']; 
					}
				}
				if( okArray($select_image) ){
				
					foreach($select_image as $v){
						if( $v['chiave'] == 'resize') $v['valore'] = unserialize($v['valore']);
						$config[strtoupper($v['gruppo'])][$v['etichetta']][$v['chiave']] = $v['valore']; 
					}
				}
				
				//leggo i moduli installati
				$modules = self::$modules;
				
				if( okArray($modules) ){
					foreach($modules as $v){
						$config['MODULES']['installed'][] = $v['directory'];
						if( !$v['active'] ) continue;
						$config['MODULES']['actived'][] = $v['directory'];
						if( $v['scope'] ){
							$config['MODULES'][$v['scope']][] = $v['directory'];
						}else{
							$config['MODULES']['admin'][] = $v['directory'];
							$config['MODULES']['frontend'][] = $v['directory'];
						}
					}
				}
				
				if( $GLOBALS['setting']['default']['CACHE']['active'] ){
					$time_cache = $GLOBALS['setting']['default']['CACHE']['time'];
					$cache->set('setting',$config,$time_cache);
				}
			}
			foreach($config as $k => $v){
				$GLOBALS['setting']['default'][$k] = $v;
			}

			
			
			
			$_root_document = _MARION_ROOT_DIR_;
			
			if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_) {
				$_root_document .= "backend/";
			}
			
			if( file_exists($_root_document."translate/".$GLOBALS['activelocale'].".php")){
							
				require_once($_root_document."translate/".$GLOBALS['activelocale'].".php");
				
			}
			
		
			if (defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ) {
				
				if( okArray($config['MODULES']['admin']) ){
					foreach($config['MODULES']['admin'] as $directory){
						if( file_exists(_MARION_MODULE_DIR_.$directory."/action.php")){
							require_once(_MARION_MODULE_DIR_.$directory."/action.php");
						}
						if( file_exists(_MARION_MODULE_DIR_.$directory."/widget.php")){
							require_once(_MARION_MODULE_DIR_.$directory."/widget.php");
						}

						
						if( file_exists(_MARION_MODULE_DIR_.$directory."/translate/".$GLOBALS['activelocale'].".php")){
							
							require_once(_MARION_MODULE_DIR_.$directory."/translate/".$GLOBALS['activelocale'].".php");
							
						}
					}

				}
			}else{
				
				if( okArray($config['MODULES']['frontend']) ){

				

					foreach($config['MODULES']['frontend'] as $directory){
						
					
						$path_theme_modules = _MARION_THEME_DIR_._MARION_THEME_.'/modules/';
						foreach($config['MODULES']['frontend'] as $directory){
							
							if( file_exists($path_theme_modules.$directory."/action.php")){
								require_once($path_theme_modules.$directory."/action.php");
							}else{
								if( file_exists(_MARION_MODULE_DIR_.$directory."/action.php")){
									require_once(_MARION_MODULE_DIR_.$directory."/action.php");
								}

							}
							if( file_exists($path_theme_modules.$directory."/widget.php")){
								require_once($path_theme_modules.$directory."/widget.php");
							}else{
								if( file_exists(_MARION_MODULE_DIR_.$directory."/widget.php")){
									require_once(_MARION_MODULE_DIR_.$directory."/widget.php");
								}

							}
						
							if( file_exists(_MARION_MODULE_DIR_.$directory."/translate/".$GLOBALS['activelocale'].".php")){
								require_once(_MARION_MODULE_DIR_.$directory."/translate/".$GLOBALS['activelocale'].".php");
							}
						}
					}

					
					
				
				}
			}
			
			if( file_exists(_MARION_ROOT_DIR_.'widget.php') ){
				require_once(_MARION_ROOT_DIR_.'widget.php');
			}
			if( file_exists(_MARION_ROOT_DIR_.'action.php')){
				require_once(_MARION_ROOT_DIR_.'action.php');
			}
		}
		
		//lettura configurazioni
		self::do_action('after_read_config',func_get_args());
		

		//controllo se è stato effettuato il login con un token
		if( _var('token') ){
			if( !authUser()){
				$check_user = User::loginWithToken(_var('token'));
				if( is_object($check_user) ){
					self::setUser($check_user);
				}
			}
		}
		

		if (self::getConfig('generale',"restrict_area")) {
			$_user_restricted = $_SERVER['PHP_AUTH_USER'];
			$_pass_restricted = $_SERVER['PHP_AUTH_PW'];
			//debugga($_SERVER);exit;
			$nomesito = $BLOBALS['setting']['default']['GENERALE']['nomesito'];
			$database = _obj('Database');
			if( !okArray($database->select('*','user',"username='{$_user_restricted}' and password='{$_pass_restricted}' and restricted=1") ) ){
				  header('WWW-Authenticate: Basic realm="'.$nomesito.'"');
				  header('HTTP/1.0 401 Unauthorized');
				  die ("Not authorized");
			}else{
				unset($_user_restricted);
				unset($_pass_restricted);
			}


	
		}

		
		/*if( !preg_match('/\ionicApplication/',$_SERVER['REQUEST_URI']) ){
			
			$_server_name = $_SERVER['SERVER_NAME'];
			$_data_tmp =  Marion::getConfig('theme_domain',$_server_name);
			
			
			if( $_data_tmp['switch_mobile'] ){
			
				$_redirect_url_mobile = $_data_tmp['redirect_mobile'];
				
				if( $_SERVER['HTTP_X_FORWARDED_HOST'] ){
					
					if(  $_SERVER['HTTP_X_FORWARDED_HOST'] != $_redirect_url_mobile) {
						
						require_once dirname(__FILE__)."/mobile.php";
					}
				}else{
					
					if( $_SERVER['HTTP_HOST'] != $_redirect_url_mobile) {
						
						require_once dirname(__FILE__)."/mobile.php";
					}
				}
			}

			
			if( self::getConfig('software',"standalone")){
				
				if(isCiro()){

				}
				if( !authUser() && $_SERVER['REQUEST_URI'] != '/auth.htm' && ($_SERVER['REQUEST_URI'] != '/account.php' || _var('action') != 'login_ajax' ) && !preg_match('/img/',$_SERVER['REQUEST_URI'])){ 
					//debugga($_SESSION['userdata']);exit;
					header('Location: /auth.htm');
				}

			
				if( authUser() && !preg_match('/\/admin\//',$_SERVER['REQUEST_URI']) && !preg_match('/\/cms\//',$_SERVER['REQUEST_URI']) && !preg_match('/img/',$_SERVER['REQUEST_URI']) ){
					header('Location: /admin/admin.php');
				}
			}
			
		}*/
		

		//verifico se il sito è un ecommerce
		/*if( self::isActivedModule('ecommerce') ){
			$last_update = Marion::getConfig('ecommerce','lastUpdatePrices');
			if( !$last_update || $last_update != date('Y-m-d') ){
			
					Marion::setConfig('ecommerce','lastUpdatePrices',date('Y-m-d'));
					Marion::refresh_config();
					Catalog::loadPrices();
			}
			
		}*/
		

		



		
	}


	public static function getConfig($key,$label=NULL){
		if( $label ){
			if( isset($GLOBALS['setting']['default'][strtoupper($key)][$label]) ){
				return $GLOBALS['setting']['default'][strtoupper($key)][$label];
			}else{
				return false;
			}
			
		}else{
			return $GLOBALS['setting']['default'][strtoupper($key)];
		}

	}

	public static function setConfig($group,$key,$value){
		//debugga($key);exit;
		if( $group && $key){
			
			$database = _obj('Database');
			if( okArray($database->select('*',"setting","gruppo='{$group}' AND chiave = '{$key}'") ) ){
				$database->update('setting',"gruppo='{$group}' AND chiave = '{$key}'",array('valore'=>$value));
			}else{
				
				$toinsert = array(
					'gruppo' => $group,
					'chiave' => $key,
					'valore' => $value,
					);
				$database->insert('setting',$toinsert);
				//debugga($database->error);exit;
			}
			
			return true;
		}else{
			return false;
		}

	}

	public static function delConfig($group,$key){
		if( $group && $key){
			$database = _obj('Database');
			$database->delete('setting',"gruppo='{$group}' AND chiave='{$key}'");
			return true;
		}else{
			return false;
		}
	}
	
	public static function refresh_config(){
		
		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			
			$cache->delete('setting');
		}	
		if( $cache->isExisting("setting_locale") ){
			$cache->delete('setting_locale');
		}

		if( $cache->isExisting("setting_currency") ){
			$cache->delete('setting_currency');
		}
		if( $cache->isExisting("setting_theme") ){
			$cache->delete('setting_theme');
		}

		Marion::loadLang();
		Marion::loadCurrency();
		Marion::read_config();

		
		
	}
	

	public static function getLocales(){
		return Marion::getConfig('locale','supportati');
	}

	public static function isMultilocale(){
		
		if( count(getConfig('locale','supportati')) > 1){
			return true;	
		}else{
			return false;
		}
		
	}
	public static function getCurrencies(){
		return Marion::getConfig('currency','supported');
	}

	public static function isMulticurrency(){
		
		if( count(getConfig('currency','supported')) > 1){
			return true;	
		}else{
			return false;
		}
		
	}


	public static function getExchangeRate($code=NULL){
		if( !$code ) $code = $GLOBALS['activecurrency'];
		$rates = self::getConfig('currency','exchangeRate');
		
		$rate = $rates[$code];
		
		if( $rate ){
			return $rate;
		}else{
			return 1;
		}
	}

	public static function getHtmlCurrency($code=NULL){
		if( !$code ) $code = $GLOBALS['activecurrency'];
		$htmls = self::getConfig('currency','html');
		
		$html = $htmls[$code];
		
		if( $html){
			return $html;
		}else{
			return "&euro;";
		}
	}



	public static function getTwig($module_dir=null){
		
		if( $module_dir ){
			$directories_templates_twig = array();
				
			$directories_templates_twig[] = _MARION_MODULE_DIR_.$module_dir."/templates_twig/";
			if( file_exists(_MARION_MODULE_DIR_.$module_dir."/templates_twig/".$GLOBALS['activelocale']) ){
				$directories_templates_twig[] = _MARION_MODULE_DIR_.$module_dir."/templates_twig/".$GLOBALS['activelocale'];
			}
		
			
			$loader = new \Twig\Loader\FilesystemLoader($directories_templates_twig);
			
			
		}else{

			$directories_templates_twig = array();
				
			$directories_templates_twig[] = _MARION_THEME_DIR_._MARION_THEME_."/templates_twig/";
			if( file_exists(_MARION_THEME_DIR_._MARION_THEME_."/templates_twig/".$GLOBALS['activelocale']) ){
				$directories_templates_twig[] = _MARION_THEME_DIR_._MARION_THEME_."/templates_twig/".$GLOBALS['activelocale'];
			}
		
			
			$loader = new \Twig\Loader\FilesystemLoader($directories_templates_twig);
			
	
		}
		

		
		
		$twig = new \Twig\Environment($loader, [
			//'cache' =>  ".."._MARION_TMP_DIR_,
			'debug' => _MARION_DISPLAY_ERROR_,
		]);

		$twig->addFunction(
			 new \Twig\TwigFunction('auth', function ($type) {
				return Marion::auth($type);
			})
		);
			$twig->addFunction(
			 new \Twig\TwigFunction('okArray', function ($array) {
				return okArray($array);
			})
		);

		$twig->addFunction(
			 new \Twig\TwigFunction('tr', function ($string,$module=null) {
				return _translate($string,$module);
			})
		);

		$twig->addFunction(
			new \Twig\TwigFunction('formattanumero', function ($val=NULL) {
				return number_format($val, 2, ',', '');
			})
		);

		$twig->addFunction(
			new \Twig\TwigFunction('getHtmlCurrency', function ($code=NULL) {
				if( !$code ) $code = $GLOBALS['activecurrency'];
				return Marion::getHtmlCurrency($code);
			})
		);


	

		return $twig;
	}

	public static function widget($module_dir){
		
		/*$options_template = self::getConfig('template','options');
		$document_root = preg_replace('/(.*)\//','',$_SERVER['DOCUMENT_ROOT']);
		if( preg_match('/\/modules\//',$_SERVER['REQUEST_URI']) ){
			$path_module = "../../../{$document_root}/modules/{$module_dir}";
		}else{
			$path_module = "../{$document_root}/modules/{$module_dir}";
		}
		$options_template['templateDir'].= ":".$path_module."/templates/it";
		$options_template['templateDir'].= ":".$path_module."/templates";
		$options_template['templateDir'].= ":".$path_module."/templates/{$GLOBALS['activelocale']}";
		if( $GLOBALS['activetheme'] ){
			$options_template['templateDir'].= ":".$path_module."/templates/it";
			$options_template['templateDir'].= ":".$path_module."/templates_{$GLOBALS['activetheme']}";
			$options_template['templateDir'].= ":".$path_module."/templates_{$GLOBALS['activetheme']}/{$GLOBALS['activelocale']}";
			
		}
		
		//debugga($options_template);

		if( class_exists($module_dir."_template") ){
			$class = $module_dir."_template";
			$widget = new $class($options_template);
		}else{
			if( class_exists("My_Template") ){
				$widget = new My_Template($options_template);
			}else{
				$widget = new Template($options_template);
			}
		}*/
		return false;
		//return $widget;

	}
	
	public static function getUser(){
		return User::withData(Storage::get('marion_userdata'));
	}

	public static function logout(){
		Marion::do_action('action_before_logout');
		Storage::unset('marion_userdata');
		session_unset();
		session_destroy();
		Marion::do_action('action_after_logout');

		
	} 


	public static function setUser($user){
		Storage::set('marion_userdata',$user);
		
	}


	public static function auth($type){
		$user = self::getUser();
		if(!is_object($user)) return false;
		return $user->auth($type);
	}


	public static function isLocked(){
		if(!authAdminUser()) return false;
		if($_SERVER['REDIRECT_admin'] != 'active') return false;
		$user = self::getUser();

		if(!is_object($user)) return false;
		return $user->locked;
	}

	
	public static function isActivedModule($module){
		$database = _obj('Database');
		$module = $database->select('*','module',"tag='{$module}' and active=1");

		if( okArray($module) ){
			return true; 
		}else{
			return false;
		}
	}

	public static function slugify($text)
	{
	  // replace non letter or digits by -
	  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

	  // transliterate
	  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	  // remove unwanted characters
	  $text = preg_replace('~[^-\w]+~', '', $text);

	  // trim
	  $text = trim($text, '-');

	  // remove duplicate -
	  $text = preg_replace('~-+~', '-', $text);

	  // lowercase
	  $text = strtolower($text);

	  if (empty($text))
	  {
		return 'n-a';
	  }

	  return $text;
	}



	public static function multilocale($flag=false){
		$database = _obj('Database');
		/*$field_product = $database->fields_table('productLocale');
		foreach($field_product as $v){
			if( $v != 'product' && $v != 'locale'){
				$fields['product'][] = $v;
			}
		}*/
		/*$field_section = $database->fields_table('sectionLocale');
		foreach($field_section as $v){
			if( $v != 'section' && $v != 'locale'){
				$fields['section'][] = $v;
			}
		}*/
		/*$field_page = $database->fields_table('pageLocale');
		foreach($field_page as $v){
			if( $v != 'page' && $v != 'locale'){
				$fields['page'][] = $v;
			}
		}*/
		/*$field_page = $database->fields_table('photoGalleryLocale');
		foreach($field_page as $v){
			if( $v != 'photoGallery' && $v != 'locale'){
				$fields['photoGallery'][] = $v;
			}
		}
		$field_page = $database->fields_table('photoGalleryImageLocale');
		foreach($field_page as $v){
			if( $v != 'photoGalleryImage' && $v != 'locale'){
				$fields['imagePhotoGalleryDetail'][] = $v;
			}
		}
		$field_page = $database->fields_table('articleLocale');
		foreach($field_page as $v){
			if( $v != 'article' && $v != 'locale'){
				$fields['article'][] = $v;
			}
		}
		$field_page = $database->fields_table('articleCategoryLocale');
		foreach($field_page as $v){
			if( $v != 'articleCategory' && $v != 'locale'){
				$fields['articleCategory'][] = $v;
			}
		}*/
		/*$field_page = $database->fields_table('paymentMethodLocale');
		foreach($field_page as $v){
			if( $v != 'paymentMethod' && $v != 'locale'){
				$fields['payment'][] = $v;
			}
		}*/
		/*$field_page = $database->fields_table('shippingMethodLocale');
		foreach($field_page as $v){
			if( $v != 'shippingMethod' && $v != 'locale'){
				$fields['shipping'][] = $v;
			}
		}*/
		/*$field_page = $database->fields_table('userCategoryLocale');
		foreach($field_page as $v){
			if( $v != 'userCategory' && $v != 'locale'){
				$fields['userCategory'][] = $v;
			}
		}*/

		/*$field_page = $database->fields_table('attributeLocale');
		foreach($field_page as $v){
			if( $v != 'attribute' && $v != 'locale'){
				$fields['attribute'][] = $v;
			}
		}*/

		/*$field_page = $database->fields_table('attributeValueLocale');
		if( okArray($field_page) ){
			foreach($field_page as $v){
				if( $v != 'attributeValue' && $v != 'locale'){
					$fields['attributeValue'][] = $v;
				}
			}
		}*/

		/*$field_page = $database->fields_table('priceListLocale');
		if( okArray($field_page) ){
			foreach($field_page as $v){
				if( $v != 'priceList' && $v != 'locale'){
					$fields['priceList'][] = $v;
				}
			}
		}*/

		/*$field_page = $database->fields_table('newsLocale');
		if( okArray($field_page) ){
			foreach($field_page as $v){
				if( $v != 'news' && $v != 'locale'){
					$fields['news'][] = $v;
				}
			}
		}*/

		/*$field_page = $database->fields_table('newsTypeLocale');
		if( okArray($field_page) ){
			foreach($field_page as $v){
				if( $v != 'newsType' && $v != 'locale'){
					$fields['type_news'][] = $v;
				}
			}
		}

		$field_page = $database->fields_table('notificationLocale');
		if( okArray($field_page) ){
			foreach($field_page as $v){
				if( $v != 'notification' && $v != 'locale'){
					$fields['notification'][] = $v;
				}
			}
		}

		$field_page = $database->fields_table('cartStatusLocale');
		if( okArray($field_page) ){
			foreach($field_page as $v){
				if( $v != 'id_cartStatus' && $v != 'locale'){
					$fields['status_cart'][] = $v;
				}
			}
		}
	
		
		if( okArray($fields) ){
			foreach($fields as $k => $v){
				$form = $database->select('*','form',"nome='{$k}'");
				if( okArray($form) ){
					unset($fields[$k]);
					foreach($v as $campo){
						$campo_data = $database->select('codice','form_campo',"campo='{$campo}' and form={$form[0]['codice']}");
						if( okArray($campo_data) ){
							$lista_campi[] = $campo_data[0]['codice'];
						}
					}
				}
				

			}
		}
		Marion::do_action('add_fields_multilocale',array(&$lista_campi));
		
		
		$where = "codice in (";
		foreach($lista_campi as $v){
			$where .= $v.",";
			
		}
		$where = preg_replace('/\,$/',')',$where);
		
		$database->update('setting',"gruppo='form' and chiave='multilocale'",array('valore'=>$flag));
		$database->update('form_campo',$where,array('multilocale' => $flag));
		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			$cache->delete('setting');
		}
		*/
		
	}

	

	/*metodo che crea le combinazioni di valori di un array di array

		
		Marion::combinations(
				array(
					array('A1','A2','A3'), 
					array('B1','B2','B3'), 
					array('C1','C2')
				)
			)


	*/
	public static function combinations($arrays, $i = 0) {
		
		if (!isset($arrays[$i])) {
			return array();
		}
		if ($i == count($arrays) - 1) {
			return $arrays[$i];
		}

		// get combinations from subsequent arrays
		$tmp = Marion::combinations($arrays, $i + 1);

		$result = array();

		// concat each array from tmp with each element from $arrays[$i]
		foreach ($arrays[$i] as $v) {
			foreach ($tmp as $t) {
				$result[] = is_array($t) ? 
					array_merge(array($v), $t) :
					array($v, $t);
			}
		}

		return $result;
	}




	

	//metodo che estende l'operazione di inserimento in sessione di una valore
	public static function sessionize($key=NULL,$value=NULL){
		if( $key && $value ){
			
			
			//verifico se un valore è encodato on base64 e nel caso lo decifro
			if ( base64_encode(base64_decode($value, true)) === $value){
				$value = base64_decode($value);
			}
			
			//verifico se il valore è serializzato e nel caso lo unserializzo
			if( Base::is_serialized($value) ){
				$value = unserialize($value);
			}
			$_SESSION[$key] = $value;
		}
	}



	public static function randomString($len=6){
		
		$result = "";
		$chars = 'abcdefghijklmnopqrstuvwxyz$_?!-0123456789';
		$charArray = str_split($chars);
		for($i = 0; $i < $len; $i++){
			$randItem = array_rand($charArray);
			$result .= "".$charArray[$randItem];
		}
		return $result;
		
	}

	public static function closeDB(){
		if( is_object($GLOBALS['Database']) ){
			$GLOBALS['Database']->close();
		}
	}

	public static function getDB():DatabaseCache{
		if( !isset($GLOBALS['database']) ){
			$options = $GLOBALS['setting']['default']['DATABASE']['options'];
			$database = new DatabaseCache($options);
			$GLOBALS['database'] = $database;

		}
		return $GLOBALS['database'];
	}

	

	/*public static function encrypt( $q ) {
		if( self::getConfig('software',"cryptKey") ){
			$cryptKey = self::getConfig('software',"cryptKey");
		}else{
			$cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
		}
		$qEncoded = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
		return base64_encode($qEncoded);
	}

	public static function decrypt( $q ) {
		if( self::getConfig('software',"cryptKey") ){
			$cryptKey = self::getConfig('software',"cryptKey");
		}else{
			$cryptKey = 'qJB0rGtIn5UB1xG03efyCp';
		}
		$q = base64_decode($q);
		$qDecoded = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
		return $qDecoded;
	}*/

	public static function encrypt( $data ) {
		if( self::getConfig('software',"cryptKey") ){
			$encryption_key = self::getConfig('software',"cryptKey");
		}else{
			$encryption_key = 'qJB0rGtIn5UB1xG03efyCp';
		}
		$ivlen = openssl_cipher_iv_length('aes-256-cbc');
		$iv = substr($encryption_key, 0, $ivlen);
		// Generate an initialization vector
		//$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		// Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
		
		$encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
		// The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
		return base64_encode($encrypted . '::' . $iv);
	}

	public static function decrypt( $data ) {
		if( self::getConfig('software',"cryptKey") ){
			$encryption_key = self::getConfig('software',"cryptKey");
		}else{
			$encryption_key = 'qJB0rGtIn5UB1xG03efyCp';
		}
		// To decrypt, split the encrypted data from our IV - our unique separator used was "::"
		list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
		return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
	}


	public static function exportTheme($name=null){
		if( $name ){
			$database = _obj('Database');
			
			if(isset($_SERVER['HTTPS'])){
				$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
			}else{
				$protocol = 'http';
			}
			$baseurlimage = $protocol . "://" . $_SERVER['HTTP_HOST'];
			

			$select = $database->select('*','page',"theme='{$name}'");
			if( okArray($select) ){
				foreach($select as $v){
					$dati_loc = $database->select('*','pageLocale',"page={$v['id']}");
					unset($v['id']);
					$images = array();
					foreach($dati_loc as $k1 => $v1){
						if( preg_match('/\/media\/images\//',$v1['content']) ){
							preg_match_all('/\/media\/images\/.*/',$v1['content'],$list);
							foreach($list as $b){
								$t = $b[0];
								
								$t = preg_replace('/\s/','',$t);
								$t = preg_replace('/\/>/','',$t);
								$t = preg_replace('/>/','',$t);
								$image = preg_replace('/[\'"]/','',$t);
								if( $image ){
									$path = preg_replace('/^\//','',$image);
									if( file_exists($path) ){
										debugga('we');
									}
									$path = trim($_SERVER['DOCUMENT_ROOT'].$image);
									
									if( file_exists($path) ){
										debugga('qui');exit;
									}
									debugga($path);exit;
									$images[] = array( 'data' => file_get_contents($_SERVER['DOCUMENT_ROOT'].$image),'url' => $image);
									debugga(file_get_contents($_SERVER['DOCUMENT_ROOT'].$image));
									debugga($images);exit;
									/*$path_file = $_SERVER['DOCUMENT_ROOT'].$image;
									debugga($path_file);exit;
									if( file_exists($_SERVER['DOCUMENT_ROOT'].$image) ){
										
										
										debugga($images);exit;
									}*/
									
								}
							}
						}
						unset($dati_loc[$k1]['id']);
						
					}
					$rows[] = array(
						'data' => $v,
						'locale' => $dati_loc,
						'images' => $images
					);
					//debugga($dati_loc);exit;
				}
			}
			
			$toreturn = array(
				'tema' => $name,
				'dati' => $rows,
				'url' => $baseurlimage
			);
			header('Content-disposition: attachment; filename=export.json');
			header('Content-type: application/json');
			echo json_encode($toreturn);
			exit;
		}
	}


	public static function importTheme($name){
		if( $name ){
			$database = _obj('Database');
			
			if(isset($_SERVER['HTTPS'])){
				$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
			}else{
				$protocol = 'http';
			}
			$baseurlimage = $protocol . "://" . $_SERVER['HTTP_HOST'];
			

			$select = $database->select('*','page',"theme='{$name}'");
			if( okArray($select) ){
				foreach($select as $v){
					$dati_loc = $database->select('*','pageLocale',"page={$v['id']}");
					unset($v['id']);
					foreach($dati_loc as $k1 => $v1){
						if( preg_match('/\/media\/images\//',$v1['content']) ){
							preg_match_all('/\/media\/images\/.*/',$v1['content'],$list);
							foreach($list as $t){
								$t = preg_replace('/\s/','',$t);
								$t = preg_replace('/\/>/','',$t);
								$t = preg_replace('/>/','',$t);
								$image = preg_replace('/[\'"]/','',$t);
								$images[] = $image;
							}
						}
						unset($dati_loc[$k1]['id']);
						
					}
					$toreturn[] = array(
						'data' => $v,
						'theme' => $name,
						'locale' => $dati_loc,
						'images' => $images,
						'baseurl' => $baseurlimage
					);
					//debugga($dati_loc);exit;
				}
			}
			header('Content-disposition: attachment; filename=export.json');
			header('Content-type: application/json');
			echo json_encode($toreturn);
			exit;
		}
	}

	public static function chmod_R($path, $filemode, $dirmode) {
		
		if (is_dir($path) ) {
			if (!chmod($path, $dirmode)) {
				$dirmode_str=decoct($dirmode);
				print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
				print "  `-> the directory '$path' will be skipped from recursive chmod\n";
				return;
			}
			$dh = opendir($path);
			while (($file = readdir($dh)) !== false) {
				if($file != '.' && $file != '..') {  // skip self and parent pointing directories
					$fullpath = $path.'/'.$file;
					self::chmod_R($fullpath, $filemode,$dirmode);
				}
			}
			closedir($dh);
		} else {
			if (is_link($path)) {
				print "link '$path' is skipped\n";
				return;
			}
			if (!chmod($path, $filemode)) {
				$filemode_str=decoct($filemode);
				print "Failed applying filemode '$filemode_str' on file '$path'\n";
				return;
			}
		}
	}



	public static function loadRoutes(){
		$path_routes = _MARION_ROOT_DIR_."routes.php";
		if( file_exists($path_routes) ){
			require_once($path_routes);
		}
		if( okArray(self::$modules) ){
			foreach(self::$modules as $v){
				if( $v['active'] ){
					$path_routes = _MARION_MODULE_DIR_.$v['directory']."/routes.php";
					if( file_exists($path_routes) ){
						require_once($path_routes);
					}
				}
				
				
			}
		}

	}


	public static function loadModules(){
		$db = self::getDB();
		self::$modules = $db->select('directory,scope,active','module',"1=1");
	}

}










?>