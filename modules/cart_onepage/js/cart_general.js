$(document).ready(function(){
	if( typeof js_action != 'undefined' && js_action != null ){
		var current_row = '';
		switch(js_action){
			case 'cart_payment':
				//current_row = 'row_payment';
				//break;
			case 'cart_shipment':
				current_row = 'row_shipment';
				break;
			case 'cart_new_address':
			case 'cart_address':
				current_row = 'row_address';
				break;
		}

		if( current_row ){
			
			$('html,body').animate({
				scrollTop: $('#'+current_row).offset().top -100
			  },'slow');
		}
	}
	$(".vediriep").click(function(){
		var bool = $(".riepilogo").hasClass("chiuso");
		var unclicked = $(".riepilogo").hasClass("unclicked");
		if(bool)
		{
			$(".fixedcart .bx-wrapper").slideToggle();
			$(".riepilogo").removeClass("chiuso");
			$(".vediriep").find(".fa").removeClass("fa-plus").addClass("fa-minus");
			if(unclicked)
			{
				$(".riepilogo").removeClass("unclicked");
				$(".riepilogo").slideToggle();
				$('.riepilogo').bxSlider({
					minSlides: 2,
					maxSlides: 2,
					moveSlides: 1,
					controls: true,
					auto: false,
					autoControls: false,
					autoHover: false,
					pager: false,
					mode: "vertical"
				});
				//$(".fixedcart .bx-viewport").addClass("riepilogo");
			}
		}
		else
		{
			$(".fixedcart .bx-wrapper").slideToggle();
			$(".riepilogo").addClass("chiuso");
			$(".vediriep").find(".fa").removeClass("fa-minus").addClass("fa-plus");
		}
	});	

	$(".scegli input").click(function() {
		if($(this).is(":checked")) {
			$(".field-hidden").slideDown();
		}else{
			$(".field-hidden").slideUp();
		}
		
	});
});

$(document).ready(function(){
	var wind = $(window).width();
	var c = $(".container").width();
	var ffix = $(".fixfixed").width();
	var spazi = (wind-ffix)/2;
	var spazicont = (wind-c)/2;

	$(".fixedcart").css("right", spazicont);
	$(".fixtop").css("width", ffix);
	var spazietto = spazi;
	$(".fixtop").css("left", spazietto);
	$(".openlogin").click(function(){
		$(".underlogin").slideToggle();
	});
});

$(window).resize(function(){
	var wind = $(window).width();
	var c = $(".container").width();
	var ffix = $(".fixfixed").width();
	var spazi = (wind-ffix)/2;
	var spazicont = (wind-c)/2;

	$(".fixedcart").css("right", spazicont);
	$(".fixtop").css("width", ffix);
	var spazietto = spazi;
	$(".fixtop").css("left", spazietto);
}); 

$(document).ready(function(){
	$(".btn-coupon").html("<i class='fa fa-arrow-right'></i>");
	$("#coupon_name").attr("placeholder", "Codice coupon");
	$("#opendett").click(function(){
		$(".nasconditab").toggleClass("open");
		var bool = $(".nasconditab").hasClass("open");
		if(bool)
		{
			$("#opendett").html("- NASCONDI");
			$("#submit_coupon").show();
		}
		else
		{
			$("#opendett").html("+ ESPANDI");
			$("#submit_coupon").hide();
		}
	});
	$(".div_riepilogo .titcart").on("click",function(){
		$(this).toggleClass("active");
		$(".cont-prod-cart").slideToggle();	
	});
	$(".user").on("click",function(){
		$(".guest").removeClass("active");
		$(this).toggleClass("active");
		$(".logindata").show();
		$(".nologindata").hide();
	});
	$(".guest").on("click",function(){
		$(".user").removeClass("active");
		$(this).addClass("active");
		$(".logindata").hide();
		$(".nologindata").show();
	});
	var popupScelta = $("#cart").is(":visible");
	if(!popupScelta) {
		$(".logo > a").on("click",function(e){
			e.preventDefault();
			$(".popup-scelta").toggleClass("active");
		});
	}
});

function chiudiPopup(){
	$(".popup-scelta").removeClass("active");
}

function resizelinea()
{
	var w = $(".fixtop").width();
	var l = (w/100)*28;
	var lui = $(".palla.current > span");
	var lui2 = $(".palla.pallaselezionata > span");
	lui.css("width", l);
	lui2.css("width", l);
}

$(document).ready(function(){
	resizelinea();	
});
$(window).resize(function(){
	resizelinea();
});

	







