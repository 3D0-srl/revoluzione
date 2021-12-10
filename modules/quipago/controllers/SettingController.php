<?php
class SettingController extends ModuleController{
	public $_auth = 'ecommerce';
	public $_twig = true;
	

	function display(){
		$action = $this->getAction();
		$this->setMenu('manage_modules');
		
		$database = _obj('Database'); 

		if( $this->isSubmitted()){
			$dati = $this->getFormdata();

			
            $array = $this->checkDataForm('quipago_conf',$dati);
            
            if( $array[0]  == 'ok'){
               
                foreach($array as $k => $v){
                    Marion::setConfig('quipago_module',$k,$v);
                }
            
                Marion::refresh_config();
                $this->displayMessage('Dati slavati con successo!');
            }else{
                $this->errors[] = $array[1];
            }
			
			

			
			
		}

       
		
		$dataform = $this->getDataForm('quipago_conf',$dati);
		
		$this->setVar('dataform',$dataform);

		$this->output('conf.htm');

		
    }

    function array_status_confirmed(){
		$status_avaiables = CartStatus::prepareQuery()->where('active',1)->where('visibility',1)->orderBy('orderView')->where('label','active','<>')->where('deleted','active','<>')->get();
		foreach($status_avaiables as $v){
			$toreturn[$v->label] = $v->get('name');
		}

		return $toreturn;
	}
    



	


	


}



?>