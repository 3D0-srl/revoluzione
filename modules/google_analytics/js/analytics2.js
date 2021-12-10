$(document).ready(function(){
	if( $('#box_visitator').length > 0 ){
		
		load_widget_traffic();
	}
});



	

function load_widget_traffic(){
	$.ajax({
	  type: "GET",
	  url: "/modules/google_analytics/controller.php",
	  data: { action: "widget_visits"},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				 $('#widget_content_analytics_traffic_loader').hide();
				 for( var iter in data ){
					if( iter != 'result' && iter != 'content'){
						if( $('#'+iter).length > 0 ){
							$('#'+iter).html(data[iter]);
						}

					}
				 }
				 $('#widget_content_analytics_traffic').show();
				
			}
	  }
	});

};