<?php
class IndexController extends ModuleController{
    public $_auth = 'cms'; 


    function display(){
        $this->setMenu('manage_modules');
        if( $this->isSubmitted()){
            $dati = $this->getFormdata();
            $array = $this->checkDataForm('google_tag_manager_snippet_setting',$dati);
            if( $array[0] == 'ok' ){
                unset($array[0]);
                foreach($array as $k => $v){
                    Marion::setConfig('google_tag_manager_snippet',$k,$v);
                }
                Marion::read_config();
                $this->displayMessage('Dati salvati con successo');
            }else{
                $this->errors[] = $array[1]; 
            }
        }else{
            $dati = Marion::getConfig('google_tag_manager_snippet');
        }

        $dataform = $this->getDataForm('google_tag_manager_snippet_setting',$dati);
        $this->setVar('dataform',$dataform);
        $this->output('setting.htm');
    }
}

?>