<?php
use Marion\Core\Console;
define('_MARION_CONSOLE_',1);
require ('config/include.inc.php');
$migration_name = $argv[1];

if( !$migration_name ){
    Console::error("Specificare il nome della migrazione");
    //echo "\033[31m Errore: Specificare il nome della migrazione\n\033[0m";
    exit;
}

if( preg_match('/[\_\-0-9%\/^!#\@\s]/',$migration_name) ){
    Console::error("Errore: il nome della migration deve essere camelCase. Non può includere caratteri speciali e numeri.");
    exit;
}


foreach($argv as $p){
    if( preg_match('/m:/',$p)){
        $module = preg_replace('/m:/','',$p);
        break;
    }
    if( preg_match('/--module=/',$p)){
        $module = preg_replace('/--module=/','',$p);
        break;
    }
}

$tmp_migration_name = $migration_name;
$tmp_migration_name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $tmp_migration_name));
$name_file = strftime('%Y_%m_%d_%H_%M_%S',time())."_".$tmp_migration_name.".php";

$path_migration = '';
if( $module ){
    if( !file_exists(_MARION_MODULE_DIR_.$module) ){
        echo "\033[31m Errore: Il modulo non esiste\n\033[0m";
    }else{
        if( !file_exists(_MARION_MODULE_DIR_.$module."/migrations") ){
            mkdir(_MARION_MODULE_DIR_.$module."/migrations");
            Console::success(_MARION_MODULE_DIR_.$module."/migrations");
        }
        $path_migration = _MARION_MODULE_DIR_.$module."/migrations/{$name_file}";
    }
}else{
    if( !file_exists(_MARION_ROOT_DIR_."migrations") ){
        mkdir(_MARION_ROOT_DIR_."migrations");
        Console::success(_MARION_ROOT_DIR_."migrations");
    }
    $path_migration = _MARION_ROOT_DIR_."migrations/{$name_file}";
}

if( file_exists($path_migration) ){
    Console::error("Errore:il file {$name_file} già esiste");
    exit;
}else{
    $data = "<?php
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Migration;
class {$migration_name}Migration extends Migration{
    
    public function up(){
        //to do
    }
    
    public function down(){
        //to do
    }
}
?>";
file_put_contents($path_migration,$data);
Console::success($name_file);
}    
?>