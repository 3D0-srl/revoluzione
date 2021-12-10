<?php
use \Marion\Core\Marion;

function google_tracking_display_header(){

        $widget = new WidgetComponent('google_tracking');
        $widget->output('header.htm');
	

}

Marion::add_action('display_header','google_tracking_display_header');


function google_tracking_display_before_body(){

        $widget = new WidgetComponent('google_tracking');
        $widget->output('before_body.htm');
	

}

Marion::add_action('display_before_body','google_tracking_display_before_body');



?>