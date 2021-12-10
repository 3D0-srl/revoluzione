var on_undercart = false;

/*function hidden_undercart(){
	setTimeout(function(){
		if( !on_undercart ){
			$('.undercart').slideToggle();
		}else{
			hidden_undercart()
		}
	}, 3000);
}*/
$(document).ready(function(){
	$('#undercart_content').on('mouseenter',function(){
		on_undercart = true;
	});
	$('#undercart_content').on('mouseleave',function(){
		on_undercart = false;
		/*hidden_undercart();*/
	});
	if (typeof $('.number') != 'undefined')	{
		$('.number').each(function() {
			$(this).on('change', function() {
				$('#qt' + $(this).attr('cod')).val($(this).val());
			});
		});
	}
});
/*
function addToCart(){
	var formdata = $('#addCart').serialize();
	$.ajax({
		  type: "GET",
		  url: "/eshop.php",
		  data: { action: "addCart",formdata : formdata},
		  dataType: "json",
		  success: function(data){
		    	if(data.result == 'ok'){
					if( $('#numberProductCart').length > 0){
						if( data.number_products > 0 ){
							$('#numberProductCart').html(data.number_products);
							$('#numberProductCart_cont').show();
						}else{
							$('#numberProductCart_cont').show();
						}
					}else{
						$('#numberProductCart_cont').hide();
					}
					if( $('#totalCart').length > 0){
						$('#totalCart').html(data.total);

					}
					if( $('#undercart_content').length > 0){
						$('#undercart_content').html(data.undercart);
						if( data.number_products > 0 ){
							if( !$('#undercart_content').is(":visible") ){
								$('.undercart').slideToggle();
							}
							setTimeout(function(){
								if( !on_undercart ){
									$('.undercart').slideToggle();
								}
								}, 3000);
							
							
						}

					}
					if( $('#add_product_mobile_popup').length > 0 ){
						
						showtopmsg(data.text_popup_mobile);
						//$('#add_product_mobile_popup').html(data.text_popup_mobile);
					}else{
						 MarionConfirm(
							 js_success_title_alert,
							 data.text_popup,
							 function(){
								document.location.href="/"+js_activelocale+"/cart.htm";
							 },
							 js_go_cart,
							 'OK');
						}
					
			    }else{
					if( $('#add_product_mobile_popup').length > 0 ){
						showtopmsg(data.error,'error');
						//$('#add_product_mobile_popup').html(data.error);
					}else{
						MarionAlert(js_error_title_alert,data.error);
					}
			    	
		    	}
		  },
		 
		});

}
*/
function addToCart(i,number){
	
	 $('.addcart').addClass('loading-addcart');
	if( $('#error_add_to_cart').length > 0 ){
		$('#error_add_to_cart').html('');

	}
	if( i ){
		if( number ){
			var formdata = $('#addCart'+number+'_'+i).serialize();
		}else{
			var formdata = $('#addCart_'+i).serialize();
		}
	}else{
		var formdata = $('#addCart').serialize();
	}
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ajax: 1, ctrl:'Ajax',mod:'ecommerce',action: "addCart",formdata : formdata,indice:i},
		  dataType: "json",
		  success: function(data){
				
		    	if(data.result == 'ok'){
					
					if(data.url_recurrent_payment ){
						document.location.href=data.url_recurrent_payment;
						return;
					}

					if( $('#numberProductCart').length > 0){
						if( data.number_products > 0 ){
							$('#numberProductCart').html(data.number_products);
							$('#numberProductCart_cont').show();
						}else{
							$('#numberProductCart_cont').show();
						}
					}else{
						$('#numberProductCart_cont').hide();
					}
					if( $('#totalCart').length > 0){
						$('#totalCart').html(data.total);

					}
					if( $('#undercart_content').length > 0){
						$('#undercart_content').html(data.undercart);
						/*if( data.number_products > 0 ){
							if( !$('#undercart_content').is(":visible") ){
								$('.undercart').slideToggle();
							}
							setTimeout(function(){
								if( !on_undercart ){
									$('.undercart').slideToggle();
								}
								}, 3000);
							
							
						}*/

					}
					 var tmp_text = $('.addcart').html();

					 $('.overlay-popup-addToCart').addClass('active');
					 $('.popup-addToCart').html(data.html_popup).addClass('active');
					 $('.addcart').removeClass('loading-addcart');
					 $('.addcart').html('<span>'+data.text_success_btn+'</span>').addClass('addcart-successful');
					 setTimeout(function(){ 
						 $('.overlay-popup-addToCart').removeClass('active');
						 $('.popup-addToCart').removeClass('active');
						 $('.addcart').removeClass('addcart-successful').html(tmp_text);
						}, 3000);
					 
					 /*MarionConfirm(
						 js_success_title_alert,
						 data.text_popup,
						 function(){
							document.location.href=js_activelocale+"/cart.htm";
						 },
						 js_go_cart,
						 'OK');*/
						
					
			    }else{
					$('.addcart').removeClass('loading-addcart');
					/*if( $('#add_product_mobile_popup').length > 0 ){
						showtopmsg(data.error,'error');
						//$('#add_product_mobile_popup').html(data.error);
					}else{
						MarionAlert(js_error_title_alert,data.error);
					}*/
					showErrorAddToCart(data.error);
			    	
		    	}
		  },
		 
		});

}

