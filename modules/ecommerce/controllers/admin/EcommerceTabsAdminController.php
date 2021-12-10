<?php

use Marion\Controllers\TabsAdminModuleController;
class EcommerceTabsAdminController extends TabsAdminModuleController{
	public $_auth = 'ecommerce';
	public $_tab_ctrls = [
			'EcommerceSettingAdminController',
			'StatusOrderAdminController',
			'TaxAdminController',
			'CurrencyAdminController',
			'CartAdminController'
	];
	

	function getTitle(){
		return _translate('ecommerce_settings');
	}

	function display(){
		
		$this->setMenu('conf_eshop');
		parent::display();
	}


}

?>