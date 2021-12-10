function get_data_mail_status(status,locale){
	
	if( locale == 0 ){
		$('#panel-form').hide();
		$('#cont_form').html();
	}else{
		
		$.ajax({
		  type: "GET",
			  url: "index.php",
			  data: { ctrl:js_ctrl,tabIndex:js_tabIndex,action: "data_mail_status",'id_status':status,'locale':locale,ajax:1,mod:'ecommerce'},
			  dataType: "json",
			  success: function(data){
					
					if(data.result == 'ok'){
						$('#panel-form').show();
						$('#cont_form').html(data.html);
						
					}else{
						$('#panel-form').hide();
						$('#cont_form').html();
						
					}
			  },
		 
		});
	}
}

function save_mail_status(){
	var formdata = $('#form_status_mail').serialize();
	$.ajax({
	  type: "POST",
		  url: "index.php",
		  data: {ctrl:js_ctrl,tabIndex:js_tabIndex, action: "save_data_mail",'formdata':formdata,ajax:1,mod:'ecommerce'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					alert(data.message)
					
					
				}else{
					alert(data.error);
					
				}
		  },
	 
	});
	
}

function executeCopy(text) {
	var input = document.createElement('textarea');
	document.body.appendChild(input);
	input.value = text;
	//input.focus();
	input.select();
	document.execCommand('Copy');
	input.remove();
}