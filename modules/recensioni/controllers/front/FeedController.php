<?php
use Marion\Controllers\BackendController;
class FeedController extends BackendController{	

    
    function index(){
        $this->setMenu('recensioni');
        $this->output('recensioni.htm');
    }

}

?>