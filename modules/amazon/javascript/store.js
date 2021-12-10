var cont_corrieri_amazon = 0;

function add_corriere(){
	cont_corrieri_amazon = cont_corrieri_amazon+1;
	var t = $('#modello_corriere').clone().appendTo('#cont_corrieri').attr('id','corriere_amazon_'+cont_corrieri_amazon).show();

	$('#select_corrieri_amazon').clone().show().attr('id','corriere_amazon_'+cont_corrieri_amazon).attr('name',"formdata[carrier]["+cont_corrieri_amazon+"][id_amazon]").appendTo('#corriere_amazon_'+cont_corrieri_amazon+" #amazon");
	$('#select_corrieri_marion').clone().show().attr('id','corriere_marion_'+cont_corrieri_amazon).attr('name',"formdata[carrier]["+cont_corrieri_amazon+"][id_marion]").appendTo('#corriere_amazon_'+cont_corrieri_amazon+" #marion");
	
	



}


var cont_corrieri_amazon_exit = 0;

function add_corriere_exit(){
	cont_corrieri_amazon_exit = cont_corrieri_amazon_exit+1;
	var t = $('#modello_corriere_exit').clone().appendTo('#cont_corrieri_exit').attr('id','corriere_amazon_exit_'+cont_corrieri_amazon_exit).show();

	$('#select_corrieri_amazon_exit').clone().show().attr('id','corriere_amazon_exit_'+cont_corrieri_amazon_exit).attr('name',"formdata[carrier_exit]["+cont_corrieri_amazon_exit+"][id_amazon]").appendTo('#corriere_amazon_exit_'+cont_corrieri_amazon_exit+" #amazon");
	$('#select_corrieri_marion').clone().show().attr('id','corriere_amazon_exit_'+cont_corrieri_amazon_exit).attr('name',"formdata[carrier_exit]["+cont_corrieri_amazon_exit+"][id_marion]").appendTo('#corriere_amazon_exit_'+cont_corrieri_amazon_exit+" #marion");
	
	

	$('#select_markets').clone().show().attr('id','corriere_amazon_exit_'+cont_corrieri_amazon_exit).attr('name',"formdata[carrier_exit]["+cont_corrieri_amazon_exit+"][market]").appendTo('#corriere_amazon_exit_'+cont_corrieri_amazon_exit+" #market");
	



}



$(document).ready(function(){
	if( typeof js_cont_map_corrieri != 'undefined' && js_cont_map_corrieri != null ){
		
		cont_corrieri_amazon = parseInt(js_cont_map_corrieri);
	}
	if( typeof js_cont_map_corrieri_exit != 'undefined' && js_cont_map_corrieri_exit != null ){
		cont_corrieri_amazon_exit = parseInt(js_cont_map_corrieri_exit);
	}
});
