function new_address(id){
	document.location.href= "addresses/"+id;
}



function deleteAddress(id){
	//MarionConfirm(js_confirm_title_alert,js_confirm_text_alert,function(){
		$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "delete",id :id, ctrl:'Address',mod:'ecommerce',ajax:1},
		  dataType: "json",
		  success: function(data){
				
				if(data.result == 'ok'){
					document.location.href="addresses";
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  },
		 
		});
		
	//});
	
}


function confirmDeleteAddress(el){
	$('.address_actions').show();
	$('.confirm_delete_address').hide();
	el.closest('.box_btn').find('.address_actions').hide();
	el.closest('.box_btn').find('.confirm_delete_address').show();
	/*MarionConfirm(js_confirm_delete_address_title,js_confirm_delete_address_message,function(){
		//var formdata = $('#shippingMethod_form').serialize();
		$(".overlay").toggleClass("actives");
		$.ajax({
			  type: "GET",
			  url: "index.php",
			  data: { action: "del_address",id :id,ctrl:'Cart',mod:'cart_onepage',ajax:1},
			  dataType: "json",
			  success: function(data){
					$('input').removeClass('field_required');
					if(data.result == 'ok'){
						document.location.href='index.php?ctrl=Cart&mod=cart_onepage&action=cart_address';
					}else{
						$(".overlay").toggleClass("actives");
						$('#'+data.field).addClass('field_required');
						
						MarionAlert(js_error_title_alert,data.error);
					}
			  },
			 
		});
	});*/
	
}

function cancelDeleteAddress(el){
	el.closest('.box_btn').find('.address_actions').show();
	el.closest('.box_btn').find('.confirm_delete_address').hide();
}





function new_address_ok(){
	$('#error').html('').hide();
	$('.field_required').removeClass('field_required');

	var formdata = $('#new_address_form').serialize();
	
	
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ctrl:'Address',mod:'ecommerce',action: "save",formdata:formdata,ajax:1},
		  dataType: "json",
		  success: function(data){
				
				if(data.result == 'ok'){
					document.location.href='addresses'; 
				}else{
					$('#'+data.field).addClass('field_required');
					$('#error').html(data.error).show();
					//MarionAlert(js_error_title_alert,data.error);
				}
		  },
		 
	});
}