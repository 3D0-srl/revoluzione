function change_status_image_instagram(id){
	$.ajax({
		type: "POST", 
		url: "index.php", 
		 dataType: "json",
		data: {"id":id,'action':'change_status' },
		success: function(data) {
			
			if( data.result == 'ok'){
				
				$('#status_'+id).html(data.html);
			}
		}
	  });
}