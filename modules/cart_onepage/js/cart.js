var cart_updated = false;


function cart_changed(){
	$('.update-quantity').show();
	$('.preview_cart_buttons').hide();
	
}


function update_cart(){
	$(".overlay").toggleClass("actives");
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "update_cart",ctrl:'Cart',mod:'cart_onepage',ajax:1},
		  dataType: "json",
		  success: function(data){
				$(".overlay").toggleClass("actives");
				$('#message_loader').hide();
				if(data.result == 'ok'){
					
				}else{
					for( var k in data.errors ){
						
						cart_row_error(k,data.errors[k]);

					}
				}
		  },
		 
		});
}
function cart_row_error(id,error){
	$('#cart-row-'+id).addClass('danger');					
	$('#error-cart-row-'+id).find('td').html(error).closest('tr').show();

	setTimeout(function(){
		$('#cart-row-'+id).removeClass('danger');					
		$('#error-cart-row-'+id).find('td').html('').closest('tr').hide();
	},3000);
}

$(document).ready(function(){
		if( typeof js_reload_cart != 'undefined' && js_reload_cart != null && js_reload_cart == 1 ){
			
			update_cart();
		}

		$('.quantity').on('change',function(){
				var prev = $(this).data('val');
				var el = $(this);
				
				if( typeof js_reload_cart_after_change_qnt != 'undefined' && js_reload_cart_after_change_qnt != null && js_reload_cart_after_change_qnt == true){
					var id = $(this).attr('cod');
					
					$(".overlay").toggleClass("actives");
					$.ajax({
					  type: "GET",
					  url: "index.php",
					  data: { action: "change_qnt",id : id,qnt:$(this).val(),ctrl:'Cart',mod:'cart_onepage',ajax:1},
					  dataType: "json",
					  success: function(data){
							
							if(data.result == 'ok'){
								document.location.reload();
								
							}else{
								$(".overlay").toggleClass("actives");
								el.val(prev);
								totalCart();
								cart_row_error(id,data.error);
								//MarionAlert(js_error_title_alert,data.error);
							}
					  },
					 
					});
				}else{
					cart_updated = true;
					cart_changed();
				}
		});
})

function plus_cart(cod){
	if( cod ){
		var el = $('#quantity_'+cod);
	}else{
		var el = $('#quantity');
	}
	
	quantity = el.val();
	el.data('val', quantity);
	quantity = parseInt(quantity)+1;
	

	el.val(quantity);
	el.trigger('change');
	if( typeof js_reload_cart_after_change_qnt != 'undefined' && js_reload_cart_after_change_qnt != null ){
	
	}else{
		totalCart();
	}
	return;
}


function minus_cart(cod){
	if( cod ){
		var el = $('#quantity_'+cod);
	}else{
		var el = $('#quantity');
	}
	
	quantity = el.val();
	el.data('val', quantity);
	quantity = parseInt(quantity)-1;
	if(quantity <= 0) quantity = 1;

	el.val(quantity);
	el.trigger('change');
	if( typeof js_reload_cart_after_change_qnt != 'undefined' && js_reload_cart_after_change_qnt != null ){
	
	}else{
		totalCart();
	}
	return;
}

function confirm_cart_update(){
	$('.cart-row').removeClass('danger');
	$('.error-cart-row').hide();
	$(".overlay").toggleClass("actives");
	$('#message_loader').show();
	
	
	var formdata = $('#cart').serialize();
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "cart_ok",formdata :formdata,ctrl:'Cart',mod:'cart_onepage',ajax:1},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					//console.log('qua');
					document.location.reload();
				}else{
					$(".overlay").toggleClass("actives");
					cart_row_error(data.id,data.error);
					for( var k in data.errors ){
						
						cart_row_error(k,data.errors[k]);

					}
					
				}
		  },
		 
	});

}



 function confirmDeleteOrder(id){
	if( typeof js_reload_cart_after_change_qnt != 'undefined' && js_reload_cart_after_change_qnt != null && js_reload_cart_after_change_qnt == 1 ){
		/*MarionConfirm(
			js_confirm_title_alert,
			null,
			function(){*/
				
					$.ajax({
					  type: "GET",
					  url: "index.php",
					  data: { action: "deleteOrder",id : id,ajax:1,mod:'cart_onepage',ctrl:'Cart'},
					  dataType: "json",
					  success: function(data){
							if(data.result == 'ok'){
								document.location.reload();
								
							}else{
								MarionAlert(js_error_title_alert,data.error);
							}
					  },
					 
					});
				
			/*}
		
		);*/
	}else{
		cart_updated = true;
		cart_changed();
		$('#cart-row-'+id).remove();
		$('#cart-row-delete-'+id).remove();
		$('#error-cart-row-'+id).remove();

		
		totalCart();
		if( $('.cart-row').length == 0 ){
			$('.cont-table-cart').remove();
		}
	}


}


function step1(){
	$('.cart-row').removeClass('danger');
	$('.error-cart-row').hide();
	var formdata = $('#cart').serialize();
	$(".overlay").toggleClass("actives");
	$('#message_loader').hide();
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "cart_ok",formdata :formdata,ctrl:'Cart',mod:'cart_onepage',ajax:1},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					//console.log('qua');
					//document.location.href=data.url;
					document.location.href="index.php?mod=cart_onepage&ctrl=Cart";

				}else{
					$(".overlay").toggleClass("actives");
					if( data.id ){
						cart_row_error(data.id,data.error);
					}else{
						MarionAlert(js_error_title_alert,data.error);
					}
					
				}
		  },
		 
	});
}