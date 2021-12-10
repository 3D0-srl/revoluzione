$(() => {
    $('.js-listener').each((index, element) => {
        let active_gallery = element.attributes['active-gallery'].value;

        $.ajax({
            url: 'index.php',
            data: {
                mod: 'photogallery',
                ctrl: 'Front',
                action: 'widget_html',
                id: active_gallery
            },
            success: res => {
                $('.js-listener').eq(index).append(res);

				let is_autoplay = $('.js-listener').eq(index)[0].parentNode.classList.contains('gallery-autoplay');

				if(is_autoplay) 
				{
					$('.js-listener').eq(index).slick({
						dots: true,
						infinite: true,
						autoplay: true,
						autoplaySpeed: 3000,
						speed: 600,
						rows: 1,
						slidesToShow: 1,
						slidesToScroll: 1,
						slidesPerRow: 1
					});
					$('.fancybox').fancybox({
						openEffect: "none",
						closeEffect: "none"
					});
				} else {
					$('.js-listener').eq(index).slick({
						dots: true,
						infinite: true,
						speed: 300,
						rows: 1,
						slidesToShow: 1,
						slidesToScroll: 1,
						slidesPerRow: 1
					});
					$('.fancybox').fancybox({
						openEffect: "none",
						closeEffect: "none"
					});
				}
                
            }
        });
    });
});

$(window).on('load', function () {
	var altezzabox = $(".slider_testimonials").innerHeight();
	$(".cont-gallery-city .image-slide-box").height(altezzabox);
});

$(window).resize(function(){
	var altezzabox = $(".slider_testimonials").innerHeight();
	$(".cont-gallery-city .image-slide-box").height(altezzabox);
});