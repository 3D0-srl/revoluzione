<?php


require ('../../../config.inc.php');
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$template = _obj('Template');
//if( !authAdminUser() ) header( "Location: /index.php");

require_once ('classes/Mailman.class.php');
include_once 'classes/phpListRESTApiClient.php';


require_once ('classes/MailMarketingCampaign.class.php');
require_once ('classes/MailWidget.class.php');
require_once ('classes/WidgetInterface.php');
require_once ('classes/WidgetBase.class.php');


$_is_demo = true;
$template->is_demo = $_is_demo;

$database = _obj('Database');

$action = _var('action');

$mesi = array(
		1 => 'Gennaio',
		2 => 'Febbaraio',
		3 => 'Marzo',
		4 => 'Aprile',
		5 => 'Maggio',
		6 => 'Giugno',
		7 => 'Luglio',
		8 => 'Agosto',
		9 => 'Settembre',
		10 => 'Ottobre',
		11 => 'Novembre',
		12 => 'Dicembre'
);

$giorni_week = array(
		1 => 'Lunedi',
		2 => 'Martedi',
		3 => 'Mercoledi',
		4 => 'Giovedi',
		5 => 'Venerdi',
		6 => 'Sabato',
		7 => 'Domenica'
);

//$gapi = _obj('Analytics');

//debugga($template);exit;

$template->current_admin = 'mail_marketing';

