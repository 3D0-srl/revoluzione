<?php
require_once(_MARION_MODULE_DIR_.'amazon/controllers/admin/AmazonController.php');
class ActionController extends AmazonController{
	private $id_store;
	function setMedia(){
        parent::setMedia();
        $this->registerJS('../modules/amazon/javascript/actions.js');
    }


	function getTabRoute(){
		$marketplace = _var('marketplace');
		$section = _var('section');
		if( !$marketplace) $marketplace = 'UK';
		if( !$section) $section = 'home';
		$this->setVar('tab_market',$marketplace);
		$this->setVar('section_market',$section);
	}

	function display(){
		$this->getTabRoute();
		$this->setMenu('amazon_store');
		$this->setTab('marketplaces');
		$id_store = _var('id_store');
		$this->setVar('id_store',$id_store);
		$this->id_store = $id_store;
		$store = AmazonStore::withId($id_store);
		$markets = array();
		foreach($store->marketplace as $v){
				$image = $this->getBaseUrl()."modules/amazon/".AmazonTool::getMarketplaceImage($v);
				$markets[] = array(
					'image' => $image,
					'name' => $v
				);
		}
		$urlbase = $_SERVER['SERVER_NAME']._MARION_BASE_URL_;
		$this->setVar('urlbase',(_MARION_ENABLE_SSL_?'https://':'http://').$urlbase);
		$cronjobs = array(
			array(
				'descrizione' => "Download risposte relative ai report sottomessi",
				'url' => 'index.php?mod=amazon&ctrl=Cron&action=report_responses&id_store='.$store->id,
				'frequenza' => 'ogni 15 min ',
				'general' => true
			),
			array(
				'descrizione' => "Invio reports",
				'url' => 'index.php?mod=amazon&ctrl=Cron&action=reports&id_store='.$store->id."&market=",
				'frequenza' => 'ogni 3 ore'
			),
			/*array(
				'descrizione' => "Report delle ordini ancora da spedire",
				'url' => 'index.php?mod=amazon&ctrl=Cron&action=unshipped_orders_report&id_store='.$store->id."&market=",
				'frequenza' => 'ogni ora'
			),*/
			array(
				'descrizione' => "Importazione degli ordini",
				'url' => 'index.php?mod=amazon&ctrl=Cron&action=import_orders&id_store='.$store->id,
				'frequenza' => 'ogni ora',
				'general' => true
			),
			array(
				'descrizione' => "Invio prodotti",
				'url' => 'index.php?mod=amazon&ctrl=Cron&action=send_products&id_store='.$store->id."&market=",
				'frequenza' => '2 volte al giorno'
			),

			array(
				'descrizione' => "Invio quantita e prezzi",
				'url' => 'index.php?mod=amazon&ctrl=Cron&action=send_prices_and_inventory&id_store='.$store->id."&market=",
				'frequenza' => 'ogni ora'
			),
			array(
				'descrizione' => "Risposte ai feed sottomessi",
				'url' => 'index.php?mod=amazon&ctrl=Cron&action=feed_responses&id_store='.$store->id,
				'frequenza' => 'ogni 15 min',
				'general' => true
			),

			array(
				'descrizione' => "Ack ordini",
				'url' => 'index.php?mod=amazon&ctrl=Cron&action=ack&id_store='.$store->id,
				'frequenza' => 'ogni 15 min',
				'general' => true
			),


			

			

			
		
		);

		$database = _obj('Database');
		$setting = $database->select('*','amazon_marketplace_setting',"id_store= {$id_store}");
		$_data_setting = [];
		foreach($setting as $v){
			if( $v['setting_key'] == 'percentage' ){
				$_data_setting[$v['marketplace']][$v['setting_key']] = unserialize($v['setting_value']);
			}
			
		}
		$this->setVar('setting_market',$_data_setting);

		$this->setVar('cronjobs',$cronjobs);

		$this->setVar('markets',$markets);


		
		$this->getProfilesTab();


		//exit;
		$this->output('actions/actions.htm');
	}


	function getProfilesTab(){
		$files = scandir(_MARION_MODULE_DIR_.'amazon/categories');
		$profile_categories = [];
		foreach($files as $file){	
			$path = _MARION_MODULE_DIR_.'amazon/categories/'.$file."/".$file.'.php';
			if( is_file($path) ){
				require_once($path);
				$name = explode('.',$file)[0];
				if( class_exists($name) ){
					$obj = new $name();
					if( is_object($obj) ){
						$profile_categories[] = $obj->getName();
					}
				}
				
			}

		}

	

		$this->setVar('profile_categories',$profile_categories);

		$database = _obj('Database');
		$list = $database->select('*','amazon_profile',"id_store={$this->id_store}");
		$profiles = [];
		foreach($list as $v){
			$profiles[$v['marketplace']][] = $v;
		}

		$this->setVar('profiles',$profiles);
	
	}



	function ajax()
	{
		$action = $this->getAction();
		switch($action){
			case 'save_profile':
				$formdata = $this->getFormdata();
				$database = _obj('Database');
				$id = $database->insert('amazon_profile',$formdata);

				if( $id ){
					$risposta = array(
						'result' => 'ok',
						'url' => "index.php?mod=amazon&ctrl=Action&id_store={$formdata['id_store']}&marketplace={$formdata['marketplace']}&section=profiles"
					
					);

				}else{

				}
				
				break;
			case 'form_category':
				$id = _var('id');
				
				$database = _obj('Database');
				$profile = $database->select('*','amazon_profile',"id={$id}");
				if( okarray($profile) ){
					$profile = $profile[0];
					$category = $profile['category'];
					$market = $profile['marketplace'];
					$id_store = $profile['id_store'];

					
	
					require_once(_MARION_MODULE_DIR_."amazon/categories/".$category."/".$category.".php");
					$obj = new $category();
					$html = $obj->getForm($id_store,$market);
					$risposta = array(
						'result' => 'ok',
						'market' => $market,
						'html' => $html
					);
				}
				
				break;
			case 'change_theme':
				$category = _var('category');
				$market = _var('market');
				$id_store = _var('id_store');


				$category = _var('category');
				$theme = _var('theme');
				require_once(_MARION_MODULE_DIR_."amazon/categories/".$category."/".$category.".php");
				$obj = new $category();
				
				
				$risposta = array(
					'result' => 'ok',
					'attributes' => $obj->getAttributesTheme($theme)
				);
				
				echo json_encode($risposta);
				exit;
			break;
			case 'ricarico':
				$formdata = $this->getFormdata();
				$database = _obj('Database');
				$id_store = _var('id_store');
				$market = _var('market');
				$database->delete('amazon_marketplace_setting',"id_store={$id_store} AND marketplace='{$market}' AND setting_key = 'percentage'");
				//debugga($database->error);exit;
				$database->insert('amazon_marketplace_setting',array(
					'id_store' => $id_store,
					'marketplace' => $market,
					'setting_key' => "percentage",
					'setting_value' => serialize(array_values($formdata))

				));
				$risposta = array(
					'result' => 'ok'
				);
				
			break;
		}
		echo json_encode($risposta);
	}
}

?>