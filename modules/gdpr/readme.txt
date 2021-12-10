STEP 1
Una volta installato il modulo andare nella pagina del tema area_utente.htm ed inserire il codice nell'elenco "ul"

{widget(#gdpr_link_account#)}

se inserisco invece il codice

{if:auth(#superadmin#)}
{widget(#gdpr_link_account#)}
{end:}

lo vede solo lo sviluppatore (account cironapo) in maniera tale da afre eventuali modifiche prima di renderlo pubblico


STEP 2 MAILMAN
Inserire nel modulo mailman del sito, qualora non presente, nella pagina action.php

/****** INIZIO CODICE IN ACTION.PHP******************/
function mailman_authorization(&$list=array()){
	
	$user = Marion::getUser();
	
	if( is_object($user) ){
		$database = _obj('Database');
		$select = $database->select('*','mailman_subscribe',"email='{$user->email}' and used=1");
		foreach($select as $v){
			$app = $database->select('*','mailman_list',"id={$v['list']}");
			$list[] = array(
				'module' => 'mailman',
				'options' => serialize(array('list' => $v['list'])),
				'name' => "Iscrizione alla newsletter <b>".$app[0]['list_name_view']."</b>"
			);
		}
	}

	

}


Marion::add_action('authorization_user','mailman_authorization');


function mailman_del_authorization($module,$options){
	
	if( $module == 'mailman'){
		
		$user = Marion::getUser();
		
		if( is_object($user) ){
			$database = _obj('Database');
			$database->delete('mailman_subscribe',"email='{$user->email}' AND list={$options['list']}");
			
		}
	}

	

}


Marion::add_action('delete_authorization_user','mailman_del_authorization');
****** FINE CODICE IN ACTION.PHP******************/

Nella pagina widget.php del modulo mailman in corrispondenza dell'array $GLOBALS['campi_mailman_newsletter_form'] impostare il campo 'default' a '0' qualora sia impostato a '1'

Nel modulo mailman, sotto la cartella templates/it aggiungere alle pagine field_newsletter_form.htm, field_newsletter_form_multiple.htm, newsletter.htm il segneute testo

"<p>Puoi annullare l'iscrizione in ogni momento, cliccando no.</p>"
"<p>Puoi annullare l'iscrizione in ogni momento, cliccando elimina.</p>" (newsletter.htm)

STEP 3 (COOKIE ALERT)
Inserire nel modulo cookie_alert del sito, qualora non presente, nella pagina action.php


/****** INIZIO CODICE COOKIE ALERT******************/
function alert_cookie_authorization(&$list=array()){
	
	
	
	if( $_COOKIE['COOKIE_LAW_CONSENT'] ){
		
		$list[] = array(
			'module' => 'cookie_alert',
			'name' => "Consenso ai <b>cookie</b> per migliorare l'esperienza di navigazione"
		);
		
	}

	

}


Marion::add_action('authorization_user','alert_cookie_authorization');


function alert_cookie_del_authorization($module,$options){
	
	if( $module == 'cookie_alert'){
		
		
		setcookie('COOKIE_LAW_CONSENT', '', -1,'/');
		
	}

	

}


Marion::add_action('delete_authorization_user','alert_cookie_del_authorization');
/****** FINE CODICE COOKIE ALERT******************/



STEP 4
Nei form del sito assicurarsi che il flag della privacy non sia spuntato di default
Inserire il testo "" per il flag privacy



STEP5

stilare le pagine del modulo gdpr rispecchiando la grafica del sito