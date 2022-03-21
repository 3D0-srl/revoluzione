<?php
use Marion\Components\WidgetComponent;
use Marion\Core\Marion;
function recensioni_home(){
		
    $widget = new WidgetComponent('recensioni');
    
    $widget->output('box_home.htm');
}

Marion::add_action('display_backend_home','recensioni_home',200);
?>