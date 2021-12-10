<?php

class MailMarketingCampaign extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'mailMarketingCampaign'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = ''; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = '';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	

	public $_newsletter_type = 'mailman';


	public function sendMail(){
		
		switch($this->_newsletter_type){
			case 'mailman':
				$mailman = Mailman::withId($this->list);
				
				$mail = _obj('Mail');
				$mail->setTo($mailman->email);

				$this->tot_users = $mailman->getCountSubscribe();
					
				//imposto il mittente
				$from = Marion::getConfig('module_mailman','email');
				if( !$from ){
					$from = Marion::getConfig('generale','mail');
				}
				
				$mail->setFrom($from);
				
				$nomesito = getConfig('generale','nomesito');
				
				$mail->setHtml($this->content);
				
				$subject = $this->name_view;
				
				
				$mail->setSubject($subject);
				
				$res = $mail->send();
				break;
			case 'phplist':

			
				$apiURL = 'http://phplist.3d0.it/admin/?page=call&pi=restapi';
				$login = 'abuse_gs2wpc1m';
				$password = 'Hc2e5O6qF_';
				
				$phpList = new phpListRESTApiClient($apiURL, $login, $password);
				$phpList->tmpPath = '/var/tmp';

				$from = Marion::getConfig('module_mailman','email');
				if( !$from ){
					$from = Marion::getConfig('generale','mail');
				}
				
				$path = 'mail_editor/exports/campaign_'.$this->id.".html";
				$content = "[URL:http://nuovo.test3d0.it/modules/mail_marketing/".$path."]";
			
				file_put_contents($path,$this->content);

				if ($phpList->login()) {
					$_options_phplist = array(
						'subject' =>  $this->name_view,
						'fromfield' => $from,
						'replyto' => $from,
						'message' => $content
					);
					
					$id_campaign = $phpList->campaignAdd($_options_phplist);
				
					$id_list = 1;
					//$id_campaign = 5;
					$res = $phpList->listCampaignAdd($id_list,$id_campaign);

					
					

				}
				$res = true;
				break;

		}

	
		return $res;



	}


	function getTimeStart(){
		$now = date('Y-m-d H:i:s');
		$datetime1 = new DateTime($now);
		$datetime2 = new DateTime($this->dateStart." ".$v->hourStart);
		$interval = $datetime1->diff($datetime2);
		return $interval->format("<b>%H</b>h <b>%I</b>m <b>%S</b>s"); 
	}



	function getNameList(){
		$mailman = Mailman::withId($this->list);
		if( is_object($mailman) ){
			return $mailman->list_name_view;
		}
	}


	


	public function start(){
		if( !$this->sent ){
			$this->prepareMail();
			if( $this->sendMail() ){
				$this->date_sent = date('Y-m-d H:i:s');
				$this->sent = true;
				$this->save();
			}
		}

		return $this->sent;
	}
	
	public function getHtmlMail(){
		$database = _obj('Database');
		$tmpl = $database->select('*','bal_email_builder',"id={$this->mail_template}");
		if( okArray($tmpl) ){
			$html = html_entity_decode($tmpl[0]['html']);
		}

		return $html;
	}

	public function prepareMail(){
		$database = _obj('Database');

		$baselink = "http://".$_SERVER['SERVER_NAME']."/modules/mail_marketing/controller.php?action=dispatch&id=";
		$check_link = "http://".$_SERVER['SERVER_NAME']."/modules/mail_marketing/controller.php?action=check_view&id={$this->id}";
		$unsubscribe_link = "http://".$_SERVER['SERVER_NAME']."/modules/mail_marketing/controller.php";
		$tmpl = $database->select('*','bal_email_builder',"id={$this->mail_template}");
		if( okArray($tmpl) ){
			
			

			$html = html_entity_decode($tmpl[0]['html']);
			
			$template = _obj('Template');
			
			ob_start();
			$template->data = $html;
			$template->output_module('mail_marketing','mail_template.htm');
			$data = ob_get_contents();
			ob_end_clean();
			
			
			$dom = new DOMDocument();
			$dom->loadHTML($data);
			$dom->encoding = 'UTF-8';
			$dom->validateOnParse = false;
			foreach ($dom->getElementsByTagName('a') as $node) {
				if( $node->hasAttribute( 'href' ) ){
					$link = $node->getAttribute( 'href' );
					
					if( !preg_match('/^#/',$link) ){
						$toinsert = array( 
							'link' => $link,
							'id_campaign' => $this->id
						);
						
						$id = $database->insert('mail_marketing_link_mail',$toinsert);
						
						$node->setAttribute('href', $baselink.$id);
						$dom->saveHtml($node);

					}else{
						if( preg_match('/unsubscribe/',$link) ){
							$node->setAttribute('href', $unsubscribe_link);
						}

					}
				}
			}
			$node = $dom->createElement("img");
			$node->setAttribute('src', $check_link);
			$node->setAttribute('style', "width:1px; height:1px;");
			$newnode = $dom->appendChild($node);
			
			ob_start();
			echo $dom->saveHTML();
			$html = ob_get_contents();
			ob_end_clean();
			
			//debugga($html);exit;
			$this->content = $html;

			
			$this->save();
			
		}
	}

	function registerIPLink($id_link){
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$database = _obj('Database');
		$toinsert = array(
			'id_campaign' => $this->id,
			'ip' => $ip,
			'id_link' => $id_link
		);
		
		$database->insert('mail_marketing_link_mail_click',$toinsert);
		
	}

	function registerIPView(){
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$database = _obj('Database');
		$toinsert = array(
			'id_campaign' => $this->id,
			'ip' => $ip
		);
		$database->insert('mail_marketing_view',$toinsert);
		
	}



	public static function dispatch($id){
		if( $id ){
			$database = _obj('Database');
			$link = $database->select('*','mail_marketing_link_mail',"id={$id}");
			
			if( okArray($link) ){
				$link = $link[0];
				$campaign = self::withId($link['id_campaign']);
				$campaign->registerIPLink($id);
				header('Location:'.$link['link']);
			}
		}

	}

	public static function view($id){
		if( $id ){
			$database = _obj('Database');
			$link = $database->select('*','mail_marketing_view',"id={$id}");
			if( okArray($link) ){
				$link = $link[0];
				$campaign = self::withId($link['id_campaign']);
				$campaign->registerIPView();
			}
		}

	}



	function getLinkReports(){
		$database = _obj('Database');
		$links = $database->select('*','mail_marketing_link_mail',"id_campaign={$this->id}");
		foreach($links as $k => $l){
			$tot = $database->select('count(*) as tot','mail_marketing_link_mail_click',"id_link={$l['id']}");
			$links[$k]['tot'] = $tot[0]['tot'];
		}
		
		return $links;
	}


}



?>