<?php
class AmazonAdminController extends AdminModuleController{
    public $store;

    public function init(){
        parent::init();
        $id_store = _var('id_store');
        $store = AmazonStore::withId($id_store);
        $this->store = $store;
        $this->setVar('store',$store);
        $this->setListOption('html_template','layouts/amazon_list.htm');
    }


    function setMedia(){
        parent::setMedia();
        $this->registerCSS('../modules/amazon/css/header.css');
    }

    public function output($tmpl)
    {
        $title = $this->getListOption('title');
        
        $this->setVar('amazon_page_title',$title);
        parent::output($tmpl);
        
    }


    function setTab($tab){
        $this->setVar('amazon_tab',$tab);
    }
}

?>