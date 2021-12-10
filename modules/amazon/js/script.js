function asin_check(){
	$('#box_result').show();
	$('#result_data').html('');
	$('#message_loader').show();


	$.ajax({
	  type: "GET",
		  url: "index.php",
		  cache: false,
		  dataType: "json",
		  data: {action:'check'},
		  success: function(data){
				$('#message_loader').hide();
				if(data.result == 'ok'){
					
					$('#result_data').html(data.data);
					
				}else{
					//MarionAlert('Attensione',data.errore);
				}
		  },
		 
	});
}


function new_products(){
	

	show_amazon_loader();
	$('#box_result').hide();
	$('#message_loader').show();
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'new_products'},
		  success: function(data){
				hide_amazon_loader();
				if(data.result == 'ok'){
					
					$('#box_result').show();
					$('#message_loader').hide();
					$('#result_data').html(data.data);
						
					
				}else{
					
				}
		  },
		 
	});
}




function delete_upload(id){

	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'delete_upload',id:id},
		  success: function(data){
				
				if(data.result == 'ok'){
					
					send_products(id);
					
				}else{
					
				}
		  },
		 
	});

}

function get_feeds(id,market){
	$('#account_'+id+"_"+market).html("<div class='row'><div class='col-md-12'><p>Caricamento dati in corso......</p></div></div>");
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'list_feeds_market',id:id,market:market},
		  success: function(data){
				
				if(data.result == 'ok'){
					
					$('#account_'+id+"_"+market).html(data.data);
					
				}else{
					
				}
		  },
		 
	});
}


function show_amazon_loader(){
	$('#overlay').show();
	$('.spinner').show();
}

function hide_amazon_loader(){
	$('#overlay').hide();
	$('.spinner').hide();
}

function get_orders(){

	show_amazon_loader();
	$('#box_result').hide();
	$('#message_loader').show();
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'orders'},
		  success: function(data){
				hide_amazon_loader();
				if(data.result == 'ok'){
					
					$('#box_result').show();
					$('#message_loader').hide();
					$('#result_data').html(data.data);
						
					
				}else{
					
				}
		  },
		 
	});

}

function check_upload(id){
	
	$('#box_result').hide();
	show_amazon_loader();
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'check_upload',id:id},
		  success: function(data){
				hide_amazon_loader();
				if(data.result == 'ok'){
					
					/*if( data.upload ){
						MarionConfirm("Attenzione!","E' presente gia' un processo di sincronizzazione.<br>Vuoi riprenderlo?",function(res){
							$('#dismiss_modal_account').click();
							if( res == true ){
								//nuova
								delete_upload(id);
							}else{
								//precedente
								
								send_products(id);
							}
							swal.close();
						},'NUOVA SINCRONIZZAZIONE',"RIPRENDI SINCRONIZZAZIONE");
					}else{*/
						$('#result_data').html(data.html);
						$('#box_result').show();
						/*MarionConfirm("Conferma Operazione","Sicuro di voler avviare la procedura?",function(){
							$('#dismiss_modal_account').click();
							send_products(id);
							swal.close()
						})*/

					//}
					
				}else{
					
				}
		  },
		 
	});
}

function confirm_update_inventory(id){
	
	$('#box_result').hide();
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'update_inventory',id:id},
		  success: function(data){
				
				if(data.result == 'ok'){
					
					
						$('#result_data').html(data.html);
						$('#box_result').show();
						
					
				}else{
					
				}
		  },
		 
	});
}


function update_inventory(){
		show_amazon_loader();
		$.ajax({
		  type: "GET",
			  url: "index.php",
			  dataType: "json",
			  data: {action:'send_single'},
			  success: function(data){
					
					if(data.result == 'ok'){
						$('#import_ok').show();
						//swal.close();
					}else{
						//MarionAlert('Attensione',data.errore);
					}
					hide_amazon_loader();
			  },
			 
		});
		 
		 //document.location.href="index.php?action=send_single";
	
}

function send_products(id,status_old=false){

	$('.hover_bkgr_fricc').show();
	$('.content_popup_loader .btn_close').hide();
	$('#btn_close').hide();
	$('.sk-circle').show();
	//$('#box_result').show();
	if( !status_old ){
		//$('#result_data').html('<ol id="importa_catalogo"></ol>');
		//$('#message_loader').show();
	}

	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'send',id:id},
		  success: function(data){
				$('#message_loader').hide();
				if(data.result == 'ok'){
					if( data.fine ){
						$('.sk-circle').hide();
						$('#image_popup').attr('src',data.image);
						$('#other_info').hide();
						$('#btn_close').show();
						//$('.hover_bkgr_fricc').hide();
						//return false;
					}
					if( !status_old || data.status  != status_old ){
						if( data.message ){
							$('#message_popup').html(data.message);
							$('#image_popup').attr('src',data.image);
						}
					}
					if( !data.fine ){
						setTimeout(function(){
							send_products(id,data.status)
						},parseFloat(data.delay));
					}
					
					
				}else{
					//MarionAlert('Attensione',data.errore);
				}
		  },
		 
	});
}


