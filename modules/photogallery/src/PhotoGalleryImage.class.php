<?php
namespace Photogallery;
use Marion\Core\Base;
use Marion\Core\Marion;
use \ImageComposed;
class PhotoGalleryImage extends Base{
	
	// COSTANTI DI BASE
	const TABLE = 'photo_gallery_image'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'photo_gallery_image_lang'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'photo_gallery_image_id';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = ''; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'lang'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	
	//OVERRIDE METODO della classe Base
	function delete(){
		$image = ImageComposed::withId($this->image);

		if($image){
			$image->delete();
		}
		parent::delete();
	}


	function afterLoad(){
		parent::afterLoad();
		
		$this->infoImage();
	}

	
	function getUrlImage($type='original'){
		
		$database = Marion::getDB();
	
		$img = $database->select('i.*',"image as i join imageComposed as c on c.{$type}=i.id","c.id={$this->image}");
		if(okArray($img)){

			$name = $img[0]['filename_original'];
			$type_short = $this->getTypeImageUrl($type);
			
			return _MARION_BASE_URL_."img/{$this->image}/{$type_short}/{$name}";
		}
		
		return false;
	}
	
	//prende le informazioni sull'immagine
	function infoImage(){
		$database = Marion::getDB();
		
		$img = $database->select('i.*,c.date_insert',"image as i join imageComposed as c on c.original=i.id","c.id={$this->image}");
	
		if(okArray($img)){

			$this->infoImage = $img[0];
		}
		
		return $this;
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


}


?>