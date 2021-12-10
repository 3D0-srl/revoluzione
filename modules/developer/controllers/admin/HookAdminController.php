<?php
use Marion\Controllers\AdminModuleController;
use Marion\Core\Marion;
class HookAdminController extends AdminModuleController{
	public $_auth = 'superadmin';
    
    
       function displayList(){


        $this->setMenu('developer_hooks');
        
        foreach(Marion::$actions_module as $action => $functions){
            foreach( $functions as $function => $info){
                $list[$action]['description'] = $this->getDescription($action);
                $list[$action]['functions'][] = array(
                    'module' => $info['module'],
                    'function' => $function
                );
            }

            
        }
        

        $this->setVar('list',$list);
        $this->output('hooks.htm');
        
    }



    function getDescription(string $hook):?string{
        $description = array(
            'init' => "Questo hook viene avviato sempre in fase di avvio dell'applicativo. E' utile per registrare delle funzioni globalmente nel cms.",
            'action_register_twig_function_front' => "Questo hook permette di aggiungere una function <b>twig</b> di template quindi disponibile nel rednering delle pagine html",
            'action_register_media_front' => "Questo hook permette di aggiungere ad un controller dei file <b>css</b> o <b>js</b>. Riceve in input un oggetto di tipo controller."
        );

        
        return $description[$hook]?$description[$hook]:'';
    }



   
    
}