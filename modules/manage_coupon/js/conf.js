$(document).ready(function(){
	
	if( typeof js_limit != 'undefined' && js_limit != null ){
		
		switch(js_limit){
			case 'category_users':
				$('#div_user_category').show();
				$('#div_users').hide();
				break;
			case 'specific_users':
				$('#div_user_category').hide();
				$('#div_users').show();
				break;
			default:
				$('#div_users').hide();
				$('#div_user_category').hide();
				
				break;
		}
	}

	if( typeof js_multiple_use != 'undefined' && js_multiple_use != null && js_multiple_use == 0){
		$('#div_num_repeat').hide();
	}
	
	$('#div_use_limit input:radio').on('change',function(){
		
		var limit = $(this).val();
		var checked = $(this).prop('checked');
		if( checked == true){
			switch(limit){
				case '0':
					$('#div_users').hide();
					$('#div_user_category').hide();
					break;
				case 'category_users':
					$('#div_user_category').show();
					$('#div_users').hide();
					break;
				case 'specific_users':
					$('#div_user_category').hide();
					$('#div_users').show();
					break;
			}
		}
	});

	$('#div_multiple_use input:radio').on('change',function(){
		var val = $(this).val();
		var checked = $(this).prop('checked');
		if( checked == true){
			switch(val){
				case '0':
					$('#div_num_repeat').hide();
					break;
				case '1':
					$('#div_num_repeat').show();
					break;
			}
		}
	});
});