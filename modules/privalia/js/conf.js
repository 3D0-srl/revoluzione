function download_carreiers(){
	
	var t = confirm("L'operazione richiede che il token di Privalia sia stato inserito e salvato. Al termine dell'operazione la pagina verrà ricaricata per cui si prega di effettuare gli opportuni salvataggi.")
	if( t ){
		$.ajax({
		  type: "GET",
			  url: "../index.php",
			  data: { action: "get_carriers",mod:'privalia', ajax:1},
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
						document.location.reload();

					}else{
						//notify(data.errore,'error');
					}
			  },
		 
		});
	}
}

function download_channels(){
	
	var t = confirm("L'operazione richiede che il token di Privalia sia stato inserito e salvato. Al termine dell'operazione la pagina verrà ricaricata per cui si prega di effettuare gli opportuni salvataggi.")
	if( t ){
		$.ajax({
		  type: "GET",
			  url: "../index.php",
			  data: { action: "get_channels",mod:'privalia', ajax:1},
			  dataType: "json",
			  success: function(data){
					if(data.result == 'ok'){
						document.location.reload();

					}else{
						//notify(data.errore,'error');
					}
			  },
		 
		});
	}
}