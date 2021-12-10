function elimina_coupon(id){
	var t = confirm('Sicuro di voler eliminare il coupon?');
	if( t ){
		document.location.href="/admin/modules/manage_coupon/controller.php?action=delete_coupon&id="+id;
	}
}


 $(document).on('click',"#submit_coupon",function(){
	var nome = $('#coupon_name').val();

	$.ajax({
		type: "GET",
		cache: false,
		dataType: 'json',
		url: "index.php",
		data: { action : 'check_coupon', name : nome, ajax:1, ctrl:'Coupon',mod:'manage_coupon' },
		success: function(data) {
			if(data.result=='ok') {
				location.reload();
				
			} else {
				MarionAlert('Attenzione',data.msg);
				$('#coupon_name').val('');
			}

		},
		error: function() {
			//alert("Chiamata fallita, si prega di riprovare...");
		}
	});
  });

  $(document).on('click',"#remove_coupon",function(){
	
	$.ajax({
		type: "GET",
		cache: false,
		dataType: 'json',
		url: "index.php",
		data: { action : 'remove_coupon', ajax:1, ctrl:'Coupon',mod:'manage_coupon' },
		success: function(data) {
			if(data.result == 'ok' ){
				location.reload();
				
			}
			if(data.result == 'nak' ) MarionAlert('Attenzione',data.msg);
		},
		error: function() {
			//alert("Chiamata fallita, si prega di riprovare...");
		}
	});
  });

  
