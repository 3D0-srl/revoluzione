function confirm_address(token,PayerID,id_order){
	$('#error').html('').hide();
	$('.field_required').removeClass('field_required');
	var formdata = $('#confirm_address').serialize();
	$.ajax({
		  type: "POST",
		  url: "index.php",
		  data: { action: "confirm_address",ctrl:'Index',mod:'paypal',ajax:1,token:token,PayerID,PayerID,formdata:formdata,id_cart:id_order},
		  dataType: "json",
		  success: function(data){
				
				if(data.result == 'ok'){
					document.location.href=data.url;
				}else{
					$('#'+data.field).addClass('field_required');
					$('#error').html(data.error).show();
				}
		  },
		 
		});
}

function process(token,PayerID,id_order){
	$('#error').html('').hide();
	$('.field_required').removeClass('field_required');
	var register = 0;
	if ( $('#registration').prop('checked') == true ){
		register = 1
	}
	var formdata = $('#paypal_register').serialize();
	
	$.ajax({
	  type: "POST",
	  url: "index.php",
	  data: { action: "confirm_order",ctrl:'Index',mod:'paypal',ajax:1,token:token,PayerID,PayerID,formdata:formdata,id_cart:id_order,register:register},
	  dataType: "json",
	  success: function(data){
			
			if(data.result == 'ok'){
				document.location.href=data.url;
			}else{
				$('#'+data.field).addClass('field_required');
				$('#error').html(data.error).show();
			}
	  },
	 
	});
	//document.location.href='index.php?mod=paypal&ctrl=PaypalBackend&id_order='+id_order+'&token='+token+"&PayerID="+PayerID;
}