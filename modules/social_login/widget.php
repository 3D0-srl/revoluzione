<?php
	
	function button_login_facebook($url=NULL){
		
		if( Marion::getConfig('social_login','enable_facebook') ){
			$widget = Marion::widget('social_login');
			$widget->url = $url;
			$widget->output('link_login_facebook.htm');
		}
	
	}

	function button_login_google($url=NULL){
		if( Marion::getConfig('social_login','enable_google') ){
			$widget = Marion::widget('social_login');
			$widget->url = $url;
			$widget->output('link_login_google.htm');
		}
	
	}
?>