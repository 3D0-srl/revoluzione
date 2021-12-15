$(document).ready(function(){
	var swiper = new Swiper('.swiper-container', {
		slidesPerView: 1,
		spaceBetween: 16,
		freeMode: false,
		grabCursor: false,
		observer: true,
		observeParents: true,
		allowTouchMove: false,
        breakpoints: {
          768: {
            slidesPerView: 2
          },
          992: {
            slidesPerView: 3
          },
          1200: {
            slidesPerView: 4
          }
        }/*,

		navigation: {
			nextEl: '.swiper-button-next',
			prevEl: '.swiper-button-prev',
		},

		scrollbar: {
			el: '.swiper-scrollbar',
		}*/
	});
});