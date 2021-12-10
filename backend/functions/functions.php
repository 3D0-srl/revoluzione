<?php
function array_layout_page(){
	$database = _obj('Database');
	$sel = $database->select('*','layout_page');
	
	foreach($sel as $v){
		$toreturn[$v['id']] = $v['nome'];
	}

	return $toreturn;
}
?>