<?php
require ('../../../config.inc.php');

$template = _obj('Template');
if( !authAdminUser() ) header( "Location: /index.php");



$database = _obj('Database');

$action = _var('action');

//$gapi = _obj('Analytics');



$template->current_admin = 'google_analytics';

if( $action == 'locality'){
	$template->current_admin_child = 'analytics_geo';
	$gapi = _obj('Analytics');

	if( !$gapi->no_key ){
		$list_filter = array('city','region','country','continent');

		foreach($list_filter as $filter){
			$dimensions = array($filter);
			$metrics = array("sessions","percentNewSessions","newUsers","avgSessionDuration","bounceRate","pageviewsPerSession","goalConversionRateAll","goalCompletionsAll","goalValueAll");
			
			$report = $gapi->createReport();
			$report->setDimensions($dimensions);
			$report->setMetrics($metrics);
			$report->sortBy('sessions','DESC');
			
			
			
			$result = $report->get();
			$tmp = array();
			foreach($result as $k => $v){
				$v['metrics']['percentNewSessions'] = number_format($v['metrics']['percentNewSessions'],2,'.','');
				$v['metrics']['bounceRate'] = number_format($v['metrics']['bounceRate'],2,'.','');
				$v['metrics']['pageviewsPerSession'] = number_format($v['metrics']['pageviewsPerSession'],2,'.','');
				$v['metrics']['avgSessionDuration'] = number_format($v['metrics']['avgSessionDuration']/60,2,'.','');
				$tmp[$v['dimensions'][$filter]] = $v['metrics'];
			}
			$toreturn[$filter]=$tmp;
			//debugga($toreturn);exit;
		}
		$template->dati = $toreturn;
	}
	
	
	$template->output_module(basename(__DIR__),'analytics_geo.htm');

}elseif( $action == 'tecnology'){
	$template->current_admin_child = 'analytics_tecnology';
	$gapi = _obj('Analytics');
	if( !$gapi->no_key ){
		$list_filter = array('browser','deviceCategory',"operatingSystem","mobileDeviceBranding");

		foreach($list_filter as $filter){
			$dimensions = array($filter);
			$metrics = array("sessions","percentNewSessions","newUsers","avgSessionDuration","bounceRate","pageviewsPerSession","goalConversionRateAll","goalCompletionsAll","goalValueAll");
			
			$report = $gapi->createReport();
			$report->setDimensions($dimensions);
			$report->setMetrics($metrics);
			$report->sortBy('sessions','DESC');
			
			
			
			$result = $report->get();
			$tmp = array();
			foreach($result as $k => $v){
				$v['metrics']['percentNewSessions'] = number_format($v['metrics']['percentNewSessions'],2,'.','');
				$v['metrics']['bounceRate'] = number_format($v['metrics']['bounceRate'],2,'.','');
				$v['metrics']['pageviewsPerSession'] = number_format($v['metrics']['pageviewsPerSession'],2,'.','');
				$v['metrics']['avgSessionDuration'] = number_format($v['metrics']['avgSessionDuration']/60,2,'.','');
				$tmp[$v['dimensions'][$filter]] = $v['metrics'];
			}
			$toreturn[$filter]=$tmp;
			//debugga($toreturn);exit;
		}
		$template->dati = $toreturn;
	}
	
	$template->output_module(basename(__DIR__),'analytics_tecnology.htm');


}elseif( $action == 'traffic'){
	$template->current_admin_child = 'analytics_traffic';
	$gapi = _obj('Analytics');
	if( !$gapi->no_key ){
		$list_filter = array('campaign','socialNetwork');

		foreach($list_filter as $filter){
			$dimensions = array($filter);
			$metrics = array("sessions","percentNewSessions","newUsers","avgSessionDuration","bounceRate","pageviewsPerSession","goalConversionRateAll","goalCompletionsAll","goalValueAll");
			
			$report = $gapi->createReport();
			$report->setDimensions($dimensions);
			$report->setMetrics($metrics);
			$report->sortBy('sessions','DESC');
			
			
			
			$result = $report->get();
			$tmp = array();
			foreach($result as $k => $v){
				$v['metrics']['percentNewSessions'] = number_format($v['metrics']['percentNewSessions'],2,'.','');
				$v['metrics']['bounceRate'] = number_format($v['metrics']['bounceRate'],2,'.','');
				$v['metrics']['pageviewsPerSession'] = number_format($v['metrics']['pageviewsPerSession'],2,'.','');
				$v['metrics']['avgSessionDuration'] = number_format($v['metrics']['avgSessionDuration']/60,2,'.','');
				$tmp[$v['dimensions'][$filter]] = $v['metrics'];
			}
			$toreturn[$filter]=$tmp;
			//debugga($toreturn);exit;
		}
		$template->dati = $toreturn;
	}
	
	$template->output_module(basename(__DIR__),'analytics_traffic.htm');


}elseif( $action == 'conf'){
	$template->current_admin_child = 'analytics_conf';
	$database = _obj('Database'); 
	$select = $database->select('*','setting',"gruppo='analytics'");
	$dati = array();
	foreach($select as $v){
		$dati[$v['chiave']] = $v['valore']; 
	}
	
	get_form($elements,'module_analytics','conf_ok',$dati);
	$template->output_module(basename(__DIR__),'analytics_conf.htm',$elements);
	//$template->output_module(basename(__DIR__),'ciao.htm',$elements);
}elseif($action == 'conf_ok'){
	$formdata = _var('formdata');
	$array = check_form($formdata,'module_analytics');
	if($array[0] == 'ok'){
		unset($array[0]);
		foreach($array as $k => $v){
			$database->update('setting',"gruppo='analytics' AND chiave = '{$k}'",array('valore'=>$v));
		}
		
		$cache = _obj('Cache');
		if( $cache->isExisting("setting") ){
			$cache->delete('setting');
		}
		$template->link = "/admin/modules/".basename(__DIR__)."/controller.php?action=conf";
		$template->output('continua.htm');
	}else{
		$template->errore = $array[1];
		get_form($elements,'module_analytics','conf_ok',$array);
		$template->output_module(basename(__DIR__),'analytics_conf.htm',$elements);
	}

}elseif( $action == 'widget_visits' ){
	
	$gapi = _obj('Analytics');
	
	if( is_object($gapi) && !$gapi->no_key){
		$visitors_today = $gapi->getVisitorsByDate(date('Y-m-d')); 
		

		
		$visitors_yesterday = $gapi->getVisitorsByDate(date('Y-m-d',strtotime("-1 days"))); 
		

		$total_visits_today = (int)($visitors_today['returning']+$visitors_today['new']);
		$total_visits_yesterday = (int)($visitors_yesterday['returning']+$visitors_yesterday['new']);
		
		$perc = ($visitors_today['new']/($visitors_today['returning']+$visitors_today['new']))*100;
		$perc_new_visits_today = number_format($perc,1,'.','.');
		$perc = ($visitors_visitors_yesterday['new']/($visitors_yesterday['returning']+$visitors_yesterday['new']))*100;
		$perc_new_visits_yesterday = number_format($perc,1,'.','.');
		$bounce_rate_today =  number_format($gapi->getBounceRateByDate(date('Y-m-d')),1,'.','.');
		$bounce_rate_yesterday = number_format($gapi->getBounceRateByDate(date('Y-m-d',strtotime("-1 days"))),1,'.','.');

	}


	$risposta = array(
		'result' => 'ok',
		'content'=> $html,
		'total_visits_today' => $total_visits_today,
		'total_visits_yesterday' => $total_visits_yesterday,
		'perc_new_visits_today' => $perc_new_visits_today,
		'perc_new_visits_yesterday' => $perc_new_visits_yesterday,
		'bounce_rate_today' => $bounce_rate_today,
		'bounce_rate_yesterday' => $bounce_rate_yesterday,

		);
	echo json_encode($risposta);
	exit; 

}elseif( $action == 'widget_statistic' ){
	
		$gapi = _obj('Analytics');
	

	/************************************ BROWSER UTILIZZATI **********************************************/
	$browser = $gapi->getMainsBrowserByYear(date('Y'));
	
	//browser principali
	$browser_principali = array('Firefox','Safari','Chrome','Internet Explorer');
	$tot = 0;
	$tot_other = 0;
	foreach($browser as $k => $v){
		if( !in_array($k,$browser_principali)){
			$tot_other += $v['visits'];
			$tot += $v['visits'];
		}else{
			$tot += $v['visits'];
		}
		
	}
	
	foreach($browser as $k => $v){
		if( in_array($k,$browser_principali)){
			$browser_statistics[$k] = number_format(($v['visits']/$tot)*100,1,'.','.');
		}else{
			//$browser_statistics['altri'] = number_format(($tot_other/$tot)*100,1,'.','.');
		}
		
	}
	/************************************ BROWSER UTILIZZATI **********************************************/

	/************************************ VISITATORI ANNO CORRENTE DISTRIBUITI PER MESE**********************************************/
	

	$visitors = $gapi->getVisitorsByYear(date('Y')); 
	//debugga($visitors);exit;
	foreach($visitors as $k => $v){
		$visitors_new[$k-1] = $v['new'];
		$visitors_returning[$k-1] = $v['returning'];
	}
	
	/************************************ VISITATORI ANNO CORRENTE DISTRIBUITI PER MESE**********************************************/

	
	ob_start();
	$template->output_module(basename(__DIR__),'widget_statistic_content.htm');
	$html = ob_get_contents();
	ob_end_clean();
	
	$risposta = array(
		'result' => 'ok',
		'content'=> $html,
		'browsers' => $browser_statistics,
		'visitors_new' => $visitors_new,
		'visitors_returning' => $visitors_returning
		);
	echo json_encode($risposta);
	exit; 

}


?>