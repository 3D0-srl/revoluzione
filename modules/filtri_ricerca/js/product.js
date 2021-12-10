$(document).ready(function() {
	$('.feature_select').on('change',function(){
		
		var feature = $(this).attr('feature');
		if( parseInt($(this).val()) == -1 ){
			$('#other_'+feature).attr('required','required').show();
		}else{
			$('#other_'+feature).hide();
		}
	})


	$('.other_future').each(function(){
		if( $(this).find('input').val().trim() ){
			$(this).show();

			$('#feature_'+$(this).attr('feature')).val('-1');
		}

	
	});

});