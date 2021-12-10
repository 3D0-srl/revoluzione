<?php

require ('../../../config.inc.php');
//$template = _obj('Template');

require_once('classes/PageComposerTemplate.class.php');
$composer_template = Marion::widget(basename(__DIR__));

$database = _obj('Database');
$action = _var('action');


Marion::setMenu('cms_page');
switch($action){
	case 'js_head':
		$id = _var('id');
		
		echo $GLOBALS['page_composer']->custom_js_head;
		
		exit;
	case 'js_end':
		
		$id = _var('id');
		echo $GLOBALS['page_composer']->custom_js_end;
		
		exit;

	case 'edit_tabs':

		$options_conf = array(
			'form_control' => 'page_composer_tabs_conf',
			'module' => basename(__DIR__),
			'template_html' => 'form_tabs.htm',
		);
		$obj = new PageComposerComponentConf($options_conf);
		$obj->render();
		
		break;
	case 'edit_tab':

		$options_conf = array(
			'form_control' => 'page_composer_tab_conf',
			'module' => basename(__DIR__),
			'template_html' => 'form_tab.htm',
		);
		$obj = new PageComposerComponentConf($options_conf);
		$obj->render();
		
		break;
	case 'edit_popup':

		$options_conf = array(
			'form_control' => 'page_composer_popup_conf',
			'module' => basename(__DIR__),
			'template_html' => 'form_popup.htm',
		);
		$obj = new PageComposerComponentConf($options_conf);
		$obj->render();
		
		break;
	case 'edit_page_ajax':
	case 'edit_page':
		$id = _var('id');

		$id_home = _var('id');
		$block = _var('block');
		
		
		$_page = $database->select('*','page_advanced',"id={$id}");
		if( okArray($_page) ){
			$_page = $_page[0];
			$layout = $database->select('*','layout_page',"id={$_page['id_layout']}");
			if( okArray($layout) ){
				$composer_template->blocks = json_decode($layout[0]['blocks']);
				
			}
		}
		

		$composer_template->id_page = $id;
		$composer_template->block = $block;
		
		if( $action == 'edit_page' ){
			$check = $database->select('count(*) as cont','composition_page_tmp',"id_adv_page={$id}");
			
			if( $check[0]['cont'] > 0 ){
				$composer_template->bozza = true;
				
			}else{
				$database->delete('composition_page_tmp',"id_adv_page={$id}");
				$database->execute("INSERT composition_page_tmp SELECT * FROM composition_page where id_adv_page={$id}");
			}

		}
		$composer = new PageComposer($id_home);
		$tree = $composer->buildTreeBlockEdit($block);
		

		
		$widgets = array_widget_base_pagecomposer();

		
		$composer_template->widgets = $widgets;

		
		$composer_template->items = $tree;

		if( $action == 'edit_page_ajax' ){
			ob_start();
			$composer_template->output('tree_ajax.htm');
			$html = ob_get_contents();
			ob_end_clean();

			$risposta = array(
				'result' => 'ok',
				'html' => $html
			);
			echo json_encode($risposta);
			exit;
		}else{
			$composer_template->output('tree.htm');
		}
		break;
	case 'edit_page_ok':
		$id_page = _var('id');
		$block = _var('block');
		$database->delete('composition_page',"id_adv_page={$id_page}");
		
		$database->execute("INSERT composition_page SELECT * FROM composition_page_tmp where id_adv_page={$id_page};");
		$database->delete('composition_page_tmp',"id_adv_page={$id_page}");
		$composer_template->link="index.php?action=edit_page&id={$id_page}&block={$block}";
		$composer_template->output('continua.htm');

		break;
	case 'get_widgets':
		$id_page = _var('id');
		$block = _var('block');
		$id_row = _var('id_row');
		$position = _var('position');
		$component = $database->select('*','composition_page_tmp',"id={$id_row}");

		if( okArray($component) ){
			$comp = $component[0];
			$box = PageComposerComponent::getTypeBox($comp['type']);
		}
		
		$module_widgets = $database->select('w.*,m.kind','widget as w join module as m on m.id=w.module',"m.active=1");
		
		if( okArray($module_widgets) ){
			foreach($module_widgets as $v){
				if( class_exists($v['function']) ){
					$class = $v['function'];
					$obj = new $class();
					$img_logo = $obj->getLogo();
					if( !$obj->isAvailable($box) ) continue;
				}
				$_widgets[] = array(
					'title' => $v['name'],
					'type' => 'module',
					'module' => $v['module'],
					'function' => $v['function'],
					'repeat' => $v['repeatable'],
					'id' => 0,
					'type_module' => $v['kind'],
					'url_edit' => $v['url_conf'],
					'img_logo' => $img_logo
				);

			}
		}

		
		
		
		
		
		$query = Page::prepareQuery();
		
		
		$query->where('theme',Marion::getConfig('SETTING_THEMES','theme'))->where('widget',1);
		

		$page = $query->get();
		if( okArray($page) ){
			foreach($page as $p){
				$_widgets[] = array(
					'title' => $p->get('url'),
					'type' => 'page',
					'module' => '',
					'id' => $p->id,
					'type_module' => 'pagine',
					'repeat' => 1,
					'img_logo' => '/modules/widget_html/img/logo.png'
				);

			}
		}

		foreach($_widgets as $v){
			$widgets[$v['module']."-".$v['type']."-".$v['id']."-".$v['function']] = $v;
			
		}
		

		
		
		$list = $database->select('*','composition_page_tmp',"id_adv_page={$id_page} AND block='{$block}' order by orderView ASC");
		
		$list_tmp = $list;
		unset($list);
		foreach($list_tmp as $k => $v){
			$list[$v['id']] = $v;
			if( $v['module'] ){
				$key = $v['module']."-".$v['function'];
			}else{
				$key = $v['module']."-".$v['type']."-".$v['id_page']."-".$v['function'];
			}
			if( $v['type'] == 'page'){
				$list[$v['id']]['url_edit'] = '/admin/content.php?action=mod_page&id='.$v['id_page'];
			}else{
				$list[$v['id']]['url_edit'] = $widgets[$key]['url_edit'];
			}
			if( !$widgets[$key]['repeat'] ){
			
				unset($widgets[$key]);
			}
			
		}
		
		foreach($widgets as $v){
			$gruppo[$v['type_module']][] = $v;
		}

		$widgets = array_widget_base_pagecomposer();
		foreach($widgets as $k => $v){
			$widgets[$k]['img_logo'] = $v['icon'];
		}
		$widgets[] = array(
					'title' => 'tabs',
					'type' => 'tabs',
					'module' => '',
					'id' => 0,
					'type_module' => 'elements',
					'repeat' => 1,
					//'img_logo' => '/modules/widget_html/img/logo.png'
				);

		$widgets[] = array(
					'title' => 'popup',
					'type' => 'popup_container',
					'module' => '',
					'id' => 0,
					'type_module' => 'elements',
					'repeat' => 1,
					//'img_logo' => '/modules/widget_html/img/logo.png'
				);
		$widgets[] =   array(
					'title' => 'accordions',
					'type' => 'accordion_container',
					'module' => '',
					'id' => 0,
					'type_module' => 'elements',
					'repeat' => 1,
					//'img_logo' => '/modules/widget_html/img/logo.png'
				);

		
		$gruppo['STRUTTURA'] = $widgets;

		

		/*$gruppo['ELEMENTI'][] = array(
			'title' => 'spazio',
			'type' => 'space',
			'module' => '',
			'id' => 0,
			'img_logo' => '/img/composer/space.png',
			'repeat' => 1,
		);*/
		
		
		
		
		$composer_template->id_row = $id_row;
		$composer_template->position = $position;
		
		ob_start();
		$composer_template->group_widgets = $gruppo;
		$composer_template->output('widgets.htm');
		$html = ob_get_contents();
		ob_end_clean();


		$risposta = array(
			'result' => 'ok',
			'html' => $html
		);
		echo json_encode($risposta);
		exit;
		


		break;
	case 'del_block':
		
		
		$id = _var('id');
		PageComposer::removeNode($id);
		
		
		$risposta = array(
			'result' => 'ok',
			//'html' => $html
		);
		echo json_encode($risposta);
		break;
	case 'cache_block':
		$id = _var('id');
		$cache= !(int)_var('cache');
		$array = array(
			'cache' => $cache
		);

		$database->update('composition_page_tmp',"id={$id}",$array);
		
		
		
		$risposta = array(
			'result' => 'ok',
			'cache' => $cache,
		);
		echo json_encode($risposta);

		break;
	case 'save_block_css':
		$id = _var('id');

		$array['id_html'] = _var('id_html');
		$array['class_html'] = _var('class_html');
		$array['animate_css'] = _var('animate_css');
		$database->update('composition_page_tmp',"id={$id}",$array);
		
		$risposta = array(
			'result' => 'ok',
			//'html' => $html
		);
		echo json_encode($risposta);

		break;
	case 'paste_box':

		$ids_box = json_decode(_var('ids_box'));
	
		$parent = _var('parent');
		foreach($ids_box as $id_box){
			
			PageComposer::copyNode($id_box,$parent);
		}
		$risposta = array(
			'result' => 'ok',
			//'html' => $html
		);
		echo json_encode($risposta);


		break;
	case 'add_block_to_page':
		$array['title'] = _var('title');
		$array['type'] = _var('type');
		$array['id_page'] = _var('id');
		$array['block'] = _var('block');
		$composer_template->block =_var('block');
		$array['module'] = _var('module');
		$array['module_function'] = _var('function');
		
		$id_home = _var('id_home');
		if( preg_match('/row-/',$array['type']) ){
			$array['type'] = 'row';
		}
		if( $array['module']  ){
			$module_widget = $database->select('*','widget',"module={$array['module']}");
			if( okArray($module_widget) ){
				//$array['module_function'] = $module_widget[0]['function'];
			}
		}

		/*if( $array['type'] == 'element' ){
			switch($array['title']){
				case 'space50':
					$array['content'] = "<div class='space50'></div>";
					break;
				case 'space30':
					$array['content'] = "<div class='space30'></div>";
					break;
			}
		}	*/

		$last = $database->select('max(orderView) as max','composition_page_tmp',"id_adv_page={$id_home}");
		$max = $last[0]['max']+1;
		$array['orderView'] = $max;
		$array['id_adv_page'] = $id_home;
		$array['parent'] = _var('parent');
		$array['position'] = _var('position');
		if( _var('type') == 'tab' || _var('type') == 'accordion' ){
			$last = $database->select('max(position) as max','composition_page_tmp',"id_adv_page={$id_home} AND parent={$array['parent']}");
			$position = $last[0]['max']+1;
			$array['position'] = $position;
		}

		
		$array['id'] = $database->insert('composition_page_tmp',$array);
		
		$num = 0;
		switch(_var('type')){
			case 'tabs':
				$child1 = array(
					'id_adv_page' => $id_home,
					'parent' => $array['id'],
					'position' => 1,
					'type' => 'tab',
					'orderView' => 1,
					'block' => $array['block'],
				);
				$child2 = array(
					'id_adv_page' => $id_home,
					'parent' => $array['id'],
					'position' => 2,
					'type' => 'tab',
					'orderView' => 2,
					'block' => $array['block'],
				);
				$database->insert('composition_page_tmp',$child1);
				$database->insert('composition_page_tmp',$child2);

				break;
			case 'row-1':
				$num = 1;
				$type = 'col-100';
				break;
			case 'row-2':
				$num = 2;
				$type = 'col-50';
				break;
			case 'row-3':
				$num = 3;
				$type = 'col-33';
				break;
			case 'row-4':
				$num = 4;
				$type = 'col-25';
				break;
			case 'row-25-75':
				$child1 = array(
					'id_adv_page' => $id_home,
					'parent' => $array['id'],
					'position' => 1,
					'type' => 'col-25',
					'orderView' => 1,
					'block' => $array['block'],
				);
				$child2 = array(
					'id_adv_page' => $id_home,
					'parent' => $array['id'],
					'position' => 2,
					'type' => 'col-75',
					'orderView' => 2,
					'block' => $array['block'],
				);
				$database->insert('composition_page_tmp',$child1);
				$database->insert('composition_page_tmp',$child2);
				
				break;
			case 'row-75-25':
				$child1 = array(
					'id_adv_page' => $id_home,
					'parent' => $array['id'],
					'position' => 1,
					'type' => 'col-75',
					'orderView' => 1,
					'block' => $array['block'],
				);
				$child2 = array(
					'id_adv_page' => $id_home,
					'parent' => $array['id'],
					'position' => 2,
					'type' => 'col-25',
					'orderView' => 2,
					'block' => $array['block'],
				);
				$database->insert('composition_page_tmp',$child1);
				$database->insert('composition_page_tmp',$child2);
				
				break;
		}
		
		for( $k=1;$k<=$num;$k++ ){
			$child = array(
				'id_adv_page' => $id_home,
				'parent' => $array['id'],
				'position' => $k,
				'type' => $type,
				'orderView' => $k,
				'block' => $array['block'],
			);
			$database->insert('composition_page_tmp',$child);
			
		}
		
		
		$risposta = array(
			'result' => 'ok',
			'dati' => $array,
		);
		echo json_encode($risposta);

		break;
	case 'save_composition':

		$id_page = _var('id');
		$block = _var('block');
		$list = _var('list');
		foreach($list as $k => $v){
			$database->update('composition_page_tmp',"id={$v['id']} AND id_adv_page={$id_page} AND block='{$block}'",array('orderView' => $k+1));
		}
		$risposta = array(
			'result' => 'ok'
		);
		echo json_encode($risposta);
		break;
	case 'sort_items':
		$id_box = _var('id_box');
		$items = $database->select('*','composition_page_tmp',"parent={$id_box} order by orderView");
			
		foreach($items as $k => $v){
			$function = $v['module_function'];
			
			if( class_exists($function) ){
				$object = new $function();
				
				$items[$k]['img_logo'] = $object->getLogo($v);
				

			}
			
			switch($v['type']){
				case 'row':
					$items[$k]['img_logo'] = '/img/composer/row-blank.jpg';
					break;
			}
		}
		

		$composer_template->id_box = $id_box;
		$composer_template->items = $items;
		$composer_template->output('page_composer_sort_items.htm');
		break;
	case 'save_order_row':
		$type = _var('type');
		$ids_box = json_decode(_var('items'));
		foreach($ids_box as $k => $v){
			if( $type == 'tab' ){
				$update = array(
					'orderView'=>($k+1),
					'position' => ($k+1),	
				);
			}else{

				$update = array(
					'orderView'=>($k+1)
				);

			}
			$database->update('composition_page_tmp',"id={$v}",$update);
			
		}
		$risposta = array(
				'result' => 'ok',
		);
		echo json_encode($risposta);

		break;
	case 'preview_page':
		$id_page = _var('id_page');
		$composer = new PageComposer($id_page,true);
		
		$composer->render();
		Marion::closeDB();
		break;
}



