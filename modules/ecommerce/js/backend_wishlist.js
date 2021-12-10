$(document).ready(function(){	
	$("#share_url_mail").on("click",function(e){
		e.preventDefault();
		$(".send-wishlist").addClass("active");
	});
	$(".cancel-email").on("click",function(e){
		e.preventDefault();
		$('#email_share').val('');
		$(".send-wishlist").removeClass("active");
	});

	
});

function send_mail_wish(){
	var email = $('#email_share').val();


	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { ctrl:'Wishlist',mod:'ecommerce',action: "share_email",email :email,ajax:1},
		  dataType: "json",
		  success: function(data){
				
				if(data.result == 'ok'){
					$('.send-success').show();
					$('#email_share').val('');
					setTimeout(function(){

							$('.send-success').hide();
							
						},3000);
				}else{
					$('#email_share').addClass('error');
				}
		  },
		 
	});

	
	
}

function new_address(id){
	
	
}