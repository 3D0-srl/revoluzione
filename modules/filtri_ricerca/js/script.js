function mod_feature(id){
	$('#id_feature').val(id);

	$.ajax({
	  type: "GET",
	  url: "ajax.php",
	  data: { action: "get_feature",id : id},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				for( var k in data.dati ){
					$('#'+k).val(data.dati[k]);
				}
				
				$('#btn_add').click();
			}
	  },
	 
	});
}

function mod_feature_value(id){
	$('#id_feature_value').val(id);

	$.ajax({
	  type: "GET",
	  url: "ajax.php",
	  data: { action: "get_feature_value",id : id},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				for( var k in data.dati ){
					$('#'+k).val(data.dati[k]);
				}
				
				$('#btn_add').click();
			}
	  },
	 
	});
}

function save_feature(){
	$('#error_form').html('');
	var id = $('#id_feature').val();
	var formdata = $('#form_feature').serialize();
	$.ajax({
	  type: "GET",
	  url: "ajax.php",
	  data: { action: "edit",id : id,formdata:formdata},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				document.location.reload();
			}else{
				$('#error_form').html(data.error);
			}
	  },
	 
	});
}

function save_feature_value(){
	$('#error_form').html('');
	var id = $('#id_feature').val();
	var formdata = $('#form_feature').serialize();
	$.ajax({
	  type: "GET",
	  url: "ajax.php",
	  data: { action: "edit_value",id : id,formdata:formdata},
	  dataType: "json",
	  success: function(data){
			if(data.result == 'ok'){
				document.location.reload();
			}else{
				$('#error_form').html(data.error);
			}
	  },
	 
	});
}


function del_feature(id){
	MarionConfirm('Conferma operazione','Eliminando questa caratteristica eliminerai anche i valori ad essa associati. Sicuro di voler procedere?',function(){
		 document.location.href="index.php?action=del_feature&id="+id;
	});
}

function del_feature_value(id){
	MarionConfirm('Conferma operazione','Sicuro di voler cancellare questo valore?',function(){
		 document.location.href="index.php?action=del_feature_value&id="+id;
	});
}


$(document).ready(function() {
    // Initialise the table
	if(  $("#tabella_features").length > 0 ){
		$("#tabella_features").tableDnD({
			onDrop: function(table, row) {
				var ordine = $('#tabella_features').tableDnDSerialize();

				$.ajax({
				  type: "GET",
				  url: "ajax.php",
				  data: { action: "order_features",ordine : ordine},
				  dataType: "json",
				  success: function(data){
						
				  },
				 
				});
			},
		});
	}
	if(  $("#tabella_values").length > 0 ){
		 $("#tabella_values").tableDnD({
			onDrop: function(table, row) {
				var ordine = $('#tabella_features').tableDnDSerialize();

				$.ajax({
				  type: "GET",
				  url: "ajax.php",
				  data: { action: "order_feature_values",ordine : ordine},
				  dataType: "json",
				  success: function(data){
						
				  },
				 
				});
			},
		});
	}
});