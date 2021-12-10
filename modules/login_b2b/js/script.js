$(function(){
		$('#submit-login').click(function(e){
			
			e.preventDefault();
		   
			var l = Ladda.create(this);
			l.start();
			
			
			var formdata = $('#form_login').serialize();
			
			setTimeout(function () {
					$.ajax({
					  type: "POST",
					  url: "/modules/login_b2b/index.php",
					  data: { 'action': "login",'formdata' : formdata},
					  dataType: "json",
					  success: function(data){


							
							if(data.result == 'ok'){
								if( typeof js_return_url != 'undefined' && js_return_url != null ){
									document.location.href=js_return_url;	
								}else{
									document.location.href="/index.php";
								}
							}else{
								
								l.stop();	
								$('#login_b2b_username').addClass('shake animated');
								$('#login_b2b_password').addClass('shake animated');
								setTimeout(function () {
									$('#login_b2b_username').removeClass('shake animated');
									$('#login_b2b_password').removeClass('shake animated');
								}, 1000);
								
								
							}
					  },
					  error: function(){
						alert("error");
					  }
					});
			}, 1000);
		});

		$('#submit-lost-pwd').click(function(e){
			
			e.preventDefault();
		   
			var l = Ladda.create(this);
			l.start();
			
			
			var formdata = $('#form_login').serialize();
			
			setTimeout(function () {
					$.ajax({
					  type: "POST",
					  url: "/modules/login_b2b/index.php",
					  data: { 'action': "lost_pwd",'formdata' : formdata},
					  dataType: "json",
					  success: function(data){


							
							if(data.result == 'ok'){
								document.location.href="?action=lost_pwd_ok";
							}else{
								
								l.stop();	
								$('#login_b2b_email').addClass('shake animated');
								
								setTimeout(function () {
									$('#login_b2b_email').removeClass('shake animated');
									
								}, 1000);
								
								
							}
					  },
					  error: function(){
						alert("error");
					  }
					});
			}, 1000);
		});

		
});