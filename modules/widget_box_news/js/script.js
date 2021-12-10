$(() => {

	jQuery.fn.nane = function () {
		// Image Loader
		//circles.svg
		//three-dots.svg
		//rings.svg
		//puff.svg			
		$loader = $(this);
		calculate($loader);
		function calculate($loader) {
			//$image = '<img src="http://samherbert.net/svg-loaders/svg-loaders/puff.svg" width="100" height="100">';
			$image = '<img src="/modules/widget_box_news/img/loader.svg" width="100" height="100">';
			$loader.append($image);
			$image = $loader.find('img');
			$image.hide();
			// Content Size
			$contentWidth = $loader.width() / 2;
			$contentHeigth = $loader.height() / 2;
			// Center Image
			$image.load(function () {
				$cx = this.width / 2,
					$cy = this.height / 2;
				//this.style.marginLeft = ($contentWidth - $cx) + "px";
				//this.style.marginTop = ($contentHeigth - $cy) + "px";
				$image.show();
			});
			$loader.find('img:not(:last)').each(function () {
				$(this).animate({
					left: $cx + "px",
					top: $cy + "px",
					opacity: '1',
					height: "-=" + this.height,
					width: "-=" + this.width
				}, 500, 'easeOutCubic');
			});
		}



		// Not by pixel resize
		var move_id;
		$(window).resize(function () {
			clearTimeout(move_id);
			move_id = setTimeout(doneResizing, 300);
		});

		// Function Call self
		function doneResizing() {
			calculate($loader);
		}

	};

	$('#loader-page').nane();

	console.log('test');

	let data = [];

	const getGlobals = id => {
		let cols = parseInt($('#' + id).attr('cols'));
		let rows = parseInt($('#' + id).attr('rows'));
		let cols_netbook = parseInt($('#' + id).attr('cols_netbook'));
		let rows_netbook = parseInt($('#' + id).attr('rows_netbook'));
		let cols_tablet = parseInt($('#' + id).attr('cols_tablet'));
		let rows_tablet = parseInt($('#' + id).attr('rows_tablet'));
		let cols_smartphone = parseInt($('#' + id).attr('cols_smartphone'));
		let rows_smartphone = parseInt($('#' + id).attr('rows_smartphone'));
		let first_news = parseInt($('#' + id).attr('first_news'));

		return data = {
			id,
			cols,
			rows,
			first_news,
			cols_netbook,
			rows_netbook,
			cols_tablet,
			rows_tablet,
			cols_smartphone,
			rows_smartphone
		}
	}

	const createSlick = (div, data) => {

		/*let show = data.cols;
		let show_netbook = data.cols_netbook;
		let show_tablet = data.cols_tablet;
		let show_smartphone = data.cols_smartphone;

		if (data.rows <= 1) {
			show = data.cols;
		} else {
			show = 1;
		}

		if (data.rows_netbook <= 1) {
			show_netbook = data.cols_netbook;
		} else {
			show_netbook = 1;
		}

		if (data.rows_tablet <= 1) {
			show_tablet = data.cols_tablet;
		} else {
			show_tablet = 1;
		}

		if (data.rows_smartphone <= 1) {
			console.log('here');
			show_smartphone = data.cols_smartphone;
		} else {
			show_smartphone = 1;
		}*/

		$(div).slick({
			dots: true,
			arrows: false,
			infinite: true,
			speed: 300,
			rows: data.rows,
			slidesToShow: 1,
			slidesToScroll: 1,
			slidesPerRow: data.cols,
			responsive: [
				{
					//Netbook
					breakpoint: 1025,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1,
						slidesPerRow: data.cols_netbook,
						rows: data.rows_netbook
					}
				},
				{
					//iPad
					breakpoint: 1024,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1,
						dots: false,
						autoplay: true,
						autoplaySpeed: 2000,
						slidesPerRow: data.cols_tablet,
						rows: data.rows_tablet
					}
				},
				{
					//Cellulari e minori
					breakpoint: 481,
					settings: {
						slidesToShow: 1,
						slidesToScroll: 1,
						dots: false,
						autoplay: true,
						autoplaySpeed: 2000,
						slidesPerRow: data.cols_smartphone,
						rows: data.rows_smartphone
					}
				}
			]
		});

		$('#loader-page').css({ display: 'none' });
	}


	const displayItem2 = (data, params) => {
		$('.widget-box-news-slider-' + params.id).slick('unslick');
		$('.widget-box-news-slider-' + params.id).remove();

		$('.news-list-' + params.id).html(data);
		createSlick('.widget-box-news-slider-' + params.id, params);

	};

	/**
	* TODO: Rimpiazzare la funzione con quella funzionante

	const displayItem = data => {

		let output = '<div class="slider">';

		for (let i = 0; i < data.length; i++) {
			output += '<div>' + data[i]._localeData.it.title + '</div>';
		}

		output += '</div>';

		console.log(output);

		$('.slider').slick('unslick');
		$('.slider').remove();
		$('#news-list').html(output);
		createSlick('.widget-box-news-slider');

	};
	*/

	const getNews = (category, data) => {
		$.ajax({
			dataType: 'json',
			url: '/index.php?ctrl=Front&mod=widget_box_news&action=get_news',
			method: 'GET',
			data: {
				'id': category,
				'box_id': data.id
			},
			success: res => {
				//displayItem(res);
				displayItem2(res.data, data);
			}
		});
	}

	$('.category-label').click((e) => {
		let category = e.currentTarget.id;
		let box_id = e.currentTarget.getAttribute('box-id');
		$('#loader-page').css({ display: 'block' });

		getNews(category, getGlobals(box_id));
	});

	$('.check-globals').each((i, element) => {
		data = {
			'id': element.getAttribute('id'),
			'cols': parseInt(element.getAttribute('cols')),
			'rows': parseInt(element.getAttribute('rows')),
			'first_news': parseInt(element.getAttribute('first_news')),
			'cols_netbook': parseInt(element.getAttribute('cols_netbook')),
			'rows_netbook': parseInt(element.getAttribute('rows_netbook')),
			'cols_tablet': parseInt(element.getAttribute('cols_tablet')),
			'rows_tablet': parseInt(element.getAttribute('rows_tablet')),
			'cols_smartphone': parseInt(element.getAttribute('cols_smartphone')),
			'rows_smartphone': parseInt(element.getAttribute('rows_smartphone'))
		};

		/** 
		* ! A VOLTE CRASHA
		*/
		getNews(data.first_news, data);

		$('.menu-mob-item').click(e => {
			let contents = e.currentTarget.innerHTML;
			$('.menu-mob-active').html(contents);

			$('.cat-active')[2].classList.remove('cat-active');

			e.currentTarget.classList.add('cat-active');
		});

		$('.menu-desk-element').click(e => {
			$('.cat-active').removeClass('cat-active');

			e.currentTarget.classList.add('cat-active');
		});

		$('.menu-mob-active').click(() => {
			$('.box-toggle').slideToggle(500);
		});

	});

});