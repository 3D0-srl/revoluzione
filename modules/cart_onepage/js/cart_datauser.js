
function step2(){
	$('#error_cart_datauser').html('').hide();
	$(".overlay").toggleClass("actives");
	var formdata = $('#buy').serialize();
	$('input').each(function(){
		$(this).removeClass('field_required');
	});
	$('select').each(function(){
		$(this).removeClass('field_required');
	});
	$.ajax({
		  type: "POST",
		  url: "index.php",
		  data: { action: "cart_datauser_ok",formdata :formdata,ctrl:'Cart',mod:'cart_onepage',ajax:1},
		  dataType: "json",
		  success: function(data){
				$('input').removeClass('field_required');
				if(data.result == 'ok'){
					document.location.href=data.url;
				}else{
					$(".overlay").toggleClass("actives");
					
					for( var field in data.fields ){
						$('#'+data.fields[field]).addClass('field_required');
						$('html,body').animate({
							scrollTop: $('#'+data.fields[field]).offset().top -100
						  },'slow');
					}
					$('#error_cart_datauser').html(data.error).show();
					//MarionAlert(js_error_title_alert,data.error);
				}
		  },
		 
	});
}

$(document).ready(function(){
	$('#registration').on('change',function(){
		
		if( $(this).prop('checked') == true ){
			$('.registration_fields').show();
		}else{
			$('.registration_fields').hide();
		}
	});	

	$('#requestInvoice').on('change',function(){
		if( $(this).prop('checked') == true ){
			$('.company_data').show();

		}else{
			$('.company_data').hide();
		}
		
	});	

	$('#country').on('change',function(){
		if( $(this).val() == 'IT' ){
			$('#div_province').show();
		}else{
			$('#div_province').hide();
			$('#province').val(0);
		}
	});
	$('#country').trigger('change');
	
})