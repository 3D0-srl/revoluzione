$(document).ready(function(){
   $('.account_amazon').on('click',function(){
    var el = $(this);
    var index = el.attr('index');
    $('.account_amazon').removeClass('active');
    el.addClass('active');
    $('.box_account_amazon').hide();
    $('#box_amazon_'+index).show();
    console.log(index);
   });
   
   $('.account_market_amazon').on('click',function(){
    var el = $(this);
    var account = el.attr('account');
    var market = el.attr('market');
    el.closest('.box_account_amazon').find('.account_market_amazon').each(function(){
        $(this).removeClass('active');
    });
    el.addClass('active');
    $('.box_market_amazon').hide();
    $('.box_market_amazon_'+account+"_"+market).show();
    //$('#box_amazon_'+index).show();
   });

   $('.parent_amazon_description').each(function(){
	   if(  $(this).prop('checked') == true ){
	    $(this).closest('.box_market_amazon').find('#parent_data_amazon').show();
	   }
   
   });
   $('.parent_amazon_description').on('change',function(){
		
	   if( $(this).prop('checked') == true ){
			$(this).closest('.box_market_amazon').find('#parent_data_amazon').show();
		}else{
			$(this).closest('.box_market_amazon').find('#parent_data_amazon').hide();
		}
	});
   
   
});