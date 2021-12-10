<?php
require ('../../../config.inc.php');
require_once ('classes/SendMailTemplate.class.php');
$template = _obj('Template');
if( !authAdminUser() ) header( "Location: /index.php");



$database = _obj('Database');

$action = _var('action');

//$gapi = _obj('Analytics');

$template->current_admin = 'invio_email_template';

if( $action == 'prepara_email'){
	$template->current_admin_child = 'prepara_email';

	get_form($elements,'module_prepara_email',$action.'_ok',$dati);
	
	$template->output_module(basename(__DIR__),'prepara_email.htm',$elements);
	


} elseif ( $action == 'prepara_email_ok'){
	$template->current_admin_child = 'prepara_email';
	$formdata = _var('formdata');

	$array = check_form($formdata,'module_prepara_email');
	//debugga($array);exit;
	if( $array[0] == 'ok'){
		unset($array[0]);
		$temp_email = get_nome_template($array['scegli_template']);
		$template->dati = $array;
		$template->dati['send_mail'] = $array;
		$array['template_email'] = $temp_email;	
		
		
		ob_start();
		$template->output_module(basename(__DIR__),$temp_email,null,null,'mail');
		$template->anteprima = ob_get_contents();
		ob_clean();
		
		
		//debugga($mail);exit;
		$_SESSION['dati_email_template']['contenuto'] = $template->anteprima;
		$_SESSION['dati_email_template']['dati'] = $array;
		
		//$array['messaggio'] = $tmp_messaggio;
		//debugga($_SESSION['dati_email_template']);exit;

		get_form($elements,'module_prepara_email','prepara_email_ok',$array);

		
		$template->output_module(basename(__DIR__),'prepara_email.htm',$elements);

	}else{
		$template->errore = $array[1];
		get_form($elements,'module_prepara_email',$action,$array);
		
		$template->output_module(basename(__DIR__),'prepara_email.htm',$elements);
		
	}
	exit;
} elseif ( $action == 'conferma_invio_ok'){
	$template->current_admin_child = 'prepara_email';
	$dati_email = $_SESSION['dati_email_template'];
	
	$mail = _obj('Mail');
	

	
	$mail->dati = $_SESSION['dati_email_template']['dati'];
	$mail->dati['send_mail'] = $_SESSION['dati_email_template']['dati'];
	
	//salvo il template nel database
	$toinsert_info_mail = array(
		"nome_mittente"			=>	$mail->dati['dati']['nome_mittente'],
		"email_mittente"		=>	$mail->dati['dati']['email_mittente'],
		"nome_destinatario"		=>	$mail->dati['dati']['nome_destinatario'],
		"email_destinatario"	=>	$mail->dati['dati']['email_destinatario'],
		"oggetto"				=>	$mail->dati['dati']['oggetto'],
		"messaggio"				=>	$mail->dati['dati']['messaggio'],
		"template_html"			=>	$mail->dati['contenuto'],
		);
	$database->insert('mail_log_template',$toinsert);


	//imposto i destinatari della mail
	$email = $_SESSION['dati_email_template']['dati']['email_destinatario'];
	$mail->setTo($email);
	
	//imposto il mittente
	$from = $_SESSION['dati_email_template']['dati']['email_mittente'];
	$mail->setFrom($from);

	$nomesito = getConfig('generale','nomesito');

	$mail->setTemplateHtml($dati_email['dati']['template_email'],'invio_email_template');
	
	$subject = $_SESSION['dati_email_template']['dati']['oggetto'];
	
	$mail->setSubject($subject);

	$result = $mail->send();
	//debugga($mail);exit;
	if ($result == 1) {
		$template->messaggio = 'Email inviata con successo';
		$template->link = 'controller.php?action=prepara_email';
		$template->output('continua.htm');
	} else {
		$array = $_SESSION['dati_email_template']['dati'];
		$template->errore = 'Si e\' verificato un errore durante l\'invio';
		get_form($elements,'module_prepara_email','prepara_email_ok',$array);
		
		$template->output_module(basename(__DIR__),'prepara_email.htm',$elements);
		
	}
} elseif ($action == 'add_template') {
	$template->current_admin_child = 'add_template_email';
	
	//leggo dal db i templates inseriti
	$mail_template = SendMailTemplate::get_template();
	
	if (okArray($mail_template)) {
		$template->mail_template = $mail_template;
	}

	
	$template->output_module(basename(__DIR__),'gestione_email_template.htm');
	
} elseif ($action == 'new_template') {
	$template->current_admin_child = 'add_template_email';
	$value = _var('value');
	if( !is_writeable('templates/it/mail') ){
		$template->errore = "Attenzione: La directory <b>templates/it/mail</b> del modulo deve avere i permessi di scrittura";
	}
	if ($value) {
		$array = SendMailTemplate::get_template($value);

		if (okArray($array)) {
			$array = $array[0];
		} else {
			unset($array);
		}
	}
	
	get_form($elements,'form_email_template','new_template_ok',$array);
	
	$template->output_module(basename(__DIR__),'form_email_template.htm',$elements);
	

} elseif ($action == 'new_template_ok') {
	$formdata = _var('formdata');
	$template->current_admin_child = 'add_template_email';
	$array = check_form($formdata,'form_email_template');
	

	

	//debugga($_FILES);exit;
	$info_file = $_FILES['formdata'];
	/*
	if ($info_file['tmp_name']['template_email']) {
		$nome = str_replace(" ", "_", $array['etichetta']);		
		$ok = move_uploaded_file($info_file['tmp_name']['template_email'],'templates/it/mail/'.$nome.'.htm');
	}
	*/
	
	if( $array[0] == 'ok'){
		if( !is_writeable('templates/it/mail') ){
			$array[0] = 'nak';
			$array[1] = "Attenzione: La directory <b>templates/it/mail</b> del modulo deve avere i permessi di scrittura";
		}
	}

	if( $array[0] == 'ok'){
		if (!okArray($info_file) && !$array['codice']) {
			$array[0] = 'nak';
			$array[1] = 'E\' obbligatorio inserire un file';
		}
	}


	if( $array[0] == 'ok'){
		unset($array[0]);

		//prendo il file e lo salvo
		$toinsert = array();

		if ($array['id'] && !$info_file['name']) {
			$toinsert['attivo'] = $array['attivo'];
			$toinsert['etichetta'] = $array['etichetta'];
			$database->update('template_newsletter', "id = {$array['id']}", $toinsert);
		} else {
			$toinsert['nome_template'] = $info_file['name']['template_email'];
			
			$newpath = 'templates/it/mail/'.$toinsert['nome_template'];

			$ok = move_uploaded_file($info_file['tmp_name']['template_email'],$newpath);

			$toinsert['attivo'] = $array['attivo'];
			$toinsert['etichetta'] = $array['etichetta'];
			
			if ($array['id']) {
				$old_data = $database->select('*','template_newsletter',"id={$array['id']}");
				if (okArray($old_data)) {
					$old_data = $old_data[0];
					unlink('templates/it/mail/'.$old_data['nome_template']);
				}
				$database->update('template_newsletter', "id = {$array['id']}", $toinsert);
			} else {
				$database = _obj('Database');
				$toinsert['locale'] = 'it';
				$database->insert('template_newsletter',$toinsert);

			}
		}
		$template->messaggio = 'Template inserito';
		$template->link = 'controller.php?action=add_template';
		$template->output('continua.htm');
	} else {
		$template->errore = $array[1];
		get_form($elements,'form_email_template','new_template_ok',$array);
		
		$template->output_module(basename(__DIR__),'form_email_template.htm',$elements);
		
	}
} elseif ($action == 'delete_email_template') {
	$id = _var('value');
	if (!$id)	$template->output('errore.htm');
	$old_data = $database->select('*','template_newsletter',"id={$id}");
	if (okArray($old_data)) {
		$old_data = $old_data[0];
		unlink('templates/it/mail/'.$old_data['nome_template']);
		$database->delete('template_newsletter',"id={$id}");
		$template->messaggio = 'Template eliminato';
		$template->link = 'controller.php?action=add_template';
		$template->output('continua.htm');
	} else {
		$template->messaggio = 'Non sono stati trovati template';
		$template->link = 'controller.php?action=add_template';
		$template->output('continua.htm');
	}
} else {
	exit;
}


function array_invio_mail_template() {
	$mail_template = SendMailTemplate::get_template();

	if (okArray($mail_template)) {
		$return = array();
		foreach ($mail_template as $riga) {
			$return[$riga['id']] = $riga['etichetta'];
		}
		return $return;
	} else {
		return false;
	}	
}

function sino($a) {
	if ($a == 't' or $a == 1) return 'SI';

	return 'NO';
}

function get_nome_template($codice) {
	$database = _obj('Database');
	$nome = $database->select('*','template_newsletter',"id={$codice}");
	if (okArray($nome)) {
		return $nome[0]['nome_template'];
	}
}


?>