function paypal_rapid_cash(){
	 $('.btn-paypal').addClass('loading-addcart');
	var formdata = $('#addCart').serialize();
	
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ajax: 1, ctrl:'Ajax',mod:'paypal',action: "product",formdata : formdata},
		  dataType: "json",
		  success: function(data){
				
		    	if(data.result == 'ok'){
					$('.overlay-paypal').show();
					setTimeout(function(){
						

						document.location.href=data.url;
					},1000);
					
			    }else{
					 $('.btn-paypal').removeClass('loading-addcart');
					showErrorAddToCart(data.error);
					
			    	
		    	}
		  },
		 
		});

}


function paypal_rapid_cash_checkout(){
	$('.overlay-paypal').show();
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ajax: 1, ctrl:'Ajax',mod:'paypal',action: "checkout"},
		  dataType: "json",
		  success: function(data){
				
		    	if(data.result == 'ok'){
					
					setTimeout(function(){
						

						document.location.href='index.php?mod=paypal&action=checkout';
					},1000);
					
			    }else{
					$('.overlay-paypal').hide();
					
					MarionAlert(js_error_title_alert,data.error);
					
			    	
		    	}
		  },
		 
		});
}

