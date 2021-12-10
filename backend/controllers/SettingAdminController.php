<?php
class SettingAdminController extends \Marion\Controllers\Controller{
    public $_auth = '';
	


	
	function display()
	{
		$action = $this->getAction();
		switch($action){
            case 'general':
                $this->displayConf();
            break;
            default:
            
                $this->displayConfBackend();
            break;
		}
    }
    

    function displayConf(){
        $this->setMenu('setting_general');
        $database = _obj('Database'); 
        

        if( $this->isSubmitted()){
            $dati = $this->getFormdata();
           
            $array = $this->checkDataForm('conf_general',$dati);
            
            
            if( $array[0] == 'ok' ){
                //debugga($array);exit;
                if( !in_array($array['default'],unserialize($array['supportati'])) ){
                    $array[0] = 'nak';
                    $array[1] = "Lingua di default non presente nelle lingue supportate";
                }
            }
        
            if($array[0] == 'ok' ){
                
                unset($array[0]);
                foreach($array as $k => $v){
                    if( $k == 'default' || $k == 'supportati' ){
                        $database->update('setting',"gruppo='locale' AND chiave = '{$k}'",array('valore'=>$v));
                    }else{
                        $database->update('setting',"gruppo='generale' AND chiave = '{$k}'",array('valore'=>$v));
                    }
        
                }
               
                /*if( count(unserialize($array['supportati'])) > 1 ){
                    Marion::multilocale(true);
                }else{
                    Marion::multilocale(false);
                }*/
                $cache = _obj('Cache');
                if( $cache->isExisting("setting") ){
                    $cache->delete('setting');
                }
                if( $cache->isExisting("setting_locale") ){
                    $cache->delete('setting_locale');
                }
                $this->displayMessage('Dati salvati con successo!');


                $dati['supportati'] = serialize($array['supportati']);
                
            }else{
                $dati['supportati'] = serialize($dati['supportati']);
                $this->errors[] = $array[1];
            }

        }else{
            $database->update('locale',"code='it'",array('code'=>'it'));

            $select = $database->select('*','setting',"gruppo='generale' order by ordine");
            $select2 = $database->select('*','setting',"gruppo='locale' order by ordine");
    
            
           
            
            //$locales_array = array_supported_locales();
            
            
            foreach($select as $v){
                $dati[$v['chiave']] = $v['valore']; 
            }
            foreach($select2 as $v){
                $dati[$v['chiave']] = $v['valore']; 
            }
        }
	

        $dataform = $this->getDataForm('conf_general',$dati);
        $this->setVar('dataform',$dataform);
        $this->output('conf_general.htm');

    }


	function displayConfBackend(){
		$current_user = Marion::getUser();
        //debugga($current_user);exit;
        if( $this->isSubmitted()){
            $dati = $this->getFormdata();
            
            $array = $this->checkDataForm('conf_marion',$dati);
            if($array[0] == 'ok' ){
                $this->displayMessage('Dati salvati con successo!');
                $current_user->set(
                    array('colorTheme' => $array['colorTheme'],'locale'=>$array['locale'])
                );
                $current_user->save();
                $current_user->save();

                Marion::setUser($current_user);
            }else{
                $this->errors[] = $array[1];
            }
           
            
            
        }else{
            $dati = array();
            $dati['colorTheme'] = $current_user->colorTheme;
            $dati['locale'] = $current_user->locale;
        }

        
        $dataform = $this->getDataForm('conf_marion',$dati);
        $this->setVar('dataform',$dataform);
        $this->output('conf_marion.htm');
        
    }
    


    function array_backend_languages(){
        $locales = Marion::getConfig('locale','supportati');
        foreach($locales as $v){
            $toreturn[$v] = $v;
        }
        return $toreturn;
    }

  
    function array_supported_locales(){
		$database = _obj('Database'); 
		$locales = $database->select('code','locale',"1=1 order by code");
		foreach($locales as $loc){
			$toreturn[$loc['code']] = $loc['code'];
		}

		return $toreturn;

	}
  
}


?>