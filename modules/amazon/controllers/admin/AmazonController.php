<?php
class AmazonController extends ModuleController{
    public $store;

    public function init(){
        parent::init();
        $id_store = _var('id_store');
        $store = AmazonStore::withId($id_store);
        $this->store = $store;
        $this->setVar('store',$store);
       
    }


    function setMedia(){
        parent::setMedia();
        $this->registerCSS('../modules/amazon/css/header.css');
    }



    function setTab($tab){
        $this->setVar('amazon_tab',$tab);
    }
}

?>