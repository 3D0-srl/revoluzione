<?php
class Contrassegno extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){

			Form::import("{\"form\":{\"gruppo\":\"7\",\"nome\":\"module_cod\",\"commenti\":null,\"action\":null,\"url\":null,\"method\":\"POST\",\"captcha\":\"0\"},\"campi\":[{\"campo\":{\"form\":\"690\",\"campo\":\"shippingMethods\",\"etichetta\":\"metodi di spedizione\",\"gettext\":\"0\",\"checklunghezza\":\"0\",\"lunghezzamin\":null,\"lunghezzamax\":null,\"type\":\"9\",\"tipo\":\"0\",\"obbligatorio\":\"0\",\"valuezero\":\"0\",\"default_value\":null,\"codice_php\":null,\"unique_value\":\"0\",\"globale\":\"0\",\"attivo\":\"1\",\"multilocale\":\"0\",\"ordine\":\"1\",\"tipo_valori\":\"0\",\"function_template\":\"array_shippingMethods\",\"tipo_textarea\":\"0\",\"tipo_data\":\"0\",\"tipo_time\":\"0\",\"tipo_file\":\"0\",\"tipo_timestamp\":\"0\",\"ext_image\":null,\"resize_image\":null,\"dimension_resize_default\":\"0\",\"dimension_image\":null,\"ext_attach\":null,\"number_files\":\"0\",\"class\":\"form-control\",\"post_function\":null,\"pre_function\":null,\"ifisnull\":\"0\",\"value_ifisnull\":null}}]}");

			$database = _obj('Database');
			

			

			$image = ImageComposed::withFile(_MARION_MODULE_DIR_.'contrassegno/images/cod.png')->save();
			if( is_object($image) ){
				$id_image = $image->getId();
			}
			$obj = PaymentMethod::create();
			$obj->set(
				array(
					'code' => 'COD',
					'price' => 0,
					'visibility' => 1,
					'orderView' => 1,
					'enabled' => 1,
					'percentage' => 0,
					'closeCart' => 1,
					'image' => $id_image,
					'online' => 0
				)
			)->setData(
				array(
					'name' => 'Contrassegno'
				),'it'
			)->save();

			


			$status = CartStatus::create();
			$data_status = array(
				'label' => 'waiting_cod',
				'color' => '#5bc0de',
				'active' => 1,
				'locked' => 0,
				'orderView' => 1,
				'paid' => 0,
				'invoice' => 0,
				'sent' => 0,
				'send_mail' => 0
			);

			$data_status_locale = array(
				'name' => 'in attesa di contrassegno',
			);
			
			$status->set($data_status)->setData($data_status_locale,'it')->save();



		}
		return $res;
	}



	function uninstall(){
		
		$res = parent::uninstall();
		if( $res ){

			Form::delete('module_cod');
			$database = _obj('Database');

			
			$obj = PaymentMethod::prepareQuery()->where('code','COD')->getOne();
			if( is_object($obj) ){
				$obj->delete();
			}

			

			$status = CartStatus::prepareQuery()->where('label','waiting_cod')->getOne();
			if( is_object($status) ){
				$status->delete();
			}

			$obj = PaymentMethod::prepareQuery()->where('code','CONTRASSEGNO')->getOne();
			if( is_object($obj) ){
				$obj->delete();
			}

			

			$status = CartStatus::prepareQuery()->where('label','waiting_contrassegno')->getOne();
			if( is_object($status) ){
				$status->delete();
			}

			
			
		}
		return $res;
	}


	function active()
	{	
		$obj = PaymentMethod::prepareQuery()->where('code','CONTRASSEGNO')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 1;
			$obj->save();
		}
		parent::active();
	}


	function disable()
	{
		$obj = PaymentMethod::prepareQuery()->where('code','CONTRASSEGNO')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 0;
			$obj->save();
		}
		parent::disable();
		
	}

	

}



?>