function select_all_orders_amazon(val){
	
	if( val ){
		$('.amazon_order_check').prop('checked',true);
	}else{
		$('.amazon_order_check').prop('checked',false);
	}
}


function import_orders(order){
	var arr = [];
	if( !order ){
		
		$('.amazon_order_check').each(function(){
			arr.push($(this).val());
		});
	}else{
		arr.push(order);
	}
	var ser = JSON.stringify(arr);
	
	
	
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'import_orders',orders:ser},
		  success: function(data){
				
				if(data.result == 'ok'){

					for( var k in data.success){
						$('#order_tr_'+data.success[k]).addClass('success');
					}

					for( var k in data.error){
						$('#order_tr_'+data.error[k]).addClass('danger');
						var html = "";
						for( var k2 in data.error_messages[k] ){
							html = html + data.error_messages[k][k2]+"<br>";
						}
						$('#error_tr_'+data.error[k]).find('#errors').html(html);
						$('#error_tr_'+data.error[k]).show();
					}
					
					
				}else{
					
				}
		  },
		 
	});

}

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
		cont_corrieri_amazon = js_cont_map_corrieri
	}
	if( typeof js_cont_map_corrieri_exit != 'undefined' && js_cont_map_corrieri_exit != null ){
		cont_corrieri_amazon_exit = js_cont_map_corrieri_exit
	}
});



function elimina_profilo(id){
	MarionConfirm('Conferma operazione','Sicuro di volere eliminare questo profilo?',function(){
		 document.location.href="index.php?action=del_profile&id="+id;
	});
	//var t = confirm('Sicuro di volere leiminare questa categoria?');
	//if(t) document.location.href="/admin/admin.php?action=del_userCategory&id="+id;
}

function elimina_store(id){
	MarionConfirm('Conferma operazione','Sicuro di volere eliminare questa store?',function(){
		 document.location.href="index.php?action=del_store&id="+id;
	});
	//var t = confirm('Sicuro di volere leiminare questa categoria?');
	//if(t) document.location.href="/admin/admin.php?action=del_userCategory&id="+id;
}


var current_xhr;
function get_asin(id,market){
	 
	 $('#box_percentuale_asin').show();
	 
	 $('.start_get_asin').hide();
	 
	 $('#refresh_'+id+'_'+market).hide();
	 $('#stop_'+id+'_'+market).show();
	 

	 $('#div_progress_'+id+'_'+market).show();

	 
	 
	 var progressBar = document.getElementById("progress_"+id+'_'+market);

	 progressBar.value = parseFloat(0);
	 progressBar.style.width = "0%";
	 $('#progress').show();
	 var xhr = new XMLHttpRequest();
	 xhr.open("POST", "index.php", true);
	 //xhr.open("POST", "/modules/amazon/index.php?action=get_asin&id="+id+"&market="+market, true);
	 //xhr.setRequestHeader("Content-type", "application/json");
	 xhr.previous_text = ''
	 xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

	 /* xhr.upload.addEventListener("progress", function (evt) {
           console.log(evt);
        }, false);
	  xhr.addEventListener("progress", function (evt) {
           console.log(evt);
        }, false);*/
	 xhr.onreadystatechange = function() {
			console.log(xhr.responseText);
			var new_response = xhr.responseText.substring(xhr.previous_text.length);
			if( new_response != 'undefined' && new_response != null  && new_response.trim() ){
				try{
					//console.log(new_response);	
					if( new_response == 'ok'){
						progressBar.value = 100;
						$('#percentuale').html('100');
						$('.start_get_asin').show();
						$('#refresh_'+id+'_'+market).show();
						$('#stop_'+id+'_'+market).hide();
						document.getElementById("progress_"+id+'_'+market).style.width = "100%";
					}else{
						var res = new_response.split("||");
						//var prec = parseInt($('#asin_found_'+id+'_'+market).html());
						//if( prec < parseInt(res[1]) ){
							$('#asin_found_'+id+'_'+market).html(res[1]);
						//}
						if( parseFloat(res[0]) <= 100 ){
							progressBar.value = parseFloat(res[0]);
							$('#percentuale').html(res[0]);
							document.getElementById("progress_"+id+'_'+market).style.width = parseFloat(res[0]) + "%";
						}
						
						
					}
				}
				catch (err) {
					console.log(err);
				}
				
			}
			//console.log(new_response);
			xhr.previous_text = xhr.responseText;
		};
	
	  /*if (xhr.upload) {
		xhr.upload.onprogress = function(e) {
		  console.log(e);
		  if (e.lengthComputable) {
			progressBar.max = e.total;
			progressBar.value = e.loaded;
			//display.innerText = Math.floor((e.loaded / e.total) * 100) + '%';
		  }
		}
		xhr.upload.onloadstart = function(e) {
		  progressBar.value = 0;
		  display.innerText = '0%';
		}
		xhr.upload.onloadend = function(e) {
		  progressBar.value = e.loaded;
		  //loadBtn.disabled = false;
		  //loadBtn.innerHTML = 'Start uploading';
		}
	  }
	  console.log(xhr);*/

	  /*form_data = new FormData();
			
	  form_data.append('id', id);
	  form_data.append('market', market);
	  form_data.append('action', 'get_asin' );
	  console.log(form_data);
	  var data = JSON.stringify(form_data);
	  //console.log(form_data.serialize())*/

	  var params = 'action=get_asin&id='+id+"&market="+market;
	  xhr.send(params);
	  current_xhr = xhr;


	
}

