<?php
use Marion\Entities\Cms\Interfaces\MenuItemFrontendInterface;
use Marion\Entities\Cms\LinkMenuFrontend;
use Photogallery\{PhotoGallery,PhotoGalleryImage};
class PhotoGalleryFrontend implements MenuItemFrontendInterface{
	
	public static function getGroupName(): string{
		 return 'Gallery';
	}


	public static function getUrl(array $params):string{
		$locale = $params['locale'];
		$id = $params['value'];
		$photogallery = PhotoGallery::withId($id);
		
		if( is_object($photogallery) ){
			return $photogallery->getUrl($locale);
		}
		return '';
	}
	
	public static function getPages():array{
		$photogallery = PhotoGallery::prepareQuery()->get();

		$list_url = array();
		if( okArray($photogallery) ){
			foreach($photogallery as $v){
				$list_url[$v->id] = $v->get('name');
			}
		}
		
		return $list_url;
	}


}

LinkMenuFrontend::registerItem('PhotoGalleryFrontend');
?>