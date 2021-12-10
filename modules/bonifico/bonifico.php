<?php
use Marion\Core\{Module};
use Shop\{PaymentMethod,CartStatus};
class Bonifico extends Module{

	

	function install(){
		$res = parent::install();
		if( $res ){
			
			

			
			$obj = PaymentMethod::prepareQuery()->where('code','BONIFICO')->getOne();
			if( !is_object($obj) ){
				$image = ImageComposed::withFile(_MARION_MODULE_DIR_.'bonifico/images/bonifico.png')->save();
				if( is_object($image) ){
					$id_image = $image->getId();
				}
				$obj = PaymentMethod::create();
				$obj->set(
					array(
						'code' => 'BONIFICO',
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
						'name' => 'Bonifico'
					),'it'
				)->save();
			}

			

			$status = CartStatus::prepareQuery()->where('label','waiting_bonifico')->getOne();
			if( !is_object($status) ){
				$status = CartStatus::create();
				$data_status = array(
					'label' => 'waiting_bonifico',
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
					'name' => 'in attesa di bonifico',
				);
			
				$status->set($data_status)->setData($data_status_locale,'it')->save();
			}



		}
		return $res;
	}



	function uninstall(){
		
		$res = parent::uninstall();
		if( $res ){
			
			$obj = PaymentMethod::prepareQuery()->where('code','BONIFICO')->getOne();
			if( is_object($obj) ){
				$obj->delete();
			}

			

			$status = CartStatus::prepareQuery()->where('label','waiting_bonifico')->getOne();
			if( is_object($status) ){
				$status->delete();
			}

			
			
		}
		return $res;
	}


	function active()
	{	
		$obj = PaymentMethod::prepareQuery()->where('code','BONIFICO')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 1;
			$obj->save();
		}
		parent::active();
	}


	function disable()
	{
		$obj = PaymentMethod::prepareQuery()->where('code','BONIFICO')->getOne();
		if( is_object($obj) ){
			$obj->enabled = 0;
			$obj->save();
		}
		parent::disable();
		
	}



}



?>