if( $action == 'add' || $action == 'mod' || $action == 'dup'){
	$template->current_admin_child = 'lists';
	if( $action != 'add'){
		$id = _var('id');
		$list = Mailman::withId($id);
		if( !isMultilocale()){
			$dati = $list->prepareForm(Marion::getConfig('locale','default'));
		}else{
			$dati = $list->prepareForm();
		}
		if( $action == 'dup'){
			$action = 'add';
			unset($dati['id']);
		}

	}
	get_form($elements,'mailman_list',$action.'_ok',$dati);
	if( isMultilocale()){
		$template->output_module(basename(__DIR__),'form_list_multilocale.htm',$elements);	
	}else{	
		$template->output_module(basename(__DIR__),'form_list.htm',$elements);	
	}


}elseif( $action == 'add_ok' || $action == 'mod_ok'){
	
	$formdata = _var('formdata');
	$campi_aggiuntivi['list_name']['obbligatorio'] = 'f';
	$campi_aggiuntivi['domain']['obbligatorio'] = 'f';
	$campi_aggiuntivi['password']['obbligatorio'] = 'f';
	$campi_aggiuntivi['protocol']['obbligatorio'] = 'f';
	$campi_aggiuntivi['locale']['obbligatorio'] = 'f';
	$campi_aggiuntivi['list_name']['obbligatorio'] = 'f';
	if( Marion::auth('superadmin') ){
		$array = check_form($formdata,'mailman_list');
	}else{
		$array = check_form($formdata,'mailman_list',$campi_aggiuntivi);
	}
	


	if( $array[0] == 'ok' ){
		if( $action == 'add_ok' ){
			$list = Mailman::create();
		}else{
			$list = Mailman::withId($array['id']);
		}
		$list->set($array);
		
		$res = $list->save();
		
		if( is_object($res) ){
			$template->link="controller.php?action=lists";
			$template->output('continua.htm');
		}else{
			$template->errore = __module('mailman',$res);
			get_form($elements,'mailman_list',$action,$array);
			if( isMultilocale()){
				$template->output_module(basename(__DIR__),'form_list_multilocale.htm',$elements);	
			}else{	
				$template->output_module(basename(__DIR__),'form_list.htm',$elements);	
			}
		}

		

	}else{
		$template->errore = $array[1];
		get_form($elements,'mailman_list',$action,$array);
		if( isMultilocale()){
			$template->output_module(basename(__DIR__),'form_list_multilocale.htm',$elements);	
		}else{	
			$template->output_module(basename(__DIR__),'form_list.htm',$elements);	
		}	

	}
	
}elseif( $action == 'delete_list'){
	$id = _var('id');
	$obj = Mailman::withId($id);
	if( is_object($obj) ){
		$obj->delete();
	}
	$template->link = "/admin/modules/mailman/controller.php?action=lists";
	$template->output('continua.htm');

}elseif( $action == 'lists'){
	$template->current_admin_child = 'lists';
	$list = Mailman::prepareQuery()->get();

	
	$template->mailist = $list;
	$template->output_module(basename(__DIR__),'list_list.htm');
}elseif( $action == 'statistic'){
	$template->current_admin_child = 'lists';
	$list = _var('list');
	$list = Mailman::withId($list);
	$template->list = $list;
	$year = date('Y');
	$year_prec = strftime('%Y',strtotime('-1 year'));
	
	$iscritti_anno_corrente = $database->select('count(*) as tot, MONTH(dateInsert) as mese','mailman_subscribe',"list={$list->id} AND used = 1 and  YEAR(dateInsert) = '{$year}' GROUP BY MONTH(dateInsert)");
	$iscritti_anno_prec = $database->select('count(*) as tot, MONTH(dateInsert) as mese','mailman_subscribe',"list={$list->id} AND used = 1 and  YEAR(dateInsert) = '{$year_prec}' GROUP BY MONTH(dateInsert)");
	
	foreach($mesi as $k => $v){
		$toreturn_1[$k] = array(
			'label' => $v,
			'tot' => 0
		);
		$toreturn_2[$k] = array(
			'label' => $v,
			'tot' => 0
		);
	}
	if( okArray($iscritti_anno_corrente) ){
		foreach($iscritti_anno_corrente as $v){
			$toreturn_1[$v['mese']]['tot'] = $v['tot'];
		}
	}
	if( okArray($iscritti_anno_prec) ){
		foreach($iscritti_anno_prec as $v){
			$toreturn_2[$v['mese']]['tot'] = $v['tot'];
		}
	}
	//debugga($toreturn);exit;
	$template->iscritti_1 = $toreturn_1;
	$template->year1 = $year;
	$template->year2 = $year_prec;
	$template->iscritti_2 = $toreturn_2;
	$template->output_module(basename(__DIR__),'statistic_list.htm');
}elseif( $action == 'subscribers'){
	$template->current_admin_child = 'lists';
	$list = _var('list');
	$list = Mailman::withId($list);

	$iscritti = $list->getSubscribers();
	$template->list = $list;
	$template->iscritti = $iscritti;
	$template->output_module(basename(__DIR__),'subscribers_list.htm');
}elseif( $action == 'add_campaign' || $action == 'mod_campaign' || $action == 'dup_campaign'){
	Marion::setMenu('campaigns');
	if( $action != 'add_campaign'){
		$id = _var('id');
		$list = MailMarketingCampaign::withId($id);
		if( $list->sent ){
			header('Location: /admin/modules/mail_marketing/controller.php?action=campaigns');
		}
		$dati = $list->prepareForm();
		
		if( $action == 'dup_campaign'){
			$action = 'add_campaign';
			unset($dati['id']);
		}

	}
	get_form($elements,'mail_marketing_campaign',$action.'_ok',$dati);
	$template->output_module(basename(__DIR__),'form_campaign.htm',$elements);	
}elseif( $action == 'add_campaign_ok' || $action == 'mod_campaign_ok'){
	Marion::setMenu('campaigns');
	$formdata = _var('formdata');
	
	$array = check_form($formdata,'mail_marketing_campaign');
	
	if( $array[0] == 'ok' ){
		if( $action == 'add_campaign_ok' ){
			$list = MailMarketingCampaign::create();
		}else{
			$list = MailMarketingCampaign::withId($array['id']);
		}
		$list->set($array);
		
		$res = $list->save();
		//debugga($list);exit;
		if( is_object($res) ){
			$template->link="controller.php?action=campaigns";
			$template->output('continua.htm');
		}else{
			$template->errore = __module('mail_marketing',$res);
			get_form($elements,'mail_marketing_campaign',$action,$array);
			$template->output_module(basename(__DIR__),'form_campaign.htm',$elements);	
		}

		

	}else{
		$template->errore = $array[1];
		get_form($elements,'mail_marketing_campaign',$action,$array);
		$template->output_module(basename(__DIR__),'form_campaign.htm',$elements);	

	}
}elseif( $action == 'delete_campaign'){
	$id = _var('id');
	$obj = MailMarketingCampaign::withId($id);
	if( is_object($obj) ){
		$obj->delete();
	}
	$template->link = "controller.php?action=campaigns";
	$template->output('continua.htm');
}elseif( $action == 'admin_action'){
	$formdata = _var('formdata');
	
	$array = $formdata;
	
	$list = Mailman::withId($array['list']);
	if( $array['action'] == 'subscribe' ){
		
		$res = $list->subscribe_admin($array['email']);
	}else{
		$res = $list->unsubscribe_admin($array['email']);
	}

	if( $res != 1){
		$res = __module('mailman',$res);
		$template->messaggio = "ERROR: {$res}";
	}
	$template->link="controller.php?action=subscribers&list=".$array['list'];
	$template->output('continua.htm');
}elseif( $action == 'mass'){
	$list = _var('list');
	$list = Mailman::withId($list);
	$template->list = $list;
	$template->output_module(basename(__DIR__),'mass_operation.htm');
}elseif( $action == 'mass_ok'){
	$formdata = _var('formdata');
	$array = $formdata;
	
	$list = Mailman::withId($array['list']);
	$emails = explode(PHP_EOL, $array['emails']);
	
	$action_newsletter = $array['action'];
	foreach($emails as $email){
		$email = trim($email);
		if( $action_newsletter == 'subscribe'){
			$list->subscribe_admin($email);
		}else{
			$list->unsubscribe_admin($email);
		}
	}
	$template->link="controller.php?action=mass&list=".$array['list'];
	$template->output('continua.htm');
}elseif( $action == 'delete_email'){
	$list = _var('list');
	$email = _var('email');
	$list = Mailman::withId($list);
	
	$res = $list->unsubscribe_admin($email);
	if( $res != 1){
		$res = __module('mailman',$res);
		$template->messaggio = "ERROR: {$res}";
	}
	$template->link="controller.php?action=subscribers&list=".$list->id;
	$template->output('continua.htm');
} elseif( $action == 'export'){
	$list = _var('list');
	$list = Mailman::withId($list);
	$iscritti = $list->getSubscribers();
	
	header("Cache-Control: ");
	header("Pragma: ");
	header("Accept-Ranges: bytes");
	header("Content-type: application/vnd.ms-excel");
	header("Content-Language: eng-US");
	header("Content-Disposition: attachment; filename=\"".date('Y-m-d').".xls\"");
	header("Content-Transfer-Encoding: binary");

	header("Content-Encoding: ");

	ob_start();//"ob_gzhandler");
	$template->righe = $iscritti;
	$template->output_module(basename(__DIR__),'export.htm');
	$size=ob_get_length();

	$diff = date('Z', $now);
	$gmt_mtime = date('D, d M Y H:i:s', $now-$diff).' GMT';

	header("Last-Modified: ".$gmt_mtime);
	header("Expires: ".$gmt_mtime);
	
	header("Content-Length: $size");
	sleep(1);

	ob_end_flush();

}elseif( $action == 'conf'){
	$template->current_admin_child = 'conf';
	
	$mailman_conf = Marion::getConfig('module_mailman');
	
	get_form($elements,'mailman_conf',$action."_ok",$mailman_conf);
	
	$template->output_module(basename(__DIR__),'conf.htm',$elements);
}elseif( $action == 'conf_ok'){
	$formdata = _var('formdata');
	$array = check_form($formdata,'mailman_conf');
	
	if( $array[0] == 'ok' ){
		//debugga($array);exit;
		unset($array[0]);
		foreach($array as $k => $v){
			Marion::setConfig('module_mailman',$k,$v);	
		}
		Marion::refresh_config();
		$template->link="controller.php?action=conf";
		$template->output('continua.htm');
	}else{
		$template->errore = $array[1];
		get_form($elements,'mailman_conf',$action,$array);
		$template->output_module(basename(__DIR__),'conf.htm',$elements);
	}
}elseif( $action == 'confirm'){
	
	$formdata = _var('serialized');
	$formdata = unserialize(base64_decode($formdata));
	
	$list = $formdata['list'];
	if( $list ){ 
		$list = Mailman::withId($list);
		if( is_object($list) ){
			if( $formdata['action'] == 'subscribe' ){
				$res = $list->confirmEmail($formdata['email'],$formdata['auth']);
				if( $res ){
					$template->info = __module('mailman','subscribe_ok');
					
					Marion::do_widget_module(basename(__DIR__),'newsletter_grazie.htm');
				}else{
					$template->errore_generico(126);
				}
			}else{
				$res = $list->confirmEmailRemove($formdata['email'],$formdata['auth']);
				if( $res ){
					$template->info = __module('mailman','unsubscribe_ok');
					Marion::do_widget_module(basename(__DIR__),'newsletter_grazie.htm');
				}else{
					$template->errore_generico(126);
				}
			}
		}else{
			$template->errore_generico(125);
		}
		
	}else{
		$template->errore_generico(124);
	}
}elseif( $action == 'execute'){
	$formdata = _var('formdata');
	

	$mailman_conf = Marion::getConfig('module_mailman');
	
	
	if( $mailman_conf['form_user_subscribe_type'] == 1 ){
		$lists =  Mailman::prepareQuery()->where('default_list',1)->get();
	}elseif( $mailman_conf['form_user_subscribe_type'] == 3 ){
		$template->selezionabile =  true;
		$lists = Mailman::prepareQuery()->where('visibility',1)->get();
	}else{
		
		$lists = Mailman::prepareQuery()->where('visibility',1)->get();
	}
	
	$template->lists = $lists;
	

	
	
	
	$array = check_form($formdata,'mailman_action');
	
	if( $array[0] == 'ok'){
		$check_domain = $template->checkDomainEmail($array['email']);
		if( !$check_domain ){
			$array[0] = 'nak';
			$array[1] = __module('mailman','domain_not_exist');
		}
	}

	if( $array[0] == 'ok'){
		if( !$formdata['lists'] ){
			$array[0] = 'nak';
			$array[1] = __module('mailman','no_selected_newsletter');
		}
		
	}
	

	if( $array[0] == 'ok'){
		$action = $array['action'] ;
		foreach($formdata['lists'] as $list){
			$list = Mailman::withId($list);
			if(is_object($list) ){
				if( $action == 'subscribe'){
					$res = $list->sendConfirmEmail($formdata['email']);
				}else{
					 $res = $list->sendConfirmRemove($formdata['email']);
				}
			}
		}
		
		if( $res != 1 ){
			$template->errore_generico(__module('mailman',$res));
		}else{
			$template->messaggio = __module('mailman','confirm_operation');
			Marion::do_widget_module(basename(__DIR__),'conferma_operazione.htm');
			//$template->output_module(basename(__DIR__),'conferma_operazione.htm');
		}
	}else{
		$template->errore = $array[1];
		get_form($elements,'mailman_action','execute',$array);
		$template->output_module(basename(__DIR__),'newsletter.htm',$elements);

	}
} elseif ( $action == 'view_page'){
	
	Mailman::getData();
	$page = _var('page');
	if( file_exists("templates/{$activelocale}/{$page}.htm")){
		$template->output_module(basename(__DIR__),$page.'.htm');
	}elseif( file_exists("templates/{$activelocale}/mail/{$page}.htm") ){
		$template->output_module(basename(__DIR__),$page.'.htm',null,null,'mail');

	}else{
		$template->output('404.htm');
	}
}elseif( $action == 'mail_editor2'){
	require_once ('mail_editor/config.php');
	require_once ('mail_editor/includes/db.class.php');

	
	$template->output_module(basename(__DIR__),'mail_editor2.htm');
}elseif( $action == 'mail_editor'){
	Marion::setMenu('mail_editor');
	$template->output_module(basename(__DIR__),'mail_editor.htm');
}elseif( $action == 'view_mail_campaign'){
	Marion::setMenu('campaigns');
	$id = _var('id');
	$obj = MailMarketingCampaign::withId($id);


	echo $obj->getHtmlMail();
	exit;
}elseif( $action == 'campaigns'){
	Marion::setMenu('campaigns');
	$old = _var('old');
	if( $old ){
		$list = MailMarketingCampaign::prepareQuery()->where('sent',1)->get();
	}else{
		$list = MailMarketingCampaign::prepareQuery()->where('sent',0)->get();
	}
	
	$template->old = $old;
	$newsletters = Mailman::prepareQuery()->get();
	foreach($newsletters as $v){
		
		$news[$v->id] = $v;
	}
	foreach($list as $k => $v){
		if( $news[$v->list] ){
			if( !$v->sent ){
				$count = $news[$v->list]->getCountSubscribe();
				$v->tot_users = $count;
			}
			if( $v->date_sent ){
				$strtotime = strtotime($v->date_sent);
				$week  = (int)date('w', $strtotime);
				
				$v->inviata['month'] = $mesi[(int)date('m',$strtotime)];
				$v->inviata['day'] = date('d',$strtotime);
				$v->inviata['year'] = date('Y',$strtotime);
				$v->inviata['giorno'] = $giorni_week[$week];
				
				//debugga($v);exit;
			}
			$v->listname = $news[$v->list]->list_name;
			$v->email = $news[$v->list]->email;
		}
		
	}
	$template->list = $list;
	$template->output_module(basename(__DIR__),'list_campaign.htm');
}elseif( $action == 'start_campaign'){
	$id = _var('id');
	
	$obj = MailMarketingCampaign::withId($id);
	if( is_object($obj) ){
		$res = $obj->start();

	}
	if( $res ){
		$risposta = array('result' => 'ok');
	}else{
		$risposta = array('result' => 'nak');
	}

	echo json_encode($risposta);
	exit;
}elseif( $action == 'check_view'){
	$id = _var('id');
	MailMarketingCampaign::view($id);
}elseif( $action == 'dispatch'){
	$id = _var('id');
	MailMarketingCampaign::dispatch($id);
}elseif( $action == 'report'){
	Marion::setMenu('campaigns');
	$id = _var('id');
	$obj = MailMarketingCampaign::withId($id);
	$template->links = $obj->getLinkReports();
	$template->campaign = $obj;
	$template->output_module(basename(__DIR__),'report_campaign.htm');
}elseif( $action == 'cronjob'){
	$list = MailMarketingCampaign::prepareQuery()->whereExpression("(sent is NULL or sent = 0)")->where('cron',1)->get();

	if( okArray($list) ){
		foreach($list as $v){
			$now = time();
			$diff = strtotime($v->dateStart." ".$v->hourStart)-$now;
			if( $diff <= 0 ){
				$v->start();
			}
			
		}
	}

}elseif( $action == 'mailup'){
	require_once('classes/MailUpClient.php');
	$MAILUP_CLIENT_ID = "93adb687-e3f1-4dee-bc2d-d9d10f15c28b"; 
	$MAILUP_CLIENT_SECRET = "56cd5726-cb4e-43d9-8a88-dd15980d5ac8";

	


	/************************************************************************************************/

	// INIZIALIZZO L'OGGETTO MAILUP
	$mailUp = new MailUpClient($MAILUP_CLIENT_ID, $MAILUP_CLIENT_SECRET, $MAILUP_CALLBACK_URI);
	$mailUp->logOnWithPassword("m51201","0ZCVSPAN");
	debugga($mailUp);exit;
}elseif( $action == 'sendmail'){
	$body=$_POST["html"];
	$email=$_POST["mail"];
	$mail = _obj('Mail');
	$mail->setHtml($body);
	$mail->setSubject("Test Email dal sito ".getConfig('generale','nomesito'));
	$mail->setTo($email);
	$mail->setFrom( getConfig('generale','mail') );
	if(!$mail->send()) {
	   $response['code']=300;
	   $response['message']='Messaggio non inviato. Mailer Error: ' . $mail->ErrorInfo;
	} else {
	   $response['code']=0;
	   $response['message']='Messaggio inviato alla casella si posta indicata:'.$email."";
	}
	echo  json_encode($response);
	exit;
}elseif( $action == 'get_html_page'){
	$page = _var('page').".htm";
	$template->output_module('mail_marketing',$page,NULL,true);
	exit;
}elseif( $action == 'get_html'){
	$id = _var('id');

	
	$widget = MailWidget::withId($id);
	$obj = $widget->getObject();
	echo $obj->getContent();
	exit;
}elseif( $action == 'elements'){
	$path = 'mail_editor/elements.json';
	$json = json_decode(file_get_contents($path));
	
	
	foreach($json->elements as $k => $v){
		
		if( $v->name == 'Footer'){
			

			$object2 = new stdClass();
			$object2->name = "Natale";
			$object2->icon = 'fa fa-minus';
			$object2->content = "/admin/modules/mail_marketing/controller.php?action=get_html_page&page=footer_email_natale";
			$v->items[] = $object2;

			$object2 = new stdClass();
			$object2->name = "Default";
			$object2->icon = 'fa fa-minus';
			$object2->content = "/admin/modules/mail_marketing/controller.php?action=get_html_page&page=footer_email";
			$v->items[] = $object2;
		}

		if( $v->name == 'Layout'){
			

			
			$object2 = new stdClass();
			$object2->name = "Cancellami";
			$object2->icon = 'fa fa-minus';
			$object2->content = "/admin/modules/mail_marketing/controller.php?action=get_html_page&page=unsubscribe_element";
			$object->items[] = $object2;
			$v->items[] = $object2;
		}
	}


	$object = new stdClass();
	$object->name = 'Top';
	
	$object2 = new stdClass();
	$object2->name = "Default";
	$object2->icon = 'fa fa-minus';
	$object2->content = "/admin/modules/mail_marketing/controller.php?action=get_html_page&page=top_email";
	$object->items[] = $object2;

	$object2 = new stdClass();
	$object2->name = "Natale";
	$object2->icon = 'fa fa-minus';
	$object2->content = "/admin/modules/mail_marketing/controller.php?action=get_html_page&page=top_email_natale";
	$object->items[] = $object2;

	$json->elements[] =  $object;

	
	
	
	
	$object = new stdClass();
	$object->name = 'Widgets';
	

	

	

	

	$list = MailWidget::prepareQuery()->get();
	


	foreach($list as $v){
		$object2 = new stdClass();
		$object2->name = $v->name;
		$object2->icon = $v->getIcon();
		$object2->content = "/admin/modules/mail_marketing/controller.php?action=get_html&id=".$v->id;
		$object->items[] = $object2;
	}
	$json->elements[] =  $object;

	/*$object = new stdClass();
	$object->name = 'Modelli';

	$object2 = new stdClass();
	$object2->name = 'Natale';
	$object2->icon = $v->getIcon();
	$object2->content = "/admin/modules/mail_marketing/controller.php?action=get_html_page&page=natale";
	$object->items[] = $object2;*/
	

	

	
	//$json->elements[] =  $object;
	echo json_encode($json);
	exit;
	
}elseif( $action == 'delete_widget'){
	Marion::setMenu('widgets');
	$id = _var('id');
	$obj = MailWidget::withId($id);
	if( is_object($obj) ){
		$obj->delete();
	}
	$template->link = "/admin/modules/mail_marketing/controller.php?action=widgets";
	$template->output('continua.htm');
}elseif( $action == 'widgets'){
	Marion::setMenu('widgets');
	

	$dir = scandir('widgets');
	foreach($dir as $d){
		if( !in_array($d,array('.','..'))){
			$dir2 = scandir('widgets/'.$d);
			$conf_file = 'widgets/'.$d.'/config.xml';
			if( file_exists($conf_file) ){
				
				$data_xml = simplexml_load_file($conf_file);
				
				foreach($data_xml->info as $t){
					$data = (array)$t;
					if( okArray($data) ){
						if( Marion::auth($data['permission']) ){
							$data['url_add'] = '/admin/modules/mail_marketing/widgets/'.$d.'/'.$data['url_add'];
							$data['logo'] = '/admin/modules/mail_marketing/widgets/'.$d.'/'.$data['logo'];
							$widgets[] = $data;
						}
					}
				}
				
			}
			
		}
	}
	
	$template->widgets = $widgets;

	$list = MailWidget::prepareQuery()->get();
	
	$template->list = $list;
	$template->output_module('mail_marketing','list_widgets.htm');
	exit;
}elseif( $action == 'add_widget_vetrina_prodotti' || $action == 'mod_widget_vetrina_prodotti'){
	Marion::setMenu('widgets');
	if( $action == 'mod_widget_vetrina_prodotti' ){
		$id = _var('id');
		$obj = WidgetVetrina::withId($id);
		$dati = $obj->prepareForm();
		
		$composition = $obj->getComposition();
		foreach($composition as $v){
			$obj = Product::withId($v['id_object']);

			if( is_object($obj) ){
				$list[] = array(
					'id' => $obj->id,
					'name' => $obj->getName(),
					'img' => $obj->getUrlImage(0,'original'),
				);
			}

		}
		$template->list = $list;
	}
	get_form($elements,'mail_marketing_vetrina_prodotti',$action."_ok",$dati);
	$template->output_module('mail_marketing','form_vetrina_prodotti.htm',$elements);
	exit;
}elseif( $action == 'add_widget_vetrina_prodotti_ok' || $action == 'mod_widget_vetrina_prodotti_ok'){
	Marion::setMenu('widgets');
	$formdata = _var('formdata');
	
	$array = check_form($formdata,'mail_marketing_vetrina_prodotti');
	if( $array[0] == 'ok' ){	
		unset($array[0]);
		
		if( $action == 'mod_widget_vetrina_prodotti_ok' ){
			$obj = WidgetVetrina::withId($array['id']);
		}else{
			$obj = WidgetVetrina::create();
		}
		$obj->type='product';
		$obj->setComposition($formdata['items']);
		$obj->set($array);

		
		
		$obj->setConf(
			array(
				'show_prices' => $array['show_prices']
			)	
		);
		
		$obj->save();
		
		$template->link = "/admin/modules/mail_marketing/controller.php?action=widgets";
		$template->output('continua.htm');
	}else{
		$template->errore = $array[1];
		get_form($elements,'mail_marketing_vetrina_prodotti',$action,$array);
		$template->output_module('mail_marketing','form_vetrina_prodotti.htm',$elements);

	}
}elseif( $action == 'add_widget_vetrina_news' || $action == 'mod_widget_vetrina_news'){
	Marion::setMenu('widgets');
	if( $action == 'mod_widget_vetrina_news' ){
		$id = _var('id');
		$obj = WidgetVetrina::withId($id);
		$dati = $obj->prepareForm();
		$composition = $obj->getComposition();
		foreach($composition as $v){
			$obj = News::withId($v['id_object']);

			if( is_object($obj) ){
				$list[] = array(
					'id' => $obj->id,
					'name' => $obj->get('title'),
					'img' => $obj->getUrlImage(0,'original'),
				);
			}
		}
		$template->list = $list;
	}
	get_form($elements,'mail_marketing_vetrina_prodotti',$action."_ok",$dati);
	$template->output_module('mail_marketing','form_vetrina_news.htm',$elements);
	exit;
}elseif( $action == 'add_widget_vetrina_news_ok' || $action == 'mod_widget_vetrina_news_ok'){
	Marion::setMenu('widgets');
	$formdata = _var('formdata');
	
	$array = check_form($formdata,'mail_marketing_vetrina_news');
	
	if( $array[0] == 'ok' ){	
		unset($array[0]);
		
		if( $action == 'mod_widget_vetrina_news_ok' ){
			$obj = WidgetVetrina::withId($array['id']);
		}else{
			$obj = WidgetVetrina::create();
		}
		$obj->type='news';
		$obj->setComposition($formdata['items']);
		$obj->set($array);


		
		/*$obj->setConf(
			array(
				'show_prices' => $array['show_prices']
			)	
		);*/
		
		$obj->save();
		
		$template->link = "/admin/modules/mail_marketing/controller.php?action=widgets";
		$template->output('continua.htm');
	}else{
		$template->errore = $array[1];
		get_form($elements,'mail_marketing_vetrina_news',$action,$array);
		$template->output_module('mail_marketing','form_vetrina_news.htm',$elements);

	}


}else{
	$formdata = _var('formdata');
	if( !$formdata ){
		$formdata = _var('formdata1');
	}
	$mailman_conf = Marion::getConfig('module_mailman');
	

	if( $mailman_conf['form_user_subscribe_type'] == 1 ){
		$lists =  Mailman::prepareQuery()->where('default_list',1)->get();
		
	}elseif( $mailman_conf['form_user_subscribe_type'] == 3 ){
		$template->selezionabile =  true;
		$lists = Mailman::prepareQuery()->where('visibility',1)->get();
	}else{
		
		$lists = Mailman::prepareQuery()->where('visibility',1)->get();
	}
	$template->lists = $lists;
	
	
	
	
	
	get_form($elements,'mailman_action','execute',$formdata);
	
	Marion::do_widget_module(basename(__DIR__),'newsletter.htm',$elements);

}

function array_mail_template(){
	$database = _obj('Database');
	$list = $database->select('*','bal_email_builder');
	$toreturn[0] = __('seleziona');
	if( okArray($list) ){
		foreach($list as $v){
			$toreturn[$v['id']] = $v['name'];
		}
	}
	return $toreturn;
}

function array_mail_list(){
	
	$toreturn[0] = __('seleziona');
	$list = Mailman::prepareQuery()->get();
	if( okArray($list) ){
		foreach($list as $v){
			$toreturn[$v->id] = $v->list_name;
		}
	}
	return $toreturn;
}



?>