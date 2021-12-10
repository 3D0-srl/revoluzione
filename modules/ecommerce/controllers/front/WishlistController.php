<?php
use Marion\Controllers\BackendController;
use Marion\Core\Marion;
use \Product;
class WishlistController extends BackendController{

	function __construct($options=[])
	{
		$this->setMenu('ecommerce_wishlist');
		parent::__construct($options);
	}

	function setMedia(){
		parent::setMedia();
		$this->registerCSS('modules/ecommerce/css/backend_ecommerce.css');
		$this->registerJS('plugins/lazyload/dist/lazyload.min.js','end');
		$this->registerJS('modules/catalogo/js/lazyload.js','end');
		$this->registerJS('plugins/jquery.inview.min.js');
		$this->registerJS('modules/ecommerce/js/backend_wishlist.js');
	}






	function getSharedLink(){
		$user = Marion::getUser();
		$array = array(
			'id' => $user->id,
			'name' => $user->name
		);
		return 'http://'.$_SERVER['SERVER_NAME']._MARION_BASE_URL_."wishlist/".base64_encode(serialize($array));
	}


	function view($referral){
		
		$referral = unserialize(base64_decode($referral));
		if( okArray($referral) && $referral['id'] ){
			$database = Marion::getDB();
			$user = Marion::getUser();
			$query = Product::prepareQuery()->where('deleted',0)->where('visibility',1)->whereExpression("(id in (select product from wishlist where user={$referral['id']}))");
			$prodotti = $query->get();
			
			$title_share = _translate('la mia wishlist','ecommerce');
			$this->setVar('title_share',$title_share);
			if( okArray($prodotti) ){
				$prod = $prodotti[0];
				$this->setVar('image_share',$prod->getUrlImage(0));


			}
			$link = $this->getSharedLink();
			$this->setVar('link',$link);
			$this->setVar('share_url_facebook',"http://www.facebook.com/sharer/sharer.php?u={$link}&title={$title_share}");

			$database->registerQuery($query->lastquery,'wishlist');
			$this->setVar('name',$referral['name']);


			$this->setVar('prodotti',$prodotti);
			$this->output('shared_wishlist.htm');
		}else{
			header('Location: '._MARION_BASE_URL_.'index.php');
		}
		
	}


	function index(){
		if( authUser()){


			$database = Marion::getDB();
			$user = Marion::getUser();
			$query = Product::prepareQuery()->where('deleted',0)->where('visibility',1)->whereExpression("(id in (select product from wishlist where user={$user->id}))");
			$prodotti = $query->get();
			$database->registerQuery($query->lastquery,'wishlist');
			

			$title_share = _translate('la mia wishlist','ecommerce');
			$this->setVar('title_share',$title_share);
			if( okArray($prodotti) ){
				$prod = $prodotti[0];
				$this->setVar('image_share',$prod->getUrlImage(0));


			}
			$link = $this->getSharedLink();
			$this->setVar('link',$link);
			$this->setVar('share_url_facebook',"http://www.facebook.com/sharer/sharer.php?u={$link}&title={$title_share}");
			
			
			$this->setVar('prodotti',$prodotti);
			$this->output('wishlist.htm');
		}else{
			$this->notAuth();
		}

	}


	function ajax(){
		$action = $this->getAction();
		switch($action){
			case 'share_email':
				$email = _var('email');
				if( !$email ){
					$error = _translate('missing_email','ecommerce');
					echo json_encode(
						array(
							'result' => 'nak',
							'error' => $error
						)
					);
					exit;
				}
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$error = _translate('invalid_email','ecommerce');
					echo json_encode(
						array(
							'result' => 'nak',
							'error' => $error
						)
					);
					exit;
				}
				$this->sendMail($email);
				echo json_encode(
						array(
							'result' => 'ok'
						)
					);
				exit;
				break;
		}
	}


	function sendMail($email){
		$user = Marion::getUser();

		$generale = Marion::getConfig('generale');
		$dati_eshop = Marion::getConfig('eshop');
		//debugga($generale);exit;

		
		
		

		$title = _translate('share_wishlist_message_title','ecommerce');
		$title = sprintf($title,$generale['nomesito']);
		$this->setVar('message_title',$title);
		

		$link = $this->getSharedLink();

		$text = _translate('share_wishlist_message_text','ecommerce');
		$text = sprintf($text,$user->name);
		$this->setVar('message_text',$text);

		$this->setVar('link',$link);

		//preparo l'html
		ob_start();
		$this->output('ecommerce_mails/wishlist.htm');
		$html = ob_get_contents();
		ob_end_clean();
		
		
		
		
		$mail = _obj('Mail');
		$mail->setHtml($html);
		$subject = _translate('subject_mail_wishlist','ecommerce');
		$subject = sprintf($subject,$generale['nomesito']);
		
		$mail->setSubject($subject);
		
		
		$mail->setTo($email);
		$mail->setFrom($user->email);
		$res = $mail->send();
		

		return $res;
	}
}