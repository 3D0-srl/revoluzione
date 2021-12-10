<?php
use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
use CookieAlert\Cookie;
class WidgetCookieAlert extends  PageComposerComponent{
	

	function registerJS($data=null){
		PageComposer::registerJS("modules/cookie_alert/js/jquery-eu-cookie-law-popup.js");
	}
	
	function registerCSS($data=null){
		PageComposer::registerCSS("modules/cookie_alert/css/jquery-eu-cookie-law-popup.css");
	}

	function build($data=null){
			
			$cookieAlert = Cookie::prepareQuery()->getOne();
			//$dati = $cookieAlert->prepareForm();
			if( is_object($cookieAlert) ){
				//debugga($dati,'qui');exit;
				$dati['popupTitle'] = $cookieAlert->get('popupTitle');
				$dati['popupText'] = $cookieAlert->get('popupText');
				$dati['urlPolicy'] = $cookieAlert->get('urlPolicy');
				$dati['buttonContinueTitle'] = $cookieAlert->get('buttonContinueTitle');
				$dati['buttonLearnmoreTitle'] = $cookieAlert->get('buttonLearnmoreTitle');
				
				$dati['autoAcceptCookiePolicy'] = $cookieAlert->get('autoAcceptCookiePolicy');
				$dati['buttonLearnmoreOpenInNewWindow'] = $cookieAlert->get('buttonLearnmoreOpenInNewWindow');
				$dati['agreementExpiresInDays'] = $cookieAlert->get('agreementExpiresInDays');
				$dati['styleCompact'] = $cookieAlert->get('styleCompact');
				$dati['popupPosition'] = $cookieAlert->get('popupPosition');
				
			}
			

			
			
			$this->setVar('module_cookie_alert_json',json_encode($dati));
			$this->setVar('module_cookie_alert',$dati);
			
			$this->output('alert_cookie.htm');


		
	}


	function isEditable(){
		

		return false;
	}
}





?>