function showErrorAddToCart(error){
	if( $('#error_add_to_cart').length > 0 ){
		$('#error_add_to_cart').html(error).show();
		setTimeout(function(){
			$('#error_add_to_cart').fadeOut('slow');
		},3000)
	}else{
		MarionAlert(js_error_title_alert,error);
	}
}
function addToCartAjax(){
	var formdata = $('#addCart').serialize();
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: {  ajax: 1, ctrl:'Ajax',mod:'ecommerce',action: "addCart",formdata : formdata},
		  dataType: "json",
		  success: function(data){
		    	if(data.result == 'ok'){
					if(data.url_recurrent_payment ){
						document.location.href=data.url_recurrent_payment;
						return;
					}
					if( window.parent.$('#numberProductCart').length > 0){
						if( data.number_products > 0 ){
							window.parent.$('#numberProductCart').html(data.number_products);
							window.parent.$('#numberProductCart_cont').show();
						}else{
							window.parent.$('#numberProductCart_cont').show();
						}
					}else{
						window.parent.$('#numberProductCart_cont').hide();
					}
					if( window.parent.$('#totalCart').length > 0){
						window.parent.$('#totalCart').html(data.total);

					}
					if( window.parent.$('#undercart_content').length > 0){
						window.parent.$('#undercart_content').html(data.undercart);
						if( data.number_products > 0 ){
							if( !window.parent.$('#undercart_content').is(":visible") ){
								window.parent.$('.undercart').slideToggle();
							}
							setTimeout(function(){
								if( !on_undercart ){
									window.parent.$('.undercart').slideToggle();
								}
								}, 3000);
							
							
						}

					}
					
					 MarionConfirm(
						 js_success_title_alert,
						 data.text_popup,
						 function(){
							parent.chiudi_popup_add_cart();
							
						 },
						 js_go_cart,
						 'OK');
			    }else{
					MarionAlert(js_error_title_alert,data.error);
			    	
		    	}
		  },
		 
		});

}
function chiudi_popup_add_cart(){
	var magnificPopup = $.magnificPopup.instance; // save instance in magnificPopup variable
	magnificPopup.close();
	document.location.href=js_activelocale+"/cart.htm";
}
function plus(cod){
	if( cod ){
		var el = $('#quantity_'+cod);
	}else{
		var el = $('#quantity');
	}
	
	quantity = el.val();
	quantity = parseInt(quantity)+1;
	

	el.val(quantity);
	el.trigger('change');
	totalCart();
	return;
}


function minus(cod){
	if( cod ){
		var el = $('#quantity_'+cod);
	}else{
		var el = $('#quantity');
	}
	
	quantity = el.val();

	quantity = parseInt(quantity)-1;
	if(quantity <= 0) quantity = 1;

	el.val(quantity);
	el.trigger('change');
	totalCart();
	return;
}

//funzione che calcola il carrello laterale
function totalCart(){
	var total = 0;
	var subtotal = 0;
	
	if( typeof js_step_cart != 'undefined' && js_step_cart == 1 ){
		$('input').each(function(index,value){
			
			cod = $(this).attr('cod');
			if( cod ){
				price = $(this).attr('price');
				quantity = $(this).val();
				totalRow = parseFloat(price)*parseFloat(quantity);
				
				subtotal = subtotal + totalRow;
				totalRowFormatted = (totalRow).formatMoney(2, ',', '');
				$('.total_'+cod).html(totalRowFormatted);
			}
			
		});
	}else{
		subtotal = parseFloat($('#subtotal').val());
	}

	if( $('#priceShipping').length > 0 ){
		var shipping = parseFloat($('#priceShipping').val());
	}else{
		var shipping = 0;
	}

	if( $('#pricePayment').length > 0 ){
		var payment = parseFloat($('#pricePayment').val());
	}else{
		var payment = 0;
	}
	
	
	total = subtotal + shipping + payment;
	var totalFormatted = (total).formatMoney(2, ',', '');
	var subtotalFormatted = (subtotal).formatMoney(2, ',', '');
	
	$('#subtotalformatted').html(subtotalFormatted);
	$('#total').html(totalFormatted);

}


