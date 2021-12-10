$(document).ready(function(){
	if( $('#shippingCountry').length > 0 ){
		$('#shippingCountry').on('change',function(){
			if( $(this).val() == 'IT' ){
				$('#div_province').show();
			}else{
				$('#div_province').hide();
				$('#shippingProvince').val(0);
			}

			
		});
		$('#shippingCountry').trigger('change');
	}
});


function getPriceShipping(country,id_order){
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "get_price_shipping",country :country,ctrl:'Index',mod:'paypal',ajax:1,id_order:id_order},
		  dataType: "json",
		  success: function(data){
				
				if(data.result == 'ok'){
					$('#priceShipping').val(data.price);
					var price_formatted = (data.price).formatMoney(2, ',', '');
					
					$('#priceShippingFormatted').html(price_formatted);
					totalCart();
					//document.location.href=data.url;
				}else{
				
				}
		  },
		 
	});
}


