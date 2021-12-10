<?php
namespace Marion\Core;
class Console{

    public static function success($message): void{
        echo "\e[32m{$message}\n\033[0m";
    }

    public static function error($error): void{
        echo "\033[31m{$error}\n\033[0m";
    }

    public static function php($cmd){
        global $_ENV;
        if( isset($_ENV['PHP_PATH'] ) ){
            exec($_ENV['PHP_PATH']." ".$cmd);
        }else{
            exec("php ".$cmd, $output);
        }
        return $output;
    }
}