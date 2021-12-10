function save_address(){
	$(".overlay").toggleClass("actives");
	$('#error_cart_address').html('').hide();
	var formdata = $('#new_address_form').serialize();
	$('input').each(function(){
		$(this).removeClass('field_required');
	});
	$('select').each(function(){
		$(this).removeClass('field_required');
	});
	
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "cart_new_address_ok",formdata :formdata,ctrl:'Cart',mod:'cart_onepage',ajax:1},
		  dataType: "json",
		  success: function(data){
				$('input').removeClass('field_required');
				$(".overlay").toggleClass("actives");
				if(data.result == 'ok'){
					document.location.href=data.url;
				}else{
					$('#'+data.field).addClass('field_required');
					$('html,body').animate({
					 scrollTop: $('#'+data.field).offset().top -100
				    },'slow');
					$('#error_cart_address').html(data.error).show();
					
					//MarionAlert(js_error_title_alert,data.error);
				}
		  },
		 
	});
}

function step3(){
	$(".overlay").toggleClass("actives");
	if( $('#address_form').length > 0 ){
		var formdata = $('#address_form').serialize();
	}else{
		
		var formdata = $('#new_address_form').serialize();
		$('input').each(function(){
			$(this).removeClass('field_required');
		});
		$('select').each(function(){
			$(this).removeClass('field_required');
		});
	}
	$.ajax({
	  type: "POST",
	  url: "index.php",
	  data: { action: "cart_address_ok",formdata :formdata,ctrl:'Cart',mod:'cart_onepage',ajax:1},
	  dataType: "json",
	  success: function(data){
			$('input').removeClass('field_required');
			if(data.result == 'ok'){
				document.location.href=data.url;
			}else{
				$(".overlay").toggleClass("actives");
				$('#'+data.field).addClass('field_required');
				
				MarionAlert(js_error_title_alert,data.error);
			}
	  },
	 
	});
	

}

$(document).ready(function(){
	if( $('#country').length > 0 ){
		$('#country').on('change',function(){
			if( $(this).val() == 'IT' ){
				$('#div_province').show();
			}else{
				$('#div_province').hide();
				$('#province').val(0);
			}
		});
		$('#country').trigger('change');
	}
});


function confirmDeleteAddress(){
	$('#address_actions').hide();
	$('#confirm_delete_address').show();
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

function cancelDeleteAddress(){
	$('#address_actions').show();
	$('#confirm_delete_address').hide();
}

function deleteAddress(id){
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
}
