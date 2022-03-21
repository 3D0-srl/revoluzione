<?php
use Marion\Controllers\BackendController;
use Marion\Core\Marion;
class SupportController extends BackendController{

    
    public function index(){
        $formdata = [];
        if( $this->isSubmitted() ){
            $formdata = $this->getFormdata();
            $check = $this->checkDataForm('elearning_support',$formdata);
            if( $check[0] == 'ok'){
                $this->sendMail($check);
                $this->displayMessage(_translate("Messaggio inviato con successo!","elearning"));

            }else{
                $this->errors[] = $check[1];
            }
        }
        $dataform = $this->getDataForm('elearning_support',$formdata);
        $this->setVar('dataform',$dataform);
        $this->setMenu('elearning_support');
		$this->output('support.htm');
		
	}


    private function sendMail($dati){
        $user = Marion::getUser();
        $mail = _obj('Mail');
        ob_start();
        $this->setVar('subject',$dati['subject']);
        $this->setVar('message',$dati['message']);
        $this->setVar('email',$user->email);
        $this->output('mails/support.htm');
        $html = ob_get_contents();
        ob_end_clean();

        $config = Marion::getConfig('generale');
        $mail->setSubject(_translate(array('Richiesta di supporto su %s',$config['nomesito']),'elearning'));
        $mail->setHtml($html);
        $mail->setFrom($user->email);
        $mail->setTo($config['mail']);
        $mail->send();
        return;
    }


}
?>