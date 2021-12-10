let widget_swipers = [];

$('.widget_swiper').each(function(index){

	var el = $(this);
	if( el.hasClass('swiper-container') ){
		
		
		var id_box = el.attr('id_box');
		var opzioni = jQuery.parseJSON(el.attr('opzioni'));
	
		el.removeAttr('opzioni');
		/*el.addClass(id); //instance need to be unique (ex: some-slider)
        el.find(".swiper-pagination").addClass("pagination-" + index);
        el.find(".swiper-button-prev").addClass("prev-" + index); //prev must be unique (ex: some-slider-prev)
        el.find(".swiper-button-next").addClass("next-" + index); //next must be unique (ex: some-slider-next)
		*/
		
		
		
		

		var data = opzioni;
		if( data ){

			if( data.width ){
				data.width = parseInt(data.width);
			}

			if( data.navigation ){
				data.navigation = parseInt(data.navigation);
			}
			if( data.scrollbar ){
				data.scrollbar = parseInt(data.scrollbar);
			}

			if( data.spaceBetween ){
				data.spaceBetween = parseInt(data.spaceBetween);
			}
			if( typeof data.freeMode != 'undefined' && data.freeMode == '1'){
				data.freeMode = true;
			}else{
				data.freeMode = false;
			}
			
		
		}

		console.log(data);
		
		//var slider = new Swiper('.widget_swiper_'+id_box);
		var slider = new Swiper('.widget_swiper_'+id_box, {
		  freeMode: data.freeMode,
		  width: data.width,
		  spaceBetween: data.spaceBetween,
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
		widget_swipers.push(slider);
		 

			
	}
	//console.log(widget_swipers);
});


/*if ($('.swiper-container').length > 0) { //some-slider-wrap-in
    let swiperInstances = [];
    $(".swiper-container").each(function(index, element){ //some-slider-wrap-in
        const $this = $(this);
        $this.addClass("instance-" + index); //instance need to be unique (ex: some-slider)
        $this.parent().find(".swiper-pagination").addClass("pagination-" + index);
        $this.parent().find(".swiper-button-prev").addClass("prev-" + index); //prev must be unique (ex: some-slider-prev)
        $this.parent().find(".swiper-button-next").addClass("next-" + index); //next must be unique (ex: some-slider-next)
        swiperInstances[index] = new Swiper(".instance-" + index, { //instance need to be unique (ex: some-slider)
            // your settings ...
            navigation: {
                prevEl: ".prev-" + index,  //prev must be unique (ex: some-slider-prev)
                nextEl: ".next-" + index, //next must be unique (ex: some-slider-next)
            },
            pagination: {
                el: '.pagination-' + index,
                type: 'bullets',
                clickable: true
            },
        });
    });

    // Now you can call the update on a specific instance in the "swiperInstances" object
    // e.g.
    swiperInstances[3].update();
    //or all of them
    setTimeout(function () {
        for (const slider of swiperInstances) {
            slider.update();
        }
    }, 50);
}*/
