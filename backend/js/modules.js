var modules_checked_flag = [];
var modules_submitted = [];
function execute_action(module,action,reload){
	

	if( action == 'install' || action == 'uninstall' ){
		$.ajax({
		  type: "GET",
			  url: "../modules/"+module+"/"+action+".php",
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
						if( reload ){
							document.location.reload();
						}else{
							modules_checked_flag.push(module);
							if( modules_checked_flag.length == modules_submitted.length){
								document.location.reload();
							}
						}
					}else{
						if( reload ){
							alert(data.errore);
						}else{
							modules_checked_flag.push(module);
							if( modules_checked_flag.length == modules_submitted.length){
								document.location.reload();
							}
						}
					}
					
			  },
			 
		});

	}else{

		$.ajax({
		  type: "GET",
			  url: "../modules/"+module+"/moduleAction.php?action="+action,
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
						if( reload ){
							document.location.reload();
						}else{
							modules_checked_flag.push(module);
							if( modules_checked_flag.length == modules_submitted.length){
								document.location.reload();
							}
						}
					}else{
						if( reload ){
							alert(data.errore);
						}else{
							modules_checked_flag.push(module);
							if( modules_checked_flag.length == modules_submitted.length){
								document.location.reload();
							}
						}
					}

					
			  },
			 
		});


	}
}

function execute_action2(module,action,reload){
	


	$.ajax({
		type: "GET",
		url: "index.php",
		data : {module:module,ctrl:'ModuleAdmin',action:action,ajax:1},
		dataType: "json",
		success: function(data){
			if(data.result == 'ok'){
				if( reload ){
					document.location.reload();
				}else{
					modules_checked_flag.push(module);
					if( modules_checked_flag.length == modules_submitted.length){
						document.location.reload();
					}
				}
			}else{
				if( reload ){
					alert(data.errore);
				}else{
					modules_checked_flag.push(module);
					if( modules_checked_flag.length == modules_submitted.length){
						document.location.reload();
					}
				}
			}

				
		},
			
	});


	
}


function module_action2(module,action){
	switch(action){
		case 'active':
			msg = "Sicuro di volere attivare questo modulo?";
			break;
		case 'disable':
			msg = "Sicuro di volere disattivare questo modulo?";
			break;
		case 'install':
			msg = "Sicuro di volere installare questo modulo?";
			break;
		case 'uninstall':
			msg = "Verranno cancellati tutte le informazioni memorizzate dal modulo nel database.\n Sicuro di volere rimuovere questo modulo?";
			break;

	}
	var t = confirm(msg);

	if( t ){
		execute_action2(module,action,true);
	}
}

function module_action(module,action){
	switch(action){
		case 'active':
			msg = "Sicuro di volere attivare questo modulo?";
			break;
		case 'disable':
			msg = "Sicuro di volere disattivare questo modulo?";
			break;
		case 'install':
			msg = "Sicuro di volere installare questo modulo?";
			break;
		case 'uninstall':
			msg = "Verranno cancellati tutte le informazioni memorizzate dal modulo nel database.\n Sicuro di volere rimuovere questo modulo?";
			break;

	}
	var t = confirm(msg);

	if( t ){
		execute_action(module,action,true);
	}
}
function install_module(module){
	var t = confirm('Sicuro di volere installare questo modulo?');
	if(t){ 
		$.ajax({
		  type: "GET",
			  url: "../modules/"+module+"/install.php",
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
							notify('modulo installato con successo','success');
							$('#install_'+module).hide();
							$('#uninstall_'+module).show();
					}else{
						notify(data.errore,'error');
					}
			  },
			 
			});
	
		//document.location.href="/admin/content.php?action=del_page&id="+id;
	}
}

function uninstall_module(module){
	var t = confirm('Sicuro di volere rimuovere questo modulo?');
	if(t){ 
		$.ajax({
		  type: "GET",
			 url: "../modules/"+module+"/uninstall.php",
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
							$('#uninstall_'+module).hide();
							$('#install_'+module).show();
							notify('modulo rimosso con successo','success');
					}else{
						notify(data.errore,'error');
					}
			  },
			 
			});
	
		//document.location.href="/admin/content.php?action=del_page&id="+id;
	}
}

function enable_module(module){
	var t = confirm('Sicuro di volere abilitare questo modulo?');
	if(t){ 
		$.ajax({
		  type: "GET",
			  url: "index.php",
			  data: {action:'enable_module_default',id:module,'ajax':1,ctrl:'ModuleAdmin'},
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
							//notify('modulo installato con successo','success');
							$('#enable_'+module).hide();
							$('#disable_'+module).show();
					}else{
						notify(data.errore,'error');
					}
			  },
			 
			});
	
		//document.location.href="/admin/content.php?action=del_page&id="+id;
	}
}

function disable_module(module){
	var t = confirm('Sicuro di volere disabilitare questo modulo?');
	if(t){ 
		$.ajax({
		  type: "GET",
			 url: "/admin/admin.php",
			  data: {action:'disable_module_default',id:module},
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
							$('#disable_'+module).hide();
							$('#enable_'+module).show();
							notify('modulo rimosso con successo','success');
					}else{
						notify(data.errore,'error');
					}
			  },
			 
			});
	
		//document.location.href="/admin/content.php?action=del_page&id="+id;
	}
}


function submit_bulk_action_modules(action){
	var t = confirm('Sicuro di volere procedere con questa operazione?');
	if(t){ 
		$('.module_check').each(function(){
			if( $(this).prop('checked') == true ){
				var active = parseInt($(this).attr('active'));
				console.log(active);
				if( active && action == 'disable'){
					var module = $(this).val();
					modules_submitted.push(module);
				}
				if( !active && action == 'active'){
					var module = $(this).val();
					modules_submitted.push(module);
				}
				
			}
		});
		if(  modules_submitted.length ){
			for( var k in modules_submitted ){
				
					execute_action(modules_submitted[k],action,false);
				
			};
		}else{
			alert("L'operazione indicata non può essere applicata a nessun modulo selezionato.")
		}
	}
}



function select_all_module(el){
	$('.module_check').prop('checked',el.prop('checked'));
	
}