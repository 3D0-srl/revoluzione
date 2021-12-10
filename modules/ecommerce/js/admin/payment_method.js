function change_visibility(id){
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  data: { ctrl: "PaymentMethodAdmin",action:'change_visibility','id':id,'ajax':1,mod:'ecommerce'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					
							
					var el = $('#status_'+id);
					if( data.status ){
						el.removeClass('label-danger').addClass('label-success').html(data.text);
					}else{
						el.removeClass('label-success').addClass('label-danger').html(data.text);
					}
			
				}else{
					
				}
		  },
	 
	});
}

