function change_visibility(el,id,field){
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  data: { ctrl: 'StatusOrderAdmin',action:'change_value','id':id,'ajax':1,field:field,mod:'ecommerce'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					el.removeClass().addClass(data.class).html(data.text);
				}else{
					
				}
		  },
	});
}