function abort_xhr(id,market){
	current_xhr.abort();
	$('.start_get_asin').show();
	 
	$('#refresh_'+id+'_'+market).show();
	$('#stop_'+id+'_'+market).hide();
	 
}



var market = '';
var id_profile;
$(document).ready(function(){
	
	id_profile = $('#id').val();
	if( $('.markeplace_profile').length > 0 ){
		$('.markeplace_profile').on('change',function(){
			new_market = $(this).val();

			if( $(this).prop('checked') ){
				if( market && new_market!=market){ 
					MarionConfirm('Conferma operazione',"Prima di passare ad un altro marketplace assicurati di aver salvato la configurazione. Procedere?",function(t){
						if( t ){
							market = new_market;
							change_profile_market(market,id_profile);
						}else{
							$('#market_'+market).prop('checked',true);
						}
						swal.close();
					
					});
				}else{

					market = new_market;
					change_profile_market(market,id_profile);

				}
			}
			
		});
	}
	
		
});
function change_amazon_category(val,id){
	$.ajax({
		  type: "GET",
		  url: "ajax.php",
		  data: { action: "get_category_form", category: val,id:id, market:market},
		  dataType: "json",
		  cache: false,
		  success: function(data){
				if(data.result == 'ok'){
					$('#profile_conf').html(data.html);
					
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});

}


function change_profile_market(val,id){
	$.ajax({
		  type: "GET",
		  url: "ajax.php",
		  data: { action: "get_profile_market", market: val,id:id},
		  dataType: "json",
		  cache: false,
		  success: function(data){
				if(data.result == 'ok'){
					$('.edit_profile').show();
					$('#category').val(data.category).selectpicker('refresh');

					change_amazon_category(data.category,id);
					console.log($('#category'));
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}



function save_profile(){
	var formdata = $('#profile').serialize();
	$('.errore').removeClass('errore');
	$.ajax({
		  type: "POST",
		  url: "ajax.php",
		  data: { action: "save_profile", formdata: formdata},
		  dataType: "json",
		  cache: false,
		  success: function(data){
				if(data.result == 'ok'){
					$('#id').val(data.id);
					MarionAlert('Salavtaggio riuscito!',"I dati sono stati salvati con successo per il marketplace selezionato.");
				}else{
					MarionAlert(js_error_title_alert,data.error);
					$('#field_'+data.campo).addClass('errore');
				}
		  }
		 
	});

}



function salva_configurazione(id){
	var formdata = $('#mapping').serialize();

	$.ajax({
		  type: "POST",
		  url: "index.php",
		  data: { action: "save_mapping", formdata: formdata,id:id},
		  dataType: "json",
		  cache: false,
		  success: function(data){
				if(data.result == 'ok'){
					MarionAlert('Salavtaggio riuscito!',"I dati della mappatura sono stati salvati con successo.");
				}else{
					MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}
	

function new_product_account(el,id){
	$('.account_amazon').removeClass('active');
	el.addClass('active');
	$('.box_store').hide();
	$('#box_store_'+id).show();
}


function new_product_market(el,id_store,market){
	$('.account_market_amazon').removeClass('active');
	el.addClass('active');


	show_amazon_loader();
	

	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'get_new_products',store:id_store,market:market},
		  success: function(data){
				hide_amazon_loader();
				if(data.result == 'ok'){
					$('#result_data').html(data.data);
					
						
					
				}else{
					
				}
		  },
		 
	});
	
}

function import_new_product_market(id_store,market){
	

	show_amazon_loader();
	

	$.ajax({
	  type: "GET",
		  url: "index.php",
		  dataType: "json",
		  data: {action:'import_new_products',store:id_store,market:market},
		  success: function(data){
				hide_amazon_loader();
				if(data.result == 'ok'){
					//$('#result_data').html(data.data);
					$('#import_ok').show();
						
					
				}else{
					
				}
		  },
		 
	});
	
}
