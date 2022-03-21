<?php
use Marion\Controllers\ModuleController;
use Shop\CartStatus;
use Marion\Core\Marion;
class SettingController extends ModuleController{
	public $_auth = 'catalog';

	

	function display(){
		$action = $this->getAction();
		$this->setMenu('manage_modules');
		
		 

		if( $this->isSubmitted()){
			$dati = $this->getFormdata();

			
            $array = $this->checkDataForm('elearning_setting',$dati);
            
            if( $array[0]  == 'ok'){
               
                foreach($array as $k => $v){
                    if( $k == 'cart_status'){
                        $v = serialize($v);
                    }
                    Marion::setConfig('elearning_conf',$k,$v);
                }
            
                Marion::refresh_config();
                $this->displayMessage('Dati slavati con successo!');
            }else{
                $this->errors[] = $array[1];
            }
			
		}else{
			$dati =  Marion::getConfig('elearning_conf');
            if( okArray($dati) ){
                $dati['cart_status'] = unserialize($dati['cart_status']);
            }
            
		}

       
		
		$dataform = $this->getDataForm('elearning_setting',$dati);
		
		$this->setVar('dataform',$dataform);

		$this->output('conf.htm');

		
    }

    function cartStatus(){
		$status_avaiables = CartStatus::prepareQuery()
            ->where('active',1)
            ->where('visibility',1)
            ->orderBy('orderView')
            ->where('label','active','<>')
            ->where('deleted','active','<>')
            ->get();
		foreach($status_avaiables as $v){
			$toreturn[$v->label] = $v->get('name');
		}

		return $toreturn;
	}
    



	


	


}



?>