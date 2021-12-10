#!/usr/bin/php
<?php
use ScssPhp\ScssPhp\Compiler;
use Marion\Core\Console;
define('_MARION_CONSOLE_',1);
require ('config/include.inc.php');

$list = scandir(_MARION_THEME_DIR_);
$parameters = array(
    'BASE_URL' => _MARION_BASE_URL_,
    'THEME_DIR' => _MARION_BASE_URL_."themes/"._MARION_THEME_,
);
$string_base = '';
foreach($parameters as $key => $value){
    $string_base .= '$'.$key.':"'.$value.'";';
}
foreach($list as $theme){
    if( $theme != '.' && $theme != '..' ){
        $scss = new Compiler();
        try{
            $path_scss = _MARION_THEME_DIR_.$theme."/theme.scss";
            $data_tmp = '';
            if( file_exists($path_scss) ){
                $data_tmp = file_get_contents($path_scss);
                $data_tmp = $string_base.$data_tmp;
            
                $compressed = $scss->compile($data_tmp);
                $destination = _MARION_THEME_DIR_.$theme."/theme.css";
                file_put_contents($destination,$compressed);
                Console::success($destination);
            } 
            
        }catch( Exception $e){
            Console::error($e->getMessage());
        }
    }
}

?>