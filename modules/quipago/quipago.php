<?php
use Marion\Core\Module;
use \ImageComposed;;
use Shop\{PaymentMethod,CartStatus};
use Marion\Core\Form;
class Quipago extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){
			$database = _obj('Database');
			

			$database->execute("
				CREATE TABLE transactionCartaSi (
				  regione varchar(100) NOT NULL,
				  session_id varchar(100) DEFAULT NULL,
				  tipoTransazione varchar(100) DEFAULT NULL,
				  data varchar(100) DEFAULT NULL,
				  mac varchar(100) DEFAULT NULL,
				  tipoProdotto varchar(100) DEFAULT NULL,
				  nazionalita varchar(100) DEFAULT NULL,
				  descrizione varchar(100) DEFAULT NULL,
				  OPTION_CF varchar(100) DEFAULT NULL,
				  esito varchar(100) DEFAULT NULL,
				  scadenza_pan varchar(100) DEFAULT NULL,
				  messaggio varchar(100) DEFAULT NULL,
				  mail varchar(100) DEFAULT NULL,
				  codAut varchar(100) DEFAULT NULL,
				  alias varchar(100) DEFAULT NULL,
				  codiceEsito varchar(100) DEFAULT NULL,
				  orario varchar(100) DEFAULT NULL,
				  importo varchar(100) DEFAULT NULL,
				  languageId varchar(100) DEFAULT NULL,
				  cognome varchar(100) DEFAULT NULL,
				  pan varchar(100) DEFAULT NULL,
				  divisa varchar(100) DEFAULT NULL,
				  brand varchar(100) DEFAULT NULL,
				  nome varchar(100) DEFAULT NULL,
				  codTrans varchar(100) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
			
			");

			$image = ImageComposed::withFile(_MARION_MODULE_DIR_.'quipago/images/quipago.png')->save();
			if( is_object($image) ){
				$id_image = $image->getId();
			}
			$obj = PaymentMethod::create();
			$obj->set(
				array(
					'code' => 'QUIPAGO',
					'price' => 0,
					'visibility' => 1,
					'orderView' => 1,
					'enabled' => 1,
					'percentage' => 0,
					'closeCart' => 1,
					'image' => $id_image,
					'online' => 1
				)
			)->setData(
				array(
					'name' => 'Carta di credito'
				),'it'
			)->save();

			Form::import("{\"form\":{\"gruppo\":\"5\",\"nome\":\"quipago_conf\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"1068\",\"campo\":\"mac_live\",\"etichetta\":\"mac live\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1068\",\"campo\":\"mac_sandbox\",\"etichetta\":\"mac sandbox\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"5\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1068\",\"campo\":\"alias_live\",\"etichetta\":\"alias live\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1068\",\"campo\":\"alias_sandbox\",\"etichetta\":\"alias sandbox\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"6\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1068\",\"campo\":\"url_live\",\"etichetta\":\"url live\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1068\",\"campo\":\"url_sandbox\",\"etichetta\":\"url sandbox\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"7\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1068\",\"campo\":\"status_confirmed\",\"etichetta\":\"stato pagamento accettato\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"8\",\"tipo_valori\":\"0\",\"function_template\":\"array_status_confirmed\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"1068\",\"campo\":\"sandbox\",\"etichetta\":\"Modalit\\u00e0 sandbox\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null},\"valori\":[{\"campo\":\"7867\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":null}]}]}");

			$status = CartStatus::create();
			$data_status = array(
				'label' => 'payment_quipago_canceled',
				'color' => '#666666',
				'active' => 0,
				'locked' => 1,
				'orderView' => 1,
				'paid' => 0,
				'invoice' => 0,
				'sent' => 0,
				'send_mail' => 0
			);

			$data_status_locale = array(
				'name' => 'transazione quipago/cartasi annullata',
			);
			
			$status->set($data_status)->setData($data_status_locale,'it')->save();


			$status = CartStatus::create();
			$data_status = array(
				'label' => 'payment_quipago_nak',
				'color' => '#d9534f',
				'active' => 0,
				'locked' => 1,
				'orderView' => 1,
				'paid' => 0,
				'invoice' => 0,
				'sent' => 0,
				'send_mail' => 0
			);

			$data_status_locale = array(
				'name' => 'errore transazione quipago/cartasi',
			);
			
			$status->set($data_status)->setData($data_status_locale,'it')->save();

		}
		return $res;
	}



	function uninstall(){
		
		$res = parent::uninstall();
		
		if( $res ){
			$database = _obj('Database');
			Form::delete('quipago_conf');

			$obj = PaymentMethod::prepareQuery()->where('code','QUIPAGO')->getOne();
			if( is_object($obj) ){
				$obj->delete();
			}

			$database->execute('DROP TABLE transactionCartaSi');

			$status = CartStatus::prepareQuery()->where('label','payment_quipago_canceled')->getOne();
			if( is_object($status) ){
				$status->delete();
			}

			$status = CartStatus::prepareQuery()->where('label','payment_quipago_nak')->getOne();
			if( is_object($status) ){
				$status->delete();
			}
			
			
		}
		return $res;
	}



	function active()
	{	
		$obj = PaymentMethod::prepareQuery()->where('code','QUIPAGO')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 1;
			$obj->save();
		}
		parent::active();
	}


	function disable()
	{
		$obj = PaymentMethod::prepareQuery()->where('code','QUIPAGO')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 0;
			$obj->save();
		}
		parent::disable();
		
	}

}



?>