<?php
use Marion\Controllers\AdminModuleController;
use Marion\Entities\Cms\HomeButton;
class ButtonBackendController extends AdminModuleController{
	public $_auth = 'superadmin';

    
	


	function displayList(){
		$this->setMenu('developer_button_backend');
		
        if( _var('deleted') ){
            $this->displayMessage('Button eliminato con successo');
        }

        if( _var('saved') ){
            $this->displayMessage('Dati salvati con successo');
        }

		$buttons = HomeButton::prepareQuery()->where('active',1)->orderBy('orderView','ASC')->get();
		$this->setVar('list',$buttons);
		
		$this->output('list_backend_button.htm');
	}


	function displayForm(){

		$this->setMenu('developer_button_backend');
        $id = $this->getID();
        $action = $this->getAction();
        if( $this->isSubmitted()){
            $dati = $this->getFormdata();
            //debugga($dati);exit;
            $array = $this->checkDataForm('backend_button',$dati);
            if( $array[0] == 'ok'){
                if($action == 'edit'){
                    $obj = HomeButton::withId($id);
                }else{
                    $obj = HomeButton::create();
                }
				
                $obj->set($array);
                $obj->save();
				
                $this->redirectToList(array('saved'=>1));
            }else{
                $this->errors[] = $array[1];
            }


        }else{
            $dati = null;
            if($action == 'edit'){
                $obj = HomeButton::withId($id);
                if(is_object($obj)){
                    $dati = $obj->prepareForm2();
                }
                
            }
           
        }

        $dataform = $this->getDataForm('backend_button',$dati);

        $this->setVar('dataform',$dataform);
        
		

		$this->output('form_backend_button.htm');
	}

	
	
    function delete(){
        $id = $this->getID();
        $obj = HomeButton::withId($id);
        if(is_object($obj)){
            $obj->delete();
        }
        $this->redirectToList((array('deleted' => 1)));
        
    }
}