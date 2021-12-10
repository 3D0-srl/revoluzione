<?php
use Marion\Controllers\FrontendController;
use News\{News,NewsType};
class NewsController extends FrontendController
{	

	function setMedia(){
		parent::setMedia();
		$this->registerCSS(_MARION_BASE_URL_."modules/news/css/style.css");
		$this->registerCSS(_MARION_BASE_URL_."modules/pagecomposer/css/pagecomposer.css");
		$this->registerJS("/modules/news/js/script.js");
		$this->registerJS("//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.js");
		$this->registerCSS("//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css");
	}




	function all($category){
		$lang = _MARION_LANG_;
		$categories = NewsType::prepareQuery()->get();


		$this->setVar('categories', $categories);

		if (!empty($category)) {
			$type = NewsType::withSlug($category);
			$result = News::prepareQuery()->where('type_news', $type->id)->get();
			$this->setVar('active', $category);

			$this->setVar('news_list', $result);
			$this->output('news_list.htm');
		} else {
			$result = News::prepareQuery()->get();
			$this->setVar('active', -1);

			$this->setVar('news_list', $result);
			$this->output('news_list.htm');
		}
	}


	function view($id,$nome){
		$lang = _MARION_LANG_;
		$result = News::withId($id);

		$image_count = count($result->images);

		$this->setVar('image_count', $image_count);

		$this->setVar('news', $result);

		$this->output('vedi_news.htm');
	}
}
