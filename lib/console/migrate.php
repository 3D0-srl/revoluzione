#!/usr/bin/php
<?php
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Console;
define('_MARION_CONSOLE_',1);
require ('config/include.inc.php');

//Marion::read_config();

$params = $argv;
unset($params[0]);

//prendo l'azione da eseguire
foreach($params as $param){
    if( in_array($param,['rollback','list','ls'] )){
        $action = $param;
        break;
    }
}

if( !$action ) $action = 'migrate';

if( isHelp() ){
    switch($action){
        case 'migrate':
            echo "1. migrate [--module=module_name]\n";
            echo "2. migrate ls [--module=module_name]\n";
            echo "3. migrate roolback [--module=module_name]\n";
            break;
    }
    exit;
}


//prendo il modulo se presente nei parametri in ingresso
foreach($params as $param){
    if( preg_match('/m:/',$param) || preg_match('/--module=/',$param) ){
        if( preg_match('/m:/',$param) ){
            $module = preg_replace('/m:/','',$param);
        }else{
            $module = preg_replace('/--module=/','',$param);
        }
        break;
    }
}


try{
    switch($action){
        case 'list':
        case 'ls':

            $migrations_path = _MARION_ROOT_DIR_."/migrations";
            if( $module ){
                $migrations_path = _MARION_ROOT_DIR_."modules/{$module}/migrations";
            }
        
            $migration_files = scandir($migrations_path);
            
            foreach($migration_files as $migration){
                if( is_file($migrations_path.'/'.$migration)){
                    $migration_name = explode('.',$migration)[0];
                    $migrations_list[$migration_name] = [
                        'name' => $migration_name,
                        'module' => '',
                        'timestamp' => ''
                    ];
                }
                
            }
           
            $get_migrations_query =  DB::table('migrations')->orderBy('timestamp','asc');
            if( $module ){
                $get_migrations_query->where('module',$module);
            }else{
                $get_migrations_query->whereNull('module');
            }
            $stored_migrations = $get_migrations_query->get();
           
            foreach($stored_migrations as $v){
                $migrations_list[$v->name] = [
                    'name' => "{$v->name}",
                    'module' => $v->module,
                    'timestamp' => $v->timestamp
                ];
            }
            $renderer = new MathieuViossat\Util\ArrayToTextTable(array_values($migrations_list));
            echo $renderer->getTable();
            break;
        case 'rollback':
            $query =  DB::table('migrations')->orderBy('timestamp','desc');
            if( $module ){
                $query->where('module',$module);
            }else{
                $query->whereNull('module');
            }
        
            $last = $query->first();
            if( $last ){
                $file =  _MARION_ROOT_DIR_.'migrations/'.$last->name.".php";
                if( $module ){
                    $file =  _MARION_ROOT_DIR_."modules/{$module}/migrations/".$last->name.".php";
                }
                require_once($file);
                $classi = get_declared_classes();
                $last = $classi[count($classi)-1];
                $obj = new $last();
                if( $obj->downgrade() ){
                    Console::success($file);
                }
            }
            break;
        case 'migrate':

          
            $script = _MARION_ROOT_DIR_."lib/console/migrate.php";
            
         
            $migration = getMigration();
            if( $migration ){
                $module = getModule();
                if(!$module) $module = null;
                include_once($migration);
                $classi = get_declared_classes();
                $last = $classi[count($classi)-1];
                $obj = new $last($module);
                if( $obj->upgrade() ){
                    Console::success($migration);
                }
                exit;
            }
            
            $output = [];
            if( !$module && file_exists('migrations') ){
                $scandir = scandir('migrations');
                foreach($scandir as $v){
                    $file =  _MARION_ROOT_DIR_.'migrations/'.$v;
                    if( is_file($file)){
                       
                        $result = Console::php("{$script} --migration-path={$file}");
                        $output = array_merge($output,$result);
                    }
                }
            }
            

            $modules = scandir('modules');
            if( $module ) $modules = [$module];
            foreach($modules as $m){
                if( is_file("modules/{$m}") ){
                    $path = "modules/{$m}/migrations";
                    if(file_exists($path)){
                        $scandir = scandir($path);
                        $file =  _MARION_ROOT_DIR_."{$path}/{$v}";
                        if( is_file($file)){
                            $result = Console::php("{$script} --setModule={$m} --migration-path={$file}");
                            $output = array_merge($output,$result);
                        }
                    }
                }
            }
            
            if( is_array($output) ){
                foreach($output as $o){
                    if( trim($o) ){
                       if( preg_match('/\.php/',$o)){
                        echo $o."\n";
                       }
                    }
                }
            }
            break;
    }
}catch( Exception $e){
    Console::error($e->getMessage());
}



function getMigration(): string{
    global $argv;
    $migration = '';
    foreach($argv as $param){
        if( preg_match('/--migration-path=/',$param) ){
           
            $migration = preg_replace('/--migration-path=/','',$param);
            break;
        }
    }
    return $migration;
}

function getModule(): string{
    global $argv;
    $module = '';
    foreach($argv as $param){
        if( preg_match('/--setModule=/',$param) ){
            $module = preg_replace('/--setModule=/','',$param);
            break;
        }
    }
    return $module;
}

function isHelp(): bool{
    global $argv;
    foreach($argv as $param){
        if( preg_match('/--help/',$param) ){
            return true;
            break;
        }
    }
    return false;
}
?>