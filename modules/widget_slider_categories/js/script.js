let widget_categories_swipers = [];
$('.widget_swiper_categories').each(function(index){
	var el = $(this);
	if( el.hasClass('swiper-container') ){
		var id_box = el.attr('id_box');
		var slider = new Swiper('.widget_swiper_'+id_box, {
		  freeMode: true,
		  width: 437,
		  spaceBetween: 15,
		  navigation: {
			prevEl: ".swiper-button-prev_" + id_box,  //prev must be unique (ex: some-slider-prev)
			nextEl: ".swiper-button-next_" + id_box, //next must be unique (ex: some-slider-next)
		  },
		  scrollbar: {
			el: '.js-swiper-scrollbar_' + id_box,
			draggable: true,
			snapOnRelease: true
		  }
		});
		widget_categories_swipers.push(slider);
	}
});