<?php

use Marion\Components\PageComposerComponent;
use Marion\Entities\Cms\PageComposer;
use SliderFull\SlideFull;




class WidgetSliderfull extends PageComposerComponent{

	



	function registerJS($data=null){

		PageComposer::registerJS("modules/sliderfull/js/sliderfull.js");

	}



	function build($data=null){

		

		

		

		$dati = $this->getParameters();

		

		$this->setVar('id_box',$this->id_box);

		

		require_once('classes/SlideFull.class.php');

		$date = date('Y-m-d');

		

		$id_slider = $dati['id_slider'];

		$slides = SlideFull::prepareQuery()->whereExpression("(dateStart is NULL OR dateStart <= '{$date}')")->where('id_slider',$id_slider)->whereExpression("(dateEnd is NULL OR dateEnd >= '{$date}')")->orderBy('orderView')->get();

		

		if(isMultilocale()){

			foreach($slides as $k => $slide){

				if( !in_array($GLOBALS['activelocale'],$slide->locales) ){

					unset($slides[$k]);

				}

			}

		}



		$this->setVar('slides',array_values($slides));

		

		if( count($slides) == 1 ){

			$this->setVar('only_one',1);

		}



		

	

		if( $this->isMobile()){

			

			$this->output('slider_mobile.htm');

		}else{

			$this->output('slider.htm');

		}



		



		



	}




	function export($directory){
		

		$dati = $this->getParameters();

		$id_slider = $dati['id_slider'];
		//$slides = SlideFull::prepareQuery()->where('id_slider',$id_slider)->get();
		$database = Marion::getDB();
		$select = $database->select('*','sliderfull',"id={$id_slider}");

		$select_slides = $database->select('*','slidefull',"id_slider={$id_slider}");
		$json = array(
			'slider' => $select[0],
			'slides' => $select_slides
		);
		foreach($select_slides as $v){
			$image = ImageComposed::withId($v['image']);
			if( is_object($image) ){
				$im = Image::withId($image->_original);

				$associazione[$image->getId()] = $im->file_src_pathname;
				$associazione_new[$image->getId()] = preg_replace('#^../upload/images/#','',$im->file_src_pathname);
			}

			$image = ImageComposed::withId($v['image_mobile']);
			if( is_object($image) ){
				$im = Image::withId($image->_original);

				$associazione[$image->getId()] = $im->file_src_pathname;
				$associazione_new[$image->getId()] = preg_replace('#^../upload/images/#','',$im->file_src_pathname);
				
			}
			
		}

		$zip = new ZipArchive;
		if ($zip->open($directory.'/images.zip', ZipArchive::CREATE) === TRUE)
		{
			
			foreach($associazione as $path){
				$new_path = preg_replace('#^../upload/images/#','',$path);
				
				if(file_exists($path)){
					//debugga($path);exit;
					$zip->addFile($path,$new_path);
				}
				
			}
			// All files are added, so close the zip file.
			$zip->close();
		}

		
		$json['associazione'] = $associazione_new;
		file_put_contents($directory."/dati.json",json_encode($json));
		

	}


	

	function import($directory){

		$parameters = $this->getParameters();
		
		$dati = json_decode(file_get_contents($directory."/dati.json"),true);
		$zip = new ZipArchive;
		$path_zip = $directory."/images.zip";
		if ($zip->open($path_zip) === TRUE) {
			$zip->extractTo($directory."/".'images');
			$zip->close();
		}
		$new = array();
		foreach($dati['associazione'] as $id_old => $path){
			$path = $directory."/".'images/'.$path;
			$image = ImageComposed::withFile($path);
			$image->save();
			
			$new[$id_old] = $image->getId();

			
		}

		//debugga($dati);
		$database = Marion::getDB();
		$slider = $dati['slider'];
		unset($slider['id']);
		$id_slider = $database->insert('sliderfull',$slider);
		foreach($dati['slides'] as $slide){
			unset($slide['id']);
			$slide['id_slider'] = $id_slider;
			$slide['image'] = $new[$slide['image']];
			$slide['image_mobile'] = $new[$slide['image_mobile']];
			$database->insert('slidefull',$slide);
		}
		$parameters['id_slider'] = $id_slider;
		$this->setParameters($parameters);
		//exit;

		return true;
		

	}




}

	



?>