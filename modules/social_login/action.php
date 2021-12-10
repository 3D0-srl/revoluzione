<?php
use \Marion\Core\Marion;
use Marion\Components\WidgetComponent;
function social_login_buttons(){
	
	$widget = new WidgetComponent('social_login');
	
	$widget->output('buttons.htm');
}

Marion::add_action('display_login','social_login_buttons');


?>