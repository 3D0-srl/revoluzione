#!/usr/bin/php
<?php
use Marion\Core\{Marion,Module};
use Illuminate\Database\Capsule\Manager as DB;
define('_MARION_CONSOLE_',1);
require ('config/include.inc.php');


$parameters = $argv;
$module_type = null;
$search = null;

foreach($parameters as $k => $cmd){
    if( $k == 0) continue;
    if( trim($cmd) && (preg_match("/--type=/",$cmd) || preg_match("/--like=/",$cmd)) ){
        if( preg_match("/--type=/",$cmd )){
            $explode = explode('--type=',$cmd);
            $module_type = trim($explode[1]);
        }
    
        if( preg_match("/--like=/",$cmd )){
            $explode = explode('--like=',$cmd);
            $search = trim($explode[1]);
        }
        
    }
}

//prendo la configurazione del sito
Marion::read_config();
$database = Marion::getDB();


$stored_modules = DB::table('module')->orderBy('tag')->get(['tag as id','name','author as autore','kind as tipo','active'])->toArray();

$installed_modules = [];
$actived_modules = [];
foreach($stored_modules as $data_module){
    $data_module = (array)$data_module;
    $installed_modules[] = $data_module['id'];
    if( $data_module['active']){
        $actived_modules[]= $v['id'];
    }
    
}
$module_directories = scandir(_MARION_MODULE_DIR_);
$modules = [];
foreach($module_directories as $directory){
    if( $directory != '.' && $dir != '..'){
        if( $directory != 'module_starter' ){
            if( file_exists(_MARION_MODULE_DIR_.$directory."/config.xml")){
                $mod = new Module($directory);
                $mod->readXML();
                $info = $mod->config['info'];
                if( $type ){
                    if( $info['kind'] != $kind ) continue;
                }
                if( $like ){
                    if( !preg_match("/{$search}/",$info['tag']) ) continue;
                }
                $modules[] = [
                    '' => in_array($info['tag'],$installed_modules)?'*':'',
                    'id' => $info['tag'],
                    'nome' => $info['name'],
                    'tipo' => $info['kind'],
                    'Attivo' => in_array($info['tag'],$actived_modules)?'Si':'',
                ];
            }
        }
        
    }
    
}
$renderer = new MathieuViossat\Util\ArrayToTextTable($modules);
echo $renderer->getTable();

?>