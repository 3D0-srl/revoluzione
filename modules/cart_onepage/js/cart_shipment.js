function step4(){
	$(".overlay").toggleClass("actives");
	var formdata = $('#shippingMethod_form').serialize();
	$('.field_required').removeClass('field_required');
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "cart_shipment_ok",formdata :formdata,ctrl:'Cart',mod:'cart_onepage',ajax:1},
		  dataType: "json",
		  success: function(data){
				
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
	
	
	$('.shipping_method').on('change',function(){
		var price= parseFloat($(this).attr('price'));
		$('#priceShipping').val(price);
		$('#priceShippingFormatted').html((price).formatMoney(2, ',', ''));
		totalCart();
	});

})


