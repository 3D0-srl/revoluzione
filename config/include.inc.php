<?php
use Marion\Core\Marion;

use League\BooBoo\BooBoo;

use Marion\Utilities\ErrorHtmlFormatter;
use League\BooBoo\Formatter\NullFormatter;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Dotenv\Dotenv;
require_once (dirname(__FILE__).'/../vendor/autoload.php');
require (dirname(__FILE__).'/env.php');

$dotenv = new Dotenv();
//carico le variabili d'ambiente
$dotenv->load(_MARION_ROOT_DIR_.'.env');

if( defined('_MARION_MEMORY_LIMIT_') ){
	ini_set('memory_limit', _MARION_MEMORY_LIMIT_);
}
if( defined('_MARION_MAX_EXECUTION_TIME_') ){
	ini_set('max_execution_time', _MARION_MAX_EXECUTION_TIME_);
}

if( defined('_MARION_DEFAULT_TIMEZONE_') ){
	date_default_timezone_set(_MARION_DEFAULT_TIMEZONE_);
}


$_MARION_ENV = array(
	"DATABASE" => array(
		"options" => array(
			"host" => $_ENV["DB_HOST"],
			"nome" => $_ENV["DB_NAME"],
			"password" => $_ENV["DB_PASS"],
			"user" => $_ENV["DB_USER"],
			"port" => $_ENV["DB_PORT"],
			"cache" => 0,
			"cache_file" => 1,
			"pathcache" => "cache",
			"lifetime" => 10000000,
			"log" => 0,
		),
	),
	"CACHE" => array(
		"active" => 0,
		"time" => 1000000,
		"storage" => "files",
		"path" => _MARION_ROOT_DIR_."cache",
		"securityKey" => "aGt784=nuovo",
	),
	"ACCESSO" => array(
		"restrected" => 0
	),
	"TWIG" => array(
		"cache" => 0
	),
	
);
/** DA ELIMINARE */
set_include_path(get_include_path().PATH_SEPARATOR._MARION_LIB_."PEAR_7".PATH_SEPARATOR._MARION_LIB_."PEAR_7/HTML");


require_once 'File/Gettext/PO.php';

if( _MARION_DISPLAY_ERROR_ ){
	ini_set('display_errors', 1);
	$booboo = new BooBoo([]);
	$html = new ErrorHtmlFormatter();
	$null = new NullFormatter();
	$html->setErrorLimit(E_ERROR | E_WARNING | E_USER_ERROR | E_USER_WARNING);
	$null->setErrorLimit(E_ALL);
	$booboo->pushFormatter($null);
	$booboo->pushFormatter($html);
	$booboo->register();
}

/** ELOQUENT ORM */
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_MARION_ENV["DATABASE"]['options']["host"],
    'database'  => $_MARION_ENV["DATABASE"]['options']["nome"],
    'username'  => $_MARION_ENV["DATABASE"]['options']["user"],
    'password'  => $_MARION_ENV["DATABASE"]['options']["password"],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);


// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
$capsule->bootEloquent();

/** END ELOQUENT */

require_once ( _MARION_LIB_.'classes/form/FormTabsContainer.class.php');
require_once _MARION_LIB_.'core/FormHelper.class.php';
require_once _MARION_LIB_."classes/string/wlString.php";
require_once ( _MARION_LIB_.'classes/image/Image.class.php');
require_once ( _MARION_LIB_.'classes/attachment/Attachment.class.php');
require_once ( _MARION_LIB_.'classes/attachment/Attachment.trait.php');
require ( _MARION_LIB_.'classes/form/Form.class.php');
require ( _MARION_LIB_.'classes/file/File.class.php');
require_once ( _MARION_LIB_.'classes/utils/Collection.php');

require_once(_MARION_LIB_."functions.php");
$GLOBALS['setting']['default'] = $_MARION_ENV;

if (_MARION_ENABLE_SSL_ && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off")) {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}


Marion::loadModules(); //carico i moduli
Marion::loadHooks(); // carico gli hooks
Marion::loadRoutes(); // carico le routes
Marion::do_action('boot'); // carico tutti gli hook registrati su boot



if( !defined('_MARION_CONSOLE_')){
	session_start(); //avvio la sessione
}


Marion::loadLang(); //carico le traduzioni
Marion::loadCurrency(); //carico la valuta
Marion::loadTheme(); //carico il tema

Marion::detectClient(); //determino il tipo di client che sta navigando il sito





if( defined('_MARION_ADMIN_SIDE_') && _MARION_ADMIN_SIDE_ ){
	$_current_user = Marion::getUser();
	if( is_object($_current_user) && $_current_user->locale ){
		
		$GLOBALS['activelocale'] = $_current_user->locale;
	}
	
}
$GLOBALS['filegettext'] = _MARION_ROOT_DIR_."locale/".$activelocale. '/LC_MESSAGES/messages.po';



$GLOBALS['gettext'] = new File_Gettext_PO($filegettext);
$gettext->load();
unset($filegettext);


//require (_MARION_LIB_.'setting.php');

//prendo la configurazione del sito
Marion::read_config();
Marion::do_action('init');
?>
