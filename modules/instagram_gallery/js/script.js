function change_status_image_instagram(id){
	$.ajax({
		type: "POST", 
		url: "index.php", 
		data : {ctrl:'Gallery',mod:'instagram_gallery',ajax:1,"id":id,'action':'change_status'},
		dataType: "json",
		success: function(data) {
			
			if( data.result == 'ok'){
				
				$('#status_'+id).html(data.html);
			}
		}
	  });
}