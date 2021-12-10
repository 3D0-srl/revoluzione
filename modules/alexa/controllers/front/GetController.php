<?php
class GetController extends FrontendController{
	
	
	function display(){
		
		$action = $this->getAction();
		switch($action){
			case 'riepilogo_mese':
				$message = $this->riepilogoMese();
				break;
			case 'riepilogo_oggi':
				$message = $this->riepilogoOggi();
				break;
			case 'riepilogo_settimana':
				$message = $this->riepilogoSettimana();
				break;
			case 'cosa_posso_fare':
				$message = $this->cosaPossoFareMessage();
				break;

		}	
		
		echo json_encode(array('message'=>$message));
	}

	

	function cosaPossoFareMessage(){
		
		

		$message = '<speak>Puoi chiedermi di <break time="1s"/>';
		//$message .= 'inviare gli ordini da spedire <break time="1s"/>';
		//$message .= 'quanto hai fatturato oggi <break time="1s"/>';
		$message .= 'dirti il riepilogo ordini di oggi <break time="1s"/>';
		$message .= 'dirti il riepilogo ordini di questo mese <break time="2s"/>';
		$message .= 'Cosa vuoi chiedermi?</speak>';
		
		return $message;
	}

	function getDataCarts($list=array()){
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
				if( $tipo == 'settimanale'){
					$message = "Mi dispiace, questa settimana non hai ricevuto nessun nuovo ordine.";
				}
				
				break;
			case 1:
				if( $tipo == 'mensile'){
					$message = "Questo mese hai ricevuto un solo ordine,";
				}
				if( $tipo == 'giornaliero'){
					$message = "Oggi hai ricevuto un solo ordine,";
				}
				if( $tipo == 'settimanale'){
					$message = "Questa settimana hai ricevuto un solo ordine,";
				}
				break;
			default:
				if( $tipo == 'mensile'){
					$message = "Questo mese ricevuto {$num_tot} ordini,";
				}
				if( $tipo == 'giornaliero'){
					$message = "Oggi hai ricevuto {$num_tot} ordini,";
				}

				if( $tipo == 'settimanale'){
					$message .= " Questa settimana hai ricevuto {$num_tot} ordini,";
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
			if( $tipo == 'settimanale'){
				$message .= " Rispetto alla settimana scorsa hai avuto un incremento del {$incremento}%. ";
			}
			
		}else{
			if( $tipo == 'mensile'){
				$message .= " Rispetto al mese scorso hai avuto un decremento del {$incremento}%. ";
			}
			if( $tipo == 'giornaliero'){
				$message .= " Rispetto a ieri hai avuto un decremento del {$incremento}%. ";
			}
			if( $tipo == 'settimanale'){
				$message .= " Rispetto alla settimana scorsa hai avuto un decremento del {$incremento}%. ";
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
			->where('evacuationDate',$inizio,'>=')
			->get();
		$list2 = array();
		return $this->riepilogo($list1,$list2,'mensile');
	}
	function riepilogoSettimana(){

		$monday = strtotime('next Monday -1 week');
		$monday = date('w', $monday)==date('w') ? strtotime(date("Y-m-d",$monday)." +7 days") : $monday;
		$sunday = strtotime(date("Y-m-d",$monday)." +6 days");
		$inizio = date("Y-m-d",$monday);
		$fine = date("Y-m-d",$sunday);

		

		$list1 = Cart::prepareQuery()
			->where('evacuationDate',$inizio,'>=')
			->where('evacuationDate',$fine,'<=')
			->get();
		
		$inizio = strftime("%Y-%m-%d",strtotime($inizio." -7 days"));
		$fine = strftime("%Y-%m-%d",strtotime($fine." -7 days"));
		
		$list2 = Cart::prepareQuery()
			->where('evacuationDate',$inizio,'>=')
			->where('evacuationDate',$fine,'<=')
			->get();
		$list2 = array();
		return $this->riepilogo($list1,$list2,'settimanale');
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
		


		
	}
}

?>