function deleteOrder(id){
	$.ajax({
	  type: "GET",
	  url: "/index.php",
	  data: { ajax: 1, ctrl:'Ajax',mod:'ecommerce',action: "deleteOrder",id : id},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				if( $('#numberProductCart').length > 0){
					if( data.number_products > 0 ){
						$('#numberProductCart').html(data.number_products);
						$('#numberProductCart_cont').show();
					}else{
						$('#numberProductCart_cont').hide();
					}
				}else{
					$('#numberProductCart_cont').hide();
				}
				if( $('#totalCart').length > 0){
					$('#totalCart').html(data.total);

				}
				if( $('#undercart_content').length > 0){
					$('#undercart_content').html(data.undercart);
					if( data.number_products > 0 ){
						if( !$('#undercart_content').is(":visible") ){
							$('.undercart').slideToggle();
						}
						setTimeout(function(){
							if( !on_undercart ){
								$('.undercart').slideToggle();
							}
							}, 3000);
						
						
					}

				}
				
			}else{
				MarionAlert(js_error_title_alert,data.error);
			}
	  },
	 
	});
}


function openProductCard(id,el){
	var qnt = parseInt(el.closest('.cont_btn').find('.qnt_add_cart').val());
	if(!qnt) qnt = 1;
	document.location.href="index.php?mod=catalogo&ctrl=Catalogo&action=product&qnt="+qnt+"&product="+id;
}

function addToCartPopup(id,el){
	
	var qnt = el.closest('div').find('.qnt_add_cart').val();

	$.magnificPopup.open({
	  items: {
		src: '/catalog.php?action=product&ajax=1&product='+id+"&qnt="+qnt
	  },
	  type: 'iframe'
	}, 0);
}


$(document).ready(function(){
	
	$(".carrellino").on("click",function(){
		$(".undercart").slideToggle();
		$(".underuser").slideUp();
	});


	$(".open_sub").click(function(){
		var lui = $(this);
		var controllo = lui.find(".sub-menu").hasClass("activissimo");
		if(controllo)
		{
			lui.find(".sub-menu").removeClass("activissimo");
			lui.find(".sub-menu").slideToggle();
		}
		else
		{
			$(".sub-menu").each(function(){
				var temp = $(this).hasClass("activissimo");
				if(temp)
				{
					$(this).slideToggle();
					$(this).removeClass("activissimo");
				}
			});
			lui.find(".sub-menu").slideToggle();
			lui.find(".sub-menu").addClass("activissimo");
		}
		$( ".sub-menu li" ).click(function( event ) {
			event.stopPropagation();
		});
	});
});

$(window).resize(function(){
	aggiustacarrellino();
});



function aggiustacarrellino()
{
	var w = $(window).width();
	var h = $(window).height();
	if(w >= 768){
		var hfixtop = $(".fixtop").height();
		var maxh = h-hfixtop-50;
		var hcart = $(".fixedcart").height();
		console.log(maxh);
		if( hcart> maxh)
		{
			$(".fixedcart").css("height" , maxh).css("overflow-y" , "scroll");
		}
		else
		{
			$(".fixedcart").css("height" , "auto").css("overflow-y" , "hidden");
		}
		
	}
}




function add_to_wishlist(product){
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ctrl:'Ajax',mod:'ecommerce',ajax:1,action: "add_to_wishlist",product : product},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					$('#add_wish_'+product).hide();
					$('#remove_wish_'+product).delay(100).fadeIn(300);
					$('.add_wish_'+product).hide();
					$('.remove_wish_'+product).delay(100).fadeIn(300);
					//MarionAlert(js_success_title_alert,data.info);
					if( data.tot > 0 ){
						$('#numberProductWishlist').html(data.tot);
						$('#numberProductWishlist_cont').fadeIn(300);
					}else{
						$('#numberProductWishlist_cont').fadeOut(200);
					}
				}else{
					if( $('#add_product_mobile_popup').length > 0 ){
						showtopmsg(data.error,'error');
						//$('#add_product_mobile_popup').html(data.error);
					}else{
						MarionAlert(js_error_title_alert,data.error);
					}
				}
		  }
		 
	});
}



function remove_from_wishlist(product,flag){
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ctrl:'Ajax',mod:'ecommerce',ajax:1,action: "remove_from_wishlist",product : product},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					$('#remove_wish_'+product).hide();
					$('#add_wish_'+product).delay(100).fadeIn(300);
					$('.remove_wish_'+product).hide();
					$('.add_wish_'+product).delay(100).fadeIn(300);
					if( data.tot > 0 ){
						$('#numberProductWishlist').html(data.tot);
						$('#numberProductWishlist_cont').fadeIn(300);
					}else{
						$('#numberProductWishlist_cont').fadeOut(200);
					}

					if( flag || (typeof wishlist != 'undefined' && wishlist == 1) ){
						$('#riga_'+product).fadeOut('300');
					}
					//MarionAlert(js_success_title_alert,data.info);
					
				}else{
					MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
	});
}









