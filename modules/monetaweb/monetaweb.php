<?php
class Monetaweb extends Module{

	

function install(){
		$res = parent::install();
		if( $res ){
			$database = _obj('Database');
			

			

			/*$image = ImageComposed::withFile(_MARION_MODULE_DIR_.'quipago/images/monetaweb.png')->save();
			if( is_object($image) ){
				$id_image = $image->getId();
			}*/
			$id_iamge = 0;
			$obj = PaymentMethod::create();
			$obj->set(
				array(
					'code' => 'MONETAWEB',
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

			

			$status = CartStatus::create();
			$data_status = array(
				'label' => 'payment_monetaweb_canceled',
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
				'name' => 'transazione monetaweb annullata',
			);
			
			$status->set($data_status)->setData($data_status_locale,'it')->save();


			$status = CartStatus::create();
			$data_status = array(
				'label' => 'payment_monetaweb_nak',
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
				'name' => 'errore transazione monetaweb',
			);
			
			$status->set($data_status)->setData($data_status_locale,'it')->save();

		}
		return $res;
	}



	function uninstall(){
		
		$res = parent::uninstall();
		if( $res ){
			$database = _obj('Database');
			
			$obj = PaymentMethod::prepareQuery()->where('code','MONETAWEB')->getOne();
			if( is_object($obj) ){
				$obj->delete();
			}

			
			$status = CartStatus::prepareQuery()->where('label','payment_monetaweb_canceled')->getOne();
			if( is_object($status) ){
				$status->delete();
			}

			$status = CartStatus::prepareQuery()->where('label','payment_monetaweb_nak')->getOne();
			if( is_object($status) ){
				$status->delete();
			}
			
			
		}
		return $res;
	}



	function active()
	{	
		$obj = PaymentMethod::prepareQuery()->where('code','MONETAWEB')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 1;
			$obj->save();
		}
		parent::active();
	}


	function disable()
	{
		$obj = PaymentMethod::prepareQuery()->where('code','MONETAWEB')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 0;
			$obj->save();
		}
		parent::disable();
		
	}

}



?>