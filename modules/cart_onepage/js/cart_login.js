  
function login_cart(){
	$('#error_login').html('').hide();
	$('.field_required').removeClass('field_required');
	var formdata = $('#cart_login').serialize();
	$.ajax({
		  type: "POST",
		  url: "index.php",
		  data: { action: "login",formdata :formdata,ajax:1,ctrl:'Access'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					//console.log('qua');
					document.location.href="index.php?mod=cart_onepage&ctrl=Cart&action=redirect";
				}else{
					$('#'+data.field).addClass('field_required');
					$('#error_login').html(data.error).show();
					//MarionAlert(js_error_title_alert,data.error);
				}
		  },
		 
	});
}

function no_registration_cart(){
	$('#error_no_registration').html('').hide();
	$('.field_required').removeClass('field_required');
	var formdata = $('#no_registration').serialize();
	$.ajax({
		  type: "POST",
		  url: "index.php",
		  data: { action: "no_registration",formdata :formdata,ajax:1,ctrl:'Cart',mod:"cart_onepage"},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					//console.log('qua');
					document.location.href="index.php?mod=cart_onepage&ctrl=Cart&action=cart_datauser";
				}else{
					$('#'+data.field).addClass('field_required');
					$('#error_no_registration').html(data.error).show();
					//MarionAlert(js_error_title_alert,data.error);
				}
		  },
		 
	});
}