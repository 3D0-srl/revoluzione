<?php
use Marion\Controllers\FrontendController;
use Photogallery\PhotoGallery;
use Photogallery\PhotoGalleryImage;
use Marion\Core\Marion;
class FrontController extends FrontendController
{

	function setMedia()
	{
		parent::setMedia();

		$this->registerJS("//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.js");
		$this->registerCSS("//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css");
		$this->registerCSS("https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css");
	}

	function display()
	{

		$action = $this->getAction();

		switch ($action) {
			case 'show_gallery':
				$this->displayGallery();
				break;

			case 'show_gallery_api':
				$this->displayGalleryApi();
				break;

			case 'display_all_api':
				$this->displayAllApi();
				break;

			case 'widget_html':
				$this->widgetHtml();
				break;

			default:
				$this->displayAll();
				break;
		}
	}

	function displayGallery()
	{
		require_once 'modules/photogallery/classes/PhotoGallery.class.php';
		require_once 'modules/photogallery/classes/PhotoGalleryImage.class.php';
		$id = _var('id');
		$query = PhotoGallery::create()->prepareQuery();
		if (!$id) {
			$slug = _var('slug');
			$query->where('slug', $slug);
		} else {
			$query->where('id', $id);
		}
		$gallery = $query->getOne();
		if (is_object($gallery)) {

			$listImages = PhotoGalleryImage::create()->prepareQuery()->where('gallery', $gallery->id)->get();
			if (okArray($listImages)) {
				foreach ($listImages as $item) {
					$image['url'] = $item->getUrlImage('small');
					$image['name'] = $item->get('name');
					$image['description'] = $item->get('description');

					$photogallery['images'][] = $image;
					$image = [];
				}
			}
			$photogallery['id'] = $gallery->id;
			$photogallery['name'] = $gallery->get('name');
			$photogallery['description'] = $gallery->get('description');
			$photogallery['descriptionShort'] = $gallery->get('descriptionShort');

			$this->setVar('photogallery', $photogallery);
		}
		$this->output('show_photo.htm');
	}

	function displayGalleryApi()
	{
		require_once 'modules/photogallery/classes/PhotoGallery.class.php';
		require_once 'modules/photogallery/classes/PhotoGalleryImage.class.php';
		$id = _var('id');
		$gallery = PhotoGallery::create()->prepareQuery()->where('id', $id)->getOne();
		$listImages = PhotoGalleryImage::create()->prepareQuery()->where('gallery', $id)->get();

		foreach ($listImages as $item) {
			$image['url'] = $item->getUrlImage('small');
			$image['name'] = $item->get('name');
			$image['description'] = $item->get('description');

			$photogallery['images'][] = $image;
			$image = [];
		}

		$photogallery['id'] = $id;
		$photogallery['name'] = $gallery->get('name');
		$photogallery['description'] = $gallery->get('description');
		$photogallery['descriptionShort'] = $gallery->get('descriptionShort');

		echo json_encode($photogallery);
	}

	function displayAll()
	{
		require_once 'modules/photogallery/classes/PhotoGallery.class.php';
		require_once 'modules/photogallery/classes/PhotoGalleryImage.class.php';
		$listPhotogallery = PhotoGallery::create()->prepareQuery()->orderBy('orderView')->get();
		foreach ($listPhotogallery as $photogallery) {
			$first_image = Marion::getConfig('photogallery_settings')[$photogallery->id];
			$listImages = PhotoGalleryImage::create()->prepareQuery()->where('gallery', $photogallery->id)->where('image', $first_image)->get()[0];
			$item['name'] = $photogallery->get('name');
			$item['image'] = $listImages->getUrlImage('small');
			$item['descriptionShort'] = $photogallery->get('descriptionShort');
			$item['parent_id'] = $photogallery->id;

			$items[] = $item;
		}
		$this->setVar('items', $items);
		$this->output('show_gallery.htm');
	}

	function displayAllApi()
	{
		$order = _var('order');
		require_once 'modules/photogallery/classes/PhotoGallery.class.php';
		require_once 'modules/photogallery/classes/PhotoGalleryImage.class.php';
		if (!empty($order)) {
			$listPhotogallery = PhotoGallery::create()->prepareQuery()->orderBy($order)->get();
		} else {
			$listPhotogallery = PhotoGallery::create()->prepareQuery()->orderBy('orderView')->get();
		}
		foreach ($listPhotogallery as $photogallery) {
			$first_image = Marion::getConfig('photogallery_settings')[$photogallery->id];
			$listImages = PhotoGalleryImage::create()->prepareQuery()->where('gallery', $photogallery->id)->where('image', $first_image)->get()[0];
			$item['name'] = $photogallery->get('name');
			$item['image'] = $listImages->getUrlImage('small');
			$item['descriptionShort'] = $photogallery->get('descriptionShort');
			$item['parent_id'] = $photogallery->id;

			$items[] = $item;
		}
		echo json_encode($items);
	}

	function widgetHtml()
	{ 
		require_once 'modules/photogallery/classes/PhotoGalleryImage.class.php';

		$id = _var('id');

		$listImages = PhotoGalleryImage::create()->prepareQuery()->where('gallery', $id)->get();
		
		foreach ($listImages as $item) {
			$image['url'] = $item->getUrlImage('large');
			$image['url_original'] = $item->getUrlImage('original');
			$image['name'] = $item->get('name');
			$image['description'] = $item->get('description');
			$image['link_redirect'] = $item->get('url');
			$image['fancybox'] = $item->get('fancybox');

			$photogallery['images'][] = $image;
			$image = [];
		}

		$this->setVar('images', $photogallery['images']);
		
		ob_start();
		$this->output('box.htm');
		$html = ob_get_contents();
		ob_end_clean();

		echo $html;
	}
}
