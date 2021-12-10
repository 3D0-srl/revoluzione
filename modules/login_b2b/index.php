<?php

require ('../../../config.inc.php');



$action = _var('action');




if( $action == 'login'){
	
	
	$formdata = _var('formdata');
	$params = array();
	parse_str($formdata, $params);
	$formdata = $params['formdata'];
	if(okArray($formdata)){

		 $array = check_form($formdata,'login');
		 
		 if( $array[0] == 'ok'){
			 $user = User::login($array['username'],$array['password']);
			
			 if( is_object($user)){
				 //metto in sessione l'utente
				 Marion::sessionize('userdata',$user);
				 
				 $risposta = array('result'=>'ok');
			 }else{
				 $risposta = array('result'=>'nak');
			}

		 }else{
			 $risposta = array('result'=>'nak');
		 }
		
	}else{
		$risposta = array('result'=>'nak');	
	}
	echo json_encode($risposta);
	exit;


}elseif( $action == 'lost_pwd'){
	

	$formdata = _var('formdata');
	$params = array();
	parse_str($formdata, $params);
	$formdata = $params['formdata'];
	$array = check_form($formdata,'lostpass');
	if( $array[0] == 'ok'){
		$user = User::prepareQuery()->where('email',$array['email'])->getOne();
		if(is_object($user)){
			$generale = getConfig('generale');

			$subject = sprintf($GLOBALS['gettext']->strings['subject_lostpass'],$generale['nomesito']);
			

			$mail = _obj('Mail');
			$mail->user = $user;

			$mail->serialized = base64_encode(serialize($array_activation));
			$mail->setTemplateHtml('mail_recupero_password.htm');
			$mail->setSubject($subject);
			
			
			$mail->setTo($user->email);
			$mail->setFrom($generale['mail']);
			$mail->send();
			
			 $risposta = array('result'=>'ok','email'=>$user->email);
			//$template->output('recupero_password_grazie.htm');
			
		}else{
			
			$errore = __('no_user');
			$risposta = array('result'=>'nak','error'=>$errore);	
		}
		
	}else{
		$errore = $array[1];
		
		$risposta = array('result'=>'nak','error'=>$errore);
	}
	echo json_encode($risposta);
	exit;
}



?>