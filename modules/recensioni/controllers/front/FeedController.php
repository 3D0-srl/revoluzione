<?php
use Marion\Controllers\BackendController;
use Illuminate\Database\Capsule\Manager as DB;
use Marion\Core\Marion;
class FeedController extends BackendController{	

    
    function index(){
        $this->setMenu('recensioni');
        $user = Marion::getUser();
        $recensione = DB::table('recensioni')
					->orderBy('data_inserimento','desc')
					->where('user_id',$user->id)
					->first();
        if($recensione ){
            $this->setVar('inserita',true);
        }

        if( $this->isSubmitted() ){
            $dati = $this->getFormdata();
            $check = $this->checkDataForm('recensioni_form',$dati);
            if( $check[0] == 'ok'){
               
                DB::table('recensioni')->insert(
                    [
                        'nickname' => $check['nickname'],
                        'message' => $check['message'],
                        'user_id' => $user->id
                    ]
                );
                $this->displayMessage(_translate('Recensione inserita con successo','recensioni'));
            }else{
                $this->errors = $check[1];
            }
        }
        $dataform = $this->getDataForm('recensioni_form',$dati);
        $this->setVar('dataform',$dataform);
        $this->output('form.htm');
    }

}

?>