function array_widget_base_pagecomposer(){
	$widgets = array( 
		array(
			'title' => 'blank',
			'type' => 'row',
			'module' => '',
			'id' => 0,
			'icon' => '/img/composer/row-blank.jpg',
			'repeat' => 1,
		),
		array(
			'title' => '1 col.',
			'type' => 'row-1',
			'module' => '',
			'id' => 0,
			'icon' => '/img/composer/row-1.jpg',
			'repeat' => 1,
		),
		array(
			'title' => '2 col.',
			'type' => 'row-2',
			'module' => '',
			'id' => 0,
			'icon' => '/img/composer/row-2.jpg',
			'repeat' => 1,
		),
	
		array(
			'title' => '3 col.',
			'type' => 'row-3',
			'module' => '',
			'id' => 0,
			'icon' => '/img/composer/row-3.jpg',
			'repeat' => 1,
		),
		array(
			'title' => '4 col.',
			'type' => 'row-4',
			'module' => '',
			'id' => 0,
			'icon' => '/img/composer/row-4.jpg',
			'repeat' => 1,
		),
	
		array(
			'title' => '2 col. 75 / 25',
			'type' => 'row-75-25',
			'module' => '',
			'id' => 0,
			'icon' => '/img/composer/row-75-25.jpg',
			'repeat' => 1,
		),
		array(
			'title' => '2 col. 25 / 75',
			'type' => 'row-25-75',
			'module' => '',
			'id' => 0,
			'icon' => '/img/composer/row-25-75.jpg',
			'repeat' => 1,
		),
		
	);

	return $widgets;

}
?>