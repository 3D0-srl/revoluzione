<?php
// Include the library
require 'modules/alexa/alexa-endpoint/autoload.php';
use MayBeTall\Alexa\Endpoint\Alexa;
use MayBeTall\Alexa\Endpoint\User;
use MayBeTall\Alexa\Endpoint\Profile;
use MayBeTall\Alexa\Endpoint\Intent;
use MayBeTall\Alexa\Endpoint\Request;

function logga($var){
	error_log(print_r($var,true), 3, "modules/alexa/error.log");
}
class IndexController extends FrontendController{
	public $path_logs = 'modules/alexa/error.log';

	public static $intent_urls = array(
		'riepilogo_oggi' => 'index.php?mod=alexa&ctrl=Alexa&action=riepilogo_oggi',
		'riepilogo_mese' => 'index.php?mod=alexa&ctrl=Alexa&action=riepilogo_oggi',
		'riepilogo_settimana' => 'index.php?mod=alexa&ctrl=Alexa&action=riepilogo_settimana',
		'cosa_posso_fare' => 'index.php?mod=alexa&ctrl=Alexa&action=cosa_posso_fare'

	);

	public static $email_sites = array(
		'ciro.napolitano87@gmail.com' => array(
			array(
				'site' => 'Outlet Brand',
				'url' => 'http://outletbrand.test3d0.it/'
			),
			array(
				'site' => 'Ciccio',
				'url' => 'http://outletbrand.test3d0.it/'
			)
			
		)

	);

	function logga($var){
		error_log(print_r($var,true), 3, $this->path_logs);
	}
	
	public static function getRemoteMessage($intent){
		$url = IndexController::$intent_urls[$intent];
		$endpoint = Alexa::recall('endpoint');
		$message = json_decode(file_get_contents($endpoint.$url));
		
		return $message->message;
	}