/*

var flag_step5 = true;
function step5(){

	
	$(".overlay").toggleClass("actives");
	if( !flag_step5 ) return;
	var formdata = $('#payment').serialize();
	
	$('input').each(function(){
		$(this).removeClass('field_required');
	});
	$('select').each(function(){
		$(this).removeClass('field_required');
	});
	flag_step5 = false;
	$.ajax({
		  type: "GET",
		  url: "/eshop.php",
		  data: { action: "cart_payment_ok",formdata :formdata},
		  dataType: "json",
		  success: function(data){
				flag_step5 = true;
				$('#btn-avanti-cart').removeAttr('disbaled');
				if(data.result == 'ok'){
					document.location.href=data.url;
				}else{
					$(".overlay").toggleClass("actives");
					
					$('#'+data.field).addClass('field_required');
					MarionAlert(js_error_title_alert,data.error);
				}
		  },
		 
	});
}
*/

 $(document).ready(function(){



	if( $('#carrello_fisso').length > 0 ){
		totalCart();

	}


	toggleactive(".s-tab", ".tabboso", "valore");

	/*if( typeof js_requestInvoice != 'undefined' && js_requestInvoice != null ){
		
		$('#requestInvoice_'+js_requestInvoice).trigger('change').closest('button').addClass('active');
	}
	if( typeof js_shipping != 'undefined' && js_shipping != null ){
		if( js_shipping == 0){
			getMethodShipping(js_shippingCountry,js_shippingMethod);
			$('#shippingData').show();
		}else{
			getMethodShipping(js_country,js_shippingMethod);
		}
		$('#shipping_'+js_shipping).trigger('change').closest('button').addClass('active');
		totalCart()
	}
	

	if( typeof js_registration != 'undefined' && js_registration != null){
		$('#registration_'+js_registration).trigger('change').closest('button').addClass('active');
	}

	$('.shippingData').on('change',function(){
		var val = $(this).val();
		if($(this).prop('checked') == true && val == 1){
			$('#shippingData').hide();
			getMethodShipping($('#country').val());
		}else{
			getMethodShipping($('#shippingCountry').val());
			$('#shippingData').show();
		}
	
		
	});

	$('.paymentData').on('change',function(){
		var val = $(this).val();
		$('.description_payment').hide();
		$('#description_'+val).show();
		if( val == 'STRIPE'){
			$('#stripe').show();
		}else{
			$('#stripe').hide();
		}

		if( $('#price_'+val).val() ){
			var prezzo_pagamento = $('#price_'+val).val();
		}else{
			var prezzo_pagamento = 0;
		}	

		$('#pricePayment').val(parseFloat(prezzo_pagamento));
		var prezzo_pagamento_formattato = (parseFloat(prezzo_pagamento)).formatMoney(2, ',', '');
		$('#pricePaymentFormatted').html(prezzo_pagamento_formattato);
		totalCart();
		
	});
	if( typeof js_paymentMethod != 'undefined' && js_paymentMethod != null ){
		$('#paymentMethod_'+js_paymentMethod).trigger('click');
	}*/
 });



/*

function registration(el){
	if( el.val() == 1 && el.prop('checked') == true){
		$('.no_registration').show();
		$('.registration').show();
	}else{
		$('.no_registration').show();
		$('.registration').hide();
	}
}


function change_shipping(val){
	$('#shippingMethod').val(val);
	var country;
	if( $('#shipping_0').prop('checked') == true){
		country = $('#shippingCountry').val();
	}else{
		country = $('#country').val();
	}
	if(val != 0){
		priceShipping(country,val);
	}else{
		$('#priceShipping').val(0);
		$('#priceShippingFormatted').html('0,00');
		$('#descriptionShipping').html('');
		totalCart();
	}
}
*/
/*
function change_country(country,type){
	
	if( type == 0 && $('#shipping_0').prop('checked') == true){
		getMethodShipping(country);
	}else if(type == 1  && $('#shipping_0').prop('checked') == false){
		getMethodShipping(country);
	}
	
}

function getMethodShipping(country,selected){
	$.ajax({
		  type: "GET",
		  url: "/eshop.php",
		  data: { action: "changeCountry",country :country},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'nak'){
					MarionAlert(js_error_title_alert,data.error);
				}
				if( $('#countryShipping').length > 0 ){
					$('#countryShipping').html(data.country);
				}
				if(!selected) selected = 0;
				crea_select($('#shippingMethod2'),data.options,selected);
				$('#shippingMethod').val('');
				if(!selected){
					$('#priceShipping').val(0);
					$('#priceShippingFormatted').html('0,00');
					$('#descriptionShipping').html('');
					totalCart();
				}else{
					change_shipping(selected);
				}
				
		  },
		 
	});
}
*/
/*
function priceShipping(country,shippingMethod){
	if(country && shippingMethod){
		$.ajax({
			  type: "GET",
			  url: "/eshop.php",
			  data: { action: "changeShipping",id :shippingMethod, country:country},
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
						if( $('#descriptionShipping').length > 0 ){
							$('#descriptionShipping').html(data.info['description']);
						}
						
						if( $('#priceShippingFormatted').length > 0 ){
							$('#priceShippingFormatted').html(data.info['priceFormatted']);
						}

						if( $('#priceShipping').length > 0 ){
							$('#priceShipping').val(data.info['price']);
						}
						
						totalCart();
					}else{
						MarionAlert(js_error_title_alert,data.error);
					}
			  },
			 
		});
	}

}

*/

