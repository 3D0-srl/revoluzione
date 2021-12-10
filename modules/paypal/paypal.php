<?php
use Marion\Core\{Module,Marion,Form};
use Shop\{PaymentMethod,CartStatus};
class Paypal extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){
			$database = Marion::getDB();
			$database->execute("
				CREATE TABLE transactionPayPal (
				  cartId bigint(20) DEFAULT NULL,
				  cartNumber bigint(20) DEFAULT NULL,
				  token varchar(20) DEFAULT NULL,
				  payerId varchar(20) DEFAULT NULL,
				  transactionId varchar(20) DEFAULT NULL,
				  profileID varchar(100) DEFAULT NULL,
				  status varchar(100) DEFAULT 'pending',
				  checked tinyint(4) DEFAULT NULL,
				  ipn tinyint(1) DEFAULT '0',
				  buyerEmail VARCHAR(100) DEFAULT NULL,
				  rapid_checkout tinyint(1) DEFAULT '0',
				  type_checkout varchar(50) DEFAULT NULL,
				  dateInsert timestamp NULL DEFAULT CURRENT_TIMESTAMP
				)
			");

			$image = ImageComposed::withFile(_MARION_MODULE_DIR_.'paypal/images/paypal.png')->save();
			if( is_object($image) ){
				$id_image = $image->getId();
			}
			$obj = PaymentMethod::create();
			$obj->set(
				array(
					'code' => 'PAYPAL',
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
					'name' => 'Paypal'
				),'it'
			)->save();

			Form::import("{\"form\":{\"gruppo\":\"5\",\"nome\":\"paypal_conf\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"893\",\"campo\":\"sandbox\",\"etichetta\":\"modalit\\u00e0 sandbox\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null},\"valori\":[{\"campo\":\"6910\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]},{\"campo\":{\"form\":\"893\",\"campo\":\"status_confirmed\",\"etichetta\":\"stato pagamento accettato\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"10\",\"tipo_valori\":\"0\",\"function_template\":\"array_paypal_status_confirmed\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"893\",\"campo\":\"sandbox_client_id\",\"etichetta\":\"Client ID\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"893\",\"campo\":\"sandbox_client_secret\",\"etichetta\":\"Client Secret\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"893\",\"campo\":\"production_client_id\",\"etichetta\":\"Client ID\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"893\",\"campo\":\"production_client_secret\",\"etichetta\":\"Client Secret\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"893\",\"campo\":\"courier\",\"etichetta\":\"metodo di spedizione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"couriers\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":\"specifica il metodo di spedizione per il pagamento rapido\",\"placeholder\":null}},{\"campo\":{\"form\":\"893\",\"campo\":\"mandadory_address_fields\",\"etichetta\":\"Campi obbligatori per la spedizione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"mandadoryAddressFields\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":\"Seleziona i campi obbligatori per la spedizione nel pagamento rapido\",\"placeholder\":null}},{\"campo\":{\"form\":\"893\",\"campo\":\"enable_registration\",\"etichetta\":\"abilita registrazione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null},\"valori\":[{\"campo\":\"6939\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":null}]}]}");


			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"paypal_address\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"894\",\"campo\":\"shippingCountry\",\"etichetta\":\"Nazione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"array_nazioni_spedizione\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingProvince\",\"etichetta\":\"Provincia\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":\"array_province\",\"tipo_textarea\":null,\"tipo_data\":null,\"tipo_time\":null,\"tipo_file\":null,\"tipo_timestamp\":null,\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingPostalCode\",\"etichetta\":\"CAP\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"5\",\"lunghezzamax\":\"5\",\"type\":\"1\",\"tipo\":\"2\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingCity\",\"etichetta\":\"Citt\\u00e0\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingAddress\",\"etichetta\":\"Indirizzo\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingEmail\",\"etichetta\":\"Email\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingName\",\"etichetta\":\"Nome\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingSurname\",\"etichetta\":\"Cognome\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingCellular\",\"etichetta\":\"Cellulare\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"2\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"894\",\"campo\":\"shippingPhone\",\"etichetta\":\"Telefono\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"2\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");


			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"paypal_register\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":null,\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"895\",\"campo\":\"username\",\"etichetta\":\"username\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"6\",\"lunghezzamax\":\"50\",\"type\":\"1\",\"tipo\":\"11\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"895\",\"campo\":\"password\",\"etichetta\":\"password\",\"gettext\":\"0\",\"checklunghezza\":\"1\",\"lunghezzamin\":\"8\",\"lunghezzamax\":\"50\",\"type\":\"1\",\"tipo\":\"15\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":null,\"tipo_valori\":\"0\",\"function_template\":null,\"tipo_textarea\":null,\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":null,\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":\"N;\",\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");
			
			$status = CartStatus::create();
			if( is_object($status) ){
				$data_status = array(
					'label' => 'payment_paypal_canceled',
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
					'name' => 'transazione paypal annullata',
				);

				
				
				$status->set($data_status)
					->setData($data_status_locale,'it')
					->save();


			


				 $clientId = "Aerr5kGcTyo7DDUkVSkTl5xdX73foEWomvQmnv1wOhLhmU1hjs_0-dCV9u_l4LQYX9vPnAbUkD2-5AeB";
				 $clientSecret = "EEcSA4fUdDKLWF0-lqOk_L-khHrN0aHU_N3pw8OxCo_vGWMFwXBdYuUgwXXWhZfN4JTgBwce8_h7EnYk";

				 Marion::setConfig('paypal_module','sandbox_client_id',$clientId);
				 Marion::setConfig('paypal_module','sandbox_client_secret',$clientSecret);

				 Marion::refresh_config();

			}
			$status = CartStatus::create();
			if( is_object($status) ){
				$data_status = array(
					'label' => 'paypal_checkout',
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
					'name' => 'paypal checkout',
				);

				
				
				$status->set($data_status)
					->setData($data_status_locale,'it')
					->save();


			



				 $clientId = "Aerr5kGcTyo7DDUkVSkTl5xdX73foEWomvQmnv1wOhLhmU1hjs_0-dCV9u_l4LQYX9vPnAbUkD2-5AeB";
				 $clientSecret = "EEcSA4fUdDKLWF0-lqOk_L-khHrN0aHU_N3pw8OxCo_vGWMFwXBdYuUgwXXWhZfN4JTgBwce8_h7EnYk";

				 Marion::setConfig('paypal_module','sandbox_client_id',$clientId);
				 Marion::setConfig('paypal_module','sandbox_client_secret',$clientSecret);

				 Marion::refresh_config();

			}
			//payment_paypal_canceled

		}
		return $res;
	}



	function uninstall(){
		
		$res = parent::uninstall();
		if( $res ){
			$database = Marion::getDB();
			Form::delete('paypal_conf');
			Form::delete('paypal_register');
			Form::delete('paypal_address');

			$obj = PaymentMethod::prepareQuery()->where('code','PAYPAL')->getOne();
			if( is_object($obj) ){
				$obj->delete();
			}

			$database->execute("DROP TABLE transactionPayPal");
			$status = CartStatus::prepareQuery()->where('label','payment_paypal_canceled')->getOne();
			if( is_object($status) ){
				$status->delete();
			}
			$status = CartStatus::prepareQuery()->where('label','paypal_checkout')->getOne();
			if( is_object($status) ){
				$status->delete();
			}
			
			$dati = Marion::getConfig('paypal_module');
			foreach($dati as $k => $v){
				Marion::delConfig('paypal_module',$k);
			}
			Marion::refresh_config();
			
		}
		return $res;
	}

	function active()
	{	
		$obj = PaymentMethod::prepareQuery()->where('code','PAYPAL')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 1;
			$obj->save();
		}
		parent::active();
	}


	function disable()
	{
		$obj = PaymentMethod::prepareQuery()->where('code','PAYPAL')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 0;
			$obj->save();
		}
		parent::disable();
		
	}

}



?>