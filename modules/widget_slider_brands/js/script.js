$(document).ready(function(){
	
	


	widget_sliderbrands = [];
	$('.widget-brands').each(function(){
		var el = $(this);
		
		var opzioni = jQuery.parseJSON(el.attr('opzioni'));
		
		

		var w = $(window).width();
		var data = null;
		if(w>1199){  
			data = opzioni.desktop;
		}else if(w>991 && w<1200) {
			data = opzioni.tabletlandscape;
		}else if(w>767 && w<992) {
			data = opzioni.tablet;
		}else if(w<768) { 
			data = opzioni.mobile;
		
		}
		
		if( data ){
			if( data.slideMargin ){
				data.slideMargin = parseInt(data.slideMargin);
			}

			if( data.slideWidth ){
				data.slideWidth = parseInt(data.slideWidth);
			}
			if( data.pager == '0'){
				data.pager = false;
			}else{
				data.pager = true;
			}
			if( data.loop == '0'){
				data.loop = false;
			}else{
				data.loop = true;
			}
			if( data.controls == '0'){
				data.controls = false;
			}else{
				data.controls = true;
			}
			if( data.autoHover == '0'){
				data.autoHover = false;
			}else{
				data.autoHover = true;
			}
			if( data.auto == '0'){
				data.auto = false;
			}else{
				data.auto = true;
			}
			if( data.autoControls == '0'){
				data.autoControls = false;
			}else{
				data.autoControls = true;
			}
			widget_sliderbrands.push($(this).bxSlider(data));
			
		}
		//}

	});
	
});

$(window).resize(function(){
	
	
	if( typeof widget_sliderbrands != 'undefined' && widget_sliderbrands != null){
		for( var k in widget_sliderbrands ){
			var el = widget_sliderbrands[k];
			var opzioni = jQuery.parseJSON(el.attr('opzioni'));
			var w = $(window).width();
			var data = null;
			if(w>1199){  
				data = opzioni.desktop;
			}else if(w>991 && w<1200) {
				data = opzioni.tabletlandscape;
			}else if(w>767 && w<992) {
				data = opzioni.tablet;
			}else if(w<768) { 
				data = opzioni.mobile;
			
			}
			
			if( data ){
				if( data.slideMargin ){
					data.slideMargin = parseInt(data.slideMargin);
				}

				if( data.slideWidth ){
					data.slideWidth = parseInt(data.slideWidth);
				}
				if( data.loop == '0'){
					data.loop = false;
				}else{
					data.loop = true;
				}
				if( data.pager == '0'){
					data.pager = false;
				}else{
					data.pager = true;
				}
				if( data.controls == '0'){
					data.controls = false;
				}else{
					data.controls = true;
				}
				if( data.autoHover == '0'){
					data.autoHover = false;
				}else{
					data.autoHover = true;
				}
				if( data.auto == '0'){
					data.auto = false;
				}else{
					data.auto = true;
				}
				if( data.autoControls == '0'){
					data.autoControls = false;
				}else{
					data.autoControls = true;
				}
				el.reloadSlider(data);
			}
			
		}
	}
});



