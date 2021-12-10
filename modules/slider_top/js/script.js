$('.sovra-top').slick({
	arrows: true,
	dots: false,
	infinite: true,
	autoplay: true,
	autoplaySpeed: 5000,
	slidesToShow: 1,
	slidesToScroll: 1
});

setInterval(function() {
$('.slider_top_countdown').each(function(){
	var countDownDate = new Date($(this).attr('date')).getTime();
	//alert($(this).attr('date'));
	$(this).addClass('compact');
	let d = $(this).attr('d');
	let h = $(this).attr('h');
	let m = $(this).attr('m');
	let s = $(this).attr('s');
	let compact = parseInt($(this).attr('compact'));
	let color = $(this).attr('color');
	let background = $(this).attr('background');
	var now = new Date().getTime();

	// Find the distance between now and the count down date
	var distance = countDownDate - now;

	// Time calculations for days, hours, minutes and seconds
	var days = Math.floor(distance / (1000 * 60 * 60 * 24));
	var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
	var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
	var seconds = Math.floor((distance % (1000 * 60)) / 1000);

	if(seconds < 10){
		seconds = "0"+seconds;
	}
	if(hours < 10){
		hours = "0"+hours;
	}

	if(minutes < 10){
		minutes = "0"+minutes;
	}
	
	if(days < 10){
		days = "0"+days;
	}


	// Display the result in the element with id="demo"
	//$(this).html('<b>'+days + "</b> "+d+" / <b> " + hours + "</b> "+h+" / <b>"+ minutes + "</b> "+m+" / <b>" + seconds + "</b> "+s);

	//$(this).html('<span>'+days +" "+d+"</span><span>"+hours+ " "+h+"</span><span>"+ minutes +" "+m+"</span><span>" + seconds +" "+s +"</span>");
	if (distance > 0) {
		if(compact){
			let span = "<span style='color:"+color+"; background:"+background+"; border-color: "+color+";'>";
			$(this).html(span+days +"</span> : "+span+hours+ "</span> : "+span+ minutes +"</span> : "+span + seconds +"</span>");
		}else{
			$(this).html('<b>'+days + "</b> "+d+" / <b> " + hours + "</b> "+h+" / <b>"+ minutes + "</b> "+m+" / <b>" + seconds + "</b> "+s);
		}
	}else{
		$(this).html('EXPIRED');
		
	}
	
	
	if (distance < 0) {
		//clearInterval(x);
		//document.getElementById("demo").innerHTML = "EXPIRED";
	}
})

},1000);




$(window).load(function(){
	$(".sovra-top").removeClass("loading");
});