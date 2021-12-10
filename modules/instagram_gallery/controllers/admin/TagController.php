<?php
class TagController extends ModuleController{
	public $_auth = 'cms';
	public $_twig = true;
	



	

	function ajax(){

		$action = $this->getAction();
		switch($action){
			case 'tag':
				$this->saveTag();
				break;
			case 'list':
				$this->listTags();
				break;
		}
		
	}


	function listTags(){
		$database = _obj('Database');
		$res = $database->select('*','instagram_image_tag',"pic_id=" . $_POST[ 'pic_id' ]);
	
		$data['boxes'] = '';
		$data['lists'] = '';
		foreach($res as $rs){
			if(!$rs[ 'name' ]){
				if( $rs['id_product'] ){
					$product = Product::withId($rs['id_product']);
					if( is_object($product) ){
						$rs['name'] = $product->get('name');

					}
				}
			}

			$data['boxes'] .= '<div class="tagview" style="left:' . $rs['pic_x'] . 'px;top:' . $rs['pic_y'] . 'px;" id="view_'.$rs['id'].'">';
			$data['boxes'] .= '<div class="square"></div>';
			$data['boxes'] .= '<div class="person" style="left:' . $rs['pic_x'] . 'px;top:' . $rs['pic_y']  . 'px;">' . $rs[ 'name' ] . '</div>';
			$data['boxes'] .= '</div>';
			if( $rs['id_product'] ){
				$product = Product::withId($rs['id_product']);
				if( is_object($product) ){
					$rs['name'] .= " - <b><span>".$product->get('name')."</span></b> "; 
				}
			}
			$data['lists'] .= '<li id="'.$rs['id'].'"><div style="width:20px; display:inline-block; border: 1px solid #333; border-radius:45px;background-color:'.$rs['color'].'">&nbsp;</div> <a>' . $rs['name'] . '</a> <a class="remove"><i class="fa fa-trash-o"></i></a></li>';
		}
		
		echo json_encode( $data );
	exit;
	}


	function saveTag(){
		$database = _obj('Database');
		if( !empty( $_POST['type'] ) && $_POST['type'] == "insert" )
		{
		  $id = _var('pic_id');  
		  $name = trim(_var('name'));
		  $id_product = _var('id_product');
			
		 

		  $pic_x = _var('pic_x');
		  $pic_y = _var('pic_y');
		  $color = _var('color');
		 
		  if( !$id_product ) $id_product = 0;
		  $sql = "INSERT INTO instagram_image_tag (pic_id,name,pic_x,pic_y,id_product,color) VALUES ( $id, '$name', $pic_x, $pic_y,$id_product,'$color')";
		
		  $database->execute($sql);

		 
		}

		if( !empty( $_POST['type'] ) && $_POST['type'] == "remove")
		{
		  $tag_id = $_POST['tag_id'];
		  $sql = "DELETE FROM instagram_image_tag WHERE id = '".$tag_id."'";
		  $database->execute($sql);
		}
	
	}


	

	
}



?>