$(document).ready(function(){
	
	var swiper = new Swiper('.swiper', {
		slidesPerView: 'auto',
		spaceBetween: 0,
		freeMode: false,
		grabCursor: true,
		observer: true,
		observeParents: true,
		allowTouchMove: true,

		navigation: {
			nextEl: '.swiper-button-next',
			prevEl: '.swiper-button-prev',
		}
	});
});