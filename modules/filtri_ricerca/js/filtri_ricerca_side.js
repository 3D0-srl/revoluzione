  $( function() {
	 
	//js_currency =  decodeURIComponent(escape(js_currency));
    $( "#slider-range" ).slider({
      range: true,
      min: 0,
      max: parseFloat(js_maxprice),
	  step: parseFloat(js_step_price),
      values: [ parseFloat(js_price_min), parseFloat(js_price_max) ],
      slide: function( event, ui ) {
		$('#price_min').val(ui.values[ 0 ]);
		$('#price_max').val(ui.values[ 1]);

		$('#price_min_html').html(ui.values[ 0 ]);
		$('#price_max_html').html(ui.values[ 1]);

		
        $( "#amount" ).val( ui.values[ 0 ] + " - "+ ui.values[ 1 ] );
      },
	  change:function(event, ui ){
		$('#filtri_ricerca_form').submit();
	  }
    });

  });
  