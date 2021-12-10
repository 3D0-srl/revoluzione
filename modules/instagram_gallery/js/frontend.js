var instagram_current_slide = 0;
number_pager_new = 1;
$(document).ready(function(){

	load_js_instagram(false);
	
	
	if( $(".instagram-show-more").length > 0 ){
		$(window).scroll(function(e) {
			
			var t = $(window).height();
			
			var h = 200*number_pager_new;
			
			if ($(document).scrollTop() >= h) {
				
				
				show_more_instagram();
				
			}
		});

	}

	
});


function instragram_build_slider(){

		if( insta_slider_enabled ){
		
			var w = $(window).width();
			if(w >= 768) {
				$('.products_instagram').bxSlider({	
					slideWidth: 149,
					minSlides: 2,
					maxSlides: 2,
					moveSlides: 2,
					slideMargin: 0,
					controls: true,
					auto: false,
					pager: false,
					autoControls: false,
					autoHover: true,
					pause: 4000,
					speed: 300,
					randomStart: false
				});
			}
			else if (w <= 767) {
				$('.products_instagram').bxSlider({	
					slideWidth: 149,
					minSlides: 1,
					maxSlides: 1,
					moveSlides: 1,
					slideMargin: 0,
					controls: true,
					auto: false,
					pager: false,
					autoControls: false,
					autoHover: true,
					pause: 4000,
					speed: 300,
					randomStart: false
				});
			}
		}
}

function load_js_instagram(reload){
	
	if( !reload ){
		$(".instafeed_post").on('click',function(){
			$('.slide_instagram').hide();
			var ordine = parseInt($(this).attr('ordine'));
			current_slide = ordine;
			
			
			$(".overlay-instagram").toggleClass("actives");
			$("body").toggleClass("noscroll");
			$('#popup_instagram_'+ordine).show();
		});

		$(".overlay-close").on('click',function(){
			$(".overlay-instagram").removeClass("actives");
			$("body").removeClass("noscroll");
		});
	}else{
		$('.instafeed_post_new').each(function(){
			$(this).on('click',function(){
			
				$('.slide_instagram').hide();
				var ordine = parseInt($(this).attr('ordine'));
				current_slide = ordine;
				$(".overlay-instagram").toggleClass("actives");
				$("body").toggleClass("noscroll");
				$('#popup_instagram_'+ordine).show();
			}).removeClass('instafeed_post_new');

			
		})
	}
	

	

	if( reload ){
		
	}else{




		$('.img_product').on('mouseover',function(){
		
			var prodotto = $(this).attr('prodotto');
			$('.pallinatag_'+prodotto).addClass('active_pallina');
			$('.pallinatag_'+prodotto).closest('span').find('.tag_instagram').fadeIn();
		});

		$('.img_product').on('mouseleave',function(){
			var prodotto = $(this).attr('prodotto');
			$('.pallinatag_'+prodotto).removeClass('active_pallina');
			$('.pallinatag_'+prodotto).closest('span').find('.tag_instagram').fadeOut();
		});


		$('.pallina_tag').on('mouseover',function(){
			$(this).closest('span').find('.tag_instagram').fadeIn();
		});
		$('.pallina_tag').on('mouseleave',function(){
			
			$(this).closest('span').find('.tag_instagram').fadeOut();
			
		});
	}

	instragram_build_slider();

	
	

}

var insta_offset = 10;
var enable_upload_instagram = true;
function show_more_instagram(){
	if( enable_upload_instagram ){
		enable_upload_instagram = false;
		$.ajax({
		type: "POST", 
		url: "index.php",
		dataType: "json",
		data: {ctrl:'Index',mod:'instagram_gallery',ajax:1,"action": 'load_others','offset':insta_offset },
		success: function(data) {
			enable_upload_instagram = true;
			if( data.result == 'ok'){
				number_pager_new = number_pager_new+1;
				if( insta_slider_enabled ){
					show_more_instagram2(insta_offset);
				}
				insta_offset = data.offset;	
				$('.instafeed').append(data.html);
				$('.instafeed_content_new').fadeIn(500).removeClass('instafeed_content_new');
				
				if( data.last ){
					$('.instagram-show-more').hide();
				}	
			}
		}
	  });
	}

}

function show_more_instagram2(offset){

	$.ajax({
	type: "POST", 
	url: "index.php", 
	 dataType: "json",
	data: {ctrl:'Index',mod:'instagram_gallery',ajax:1,"action": 'load_others','offset': offset, 'type': 'slider'},
	success: function(data) {
		
		if( data.result == 'ok'){
			$('#slider_instagram').append(data.html2);
			load_js_instagram(true);
		}
	}
  });

}


function instagram_prev_slide(){

	var ordine = instagram_current_slide-1;
	
	if( $('#popup_instagram_'+ordine).length > 0 ){
		 $('.slide_instagram').hide();
		 $('#popup_instagram_'+ordine).show();
		 instagram_current_slide = ordine;
	}
	
	

}

function instagram_next_slide(){
		
		var ordine = instagram_current_slide+1;
		if( $('#popup_instagram_'+ordine).length > 0 ){
			 $('.slide_instagram').hide();
			 $('#popup_instagram_'+ordine).show();
			 instagram_current_slide = ordine;
		}
}