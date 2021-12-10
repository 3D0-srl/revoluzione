$(document).ready(function(){
	$('.paymentData').on('change',function(){
		var val = $(this).val();
		$('.description_payment').hide();
		$('#description_'+val).show();
		if( val == 'STRIPE'){
			$('#stripe').show();
		}else{
			$('#stripe').hide();
		}

		if( $('#price_'+val).val() ){
			var prezzo_pagamento = $('#price_'+val).val();
		}else{
			var prezzo_pagamento = 0;
		}	

		$('#pricePayment').val(parseFloat(prezzo_pagamento));
		var prezzo_pagamento_formattato = (parseFloat(prezzo_pagamento)).formatMoney(2, ',', '');
		$('#pricePaymentFormatted').html(prezzo_pagamento_formattato);
		totalCart();
		
	});
	if( typeof js_paymentMethod != 'undefined' && js_paymentMethod != null ){
		$('#paymentMethod_'+js_paymentMethod).trigger('click');
	}
});



var flag_step5 = true;
function step5(){
	$('#error_payment').html('').hide();
	
	$(".overlay").toggleClass("actives");
	$('#message_loader').hide();
	if( !flag_step5 ) return;
	var formdata = $('#payment').serialize();
	
	$('input').each(function(){
		$(this).removeClass('field_required');
	});
	$('select').each(function(){
		$(this).removeClass('field_required');
	});
	flag_step5 = false;
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "cart_payment_ok",formdata :formdata,ctrl:'Cart',mod:'cart_onepage',ajax:1},
		  dataType: "json",
		  success: function(data){
				flag_step5 = true;
				$('#message_loader').show();
				$('#btn-avanti-cart').removeAttr('disbaled');
				if(data.result == 'ok'){
					document.location.href=data.url;
				}else{
					$(".overlay").toggleClass("actives");
					
					$('#'+data.field).addClass('field_required');
					
					$('#error_payment').html(data.error).show();
					//MarionAlert(js_error_title_alert,data.error);
				}
		  },
		 
	});
}
