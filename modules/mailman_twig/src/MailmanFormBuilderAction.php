<?php
namespace Mailman;
use Marion\Core\Marion;
use FormBuilder\{FormBuilderAction};
class MailmanFormBuilderAction extends FormBuilderAction{
	public static function register(){
			return array(
				'send_newsletter' => 'Registra email alla newsletter'
			);
	}


	public static function execute(string $action, array $formdata, array $params, $ctrl){
			
		switch($action){
			case 'send_newsletter':
				$campi = $params['fields']; 
				foreach($campi as $v){
					if( $v['type'] == 'mailman_newsletter' ){
						if( $formdata[$v['name']] ){
							$emails[] = $formdata[$v['name']];
						}
					}
				}
				if( !okArray($emails) ){
					return _translate('no_email','mailman_twig');
				}else{
					require_once(_MARION_MODULE_DIR_.'mailman_twig/classes/Mailman.class.php');
					foreach($emails as $email){
						foreach($formdata['mailman_list_subscribe'] as $id_list){
							$list = Mailman::withId($id_list);
							if(is_object($list) ){
								$ctrl->addTwingTemplatesDir('modules/mailman_twig/templates_twig');
								

								$message = _translate('confirm_email_subscribe_message','mailman_twig');
								$ctrl->setVar('message',$message);
								/*<p flexy:if="dati[subscribe]">abbiamo ricevuto la tua richiesta di iscrizione alla newsletter {dati[list_name_view]}. <br>Per confermare questa richiesta ti invitiamo a cliccare sul pulsante 'conferma'</p>
<p flexy:if="dati[unsubscribe]">abbiamo ricevuto la tua richiesta di cancellazione dalla newsletter {dati[list_name_view]}. <br>Per confermare questa richiesta ti invitiamo a cliccare sul pulsante 'conferma'</p>
								*/
								ob_start();
								$ctrl->output('mail/newsletter.htm');
								$html = ob_get_contents();
								ob_end_clean();
								
								


								$res = $list->sendConfirmEmail($email,$html);
								if( $res != 1){
									return $res;
								}
							}
						}
					}
				}
				break;

		}
		return true;
	}

	public static function successMessage(string $action){
		$message = '';
		switch($action){
			case 'send_newsletter':
				$message = _translate('sending_success','mailman_twig');
				break;
		}
		return $message;

	}

}
?>












