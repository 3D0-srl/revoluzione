function change_status(val){
		$('.status_cart_button').html('');
		$('#status_cart_'+val).html('</span><i class="fa fa-check"></i>');
		
		if( js_status_email[val] == 1){
			$('#send_mail_message').show();
		}else{
			$('#send_mail_message').hide();
		}
		if( js_status_sent[val] == 1){
			$('#div_trackingCode').fadeIn();
		}else{
			$('#div_trackingCode').fadeOut();
		}
}

function modify_cart_user(id){
	if( $('#manage_order_user').hasClass('btn-primary') ){
		$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "manage_order_user", id: id, type: 'enabled',ajax:1,ctrl:"OrderAdmin",mod:'ecommerce'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					$('#manage_order_user').removeClass('btn-primary').addClass('btn-warning').html("<i class='fa fa-check'></i> Disabilita modalità utente");	
				}else{
					//notify(data.errore,'error');
				}
		  },
		  
		});
		
	}else{
		$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "manage_order_user", id: id, type: 'disabled',ajax:1,ctrl:"OrderAdmin",mod:'ecommerce'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					$('#manage_order_user').removeClass('btn-warning').addClass('btn-primary').html("Abilita modalità utente");		
				}else{
					//notify(data.errore,'error');
				}
		  },
		  
		});
		
	}
}

function changeStatusOrder(){
	var t = confirm("Sicuro di voler cambiare lo stato attuale dell'ordine?");
	if( t ){
		 var formdata = $('#change_status_order').serialize();
	
			$.ajax({
			  type: "GET",
			  url: "../index.php",
			  data: { action: "changeStatusOrder", formdata: formdata,ajax:1,ctrl:"Orders",mod:'ecommerce'},
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
						//alert('Stato aggiornato con successo');
						//MarionAlert('Operazione eseguita','Stato aggiornato con successo');
						//notify('Stato aggiornato con successo','success');
						update_history(data.id);

					}else{
						alert(data.errore);
						//notify(data.errore,'error');
					}
			  },
			 
			});
	}
	
}

function updateTrackingCode(){
	var t = confirm("Sicuro di voler aggiornare il codice di tracking?");
	if( t ){
		 var formdata = $('#tracking_code_form').serialize();
	
			$.ajax({
			  type: "GET",
			  url: "index.php",
			  data: { action: "update_tracking", formdata: formdata,ajax:1,ctrl:"OrderAdmin",mod:'ecommerce'},
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
						
						alert('Codice di tracking aggiornato con successo');

					}else{
						alert(data.errore);
						//notify(data.errore,'error');
					}
			  },
			 
			});
	}
	
}

function update_history(id){
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "update_history", id: id,ajax:1,ctrl:"OrderAdmin",mod:'ecommerce'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					
					$('#history').html(data.history);
					$('#dateShipping_cart').html(data.dateShipping);
					$('#datePayment_cart').html(data.datePayment);
					$('#status_cart').html(data.status);
					$('.status_cart_text').each(function(){
						$(this).html(data.status);	
					})
					alert('Stato aggiornato con successo');

				}
		  },
		 
		});
}


function send_mail_buyer(){
	var formdata = $('#form_email_buyer').serialize();
	
	$.ajax({
	  type: "POST",
	  url: "../index.php",
	  data: { action: "send_mail_customer", formdata: formdata,ajax:1,ctrl:"Orders",mod:'ecommerce'},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				alert('Email inviata con successo');
				//notify('Email inviata con successo','success');
			}else{
				alert(data.errore);
				//notify(data.errore,'error');
			}
	  },
	 
	});

}

function submit_bulk_action_orders(url){
	
	var fd = {};
	var cont = 0;
	var check = false;
	$('.check_action_bulk').each(function(i){
		if( $(this).prop('checked') ){
			check = true;
			fd[cont] = $(this).val();
			cont = cont+1;
		}
	})
	if( !check ){
		alert('Nessun ordine selezionato');
	}else{
		document.location.href=url+"&id="+JSON.stringify(fd);
	}
	

}


$(document).ready(function(){
	$('#check_action_bulk').on('change',function(){
		$('.check_action_bulk').prop('checked',$(this).prop('checked'));
	});	
	
	if( $('#status').length > 0 ){
		
		$('#status').find('option').each(function(index,value){
			if( js_status_color[value.value] ){
				$(this).attr('data-content',"<span class='label' style='background:"+js_status_color[value.value]+"'>"+js_status_name[value.value]+"</span>");
			}
		});

		$('#status').selectpicker('refresh');
		
	}

	if( $('#new_status').length > 0 ){
		$('#new_status').find('option').each(function(index,value){
			if( js_status_color[value.value] ){
				$(this).attr('data-content',"<span class='label' style='background:"+js_status_color[value.value]+"'>"+js_status_name[value.value]+"</span>");
			}
		});

		$('#status').selectpicker('refresh');
		
	}

	$(".s-tabi").click(function(){
		var lui = $(this);
		$(".s-tabi").removeClass("active");
		lui.addClass("active");
	});

});




