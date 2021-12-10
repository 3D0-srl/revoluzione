<?php
use Marion\Entities\Cms\Interfaces\MenuItemFrontendInterface;
use Marion\Entities\Cms\LinkMenuFrontend;
use News\{News,NewsType};
class NewsItemFrontend implements MenuItemFrontendInterface{
	
	public static function getGroupName(): string{
		 return 'News';
	}


	public static function getUrl(array $params):string{
		$locale = $params['locale'];
		$id = $params['value'];
		
		
		
		
		$news = News::withId($id);
		if( is_object($news) ){
			return $news->getUrl($locale);
		}
		
		return '';
	}
	
	public static function getPages():array{
		
		
		$news = News::prepareQuery()->where('visibility',1)->get();
		
		$list_url = array();
		if( okArray($news) ){
			foreach($news as $v){
				$list_url[$v->id] = $v->get('title');
			}
		}
		
		return $list_url;
	}


}

class NewsTypeItemFrontend implements MenuItemFrontendInterface{
	
	public static function getGroupName(): string{
		 return 'Categoria News';
	}


	public static function getUrl(array $params):string{

		$locale = $params['locale'];
		$id = $params['value'];
		
		$news = NewsType::withId($id);
		if( is_object($news) ){
			return $news->getUrl($locale);
		}
		
		return '';
	}
	
	public static function getPages():array{
	
		
		$news = NewsType::prepareQuery()->where('visibility',1)->get();
		$list_url = array();
		if( okArray($news) ){
			foreach($news as $v){
				$list_url[$v->id] = $v->get('name');
			}
		}

		return $list_url;
	}


}

LinkMenuFrontend::registerItem('NewsItemFrontend');
LinkMenuFrontend::registerItem('NewsTypeItemFrontend');



?>