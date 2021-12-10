<?php
class IndexController extends ModuleController{
		

		function setMedia(){
			$action = $this->getAction();
			if( $action == 'conf'){
				
				$this->registerJS("../modules/privalia/js/conf.js");

			}
		}
	
		function display(){
			$action = $this->getAction();
			switch($action){
				
				case 'conf':
					$this->setting();
					//$this->output('setting/form.htm');
					break;
				case 'sincro':
					$this->output('sincro.htm');
					break;
				case 'reports':
					$database = _obj('Database');
					$list = $database->select('*','privalia_feed',"1=1 order by timestamp desc");
					$this->setVar('list',$list);
					$this->output('reports.htm');
					break;
				default:
					$database = _obj('Database');
					$sel = $database->select('count(*) as tot','privalia_profile');
					$this->setVar('num_profiles',$sel[0]['tot']);
					$sel = $database->select('count(*) as tot','privalia_list');
					$this->setVar('num_list',$sel[0]['tot']);
					$this->output('home.htm');
					break;
			}
			
			
		}


		function setting(){
			/*$categories = Catalog::getSectionTree(1);
			$this->setVar('categories',$categories);
			$database = _obj('Database');
			$market = _var('market');
			$profili = $database->select('*','privalia_profile',"market='{$market}'");
			foreach($profili as $p){
				$profiles[$p['id']] = $p['name'];
			}
			$categorie_selezionate = array(6);
			$this->setVar('categorie_selezionate',$categorie_selezionate);
			$this->setVar('profiles',$profiles);*/


			$database = _obj('Database');
			$couriers = $database->select('*','privalia_carrier');
			if( okArray($couriers) ){
				$this->setVar('couriers',$couriers);
			}

			
			$channels = $database->select('*','privalia_channel');
			if( okArray($channels) ){
				
				$this->setVar('channels',$channels);
			}
			
			$shipping_methods = ShippingMethod::prepareQuery()->get();
			$this->setVar('shipping_methods',$shipping_methods);
			if( $this->isSubmitted()){
				$dati = $this->getFormData();
				$array = $this->checkDataForm('privalia_conf',$dati);
				if( $array[0] == 'ok'){
					$array['mapping_shipping'] = $dati['mapping_shipping'];
					$array['channels'] = $dati['channels'];
					unset($array[0]);
					
					foreach($array as $k => $v){
						if( in_array($k,array('mapping_shipping','channels'))) $v = serialize($v);
						Marion::setConfig('privalia',$k,$v);
					}
					Marion::refresh_config();
					$this->displayMessage('Dati salvati con successo');
				}else{
					$this->errors[] = $array[1];
				}
			}else{
				$dati = Marion::getConfig('privalia');
				
				$dati['mapping_shipping'] = unserialize($dati['mapping_shipping']);
				$dati['channels'] = unserialize($dati['channels']);
			}
			
			

			$this->setVar('corrieri_selezionati',$dati['mapping_shipping']);
			$this->setVar('selected_channels',$dati['channels']);
			
			$dataform = $this->getDataForm('privalia_conf',$dati);
			
			$this->setVar('dataform',$dataform);
			//debugga($this);exit;
			$this->output('setting/form.htm');
		}



		function status(){
			$list = Cartstatus::prepareQuery()->where('active',1)->get();
			$stati = array();
			foreach($list as $v){
				$stati[$v->label] = $v->get('name');
			}
			return $stati;
		}

		function privaliaStaus(){
			
			$stati = array(
				'PENDING' => 'PENDING',
				'PROCESSING' => 'PROCESSING',
				'SHIPPED' => 'SHIPPED',
				'CANCELLED' => 'CANCELLED',
			);
			return $stati;
		}




		
}

?>