	function display(){


		logga($_POST);
		logga($_GET);
		$dati = json_decode(file_get_contents('php://input'));
		logga($dati);

		// User launched the skill.
		Alexa::init();
		/*$payload = Request::getPayload();
		logga($payload);
		
		Alexa::messages(function(){
			$payload = Request::getPayload();
			$message = (string)$payload->request->message->sampleMessage;
			Alexa::say($message);
			logga($payload);
		});*/
		
		/*if( Request::getType() == 'Messaging.MessageReceived'){
			logga(Request::getPayload());

			
			//logga($message);
			Alexa::say($message);
			exit;
		}*/
		


		Alexa::enters(function() {
			

			$profile = new Profile();
			$fullname = $profile->getName();
			$email = $profile->getEmail();
			
			$data = explode(' ',$fullname);
			$name = $data[0];

			
			
			if( !$email ){
				Alexa::say('Devi fornire i premessi per accedere alla tua email!');
			}else{

				Alexa::remember('email',$email);
				Alexa::remember('name',$name);

				if( time() > strtotime('12:00')){
					$buondi = 'Buonasera';
				}else{
					$buondi = 'Buongiorno';
				}



				$endpoints = array_values(IndexController::$email_sites[$email]);
				
				if( is_array($endpoints) && count($endpoints) > 0 ){
					if( count($endpoints) == 1 ){
						$endpoint = $endpoints[0];
					}else{
						$select_site = '';
						foreach($endpoints as $k=> $v){
							$t = $k+1;
							$select_site .= $t." per {$v['site']}, ";
						}
						Alexa::ask("<speak>".'<say-as interpret-as="interjection">'.$buondi.' '.$name.'!</say-as>'." La tua email è associata a più siti. Quale vuoi selezionare? Rispondi indicando {$select_site}. Ad esempio: seleziona sito 1 per selezionare il primo sito.</speak>");
						exit;
						
						
					}
				}else{
					Alexa::say("<speak>".'<say-as interpret-as="interjection">'.$buondi.' '.$name.'!</say-as>'." La tua email non è associata a nessun sito. Assicurati di aver installato sul tuo sito il modulo di Alexa e che la sua configurazione sia corretta.</speak>");
					exit;
				}

				if( $endpoint ){
					Alexa::remember('endpoint',$endpoint['url']);
				}
		
				Alexa::ask('<speak>
						<say-as interpret-as="interjection">'.$buondi.' '.$name.'!</say-as> Come posso aiutarti?</speak>');
			}
		});

		User::triggered('seleziona_sito', function() {
			$numero = User::stated('numero');
			$email = Alexa::recall('email');
			$endpoints = array_values(IndexController::$email_sites[$email]);
				
			if( is_array($endpoints) && count($endpoints) > 0 ){
				$endpoint = $endpoints[$numero-1];
			}
			if( $endpoint ){
				Alexa::remember('endpoint',$endpoint['url']);
				Alexa::ask('Perfetto hai selezionato il sito '.$endpoint['site']."!. Cosa posso fare per te?");
			}else{
				$tot = count($endpoints);
				Alexa::ask('Mi dispiace la selezione non corrisponde a nessun sito. I valori ammessi sono compresi tra 1 e '.$tot.". Puoi ripetere la selezione?");	
			}
		
			
		});
		User::triggered('riepilogo_settimana', function() {
			
			
			$message = IndexController::getRemoteMessage('riepilogo_settimana');
			Alexa::ask($message);
		});

		User::triggered('riepilogo_oggi', function() {
			
			
			$message = IndexController::getRemoteMessage('riepilogo_oggi');
			Alexa::ask($message);
		});

		User::triggered('riepilogo_mese', function() {
			
			$message = IndexController::getRemoteMessage('riepilogo_mese');
			Alexa::ask($message);
		});

		User::triggered('invio_ordini', function() {
			
			Alexa::remember('azione','invio_ordini');
			Alexa::ask("Dove vuoi chi ti invii gli ordini? Email o telegram?");
		

		});

		User::triggered('AMAZON.CancelIntent', function() {
		
			Alexa::say('Ok, operazione annullata!',false);

		});

		User::triggered('AMAZON.StopIntent', function() {
		
			Alexa::say('Ok, operazione annullata!',false);

		});

		User::triggered('cosa_posso_fare', function() {
			$message = IndexController::getRemoteMessage('cosa_posso_fare');
			Alexa::ask($message);
		});

		User::triggered('no', function() {
			$last_action = Alexa::recall('azione');

			if( $last_action && $last_action == 'invio_ordini'){
				
					Alexa::forget('azione');
					Alexa::say('OK!');
				
			}else{
				Alexa::say('OK!');
			}
		});

		exit;
	}



	function sendOrders(){
		
	}
	
	/*function getDataCarts($list=array()){
		$dati = array(
			'tot' => 0,
			'num' => 0,
			'num_products' => 0,
			'no_shipping' => 0,
		);

		$sel_status = CartStatus::prepareQuery()->where('active',1)->get();
		$da_spedire = array();
		foreach($sel_status as $v){
			if( !$v->sent ){
				$da_spedire[] = $v->label;
			}
		}
		if( okArray($list) ){
			foreach($list as $v){
				if( in_array($v->status,$da_spedire) ){
					$dati['no_shipping']++;
				}
				$dati['num']++;
				$dati['tot_products'] += $v->num_products;
				$dati['tot'] += $v->total+$v->shippingPrice+$v->paymentPrice-$v->discount;
			}
		}
		return $dati;
	}

	function getIncrement($tot1,$tot2){
		$diff = $tot1-$tot2;
		
		$increment = (int)(($diff/$tot1)*100);
		return $increment;
	}

	function riepilogo($current=array(),$prec=array(),$tipo=''){
		$tot_oggi = 0;
		$incremento = 0;
		$num_tot = 0;
		$tot_products = 0;
		$num_da_spedire = 0;


		
		$dati_oggi = $this->getDataCarts($current);

		
		$dati_ieri = $this->getDataCarts($prec);
		
		$incremento = $this->getIncrement($dati_oggi['tot'],$dati_ieri['tot']);
		$tot_oggi =  Eshop::formatMoney($dati_oggi['tot']);
		$num_tot = $dati_oggi['num'];
		$num_products = $dati_oggi['num_products'];
		$num_da_spedire = $dati_oggi['no_shipping'];
	
		$tot_oggi = Eshop::formatMoney($tot_oggi);


		switch($num_tot){
			case 0:
				if( $tipo == 'mensile'){
					$message = "Mi dispiace, questo mese non hai ricevuto nessun nuovo ordine.";
				}
				if( $tipo == 'gioraliero'){
					$message = "Mi dispiace, oggi non hai ricevuto nessun nuovo ordine.";
				}
				
				break;
			case 1:
				if( $tipo == 'mensile'){
					$message = "Questo mese hai ricevuto un solo ordine,";
				}
				if( $tipo == 'giornaliero'){
					$message = "Oggi hai ricevuto un solo ordine,";
				}
				break;
			default:
				if( $tipo == 'mensile'){
					$message = "Questo mese ricevuto {$num_tot} ordini,";
				}
				if( $tipo == 'giornaliero'){
					$message = "Oggi hai ricevuto {$num_tot} ordini,";
				}
				
				break;
		}
		if( $num_da_spedire ){
			if( $num_da_spedire == $num_tot ){
				if( $num_da_spedire == 1 ){
					$message .= " che deve essere ancora spedito,";
				}else{
					$message .= " tutti ancora da spedire,";
				}
			}else{
				$message .= " di cui {$num_da_spedire} sono ancora da spedire,";
			}
		}
		
		if( $num_tot > 0 ){
			$message .= " con un incasso complessivo di {$tot_oggi} euro.";
		}
		if( $incremento > 0 ){
			if( $tipo == 'mensile'){
				$message .= " Rispetto al mese scorso hai avuto un incremento del {$incremento}%. ";
			}
			if( $tipo == 'giornaliero'){
				$message .= " Rispetto a ieri hai avuto un incremento del {$incremento}%. ";
			}
			
		}else{
			if( $tipo == 'mensile'){
				$message .= " Rispetto al mese scorso hai avuto un decremento del {$incremento}%. ";
			}
			if( $tipo == 'giornaliero'){
				$message .= " Rispetto a ieri hai avuto un decremento del {$incremento}%. ";
			}
		}
		if( (int)$tot_products ){
			$message .= " Il numero totale di prodotti venduti è {$tot_products}. ";
		}
		$message .= " Posso fare altro per te?";

		return $message;
	}

	function riepilogoMese(){
		$inizio = date('Y-m-01');
		$list1 = Cart::prepareQuery()
			->where('evacuationDate',$oggi,'>=')
			->get();
		$list2 = array();
		return $this->riepilogo($list1,$list2,'mensile');
	}

	function riepilogoOggi(){
		$oggi = date('Y-m-d');
		$ieri = date('Y-m-d', strtotime( '-1 days' ) );
		$list1 = Cart::prepareQuery()
			->where('evacuationDate',$oggi,'>=')
			->get();

		$list2 = Cart::prepareQuery()
			->where('evacuationDate',$ieri,'>=')
			->where('evacuationDate',$ieri,'<')
			->get();

		return $this->riepilogo($list1,$list2,'giornaliero');
		/*$tot_oggi = 0;
		$incremento = 0;
		$num_tot = 0;
		$tot_products = 0;
		$num_da_spedire = 0;


		$list = Cart::prepareQuery()->where('evacuationDate',$oggi,'>=')->get();
		
		$dati_oggi = $this->getDataCarts($list);

		$list = Cart::prepareQuery()
			->where('evacuationDate',$ieri,'>=')
			->where('evacuationDate',$ieri,'<')
			->get();

		$dati_ieri = $this->getDataCarts($list);
		
		$incremento = $this->getIncrement($dati_oggi['tot'],$dati_ieri['tot']);
		$tot_oggi =  Eshop::formatMoney($dati_oggi['tot']);
		$num_tot = $dati_oggi['num'];
		$num_products = $dati_oggi['num_products'];
		$num_da_spedire = $dati_oggi['no_shipping'];
	
		$tot_oggi = Eshop::formatMoney($tot_oggi);


		switch($num_tot){
			case 0:
				$message = "Mi dispiace, oggi non hai ricevuto nessun nuovo ordine.";
				break;
			case 1:
				$message = "Oggi hai ricevuto un solo ordine,";
				break;
			default:
				$message = "Oggi hai ricevuto {$num_tot} ordini,";
				break;
		}
		if( $num_da_spedire ){
			if( $num_da_spedire == $num_tot ){
				if( $num_da_spedire == 1 ){
					$message .= " ancora da spedire,";
				}else{
					$message .= " tutti ancora da spedire,";
				}
			}else{
				$message .= " di cui {$num_da_spedire} sono ancora da spedire,";
			}
		}
		
		if( $num_tot > 0 ){
			$message .= " con un incasso complessivo di {$tot_oggi} euro.";
		}
		if( $incremento > 0 ){
			$message .= " Rispetto a ieri hai avuto un incremento del {$incremento}%. ";
		}else{
			$message .= " Rispetto a ieri hai avuto un decremento del {$incremento}%. ";
		}
		if( (int)$tot_products ){
			$message .= " Il numero totale di prodotti venduti è {$tot_products}. ";
		}
		$message .= " Posso fare altro per te?";

		return $message;
	}*/

		
}

?>