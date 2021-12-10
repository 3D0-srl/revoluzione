<?php
use Marion\Entities\Cms\PageComposer;
class IndexController extends \Marion\Controllers\Controller{
	public $_auth = 'admin';
	
	

	function loadComposer(){
		


		$this->addTemplateFunction(
			new \Twig\TwigFunction('page_composer', function ($block,$composer_name=null) {
				return page_composer($block,$composer_name);
			})
		);


		
		$composer = new PageComposer(_PAGE_COMPOSER_DASHBOARD_PAGE_ID_);
		
		$this->setVar('dashboard_page_id',_PAGE_COMPOSER_DASHBOARD_PAGE_ID_);
		$composer->addDataToCtrl($this);


	
	

	}
	
	function display(){
		$this->loadComposer();
		$this->output('index.htm');
	}

	

}



?>