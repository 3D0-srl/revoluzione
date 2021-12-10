$(document).ready(function(){
	
	
	

	widget_slidertag = [];
	$('.widget_slidertag').each(function(){
		var el = $(this);
		//var controls = el.attr('wcontrols');
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

			//console.log(data);
			
			widget_slidertag.push($(this).bxSlider(data));
			
		}
		//}

	});
	
});

$(window).resize(function(){
	
	
	if( typeof widget_slidertag != 'undefined' && widget_slidertag != null){
		for( var k in widget_slidertag ){
			var el = widget_slidertag[k];
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
				el.reloadSlider(data);
			}
			
		}
	}
});

