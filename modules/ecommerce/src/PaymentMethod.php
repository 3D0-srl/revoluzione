<?php
namespace Shop;
use Marion\Core\Base;
use Marion\Core\Marion;
class PaymentMethod extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'paymentMethod'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'paymentMethodLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'paymentMethod';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	 

	//metodo che inizializza l'oggetto pagamento a partire dal suo codice pagamento
	public static function withCode($code)
	{
		
		
		self::initClass();
		
		if($code){
			$database = Marion::getDB();
			$data = $database->select('*',static::TABLE,"code='{$code}'");
			
			if(okArray($data)){
				return static::withData($data[0]);
			}else{
				static::writeLog("nessun dato trovato nel database per l'etichetta specificata nel metodo << withCode >>");
				return false;
			}

		}else{
			static::writeLog("codice pagamento in input vuoto nel metodo << withCode >>");
			return false;
		}


	}


	function getPrice(){
		$total = Cart::getCurrentTotal();
		if( $this->percentage ){
			$price = round($this->price*$total/100,2);
		}else{
			$price = round($this->price,2);
		}
		return Eshop::priceValue($price);
	}


	//restituisce l'immagine all'indice specificato del formato specificato
	function getUrlImage($type='original',$watermark=true,$name_image=NULL){
		if( $this->image ){
			$database = _obj('Database');
		
			$img = $database->select('i.*',"image as i join imageComposed as c on c.{$type}=i.id","c.id={$this->image}");
			if(okArray($img) ){
				if( $name_image ){
					$name = $name_image;
				}else{
					$name = $img[0]['filename_original'];
					$name = explode('.',$name);
					$name = Marion::slugify($name);
					$name = $name[0].".".$img[0]['ext'];
				}
				
				$type_short = $this->getTypeImageUrl($type);
				
				if( !$watermark ){
					return "/img/{$this->image}/{$type_short}-nw/{$name}";
				}else{
					return "/img/{$this->image}/{$type_short}/{$name}";
				}
			}
		}
		return '';
	}

	function getTypeImageUrl($type){
		switch( $type ){
			case 'thumbnail':
				$type = 'th';
				break;
			case 'small':
				$type = 'sm';
				break;
			case 'medium':
				$type = 'md';
				break;
			case 'large':
				$type = 'lg';
				break;
			case 'original':
				$type = 'or';
				break;
			default:
				$type='or';

		}
		return $type;

	}
	
	//controllo validita' del metodo di pagamento a partire da condizioni stabilite
	function checkOtherConditions(){
		$check = true;
		Marion::do_action('check_conditions_payment',array($this,&$check));

		return $check;
	}


	function afterLoad(){
		parent::afterLoad();
		$this->getUserCategories();
	}


	function afterSave(){
		parent::afterSave();
		$this->saveUserCategories();
	}
	
	function setUserCategories($array=array()){
		$this->userCategories = $array;
	}

	function saveUserCategories(){
		$database = _obj('Database');
		$database->delete('paymentMethod_userCategory',"id_paymentMethod={$this->id}");
		if( okArray($this->userCategories) ){
			foreach($this->userCategories as $v){
				$toinsert = array(
					'id_paymentMethod' => $this->id,
					'id_userCategory' => $v
				);
				$database->insert('paymentMethod_userCategory',$toinsert);
			}
		}
	}
	

	function getUserCategories(){
		if( $this->id ){
			$database = _obj('Database');
			$select = $database->select('*','paymentMethod_userCategory',"id_paymentMethod={$this->id}");
			if( okArray($select) ){
				foreach($select as $v){
					$this->userCategories[] = $v['id_userCategory'];
				}
			}

		}
	}
	

	function isAvailableForUserCategory($id_user_category){
		return in_array($id_user_category,$this->userCategories);
	}
}

?>