function toggleactive(a,b,c)
{	
	var cliccoso = a;
	var secondo = b;
	var valore = c;
	$(cliccoso).click(function(){
		var n = $(this).attr(valore);
		$(cliccoso).each(function(){
			if($(this).attr(valore)==n)
			{
				$(this).addClass("active");
			}
			else
			{
				$(this).removeClass("active");
			}
		});
		if(n==1)
		{
			$("#regcon").hide();
			$("#logindata").show();
		}
		if(n==2)
		{ 
			$("#logindata").hide();
			$("#regcon").show();
			$("#registration").show();
			$("#registration_1").trigger("click");
			$("#regcon").addClass("his");
		}
		if(n==3)
		{ 
			$("#logindata").hide();
			$("#regcon").show();
			$("#registration").hide();
			$("#registration_0").trigger("click");
			$("#regcon").addClass("his");
		}
	});
}


function totalCart(){
	
	var subtotal = 0;
	var subtotal_witout_tax = 0;
	var subtotal_tax = 0;
	var total = 0;
	var shipping_price=0;
	var payment_price=0;
	var discount=0;
	if( typeof js_step != 'undefined' && js_step == 1 ){
		$('input').each(function(index,value){
			
			cod = $(this).attr('cod');
			if( cod ){
				price = $(this).attr('price');
				price_without_tax = $(this).attr('price_without_tax');
				taxPrice = $(this).attr('taxPrice');
				quantity = $(this).val();
				totalRow = parseFloat(price)*parseFloat(quantity);
				totalRowTax = parseFloat(taxPrice)*parseFloat(quantity);
				totalRowWitoutTax = parseFloat(price_without_tax)*parseFloat(quantity);
				
				subtotal = subtotal + totalRow;
				subtotal_tax = subtotal_tax + totalRowTax;
				subtotal_witout_tax = subtotal_witout_tax + totalRowWitoutTax;
				totalRowFormatted = (totalRow).formatMoney(2, ',', '');
				totalwithoutTaxRowFormatted = (totalRowWitoutTax).formatMoney(2, ',', '');
				$('.total_'+cod).html(totalRowFormatted);
				$('.total_without_tax_'+cod).html(totalwithoutTaxRowFormatted);
				
			}
			
		});
		if( $('#cart_discount').length > 0 ){
			discount = parseFloat($('#cart_discount').val());
		}
		total = subtotal-discount;
		

		var totalFormatted = (total).formatMoney(2, ',', '');
		var subtotal_taxFormatted = (subtotal_tax).formatMoney(2, ',', '');
		var subtotalFormatted = (subtotal).formatMoney(2, ',', '');
		var subtotal_witout_taxFormatted = (subtotal_witout_tax).formatMoney(2, ',', '');
		$('#cart_total_products_without_tax').html(subtotal_witout_taxFormatted);
		$('#cart_total_products').html(subtotalFormatted);
		$('#cart_taxes').html(subtotal_taxFormatted);

	
		$('#cart_total').html(totalFormatted);
	}else{
		subtotal = parseFloat($('#cart_total_products_value').val());
		
		if( $('#priceShipping').length > 0 ){
			shipping_price = parseFloat($('#priceShipping').val());
		}
		

		if( $('#pricePayment').length > 0 ){
			payment_price = parseFloat($('#pricePayment').val());
		}
		

		if( $('#cart_discount').length > 0 ){
			discount = parseFloat($('#cart_discount').val());
		}
		total = subtotal+shipping_price+payment_price-discount;
		
		var totalFormatted = (total).formatMoney(2, ',', '');
		$('#cart_total').html(totalFormatted);
	}

	

}



