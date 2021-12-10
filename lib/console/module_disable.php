#!/usr/bin/php
<?php
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\{Marion,Console};
define('_MARION_CONSOLE_',1);
require ('config/include.inc.php');

//prendo la configurazione del sito
Marion::read_config();
$module_name = $argv[1];

if( !$module_name ){
    Console::error("Errore: specificare un modulo");
    exit;
} 

if( file_exists(_MARION_MODULE_DIR_.$module_name)){
   
    if( DB::table('module')->where('tag',$module_name)->exists() ){
        require_once(_MARION_MODULE_DIR_.$module_name."/{$module_name}.php");
        $name_class = getModuleClassName($module_name);
        $module = new $name_class($module_name);
        $module->readXML();
        $module->disable();
        if( $module->errorMessage ){
            Console::error($module->errorMessage);
        }else{
            Console::success("Modulo disabilitato con successo");
        }
    }else{
        Console::error("Errore: modulo non installato");
    }
}else{
    Console::error("Errore: modulo non esistente");
}

function getModuleClassName(string $string):string{
    $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    return $str;

}
?>