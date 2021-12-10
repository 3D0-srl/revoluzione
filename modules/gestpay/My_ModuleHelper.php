<?php
class My_ModuleHelper extends ModuleHelper{

	

	function install(){
		$res = parent::install();
		if( $res ){
			$database = _obj('Database');
			

			$database->execute(
			"
			CREATE TABLE transazione_gestpay (
			  id bigint(20) UNSIGNED NOT NULL,
			  id_cart bigint(20) UNSIGNED NOT NULL,
			  checked tinyint(1) DEFAULT '0',
			  status varchar(10) DEFAULT NULL,
			  num int(11) DEFAULT NULL,
			  error_code varchar(5) DEFAULT NULL,
			  error_text tinytext
			);
			"	
			);

			$database->execute("ALTER TABLE transazione_gestpay ADD UNIQUE KEY id (id);");
			$database->execute("ALTER TABLE transazione_gestpay MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;");


			$image = ImageComposed::withFile('images/gestpay.png')->save();
			if( is_object($image) ){
				$id_image = $image->getId();
			}
			$obj = PaymentMethod::create();
			$obj->set(
				array(
					'code' => 'GESTPAY',
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

			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"gestpay_conf\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"564\",\"campo\":\"sandbox\",\"etichetta\":\"sandbox\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"4\",\"tipo\":\"6\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":\"1\",\"codice_php\":null,\"unique_value\":\"1\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"3\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":null,\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"2\",\"value_ifisnull\":\"0\",\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null},\"valori\":[{\"campo\":\"3854\",\"etichetta\":\"SI\",\"valore\":\"1\",\"locale\":\"it\",\"ordine\":\"1\"}]},{\"campo\":{\"form\":\"564\",\"campo\":\"shopLogin\",\"etichetta\":\"Shop Login\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"564\",\"campo\":\"status_confirmed\",\"etichetta\":\"stato pagamento accettato\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"2\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"4\",\"tipo_valori\":\"0\",\"function_template\":\"array_gestpay_status_confirmed\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}},{\"campo\":{\"form\":\"564\",\"campo\":\"shopLoginTest\",\"etichetta\":\"Shop Login Sandbox\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"1\",\"tipo\":\"0\",\"obbligatorio\":\"1\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"2\",\"tipo_valori\":\"1\",\"function_template\":null,\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null,\"dropzone\":\"0\",\"descrizione\":null,\"placeholder\":null}}]}");


			$status = CartStatus::create();
			$data_status = array(
				'label' => 'payment_gestpay_canceled',
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
				'name' => 'transazione gestpay annullata',
			);
			
			$status->set($data_status)->setData($data_status_locale,'it')->save();


			$status = CartStatus::create();
			$data_status = array(
				'label' => 'payment_gestpay_nak',
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
				'name' => 'errore transazione gestpay',
			);
			
			$status->set($data_status)->setData($data_status_locale,'it')->save();

		}
		return $res;
	}



	function uninstall(){
		
		$res = parent::uninstall();
		if( $res ){
			$database = _obj('Database');

			$database->execute("DROP TABLE transazione_gestpay");
			Form::delete('gestpay_conf');

			$obj = PaymentMethod::prepareQuery()->where('code','GESTPAY')->getOne();
			if( is_object($obj) ){
				$obj->delete();
			}

			

			$status = CartStatus::prepareQuery()->where('label','payment_gestpay_canceled')->getOne();
			if( is_object($status) ){
				$status->delete();
			}

			$status = CartStatus::prepareQuery()->where('label','payment_gestpay_nak')->getOne();
			if( is_object($status) ){
				$status->delete();
			}
			
			
		}
		return $res;
	}

}



?>